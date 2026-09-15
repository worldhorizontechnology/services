<?php

namespace App\Tools;

use App\Resources\GoogleCalendarResource;
use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;
use Doctrine\DBAL\Connection;
use DateTimeImmutable;
use DateTimeZone;
use Throwable;

class CalendarTools
{
    public function __construct(
        private GoogleCalendarResource $calendarService,
        private Connection $connection
    ) {}

    /**
     * Checks available slots across multiple master calendars filtered by serviceId OR masterId.
     */
    #[McpTool(name: 'check_calendar_slots')]
    public function checkCalendarSlots(
        #[Schema(pattern: '^\d{4}-\d{2}-\d{2}$', description: 'Start date of the range in YYYY-MM-DD format')]
        string $startDate,

        #[Schema(pattern: '^\d{4}-\d{2}-\d{2}$', description: 'End date of the range in YYYY-MM-DD format')]
        string $endDate,

        #[Schema(description: 'Optional database ID of the requested service')]
        ?int $serviceId = null,

        #[Schema(description: 'Optional database ID of a specific master/user')]
        ?int $masterId = null
    ): string {
        try {
            // Validate service existence if serviceId is provided
            if ($serviceId !== null) {
                $serviceExists = $this->connection->fetchOne(
                    'SELECT 1 FROM services WHERE id = :serviceId',
                    ['serviceId' => $serviceId]
                );

                if (!$serviceExists) {
                    return json_encode([
                        'error' => sprintf('Service with ID %d not found', $serviceId)
                    ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
                }
            }

            $query = 'SELECT DISTINCT u.google_calendar_id FROM users u';
            $params = [];

            if ($serviceId !== null) {
                $query .= ' JOIN service_user su ON u.id = su.user_id
                            WHERE su.service_id = :serviceId AND u.google_calendar_id IS NOT NULL AND u.is_active = 1';
                $params['serviceId'] = $serviceId;
            } elseif ($masterId !== null) {
                $query .= ' WHERE u.id = :masterId AND u.google_calendar_id IS NOT NULL AND u.is_active = 1';
                $params['masterId'] = $masterId;
            } else {
                $query .= ' WHERE u.google_calendar_id IS NOT NULL AND u.is_active = 1';
            }

            // Fetch flat array of calendar IDs
            $calendarIds = $this->connection->fetchFirstColumn($query, $params);

            if (empty($calendarIds)) {
                return json_encode(['error' => 'No active master calendars found'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
            }

            // Pass the entire array of IDs into the service for a single Free/Busy request
            $availableSlotsGrouped = $this->calendarService->getAvailableSlots($calendarIds, $startDate, $endDate, 60);

            return json_encode($availableSlotsGrouped, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

        } catch (Throwable $e) {
            return json_encode([
                'error'      => 'Execution error',
                'error_type' => get_class($e),
                'message'    => $e->getMessage(),
                'file'       => $e->getFile() . ':' . $e->getLine()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Books a slot in Google Calendar for an Instagram client.
     * Automatically resolves calendarId via masterId or serviceId if not explicitly provided.
     */
    #[McpTool(name: 'book_calendar_slots')]
    public function bookCalendarSlots(
        #[Schema(pattern: '^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$', description: 'Start datetime in YYYY-MM-DD HH:MM format')]
        string $datetime_start,

        #[Schema(description: 'Client name')]
        string $client_name,

        #[Schema(description: 'Instagram Scoped User ID')]
        string $igsid,

        #[Schema(description: 'Optional direct ID of the calendar')]
        ?string $calendarId = null,

        #[Schema(description: 'Optional database ID of a specific master/user')]
        ?int $masterId = null,

        #[Schema(description: 'Optional database ID of the service')]
        ?int $serviceId = null
    ): string {
        try {
            $resolvedCalendarId = $calendarId;

            // If calendarId is not explicitly provided, attempt resolution via masterId or serviceId
            if (empty($resolvedCalendarId)) {
                if ($masterId !== null) {
                    $resolvedCalendarId = $this->connection->fetchOne(
                        'SELECT google_calendar_id FROM users WHERE id = :masterId AND is_active = 1 AND google_calendar_id IS NOT NULL',
                        ['masterId' => $masterId]
                    );

                    if (!$resolvedCalendarId) {
                        return json_encode([
                            'error' => sprintf('Active master with ID %d and valid Google Calendar not found', $masterId)
                        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
                    }
                } elseif ($serviceId !== null) {
                    // Validate service existence
                    $serviceExists = $this->connection->fetchOne(
                        'SELECT 1 FROM services WHERE id = :serviceId',
                        ['serviceId' => $serviceId]
                    );

                    if (!$serviceExists) {
                        return json_encode([
                            'error' => sprintf('Service with ID %d not found', $serviceId)
                        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
                    }

                    // Retrieve the first available calendar ID of an active master assigned to this service
                    $resolvedCalendarId = $this->connection->fetchOne(
                        'SELECT u.google_calendar_id
                         FROM users u
                         JOIN service_user su ON u.id = su.user_id
                         WHERE su.service_id = :serviceId
                           AND u.is_active = 1
                           AND u.google_calendar_id IS NOT NULL
                         LIMIT 1',
                        ['serviceId' => $serviceId]
                    );

                    if (!$resolvedCalendarId) {
                        return json_encode([
                            'error' => sprintf('No active masters with Google Calendar found for service ID %d', $serviceId)
                        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
                    }
                } else {
                    return json_encode([
                        'error' => 'At least one of calendarId, masterId, or serviceId must be provided'
                    ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
                }
            }

            $timezone = new DateTimeZone('Europe/Madrid');
            $startDt = new DateTimeImmutable($datetime_start, $timezone);
            $endDt = $startDt->modify('+60 minutes');

            $eventId = $this->calendarService->createEvent(
                $resolvedCalendarId,
                $igsid,
                $client_name,
                $startDt->format(DATE_RFC3339),
                $endDt->format(DATE_RFC3339)
            );

            return json_encode([
                'status' => 'success',
                'event_id' => $eventId,
                'calendar_id' => $resolvedCalendarId,
                'start' => $startDt->format(DATE_RFC3339),
                'end' => $endDt->format(DATE_RFC3339)
            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

        } catch (Throwable $e) {
            return json_encode([
                'error'      => sprintf('Failed to book slot: %s', $e->getMessage()),
                'error_type' => get_class($e),
                'file'       => $e->getFile() . ':' . $e->getLine()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}