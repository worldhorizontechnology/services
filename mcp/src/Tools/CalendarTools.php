<?php

namespace App\Tools;

use App\Services\GoogleCalendarService;
use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;
use Doctrine\DBAL\Connection;
use DateTimeImmutable;
use DateTimeZone;

class CalendarTools
{
    public function __construct(
        private GoogleCalendarService $calendarService,
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
        $query = 'SELECT DISTINCT u.google_calendar_id FROM users u';
        $params = [];

        if ($serviceId !== null) {
            $query .= ' JOIN assignments a ON u.id = a.executor_id
                        JOIN order_service os ON a.order_id = os.order_id
                        WHERE os.service_id = :serviceId AND u.google_calendar_id IS NOT NULL AND u.is_active = 1';
            $params['serviceId'] = $serviceId;
        } elseif ($masterId !== null) {
            $query .= ' WHERE u.id = :masterId AND u.google_calendar_id IS NOT NULL AND u.is_active = 1';
            $params['masterId'] = $masterId;
        } else {
            $query .= ' WHERE u.google_calendar_id IS NOT NULL AND u.is_active = 1';
        }

        // Fetch flat array of calendar IDs from SQLite
        $calendarIds = $this->connection->fetchFirstColumn($query, $params);

        if (empty($calendarIds)) {
            return json_encode(['error' => 'No active master calendars found'], JSON_THROW_ON_ERROR);
        }

        // Pass the entire array of IDs into the service for a SINGLE Free/Busy request
        $availableSlotsGrouped = $this->calendarService->getAvailableSlots($calendarIds, $startDate, $endDate, 60);

        return json_encode($availableSlotsGrouped, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Books a slot in Google Calendar for an Instagram client.
     */
    #[McpTool(name: 'book_calendar_slots')]
    public function bookCalendarSlots(
        #[Schema(description: 'ID of the calendar')]
        string $calendarId,

        #[Schema(description: 'Start datetime in YYYY-MM-DD HH:MM format')]
        string $datetime_start,

        #[Schema(description: 'Client name')]
        string $client_name,

        #[Schema(description: 'Instagram Scoped User ID')]
        string $igsid
    ): string {
        $timezone = new DateTimeZone('Europe/Madrid');
        $startDt = new DateTimeImmutable($datetime_start, $timezone);
        $endDt = $startDt->modify('+60 minutes');

        return $this->calendarService->createEvent(
            $calendarId, 
            $igsid, 
            $client_name, 
            $startDt->format(DATE_RFC3339), 
            $endDt->format(DATE_RFC3339)
        );
    }
}
