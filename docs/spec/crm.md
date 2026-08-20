CRM Service Specification
1. Purpose
The CRM Service acts as the operational, transactional, and customer-management backbone of the platform. It serves as the single source of truth for business states, customer records, lead lifecycles, and sales pipelines.
2. Role in Platform
The CRM Service is an operational system (OLTP) that handles high-concurrency transactional reads and writes. It acts as a passive business logic executor that processes structured data received from external touchpoints, AI agents, and internal users.
3. Position in Architecture
Positioned directly behind the API Gateway and Auth Service in the business services layer. It interfaces directly with PostgreSQL for relational storage, emits domain events to Redis Horizon queues, and exposes REST APIs for internal microservices and frontends.
                 ┌───────────────────────────┐
                 │        API Gateway        │
                 └─────────────┬─────────────┘
                               │
                 ┌─────────────▼─────────────┐
                 │        CRM Service        │
                 │   (Laravel 12 / PHP 8.4)  │
                 └──────┬─────────────┬──────┘
                        │             │
        ┌───────────────▼┐           ┌▼───────────────┐
        │  PostgreSQL 16 │           │ Redis Queues   │
        │   (OLTP Store) │           │ (Horizon CDC)  │
        └────────────────┘           └───────┬────────┘
                                             │
                                     ┌───────▼────────┐
                                     │   Snowflake    │
                                     │ (OLAP Data W/H)│
                                     └────────────────┘

4. CRM Responsibilities
 * Managing operational core entities (Leads, Customers, Deals, Promotions).
 * Tracking customer interaction history and linking RAG usage logs.
 * Applying deterministic rules for customer classification (new vs. repeat).
 * Exposing administrative control interfaces via Filament v3.
 * Publishing domain events for downstream analytics streaming to Snowflake.
5. CRM Does NOT Own
 * Direct LLM generation or natural language understanding (handled by AI Agents).
 * Calendar slot management or double-booking locks (handled by Booking Service).
 * Payment tokenization, processing, or gateway webhooks (handled by Payment Service).
 * Web scraping or competitor price intelligence (handled by Competitor Scraper).
 * Canonical document base knowledge storage (handled by Google Workspace).
6. Components Connected to CRM
 * API Gateway: Routes authenticated ingress traffic to CRM API endpoints.
 * WhatsApp Sales Agent: Submits qualified leads and interaction history logs.
 * Booking Service: Syncs appointment scheduling status with lead/deal records.
 * Payment Service: Sends payment confirmation events to update deal statuses.
 * Telegram / Marketing AI: Receives approval notifications and registers approved promotion codes.
 * Landing Services: Posts direct form submissions and attributes lead sources.
 * Notification Service: Triggers transactional alerts based on CRM state changes.
 * Snowflake: Consumes asynchronous CDC events for OLAP analysis.
 * Manager / CRM UI: Provides admin access for human operators via Filament v3.
7. CRM Domain
 * Leads: Unqualified or qualified incoming inquiries.
 * Customers: Primary client entities with unified identity profiles.
 * Companies: B2B organizational records associated with customers.
 * Contacts: Individual touchpoints attached to company profiles.
 * Products: Standard catalog items available for sale.
 * Services: Bookable service definitions linked to pricing models.
 * Pipeline: Configurable multi-stage workflows representing sales progression.
 * Opportunities: Quantified active business inquiries linked to pipelines.
 * Interactions: Complete history of communication events and channels.
 * Promotions: Active discount programs and offer conditions.
 * Campaigns: Marketing efforts associated with lead attribution.
 * Bookings: Operational appointment references linked to deals.
 * Payments: Transaction records and financial settlement statuses.
 * AI Agent Logs: Audit trails of AI-generated responses and RAG sources used.
8. Customer Lifecycle
Prospect \rightarrow Lead \rightarrow Active Customer \rightarrow Repeat Customer \rightarrow At-Risk \rightarrow Churned
9. Lead Lifecycle
New \rightarrow Contacted \rightarrow Qualified \rightarrow Converted to Deal \rightarrow Unqualified / Rejected
10. Sales Pipeline
New Inquiry \rightarrow Qualification \rightarrow Booking Scheduled \rightarrow Payment Pending \rightarrow Closed Won / Closed Lost
11. New / Repeat Customer Logic
When a lead payload enters the CRM, the system queries the customers database using the normalized phone number (E.164 format) or primary email address:
 * Match Exists: The incoming lead is associated with the existing Customer entity. The flag is_repeat is set to true, and the lifetime order count is incremented.
 * No Match Exists: A new Customer record is instantiated. The flag is_repeat is set to false.
12. Lead Source / Attribution
Every incoming lead schema requires attribution metadata: utm_source, utm_medium, utm_campaign, utm_content, and referrer_channel. If submitted by an AI Agent, channel is explicitly set to whatsapp_sales_agent or telegram_agent.
13. Promotions
Promotions are created algorithmically or generated by Marketing AI. Promos generated by AI remain in a pending_approval state until a manager executes a confirmation action in Telegram. Once approved, the CRM registers the promotion code, discount parameters, and expiration limits for validation by frontends and agents.
14. Booking Integration
The CRM does not check calendar slots. When a booking is finalized by the Booking Service, it issues a POST call to /api/v1/crm/bookings/sync. The CRM updates the related deal stage to Booking Scheduled and links the booking UUID to the customer timeline.
15. Payment Integration
The CRM does not process card data. The Payment Service handles tokenization and gateway execution. Upon successful settlement, the Payment Service sends an authenticated webhook event (payment.succeeded) to the CRM, transitioning the deal state to Closed Won and issuing a financial transaction record.
16. WhatsApp Sales Agent → CRM
Submits qualified lead data via POST /api/v1/crm/leads. The payload includes customer contact parameters, user intention, extracted requirements, and full transcript snippets. Includes used RAG sources (document_id, chunk_id) to log contextual citations.
17. Telegram / Marketing AI → CRM
Marketing AI sends proposed promotion objects. Once approved via Telegram ChatOps buttons, the Telegram Service executes a callback to POST /api/v1/crm/promotions containing the payload, approval token, and Telegram manager ID.
18. Landing → CRM
Vue landing pages interact with the CRM through API Gateway endpoints (POST /api/v1/crm/public/leads). Submissions contain contact forms, tracking cookies, and UTM attributes.
19. CRM → Snowflake
The CRM uses PostgreSQL CDC (Change Data Capture) via asynchronous Laravel Horizon queues (ExportToSnowflakeJob). Events are batched and streamed to Snowflake staging tables to prevent impact on OLTP performance.
20. CRM → Notification
State changes (e.g., Lead Created, Deal Won) emit internal domain events. The Notification Service listens to these queues and dispatches SMS, email, or internal push alerts.
21. Data Ownership
The CRM owns the primary relational datasets stored in PostgreSQL: users, customers, leads, deals, promotions, interaction_logs, products, and services.
22. Data Flow
 * Ingress: External Agent/Landing \rightarrow API Gateway \rightarrow CRM API Endpoint.
 * Execution: Middleware Authorization \rightarrow FormRequest Validation \rightarrow Action execution \rightarrow PostgreSQL transaction.
 * Egress: Domain Event Emission \rightarrow Redis Horizon \rightarrow CDC Worker \rightarrow Snowflake DW.
23. Events
 * Lead.Created
 * Lead.StatusChanged
 * Customer.Identified
 * Deal.StageUpdated
 * Promotion.Activated
 * Payment.Linked
24. Security
 * Authentication: Sanctum Bearer token validation for internal API calls.
 * Authorization: Role-based Access Control (RBAC) enforced via Filament policies.
 * Data Protection: Encryption at rest for PII fields (phone numbers, personal emails) using AES-256-GCM.
 * Transport: TLS 1.3 enforced across all service boundaries.
25. AI Agent Rules
 * Agents must authenticate using designated agent service tokens.
 * Agents are prohibited from performing direct database writes; all state changes must pass through validated REST APIs.
 * Agents must provide mandatory source metadata when submitting interaction logs.
26. Laravel Architecture
 * Framework: Laravel 12 on PHP 8.4.
 * UI: Filament v3 for admin operations.
 * Pattern: Single-Action Classes (app/Actions) for domain logic.
 * Queues: Laravel Horizon managing Redis queues.
27. PostgreSQL Data Model
[customers] 1 ───< N [leads] 1 ───< 1 [deals]
     │                 │
     ├───< N [logs]     └───> N [promotions]
     │
     └───< N [payments]

 * customers: id (UUID), phone (VARCHAR, INDEXED), email (VARCHAR), full_name (VARCHAR), is_repeat (BOOLEAN), created_at (TIMESTAMP).
 * leads: id (UUID), customer_id (FK), status (VARCHAR), source (VARCHAR), utm_data (JSONB), created_at (TIMESTAMP).
 * deals: id (UUID), lead_id (FK), customer_id (FK), stage (VARCHAR), amount (DECIMAL), created_at (TIMESTAMP).
 * interaction_logs: id (UUID), customer_id (FK), channel (VARCHAR), payload (TEXT), rag_sources (JSONB), created_at (TIMESTAMP).
28. API Boundary
 * POST /api/v1/crm/leads — Create/update lead records.
 * GET /api/v1/crm/customers/{id} — Fetch unified customer profile.
 * POST /api/v1/crm/promotions — Register approved promotion.
 * POST /api/v1/crm/bookings/sync — Synchronize appointment reference.
 * POST /api/v1/crm/payments/webhook — Process payment event.
29. Error Handling
 * Standardized JSON API response structures.
 * HTTP 422 Unprocessable Entity for validation failures with detailed field error schemas.
 * HTTP 401 Unauthorized / 403 Forbidden for security violations.
 * Unhandled exceptions return HTTP 500 Internal Server Error and log stack traces to Cloud Logging.
30. Idempotency
All POST endpoints accept an X-Idempotency-Key header. Requests containing duplicate keys within a 24-hour window return cached HTTP responses without re-executing database transactions.
31. Audit
All mutation actions record audit trails containing: operator_id, operator_type (user vs. AI agent token), ip_address, action_type, before_state (JSON), and after_state (JSON).
32. Testing
 * Framework: Pest PHP / PHPUnit.
 * Coverage Target: Minimum 85% line coverage for Domain Actions and Controllers.
 * Types: Feature tests for API contracts, Unit tests for internal actions, Database tests using isolated PostgreSQL test databases.
33. Deployment
 * Containerization: Multi-stage Docker container build.
 * Platform: Google Cloud Run instance execution.
 * CI/CD: GitHub Actions pipeline triggering Cloud Build upon merge to main.
34. Dependencies
 * php: ^8.4
 * laravel/framework: ^12.0
 * filament/filament: ^3.0
 * laravel/sanctum: ^4.0
 * laravel/horizon: ^5.0
35. Forbidden Dependencies / Forbidden Behavior
 * NO Direct LLM Integrations: The CRM codebase must not import OpenAI, Gemini, or LangChain SDKs.
 * NO Direct Calendar Modifications: The CRM must not call Google Calendar API directly.
 * NO Direct Payment Gateway Execution: The CRM must not communicate directly with Stripe or banking APIs.
 * NO Raw SQL Queries: All queries must use Eloquent ORM or strict query builders to prevent SQL injection vulnerabilities.
36. Architectural Invariants
 * The CRM is strictly an OLTP engine; analytical processing must be offloaded to Snowflake.
 * AI Agents cannot mutate CRM data without passing through REST API validation layers.
 * Financial and calendar states are updated via asynchronous events from Payment and Booking services.
 * Every Customer record must be uniquely identified by an E.164 phone number or validated primary email.
