<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class CrmIntegrationService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        #[Autowire(env: 'LARAVEL_CRM_URL')] private string $crmUrl,
        #[Autowire(env: 'LARAVEL_API_TOKEN')] private string $apiToken,
    ) {}

    public function createCustomer(array $data): string
    {
        return $this->post('/customers', $data);
    }

    public function createChannel(array $data): string
    {
        return $this->post('/channels', $data);
    }

    public function createCampaign(array $data): string
    {
        return $this->post('/campaigns', $data);
    }

    public function createAssignment(array $data): string
    {
        return $this->post('/assignments', $data);
    }

    public function executeCompleteBookingTransaction(array $payload): string
    {
        // Calls single Laravel endpoint that handles DB transaction across all tables
        return $this->post('/bookings/complete-transaction', $payload);
    }

    private function post(string $endpoint, array $data): string
    {
        $response = $this->httpClient->request('POST', "{$this->crmUrl}{$endpoint}", [
            'headers' => [
                'Authorization' => "Bearer {$this->apiToken}",
                'Accept' => 'application/json',
            ],
            'json' => $data,
        ]);

        if (in_array($response->getStatusCode(), [200, 201])) {
            return json_encode($response->toArray());
        }

        return "API Error [{$response->getStatusCode()}]: " . $response->getContent(false);
    }
}
