<?php

namespace App\Controller;

use Mcp\Server;
use Mcp\Server\Transport\StreamableHttpTransport;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class McpController extends AbstractController
{

  public function __construct(
    private CrmTools $crmTools,
    private WorkspaceTools $workspaceTools,
    private CalendarTools $calendarTools
) {}
  
    #[Route('/mcp', name: 'mcp_endpoint', methods: ['GET', 'POST'])]
    public function handleMcp(Request $request): Response
    {
        // 1. Build MCP Server with automatic attribute discovery
       $server = Server::builder()
    ->setServerInfo('Symfony B2C CRM MCP Server', '1.0.0')
    // Customer, Channel, Campaign & Assignment tools
    ->addTool([$this->crmTools, 'createCustomer'], 'create_customer')
    ->addTool([$this->crmTools, 'createChannel'], 'create_channel')
    ->addTool([$this->crmTools, 'createCampaign'], 'create_campaign')
    ->addTool([$this->crmTools, 'assignExecutor'], 'assign_executor_to_order')
    ->addTool([$this->crmTools, 'createCompleteBooking'], 'create_complete_service_booking')
    // Search Knowledge Base
    ->addTool([$this->workspaceTools, 'searchWorkspaceInfo'], 'search_workspace_info')
    // Google Calendar tools
    ->addTool([$this->calendarTools, 'checkCalendarSlots'], 'check_calendar_slots')
    ->addTool([$this->calendarTools, 'bookCalendarSlot'], 'book_calendar_slot')
    ->build();

        // 2. Convert Symfony Request to PSR-7 Request required by MCP SDK
        $psr17Factory = new Psr17Factory();
        $psrHttpFactory = new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
        $psrRequest = $psrHttpFactory->createRequest($request);

        // 3. Initialize HTTP Streamable Transport
        $transport = new StreamableHttpTransport(
            $psrRequest,
            $psr17Factory, // Response Factory
            $psr17Factory  // Stream Factory
        );

        // 4. Run MCP Server and handle execution
        $psrResponse = $server->run($transport);

        // 5. Convert PSR-7 Response back to Symfony Response
        $symfonyResponse = new Response(
            (string) $psrResponse->getBody(),
            $psrResponse->getStatusCode(),
            $psrResponse->getHeaders()
        );

        return $symfonyResponse;
    }
}
