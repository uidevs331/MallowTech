<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Retail Counter</title>
    <style>
        :root {
            --ink: #12202b;
            --muted: #5b6b76;
            --line: #d7e0e6;
            --paper: #f4f7f5;
            --card: #ffffff;
            --accent: #0f6b5c;
            --accent-dark: #0b5247;
            --warn: #9a3412;
            --warn-bg: #fff4ed;
            --ok: #166534;
            --ok-bg: #ecfdf3;
            --shadow: 0 10px 30px rgba(18, 32, 43, 0.06);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", system-ui, sans-serif;
            color: var(--ink);
            background: var(--paper);
        }
        header {
            background: var(--ink);
            color: #fff;
            padding: 1.1rem 1.5rem;
        }
        header strong { font-size: 1.15rem; letter-spacing: .02em; }
        header p { margin: .25rem 0 0; color: #c5d0d6; font-size: .92rem; }
        main {
            max-width: 1180px;
            margin: 0 auto;
            padding: 1.25rem;
            display: grid;
            grid-template-columns: 1.4fr .9fr;
            gap: 1rem;
        }
        @media (max-width: 900px) { main { grid-template-columns: 1fr; } }
        .card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 14px;
            box-shadow: var(--shadow);
            padding: 1rem 1.1rem;
        }
        h2 { margin: 0 0 .75rem; font-size: 1.05rem; }
        table { width: 100%; border-collapse: collapse; font-size: .92rem; }
        th, td { text-align: left; padding: .55rem .4rem; border-bottom: 1px solid var(--line); }
        th { color: var(--muted); font-weight: 600; font-size: .78rem; text-transform: uppercase; letter-spacing: .04em; }
        .stock { display: inline-block; padding: .15rem .45rem; border-radius: 999px; font-size: .78rem; font-weight: 600; }
        .stock-ok { background: var(--ok-bg); color: var(--ok); }
        .stock-low { background: var(--warn-bg); color: var(--warn); }
        .add {
            border: 0;
            background: var(--accent);
            color: #fff;
            border-radius: 8px;
            padding: .35rem .65rem;
            cursor: pointer;
        }
        .add:hover { background: var(--accent-dark); }
        label { display: block; font-size: .82rem; color: var(--muted); margin: .55rem 0 .2rem; }
        input {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: .5rem .6rem;
            font: inherit;
        }
        .actions { display: flex; gap: .5rem; margin-top: .9rem; }
        .primary, .ghost, .linkish {
            border-radius: 8px;
            padding: .55rem .8rem;
            font: inherit;
            cursor: pointer;
        }
        .primary { border: 0; background: var(--accent); color: #fff; flex: 1; }
        .primary:hover { background: var(--accent-dark); }
        .ghost { border: 1px solid var(--line); background: #fff; }
        .empty { color: var(--muted); font-size: .92rem; }
        .receipt {
            background: #f8faf9;
            border: 1px dashed var(--line);
            border-radius: 10px;
            padding: .85rem;
            min-height: 8rem;
            font-size: .9rem;
            white-space: pre-wrap;
        }
        .ok-text { color: var(--ok); }
        .err-text { color: var(--warn); }
        .hint { margin: 0 0 .75rem; color: var(--muted); font-size: .85rem; }
        .qty { width: 4.2rem; }
        .remove { border: 0; background: transparent; color: var(--warn); cursor: pointer; }
        .split { display: grid; grid-template-columns: 1fr 1fr; gap: .6rem; }
        @media (max-width: 600px) { .split { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <header>
        <strong>Retail Counter</strong>
        <p>Record customer orders, keep catalog stock in sync, and look up history.</p>
    </header>

    <main>
        <section class="card">
            <h2>Product catalog</h2>
            <p class="hint">Stock below {{ $lowStockThreshold }} is marked low. Click Add to put a product on the order.</p>
            @if ($products->isEmpty())
                <p class="empty">No products found. Run <code>php artisan migrate --seed</code>.</p>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Code</th>
                            <th>Price</th>
                            <th>Tax</th>
                            <th>Stock</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            @php $isLow = $product->stock_on_hand < $lowStockThreshold; @endphp
                            <tr data-product-row="{{ $product->id }}">
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->code }}</td>
                                <td>₹{{ $product->price }}</td>
                                <td>{{ $product->tax_percentage }}%</td>
                                <td>
                                    <span class="stock {{ $isLow ? 'stock-low' : 'stock-ok' }}" data-stock="{{ $product->id }}">
                                        {{ $product->stock_on_hand }}
                                    </span>
                                </td>
                                <td>
                                    <button
                                        type="button"
                                        class="add"
                                        data-add
                                        data-id="{{ $product->id }}"
                                        data-name="{{ $product->name }}"
                                        data-stock="{{ $product->stock_on_hand }}"
                                    >Add</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>

        <aside>
            <section class="card">
                <h2>New order</h2>
                <div class="split">
                    <div>
                        <label for="customer-name">Customer name</label>
                        <input id="customer-name" value="Anita Sharma" autocomplete="name">
                    </div>
                    <div>
                        <label for="customer-email">Customer email</label>
                        <input id="customer-email" type="email" value="anita.sharma@example.com" autocomplete="email">
                    </div>
                </div>

                <h2 style="margin-top:1rem;">Order lines</h2>
                <table>
                    <thead>
                        <tr><th>Item</th><th>Qty</th><th></th></tr>
                    </thead>
                    <tbody id="cart-body">
                        <tr id="cart-empty"><td colspan="3" class="empty">No items yet.</td></tr>
                    </tbody>
                </table>

                <div class="actions">
                    <button type="button" class="primary" id="place-order">Place order</button>
                    <button type="button" class="ghost" id="clear-cart">Clear</button>
                </div>
            </section>

            <section class="card" style="margin-top:1rem;">
                <h2>Result</h2>
                <div id="result" class="receipt">Place an order to see the receipt here.</div>
            </section>

            <section class="card" style="margin-top:1rem;">
                <h2>Customer history</h2>
                <label for="history-email">Email</label>
                <input id="history-email" type="email" value="anita.sharma@example.com">
                <div class="actions">
                    <button type="button" class="primary" id="load-history">Load orders</button>
                </div>
                <div id="history" class="receipt" style="margin-top:.75rem;">No history loaded.</div>
            </section>
        </aside>
    </main>

    <script>
        const cart = new Map();
        const cartBody = document.getElementById('cart-body');
        const resultEl = document.getElementById('result');

        function renderCart() {
            cartBody.innerHTML = '';
            if (cart.size === 0) {
                cartBody.innerHTML = '<tr id="cart-empty"><td colspan="3" class="empty">No items yet.</td></tr>';
                return;
            }
            cart.forEach((item) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${item.name}</td>
                    <td><input class="qty" type="number" min="1" value="${item.quantity}" data-qty="${item.id}"></td>
                    <td><button type="button" class="remove" data-remove="${item.id}">Remove</button></td>
                `;
                cartBody.appendChild(row);
            });
        }

        document.querySelectorAll('[data-add]').forEach((button) => {
            button.addEventListener('click', () => {
                const id = Number(button.dataset.id);
                const existing = cart.get(id);
                cart.set(id, {
                    id,
                    name: button.dataset.name,
                    quantity: existing ? existing.quantity + 1 : 1,
                });
                renderCart();
            });
        });

        cartBody.addEventListener('input', (event) => {
            const id = Number(event.target.dataset.qty);
            if (!id) return;
            const item = cart.get(id);
            if (!item) return;
            item.quantity = Math.max(1, Number(event.target.value || 1));
            cart.set(id, item);
        });

        cartBody.addEventListener('click', (event) => {
            const id = Number(event.target.dataset.remove);
            if (!id) return;
            cart.delete(id);
            renderCart();
        });

        document.getElementById('clear-cart').addEventListener('click', () => {
            cart.clear();
            renderCart();
        });

        document.getElementById('place-order').addEventListener('click', async () => {
            if (cart.size === 0) {
                resultEl.className = 'receipt err-text';
                resultEl.textContent = 'Add at least one product before placing the order.';
                return;
            }

            const payload = {
                customer: {
                    name: document.getElementById('customer-name').value,
                    email: document.getElementById('customer-email').value,
                },
                items: Array.from(cart.values()).map((item) => ({
                    product_id: item.id,
                    quantity: item.quantity,
                })),
            };

            const response = await fetch('/api/orders', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(payload),
            });
            const body = await response.json();

            if (!response.ok) {
                resultEl.className = 'receipt err-text';
                resultEl.textContent = formatError(response.status, body);
                return;
            }

            const order = body.data;
            resultEl.className = 'receipt ok-text';
            resultEl.textContent = [
                'Order #' + order.id + ' created',
                'Customer: ' + order.customer.name + ' <' + order.customer.email + '>',
                'Subtotal: ₹' + order.subtotal,
                'Tax: ₹' + order.tax,
                'Grand total: ₹' + order.grand_total,
                '',
                ...order.items.map((line) =>
                    line.quantity + ' x ' + line.product_name + ' @ ₹' + line.unit_price + ' = ₹' + line.line_total
                ),
            ].join('\n');

            payload.items.forEach((item) => {
                const badge = document.querySelector('[data-stock="' + item.product_id + '"]');
                if (!badge) return;
                const next = Number(badge.textContent) - item.quantity;
                badge.textContent = String(next);
                badge.className = 'stock ' + (next < {{ $lowStockThreshold }} ? 'stock-low' : 'stock-ok');
            });

            cart.clear();
            renderCart();
        });

        document.getElementById('load-history').addEventListener('click', async () => {
            const email = document.getElementById('history-email').value.trim();
            const historyEl = document.getElementById('history');
            const response = await fetch('/api/customers/' + encodeURIComponent(email) + '/orders', {
                headers: { 'Accept': 'application/json' },
            });
            const body = await response.json();

            if (!response.ok) {
                historyEl.className = 'receipt err-text';
                historyEl.textContent = response.status === 404
                    ? 'No customer found for that email.'
                    : formatError(response.status, body);
                return;
            }

            if (!body.data.length) {
                historyEl.className = 'receipt';
                historyEl.textContent = 'This customer has no orders yet.';
                return;
            }

            historyEl.className = 'receipt';
            historyEl.textContent = body.data.map((order) => {
                const lines = order.items.map((line) =>
                    '  ' + line.quantity + ' x ' + line.product_name + ' = ₹' + line.line_total
                ).join('\n');
                return 'Order #' + order.id + ' | ₹' + order.grand_total + '\n' + lines;
            }).join('\n\n');
        });

        function formatError(status, body) {
            if (body.error === 'insufficient_stock') {
                return 'Insufficient stock for product ' + body.product_id +
                    ' (requested ' + body.requested_quantity + ', available ' + body.available_stock + ').';
            }
            if (body.errors) {
                return Object.values(body.errors).flat().join('\n');
            }
            return 'HTTP ' + status + '\n' + (body.message || JSON.stringify(body, null, 2));
        }
    </script>
</body>
</html>
