import os
from typing import TypedDict, Annotated
from dotenv import load_dotenv
from langgraph.graph import StateGraph, START, END
from langgraph.graph.message import add_messages
from langgraph.prebuilt import ToolNode
from langchain_google_genai import ChatGoogleGenerativeAI
from langchain_core.messages import BaseMessage, HumanMessage, SystemMessage

load_dotenv()

from app.mcp_connector import ExternalMCPClient
from app.rag import WorkspaceRAG

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
    rag = WorkspaceRAG(mcp_client)
    
    # Initialize Gemini model with active MCP tools
    llm = ChatGoogleGenerativeAI(
        model="gemini-1.5-flash", 
        temperature=0.2,
        google_api_key=os.getenv("GEMINI_API_KEY")
    )
    llm_with_tools = llm.bind_tools(tools)
    
    async def assistant_node(state: AgentState):
        """Processes user dialogue and triggers MCP actions as needed."""
        user_query = next(
            (
                message.content
                for message in reversed(state["messages"])
                if isinstance(message, HumanMessage) and isinstance(message.content, str)
            ),
            "",
        )
        workspace_context = await rag.retrieve(user_query)
        sys_instruction = (
            "You are the sales assistant for an Instagram business account.\n"
            "Use the retrieved workspace context as the only source of truth for services, prices, descriptions, promotions, discounts, company information, procedures, and policies.\n"
            "Never invent missing facts, prices, IDs, availability, or discounts. If the context is insufficient, ask a concise clarification question.\n\n"
            "AVAILABLE ACTIONS:\n"
            "- find_masters_for_service: after the client selects a service, resolve active masters and Google Calendar IDs.\n"
            "- check_calendar_slots: check availability using the selected service/master context.\n"
            "- book_calendar_slots: book only a slot confirmed as available.\n"
            "- create_customer: create or update the client in CRM.\n"
            "- create_order: create an order for an existing customer and service.\n"
            "- create_complete_service_booking: atomically create customer, order, service line, and assignment.\n"
            "- create_channel, create_campaign, assign_executor_to_order: use only when explicitly required.\n\n"
            "Client Metadata Context:\n"
            f"- IGSID: {state['igsid']}\n"
            f"- Interaction Type: {state['interaction_type']}\n"
            f"- Ad Campaign ID: {state['ad_id']}\n\n"
            "WORKFLOW:\n"
            "1. Use the retrieved workspace context to clarify the client's requested service.\n"
            "2. Confirm service details, price, promotion, and missing customer information.\n"
            "3. Do not guess service IDs. If there is no reliable service ID, ask for clarification.\n"
            "4. After the client confirms the service, call find_masters_for_service.\n"
            "5. Use the returned master_id and calendar_id to call check_calendar_slots.\n"
            "6. Ask the client to choose a returned slot when needed.\n"
            "7. Call book_calendar_slots only after availability is confirmed and the client agrees.\n"
            "8. After calendar booking, create the CRM customer/order or use create_complete_service_booking.\n"
            "9. Tell the client only what was confirmed by tool results.\n\n"
            f"WORKSPACE RAG CONTEXT:\n{workspace_context}"
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
