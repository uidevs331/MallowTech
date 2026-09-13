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
            --paper: #eef2f0;
            --card: #ffffff;
            --accent: #0f6b5c;
            --accent-dark: #0b5247;
            --warn: #9a3412;
            --warn-bg: #fff4ed;
            --ok: #166534;
            --ok-bg: #ecfdf3;
        }
        * { box-sizing: border-box; }
        html, body {
            height: 100%;
            margin: 0;
            overflow: hidden;
            font-family: "Segoe UI", system-ui, sans-serif;
            color: var(--ink);
            background: linear-gradient(180deg, #eef3f2 0%, #f6f8f8 100%);
        }
        .page {
            height: 100%;
            display: grid;
            grid-template-rows: auto 1fr;
        }
        header {
            background: linear-gradient(135deg, #12202b 0%, #1a2f3d 100%);
            color: #fff;
            padding: .55rem 1rem;
            display: flex;
            align-items: baseline;
            gap: .75rem;
            border-bottom: 1px solid rgba(255,255,255,.08);
            box-shadow: 0 1px 0 rgba(18,32,43,.12);
        }
        header strong { font-size: 1rem; }
        header span { color: #c5d0d6; font-size: .8rem; }
        main {
            min-height: 0;
            display: grid;
            grid-template-columns: 1.35fr 1fr;
            gap: .75rem;
            padding: .75rem;
            height: 100%;
        }
        @media (max-width: 980px) {
            html, body { overflow: auto; }
            .page { height: auto; min-height: 100%; }
            main { grid-template-columns: 1fr; }
        }
        .card {
            background: rgba(255,255,255,.95);
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: .7rem .8rem;
            min-height: 0;
            display: flex;
            flex-direction: column;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04);
        }
        .panel {
            min-height: 0;
            display: grid;
            gap: .75rem;
            grid-template-rows: auto minmax(0, 1fr) minmax(0, 1.2fr);
        }
        h2 {
            margin: 0 0 .4rem;
            font-size: .92rem;
        }
        .hint {
            margin: 0 0 .4rem;
            color: var(--muted);
            font-size: .75rem;
        }
        .scroll {
            min-height: 0;
            overflow: auto;
        }
        .receipt,
        .history-shell {
            background: #f8faf9;
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: .65rem .7rem;
            min-height: 0;
            max-height: 100%;
            overflow: auto;
            flex: 1;
        }
        .history-shell {
            display: flex;
            flex-direction: column;
            gap: .3rem;
            background: #f8faf9;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: .25rem;
            min-height: 0;
            max-height: 12rem;
            overflow: auto;
        }
        .history-order {
            display: grid;
            grid-template-columns: 1fr 1.8fr auto;
            gap: .35rem;
            align-items: center;
            border-top: 1px solid var(--line);
            background: transparent;
            border-radius: 0;
            padding: .42rem 0;
        }
        .history-order:first-child {
            border-top: 0;
            padding-top: 0;
        }
        .history-cell {
            min-width: 0;
        }
        .history-tag {
            display: inline-block;
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: var(--muted);
        }
        .history-meta {
            font-size: .66rem;
            color: var(--muted);
            line-height: 1.3;
        }
        .history-lines {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            gap: .14rem;
        }
        .history-lines li {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            font-size: .72rem;
            color: var(--ink);
        }
        .history-total {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            min-height: 100%;
            font-size: .76rem;
            font-weight: 700;
            color: var(--ink);
            white-space: nowrap;
        }
        .history-empty {
            border: 1px dashed var(--line);
            border-radius: 8px;
            background: #f9fbfb;
            color: var(--muted);
            font-size: .76rem;
            padding: .7rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: .8rem;
        }
        th, td {
            text-align: left;
            padding: .28rem .3rem;
            border-bottom: 1px solid var(--line);
            vertical-align: middle;
        }
        th {
            color: var(--muted);
            font-weight: 600;
            font-size: .68rem;
            text-transform: uppercase;
            letter-spacing: .03em;
            position: sticky;
            top: 0;
            background: var(--card);
        }
        .stock {
            display: inline-block;
            padding: .1rem .35rem;
            border-radius: 999px;
            font-size: .72rem;
            font-weight: 600;
        }
        .stock-ok { background: var(--ok-bg); color: var(--ok); }
        .stock-low { background: var(--warn-bg); color: var(--warn); }
        .add, .primary, .ghost, .remove {
            font: inherit;
            cursor: pointer;
        }
        .add {
            border: 0;
            background: var(--accent);
            color: #fff;
            border-radius: 6px;
            padding: .2rem .45rem;
            font-size: .75rem;
        }
        .add:hover, .primary:hover { background: var(--accent-dark); }
        label {
            display: block;
            font-size: .72rem;
            color: var(--muted);
            margin: 0 0 .15rem;
        }
        input {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 6px;
            padding: .35rem .45rem;
            font: inherit;
            font-size: .82rem;
        }
        .actions {
            display: flex;
            gap: .4rem;
            margin-top: .45rem;
        }
        .primary {
            border: 0;
            background: var(--accent);
            color: #fff;
            border-radius: 6px;
            padding: .4rem .65rem;
            flex: 1;
            font-size: .82rem;
        }
        .ghost {
            border: 1px solid var(--line);
            background: #fff;
            border-radius: 6px;
            padding: .4rem .65rem;
            font-size: .82rem;
        }
        .empty { color: var(--muted); font-size: .8rem; }
        .receipt {
            background: #f8faf9;
            border: 1px dashed var(--line);
            border-radius: 10px;
            padding: .65rem .7rem;
            font-size: .78rem;
            white-space: pre-wrap;
            min-height: 0;
            overflow: auto;
            flex: 1;
        }
        .ok-text { color: var(--ok); }
        .err-text { color: var(--warn); }
        .qty { width: 3.6rem; }
        .remove {
            border: 0;
            background: transparent;
            color: var(--warn);
            font-size: .75rem;
            padding: 0;
        }
        .split {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .4rem;
        }
        .row-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            margin-bottom: .35rem;
        }
        .row-title h2 { margin: 0; }
        .inline-actions {
            display: flex;
            gap: .35rem;
            align-items: end;
        }
        .inline-actions > div { flex: 1; }
        .inline-actions .primary { flex: 0 0 auto; }
        .cart-wrap { max-height: 7.5rem; overflow: auto; margin-top: .35rem; }
    </style>
</head>
<body>
    <div class="page">
        <header>
            <strong>Retail Counter</strong>
            <span>Catalog · order · receipt · history on one screen</span>
        </header>

        <main>
            <section class="card">
                <h2>Product catalog</h2>
                <p class="hint">Stock below {{ $lowStockThreshold }} is low. Use Add, then Place order.</p>
                <div class="scroll">
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
                                    <tr>
                                        <td>{{ $product->name }}</td>
                                        <td>{{ $product->code }}</td>
                                        <td>₹{{ $product->price }}</td>
                                        <td>{{ $product->tax_percentage }}%</td>
                                        <td>
                                            <span class="stock {{ $isLow ? 'stock-low' : 'stock-ok' }}" data-stock-badge="{{ $product->id }}">
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
                                            >Add</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </section>

            <aside class="panel">
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
                    <div class="row-title" style="margin-top:.45rem;">
                        <h2>Order lines</h2>
                    </div>
                    <div class="cart-wrap">
                        <table>
                            <thead>
                                <tr><th>Item</th><th>Qty</th><th></th></tr>
                            </thead>
                            <tbody id="cart-body">
                                <tr id="cart-empty"><td colspan="3" class="empty">No items yet.</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="actions">
                        <button type="button" class="primary" id="place-order">Place order</button>
                        <button type="button" class="ghost" id="clear-cart">Clear</button>
                    </div>
                </section>

                <section class="card">
                    <div class="row-title">
                        <h2>Result</h2>
                    </div>
                    <div id="result" class="receipt">Place an order to see the receipt here.</div>
                </section>

                <section class="card">
                    <div class="row-title">
                        <h2>Customer history</h2>
                    </div>
                    <div class="inline-actions">
                        <div>
                            <label for="history-email">Email</label>
                            <input id="history-email" type="email" value="anita.sharma@example.com">
                        </div>
                        <button type="button" class="primary" id="load-history">Load</button>
                    </div>
                    <div id="history" class="history-shell" style="margin-top:.45rem;">
                        <div class="history-empty">No history loaded.</div>
                    </div>
                </section>
            </aside>
        </main>
    </div>

    <script>
        const cart = new Map();
        const cartBody = document.getElementById('cart-body');
        const resultEl = document.getElementById('result');
        const historyEl = document.getElementById('history');
        const customerEmail = document.getElementById('customer-email');
        const historyEmail = document.getElementById('history-email');
        const lowStockThreshold = {{ (int) $lowStockThreshold }};

        customerEmail.addEventListener('change', () => {
            historyEmail.value = customerEmail.value.trim();
        });

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
                    <td><input class="qty" type="number" min="1" step="1" value="${item.quantity}" data-qty="${item.id}"></td>
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
            item.quantity = Math.max(1, Math.floor(Number(event.target.value || 1)));
            event.target.value = String(item.quantity);
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
                    email: customerEmail.value,
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

            order.items.forEach((line) => {
                const badge = document.querySelector('[data-stock-badge="' + line.product_id + '"]');
                if (!badge) return;
                const next = Number(badge.textContent) - Number(line.quantity);
                badge.textContent = String(next);
                badge.className = 'stock ' + (next < lowStockThreshold ? 'stock-low' : 'stock-ok');
            });

            historyEmail.value = order.customer.email;
            cart.clear();
            renderCart();
        });

        document.getElementById('load-history').addEventListener('click', async () => {
            const email = historyEmail.value.trim();
            const response = await fetch('/api/customers/' + encodeURIComponent(email) + '/orders', {
                headers: { 'Accept': 'application/json' },
            });
            const body = await response.json();

            if (!response.ok) {
                historyEl.className = 'history-shell';
                historyEl.innerHTML = '<div class="history-empty err-text">' + escapeHtml(
                    response.status === 404
                        ? 'No customer found for that email.'
                        : formatError(response.status, body)
                ) + '</div>';
                return;
            }

            if (!body.data.length) {
                historyEl.className = 'history-shell';
                historyEl.innerHTML = '<div class="history-empty">This customer has no orders yet.</div>';
                return;
            }

            historyEl.className = 'history-shell';
            historyEl.innerHTML = body.data.map((order) => {
                const lines = order.items.map((line) =>
                    '<li><span>' + escapeHtml(line.quantity + ' × ' + line.product_name) + '</span><span>₹' + escapeHtml(line.line_total) + '</span></li>'
                ).join('');
                return '<article class="history-order">' +
                    '<div class="history-cell">' +
                        '<div class="history-tag">Order #' + escapeHtml(order.id) + '</div>' +
                        '<div class="history-meta">Customer history</div>' +
                    '</div>' +
                    '<div class="history-cell">' +
                        '<ul class="history-lines">' + lines + '</ul>' +
                    '</div>' +
                    '<div class="history-cell history-total">₹' + escapeHtml(order.grand_total) + '</div>' +
                    '</article>';
            }).join('');
        });

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/\"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

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
