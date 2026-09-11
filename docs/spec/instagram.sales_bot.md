# Specification: Instagram LLM Sales Bot Agent Microservice

## 1. Project Overview & Architectural Scope

This specification defines the internal architecture, file layout, state management, execution workflows, and interaction models strictly for the **Instagram LLM Bot Microservice** (`ig_bot_microservice`).

The Agent acts as an autonomous, event-driven orchestrator built on **FastAPI**, **LangGraph**, and **Google Gemini** (`gemini-1.5-flash`), consuming tools exposed by an external Model Context Protocol (MCP) Server via SSE.

---

## 2. Project Structure & File Layout

```text
/ig_bot_microservice
├── Dockerfile                  # Container image definition (Python 3.11-slim)
├── docker-compose.yml          # Container orchestration and environment mapping
├── requirements.txt            # Python dependencies (FastAPI, LangGraph, MCP, etc.)
└── app/
    ├── __init__.py             # Module initialization
    ├── main.py                 # FastAPI application, webhook handlers & Meta API client
    ├── agent.py                # LangGraph state machine & Gemini LLM orchestration
    └── mcp_connector.py        # SSE client & dynamic LangChain tool generator

```

---

## 3. Environment Variables & Runtime Configuration

| Variable Name | Type | Description | Example / Default |
| --- | --- | --- | --- |
| `GEMINI_API_KEY` | String (Secret) | Authentication key for Google Gemini model API calls (`gemini-1.5-flash`). | `AIzaSy...` |
| `META_VERIFY_TOKEN` | String (Secret) | Custom challenge verification token configured in the Meta Developer Portal. | `my_secure_token_123` |
| `META_PAGE_ACCESS_TOKEN` | String (Secret) | Long-lived page token for dispatching messages via Meta Graph API v18.0. | `EAAX...` |
| `MCP_SERVER_SSE_URL` | String (URL) | Full SSE endpoint URL of the external Symfony MCP Server microservice. | `http://mcp-server:8000/mcp` |

---

## 4. Internal Component Architecture

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

## 5. Agent Use-Case Model

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

## 6. End-to-End Agent Sequence Diagram

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

## 7. LangGraph State Machine & Control Flow

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

## 8. Agent State Model (`AgentState` Schema)

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

## 9. Component & Module Breakdown

### 9.1 Webhook Transport & Messaging (`app/main.py`)

* **Endpoint `GET /webhook`:**
* Verifies incoming challenge requests from Meta.
* Validates `hub.mode == 'subscribe'` and `hub.verify_token == META_VERIFY_TOKEN`.
* Returns `hub.challenge` as an integer on success, or raises `HTTPException(403)` on failure.


* **Endpoint `POST /webhook`:**
* Ingests JSON webhooks from Instagram Direct messaging.
* Filters out system noise, empty sender IDs, and echo messages (`is_echo == True`).
* Extracts referral metadata (`ad_id`, `interaction_type`) from payload postbacks or referral objects.
* **Non-blocking Execution:** Offloads heavy LLM and MCP tool execution to FastAPI's `BackgroundTasks` to respond with `200 OK` within Meta’s required 5-second timeout window.


* **Outbound Client (`send_instagram_reply`):**
* Dispatches final text responses asynchronously using `httpx.AsyncClient` to `[https://graph.facebook.com/v18.0/me/messages](https://graph.facebook.com/v18.0/me/messages)`.



### 9.2 LangGraph Orchestrator (`app/agent.py`)

* **State Schema (`AgentState`):**
* `messages`: Appending message history managed by `add_messages`.
* `igsid`: Target Instagram Scoped User ID.
* `interaction_type`: Entry vector (`direct_message`, `ad_referral`, etc.).
* `ad_id`: Linked Meta Ad Campaign ID (or `"none"`).
* `profile_data`: Extracted user profile context.


* **Assistant Node (`assistant_node`):**
* Injects a dynamic system prompt containing the client's context (`IGSID`, `ad_id`, `interaction_type`).
* Binds dynamically loaded tools (`llm.bind_tools(tools)`) to `ChatGoogleGenerativeAI`.
* Invokes Gemini with low temperature (`0.2`) to ensure deterministic tool arguments.


* **Conditional Routing (`route_to_tools`):**
* Evaluates the last `AIMessage` in the state.
* If `tool_calls` exist, routes execution to `ToolNode`.
* If no `tool_calls` are present, terminates execution (`END`) and hands off the message to the dispatch layer.



### 9.3 MCP SSE Connector (`app/mcp_connector.py`)

* **Transport Session (`ExternalMCPClient.connect`):**
* Establishes a persistent SSE stream using `mcp.client.sse.sse_client` and `ClientSession`.


* Performs the standard MCP JSON-RPC protocol initialization handshake (`session.initialize()`).




* **Dynamic Tool Factory (`fetch_langchain_tools`):**
* Fetches available tool definitions from the external Symfony MCP Server via `session.list_tools()`.


* Maps MCP parameter schemas directly into native LangChain `StructuredTool` instances.
* Wraps execution inside an async closure that executes `session.call_tool()` over SSE streams and returns the text result.





---

## 10. Execution Lifecycle & Error Handling Strategy

1. **Meta Webhook Ingestion:** Fast 200 OK acknowledgement prevents duplicate webhook retries from Meta.
2. **MCP Discovery Failure:** If the Symfony MCP Server is unreachable, `ExternalMCPClient` catches connection errors, falling back gracefully to pure text generation without tool bindings.
3. **Tool Execution Error:** If an external MCP tool execution fails (e.g., CRM offline, Google Calendar rate limited), the error string is formatted into a `ToolMessage` and returned to Gemini so the model can inform the user or attempt an alternative action.
4. **State Isolation:** Every user message generates an isolated `AgentState` execution graph, preventing cross-talk between concurrent Instagram Direct conversations.
