```mermaid
erDiagram
    CHANNELS ||--o{ CAMPAIGNS : "groups"
    CHANNELS ||--o{ CUSTOMERS : "attracts (First Touch)"
    CAMPAIGNS ||--o{ CUSTOMERS : "converts via promotion"
    CAMPAIGNS ||--o{ ORDERS : "applies discount"
    
    CUSTOMERS ||--o{ ORDERS : "places"
    
    ORDERS ||--o{ ORDER_SERVICE : "contains item"
    SERVICES ||--o{ ORDER_SERVICE : "included in"
    
    ORDERS ||--o{ PAYMENTS : "paid via"
    ORDERS ||--o{ ASSIGNMENTS : "requires completion"
    USERS ||--o{ ASSIGNMENTS : "performs work"
```
