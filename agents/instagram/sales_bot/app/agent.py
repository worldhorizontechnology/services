import os
from typing import TypedDict, Annotated
from langgraph.graph import StateGraph, START, END
from langgraph.graph.message import add_messages
from langgraph.prebuilt import ToolNode
from langchain_google_genai import ChatGoogleGenerativeAI
from langchain_core.messages import BaseMessage, SystemMessage
from app.mcp_connector import ExternalMCPClient

class AgentState(TypedDict):
    messages: Annotated[list[BaseMessage], add_messages]
    igsid: str
    interaction_type: str
    ad_id: str
    profile_data: dict

async def build_agent_graph():
    """Builds and compiles the LangGraph multi-agent orchestrator."""
    
    mcp_client = ExternalMCPClient()
    tools = await mcp_client.fetch_langchain_tools()
    
    # Initialize Gemini model with active MCP tools
    llm = ChatGoogleGenerativeAI(
        model="gemini-1.5-flash", 
        temperature=0.2,
        google_api_key=os.getenv("GEMINI_API_KEY")
    )
    llm_with_tools = llm.bind_tools(tools)
    
    async def assistant_node(state: AgentState):
        """Processes user dialogue and triggers MCP actions as needed."""
        sys_instruction = (
            "You are an AI assistant for an Instagram business account.\n"
            "You have access to external tools provided by a Symfony MCP microservice:\n"
            "1. Knowledge Base ('search_workspace_info'): Search prices, procedures, and salon policies.\n"
            "2. Calendar ('check_calendar_slots', 'book_calendar_slot'): Verify specialist availability and book slots.\n"
            "3. CRM Transactions:\n"
            "   - 'create_complete_service_booking': Perform atomic full booking (Customer -> Order -> Assignment).\n"
            "   - Entity-specific tools: 'create_customer', 'create_channel', 'create_campaign', 'assign_executor_to_order'.\n\n"
            "Client Metadata Context:\n"
            f"- IGSID: {state['igsid']}\n"
            f"- Interaction Type: {state['interaction_type']}\n"
            f"- Ad Campaign ID: {state['ad_id']}\n\n"
            "Always consult 'search_workspace_info' for accurate pricing before confirming services."
        )
        sys_msg = SystemMessage(content=sys_instruction)
        
        response = await llm_with_tools.ainvoke([sys_msg] + state["messages"])
        return {"messages": [response]}

    # LangGraph construction
    workflow = StateGraph(AgentState)
    workflow.add_node("assistant", assistant_node)
    workflow.add_node("tools", ToolNode(tools))
    
    workflow.add_edge(START, "assistant")
    
    def route_to_tools(state: AgentState):
        last_message = state["messages"][-1]
        if hasattr(last_message, "tool_calls") and last_message.tool_calls:
            return "tools"
        return END

    workflow.add_conditional_edges("assistant", route_to_tools)
    workflow.add_edge("tools", "assistant")
    
    return workflow.compile()
