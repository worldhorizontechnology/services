
### 1. ER Diagram: Core Analytics Domain


```mermaid
erDiagram
    CHANNELS ||--o{ CAMPAIGNS : "groups"
    CHANNELS ||--o{ CUSTOMERS : "attracts"
    CAMPAIGNS ||--o{ CUSTOMERS : "converts"
    CAMPAIGNS ||--o{ ORDERS : "applies discount"
    
    CUSTOMERS ||--o{ ORDERS : "places"
    ORDERS ||--o{ ORDER_SERVICE : "contains"
    SERVICES ||--o{ ORDER_SERVICE : "included in"
    
    CHANNELS ||--o{ MARKETING_ANALYTICS_SNAPSHOTS : "aggregates"
    CAMPAIGNS ||--o{ MARKETING_ANALYTICS_SNAPSHOTS : "aggregates"

    MARKETING_ANALYTICS_SNAPSHOTS {
        bigint id PK
        date period_date
        enum period_type
        bigint channel_id FK
        bigint campaign_id FK
        uint new_leads_count
        uint new_paying_customers_count
        decimal conversion_rate
        decimal total_revenue
        decimal total_discounts
        decimal average_order_value
        decimal ad_spend
        decimal cac
        decimal roas
        uint total_orders_count
        uint completed_orders_count
        uint cancelled_orders_count
        jsonb services_breakdown
        jsonb ai_context_data
    }

```

### 2. Class Diagram: Core Analytics Domain


```mermaid
classDiagram
    class MarketingAnalyticsSnapshot {
        +int id
        +Date period_date
        +String period_type
        +int channel_id
        +int campaign_id
        +int new_leads_count
        +int new_paying_customers_count
        +float conversion_rate
        +float total_revenue
        +float total_discounts
        +float average_order_value
        +float ad_spend
        +float cac
        +float roas
        +int total_orders_count
        +int completed_orders_count
        +int cancelled_orders_count
        +Array services_breakdown
        +Array ai_context_data
        +channel() BelongsTo
        +campaign() BelongsTo
        +aggregatePeriod(Carbon start, Carbon end, String type, int campaignId, int channelId) MarketingAnalyticsSnapshot
    }

    class MarketingAnalyticsSnapshotController {
        +index(Request request) JsonResponse
        +recalculate(Request request) JsonResponse
    }

    class Channel {
        +int id
        +String name
        +String type
        +Boolean is_active
    }

    class Campaign {
        +int id
        +int channel_id
        +String name
        +String promo_code
        +float budget
    }

    MarketingAnalyticsSnapshotController ..> MarketingAnalyticsSnapshot : queries & triggers
    MarketingAnalyticsSnapshot "1" --> "0..1" Channel : references
    MarketingAnalyticsSnapshot "1" --> "0..1" Campaign : references

```

### 3. Sequence Diagram: Scheduled Batch Aggregation Workflow


```mermaid
sequenceDiagram
    autonumber
    participant Scheduler as Laravel Scheduler / Queue Worker
    participant Model as MarketingAnalyticsSnapshot
    participant DB as PostgreSQL Database

    Scheduler->>Model: aggregatePeriod(startDate, endDate, periodType)
    activate Model
    
    Model->>DB: COUNT(customers) WHERE created_at BETWEEN dates
    DB-->>Model: newLeadsCount
    
    Model->>DB: SELECTRaw COUNT, SUM, DISTINCT(paying_customers) FROM orders
    DB-->>Model: orderStats (revenue, discounts, orders_count)
    
    Model->>DB: JOIN order_service & services (GROUP BY service, SUM sales)
    DB-->>Model: servicesBreakdown Array
    
    Model->>Model: Compute metrics (conversionRate, CAC, ROAS, AOV)
    
    Model->>DB: updateOrCreate(period_date, period_type, metrics_payload)
    DB-->>Model: Model Instance
    
    deactivate Model
    Model-->>Scheduler: Snapshot Saved Successfully

```

### 4. Sequence Diagram: AI Agent Context Retrieval & Promotion Generation


```mermaid
sequenceDiagram
    autonumber
    participant Agent as LLM Marketing Agent
    participant API as MarketingAnalyticsSnapshotController
    participant GWS as Google Workspace (Sheets/Docs)
    participant Web as Web Search Engine (External)

    Agent->>API: GET /api/analytics/snapshots?start_date=X&end_date=Y
    API-->>Agent: JSON Response (Internal DB Metrics: CAC, AOV, etc.)

    Agent->>GWS: Fetch company info, current prices, margins & brand tone
    GWS-->>Agent: Sheets (Pricing/Services), Docs (Company guidelines)

    Note over Agent: Evaluates DB Metrics against Workspace Margins
    
    Agent->>Web: Search Competitor Prices, Local Holidays, & Trends
    Web-->>Agent: Search Results (External Context)

    Note over Agent: Synthesizes Internal Performance + Margins + External Trends

    Agent->>Agent: Generate Promotion (Dates, Audience, Discount Price, Budget)

```

### 5. State Diagram: Order Lifecycle Impact on Snapshots


```mermaid
stateDiagram-v2
    [*] --> New: Order Created
    
    New --> InProgress: Assigned to Executor
    New --> Cancelled: Customer/System Cancelled
    
    InProgress --> Completed: Service Delivered & Paid
    InProgress --> Cancelled: Aborted during execution
    
    state SnapshotAggregation {
        [*] --> FetchOrders
        FetchOrders --> CountTotal: Includes (New + InProgress + Completed + Cancelled)
        FetchOrders --> CountCompleted: Filter (status = completed) -> Total Revenue & AOV
        FetchOrders --> CountCancelled: Filter (status = cancelled) -> Cancellation Rate
    }

    Completed --> SnapshotAggregation
    Cancelled --> SnapshotAggregation

```

### 6. System Component Architecture

```mermaid
graph TD
    subgraph Storage Layer
        DB[(PostgreSQL Database)]
    end

    subgraph Laravel Framework
        Scheduler[Laravel Cron Scheduler]
        Job[Batch Processing Jobs]
        Model[MarketingAnalyticsSnapshot Model]
        Controller[Analytics API Controller]
    end

    subgraph AI Infrastructure
        AIAgent[LLM Marketing Agent]
        WebSearch[External Web Search Engine]
        GWS[Google Workspace API: Sheets/Docs]
    end

    Scheduler -->|Triggers Daily/Weekly| Job
    Job -->|Calls aggregatePeriod| Model
    Model -->|Aggregates via Raw SQL| DB
    Controller -->|Queries Snapshots| Model
    AIAgent -->|HTTP GET /api/analytics/snapshots| Controller
    AIAgent -->|Enriches with Market Data| WebSearch
    AIAgent -->|Fetches Pricing, Margins, Content Rules| GWS

```

### 7. Flowchart: End-to-End Analytics & Campaign Generation Pipeline


```mermaid
flowchart TD
    A[Raw Transactions: Customers, Orders, Services] -->|Scheduled Nightly Aggregation| B(MarketingAnalyticsSnapshot)
    B -->|Provides Financial & Funnel Metrics| C{AI Agent Audit}
    
    C -->|Detects Drop in Conversion or AOV| D[Identify Underperforming Services]
    C -->|Detects Low Lead Volume| E[Identify Ad Spend / CAC Inefficiencies]
    
    D --> F[External Web Search: Local Trends & Competitor Offers]
    E --> G[External Web Search: Local Holidays & Event Triggers]
    
    W[Google Workspace: Sheets Prices & Docs Guidelines] --> H
    F --> H[LLM Synthesis & Strategy Engine]
    G --> H
    
    H --> I[Output: Weekly/Monthly Executive Report]
    H --> J[Output: Tailored Promotional Campaign]
    J --> K(Start/End Dates & Budget)
    J --> L(Discount Price & Target Audience)
    J --> M(Ad Copy & Messenger Scripts)

```

---

### External Market Search Requirements for AI Agent

When the LLM Agent reads internal aggregate metrics from `MarketingAnalyticsSnapshot` (such as `conversion_rate`, `cac`, `roas`, `average_order_value`, and `services_breakdown`), internal numbers alone only explain **what happened**, not **why** or **how to fix it**.

Grounded in classic marketing literature—such as **Philip Kotler’s Environmental Scanning & PESTLE Framework** (Marketing Management), **Avinash Kaushik’s Contextual Benchmarking** (Web Analytics 2.0), and **W. Chan Kim’s Non-Customer Analysis** (Blue Ocean Strategy)—the LLM agent must enrich internal snapshot data with specific web search queries **and cross-reference them with Google Workspace documents (prices, margins, brand info)** to generate actionable campaign parameters.

```text
+-------------------------------------------------------------+
|               AI Agent Decision Loop                        |
|                                                             |
|   +-----------------------------------------------------+   |
|   |  Internal Snapshot Metrics (DB)                     |   |
|   |  (Revenue, CAC, AOV, Services Breakdown)            |   |
|   +--------------------------+--------------------------+   |
|                              |                              |
|   +-----------------------------------------------------+   |
|   |  Internal Company Context (Google Workspace)        |   |
|   |  (Sheets: Margins/Prices | Docs: Brand Guidelines)  |   |
|   +--------------------------+--------------------------+   |
|                              |                              |
|                              v                              |
|   +-----------------------------------------------------+   |
|   |  Strategic Gap Identification                       |   |
|   |  (e.g., Hair Salon AOV drop, Massage CAC spike)     |   |
|   +--------------------------+--------------------------+   |
|                              |                              |
|                              v                              |
|   +-----------------------------------------------------+   |
|   |  Targeted External Web Search                       |   |
|   |  (Competitors, Holidays, Weather, Local Trends)     |   |
|   +--------------------------+--------------------------+   |
|                              |                              |
|                              v                              |
|   +-----------------------------------------------------+   |
|   |  Synthesized Promotional Strategy & Execution       |   |
|   |  (Generates Dates, Audience, Price, Budget, Copy)   |   |
|   +-----------------------------------------------------+   |
+-------------------------------------------------------------+

```

#### 1. Local Holidays, Micro-Events, and Trigger Calendar

* **Internal Trigger**: Low `new_leads_count` or upcoming historically low revenue periods in `period_date`.
* **Workspace Constraint**: Agent checks Google Sheets for staff availability/shifts and baseline service prices.
* **Theoretical Framework**: Kotler's Promotional Timing & Demand Shaping. Service demand in beauty and wellness is highly cyclical and date-driven.
* **External Data to Search**: Upcoming Local Public Holidays, Micro-Events (proms, wedding expos), Weather & Seasonal Shifts (e.g., heatwaves driving demand for pedicures or cooling body wraps).
* **Agent Output Generation**: Agent sets exact **Start and End Dates** for the campaign (e.g., running 5 days prior to a local festival) and selects the **Target Audience** (e.g., female clients aged 25-45).

#### 2. Competitor Intelligence & Pricing Benchmarks

* **Internal Trigger**: Declining `conversion_rate` or dropping `average_order_value` (AOV) for specific items in `services_breakdown`.
* **Workspace Constraint**: Agent reads Google Sheets to determine the maximum allowable **Discount Price** without violating the profit margin threshold for services like a 60-min massage.
* **Theoretical Framework**: Avinash Kaushik’s Competitive Intelligence & Contextual Benchmarking. Drop in conversion without changes to ad spend usually indicates competitor offer aggression.
* **External Data to Search**: Competitor Pricing & Package Bundles on Google Maps/Instagram, Active Competitor Promotions, Customer Complaints on Competitor Reviews to extract value differentiators.
* **Agent Output Generation**: Agent calculates a specific **Discount Price** or bundle value, and allocates a specific **Ad Budget** based on the required CAC to outbid local competitors.

#### 3. Macro Trends & Service Innovations

* **Internal Trigger**: Stagnant repeat bookings or dropping `roas` on long-running campaigns.
* **Workspace Constraint**: Agent reads Google Docs to ensure the generated promotional copy matches the company's brand voice (e.g., maintaining a relaxing, premium "В ритме моря" tone rather than aggressive sales jargon).
* **Theoretical Framework**: W. Chan Kim & Renée Mauborgne's Blue Ocean Strategy (Value Innovation). Instead of discounting core services in a red ocean, bundle high-value, low-cost trending procedures.
* **External Data to Search**: Trending Hair Care Procedures (e.g., Scalp Detox, Glossing), Trending Massage & Wellness Procedures (e.g., Lymphatic Drainage, Sound bath), Seasonal Beauty Needs.
* **Agent Output Generation**: Agent drafts the final **Ad Copy** and WhatsApp/Telegram messenger scripts incorporating the trending keywords found online, tailored to the brand's services.

#### 4. Market Search Matrix for AI Agent Prompts

| Internal Snapshot Condition | Identified Anomaly | External Search Strategy + Workspace Check | Target Output Action (Generated Parameters) |
| --- | --- | --- | --- |
| `services_breakdown` shows low massage sales, but high hair salon sales | Cross-sell inefficiency | Search: "Trending relaxation add-ons for salon clients". Check Sheets for massage oil costs. | Generate **Bundle Promo**: "Express Relaxation". Set **Discount Price**: 15%. Define **Audience**: Past hair clients. |
| `cac` rises above target on campaigns | Ad fatigue or high competition | Search: "Current local hair/massage competitor promos in [City]". Check Docs for brand USP. | Generate **New Ad Copy** highlighting unique value over competitors. Reallocate **Budget**: Focus 80% on retargeting. |
| `period_date` approaches 2–3 weeks before major holiday | Demand spike opportunity | Search: "Local holiday event calendar [City] next month". Check Sheets for peak pricing rules. | Generate **Campaign Dates**: 14 days before holiday. Set **Price**: Early-bird rate. Draft **Copy**: "Lock in your spot". |
| High `cancelled_orders_count` | Customer attrition / friction | Search: "Best customer retention offers beauty salon industry". Check Sheets for maximum retention discount. | Generate **Win-back Campaign**. Set **Audience**: Inactive > 60 days. Set **Discount**: Highest allowable margin threshold. |
