# Specification: Instagram LLM Sales Bot Agent Microservice

## 1. Microservice Architectural Scope

This specification defines the internal architecture, state management, execution workflows, and interaction models strictly for the **Instagram LLM Bot Microservice** (`ig_bot_microservice`).

The Agent acts as an autonomous, event-driven orchestrator built on **FastAPI**, **LangGraph**, and **Google Gemini** (`gemini-1.5-flash`), consuming tools exposed by an external Model Context Protocol (MCP) Server via SSE.

---

## 2. Internal Component Architecture

```mermaid
graph TB
    subgraph Web_Transport["FastAPI Application Layer (app/main.py)"]
        Webhook["/webhook Endpoint"]
        BGManager["BackgroundTasks Manager"]
        IGSender["send_instagram_reply()"]
    end

    subgraph LangGraph_Engine["LangGraph Orchestrator (app/agent.py)"]
        StateGraph["StateGraph (AgentState)"]
        AssistantNode["assistant_node"]
        ToolNode["tools (ToolNode)"]
        GeminiLLM["ChatGoogleGenerativeAI (Gemini 1.5 Flash)"]
    end

    subgraph MCP_Integration["MCP Connector Layer (app/mcp_connector.py)"]
        Client["ExternalMCPClient"]
        SSEStream["mcp.client.sse (sse_client)"]
        ToolWrapper["LangChain StructuredTools Factory"]
    end

    Webhook -->|1. Ingest Message| BGManager
    BGManager -->|2. Trigger Async Execution| StateGraph
    StateGraph -->|3. Fetch External Tools| Client
    Client <-->|4. SSE Handshake / Tool Discovery| SSEStream
    Client -->|5. Wrap Schemas| ToolWrapper
    ToolWrapper -->|6. Bind Tools| GeminiLLM
    
    StateGraph -->|7. Invoke Prompt + History| AssistantNode
    AssistantNode <-->|8. Inference| GeminiLLM
    StateGraph -->|9. Delegate Tool Call| ToolNode
    ToolNode -->|10. Execute Tool via SSE| Client
    
    StateGraph -->|11. Return Final Text| IGSender

```

---

## 3. Agent Use-Case Model

```mermaid
graph LR
    actor_meta["Meta Graph API"]
    actor_gemini["Google Gemini API"]
    actor_mcp["External MCP Server"]

    subgraph Agent_Boundary["Instagram Bot Agent Microservice"]
        uc_verify(["Verify Webhook Challenge"])
        uc_receive(["Receive Webhook Event"])
        uc_fetch_tools(["Discover Remote MCP Tools"])
        uc_run_agent(["Execute LangGraph State Graph"])
        uc_invoke_llm(["Infer Intent & Select Tools"])
        uc_exec_mcp(["Call External MCP Tool"])
        uc_reply(["Dispatch Direct Response"])
    end

    actor_meta --> uc_verify
    actor_meta --> uc_receive
    uc_receive --> uc_fetch_tools
    uc_fetch_tools --> actor_mcp
    uc_receive --> uc_run_agent
    uc_run_agent --> uc_invoke_llm
    uc_invoke_llm --> actor_gemini
    uc_run_agent --> uc_exec_mcp
    uc_exec_mcp --> actor_mcp
    uc_run_agent --> uc_reply
    uc_reply --> actor_meta

```

---

## 4. End-to-End Agent Sequence Diagram

```mermaid
sequenceDiagram
    autonumber
    actor Meta as Meta Graph API
    participant API as FastAPI (main.py)
    participant BG as Background Task
    participant Conn as MCP Connector (mcp_connector.py)
    participant Graph as LangGraph Orchestrator (agent.py)
    participant Gemini as ChatGoogleGenerativeAI
    participant MCP as External MCP Server

    Meta->>API: POST /webhook (Payload: IGSID, Text, Referral Metadata)
    API-->>Meta: 200 OK (Immediate HTTP response to prevent Webhook timeout)
    API->>BG: add_task(execute_agent_workflow, sender_id, text, metadata)

    BG->>Conn: fetch_langchain_tools()
    Conn->>MCP: sse_client(URL) & session.initialize()[cite: 1]
    MCP-->>Conn: session.list_tools() response[cite: 1]
    Conn-->>BG: Wrapped List[StructuredTool]

    BG->>Graph: build_agent_graph() & graph.ainvoke(AgentState)
    
    rect rgb(240, 248, 255)
        note over Graph, Gemini: Loop Iteration 1: Inference & Tool Choice
        Graph->>Gemini: ainvoke([SystemMessage + HumanMessage])
        Gemini-->>Graph: AIMessage (content="", tool_calls=[{name, args}])
    end

    rect rgb(255, 245, 238)
        note over Graph, MCP: Loop Iteration 2: Remote Execution via MCP Client
        Graph->>Conn: Execute Tool via call_tool(name, args)
        Conn->>MCP: session.call_tool(name, arguments=kwargs)[cite: 1]
        MCP-->>Conn: Return result.content[0].text[cite: 1]
        Conn-->>Graph: ToolMessage(content=result_text)
    end

    rect rgb(240, 248, 255)
        note over Graph, Gemini: Loop Iteration 3: Final Response Generation
        Graph->>Gemini: ainvoke([SystemMessage, HumanMessage, AIMessage, ToolMessage])
        Gemini-->>Graph: AIMessage (content="Your booking is confirmed...")
    end

    Graph-->>BG: Return final state messages
    BG->>API: send_instagram_reply(igsid, text)
    API->>Meta: POST /v18.0/me/messages (Payload)
    Meta-->>API: 200 OK

```

---

## 5. LangGraph State Machine & Control Flow

```mermaid
stateDiagram-v2
    [*] --> IngestWebhook: Incoming Meta Webhook Event

    state IngestWebhook {
        [*] --> ParseMetadata: Extract IGSID, ad_id & interaction_type
        ParseMetadata --> BuildState: Construct initial AgentState
    }

    IngestWebhook --> FetchMCPTools: Hand off to Background Task

    state FetchMCPTools {
        [*] --> ConnectSSE: ExternalMCPClient.connect()[cite: 1]
        ConnectSSE --> ListTools: session.list_tools()[cite: 1]
        ListTools --> BuildLangChainTools: Factory generates StructuredTools
    }

    FetchMCPTools --> AssistantNode: Start Graph Execution

    state AssistantNode {
        [*] --> BuildSystemInstruction: Inject Metadata & Persona Instructions
        BuildSystemInstruction --> CallGemini: LLM.bind_tools(tools).ainvoke()
    }

    AssistantNode --> RouteDecision: Check AIMessage.tool_calls

    state RouteDecision <<choice>>
    RouteDecision --> ToolsNode: tool_calls is NOT empty
    RouteDecision --> EndWorkflow: tool_calls is empty

    state ToolsNode {
        [*] --> ExecuteMCPCall: Exec session.call_tool() via SSE stream[cite: 1]
        ExecuteMCPCall --> AppendToolMessage: Add ToolMessage to AgentState
    }

    ToolsNode --> AssistantNode: Loop back with updated state

    state EndWorkflow {
        [*] --> ExtractFinalText: Extract content from last AIMessage
        ExtractFinalText --> DispatchReply: Call send_instagram_reply()
    }

    EndWorkflow --> [*]

```

---

## 6. Agent State Model (`AgentState` Schema)

```mermaid
classDiagram
    class AgentState {
        +Annotated~List~BaseMessage~~ messages
        +String igsid
        +String interaction_type
        +String ad_id
        +Dict profile_data
    }

    class BaseMessage {
        <<interface>>
        +String content
    }

    class SystemMessage {
        +String content
    }

    class HumanMessage {
        +String content
    }

    class AIMessage {
        +String content
        +List~Dict~ tool_calls
    }

    class ToolMessage {
        +String content
        +String tool_call_id
    }

    AgentState *-- BaseMessage : contains
    BaseMessage <|-- SystemMessage
    BaseMessage <|-- HumanMessage
    BaseMessage <|-- AIMessage
    BaseMessage <|-- ToolMessage

```

---

## 7. Component Responsibilities & Boundaries

* **`app/main.py`**: Webhook endpoint handler (`/webhook`). Validates tokens with Meta, ingests user payloads, returns HTTP 200 OK immediately, and offloads processing to `BackgroundTasks` to prevent Meta webhook timeouts.
* **`app/agent.py`**: Compiles the `LangGraph` StateGraph workflow. Injects system instructions with runtime metadata (`igsid`, `ad_id`, `interaction_type`), handles non-blocking LLM calls with Gemini, and manages state transition loops.
* **`app/mcp_connector.py`**: Connects via SSE to the external MCP Server. Converts raw JSON-RPC tool schemas into native LangChain `StructuredTool` instances, maintaining dynamic parameter schema compatibility for LLM tool calling.
