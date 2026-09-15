import os
from typing import Any, Dict, List

import httpx
from langchain_core.tools import StructuredTool
from pydantic import create_model, Field

MCP_SERVER_URL = os.getenv("MCP_SERVER_URL", "http://host.docker.internal:8788/")

class ExternalMCPClient:
    """Calls the Symfony MCP server through Streamable HTTP JSON-RPC."""

    async def _request(
        self,
        client: httpx.AsyncClient,
        request_id: int,
        method: str,
        params: Dict[str, Any] | None = None,
        session_id: str | None = None,
    ) -> tuple[dict[str, Any], str | None]:
        headers = {
            "Content-Type": "application/json",
            "Accept": "application/json, text/event-stream",
        }
        if session_id:
            headers["Mcp-Session-Id"] = session_id

        response = await client.post(
            MCP_SERVER_URL,
            headers=headers,
            json={"jsonrpc": "2.0", "id": request_id, "method": method, "params": params or {}},
        )
        response.raise_for_status()
        return response.json(), response.headers.get("Mcp-Session-Id", session_id)

    async def _open_session(self, client: httpx.AsyncClient) -> str | None:
        result, session_id = await self._request(
            client,
            1,
            "initialize",
            {
                "protocolVersion": "2024-11-05",
                "capabilities": {},
                "clientInfo": {"name": "instagram-sales-agent", "version": "1.0.0"},
            },
        )
        if "error" in result:
            raise RuntimeError(f"MCP initialize failed: {result['error']}")

        return session_id

    async def _list_tools(self) -> List[dict[str, Any]]:
        async with httpx.AsyncClient(timeout=30.0) as client:
            session_id = await self._open_session(client)
            result, _ = await self._request(client, 2, "tools/list", session_id=session_id)
            if "error" in result:
                raise RuntimeError(f"MCP tools/list failed: {result['error']}")
            return result["result"]["tools"]

    async def _call_tool(self, name: str, arguments: Dict[str, Any]) -> str:
        async with httpx.AsyncClient(timeout=60.0) as client:
            session_id = await self._open_session(client)
            result, _ = await self._request(
                client,
                2,
                "tools/call",
                {"name": name, "arguments": arguments},
                session_id,
            )
            if "error" in result:
                raise RuntimeError(f"MCP tool {name} failed: {result['error']}")

            content = result.get("result", {}).get("content", [])
            return "\n".join(item.get("text", "") for item in content if item.get("type") == "text")

    @staticmethod
    def _args_schema(input_schema: dict[str, Any]) -> type:
        properties = input_schema.get("properties", {})
        required = set(input_schema.get("required", []))
        fields: dict[str, tuple[Any, Any]] = {}

        for name, schema in properties.items():
            schema_type = schema.get("type", "string")
            if isinstance(schema_type, list):
                schema_type = next((item for item in schema_type if item != "null"), "string")
            python_type = {"integer": int, "number": float, "boolean": bool, "array": list, "object": dict}.get(schema_type, str)
            if name not in required:
                python_type = python_type | None
            default = ... if name in required else schema.get("default", None)
            fields[name] = (python_type, Field(default, description=schema.get("description")))

        return create_model("MCPToolArguments", **fields)

    async def fetch_langchain_tools(self) -> List[StructuredTool]:
        tools = await self._list_tools()
        langchain_tools = []

        for tool in tools:
            tool_name = tool["name"]
            tool_description = tool.get("description", "MCP tool")
            args_schema = self._args_schema(tool.get("inputSchema", {}))

            async def call_tool(name: str = tool_name, **arguments: Any) -> str:
                return await self._call_tool(name, arguments)

            langchain_tools.append(StructuredTool.from_function(
                coroutine=call_tool,
                name=tool_name,
                description=tool_description,
                args_schema=args_schema,
            ))

        return langchain_tools
