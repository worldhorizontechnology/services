import os
from typing import List, Any
from contextlib import asynccontextmanager
from mcp.client.sse import sse_client
from mcp.client.session import ClientSession
from langchain_core.tools import StructuredTool
from pydantic import create_model

MCP_SERVER_SSE_URL = os.getenv("MCP_SERVER_SSE_URL", "http://symfony-mcp-server:8000/mcp")

class ExternalMCPClient:
    """Handles async SSE client sessions with the Symfony MCP server."""

    @asynccontextmanager
    async def connect(self):
        """Yields an active MCP ClientSession connected via Streamable HTTP/SSE."""
        async with sse_client(MCP_SERVER_SSE_URL) as streams:
            async with ClientSession(streams[0], streams[1]) as session:
                await session.initialize()
                yield session

    async def fetch_langchain_tools(self) -> List[StructuredTool]:
        """
        Fetches tool definitions (CrmTools, WorkspaceTools, CalendarTools) 
        from the Symfony MCP server and wraps them into LangChain StructuredTools.
        """
        async with self.connect() as session:
            mcp_tools_response = await session.list_tools()
            lc_tools = []

            for mcp_tool in mcp_tools_response.tools:
                tool_name = mcp_tool.name
                tool_description = mcp_tool.description or "MCP Tool"

                # Define standard async caller for the target tool
                async def _make_call(name=tool_name, **kwargs):
                    async with self.connect() as exec_session:
                        result = await exec_session.call_tool(name, arguments=kwargs)
                        if result.content and len(result.content) > 0:
                            return result.content[0].text
                        return "Execution finished with no text output."

                # Create StructuredTool for precise JSON schema parsing by Gemini
                lc_tool = StructuredTool.from_function(
                    coroutine=_make_call,
                    name=tool_name,
                    description=tool_description,
                )
                lc_tools.append(lc_tool)

            return lc_tools
