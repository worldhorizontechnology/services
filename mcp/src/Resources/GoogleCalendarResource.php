<?php

namespace App\Resources;

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\FreeBusyRequest;
use Google\Service\Calendar\FreeBusyRequestItem;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Mcp\Capability\Attribute\McpResource;

class GoogleCalendarResource
{
    private Calendar $calendar;

    public function __construct()
    {
        $client = new Client();
        
        // Fetch credentials strictly from $_ENV (Symfony style)
        $client->setClientId($_ENV['GOOGLE_CLIENT_ID'] ?? null);
        $client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET'] ?? null);
        $client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI'] ?? 'http://localhost');
        
        $client->addScope(Calendar::CALENDAR);
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        // Path to store access token (e.g. in the var directory of a Symfony project)
        $tokenPath = dirname(__DIR__, 2) . '/var/google_calendar_token.json';

        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        if ($client->isAccessTokenExpired()) {
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                file_put_contents($tokenPath, json_encode($client->getAccessToken()));
            } else {
                // If running in CLI for initial token generation
                $authUrl = $client->createAuthUrl();
                
                echo "\n==================================================\n";
                echo "OPEN THIS URL IN BROWSER FOR AUTHORIZATION:\n";
                echo $authUrl . "\n";
                echo "==================================================\n\n";
                
                $authCode = $_GET['code'] ?? null;

                if (!$authCode) {
                    throw new Exception("Authorization code is missing from the URL parameters.");
          }
                
                if (empty($authCode)) {
                    throw new Exception("Authorization code cannot be empty.");
                }

                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($accessToken);

                file_put_contents($tokenPath, json_encode($client->getAccessToken()));
                echo "\n[SUCCESS] File google_calendar_token.json was created in var directory!\n";
            }
        }

        $this->calendar = new Calendar($client);
    }

    /**
     * Executes ONE single Free/Busy query for all calendars and returns slots mapped to each calendar ID.
     */
    #[McpResource(
        uri: 'calendar://slots/available',
        name: 'google_calendar_slots',
        mimeType: 'application/json'
    )]
    public function getAvailableSlots(
        array $calendarIds,
        string $startDate,
        string $endDate,
        int $durationMinutes = 60,
        string $workStart = '09:00',
        string $workEnd = '22:00',
        int $stepMinutes = 60
    ): array {
        $timezone = new DateTimeZone('Europe/Madrid');
        $rangeStart = new DateTimeImmutable("$startDate $workStart", $timezone);
        $rangeEnd = new DateTimeImmutable("$endDate $workEnd", $timezone);

        $requestItems = [];
        foreach ($calendarIds as $id) {
            $item = new FreeBusyRequestItem();
            $item->setId($id);
            $requestItems[] = $item;
        }

        $fbRequest = new FreeBusyRequest();
        $fbRequest->setTimeMin($rangeStart->format(DATE_RFC3339));
        $fbRequest->setTimeMax($rangeEnd->format(DATE_RFC3339));
        $fbRequest->setTimeZone('Europe/Madrid');
        $fbRequest->setItems($requestItems);

        $fbResponse = $this->calendar->freebusy->query($fbRequest);
        $calendarsData = $fbResponse->getCalendars();

        $result = [];

        foreach ($calendarIds as $calendarId) {
            $data = is_array($calendarsData)
                ? ($calendarsData[$calendarId] ?? null)
                : $calendarsData->get($calendarId);
            $busyIntervals = [];

            if ($data && method_exists($data, 'getBusy')) {
                foreach ($data->getBusy() as $period) {
                    $busyIntervals[] = [
                        'start' => new DateTimeImmutable($period->getStart(), $timezone),
                        'end' => new DateTimeImmutable($period->getEnd(), $timezone)
                    ];
                }
            }

            $masterSlots = [];
            $currentDay = new DateTimeImmutable($startDate, $timezone);
            $lastDay = new DateTimeImmutable($endDate, $timezone);

            while ($currentDay <= $lastDay) {
                $dayStr = $currentDay->format('Y-m-d');
                $slotStart = new DateTimeImmutable("$dayStr $workStart", $timezone);
                $dayEnd = new DateTimeImmutable("$dayStr $workEnd", $timezone);

                while (true) {
                    $slotEnd = $slotStart->modify("+{$durationMinutes} minutes");

                    if ($slotEnd > $dayEnd) {
                        break;
                    }

                    $free = true;
                    foreach ($busyIntervals as $busy) {
                        if ($slotStart < $busy['end'] && $slotEnd > $busy['start']) {
                            $free = false;
                            break;
                        }
                    }

                    if ($free) {
                        $masterSlots[] = [
                            'start' => $slotStart->format(DATE_RFC3339),
                            'end' => $slotEnd->format(DATE_RFC3339),
                        ];
                    }

                    $slotStart = $slotStart->modify("+{$stepMinutes} minutes");
                }

                $currentDay = $currentDay->modify('+1 day');
            }

            if (!empty($masterSlots)) {
                $result[] = [
                    'calendar_id' => $calendarId,
                    'available_slots' => $masterSlots
                ];
            }
        }

        return $result;
    }

    public function createEvent(
        string $calendarId,
        string $igsid,
        string $client_name,
        string $start,
        string $end,
        ?string $description = null
    ): string {
        $event = new Calendar\Event();
        $event->setSummary($client_name);

        if (!empty($igsid)) {
            $cleanIgsid = ltrim($igsid, '@');
            $instagramUrl = "Instagram: https://instagram.com" . $cleanIgsid;
            $description = $description ? "{$description}\n\n{$instagramUrl}" : $instagramUrl;
        }

        if ($description !== null) {
            $event->setDescription($description);
        }

        $startTime = new Calendar\EventDateTime();
        $startTime->setDateTime($start);
        $startTime->setTimeZone('Europe/Madrid');

        $endTime = new Calendar\EventDateTime();
        $endTime->setDateTime($end);
        $endTime->setTimeZone('Europe/Madrid');

        $event->setStart($startTime);
        $event->setEnd($endTime);

        $created = $this->calendar->events->insert($calendarId, $event);

        return $created->getId();
    }
}