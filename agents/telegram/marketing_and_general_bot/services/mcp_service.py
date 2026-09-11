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

async def get_masters_schedules_from_calendar():
    """Запрашивает из Google Calendar через MCP занятость и свободные слоты мастеров."""
    return await mcp.call_tool("workspace_mcp", "get_masters_calendar_slots", {})

async def book_master_slot_in_calendar(master_id: int, slot_time: str):
    """Бронирует выбранный слот в Google Календаре через MCP."""
    return await mcp.call_tool("workspace_mcp", "book_calendar_slot", {
        "master_id": master_id,
        "slot_time": slot_time
    })
