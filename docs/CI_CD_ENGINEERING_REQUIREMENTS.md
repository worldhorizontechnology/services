# CI/CD & ENGINEERING REQUIREMENTS

## 1. CI/CD Architecture (Multi-Stream Pipeline)

### 1.1. AI Stream (Python Microservices)
* **Package Manager & Build Tool**: `uv` for ultra-fast, predictable dependency resolution and optimized Docker layers.
* **Linter & Formatter**: `Ruff` handles both strict linting and code formatting natively, completely replacing Black.
* **Static Type Checker**: `MyPy` executed with the `--strict` flag to guarantee type safety across agent boundaries.
* **Security Scanners (SAST)**: `Bandit`, `Semgrep`.
* **Testing Framework**: `Pytest` + `Coverage` with a minimum 90% test coverage threshold enforced at the pipeline level.

### 1.2. CRM & Business Portal Stream (Laravel / PHP)
* **Linter & Formatter**: `Laravel Pint` zero-configuration code style tool preset to the Laravel standard.
* **Static Type Checker**: `PHPStan` configured to strict Level 8 or higher.
* **Security Scanners**: `Composer Audit` for dependency checks, `Semgrep` utilizing specific Laravel security rule-sets.
* **Testing Framework**: `Pest` or `PHPUnit` covering Leads, Pipelines, and Integration APIs.

### 1.3. Frontend & Algorithmic Services Stream (JavaScript / TypeScript)
* **Linter & Formatter**: `ESLint`, `Prettier`.
* **Static Type Checker**: TypeScript Compiler (`tsc` in `strict: true` mode).
* **Security Scanners**: `npm audit` / `yarn npm audit`, `Semgrep`.
* **Testing Framework**: `Vitest` or `Jest` for API Gateway, Scraping, and Booking logic.

### 1.4. Global Cloud & Security Infrastructure

* **Global Code Analysis**: GitHub CodeQL and Snyk are integrated into all repository workflows.
* **Container Security**: Multi-stage Docker builds scanned via Trivy (FS & Container image layers).
* **Artifact Deployment**: Secure authentication via Workload Identity Federation to Google Artifact Registry.
* **Cloud Infrastructure**: Orchestrated deployment to Google Cloud Run utilizing Cloud Build.
* **Traffic Management**: Strict automated versioning, zero-downtime canary deployment strategies, and instant rollback capability on health check failure.

---

## 2. Engineering & Architecture Requirements

### 2.1. System & Service Boundaries
* **Microservice Isolation**: Complete architectural decoupling; zero monolith deployment. Independent pipelines for every deployable service.
* **Contract-Driven Design**: All inter-service communications must pass through strictly defined APIs and explicit OpenAPI contracts.
* **AI Separation**: Complete physical and logical separation of AI Agents from regular algorithmic/backend services.
* **LLM Cost Mitigation**: Algorithmic operations (Booking, Payment, CRM, ETL, Scraper, Notifications) must execute without calling LLMs unless strictly required.

### 2.2. Code Quality & Design Patterns
* **Clean Architecture**: Structural separation of Domain, Application, and Infrastructure layers across all major services.
* **SOLID Principles**: Hard enforcement of interface segregation and dependency inversion.
* **Domain-Driven Design (DDD)**: Applied strictly where justified (e.g., complex RAG indexing pipelines, advanced sales workflows).
* **Dependency Injection (DI)**: Mandatory constructor or framework-native injection for decoupling business logic from third-party clients (Google Drive API, WhatsApp API, Snowflake).
* **Data Integrity & Validation**: Strict type enforcement across all languages. Heavy reliance on Pydantic validation (Python), Form Requests/DTOs (Laravel), and Zod/TypeScript guards (JavaScript).
* **Performance**: Use of Async Python (async/await), PHP Swoole/Octane for high-throughput CRM actions, and non-blocking asynchronous Node.js for I/O operations.

### 2.3. Data Synchronization & Consistency (Sync)
* **Incremental RAG Indexing**: Knowledge base updates must use webhooks (Google Drive Activity API) to trigger incremental, atomic chunk updates. Avoid wiping vector database collections; update only modified text blocks.
* **State & Session Management**: AI Agent context and dialog states must be synchronized via a shared high-speed caching layer (Google Cloud Memorystore / Redis) to manage multi-message user sessions asynchronously.
* **Distributed State Handling**: State changes affecting external systems (e.g., creating a booking in Google Calendar via Booking Service or capturing a payment) must implement the **Transactional Outbox Pattern** or idempotent API keys to avoid double-booking and out-of-sync states during network drops.
* **Operational to Analytical Sync**: Replication of transaction events from PostgreSQL (Laravel) to Snowflake must be asynchronous (via Google Cloud Pub/Sub), ensuring zero performance impact on the operational database.

### 2.4. Observability & Monitoring Layer (APM)
* **Distributed Tracing**: Implementation of **OpenTelemetry** across all streams (Python, Laravel, Node.js). Every request must carry a unique `trace_id` from the API Gateway down to the AI Agent and CRM database queries to isolate bottlenecks.
* **Centralized Logging**: Stream all application logs in structured JSON format to **Google Cloud Logging** (Stackdriver). Direct logging of prompts, completions, and raw API exceptions is mandatory for AI auditing.
* **LLM Metrics & Cost Tracking**: Dedicated monitoring of LLM latency, token consumption (input/output), cache hit rates, and monetary costs per AI Agent instance.
* **Alerting & Health Checks**: Strict definition of `/healthz` endpoints for every Cloud Run container. Automated alerts via Google Cloud Monitoring for 5xx errors, webhook degradation (WhatsApp/Telegram timeouts), and execution failures in the asynchronous sync pipelines.

### 2.5. Testing, Documentation & Auditing
* **Documentation**: Automatic API documentation generated directly via OpenAPI definitions. Highly detailed docstrings for all public boundaries.
* **Testing Matrix**: Mandatory unit and integration test suites. 100% of critical business logic and synchronization routines must be fully covered by automated regression tests.
