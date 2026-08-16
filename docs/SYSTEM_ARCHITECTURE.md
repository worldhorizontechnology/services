# SYSTEM ARCHITECTURE

## 1. Key architectural principles

* **Microservice architecture** instead of a monolith.
* **Separation of AI Agents** from regular backend services.
* **Minimizing the use of LLM** to reduce operating costs.
* **Google Cloud** as the primary cloud platform.
* **Google Workspace** as the corporate document and knowledge environment.
* **Google Drive/Docs** is the source of **the corporate knowledge base**; RAG uses its current content.
* **Laravel** is the **corporate CRM** and business portal.
* **Vue** is separate **frontend/landing** projects.
* **Google Calendar** is accessed through a separate **Booking Service**, not directly from the AI ​​agent.
* **Snowflake** is an analytics hub, not an operational database.
* **All integrations are via APIs and clear service contracts.
* **Docker containerization and independent CI/CD pipelines**.
* Clean Architecture, strong typing, automated tests, and security-by-design.

## 2. AI Components and Standard Services

### 2.1. AI Agents
* **WhatsApp Sales Agent** — customer communication, RAG, recommendations, lead collection.
* **Knowledge Agent** — processing unstructured internal knowledge and preparing knowledge base updates.
* **Meeting Intelligence Agent** — processing meeting transcripts, extracting solutions, tasks, and requirements.
* **Marketing AI** — generating offers/promotions based on prepared analytics.
* **RAG Agent/Layer** — intelligent search and source control for AI components.

### 2.1.1. WhatsApp Sales Agent

* **Receiving messages** via the WhatsApp Business API.
* Determining **customer intent**.
* **RAG** search of the corporate knowledge base.
* **Answers to questions** about products, services, pricing, and FAQs.
* **Product recommendations**.
* **Collecting name, phone number, email, company, product of interest, and comments**.
* **Creating/updating a lead** via the CRM API.
* **Calling the Booking Service** when an appointment is needed.
* **Calling the Payment Service** only if the specific scenario supports payment.
The Sales Agent does not modify the Knowledge Base directly.

### 2.1.2. Telegram Knowledge Agent

* **Connection to internal Telegram groups**.
* **Highlighting useful business information**: products, pricing, FAQs, instructions, manager decisions, customer responses, and changes.
* **Transferring structured data to the Knowledge Service/Knowledge Agent**.
Must not independently publish unverified changes to the public knowledge base.

### 2.1.3. Google Meet Intelligence Agent

* Retrieves available **Google Meet transcripts**.
* **Extracts** decisions, tasks, product changes, customer requirements, and agreements.
* **Generates** a structured knowledge update.
* **Transfers the result** to the Knowledge Service.

### 2.1.4. Marketing AI

* **Receives only prepared aggregated data from the Marketing Analytics Service**.
* **Generates options for promotions, offers, or marketing messages**.
* **Submits the proposal** to Telegram for approval.
* **Once approved, the proposal is transferred to CRM/Landing Services**.
* **Campaign results are returned to Snowflake** for the next analytical cycle.


### 2.1.5. RAG Agent/Layer 

* **Google Workspace Infrastructure:** The entire corporate knowledge base is fully hosted within the **Google Workspace** ecosystem.
* **Access Control:** Connection, authentication, and secure data extraction from Google Workspace are strictly managed via **GCP Service Accounts** with the principle of least privilege.
* **Source Repositories:** 
  * **Google Drive** acts as the central corporate document repository.
  * **Google Docs** is utilized for managing editable and dynamic content.
* **Scope of Data:** The knowledge base includes a product catalog, pricing, instructions, technical documentation, FAQs, sales materials, policies, and marketing materials.
* **RAG Source Control:** RAG must preserve source metadata, ensuring the system can always trace exactly which document and fragment the retrieved context is derived from.

### Knowledge Update Flow
```text
Telegram / Google Meet
↓
Knowledge Processing 
↓ 
Validation / Deduplication / Classification 
↓ 
Google Drive / Google Docs (via GCP Service Accounts)
↓
Incremental Indexing 
↓
Chunking + Embeddings 
↓ 
Vector Database 
↓ 
RAG Search 
↓
Sales Agent / other AI components
```

### 2.2. Algorithmic Services

* **API Gateway**
* **Auth Service**
* **CRM Service**
* **Booking Service**
* **Payment Service**
* **Competitor Scraper Service**
* **Marketing Analytics Service**
* **Snowflake Connector Service**
* **Notification Service**
* **File Service**
* **Knowledge/Indexing Service**
* **Landing/API services
Competitor Scraper Service is not an AI agent: it performs standard scraping, parsing, company search, and data collection. Similarly, Booking, Payment, CRM, ETL, Calendar, notification, and analytics operations should not call LLM unless specifically required.



### 2.2.1 CRM
   
Laravel CRM is the primary operating system for customer and sales management.
* **Leads**
* **Customers**
* **Products**
* **Sales Pipeline**
* **Managers**
* **Interactions / communication history**
* **Bookings**
* **Payments**
* **Promotions**
* **AI Agent logs**
* **Knowledge Base viewer**
* **Analytics dashboards**
* **Lead source and promotion attribution**
* **Customer status, including new/repeat customer**
CRM is not a replacement for Snowflake. Operational data is stored in PostgreSQL/Laravel, while analytical data and historical events are transferred to Snowflake.

### 2.2.2 Booking Service and Google Calendar
   
The Booking Service is a separate microservice. AI agents and the frontend do not work directly with Google Calendar.
* **Getting free slots**.
* **Creating an appointment**.
* **Cancelling**.
* **Rescheduling**.
* **Syncing with Google Calendar**.
* **Reminders**.
* **Conflict checking**.
* **API for CRM, Landing, and AI Sales Agent**

### 2.2.3 Payment Service
**Payment is an optional business scenario. Not every landing page or promotion needs to have a payment button**.

### 2.2.4 Competitor Scraper Service


### Purpose

Competitor Scraper Service is a deterministic algorithmic microservice responsible for collecting structured market data for a specified geographic area and business domain.

The service does not use LLMs or AI agents.

Its purpose is to transform a business/geographic request into a normalized dataset containing competitors, services, prices, promotions, locations and source information.

The collected data is stored directly in Google Cloud Storage and BigQuery for historical storage and subsequent analytical processing.

### Input

The scraper receives a structured scraping request:

```json
{
  "business_type": "hair salon",
  "geography": {
    "country": "Poland",
    "region": "Greater Poland",
    "city": "Poznan"
  },
  "parameters": {
    "languages": ["pl", "en"],
    "include_promotions": true,
    "include_prices": true
  }
}
```

The geography may represent a city, region, municipality, radius or other supported geographic scope.

### Processing Pipeline

```text
Scraping Request
       ↓
Geography Resolution
       ↓
Keyword Strategy
       ↓
Competitor Discovery
       ↓
Website / Source Collection
       ↓
HTML Fetching
       ↓
Content Parsing
       ↓
Service / Price / Promotion Extraction
       ↓
Normalization
       ↓
Validation / Deduplication
       ↓
Google Cloud Storage / BigQuery
```

### Keyword Strategy

The service generates deterministic search queries from the business domain and geographic parameters.

Example:

```text
hair salon Poznan
hairdresser Poznan
hair coloring Poznan
balayage Poznan
haircut Poznan
hair salon price Poznan
balayage price Poznan
hair salon promotion Poznan
```

Keyword generation must be implemented through configurable dictionaries, templates and query-generation rules.

LLM-based keyword generation is not required.

### Competitor Discovery

The service identifies potential competitors using available search and business-data sources.

For each discovered competitor, the service attempts to collect:

* company name;
* business category;
* address;
* city;
* region;
* geographic coordinates where available;
* website;
* source URL;
* available contact information;
* available services.

The service must preserve the source of every collected value where possible.

### Website Scraping

The scraper uses a layered strategy.

### Level 1 — HTTP

Use standard HTTP requests for pages that can be retrieved without browser execution.

Preferred implementation:

```text
httpx
```

### Level 2 — HTML Parsing

Static HTML is parsed using deterministic parsers.

Preferred implementations:

```text
lxml
BeautifulSoup
```

### Level 3 — Structured Data

The scraper should detect structured information such as:

* JSON-LD;
* schema.org data;
* embedded product/service data;
* metadata.

Structured data should be preferred over fragile visual selectors where available.

### Level 4 — Browser Rendering

Playwright may be used only when the required information cannot be obtained from static HTTP responses.

The service must not use a browser for every page by default.

### Data Extraction

The scraper should extract, where available:

```text
Competitor
Service
Category
Price
Currency
Duration
Discount
Promotion
Promotion Start
Promotion End
Promotion Conditions
Address
City
Region
Latitude
Longitude
Source URL
Scraped At
```

### Normalization

Raw extracted values must be normalized before being written to BigQuery.

Examples:

```text
"45 zł"
"45 PLN"
"PLN 45"
```

must be normalized to:

```text
price = 45
currency = PLN
```

Different textual representations of the same service should be mapped to a common service/category representation where deterministic rules are sufficient.

AI-based semantic normalization is not part of the scraper.

Vertex AI Gemini API may subsequently be used for analytical classification or semantic processing where required.

## Deduplication

The scraper must prevent duplicate competitor and offer records using deterministic identifiers where possible.

Potential matching attributes include:

* normalized company name;
* normalized address;
* domain;
* source identifier;
* geographic coordinates.

Historical observations must not be overwritten unnecessarily.

Each scraping run should be identifiable by a unique `scrape_run_id`.

### GCP Integration

The Competitor Scraper writes normalized observations directly to BigQuery.

The scraper should use the official Google Cloud BigQuery client library (Google GenAI ADK / Client Libraries) rather than introducing an intermediate database solely for scraper storage.

Logical data structure:

```text
SCRAPE_RUN
COMPETITOR
COMPETITOR_LOCATION
SERVICE
COMPETITOR_SERVICE
PRICE_OBSERVATION
PROMOTION_OBSERVATION
SOURCE
```

Historical observations must include:

```text
scrape_run_id
competitor_id
service_id
observed_at
source_url
```

This allows BigQuery to analyze market changes over time.

### Service Boundary

Competitor Scraper Service is responsible for:

* discovering competitors;
* collecting source data;
* scraping;
* parsing;
* extraction;
* normalization;
* validation;
* deduplication;
* writing observations to BigQuery.

It is not responsible for:

* marketing recommendations;
* promotion generation;
* CRM operations;
* Telegram communication;
* AI agent orchestration;
* business decision generation.

---

### 2.2.5 Marketing Analytics

### Purpose

Marketing Analytics is the analytical layer responsible for transforming competitor observations and business data into actionable marketing recommendations.

Marketing Analytics is implemented primarily inside BigQuery.

It is not a separate LLM agent.

It does not require a standalone Python microservice unless future requirements introduce business logic that cannot reasonably be implemented inside BigQuery.

### Analytical Flow

```text
Competitor Scraper
       ↓
BigQuery Raw / Normalized Data
       ↓
SQL Analytics
       ↓
Market Aggregation
       ↓
Vertex AI Gemini API where required
       ↓
Business Rules / Recommendation Logic
       ↓
Marketing Recommendation
```

### Market Analysis

BigQuery analyzes competitor data by:

* geography;
* service;
* competitor;
* price;
* promotion;
* time period;
* market segment.

Typical calculated metrics include:

```text
competitor_count
service_count
minimum_price
maximum_price
average_price
median_price
price_percentiles
average_discount
median_discount
promotion_frequency
promotion_duration
regional_price_difference
competitor_density
```

### Geographic Analysis

The system must be able to compare market conditions between geographic areas.

Example:

```text
Poznan
Poznan - Jeżyce
Poznan - Grunwald
Poznan - Stare Miasto
```

The analytical layer can identify:

* regions with lower competition;
* regions with higher prices;
* regions with greater promotion frequency;
* services with insufficient competitive supply;
* regional price differences.

### Service Analysis

The analytical layer compares equivalent or sufficiently similar services.

Example:

```text
Service: Balayage

Market:
P25 = 180 PLN
Median = 250 PLN
P75 = 320 PLN
Competitors = 42
Active promotions = 7
```

The system may use Vertex AI Gemini API via BigQuery ML or official SDKs when deterministic rules are insufficient for:

* text classification;
* extracting information from unstructured promotion descriptions;
* semantic similarity of service descriptions;
* aggregation or interpretation of large textual datasets.

AI must not replace deterministic SQL calculations where standard analytical methods are sufficient.

### Marketing Recommendation

The final analytical result should provide a concrete recommendation rather than only raw competitor statistics.

Example:

```text
Recommended region: Poznan - Grunwald
Recommended service: Balayage
Recommended price: 229 PLN
Market median: 250 PLN
Recommended discount: 15%
Recommended promotion duration: 14 days
Target customers: 30
Minimum viable customers: 22
```

The exact recommendation methodology must be defined as explicit analytical rules, models or SQL logic.

AI-generated interpretation must not be treated as an unrestricted replacement for deterministic business constraints.

### Output

Marketing Analytics produces a structured recommendation that can be stored and processed within BigQuery.


# 3. Technology Stack

## **Backend Framework**
- Laravel (current stable version)
- (WebSocket support)

### **Database & Cache**
- PostgreSQL 12+ (primary)
- Redis (Channels layer - upgradeable)
- SQLite3 (fallback/testing)

### **AI/ML Services**
- Vertex AI Gemini API (Gemini models)
- Vertex AI Model Garden (OSS LLMs + embeddings)
- Google GenAI ADK (Model invocation & tooling)
- Python (current stable version)


### **External Services**
- Stripe (payment processing)
- Google Workspace (RAG)
- Snowflake (Analytics)

### **Frontend**
- HTML/CSS/JavaScript
- Vue
- 
### **Scraping service**
- Python

---

## Data Flow Examples

### **Lead generation/Consultation Booking Flow**
```
Customer
  ↓
Sees landing in Facebook/Instagram
  ↓
Clicks the button to WhatsApp following
  ↓
Conversates with Sales Bot (answers questions to share data/asks questions to get products info)
  ↓
Books consultation through a calendar
  
```
