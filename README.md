# Retail Counter API

Laravel application for a retail counter: record customer orders against a product catalog and keep stock in sync.

This is a take-home assignment for a Laravel Developer role at Mallow Technologies. Scope is limited to the assignment: catalog, customers, order creation, stock safety, order history, low-stock reporting, a queued confirmation job, and tests.

## Requirements

- PHP 8.3+
- Composer
- SQLite (default; no extra database server is required)
- PHP extensions: `bcmath`, `pdo_sqlite`, `sqlite3`, `mbstring`, `openssl`, `curl`, `zip`

MySQL or PostgreSQL can be used instead of SQLite by changing `.env`. Row-level locking is intended for those engines.

## Installation / setup

```bash
composer install
copy .env.example .env   # Windows
# cp .env.example .env   # macOS / Linux
php artisan key:generate
```

Create the SQLite database file if it does not exist:

```bash
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
```

On Windows PowerShell:

```powershell
if (-not (Test-Path database\database.sqlite)) { New-Item database\database.sqlite -ItemType File }
```

A frontend build (`npm`) is not required. The optional demonstration page is plain Blade/HTML.

## Environment configuration

Copy `.env.example` to `.env`. Important values:

| Variable | Purpose | Default |
| --- | --- | --- |
| `APP_KEY` | Application encryption key | generated |
| `DB_CONNECTION` | Database driver | `sqlite` |
| `QUEUE_CONNECTION` | Queue driver | `database` |
| `MAIL_MAILER` | Mailer (unused for real SMTP) | `log` |
| `LOW_STOCK_THRESHOLD` | Default low-stock cutoff | `5` |

Do not commit `.env`. It is gitignored.

## Database setup, migrations, and seeders

```bash
php artisan migrate
php artisan db:seed
```

Or together:

```bash
php artisan migrate:fresh --seed
```

Seed data includes eight sample products and four customers.

## Queue setup / how to run locally

Order confirmation is a queued job (`App\Jobs\SendOrderConfirmationJob`). Production-style local config uses the `database` queue (`QUEUE_CONNECTION=database`). The `jobs` table is created by Laravel's default migrations.

After creating an order, process the queue:

```bash
php artisan queue:work
```

The job does **not** send real email. It writes a log line such as:

`Simulated order confirmation email.`

with `order_id`, `customer_email`, and `grand_total`.

PHPUnit sets `QUEUE_CONNECTION=sync` so tests do not need a worker. Dispatch is asserted with `Bus::fake()`, so the HTTP tests do not run the job body.

## How to run tests

```bash
php artisan test
```

or:

```bash
vendor/bin/phpunit
```

Tests use an in-memory SQLite database (`phpunit.xml`).

## Run the application

```bash
php artisan serve
```

- Demonstration UI: `http://localhost:8000/`
- Health: `http://localhost:8000/up`

## Demo / Recording

A short demonstration of the application and workflow is available here:

- Loom: [Retail Counter API demo](https://www.loom.com/share/99961c212ef84c71a6e49e69e4d43e4a)

## API endpoints

All JSON APIs are under `/api`.

| Method | Path | Description |
| --- | --- | --- |
| `POST` | `/api/orders` | Create an order |
| `GET` | `/api/customers/{email}/orders` | Customer order history |
| `GET` | `/api/products/low-stock` | Products below a stock threshold |

### Create order

`POST /api/orders`

```json
{
  "customer": {
    "name": "Anita Sharma",
    "email": "anita.sharma@example.com"
  },
  "items": [
    { "product_id": 1, "quantity": 2 },
    { "product_id": 2, "quantity": 4 }
  ]
}
```

Successful response: `201 Created`

```json
{
  "data": {
    "id": 1,
    "customer": {
      "id": 1,
      "name": "Anita Sharma",
      "email": "anita.sharma@example.com"
    },
    "subtotal": "548.00",
    "tax": "98.64",
    "grand_total": "646.64",
    "items": [
      {
        "id": 1,
        "product_id": 1,
        "product_name": "A4 Copy Paper Ream",
        "product_code": "STN-A4-001",
        "quantity": 2,
        "unit_price": "249.00",
        "tax_percentage": "18.00",
        "tax_amount": "89.64",
        "line_subtotal": "498.00",
        "line_total": "587.64"
      },
      {
        "id": 2,
        "product_id": 2,
        "product_name": "Blue Ballpoint Pen",
        "product_code": "STN-PEN-002",
        "quantity": 4,
        "unit_price": "12.50",
        "tax_percentage": "18.00",
        "tax_amount": "9.00",
        "line_subtotal": "50.00",
        "line_total": "59.00"
      }
    ],
    "created_at": "2026-09-12T08:00:00.000000Z"
  }
}
```

Validation failures return `422` with Laravel's standard `message` and `errors` object.

Insufficient stock returns `422`:

```json
{
  "message": "Insufficient stock for product 1: requested 2, available 1.",
  "error": "insufficient_stock",
  "product_id": 1,
  "requested_quantity": 2,
  "available_stock": 1
}
```

### Customer order history

`GET /api/customers/{email}/orders`

Encode `@` in the path (for example `anita.sharma%40example.com`). Unknown emails return `404`.

The response is a list of orders with line items. Product name/code on each line come from the current catalog via eager-loaded `items.product`. Unit price, tax percentage, and amounts are snapshots stored on the order line.

### Low stock

`GET /api/products/low-stock`

`GET /api/products/low-stock?threshold=10`

Products with `stock_on_hand` **strictly less than** the threshold are returned.

- If `threshold` is omitted, `config/inventory.php` is used (`LOW_STOCK_THRESHOLD`, default `5`).
- If `threshold` is provided, it overrides config for that request.
- `meta.threshold` in the response shows the value that was applied.

## Database design

```
customers
  id, name, email (unique), timestamps

products
  id, name, code (unique), price decimal(12,2),
  tax_percentage decimal(5,2), stock_on_hand unsigned int, timestamps
  index on stock_on_hand

orders
  id, customer_id (FK, restrict on delete),
  subtotal, tax, grand_total decimal(12,2), timestamps

order_items
  id, order_id (FK, cascade on delete),
  product_id (FK, restrict on delete),
  quantity, unit_price, tax_percentage, tax_amount,
  line_subtotal, line_total, timestamps
  unique (order_id, product_id)
```

Laravel default tables (`users`, `sessions`, `cache`, `jobs`, `failed_jobs`) remain for framework features. This assignment does not implement authentication.

Money columns use `decimal(12,2)`. Calculations use `brick/math` `BigDecimal` with scale 2 and `RoundingMode::HalfUp` rounding, not floating-point arithmetic.

Tax is calculated per line, then summed:

- `line_subtotal = unit_price * quantity`
- `tax_amount = line_subtotal * tax_percentage / 100`
- `line_total = line_subtotal + tax_amount`
- order `subtotal` / `tax` / `grand_total` are sums of the line values

## Concurrency / stock safety

Order creation runs in a single database transaction.

1. Resolve the customer.
2. Load requested products with `lockForUpdate()`, ordered by `id` ascending (consistent lock order to reduce deadlocks).
3. Reject the whole request if any line has insufficient stock. Nothing is committed.
4. Persist the order and order items using catalog price/tax snapshots.
5. Decrement stock with `WHERE stock_on_hand >= quantity` so stock cannot go negative even under races.
6. Commit.
7. Dispatch `SendOrderConfirmationJob` **after** the transaction returns so a rolled-back order cannot enqueue a confirmation.

If two requests try to buy the last unit at the same time, one transaction should succeed and the other should fail with `insufficient_stock`. Stock must not go negative.

SQLite (used by default in tests and local setup) serializes writers. `SELECT ... FOR UPDATE` is fully meaningful on MySQL/PostgreSQL. A dedicated parallel race test was not run because the PHPUnit in-memory SQLite setup cannot reliably reproduce concurrent writers. The production code path is still lock + atomic decrement.

## Architecture

- `CreateOrderRequest` — validation
- `OrderService` — customer resolution, locking, totals, stock deduction, job dispatch
- API controllers — HTTP only
- `OrderResource` / `OrderItemResource` / `ProductResource` — JSON shape
- `SendOrderConfirmationJob` — queued simulated email
- `App\Support\Money` — decimal math

## Assumptions

1. **Customer identity is email.** `firstOrCreate` by lowercased email. If the customer already exists, the stored name is kept; a different name on a later request is ignored.
2. **Duplicate `product_id` values in one request are rejected**, not merged. Send each product once with the intended quantity.
3. **Low-stock comparison is strictly less than** the threshold. A product with stock `5` is not low when the threshold is `5`.
4. **No authentication.** The assignment does not require login, roles, or payments.
5. **No real SMTP.** Confirmation is a log message from a queued job.
6. **Product name/code on responses** are current catalog values (eager loaded). Price and tax on the line are historical snapshots.
7. **Emails are normalized** to lowercase and trimmed before lookup/create.

## Known limitations

- Parallel oversell tests are not run in this PHPUnit/SQLite environment (see concurrency section).
- There is no product/customer admin CRUD API beyond seeding and order-time customer create.
- The demonstration UI is a single local page, not a styled storefront.
- SQLite `lockForUpdate` is weaker than MySQL/PostgreSQL row locks; use MySQL/PostgreSQL if this will be deployed with concurrent writers.

## AI-assisted development

AI was used during development and review of this assignment. The actual prompt screenshots are included in the repository under `prompts/`.

## Project layout (assignment code)

```
app/Http/Controllers/Api/   Order, history, low-stock endpoints
app/Http/Requests/          CreateOrderRequest
app/Http/Resources/         JSON resources
app/Services/OrderService.php
app/Jobs/SendOrderConfirmationJob.php
app/Models/                 Customer, Product, Order, OrderItem
app/Support/Money.php
config/inventory.php
routes/api.php
database/migrations/        customers, products, orders, order_items
database/factories/
database/seeders/
tests/Feature/
tests/Unit/MoneyTest.php
```
