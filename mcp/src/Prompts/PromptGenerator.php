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

    #[McpPrompt(name: 'create_customer')]
    public function createCustomer(
        string $name,
        string $phone,
        ?string $email = null,
        ?int $channelId = null,
        ?int $campaignId = null,
        string $entryPoint = 'instagram_bot'
    ): array {
        return [
            ['role' => 'assistant', 'content' => 'You register or update a customer in the CRM.'],
            ['role' => 'user', 'content' => "Create or update CRM customer {$name}, phone {$phone}, email " . ($email ?? 'not provided') . ". Use channelId=" . ($channelId ?? 'none') . ", campaignId=" . ($campaignId ?? 'none') . ", entryPoint={$entryPoint}. Use create_customer and report the CRM result."]
        ];
    }

    #[McpPrompt(name: 'create_channel')]
    public function createChannel(string $name, string $type = 'online'): array
    {
        return [
            ['role' => 'assistant', 'content' => 'You register a customer acquisition channel in the CRM.'],
            ['role' => 'user', 'content' => "Create the {$type} acquisition channel '{$name}' using create_channel and report the created channel."]
        ];
    }

    #[McpPrompt(name: 'create_campaign')]
    public function createCampaign(
        int $channelId,
        string $name,
        ?string $promoCode = null,
        float $budget = 0.0
    ): array {
        return [
            ['role' => 'assistant', 'content' => 'You register marketing campaigns in the CRM.'],
            ['role' => 'user', 'content' => "Create campaign '{$name}' for channel {$channelId}, promo code " . ($promoCode ?? 'none') . ", budget {$budget}. Use create_campaign and report the result."]
        ];
    }

    #[McpPrompt(name: 'create_order')]
    public function createOrder(
        int $customerId,
        int $serviceId,
        int $quantity = 1,
        ?int $campaignId = null,
        float $discountAmount = 0.0,
        string $status = 'new',
        string $paymentStatus = 'unpaid'
    ): array {
        return [
            ['role' => 'assistant', 'content' => 'You create a CRM order for an existing customer and service. Verify the customer and service context before creating the order.'],
            ['role' => 'user', 'content' => "Create an order for customer {$customerId}, service {$serviceId}, quantity {$quantity}, campaignId=" . ($campaignId ?? 'none') . ", discount={$discountAmount}, status={$status}, paymentStatus={$paymentStatus}. Use create_order and report the order and service line details."]
        ];
    }

    #[McpPrompt(name: 'assign_executor_to_order')]
    public function assignExecutor(
        int $orderId,
        int $executorId,
        string $startDate,
        ?string $dueDate = null
    ): array {
        return [
            ['role' => 'assistant', 'content' => 'You assign a CRM specialist to an order.'],
            ['role' => 'user', 'content' => "Assign executor {$executorId} to order {$orderId} starting at {$startDate}, deadline " . ($dueDate ?? 'not specified') . ". Use assign_executor_to_order and report the result."]
        ];
    }

    #[McpPrompt(name: 'create_complete_service_booking')]
    public function createCompleteServiceBooking(
        string $customerName,
        string $customerPhone,
        int $serviceId,
        int $executorId,
        string $scheduledStartAt,
        ?int $channelId = null,
        ?int $campaignId = null,
        float $discountAmount = 0.0
    ): array {
        return [
            ['role' => 'assistant', 'content' => 'You complete a CRM service booking atomically. Confirm the service and availability before creating the booking.'],
            ['role' => 'user', 'content' => "Create a complete service booking for {$customerName}, phone {$customerPhone}, service {$serviceId}, executor {$executorId}, scheduled at {$scheduledStartAt}. ChannelId=" . ($channelId ?? 'none') . ", campaignId=" . ($campaignId ?? 'none') . ", discount={$discountAmount}. Use create_complete_service_booking and report customer, order, and assignment results."]
        ];
    }

    #[McpPrompt(name: 'search_workspace_info')]
    public function searchWorkspaceInfo(string $query): array
    {
        return [
            ['role' => 'assistant', 'content' => 'You answer business questions using the workspace knowledge base. Do not invent prices, services, policies, or availability.'],
            ['role' => 'user', 'content' => "Search workspace knowledge for: {$query}. Use search_workspace_info and answer only from the returned information."]
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