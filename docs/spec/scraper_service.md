### 2.2.4 Competitor Scraper Service


#### Purpose

Competitor Scraper Service is a deterministic microservice responsible for collecting structured market data for a specified geographic area and agricultural business domain.

The service does not use LLMs or AI agents.

Its purpose is to:

- Receive target geography and agricultural business context.
- Generate relevant search keywords and search phrases.
- Discover competitors, producers, and potential buyers.
- Collect products, services, prices, promotions, and locations.
- Normalize and validate collected data.
- Store historical market observations in Snowflake.

The service does not make marketing decisions. It prepares market data for subsequent analysis in Snowflake.

---

#### Input

The service receives a structured market research request.

```json
{
  "business_domain": "smart greenhouse / microgreens",
  "geography": {
    "country": "Spain",
    "autonomous_community": "Castilla y León",
    "provinces": [],
    "municipalities": []
  },
  "products": [
    "microgreens",
    "fresh herbs"
  ],
  "market_segments": [
    "local farms",
    "restaurants",
    "hotels",
    "food businesses",
    "local retailers"
  ]
}
````

The geography must be configurable.

Supported geographic scopes may include:

* Autonomous community
* Province
* Municipality
* Group of municipalities
* Defined radius
* Other geographic areas supported by the discovery layer

---

#### Processing Pipeline

```text
Market Research Request
        |
        v
Geography Resolution
        |
        v
Keyword Strategy
        |
        v
Competitor / Buyer Discovery
        |
        v
Source Collection
        |
        v
HTTP Fetching
        |
        v
HTML / Structured Data Parsing
        |
        v
Product / Service / Price Extraction
        |
        v
Normalization
        |
        v
Validation / Deduplication
        |
        v
Snowflake
```

---

### Keyword Strategy

The service generates deterministic search queries from:

* Business domain
* Product categories
* Services
* Geographic parameters
* Market segments
* Configured synonyms
* Configured language variants

For agricultural market research, the keyword strategy may generate combinations such as:

```text
microgreens
microgreens Castilla y León
microgreens Valladolid
microgreens León
microgreens Salamanca
microgreens precio
microgreens venta
microgreens productor
microgreens restaurantes
microgreens cultivo local
invernadero inteligente
invernadero agrícola
productos agrícolas locales
hortalizas locales
productor local
```

Keyword generation must be implemented using:

* Dictionaries
* Synonyms
* Templates
* Geographic substitutions
* Deterministic query-generation rules

LLM-based keyword generation is not part of Competitor Scraper Service.

The keyword system must be extensible without changing the core scraping engine.

---

### Market Discovery

The service identifies relevant market entities within the requested geographic area.

Depending on configuration, entities may include:

* Agricultural producers
* Farms
* Greenhouse producers
* Local food producers
* Restaurants
* Hotels
* Shops
* Distributors
* Food-service businesses
* Competing producers
* Potential buyers

For each discovered entity, the service attempts to collect:

* Entity name
* Entity type
* Business category
* Address
* Municipality
* Province
* Autonomous community
* Geographic coordinates
* Website
* Source URL
* Available contact information
* Relevant products
* Relevant services

Every collected value should retain source information where technically possible.

---

### Website Scraping

The scraper uses a layered deterministic strategy.

#### Level 1 — HTTP

Use standard HTTP requests whenever the required information is available without browser execution.

Preferred implementation:

* `httpx`

#### Level 2 — HTML Parsing

Static HTML is parsed using deterministic parsers.

Preferred implementations:

* `lxml`
* `BeautifulSoup`

#### Level 3 — Structured Data

The scraper should detect and use structured information when available:

* JSON-LD
* Schema.org
* Embedded product data
* Metadata
* Structured price information

Structured data should be preferred over fragile CSS selectors where possible.

#### Level 4 — Browser Rendering

Playwright may be used only when the required information cannot be obtained from static HTTP responses.

The scraper must not use browser rendering for every page by default.

---

### Data Extraction

The scraper should extract, where available:

| Field                | Description                |
| -------------------- | -------------------------- |
| Entity               | Business or organization   |
| Business Category    | Type of business           |
| Product              | Product name               |
| Product Category     | Normalized category        |
| Service              | Service offered            |
| Price                | Numerical price            |
| Currency             | Currency                   |
| Unit                 | Price unit                 |
| Quantity             | Package or quantity        |
| Duration             | Service/promotion duration |
| Promotion            | Promotional information    |
| Discount             | Discount information       |
| Promotion Conditions | Conditions of promotion    |
| Address              | Physical address           |
| Municipality         | Municipality               |
| Province             | Province                   |
| Autonomous Community | Autonomous community       |
| Latitude             | Geographic latitude        |
| Longitude            | Geographic longitude       |
| Source URL           | Original source            |
| Scraped At           | Collection timestamp       |

For agricultural products, prices must support different units, including:

```text
EUR/kg
EUR/100g
EUR/unit
EUR/tray
EUR/box
EUR/package
```

The original source representation must be retained for traceability.

---

###  Agricultural Product Normalization

Different descriptions of equivalent products should be mapped to normalized categories where deterministic rules are sufficient.

For example:

```text
microgreens
microbrotes
brotes tiernos
microvegetales
```

may be mapped to a common normalized product category.

The original source description must remain available.

The scraper must not use an LLM merely to normalize simple product names.

Snowflake AI capabilities may be used later for semantic classification when deterministic normalization is insufficient.

---

###  Geographic Normalization

Geographic information must be normalized into a consistent hierarchy:

```text
Country
    |
    v
Autonomous Community
    |
    v
Province
    |
    v
Municipality
    |
    v
Local Area
```

This allows Snowflake to compare market conditions across municipalities and regions.

---

###  Price Normalization

Price information must be converted into structured numerical data.

Example:

```text
Original:
"12,50 € / bandeja"

Normalized:
price = 12.50
currency = EUR
unit = tray
```

The original value must be retained.

Different package sizes and units must not be incorrectly treated as equivalent.

Where conversion is possible, normalized comparable units may additionally be calculated.

---

### Promotion Normalization

Promotional information should be separated into structured fields where possible:

```text
promotion_type
discount_percent
original_price
promotional_price
promotion_start
promotion_end
minimum_quantity
promotion_conditions
```

The original promotional text must be retained for traceability.

---

### Deduplication

The scraper must prevent duplicate entities and duplicate observations.

Possible deterministic matching attributes include:

* Normalized entity name
* Normalized address
* Domain
* Source identifier
* Geographic coordinates

Historical observations must not be overwritten unnecessarily.

Every scraping execution must have a unique:

```text
scrape_run_id
```

Historical observations must preserve the observation timestamp.

---

###  Snowflake Integration

Competitor Scraper Service writes normalized market observations directly to Snowflake.

No PostgreSQL database is required solely for storing scraper results.

Logical Snowflake data structures may include:

```text
SCRAPE_RUN
MARKET_ENTITY
ENTITY_LOCATION
PRODUCT
SERVICE
ENTITY_PRODUCT
PRICE_OBSERVATION
PROMOTION_OBSERVATION
SOURCE
```

Historical observations should include:

```text
scrape_run_id
entity_id
product_id
observed_at
source_url
```

This allows Snowflake to analyze changes in the market over time.

---

###  Competitor Scraper Responsibilities

Competitor Scraper Service is responsible for:

* Market research request processing
* Geographic resolution
* Deterministic keyword generation
* Competitor discovery
* Buyer discovery
* Source collection
* HTTP scraping
* Browser-based scraping when required
* HTML parsing
* Structured-data parsing
* Product extraction
* Service extraction
* Price extraction
* Promotion extraction
* Normalization
* Validation
* Deduplication
* Snowflake ingestion

Competitor Scraper Service is **not** responsible for:

* Marketing recommendations
* Pricing decisions
* Promotion decisions
* CRM operations
* Telegram communication
* AI-agent orchestration
* Final business decisions

---

### 2.2.5 Marketing Analytics service

### Purpose

Marketing Analytics is the analytical layer implemented primarily inside Snowflake.

It transforms historical competitor and market observations into concrete business recommendations for agricultural businesses.

It is not a separate AI agent.

It is not a mandatory Python microservice.

The primary analytical engine is Snowflake.

---

### Analytical Flow

```text
Competitor Scraper
        |
        v
Snowflake Raw Data
        |
        v
Normalized Market Data
        |
        v
SQL Analytics
        |
        v
Market Aggregation
        |
        +--------------------+
        |                    |
        v                    v
Statistical Analysis    Snowflake AI / ML
        |                    |
        +---------+----------+
                  |
                  v
        Business Rules / Models
                  |
                  v
      Marketing Recommendation
```

---

### Market Analysis

Snowflake must calculate market indicators by:

* Region
* Province
* Municipality
* Product
* Service
* Competitor
* Buyer segment
* Price
* Promotion
* Time period

Typical metrics include:

```text
competitor_count
producer_count
buyer_count
product_count
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
market_density
```

---

### Geographic Opportunity Analysis

The analytical layer must compare agricultural market opportunities between geographic areas.

For agricultural projects, this may identify:

* Regions with lower competitive pressure
* Regions with higher market prices
* Regions with insufficient local supply
* Regions with sufficient potential buyers
* Municipalities with higher buyer density
* Products with stronger market opportunities
* Geographic differences in prices
* Geographic differences in competition

The result should not be limited to descriptive statistics.

The system should identify the geographic market with the most suitable commercial conditions according to defined business rules.

---

### Product Opportunity Analysis

Snowflake must compare products and services using normalized market observations.

The analysis may include:

| Metric                | Description             |
| --------------------- | ----------------------- |
| Product               | Product name            |
| Market Price          | Observed market price   |
| Median Price          | Median market price     |
| P25                   | 25th percentile         |
| P75                   | 75th percentile         |
| Number of Competitors | Competitive count       |
| Number of Buyers      | Potential buyer count   |
| Active Promotions     | Current promotions      |
| Promotion Frequency   | Frequency of promotions |
| Regional Availability | Geographic availability |

This allows the system to determine which products have potentially attractive market conditions.

---

### Price Analysis

Price recommendations must be based primarily on deterministic analytical calculations.

Snowflake can calculate:

```text
market_min_price
market_max_price
market_average_price
market_median_price
market_p25
market_p75
regional_median_price
competitor_price_position
```

The recommendation layer can then calculate a target price according to explicit business rules or trained models.

The system must not rely on an LLM to perform basic numerical price calculations.

---

### Promotion Analysis

Snowflake can analyze competitor promotions by:

* Discount percentage
* Promotional price
* Promotion duration
* Frequency
* Seasonality
* Product
* Geographic area
* Competitor

This allows the system to determine:

* Whether a product is heavily promoted
* Whether the market is price-sensitive
* Typical discount levels
* Typical promotion duration
* Whether a proposed promotion would be competitive

---

### Snowflake AI

Snowflake AI capabilities may be used only where they provide value beyond deterministic SQL.

Potential applications include:

* Classification of product descriptions
* Extraction of structured information from promotional text
* Semantic comparison of products and services
* Classification of market entities
* Analysis of unstructured competitor descriptions
* Aggregation and interpretation of large text datasets

Snowflake AI must complement, not replace, deterministic market calculations.

---

### Snowflake ML and Statistical Analysis

Where sufficient historical data exists, Snowflake ML or statistical models may be used for:

* Trend analysis
* Forecasting
* Anomaly detection
* Classification
* Demand-related modelling
* Price modelling

The architecture should prefer conventional SQL and statistical methods whenever they are sufficient.

---

### Marketing Recommendation

The final analytical output must provide a concrete recommendation.

The recommendation may contain:

```text
Recommended Region
Recommended Municipality
Recommended Product
Recommended Service
Market Median Price
Recommended Selling Price
Recommended Price Difference
Recommended Discount
Recommended Promotion Duration
Competitive Pressure
Potential Buyer Segment
Target Customer Count
Minimum Viable Customer Count
Expected Market Position
```

The system must answer:

```text
What should be sold?
Where should it be sold?
At what price?
How much cheaper or more competitive should the offer be?
How long should the promotion run?
Which market segment should be targeted?
How many customers are required?
```

The exact values must be calculated from collected market data and defined business rules or models.

---

### Historical Market Analysis

Snowflake must retain historical observations.

Historical data allows the system to identify:

* Price trends
* Changes in competition
* Competitor entry
* Competitor exit
* Promotion frequency
* Seasonal patterns
* Changes in product availability
* Regional market changes

A single scraping execution represents only a market snapshot.

Historical observations are therefore required for meaningful trend analysis.

---

### 2.2.6 Snowflake Connector

### Architectural Decision

A separate `Snowflake Connector Service` is **not required** in the current architecture.

The Competitor Scraper Service connects directly to Snowflake using the official Snowflake connector/SDK.

Marketing Analytics is implemented inside Snowflake and therefore does not require a separate API between a Python analytics service and Snowflake.

The architecture is:

```text
Competitor Scraper
        |
        | Official Snowflake Connector / SDK
        v
    Snowflake
        |
        +-- Raw Market Data
        +-- Normalized Market Data
        +-- SQL Analytics
        +-- Snowflake AI / ML
        +-- Marketing Recommendation
        |
        v
Google Workspace / Knowledge Base
        |
        v
Telegram / CRM / Other Consumers
```

---

### Connector as Infrastructure Component

The Snowflake connector should be implemented as an infrastructure adapter inside the Competitor Scraper Service.

Example project structure:

```text
competitor-scraper/
├── app/
│   ├── domain/
│   ├── application/
│   └── infrastructure/
│       └── snowflake/
│           ├── client.py
│           ├── repository.py
│           └── models.py
├── tests/
├── Dockerfile
└── pyproject.toml
```

The application layer should depend on an abstraction:

```python
class MarketDataRepository(Protocol):

    async def write_observations(
        self,
        observations: list[MarketObservation],
    ) -> None:
        ...
```

The concrete infrastructure implementation uses the Snowflake connector.

This keeps the service independent from Snowflake-specific implementation details while avoiding an unnecessary network hop.

---

### Why a Separate Connector API Is Not Required

The following architecture is unnecessary for the current project:

```text
Competitor Scraper
        |
        | HTTP
        v
Snowflake Connector Service
        |
        v
Snowflake
```

It introduces:

* Another deployable service
* Another API
* Another authentication boundary
* Another network hop
* Additional monitoring
* Additional CI/CD
* Additional failure points

The simpler architecture is:

```text
Competitor Scraper
        |
        | Snowflake SDK
        v
Snowflake
```

A separate connector service should only be introduced if multiple independent services later require centralized Snowflake access and there is a concrete architectural requirement for such an API.

---

### Final Responsibility Boundaries

### Competitor Scraper Service

```text
Competitor Scraper Service
    |
    +-- Geography
    +-- Keyword Strategy
    +-- Market Discovery
    +-- Scraping
    +-- Parsing
    +-- Extraction
    +-- Normalization
    +-- Validation
    +-- Deduplication
    +-- Snowflake Ingestion
```

### Snowflake

```text
Snowflake
    |
    +-- Market Data Storage
    +-- Historical Data
    +-- SQL Analytics
    +-- Statistical Analysis
    +-- Snowflake AI
    +-- Snowflake ML
    +-- Marketing Recommendation
```

### Google Workspace / Knowledge Base

```text
Google Workspace / Knowledge Base
    |
    +-- Analytical Results
    +-- Marketing Recommendations
    +-- Knowledge Distribution
```

### Telegram Service

```text
Telegram Service
    |
    +-- Human-facing Delivery
```

### CRM

```text
CRM
    |
    +-- Business Operations
    +-- Customers
    +-- Leads
    +-- Promotions
    +-- Sales
```

---

### Architecture Summary

```text
                    ┌──────────────────────────┐
                    │   Market Research Input  │
                    │                          │
                    │ Geography + Business     │
                    │ Domain + Products        │
                    └────────────┬─────────────┘
                                 |
                                 v
                    ┌──────────────────────────┐
                    │ Competitor Scraper       │
                    │                          │
                    │ Deterministic            │
                    │ No LLM                   │
                    │ No AI Agent              │
                    └────────────┬─────────────┘
                                 |
                                 | Snowflake SDK
                                 v
                    ┌──────────────────────────┐
                    │       Snowflake          │
                    │                          │
                    │ Market Data              │
                    │ Historical Data          │
                    │ SQL Analytics             │
                    │ Statistical Analysis     │
                    │ Cortex AI                │
                    │ Snowflake ML             │
                    │ Recommendation Logic     │
                    └────────────┬─────────────┘
                                 |
                                 v
                    ┌──────────────────────────┐
                    │ Google Workspace /       │
                    │ Knowledge Base           │
                    └────────────┬─────────────┘
                                 |
                                 v
                    ┌──────────────────────────┐
                    │ Telegram / CRM           │
                    └──────────────────────────┘
```





