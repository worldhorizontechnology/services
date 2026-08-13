# UML / ARCHITECTURE DIAGRAMS

## 1. UML / Architecture Diagrams

### 1.1. Component Diagram

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
   
graph TB
    %% Стилизация компонентов
    classDef python fill:#ffd343,stroke:#3b7bb2,stroke-width:2px,color:#000;
    classDef laravel fill:#ff2d20,stroke:#b31b12,stroke-width:2px,color:#fff;
    classDef node fill:#339933,stroke:#215721,stroke-width:2px,color:#fff;
    classDef storage fill:#00a3e0,stroke:#006699,stroke-width:2px,color:#fff;
    classDef snowflake fill:#29b5e8,stroke:#165b75,stroke-width:2px,color:#fff;

    %% Клиентские каналы
    subgraph Channels [Внешние Каналы и Фронтенд]
        WA[WhatsApp Business API]
        TG_In[Telegram Groups]
        Vue[Vue.js Landings / WebUI]
    end

    %% API Gateway
    GW[API Gateway / Auth Service]:::node

    %% ИИ Агенты (Python)
    subgraph AIAgents [Слой ИИ Агентов (Python / Clean Architecture)]
        SalesAgent[WhatsApp Sales Agent]:::python
        KnowledgeAgent[Telegram Knowledge Agent]:::python
        MeetAgent[Google Meet Intel Agent]:::python
        MarketingAI[Marketing AI Engine]:::python
        RAG[RAG Agent / Layer]:::python
    end

    %% Алгоритмические сервисы
    subgraph AlgoServices [Алгоритмические Сервисы]
        CRM[Laravel CRM Service]:::laravel
        Booking[Booking Service]:::node
        Payment[Payment Service]:::node
        Scraper[Competitor Scraper]:::node
        MktAnalytics[Marketing Analytics]:::node
        Notification[Notification Service]:::node
        KnowledgeIndexer[Knowledge Indexing Service]:::node
    end

    %% Слой Хранения Данных
    subgraph Storage [Слой Хранения и Инфраструктуры]
        Redis[(GCP Memorystore / Redis State)]:::storage
        Postgres[(Cloud SQL / PostgreSQL OLTP)]:::storage
        VectorDB[(Vector Database)]:::storage
        GDrive[Google Drive / Docs API]:::storage
        PubSub{GCP Pub/Sub Bus}:::storage
        Snowflake[(Snowflake Analytics OLAP)]:::snowflake
    end

    %% Связи и направления потоков данных
    WA -->|Trace_ID| GW
    TG_In -->|Trace_ID| GW
    Vue --> GW

    GW --> SalesAgent
    GW --> KnowledgeAgent
    GW --> CRM
    GW --> Booking

    %% Взаимодействие ИИ с RAG и Кешем
    SalesAgent <-->|Контекст сессии| Redis
    SalesAgent <-->|Поиск контекста| RAG
    RAG <-->|Embeddings / Metadata| VectorDB

    %% AI к Алгоритмическим сервисам (Только через API)
    SalesAgent -->|POST /leads| CRM
    SalesAgent -->|POST /bookings| Booking
    SalesAgent -->|POST /payments| Payment
    
    %% Потоки знаний
    KnowledgeAgent --> KnowledgeIndexer
    MeetAgent --> KnowledgeIndexer
    KnowledgeIndexer --> GDrive
    GDrive -->|Webhooks / Incremental Sync| KnowledgeIndexer
    KnowledgeIndexer --> VectorDB

    %% Маркетинговый цикл
    MktAnalytics -->|Агрегированные данные| MarketingAI
    MarketingAI -->|Утверждение контента| Notification
    Notification -->|Асинхронный пуш| TG_In

    %% Аналитический поток (Делинг OLTP/OLAP)
    CRM -->|Асинхронные события| PubSub
    SalesAgent -->|Логи токенов и промптов| PubSub
    PubSub --> Snowflake
```
