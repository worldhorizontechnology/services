<?php

namespace App\Tools;

use App\Service\CrmIntegrationService;
use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;

class CrmTools
{
    public function __construct(
        private CrmIntegrationService $crmService
    ) {}

    /**
     * 1. CUSTOMER MODULE
     * Directly creates or updates a record in the 'customers' table.
     */
    #[McpTool(name: 'create_customer')]
    public function createCustomer(
        #[Schema(description: 'Client full name or Instagram handle')]
        string $name,

        #[Schema(description: 'Primary phone number (indexed for search)')]
        string $phone,

        #[Schema(description: 'Client email address')]
        ?string $email = null,

        #[Schema(description: 'FK -> channels.id: Initial traffic channel')]
        ?int $channelId = null,

        #[Schema(description: 'FK -> campaigns.id: First contact campaign')]
        ?int $campaignId = null,

        #[Schema(description: 'First contact method: direct_message, call, promo, office')]
        string $entryPoint = 'direct_message'
    ): string {
        return $this->crmService->createCustomer([
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'channel_id' => $channelId,
            'campaign_id' => $campaignId,
            'entry_point' => $entryPoint,
        ]);
    }

    /**
     * 2. MARKETING & ACQUISITION (CHANNELS & CAMPAIGNS)
     * Creates acquisition source in 'channels' table.
     */
    #[McpTool(name: 'create_channel')]
    public function createChannel(
        #[Schema(description: 'Channel name e.g. "Instagram Direct Bot", "Google Ads"')]
        string $name,

        #[Schema(description: 'Channel type: online or offline')]
        string $type = 'online'
    ): string {
        return $this->crmService->createChannel([
            'name' => $name,
            'type' => $type,
        ]);
    }

    /**
     * Creates ad campaign in 'campaigns' table tied to a channel.
     */
    #[McpTool(name: 'create_campaign')]
    public function createCampaign(
        #[Schema(description: 'FK -> channels.id')]
        int $channelId,

        #[Schema(description: 'Campaign name e.g. "Summer Sale 2026"')]
        string $name,

        #[Schema(description: 'Unique promotional code if applicable')]
        ?string $promoCode = null,

        #[Schema(description: 'Marketing budget for ROI calculation')]
        float $budget = 0.00
    ): string {
        return $this->crmService->createCampaign([
            'channel_id' => $channelId,
            'name' => $name,
            'promo_code' => $promoCode,
            'budget' => $budget,
        ]);
    }

    /**
     * 3. FULFILLMENT MODULE (ASSIGNMENTS)
     * Assigns a master/employee (users.id) to execute an order (orders.id) in 'assignments' table.
     */
    #[McpTool(name: 'assign_executor_to_order')]
    public function assignExecutor(
        #[Schema(description: 'FK -> orders.id')]
        int $orderId,

        #[Schema(description: 'FK -> users.id (employee/master with role executor)')]
        int $executorId,

        #[Schema(description: 'Scheduled start time in YYYY-MM-DD HH:MM:SS format')]
        string $startDate,

        #[Schema(description: 'Fulfillment deadline in YYYY-MM-DD HH:MM:SS format')]
        ?string $dueDate = null
    ): string {
        return $this->crmService->createAssignment([
            'order_id' => $orderId,
            'executor_id' => $executorId,
            'start_date' => $startDate,
            'due_date' => $dueDate,
            'status' => 'pending',
        ]);
    }

    /**
     * 4. END-TO-END ORCHESTRATION TOOL
     * Atomically executes full chain: Customer resolution -> Order -> Order_Service -> Assignment.
     */
    #[McpTool(name: 'create_complete_service_booking')]
    public function createCompleteBooking(
        #[Schema(description: 'Client full name')]
        string $customerName,

        #[Schema(description: 'Client phone number')]
        string $customerPhone,

        #[Schema(description: 'FK -> services.id: Service being ordered')]
        int $serviceId,

        #[Schema(description: 'FK -> users.id: Assigned specialist/master')]
        int $executorId,

        #[Schema(description: 'Execution datetime (YYYY-MM-DD HH:MM:SS)')]
        string $scheduledStartAt,

        #[Schema(description: 'Optional FK -> channels.id')]
        ?int $channelId = null,

        #[Schema(description: 'Optional FK -> campaigns.id')]
        ?int $campaignId = null,

        #[Schema(description: 'Discount amount in currency unit')]
        float $discountAmount = 0.00
    ): string {
        return $this->crmService->executeCompleteBookingTransaction([
            'customer' => [
                'name' => $customerName,
                'phone' => $customerPhone,
                'channel_id' => $channelId,
                'campaign_id' => $campaignId,
                'entry_point' => 'instagram_bot',
            ],
            'order' => [
                'campaign_id' => $campaignId,
                'discount_amount' => $discountAmount,
                'status' => 'new',
                'payment_status' => 'unpaid',
            ],
            'service_id' => $serviceId,
            'assignment' => [
                'executor_id' => $executorId,
                'start_date' => $scheduledStartAt,
                'status' => 'pending',
            ]
        ]);
    }
}
