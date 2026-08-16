# UML / ARCHITECTURE DIAGRAMS

### 1. Component diagram

```mermaid
graph TB
    %% Actors / Clients
    Customer((Customer))
    Manager((Manager))
    Landings["Vue Landing Pages"]

    %% External Platforms
    WA_Cloud["WhatsApp Business API"]
    TG_Service["Telegram API"]
    G_Workspace["Google Workspace<br/>(Drive/Docs/Meet/Calendar)"]

    %% Infrastructure Components
    GW["API Gateway<br/>(Reverse Proxy / Auth)"]

    %% AI Layer (GCP Cloud Run)
    subgraph AI_Layer["AI Layer (Cloud Run)"]
        WA_Agent["WhatsApp Sales Agent"]
        TG_Agent["Knowledge Agent"]
        Meet_Agent["Meeting Intelligence Agent"]
        Mkt_AI["Marketing AI"]
        RAG_Layer["RAG Agent / Layer"]
    end

    %% Algorithmic Services (GCP Cloud Run)
    subgraph Algorithmic_Services["Algorithmic Services (Cloud Run)"]
        Auth_Svc["Auth Service"]
        CRM_Svc["CRM Service (Laravel)"]
        Booking_Svc["Booking Service"]
        Payment_Svc["Payment Service"]
        Scraper_Svc["Competitor Scraper Service"]
        Mkt_An_Svc["Marketing Analytics Service"]
        An_Svc["Analytics Service"]
        SF_Conn["Snowflake Connector Service"]
        Notify_Svc["Notification Service"]
        Index_Svc["Knowledge / Indexing Service"]
    end

    %% Storage Layer (GCP Managed + Snowflake)
    subgraph Storage_Layer["Storage & Data Layer"]
        PG[("Cloud SQL<br/>(PostgreSQL)")]
        Redis[("Cloud Memorystore<br/>(Redis State / Cache)")]
        VDB[("Vertex AI<br/>Vector Search")]
        SF[("Snowflake<br/>(Analytics Hub)")]
    end

    %% 1. External Ingress & Gateway Communication
    Customer -->|Interacts| Landings
    Landings --> GW
    Manager -->|Admin Access| GW
    GW -->|Validate Tokens| Auth_Svc

    %% 2. Webhooks & External Event Ingress
    WA_Cloud -->|Webhooks| WA_Agent
    TG_Service -->|Webhooks| TG_Agent
    G_Workspace -->|Meet Transcripts / Drive Webhooks| Index_Svc

    %% 3. AI Agents State & Operations
    WA_Agent <-->|Session State| Redis
    TG_Agent <-->|Session State| Redis
    
    %% 4. RAG & Knowledge Update Flows (Two-way communication fixed)
    RAG_Layer <-->|Bi-directional Search| VDB
    WA_Agent <-->|Query / Context Request| RAG_Layer

    %% 5. Knowledge Pipeline Flow
    TG_Agent -->|Extract Unstructured Data| Index_Svc
    Meet_Agent -->|Extract Decisions & Changes| Index_Svc
    Index_Svc -->|Read/Write Auth via GCP Service Acct| G_Workspace
    Index_Svc -->|Incremental Chunking & Embeddings| VDB

    %% 6. Algorithmic Service Routing (API Contracts)
    GW --> CRM_Svc
    WA_Agent -->|Create Lead / Check Status| CRM_Svc
    WA_Agent -->|Request Appointment Slots| Booking_Svc
    WA_Agent -->|Trigger Scenario Payment| Payment_Svc

    %% 7. Operational Storage Relations
    CRM_Svc <--> PG
    Booking_Svc <--> PG
    Payment_Svc <--> PG
    Booking_Svc -->|Sync Free Slots & Appointments| G_Workspace

    %% 8. Analytical Data Pipelines (Asynchronous Pub/Sub)
    CRM_Svc -->|Async Events / Logs| SF_Conn
    Booking_Svc -->|Async Events| SF_Conn
    Payment_Svc -->|Async Events| SF_Conn
    Scraper_Svc -->|Raw Scraped Data| SF_Conn
    SF_Conn -->|Structured Data Ingestion| SF

    %% 9. Marketing AI Feedback Loop
    SF -->|Analytical Data Source| An_Svc
    An_Svc --> Mkt_An_Svc
    Mkt_An_Svc -->|Prepared Aggregated Data| Mkt_AI
    Mkt_AI -->|Submit Option for Approval| Notify_Svc
    Notify_Svc -->|Telegram Approval Message| TG_Service
    TG_Service -->|Manager Approves/Rejects| CRM_Svc
    CRM_Svc -->|Deploy approved promo| Landings
```
### 2. Deployment diagram
```mermaid
graph LR
    classDef github fill:#181717,stroke:#fff,stroke-width:2px,color:#fff;
    classDef gcp fill:#4285F4,stroke:#34A853,stroke-width:2px,color:#fff;
    classDef azure fill:#0089D6,stroke:#007FFF,stroke-width:2px,color:#fff;
    classDef workspace fill:#EA4335,stroke:#FBBC05,stroke-width:2px,color:#fff;
    classDef user fill:#666,stroke:#333,stroke-width:1px,color:#fff;

    subgraph GH [GitHub Infrastructure]
        Pages[GitHub Pages: Vue.js Static Landing]:::github
        Actions[GitHub Actions: CI/CD Engine]:::github
    end

    subgraph GoogleCloud [Google Cloud Run / Compute Layer]
        GW[API Gateway / Node.js]:::gcp
        CRM[Laravel CRM / PHP Octane]:::gcp
        AI[Python AI Agents Layer]:::gcp
        Algo[Algorithmic Services / JS]:::gcp
    end

    subgraph MSAzure [Azure / Free Tier Storage]
        Postgres[(Managed PostgreSQL DB)]:::azure
        VectorDB[(Vector Database / RAG)]:::azure
    end

    subgraph GWorkspace [Google Workspace SaaS]
        Workspace_API["Drive / Docs / Calendar / Meet"]:::workspace
    end

    subgraph Analytics_Hub [Analytics Warehouse]
        Snowflake[(Snowflake Hub)]:::user
    end

    %% Deployment Automation Flow (Добавлен деплой для Algo)
    Actions -->|Deploy Static HTML/JS| Pages
    Actions -->|Build & Push Container| GW
    Actions -->|Build & Push Container| CRM
    Actions -->|Build & Push Container| AI
    Actions -->|Build & Push Container| Algo

    %% Production Runtime Traffic (Разделен трафик браузера и вебхуков мессенджеров)
    User_Browser((Client Browser)):::user -->|HTTPS| Pages
    Webhooks((WhatsApp / Telegram API)):::user -->|HTTPS Webhooks| GW
    Pages -->|REST API Requests| GW
    
    GW -->|Internal REST| AI
    GW -->|Internal REST| CRM
    GW -->|Internal REST| Algo

    %% Cross-Cloud Data Connectivity & Integrations (Добавлен Snowflake и Workspace)
    CRM -->|Cross-Cloud Secure SQL| Postgres
    AI -->|Cross-Cloud Vector Queries / RAG| VectorDB
    Algo -->|Asynchronous Data Sync| Snowflake
    AI & Algo <-->|OAuth2 API Access via GCP Service Accounts| Workspace_API
```

### 3. Use case diagram
```mermaid
usecaseDiagram
    %% --- ACTORS (Strictly from Section 4, 10, 11) ---
    actor Customer as "Customer"
    actor Manager as "Manager"

    %% --- REAL EXTERNAL CHANNELS (Section 4) ---
    actor WhatsAppAPI as "WhatsApp Business Cloud API"
    actor TelegramBotAPI as "Telegram Bot API"

    %% --- PLATFORM BOUNDARY ---
    subgraph Platform["AI Multi-Agent Business Automation Platform"]

        %% 3.1. AI Agents: WhatsApp Sales Agent (Section 4.1, 5.1)
        subgraph WhatsAppSalesAgent["Subsystem: services/sales-agent"]
            usecase UC_Determine_Intent as "Determining customer intent"
            usecase UC_RAG_Search as "RAG search of the corporate knowledge base"
            usecase UC_Collect_Data as "Collecting name, phone number, email, company, product of interest, and comments"
            usecase UC_Call_Booking as "Calling the Booking Service"
            usecase UC_Call_Payment as "Calling the Payment Service"
        end

        %% 3.1. AI Agents: Telegram Knowledge Agent & Google Meet Intelligence Agent (Section 5.2, 5.3, 6)
        subgraph KnowledgeMeetAgents["Subsystem: knowledge-&-rag-services"]
            usecase UC_Highlight_Info as "Highlighting useful business information"
            usecase UC_Extract_Meet as "Extracts decisions, tasks, product changes, customer requirements, and agreements"
            usecase UC_Transfer_KS as "Transferring structured data to the Knowledge Service"
            usecase UC_Incremental_Indexing as "Incremental Indexing (Chunking + Embeddings)"
        end

        %% 3.2. Algorithmic Services (Section 7, 8, 9)
        subgraph AlgorithmicServices["Subsystem: algorithmic-business-services & laravel-crm"]
            usecase UC_Create_Lead as "Creating/updating a lead via the CRM API"
            usecase UC_Booking_Operations as "Getting free slots / Creating an appointment / Syncing with Google Calendar"
            usecase UC_Payment_Operations as "Payment operations"
        end

        %% 3.2. Algorithmic Services: Competitor Scraper Service (Section 10)
        subgraph ScraperService["Subsystem: services/competitor-scraper"]
            usecase UC_Scraper_Ops as "Standard scraping, parsing, company search, and data collection"
        end

        %% 3.1. AI Agents: Marketing AI (Section 3.4, 11)
        subgraph MarketingSubsystem["Subsystem: services/marketing-service & analytics"]
            usecase UC_Gen_Options as "Generates options for promotions, offers, or marketing messages"
            usecase UC_Sub_Telegram as "Submits the proposal to Telegram for approval"
        end
    end

    %% --- ACTOR ASSOCIATIONS (Section 4) ---
    Customer --> UC_Determine_Intent
    Customer --> UC_Call_Booking
    Customer --> UC_Call_Payment

    %% External System Webhook Inbound Lines
    WhatsAppAPI --> UC_Determine_Intent
    TelegramBotAPI --> UC_Highlight_Info

    %% Manager Interactions
    Manager --> UC_Sub_Telegram
    Manager --> UC_Create_Lead : "Manual CRM Operations"

    %% --- INTERNAL CODE EXECUTIONS (Strict UML Stereotypes) ---
    %% Sales Agent Internal Logic Flow (Section 5.1)
    UC_Determine_Intent ..> UC_RAG_Search : <<include>>
    UC_Determine_Intent ..> UC_Collect_Data : <<extend>>

    %% Knowledge Base Pipeline Logic Flow (Section 6)
    UC_Highlight_Info ..> UC_Transfer_KS : <<include>>
    UC_Extract_Meet ..> UC_Transfer_KS : <<include>>
    UC_Transfer_KS ..> UC_Incremental_Indexing : <<include>>

    %% Marketing AI Internal Logic Flow (Section 11)
    UC_Gen_Options ..> UC_Sub_Telegram : <<include>>

    %% --- INTER-SERVICE API CALLS (Solid Contract Lines) ---
    %% Sales Agent executing API calls against independent algorithmic business endpoints
    UC_Collect_Data --> UC_Create_Lead : "API: Create/Update Lead"
    UC_Call_Booking --> UC_Booking_Operations : "API: Request Booking"
    UC_Call_Payment --> UC_Payment_Operations : "API: Request Payment"

    %% Marketing AI pushing state to CRM after approval cycle succeeds
    UC_Sub_Telegram --> UC_Create_Lead : "API: Transfer approved proposal to CRM/Landing"
```
