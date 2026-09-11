<?php

namespace App\Tools;

use App\Service\GoogleCalendarService;
use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;

class CalendarTools
{
    public function __construct(
        private GoogleCalendarService $calendarService
    ) {}

    /**
     * Checks available booking slots in Google Calendar for a specialist on a date.
     */
    #[McpTool(name: 'check_calendar_slots')]
    public function checkCalendarSlots(
        #[Schema(description: 'Name or identifier of the specialist')]
        string $master_name,

        #[Schema(pattern: '^\d{4}-\d{2}-\d{2}$', description: 'Date in YYYY-MM-DD format')]
        string $date
    ): string {
        return $this->calendarService->checkAvailableSlots($master_name, $date);
    }

    /**
     * Books a slot in Google Calendar for an Instagram client.
     */
    #[McpTool(name: 'book_calendar_slot')]
    public function bookCalendarSlot(
        #[Schema(description: 'Name of the specialist')]
        string $master_name,

        #[Schema(description: 'Start datetime in YYYY-MM-DD HH:MM format')]
        string $datetime_start,

        #[Schema(description: 'Client full name')]
        string $client_name,

        #[Schema(description: 'Instagram Scoped User ID')]
        string $igsid
    ): string {
        return $this->calendarService->bookSlot($master_name, $datetime_start, $client_name, $igsid);
    }
}
