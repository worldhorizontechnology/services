# UML / ARCHITECTURE DIAGRAMS

### 1. Component Diagram

```mermaid
graph TB
    %% Actors
    Customer((Customer))
    Manager((Manager))

    %% External Services
    WA_Cloud["WhatsApp Cloud API"]
    TG_Service["Telegram Service"]
    G_Meet["Google Meet"]
    G_Drive["Google Drive / Docs"]
    G_Calendar["Google Calendar"]

    %% AI Layer Package
    subgraph AI_Layer["AI Layer"]
        WA_Agent["WhatsApp Sales Agent"]
        TG_Agent["Knowledge Agent"]
        Meet_Agent["Meeting Intelligence Agent"]
        Mkt_AI["Marketing AI"]
        RAG["RAG Service"]
    end

    %% Business Services Package
    subgraph Business_Services["Business Services"]
        CRM["CRM Service"]
        Booking["Booking Service"]
        Payment["Payment Service"]
        Scraper["Competitor Scraper"]
        Mkt_Analytics["Marketing Analytics"]
        Notify["Notification Service"]
    end

    %% Databases
    PG[("PostgreSQL")]
    SF[("Snowflake")]
    VDB[("Vector DB")]

    %% Connections / Flows
    Customer --> WA_Cloud
    WA_Cloud --> WA_Agent
    
    Customer --> TG_Service
    TG_Service --> TG_Agent
    
    G_Meet --> Meet_Agent
    Meet_Agent --> RAG
    
    WA_Agent --> RAG
    TG_Agent --> RAG
    RAG --> VDB
    
    WA_Agent --> CRM
    TG_Agent --> CRM
    
    CRM --> PG
    
    Booking --> G_Calendar
    CRM --> Booking
    Booking --> SF
    
    Payment --> SF
    Scraper --> SF
    
    CRM --> SF
    SF --> Mkt_Analytics
    
    Mkt_Analytics --> Mkt_AI
    Mkt_AI --> Notify
    
    Notify --> TG_Service
    TG_Service --> Manager
    Manager --> CRM
    
    TG_Agent --> Payment
    Meet_Agent --> G_Drive
    G_Drive --> RAG
   
```
### 2.1 System Deployment/Beta Architecture Syntax

```mermaid
architecture-beta
    %% Infrastructure Groups
    group github(logos:github-icon) [GitHub Infrastructure]
    group gcp(logos:google-cloud) [Google Cloud Platform]
    group azure(logos:microsoft-azure) [Azure Cloud Free Tier]

    %% Components: GitHub Cloud
    service gh_pages(logos:github-octocat) in github [Vue.js Static Landing]
    service gh_actions(logos:github-actions) in github [GitHub Actions CI/CD]

    %% Components: GCP Compute (Cloud Run)
    service gw_node(logos:nodejs-icon) in gcp [API Gateway / Node.js]
    service crm_laravel(logos:laravel) in gcp [CRM Service / PHP Octane]
    service sales_agent(logos:python) in gcp [WhatsApp Sales Agent]
    service know_agent(logos:python) in gcp [Knowledge Agents]
    service algo_services(logos:javascript) in gcp [Algorithmic Services]

    %% Components: Azure Data Tier
    service db_postgres(logos:postgresql) in azure [Managed PostgreSQL]
    service rag_vector(logos:azure) in azure [Vector Database / RAG]

    %% CI/CD Delivery Paths
    gh_actions:R -- L: gh_pages [Builds & Pushes Static Web]
    gh_actions:B -- T: gw_node [Docker Build & Deploy]
    gh_actions:B -- T: crm_laravel [Docker Build & Deploy]
    gh_actions:B -- T: sales_agent [Docker Build & Deploy]

    %% Network & Request Routing
    gh_pages:B -- T: gw_node [REST API Requests]
    gw_node:R -- L: sales_agent [Routes Internal REST Traffic]
    gw_node:R -- L: crm_laravel [Routes Internal REST Traffic]
    gw_node:R -- L: algo_services [Routes Internal REST Traffic]

    %% Cross-Cloud Data Layer Connections
    crm_laravel:B -- T: db_postgres [Secure SQL Connection via TLS]
    sales_agent:B -- T: rag_vector [External Vector Embeddings Lookup]
    know_agent:B -- T: rag_vector [Incremental Index Upserts]
```
### 2.2  System Deployment/Legacy Standard Syntax

```mermaid
graph LR
    classDef github fill:#181717,stroke:#fff,stroke-width:2px,color:#fff;
    classDef gcp fill:#4285F4,stroke:#34A853,stroke-width:2px,color:#fff;
    classDef azure fill:#0089D6,stroke:#007FFF,stroke-width:2px,color:#fff;
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

    %% Deployment Automation Flow
    Actions -->|Deploy Static HTML/JS| Pages
    Actions -->|Build & Push Container| GW
    Actions -->|Build & Push Container| CRM
    Actions -->|Build & Push Container| AI

    %% Production Runtime Traffic
    User((Client Browser / WhatsApp)):::user -->|HTTPS| Pages
    Pages -->|REST API Requests| GW
    
    GW -->|Internal REST| AI
    GW -->|Internal REST| CRM
    GW -->|Internal REST| Algo

    %% Cross-Cloud Data Connectivity
    CRM -->|Cross-Cloud Secure SQL| Postgres
    AI -->|Cross-Cloud Vector Queries / RAG| VectorDB
```


