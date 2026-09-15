<?php

namespace App\Prompts;

use Mcp\Capability\Attribute\McpPrompt;
use Mcp\Schema\Content\{TextContent, ImageContent};
use Mcp\Schema\PromptMessage;
use Mcp\Schema\Enum\Role;

class PromptGenerator
{
    /**
     * Generates a code review request prompt.
     */
    #[McpPrompt(name: 'code_review')]
    public function reviewCode(string $language, string $code, string $focus = 'general'): array
    {
        return [
            ['role' => 'assistant', 'content' => 'You are an expert code reviewer.'],
            ['role' => 'user', 'content' => "Review this {$language} code focusing on {$focus}:\n\n```{$language}\n{$code}\n```"]
        ];
    }

    /**
     * Generates a request to find available Google Calendar slots.
     */
    #[McpPrompt(name: 'find_calendar_slots')]
    public function findCalendarSlots(
        string $startDate,
        string $endDate,
        ?int $serviceId = null,
        ?int $masterId = null
    ): array {
        $filters = [];

        if ($serviceId !== null) {
            $filters[] = "serviceId={$serviceId}";
        }

        if ($masterId !== null) {
            $filters[] = "masterId={$masterId}";
        }

        $filterText = $filters === [] ? 'all active master calendars' : implode(' and ', $filters);

        return [
            ['role' => 'assistant', 'content' => 'You help users find available appointment slots in Google Calendar.'],
            ['role' => 'user', 'content' => "Find available 60-minute slots from {$startDate} through {$endDate} for {$filterText}. Use the check_calendar_slots tool and return the available slots grouped by calendar."]
        ];
    }

    /**
     * Generates a request to book a Google Calendar slot.
     */
    #[McpPrompt(name: 'book_calendar_slot')]
    public function bookCalendarSlot(
        string $datetimeStart,
        string $clientName,
        string $igsid,
        ?string $calendarId = null,
        ?int $masterId = null,
        ?int $serviceId = null
    ): array {
        $calendarFilter = $calendarId !== null
            ? "calendarId={$calendarId}"
            : implode(' and ', array_filter([
                $masterId !== null ? "masterId={$masterId}" : null,
                $serviceId !== null ? "serviceId={$serviceId}" : null,
            ]));

        return [
            ['role' => 'assistant', 'content' => 'You help users book appointments in Google Calendar. Never book a slot before checking that it is available.'],
            ['role' => 'user', 'content' => "Book the appointment at {$datetimeStart} for client {$clientName} (Instagram ID: {$igsid}) using {$calendarFilter}. First verify the requested time with check_calendar_slots, then use book_calendar_slots only if the slot is available. Confirm the booking result to the client."]
        ];
    }

    #[McpPrompt]
    public function analyzeImage(string $imageUrl, string $question): array
    {
        $imageData = file_get_contents($imageUrl);

        return [
            new PromptMessage(Role::Assistant, [
                new TextContent('You are an image analysis expert.')
            ]),
            new PromptMessage(Role::User, [
                new TextContent($question),
                new ImageContent(
                    data: base64_encode($imageData),
                    mimeType: 'image/jpeg'
                )
            ])
        ];
    }
}