# UML / ARCHITECTURE DIAGRAMS

## 1. UML / Architecture Diagrams

### 1.1. Component Diagram

@startuml actor Customer actor Manager
component "WhatsApp Cloud API" as WA component "Telegram Service" as TG component "Google Meet" as Meet
package "AI Layer" { component "WhatsApp Sales Agent" as Sales component "Knowledge Agent" as Knowledge component "Meeting Intelligence Agent" as Meeting component "Marketing AI" as MarketingAI component "RAG Service" as RAG }
package "Business Services" { component "CRM Service" as CRM component "Booking Service" as Booking component "Payment Service" as Payment component "Competitor Scraper" as Scraper component "Marketing Analytics" as MarketingAnalytics component "Notification Service" as Notify }
database "PostgreSQL" as PG database "Snowflake" as SF database "Vector DB" as VDB cloud "Google Drive / Docs" as Drive cloud "Google Calendar" as Calendar
WA --> Sales TG --> Knowledge Meet --> Meeting Sales --> RAG Sales --> CRM Sales --> Booking Sales --> Payment Knowledge --> Drive Meeting --> Drive Drive --> RAG RAG --> VDB CRM --> PG Booking --> Calendar CRM --> SF Booking --> SF Payment --> SF Scraper --> SF SF --> MarketingAnalytics MarketingAnalytics --> MarketingAI MarketingAI --> TG Manager --> CRM @enduml

