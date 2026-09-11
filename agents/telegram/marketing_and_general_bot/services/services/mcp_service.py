from mcp_client import AsyncMCPClient

mcp = AsyncMCPClient(endpoint="http://localhost:8080/mcp")

async def get_laravel_snapshot():
    return await mcp.call_tool("laravel_mcp", "get_latest_analytics", {
        "model": "MarketingAnalyticsSnapshot"
    })

async def get_workspace_docs():
    return await mcp.call_tool("workspace_mcp", "fetch_folder_docs", {
        "folder_id": "YOUR_FOLDER_ID"
    })
