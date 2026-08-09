# SYSTEM ARCHITECTURE

## 1. Key architectural principles

Microservice architecture instead of a monolith.
Separation of AI Agents from regular backend services.
Minimizing the use of LLM to reduce operating costs.
Google Cloud as the primary cloud platform.
Google Workspace as the corporate document and knowledge environment.
Google Drive/Docs is the source of the corporate knowledge base; RAG uses its current content.
Laravel is the corporate CRM and business portal.
Vue is separate frontend/landing projects.
Google Calendar is accessed through a separate Booking Service, not directly from the AI ​​agent.
Snowflake is an analytics hub, not an operational database.
All integrations are via APIs and clear service contracts.
Docker containerization and independent CI/CD pipelines.
Clean Architecture, SOLID, strong typing, automated tests, and security-by-design.

## 2. AI Components and Standard Services

### 2.1. AI Agents
WhatsApp Sales Agent — customer communication, RAG, recommendations, lead collection.
Knowledge Agent — processing unstructured internal knowledge and preparing knowledge base updates.
Meeting Intelligence Agent — processing meeting transcripts, extracting solutions, tasks, and requirements.
Marketing AI — generating offers/promotions based on prepared analytics.
RAG Agent/Layer — intelligent search and source control for AI components.

### 2.2. Algorithmic Services

API Gateway
Auth Service
CRM Service
Booking Service
Payment Service
Competitor Scraper Service
Marketing Analytics Service
Analytics Service
Snowflake Connector Service
Notification Service
File Service
Knowledge/Indexing Service
Landing/API services
Competitor Scraper Service is not an AI agent: it performs standard scraping, parsing, company search, and data collection. Similarly, Booking, Payment, CRM, ETL, Calendar, notification, and analytics operations should not call LLM unless specifically required.

## 3. AI Agents

### 3.1. WhatsApp Sales Agent

Receiving messages via the WhatsApp Business API.
Determining customer intent.
RAG search of the corporate knowledge base.
Answers to questions about products, services, pricing, and FAQs.
Product recommendations.
Collecting name, phone number, email, company, product of interest, and comments.
Creating/updating a lead via the CRM API.
Calling the Booking Service when an appointment is needed.
Calling the Payment Service only if the specific scenario supports payment.
The Sales Agent does not modify the Knowledge Base directly.

### 3.2. Telegram Knowledge Agent

Connection to internal Telegram groups.
Highlighting useful business information: products, pricing, FAQs, instructions, manager decisions, customer responses, and changes.
Transferring structured data to the Knowledge Service/Knowledge Agent.
Must not independently publish unverified changes to the public knowledge base.

### 3.3. Google Meet Intelligence Agent

Retrieves available Google Meet transcripts.
Extracts decisions, tasks, product changes, customer requirements, and agreements.
Generates a structured knowledge update.
Transfers the result to the Knowledge Service.

### 3.4. Marketing AI

Receives only prepared aggregated data from the Marketing Analytics Service.
Generates options for promotions, offers, or marketing messages.
Submits the proposal to Telegram for approval.
Once approved, the proposal is transferred to CRM/Landing Services.
Campaign results are returned to Snowflake for the next analytical cycle.

# 4. Knowledge Base and RAG
   
Google Drive is a corporate document repository. Google Docs is used for editable content. The knowledge base includes a product catalog, pricing, instructions, technical documentation, FAQs, sales materials, policies, and marketing materials. Knowledge Update Flow
Telegram / Google Meet ↓ Knowledge Processing ↓ Validation / Deduplication / Classification ↓ Google Drive / Google Docs ↓ Incremental Indexing ↓ Chunking + Embeddings ↓ Vector Database ↓ RAG Search ↓ Sales Agent / other AI components
RAG should preserve source metadata and provide the ability to determine which document and fragment the context is derived from.

# 5. CRM
   
Laravel CRM is the primary operating system for customer and sales management.
Leads
Customers
Products
Sales Pipeline
Managers
Interactions / communication history
Bookings
Payments
Promotions
AI Agent logs
Knowledge Base viewer
Analytics dashboards
Lead source and promotion attribution
Customer status, including new/repeat customer
CRM is not a replacement for Snowflake. Operational data is stored in PostgreSQL/Laravel, while analytical data and historical events are transferred to Snowflake.

# 6. Booking Service and Google Calendar
   
The Booking Service is a separate microservice. AI agents and the frontend do not work directly with Google Calendar.
Getting free slots.
Creating an appointment.
Cancelling.
Rescheduling.
Syncing with Google Calendar.
Reminders.
Conflict checking.
API for CRM, Landing, and AI Sales Agent

# 7. Payment Service
Payment is an optional business scenario. Not every landing page or promotion needs to have a payment button.
