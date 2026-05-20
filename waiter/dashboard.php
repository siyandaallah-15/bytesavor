<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
  <title>ByteSavor — Waiter</title>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --black:   #080a0d;
      --dark:    #0d1017;
      --card:    #111520;
      --card2:   #161b28;
      --border:  #1e2535;
      --gold:    #f5a623;
      --orange:  #e8520a;
      --text:    #eceae4;
      --muted:   #4a5568;
      --soft:    #8892a4;
      --danger:  #e05555;
      --success: #3eb87a;
      --info:    #4a9eff;
      --radius:  10px;

      --header-h: 62px;
      --sidebar-w: 300px;
    }

    html, body {
      height: 100%; overflow: hidden;
      background: var(--black);
      color: var(--text);
      font-family: 'Outfit', sans-serif;
      -webkit-tap-highlight-color: transparent;
    }

    /* ══════════════════════════════════
       LAYOUT — 3 zones
       [header][main area][order sidebar]
    ══════════════════════════════════ */
    .app {
      display: grid;
      grid-template-rows: var(--header-h) 1fr;
      grid-template-columns: 1fr var(--sidebar-w);
      height: 100vh;
    }

    /* ── Header ── */
    .header {
      grid-column: 1 / -1;
      display: flex; align-items: center;
      padding: 0 20px;
      background: var(--dark);
      border-bottom: 1px solid var(--border);
      gap: 16px; z-index: 10;
    }
    .header-logo {
      display: flex; align-items: center; gap: 9px; flex-shrink: 0;
    }
    .header-logo .ico {
      width: 34px; height: 34px;
      background: linear-gradient(135deg, var(--gold), var(--orange));
      border-radius: 8px; display: grid; place-items: center; font-size: 16px;
    }
    .header-logo .name {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 22px; letter-spacing: 1px;
    }
    .header-logo .name span { color: var(--gold); }

    .header-divider {
      width: 1px; height: 28px; background: var(--border); flex-shrink: 0;
    }

    .header-table-info {
      display: flex; align-items: center; gap: 10px; flex: 1;
    }
    .table-badge {
      padding: 5px 14px;
      background: rgba(245,166,35,0.12);
      border: 1px solid rgba(245,166,35,0.3);
      border-radius: 20px;
      font-size: 13px; font-weight: 600; color: var(--gold);
      display: none;
    }
    .table-badge.show { display: block; }
    .header-hint {
      font-size: 13px; color: var(--muted);
    }

    .header-right {
      display: flex; align-items: center; gap: 12px; margin-left: auto;
    }
    .staff-pill {
      display: flex; align-items: center; gap: 8px;
      padding: 6px 14px;
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: 20px;
      font-size: 13px; color: var(--soft);
    }
    .staff-dot {
      width: 7px; height: 7px; border-radius: 50%;
      background: var(--success);
      box-shadow: 0 0 6px rgba(62,184,122,0.6);
    }
    .btn-logout {
      padding: 6px 14px;
      background: transparent;
      border: 1px solid var(--border);
      border-radius: 8px;
      color: var(--soft); font-family: 'Outfit', sans-serif;
      font-size: 13px; cursor: pointer;
      transition: all 0.15s;
    }
    .btn-logout:hover { border-color: var(--danger); color: var(--danger); }

    /* ── Main content area ── */
    .main {
      grid-column: 1;
      grid-row: 2;
      overflow-y: auto;
      padding: 20px;
      display: flex; flex-direction: column; gap: 20px;
    }

    /* ── Section headings ── */
    .section-title {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 13px; letter-spacing: 0.2em;
      color: var(--muted); text-transform: uppercase;
      margin-bottom: 12px;
    }

    /* ════════════════
       TABLE SELECTION
    ════════════════ */
    .tables-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
      gap: 10px;
    }
    .table-btn {
      aspect-ratio: 1;
      background: var(--card);
      border: 1.5px solid var(--border);
      border-radius: var(--radius);
      color: var(--soft);
      font-family: 'Outfit', sans-serif;
      font-size: 13px; font-weight: 600;
      cursor: pointer;
      transition: all 0.15s;
      display: flex; flex-direction: column;
      align-items: center; justify-content: center; gap: 4px;
      position: relative;
    }
    .table-btn .t-num {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 22px; line-height: 1; color: var(--text);
    }
    .table-btn .t-cap {
      font-size: 10px; color: var(--muted); letter-spacing: 0.05em;
    }
    .table-btn .t-status {
      position: absolute; top: 6px; right: 6px;
      width: 7px; height: 7px; border-radius: 50%;
    }
    .table-btn.available .t-status { background: var(--success); box-shadow: 0 0 5px rgba(62,184,122,0.5); }
    .table-btn.occupied  .t-status { background: var(--danger);  box-shadow: 0 0 5px rgba(224,85,85,0.5); }
    .table-btn.reserved  .t-status { background: var(--gold);    box-shadow: 0 0 5px rgba(245,166,35,0.5); }
    .table-btn.cleaning  .t-status { background: var(--info);    box-shadow: 0 0 5px rgba(74,158,255,0.5); }

    .table-btn:hover:not(.occupied):not(.reserved) {
      border-color: rgba(245,166,35,0.4);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(0,0,0,0.3);
    }
    .table-btn.selected {
      background: rgba(245,166,35,0.1);
      border-color: var(--gold);
      color: var(--gold);
    }
    .table-btn.selected .t-num { color: var(--gold); }
    /* Occupied — fully blocked */
    .table-btn.occupied { opacity: 0.5; cursor: not-allowed; }
    /* Reserved — manager only, waiter cannot select */
    .table-btn.reserved { opacity: 0.55; cursor: not-allowed; }
    .t-lock { position: absolute; top: 5px; left: 6px; font-size: 10px; opacity: 0.6; }
    /* Cleaning — selectable, shows countdown */
    .table-btn.cleaning { cursor: pointer; }
    .t-timer { font-size: 9px; color: var(--info); letter-spacing: 0.02em; margin-top: 1px; }

    /* ════════════════
       CATEGORY GRID
    ════════════════ */
    .categories-section { display: none; }
    .categories-section.show { display: block; }

    .cat-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
      gap: 12px;
    }
    .cat-btn {
      padding: 20px 14px;
      background: var(--card);
      border: 1.5px solid var(--border);
      border-radius: var(--radius);
      cursor: pointer;
      transition: all 0.15s;
      text-align: center;
      display: flex; flex-direction: column;
      align-items: center; gap: 10px;
    }
    .cat-btn .cat-emoji { font-size: 28px; line-height: 1; }
    .cat-btn .cat-name {
      font-size: 13px; font-weight: 600; color: var(--text);
      line-height: 1.2;
    }
    .cat-btn .cat-count {
      font-size: 11px; color: var(--muted);
    }
    .cat-btn:hover {
      border-color: rgba(245,166,35,0.35);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(0,0,0,0.3);
    }
    .cat-btn.active {
      background: rgba(245,166,35,0.08);
      border-color: var(--gold);
    }
    .cat-btn.active .cat-name { color: var(--gold); }

    /* ════════════════
       MENU ITEMS
    ════════════════ */
    .items-section { display: none; }
    .items-section.show { display: block; }

    .items-header {
      display: flex; align-items: center; gap: 12px;
      margin-bottom: 14px;
    }
    .btn-back {
      padding: 6px 14px;
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: 8px;
      color: var(--soft); font-family: 'Outfit', sans-serif;
      font-size: 13px; cursor: pointer;
      transition: all 0.15s;
    }
    .btn-back:hover { border-color: var(--gold); color: var(--gold); }

    .items-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
      gap: 12px;
    }
    .item-card {
      background: var(--card);
      border: 1.5px solid var(--border);
      border-radius: var(--radius);
      padding: 16px;
      cursor: pointer;
      transition: all 0.15s;
      display: flex; flex-direction: column; gap: 8px;
      position: relative; overflow: hidden;
    }
    .item-card::before {
      content: '';
      position: absolute; top: 0; left: 0; right: 0;
      height: 2px;
      background: linear-gradient(90deg, var(--gold), var(--orange));
      opacity: 0; transition: opacity 0.15s;
    }
    .item-card:hover {
      border-color: rgba(245,166,35,0.35);
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(0,0,0,0.3);
    }
    .item-card:hover::before { opacity: 1; }
    .item-card:active { transform: scale(0.97); }

    .item-emoji { font-size: 26px; }
    .item-name  { font-size: 14px; font-weight: 600; color: var(--text); line-height: 1.3; }
    .item-desc  { font-size: 11.5px; color: var(--muted); line-height: 1.4; }
    .item-price {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 20px; color: var(--gold); letter-spacing: 0.5px;
      margin-top: auto;
    }
    .item-unavail {
      opacity: 0.35; cursor: not-allowed;
    }
    .item-unavail:hover { transform: none; box-shadow: none; }

    /* ════════════════════════
       RIGHT SIDEBAR — ORDER
    ════════════════════════ */
    .sidebar {
      grid-column: 2;
      grid-row: 2;
      background: var(--dark);
      border-left: 1px solid var(--border);
      display: flex; flex-direction: column;
      overflow: hidden;
    }

    .sidebar-header {
      padding: 16px 18px;
      border-bottom: 1px solid var(--border);
      display: flex; align-items: center; justify-content: space-between;
    }
    .sidebar-title {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 18px; letter-spacing: 1px; color: var(--text);
    }
    .order-count {
      width: 22px; height: 22px;
      background: var(--gold); border-radius: 50%;
      color: #080a0d; font-size: 12px; font-weight: 700;
      display: grid; place-items: center;
    }

    .order-list {
      flex: 1; overflow-y: auto;
      padding: 14px 18px;
      display: flex; flex-direction: column; gap: 8px;
    }

    /* Empty state */
    .order-empty {
      flex: 1; display: flex; flex-direction: column;
      align-items: center; justify-content: center;
      gap: 10px; color: var(--muted);
      padding: 40px 20px; text-align: center;
    }
    .order-empty .empty-ico { font-size: 36px; opacity: 0.3; }
    .order-empty p { font-size: 13px; line-height: 1.6; }

    /* Order line item */
    .order-item {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 10px 12px;
      display: flex; align-items: center; gap: 10px;
      animation: slideIn 0.2s ease;
    }
    @keyframes slideIn {
      from { opacity: 0; transform: translateX(10px); }
      to   { opacity: 1; transform: translateX(0); }
    }
    .oi-info { flex: 1; min-width: 0; }
    .oi-name { font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .oi-price { font-size: 12px; color: var(--gold); margin-top: 2px; }
    .oi-note { font-size: 11px; color: var(--muted); margin-top: 2px; font-style: italic; }

    .oi-qty {
      display: flex; align-items: center; gap: 6px; flex-shrink: 0;
    }
    .qty-btn {
      width: 24px; height: 24px;
      background: var(--card2); border: 1px solid var(--border);
      border-radius: 6px; color: var(--text);
      font-size: 14px; font-weight: 600;
      cursor: pointer; display: grid; place-items: center;
      transition: all 0.12s; line-height: 1;
    }
    .qty-btn:hover { border-color: var(--gold); color: var(--gold); }
    .qty-btn.minus:hover { border-color: var(--danger); color: var(--danger); }
    .qty-num {
      font-size: 14px; font-weight: 700;
      min-width: 18px; text-align: center;
    }

    /* Order totals */
    .order-totals {
      padding: 14px 18px;
      border-top: 1px solid var(--border);
    }
    .total-row {
      display: flex; justify-content: space-between;
      font-size: 13px; color: var(--soft); margin-bottom: 6px;
    }
    .total-row.grand {
      font-size: 16px; font-weight: 700;
      color: var(--text); margin-top: 10px;
      padding-top: 10px; border-top: 1px solid var(--border);
    }
    .total-row.grand .amt { color: var(--gold); }

    /* Note input */
    .note-wrap {
      padding: 0 18px 12px;
    }
    .note-input {
      width: 100%; padding: 9px 12px;
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--radius); color: var(--text);
      font-family: 'Outfit', sans-serif; font-size: 12px;
      outline: none; resize: none; transition: border-color 0.15s;
    }
    .note-input::placeholder { color: var(--muted); }
    .note-input:focus { border-color: var(--gold); }

    /* Action buttons */
    .sidebar-actions {
      padding: 14px 18px;
      display: flex; flex-direction: column; gap: 8px;
      border-top: 1px solid var(--border);
    }
    .btn-review {
      width: 100%; padding: 13px;
      background: linear-gradient(135deg, var(--gold), var(--orange));
      border: none; border-radius: var(--radius);
      color: #080a0d; font-family: 'Outfit', sans-serif;
      font-size: 14px; font-weight: 700; cursor: pointer;
      transition: all 0.15s; position: relative; overflow: hidden;
    }
    .btn-review:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(245,166,35,0.25); }
    .btn-review:disabled { opacity: 0.4; pointer-events: none; }
    .btn-clear {
      width: 100%; padding: 10px;
      background: transparent; border: 1px solid var(--border);
      border-radius: var(--radius); color: var(--muted);
      font-family: 'Outfit', sans-serif; font-size: 13px; cursor: pointer;
      transition: all 0.15s;
    }
    .btn-clear:hover { border-color: var(--danger); color: var(--danger); }

    /* ══════════════════════════
       REVIEW MODAL (overlay)
    ══════════════════════════ */
    .modal-overlay {
      position: fixed; inset: 0; z-index: 100;
      background: rgba(8,10,13,0.85);
      backdrop-filter: blur(6px);
      display: none; align-items: center; justify-content: center;
      padding: 20px;
    }
    .modal-overlay.show { display: flex; }

    .modal {
      background: var(--dark);
      border: 1px solid var(--border);
      border-radius: 16px;
      width: 100%; max-width: 480px;
      max-height: 85vh; overflow-y: auto;
      animation: modalIn 0.25s ease;
    }
    @keyframes modalIn {
      from { opacity: 0; transform: scale(0.95) translateY(10px); }
      to   { opacity: 1; transform: scale(1)    translateY(0); }
    }

    .modal-header {
      padding: 20px 22px 16px;
      border-bottom: 1px solid var(--border);
      display: flex; align-items: center; justify-content: space-between;
    }
    .modal-title {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 22px; letter-spacing: 1px;
    }
    .modal-close {
      width: 32px; height: 32px;
      background: var(--card); border: 1px solid var(--border);
      border-radius: 8px; color: var(--soft);
      font-size: 16px; cursor: pointer;
      display: grid; place-items: center;
      transition: all 0.15s;
    }
    .modal-close:hover { border-color: var(--danger); color: var(--danger); }

    .modal-body { padding: 20px 22px; }

    .review-table-badge {
      display: inline-flex; align-items: center; gap: 8px;
      padding: 6px 16px;
      background: rgba(245,166,35,0.1);
      border: 1px solid rgba(245,166,35,0.3);
      border-radius: 20px; font-size: 13px;
      font-weight: 600; color: var(--gold);
      margin-bottom: 18px;
    }

    .review-items { display: flex; flex-direction: column; gap: 8px; margin-bottom: 18px; }
    .review-item {
      display: flex; align-items: center;
      padding: 10px 14px;
      background: var(--card); border-radius: var(--radius);
      font-size: 13px;
    }
    .ri-qty {
      width: 28px; height: 28px;
      background: rgba(245,166,35,0.1);
      border-radius: 6px; color: var(--gold);
      font-weight: 700; font-size: 13px;
      display: grid; place-items: center; flex-shrink: 0; margin-right: 12px;
    }
    .ri-name { flex: 1; font-weight: 500; }
    .ri-note { font-size: 11px; color: var(--muted); display: block; }
    .ri-price { font-family: 'Bebas Neue', sans-serif; font-size: 17px; color: var(--gold); letter-spacing: 0.5px; }

    .review-divider { border: none; border-top: 1px solid var(--border); margin: 14px 0; }

    .review-total-row {
      display: flex; justify-content: space-between;
      font-size: 13px; color: var(--soft); margin-bottom: 6px;
    }
    .review-total-row.grand {
      font-size: 18px; font-weight: 700; color: var(--text);
      margin-top: 10px;
    }
    .review-total-row.grand span:last-child { color: var(--gold); }

    .modal-note {
      margin-top: 16px;
      padding: 10px 14px;
      background: var(--card); border-radius: var(--radius);
      font-size: 12px; color: var(--muted); line-height: 1.6;
    }
    .modal-note strong { color: var(--soft); }

    .modal-footer {
      padding: 16px 22px 20px;
      display: flex; gap: 10px;
    }
    .btn-edit {
      flex: 1; padding: 13px;
      background: transparent; border: 1.5px solid var(--border);
      border-radius: var(--radius); color: var(--soft);
      font-family: 'Outfit', sans-serif; font-size: 14px; font-weight: 600;
      cursor: pointer; transition: all 0.15s;
    }
    .btn-edit:hover { border-color: var(--gold); color: var(--gold); }
    .btn-confirm {
      flex: 2; padding: 13px;
      background: linear-gradient(135deg, var(--gold), var(--orange));
      border: none; border-radius: var(--radius);
      color: #080a0d; font-family: 'Outfit', sans-serif;
      font-size: 14px; font-weight: 700; cursor: pointer;
      transition: all 0.15s;
    }
    .btn-confirm:hover { box-shadow: 0 6px 20px rgba(245,166,35,0.3); }

    /* ── Toast notification ── */
    .toast {
      position: fixed; bottom: 24px; left: 50%;
      transform: translateX(-50%) translateY(20px);
      padding: 12px 22px;
      background: var(--card2); border: 1px solid var(--border);
      border-radius: 30px; font-size: 13px; font-weight: 500;
      color: var(--text); z-index: 200;
      opacity: 0; transition: all 0.3s;
      white-space: nowrap;
      box-shadow: 0 8px 30px rgba(0,0,0,0.4);
    }
    .toast.show {
      opacity: 1; transform: translateX(-50%) translateY(0);
    }
    .toast.success { border-color: rgba(62,184,122,0.4); color: var(--success); }
    .toast.error   { border-color: rgba(224,85,85,0.4);  color: var(--danger); }

    /* Scrollbar */
    ::-webkit-scrollbar { width: 4px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

    /* Mobile responsive */
    @media (max-width: 700px) {
      :root { --sidebar-w: 0px; }
      .app { grid-template-columns: 1fr; }
      .sidebar { display: none; }
      .float-cart {
        position: fixed; bottom: 20px; right: 20px;
        width: 58px; height: 58px;
        background: linear-gradient(135deg, var(--gold), var(--orange));
        border-radius: 50%; border: none;
        font-size: 22px; cursor: pointer;
        box-shadow: 0 6px 24px rgba(245,166,35,0.35);
        display: grid; place-items: center; z-index: 50;
      }
      .float-badge {
        position: absolute; top: -4px; right: -4px;
        width: 20px; height: 20px; border-radius: 50%;
        background: var(--danger); color: #fff;
        font-size: 11px; font-weight: 700;
        display: grid; place-items: center;
      }
    }
    @media (min-width: 701px) { .float-cart { display: none; } }
  </style>
</head>
<body>

<!-- ══ TOAST ══ -->
<div class="toast" id="toast"></div>

<!-- ══ REVIEW MODAL ══ -->
<div class="modal-overlay" id="modal">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Review Order</span>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>
    <div class="modal-body">
      <div class="review-table-badge" id="modal-table">🪑 Table —</div>
      <div class="review-items" id="modal-items"></div>
      <hr class="review-divider"/>
      <div class="review-total-row"><span>Subtotal</span><span id="modal-sub">R0.00</span></div>
      <div class="review-total-row"><span>VAT (15%)</span><span id="modal-vat">R0.00</span></div>
      <div class="review-total-row grand"><span>Total</span><span id="modal-total">R0.00</span></div>
      <div class="modal-note" id="modal-note-display" style="display:none">
        <strong>Order note:</strong> <span id="modal-note-text"></span>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn-edit" onclick="closeModal()">✏️ Edit</button>
      <button class="btn-confirm" onclick="confirmOrder()">Send to Kitchen 🍳</button>
    </div>
  </div>
</div>

<!-- ══ FLOAT CART (mobile) ══ -->
<button class="float-cart" onclick="openModal()" id="float-cart" style="display:none">
  🛒<span class="float-badge" id="float-badge">0</span>
</button>

<!-- ══ APP ══ -->
<div class="app">

  <!-- Header -->
  <header class="header">
    <div class="header-logo">
      <div class="ico">🍽️</div>
      <div class="name">Byte<span>Savor</span></div>
    </div>
    <div class="header-divider"></div>
    <div class="header-table-info">
      <div class="table-badge" id="table-badge">Table —</div>
      <span class="header-hint" id="header-hint">Select a table to begin</span>
    </div>
    <div class="header-right">
      <div class="staff-pill">
        <div class="staff-dot"></div>
        <span id="staff-name">Waiter</span>
      </div>
      <button class="btn-logout" onclick="window.location.href='../logout.php'">Sign Out</button>
    </div>
  </header>

  <!-- Main -->
  <main class="main">

    <!-- STEP 1: Table selection -->
    <section id="section-tables">
      <p class="section-title">Step 1 — Select a Table</p>
      <div class="tables-grid" id="tables-grid">
        <!-- Generated by JS -->
      </div>
    </section>

    <!-- STEP 2: Category grid (hidden until table selected) -->
    <section class="categories-section" id="section-cats">
      <p class="section-title">Step 2 — Choose a Category</p>
      <div class="cat-grid" id="cat-grid">
        <!-- Generated by JS -->
      </div>
    </section>

    <!-- STEP 3: Menu items (hidden until category selected) -->
    <section class="items-section" id="section-items">
      <div class="items-header">
        <button class="btn-back" onclick="backToCategories()">← Back</button>
        <p class="section-title" id="items-title" style="margin-bottom:0">Items</p>
      </div>
      <div class="items-grid" id="items-grid">
        <!-- Generated by JS -->
      </div>
    </section>

  </main>

  <!-- Order sidebar -->
  <aside class="sidebar">
    <div class="sidebar-header">
      <span class="sidebar-title">Current Order</span>
      <div class="order-count" id="order-count">0</div>
    </div>

    <div class="order-list" id="order-list">
      <div class="order-empty">
        <div class="empty-ico">🛒</div>
        <p>No items yet.<br/>Select a table and start adding items.</p>
      </div>
    </div>

    <div class="note-wrap">
      <textarea class="note-input" id="order-note"
        placeholder="Order note (allergies, special requests...)"
        rows="2"></textarea>
    </div>

    <div class="order-totals">
      <div class="total-row"><span>Subtotal</span><span id="sub-total">R0.00</span></div>
      <div class="total-row"><span>VAT (15%)</span><span id="vat-total">R0.00</span></div>
      <div class="total-row grand"><span>Total</span><span class="amt" id="grand-total">R0.00</span></div>
    </div>

    <div class="sidebar-actions">
      <button class="btn-review" id="btn-review" onclick="openModal()" disabled>
        Review &amp; Confirm Order
      </button>
      <button class="btn-clear" onclick="clearOrder()">Clear Order</button>
    </div>
  </aside>

</div>

<script>
// ════════════════════════════════════════
//  ByteSavor — Waiter Dashboard
//  All data here is DEMO data.
//  Later: fetch from api/menu.php and api/tables.php
// ════════════════════════════════════════

// ── Demo: tables ──
// cleaningSince: timestamp (ms) when cleaning started — null if not cleaning
const TABLES = [
  { id:1,  num:'T1',   cap:2,  status:'available', cleaningSince: null },
  { id:2,  num:'T2',   cap:4,  status:'available', cleaningSince: null },
  { id:3,  num:'T3',   cap:4,  status:'occupied',  cleaningSince: null },
  { id:4,  num:'T4',   cap:6,  status:'available', cleaningSince: null },
  { id:5,  num:'T5',   cap:2,  status:'reserved',  cleaningSince: null },
  { id:6,  num:'T6',   cap:8,  status:'available', cleaningSince: null },
  { id:7,  num:'T7',   cap:4,  status:'available', cleaningSince: null },
  { id:8,  num:'T8',   cap:4,  status:'cleaning',  cleaningSince: Date.now() - (2 * 60 * 1000) }, // 2 min ago (demo)
  { id:9,  num:'VIP1', cap:8,  status:'available', cleaningSince: null },
  { id:10, num:'VIP2', cap:10, status:'available', cleaningSince: null },
  { id:11, num:'BAR1', cap:4,  status:'available', cleaningSince: null },
  { id:12, num:'BAR2', cap:4,  status:'available', cleaningSince: null },
];

// ── Demo: menu categories & items ──
const MENU = [
  {
    id:1, name:'Starters', emoji:'🥗', items:[
      { id:101, name:'Chicken Wings',       desc:'Crispy wings, peri-peri or BBQ',     price:89,  emoji:'🍗', avail:true  },
      { id:102, name:'Calamari',            desc:'Lightly fried with tartare sauce',    price:95,  emoji:'🦑', avail:true  },
      { id:103, name:'Soup of the Day',     desc:'Ask your waiter for today\'s special',price:65,  emoji:'🍲', avail:true  },
      { id:104, name:'Bruschetta',          desc:'Toasted bread with tomato & basil',   price:72,  emoji:'🍅', avail:false },
    ]
  },
  {
    id:2, name:'Mains', emoji:'🍽️', items:[
      { id:201, name:'Beef Burger',         desc:'200g beef patty, cheese, chips',      price:145, emoji:'🍔', avail:true  },
      { id:202, name:'Grilled Chicken',     desc:'Served with roast veg & rice',        price:165, emoji:'🍗', avail:true  },
      { id:203, name:'Beef Steak 300g',     desc:'Sirloin, choice of sauce & sides',    price:265, emoji:'🥩', avail:true  },
      { id:204, name:'Pasta Carbonara',     desc:'Creamy bacon & mushroom pasta',       price:135, emoji:'🍝', avail:true  },
      { id:205, name:'Fish & Chips',        desc:'Battered hake, chips & tartar',       price:155, emoji:'🐟', avail:true  },
      { id:206, name:'Veggie Burger',       desc:'Plant-based patty, avocado, chips',   price:125, emoji:'🌿', avail:true  },
    ]
  },
  {
    id:3, name:'Pizzas', emoji:'🍕', items:[
      { id:301, name:'Margherita',          desc:'Tomato, mozzarella, fresh basil',     price:120, emoji:'🍕', avail:true  },
      { id:302, name:'BBQ Chicken',         desc:'BBQ base, chicken, peppers, onion',   price:145, emoji:'🍕', avail:true  },
      { id:303, name:'Meat Lovers',         desc:'Ham, bacon, mince, pepperoni',        price:155, emoji:'🍕', avail:true  },
      { id:304, name:'Vegetarian',          desc:'Seasonal roast veg, feta, olives',    price:130, emoji:'🍕', avail:true  },
    ]
  },
  {
    id:4, name:'Sides', emoji:'🍟', items:[
      { id:401, name:'Chips',               desc:'Crispy golden chips',                 price:45,  emoji:'🍟', avail:true  },
      { id:402, name:'Onion Rings',         desc:'Beer-battered onion rings',           price:50,  emoji:'🧅', avail:true  },
      { id:403, name:'Side Salad',          desc:'Mixed greens, cherry tomato, dressing',price:55, emoji:'🥗', avail:true  },
      { id:404, name:'Coleslaw',            desc:'Creamy homemade coleslaw',            price:40,  emoji:'🥣', avail:true  },
    ]
  },
  {
    id:5, name:'Drinks', emoji:'🥤', items:[
      { id:501, name:'Soft Drink',          desc:'Coke, Sprite, Fanta — 330ml',         price:35,  emoji:'🥤', avail:true  },
      { id:502, name:'Still Water',         desc:'500ml bottle',                        price:25,  emoji:'💧', avail:true  },
      { id:503, name:'Juice',               desc:'Apple, orange or guava — 250ml',      price:32,  emoji:'🧃', avail:true  },
      { id:504, name:'Milkshake',           desc:'Choc, vanilla or strawberry',         price:65,  emoji:'🥛', avail:true  },
      { id:505, name:'Coffee',              desc:'Espresso, flat white, cappuccino',     price:48,  emoji:'☕', avail:true  },
    ]
  },
  {
    id:6, name:'Desserts', emoji:'🍰', items:[
      { id:601, name:'Chocolate Lava Cake', desc:'Warm cake, vanilla ice cream',        price:85,  emoji:'🍫', avail:true  },
      { id:602, name:'Cheesecake',          desc:'New York style with berry compote',   price:80,  emoji:'🍰', avail:true  },
      { id:603, name:'Ice Cream',           desc:'3 scoops, choice of flavour',         price:60,  emoji:'🍨', avail:true  },
    ]
  },
];

// ── State ──
let selectedTable  = null;
let selectedCatId  = null;
let orderItems     = [];   // { menuItem, qty, note }

// ── Cleaning auto-timer: 5 minutes = 300,000ms ──
const CLEAN_DURATION = 5 * 60 * 1000;

// ── On load ──
renderTables();
// Tick every second to update cleaning countdowns
setInterval(tickCleaningTimers, 1000);

function tickCleaningTimers() {
  const now = Date.now();
  let changed = false;
  TABLES.forEach(t => {
    if (t.status === 'cleaning' && t.cleaningSince !== null) {
      const elapsed = now - t.cleaningSince;
      if (elapsed >= CLEAN_DURATION) {
        // Auto-switch to available after 5 min
        t.status = 'available';
        t.cleaningSince = null;
        changed = true;
        showToast(t.num + ' is now available ✓', 'success');
      }
    }
  });
  // Always re-render to update countdown display
  renderTables();
}

function getCleaningTimeLeft(cleaningSince) {
  const elapsed = Date.now() - cleaningSince;
  const remaining = Math.max(0, CLEAN_DURATION - elapsed);
  const mins = Math.floor(remaining / 60000);
  const secs = Math.floor((remaining % 60000) / 1000);
  return `${mins}:${secs.toString().padStart(2,'0')} left`;
}

function renderTables() {
  const grid = document.getElementById('tables-grid');
  grid.innerHTML = TABLES.map(t => {
    const isSelected  = selectedTable?.id === t.id;
    const isCleaning  = t.status === 'cleaning';
    const isOccupied  = t.status === 'occupied';
    const isReserved  = t.status === 'reserved';
    // Only occupied and reserved block ordering
    const isDisabled  = isOccupied || isReserved;
    const timerLabel  = isCleaning && t.cleaningSince
                        ? getCleaningTimeLeft(t.cleaningSince) : '';
    return `
      <button class="table-btn ${t.status} ${isSelected ? 'selected' : ''}"
              onclick="${isDisabled ? '' : `selectTable(${t.id})`}"
              ${isDisabled ? 'disabled' : ''}>
        <div class="t-status"></div>
        ${isReserved ? '<span class="t-lock">🔒</span>' : ''}
        <div class="t-num">${t.num}</div>
        <div class="t-cap">${isReserved ? 'Manager only' : `${t.cap} seats`}</div>
        ${isCleaning ? `<div class="t-timer">🧹 ${timerLabel}</div>` : ''}
      </button>
    `;
  }).join('');
}

function selectTable(id) {
  selectedTable = TABLES.find(t => t.id === id);
  renderTables();

  const isCleaning = selectedTable.status === 'cleaning';

  // Update header
  document.getElementById('table-badge').textContent = '🪑 ' + selectedTable.num;
  document.getElementById('table-badge').classList.add('show');
  document.getElementById('header-hint').textContent = isCleaning
    ? 'Cleaning in progress — order will be ready when done'
    : 'Now choose a category';

  // Show categories
  document.getElementById('section-cats').classList.add('show');
  document.getElementById('section-items').classList.remove('show');
  renderCategories();

  document.getElementById('section-cats').scrollIntoView({ behavior:'smooth' });
  const msg = isCleaning
    ? 'Table ' + selectedTable.num + ' selected (cleaning) ✓'
    : 'Table ' + selectedTable.num + ' selected ✓';
  showToast(msg, 'success');
}

// ── When an order is confirmed, set table to cleaning ──
function setTableCleaning(tableId) {
  const t = TABLES.find(t => t.id === tableId);
  if (t) {
    t.status = 'cleaning';
    t.cleaningSince = Date.now();
  }
}

// ── Categories ──
function renderCategories() {
  const grid = document.getElementById('cat-grid');
  grid.innerHTML = MENU.map(cat => `
    <button class="cat-btn ${selectedCatId===cat.id?'active':''}"
            onclick="selectCategory(${cat.id})">
      <span class="cat-emoji">${cat.emoji}</span>
      <span class="cat-name">${cat.name}</span>
      <span class="cat-count">${cat.items.filter(i=>i.avail).length} items</span>
    </button>
  `).join('');
}

function selectCategory(id) {
  if (!selectedTable) { showToast('Please select a table first', 'error'); return; }
  selectedCatId = id;
  renderCategories();

  const cat = MENU.find(c => c.id === id);
  document.getElementById('items-title').textContent = cat.name;
  renderItems(cat);

  document.getElementById('section-items').classList.add('show');
  document.getElementById('section-items').scrollIntoView({ behavior:'smooth' });
}

function backToCategories() {
  selectedCatId = null;
  document.getElementById('section-items').classList.remove('show');
  renderCategories();
  document.getElementById('section-cats').scrollIntoView({ behavior:'smooth' });
}

// ── Menu items ──
function renderItems(cat) {
  const grid = document.getElementById('items-grid');
  grid.innerHTML = cat.items.map(item => `
    <div class="item-card ${!item.avail?'item-unavail':''}"
         onclick="${item.avail?`addItem(${item.id},${cat.id})`:''}">
      <div class="item-emoji">${item.emoji}</div>
      <div class="item-name">${item.name}</div>
      <div class="item-desc">${item.desc}${!item.avail?' — <span style="color:var(--danger)">Unavailable</span>':''}</div>
      <div class="item-price">R${item.price.toFixed(2)}</div>
    </div>
  `).join('');
}

// ── Add item to order ──
function addItem(itemId, catId) {
  const cat  = MENU.find(c => c.id === catId);
  const item = cat.items.find(i => i.id === itemId);
  const existing = orderItems.find(o => o.menuItem.id === itemId);

  if (existing) {
    existing.qty++;
  } else {
    orderItems.push({ menuItem: item, qty: 1, note: '' });
  }

  renderOrderSidebar();
  showToast(item.name + ' added', 'success');
}

// ── Render order sidebar ──
function renderOrderSidebar() {
  const list  = document.getElementById('order-list');
  const count = orderItems.reduce((s, o) => s + o.qty, 0);
  const sub   = orderItems.reduce((s, o) => s + o.menuItem.price * o.qty, 0);
  const vat   = sub * 0.15;
  const grand = sub + vat;

  document.getElementById('order-count').textContent = count;
  document.getElementById('sub-total').textContent   = 'R' + sub.toFixed(2);
  document.getElementById('vat-total').textContent   = 'R' + vat.toFixed(2);
  document.getElementById('grand-total').textContent = 'R' + grand.toFixed(2);
  document.getElementById('btn-review').disabled     = orderItems.length === 0;

  // Mobile float cart
  const fc = document.getElementById('float-cart');
  document.getElementById('float-badge').textContent = count;
  fc.style.display = (count > 0 && window.innerWidth <= 700) ? 'grid' : 'none';

  if (orderItems.length === 0) {
    list.innerHTML = `
      <div class="order-empty">
        <div class="empty-ico">🛒</div>
        <p>No items yet.<br/>Select a table and start adding items.</p>
      </div>`;
    return;
  }

  list.innerHTML = orderItems.map((o, idx) => `
    <div class="order-item">
      <div class="oi-info">
        <div class="oi-name">${o.menuItem.name}</div>
        <div class="oi-price">R${(o.menuItem.price * o.qty).toFixed(2)}</div>
      </div>
      <div class="oi-qty">
        <button class="qty-btn minus" onclick="changeQty(${idx},-1)">−</button>
        <span class="qty-num">${o.qty}</span>
        <button class="qty-btn" onclick="changeQty(${idx},1)">+</button>
      </div>
    </div>
  `).join('');
}

function changeQty(idx, delta) {
  orderItems[idx].qty += delta;
  if (orderItems[idx].qty <= 0) orderItems.splice(idx, 1);
  renderOrderSidebar();
}

function clearOrder() {
  if (orderItems.length === 0) return;
  if (confirm('Clear the entire order?')) {
    orderItems = [];
    renderOrderSidebar();
    showToast('Order cleared', '');
  }
}

// ── Review modal ──
function openModal() {
  if (!selectedTable) { showToast('Please select a table first', 'error'); return; }
  if (orderItems.length === 0) { showToast('No items in order', 'error'); return; }

  const sub   = orderItems.reduce((s, o) => s + o.menuItem.price * o.qty, 0);
  const vat   = sub * 0.15;
  const grand = sub + vat;
  const note  = document.getElementById('order-note').value.trim();

  document.getElementById('modal-table').textContent = '🪑 ' + selectedTable.num;
  document.getElementById('modal-sub').textContent   = 'R' + sub.toFixed(2);
  document.getElementById('modal-vat').textContent   = 'R' + vat.toFixed(2);
  document.getElementById('modal-total').textContent = 'R' + grand.toFixed(2);

  document.getElementById('modal-items').innerHTML = orderItems.map(o => `
    <div class="review-item">
      <div class="ri-qty">${o.qty}</div>
      <div class="ri-name">
        ${o.menuItem.name}
        ${o.note?`<span class="ri-note">${o.note}</span>`:''}
      </div>
      <div class="ri-price">R${(o.menuItem.price * o.qty).toFixed(2)}</div>
    </div>
  `).join('');

  const noteEl = document.getElementById('modal-note-display');
  if (note) {
    noteEl.style.display = 'block';
    document.getElementById('modal-note-text').textContent = note;
  } else {
    noteEl.style.display = 'none';
  }

  document.getElementById('modal').classList.add('show');
}

function closeModal() {
  document.getElementById('modal').classList.remove('show');
}

function confirmOrder() {
  // In production: POST to api/orders.php
  closeModal();
  showToast('✅ Order sent to kitchen!', 'success');

  // When a bill is closed / order confirmed, mark table as cleaning.
  // It will automatically switch to available after 5 minutes.
  if (selectedTable) {
    setTableCleaning(selectedTable.id);
  }

  // Reset for next order
  setTimeout(() => {
    orderItems    = [];
    selectedTable = null;
    selectedCatId = null;
    renderTables();
    renderOrderSidebar();
    document.getElementById('table-badge').classList.remove('show');
    document.getElementById('header-hint').textContent = 'Select a table to begin';
    document.getElementById('section-cats').classList.remove('show');
    document.getElementById('section-items').classList.remove('show');
    document.getElementById('order-note').value = '';
  }, 1500);
}

// ── Toast ──
let toastTimer;
function showToast(msg, type) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className   = 'toast show ' + (type||'');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => { t.className = 'toast'; }, 2500);
}

// Close modal on overlay click
document.getElementById('modal').addEventListener('click', e => {
  if (e.target === document.getElementById('modal')) closeModal();
});
</script>

</body>
</html>
