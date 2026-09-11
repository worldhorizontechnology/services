<?php

namespace App\Tools;

use App\Service\CrmIntegrationService;
use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;

class WorkspaceTools
{
    public function __construct(
        private CrmIntegrationService $crmService
    ) {}

    /**
     * Searches company info, service prices, and workspace guidelines.
     */
    #[McpTool(name: 'search_workspace_info')]
    public function searchWorkspaceInfo(
        #[Schema(description: 'Search query regarding prices, services, or salon policies')]
        string $query
    ): string {
        return $this->crmService->getWorkspace($query);
    }
}
