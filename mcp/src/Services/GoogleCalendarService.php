<?php

namespace App\Service;

use Google\Client;
use Google\Service\Calendar;
use DateTimeImmutable;
use DateTimeZone;

class GoogleCalendarService
{
    private Calendar $calendar;

    public function __construct()
    {
        $client = new Client();

        $client->setAuthConfig(
            $_ENV['GOOGLE_APPLICATION_CREDENTIALS']
        );

        $client->addScope(Calendar::CALENDAR);

        $this->calendar = new Calendar($client);
    }

    public function getAvailableSlots(
        string $calendarId,
        string $date,
        int $durationMinutes,
        string $workStart = '09:00',
        string $workEnd = '18:00',
        int $stepMinutes = 30
    ): array {
        $timezone = new DateTimeZone('Europe/Madrid');

        $dayStart = new DateTimeImmutable(
            "$date $workStart",
            $timezone
        );

        $dayEnd = new DateTimeImmutable(
            "$date $workEnd",
            $timezone
        );

        // Google Calendar сам отдаёт занятые события
        $events = $this->calendar->events->listEvents(
            $calendarId,
            [
                'timeMin' => $dayStart->format(DATE_RFC3339),
                'timeMax' => $dayEnd->format(DATE_RFC3339),
                'singleEvents' => true,
                'orderBy' => 'startTime',
            ]
        );

        $busy = [];

        foreach ($events->getItems() as $event) {
            $start = $event->getStart()->getDateTime();
            $end = $event->getEnd()->getDateTime();

            // Пропускаем события без конкретного времени
            if (!$start || !$end) {
                continue;
            }

            $busy[] = [
                'start' => new DateTimeImmutable($start),
                'end' => new DateTimeImmutable($end),
            ];
        }

        $slots = [];

        // Перебираем возможные начала записи
        for (
            $slotStart = $dayStart;
            $slotStart->modify("+{$durationMinutes} minutes") <= $dayEnd;
            $slotStart = $slotStart->modify("+{$stepMinutes} minutes")
        ) {
            $slotEnd = $slotStart->modify(
                "+{$durationMinutes} minutes"
            );

            $free = true;

            // Проверяем пересечение с каждым событием Google Calendar
            foreach ($busy as $event) {
                if (
                    $slotStart < $event['end'] &&
                    $slotEnd > $event['start']
                ) {
                    $free = false;
                    break;
                }
            }

            if ($free) {
                $slots[] = [
                    'start' => $slotStart->format(DATE_RFC3339),
                    'end' => $slotEnd->format(DATE_RFC3339),
                ];
            }
        }

        return $slots;
    }

    public function createEvent(
        string $calendarId,
        string $summary,
        string $start,
        string $end,
        ?string $description = null
    ): string {
        $event = new Calendar\Event([
            'summary' => $summary,
            'description' => $description,
            'start' => [
                'dateTime' => $start,
                'timeZone' => 'Europe/Madrid',
            ],
            'end' => [
                'dateTime' => $end,
                'timeZone' => 'Europe/Madrid',
            ],
        ]);

        $created = $this->calendar->events->insert(
            $calendarId,
            $event
        );

        return $created->getId();
    }
}