resources/js/
├── Layouts/
│   ├── AppLayout.vue                   # Main layout (Header + Sidebar + Footer + slot)
│   ├── GuestLayout.vue                 # Auth and error layout
│   └── PrintLayout.vue                 # Print layout for receipts and reports
│
├── Components/
│   ├── Layout/                         # Layout shell components
│   │   ├── Header.vue                  # Top bar (search, profile, dates)
│   │   ├── Footer.vue                  # Footer (version, status indicators)
│   │   ├── Sidebar.vue                 # Sidebar navigation
│   │   ├── SidebarLink.vue             # Sidebar navigation link
│   │   ├── Breadcrumbs.vue             # Breadcrumbs navigation
│   │   ├── NotificationsDropdown.vue   # Notifications dropdown
│   │   └── UserDropdown.vue            # User profile dropdown
│   │
│   ├── Forms/                          # Single-source forms (reused in Pages and Modals)
│   │   ├── OrderForm.vue               # Order form
│   │   ├── PaymentForm.vue             # Payment form
│   │   ├── AssignmentForm.vue          # Assignment form
│   │   ├── ServiceForm.vue             # Service form
│   │   ├── CustomerForm.vue            # Customer form
│   │   ├── UserForm.vue                # User/Staff form
│   │   ├── CampaignForm.vue            # Campaign form
│   │   └── ChannelForm.vue             # Channel form
│   │
│   ├── Modals/                         # Modal wrappers for quick action forms
│   │   ├── Modal.vue                   # Base modal overlay
│   │   ├── OrderModal.vue              # OrderForm.vue modal wrapper
│   │   ├── PaymentModal.vue            # PaymentForm.vue modal wrapper
│   │   ├── AssignmentModal.vue         # AssignmentForm.vue modal wrapper
│   │   ├── ServiceModal.vue            # ServiceForm.vue modal wrapper
│   │   ├── CustomerModal.vue           # CustomerForm.vue modal wrapper
│   │   ├── UserModal.vue               # UserForm.vue modal wrapper
│   │   ├── CampaignModal.vue           # CampaignForm.vue modal wrapper
│   │   └── ChannelModal.vue            # ChannelForm.vue modal wrapper
│   │
│   ├── Tables/                         # Data tables
│   │   ├── OrdersTable.vue
│   │   ├── PaymentsTable.vue
│   │   ├── AssignmentsTable.vue
│   │   ├── ServicesTable.vue
│   │   ├── CustomersTable.vue
│   │   ├── UsersTable.vue
│   │   ├── CampaignsTable.vue
│   │   └── ChannelsTable.vue
│   │
│   ├── Widgets/                        # Charts and dashboard widgets
│   │   ├── MetricCard.vue
│   │   ├── RevenueLineChart.vue
│   │   └── ChannelDoughnutChart.vue
│   │
│   └── UI/                             # Atomic UI elements
│       ├── PrimaryButton.vue
│       ├── SecondaryButton.vue
│       ├── DangerButton.vue
│       ├── TextInput.vue
│       ├── SelectInput.vue
│       ├── StatusBadge.vue
│       ├── Pagination.vue
│       └── Toast.vue
│
└── Pages/                              # Entry points (Inertia Pages)
    ├── Analytics/ (Index.vue, Show.vue)
    ├── Orders/    (Index.vue, Show.vue, Create.vue, Edit.vue)
    ├── Payments/  (Index.vue, Show.vue)
    ├── Services/  (Index.vue, Show.vue, Create.vue, Edit.vue)
    ├── Customers/ (Index.vue, Show.vue, Create.vue, Edit.vue)
    ├── Users/     (Index.vue, Show.vue, Create.vue, Edit.vue)
    ├── Profile/   (Edit.vue + Partials/) # Out-of-the-box Breeze profile
    ├── Campaigns/ (Index.vue, Show.vue, Create.vue, Edit.vue)
    └── Channels/  (Index.vue, Show.vue, Create.vue, Edit.vue)

    1. Маркетинг & Аналитика (/analytics)Связь с базой: Агрегированные данные из orders, payments, campaigns, channels.  Главный дашборд: /analytics — сводный экран с карточками метрик (Total Revenue, Ads Spend, ROAS, CAC) и графиками Chart.js («Выручка vs Расходы», «Конверсия по Каналам»).  Детализация периода / снимка: /analytics/{period} — страница детализации финансовых показателей за выбранный временной срез (Сегодня, 7 Дней, 30 Дней).  2. Заказы, Финансы & Исполнение (/orders, /payments, /assignments)Связь с базой: Модели Order, OrderService, Payment, Assignment.  Реестр заказов: /orders — таблица всех сделок с поиском, фильтрами по статусам (status: new, in_progress, completed) и оплате (payment_status: paid, unpaid).  Детализация заказа: /orders/{id} — отдельная карточка заказа с составом услуг из order_service, суммой total_amount, скидкой discount_amount, привязанной кампаниями campaign_id, проведенными платежами из payments и статусом поручения из assignments.  Детализация платежа: /payments/{id} — карточка конкретной транзакции (amount, method, paid_at).  Модальное окно заказа (#order-modal): OrderModal — форма создания заказа с выбором клиента (customer_id), назначением исполнителя (user_id), выбором услуг (order_service), скидкой и кампанией.  3. Услуги & Квалификации (/services)Связь с базой: Модели Service и ServiceUser (Pivot квалификаций).  Каталог услуг: /services — сетка карточек услуг с базовой ценой (price), длительностью и статусом is_active.  Детализация услуги: /services/{id} — отдельная карточка услуги, статистика её продаж в заказах (order_service) и список квалифицированных сотрудников из таблицы service_user.  Модальное окно услуги: ServiceModal — форма добавления/редактирования услуги (name, price, is_active) и привязки допустимых исполнителей (service_user).  4. База Клиентов (/customers)Связь с базой: Модель Customer (связи с channels, campaigns, orders).  Реестр клиентов: /customers — таблица базы B2C клиентов с контактами (phone, email), первичным каналом (channel_id) и точкой входа (entry_point / промокод).  Профиль клиента: /customers/{id} — отдельная карточка клиента, история всех его заказов из orders, итоговый LTV и источник первого контакта (channel_id, campaign_id).  Модальное окно клиента: CustomerModal — форма создания/редактирования профиля клиента (name, phone, email, channel_id, campaign_id, entry_point).  5. Команда & Исполнители (/users)Связь с базой: Модель User (связи с assignments и service_user).  Реестр сотрудников: /users — таблица пользователей системы с ролями (role: admin, manager, executor), статусом доступа is_active и счетчиком активных заказов.  Профиль сотрудника: /users/{id} — отдельный экран сотрудника, его квалификации из service_user и текущие/завершенные поручения из assignments.  Модальное окно сотрудника: UserModal — форма добавления/редактирования пользователя (name, email, password, role, phone, is_active).  6. Кампании & Каналы Трафика (/campaigns, /channels)Связь с базой: Модели Campaign и Channel.  Менеджер кампаний и каналов: /campaigns — сводный экран управления источниками трафика (channels) и привязанными маркетинговыми акциями (campaigns) с их бюджетами и промокодами.  Детализация кампании: /campaigns/{id} — отдельная карточка акции с бюджетом (budget), датами проведения (start_date, end_date), промокодом и количеством привлеченных клиентов/заказов.  Детализация канала: /channels/{id} — отдельная карточка источника трафика (name, type: online/offline, is_active) со списком всех его кампаний.  Модальное окно кампании: CampaignModal — форма настройки акции (name, channel_id, promo_code, budget, start_date, end_date).  Модальное окно канала: ChannelModal — форма добавления/редактирования источника трафика (name, type, is_active).

    # ApexCRM Route & Endpoint Specification

## 1. Marketing & Analytics (AnalyticsController)

| HTTP Method | URI Route | Controller & Method | Description / Purpose |
| :--- | :--- | :--- | :--- |
| `GET` | `/analytics` | `AnalyticsController@index` | Main end-to-end analytics dashboard and performance charts |
| `GET` | `/analytics/{period}` | `AnalyticsController@show` | Financial metrics breakdown by period (`today`, `7days`, `30days`) |

---

## 2. Orders, Payments & Execution (OrderController, PaymentController, AssignmentController)

### 2.1. Orders (OrderController)

| HTTP Method | URI Route | Controller & Method | Description / Purpose |
| :--- | :--- | :--- | :--- |
| `GET` | `/orders` | `OrderController@index` | Order registry with status and payment filtering |
| `GET` | `/orders/create` | `OrderController@create` | Form data for order creation modal |
| `POST` | `/orders` | `OrderController@store` | Store new order and attach services (`order_service`) |
| `GET` | `/orders/{id}` | `OrderController@show` | Order details: card `#ORD-XXXX`, service items, payments, and assignments |
| `GET` | `/orders/{id}/edit` | `OrderController@edit` | Data for order editing modal/form |
| `PUT/PATCH` | `/orders/{id}` | `OrderController@update` | Update status, service items, discount, or assigned executor |
| `DELETE` | `/orders/{id}` | `OrderController@destroy` | Delete / cancel order |

### 2.2. Payments (PaymentController)

| HTTP Method | URI Route | Controller & Method | Description / Purpose |
| :--- | :--- | :--- | :--- |
| `POST` | `/orders/{order}/payments` | `PaymentController@store` | Process new payment for an order |
| `GET` | `/payments/{id}` | `PaymentController@show` | Payment details: receipt, payment method, date |

### 2.3. Assignments & Workload (AssignmentController)

| HTTP Method | URI Route | Controller & Method | Description / Purpose |
| :--- | :--- | :--- | :--- |
| `POST` | `/orders/{order}/assign` | `AssignmentController@store` | Assign executor (`users`) to an order |
| `PATCH` | `/assignments/{id}/status` | `AssignmentController@updateStatus` | Update execution status (`pending`, `in_progress`, `done`) |

---

## 3. Service Catalog & Qualifications (ServiceController)

| HTTP Method | URI Route | Controller & Method | Description / Purpose |
| :--- | :--- | :--- | :--- |
| `GET` | `/services` | `ServiceController@index` | System service catalog |
| `GET` | `/services/create` | `ServiceController@create` | Data for service creation modal |
| `POST` | `/services` | `ServiceController@store` | Store new service |
| `GET` | `/services/{id}` | `ServiceController@show` | Service details: sales stats, qualified employees (`service_user`) |
| `GET` | `/services/{id}/edit` | `ServiceController@edit` | Data for service edit form |
| `PUT/PATCH` | `/services/{id}` | `ServiceController@update` | Update price, title, and qualified executors list |
| `DELETE` | `/services/{id}` | `ServiceController@destroy` | Deactivate / remove service from catalog |

---

## 4. Customer Base (CustomerController)

| HTTP Method | URI Route | Controller & Method | Description / Purpose |
| :--- | :--- | :--- | :--- |
| `GET` | `/customers` | `CustomerController@index` | Customer registry and search |
| `GET` | `/customers/create` | `CustomerController@create` | Data for customer creation modal |
| `POST` | `/customers` | `CustomerController@store` | Add new customer (Full Name, phone, channel) |
| `GET` | `/customers/{id}` | `CustomerController@show` | Customer profile: full order history, LTV, acquisition source |
| `GET` | `/customers/{id}/edit` | `CustomerController@edit` | Data for customer edit form |
| `PUT/PATCH` | `/customers/{id}` | `CustomerController@update` | Update customer contact information |
| `DELETE` | `/customers/{id}` | `CustomerController@destroy` | Delete customer profile |

---

## 5. Team & Users (UserController)

| HTTP Method | URI Route | Controller & Method | Description / Purpose |
| :--- | :--- | :--- | :--- |
| `GET` | `/users` | `UserController@index` | List team members, roles (`ADMIN`, `EXECUTOR`), and current workload |
| `GET` | `/users/create` | `UserController@create` | Data for employee creation modal |
| `POST` | `/users` | `UserController@store` | Store new system user |
| `GET` | `/users/{id}` | `UserController@show` | Employee profile: qualifications (`service_user`), assignments list (`assignments`) |
| `GET` | `/users/{id}/edit` | `UserController@edit` | Data for employee edit form |
| `PUT/PATCH` | `/users/{id}` | `UserController@update` | Update role, contact details, or `is_active` flag |
| `DELETE` | `/users/{id}` | `UserController@destroy` | Block / soft-delete user |

---

## 6. Marketing Campaigns (CampaignController)

| HTTP Method | URI Route | Controller & Method | Description / Purpose |
| :--- | :--- | :--- | :--- |
| `GET` | `/campaigns` | `CampaignController@index` | Summary registry of marketing campaigns and budgets |
| `GET` | `/campaigns/create` | `CampaignController@create` | Data for campaign creation modal |
| `POST` | `/campaigns` | `CampaignController@store` | Store new marketing campaign |
| `GET` | `/campaigns/{id}` | `CampaignController@show` | Campaign details: budget, expenditure, attached orders, ROMI |
| `GET` | `/campaigns/{id}/edit` | `CampaignController@edit` | Data for campaign edit form |
| `PUT/PATCH` | `/campaigns/{id}` | `CampaignController@update` | Update budget, promo code, or date range |
| `DELETE` | `/campaigns/{id}` | `CampaignController@destroy` | Delete marketing campaign |

---

## 7. Traffic Channels (ChannelController)

| HTTP Method | URI Route | Controller & Method | Description / Purpose |
| :--- | :--- | :--- | :--- |
| `GET` | `/channels` | `ChannelController@index` | Registry of acquisition sources (online/offline) |
| `GET` | `/channels/create` | `ChannelController@create` | Data for channel creation modal |
| `POST` | `/channels` | `ChannelController@store` | Add new traffic channel |
| `GET` | `/channels/{id}` | `ChannelController@show` | Channel details: list of associated campaigns and primary customers |
| `GET` | `/channels/{id}/edit` | `ChannelController@edit` | Data for channel edit form |
| `PUT/PATCH` | `/channels/{id}` | `ChannelController@update` | Update channel name, type, or `is_active` status |
| `DELETE` | `/channels/{id}` | `ChannelController@destroy` | Delete traffic source |