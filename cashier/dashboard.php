<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"/>
  <title>ByteSavor — Payment</title>
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
    }

    html, body {
      height: 100%; overflow: hidden;
      background: var(--black);
      color: var(--text);
      font-family: 'Outfit', sans-serif;
      -webkit-tap-highlight-color: transparent;
    }

    /* ══ LAYOUT ══ */
    .app {
      display: grid;
      grid-template-rows: 58px 1fr;
      grid-template-columns: 1fr 380px;
      height: 100vh;
    }

    /* ══ HEADER ══ */
    .header {
      grid-column: 1 / -1;
      display: flex; align-items: center;
      padding: 0 20px; gap: 16px;
      background: var(--dark);
      border-bottom: 1px solid var(--border);
      z-index: 10;
    }
    .logo { display: flex; align-items: center; gap: 9px; }
    .logo-box {
      width: 32px; height: 32px;
      background: linear-gradient(135deg, var(--gold), var(--orange));
      border-radius: 8px; display: grid; place-items: center; font-size: 15px;
    }
    .logo-name { font-family: 'Bebas Neue', sans-serif; font-size: 20px; letter-spacing: 1px; }
    .logo-name span { color: var(--gold); }
    .hdiv { width: 1px; height: 24px; background: var(--border); }
    .hscreen {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 16px; letter-spacing: 0.15em; color: var(--soft);
    }
    .hright { margin-left: auto; display: flex; align-items: center; gap: 10px; }
    .staff-pill {
      display: flex; align-items: center; gap: 7px;
      padding: 5px 13px;
      background: var(--card); border: 1px solid var(--border);
      border-radius: 20px; font-size: 12px; color: var(--soft);
    }
    .sdot { width: 7px; height: 7px; border-radius: 50%; background: var(--success); box-shadow: 0 0 6px rgba(62,184,122,0.6); }
    .btn-out {
      padding: 5px 13px; background: transparent;
      border: 1px solid var(--border); border-radius: 8px;
      color: var(--soft); font-family: 'Outfit', sans-serif;
      font-size: 12px; cursor: pointer; transition: all 0.15s;
    }
    .btn-out:hover { border-color: var(--danger); color: var(--danger); }

    /* ══ LEFT — Bill selection + items ══ */
    .left {
      grid-column: 1; grid-row: 2;
      overflow-y: auto; padding: 18px 20px;
      display: flex; flex-direction: column; gap: 18px;
    }

    .sec-title {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 12px; letter-spacing: 0.2em;
      color: var(--muted); text-transform: uppercase;
      margin-bottom: 10px;
    }

    /* ── Open bills grid ── */
    .bills-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
      gap: 10px;
    }
    .bill-btn {
      padding: 14px 10px;
      background: var(--card);
      border: 1.5px solid var(--border);
      border-radius: var(--radius);
      cursor: pointer; transition: all 0.15s;
      display: flex; flex-direction: column;
      align-items: center; gap: 5px;
      position: relative;
    }
    .bill-btn .b-table {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 22px; letter-spacing: 1px; color: var(--text);
    }
    .bill-btn .b-items { font-size: 11px; color: var(--muted); }
    .bill-btn .b-total { font-size: 13px; font-weight: 700; color: var(--gold); }
    .bill-btn .b-owner {
      font-size: 10px; color: var(--soft);
      display: flex; align-items: center; gap: 4px;
    }
    /* Lock badge for bills not owned by current user */
    .bill-btn .b-lock {
      position: absolute; top: 6px; right: 7px;
      font-size: 11px; opacity: 0.5;
    }
    .bill-btn.mine:hover {
      border-color: rgba(245,166,35,0.4);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(0,0,0,0.3);
    }
    .bill-btn.mine.selected {
      background: rgba(245,166,35,0.08);
      border-color: var(--gold);
    }
    .bill-btn.locked { opacity: 0.4; cursor: not-allowed; }
    .bill-btn.locked:hover { transform: none; }

    /* ── Bill items table ── */
    .bill-box {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      overflow: hidden; display: none;
    }
    .bill-box.show { display: block; }

    .bill-box-head {
      padding: 13px 16px;
      background: var(--card2);
      border-bottom: 1px solid var(--border);
      display: flex; align-items: center; gap: 10px;
    }
    .bbh-table {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 20px; letter-spacing: 1px;
    }
    .bbh-type {
      padding: 3px 10px; border-radius: 20px;
      font-size: 11px; font-weight: 700;
    }
    .type-dine { background: rgba(74,158,255,0.12); color: var(--info); border: 1px solid rgba(74,158,255,0.25); }
    .type-tw   { background: rgba(232,82,10,0.12);  color: var(--orange); border: 1px solid rgba(232,82,10,0.25); }
    .type-online{ background: rgba(62,184,122,0.12); color: var(--success); border: 1px solid rgba(62,184,122,0.25); }
    .bbh-waiter { margin-left: auto; font-size: 12px; color: var(--soft); }

    .bill-items { padding: 10px 16px; }
    .bi-row {
      display: flex; align-items: center;
      padding: 8px 0;
      border-bottom: 1px solid var(--border);
      font-size: 13px; gap: 10px;
    }
    .bi-row:last-child { border-bottom: none; }
    .bi-qty {
      width: 24px; height: 24px;
      background: var(--card2); border-radius: 6px;
      color: var(--gold); font-size: 12px; font-weight: 700;
      display: grid; place-items: center; flex-shrink: 0;
    }
    .bi-name { flex: 1; font-weight: 500; }
    .bi-tw {
      font-size: 10px; color: var(--orange);
      background: rgba(232,82,10,0.1); border-radius: 4px;
      padding: 1px 5px; flex-shrink: 0;
    }
    .bi-price { font-weight: 600; color: var(--text); flex-shrink: 0; }

    /* Split toggle per item */
    .bi-split {
      width: 22px; height: 22px;
      background: var(--card2);
      border: 1px solid var(--border);
      border-radius: 6px; cursor: pointer;
      display: grid; place-items: center;
      font-size: 11px; flex-shrink: 0;
      transition: all 0.15s; color: var(--muted);
    }
    .bi-split.on { background: rgba(74,158,255,0.15); border-color: var(--info); color: var(--info); }
    .bi-split:hover { border-color: var(--info); color: var(--info); }

    .bill-totals {
      padding: 12px 16px;
      border-top: 1px solid var(--border);
      background: var(--card2);
    }
    .tot-row {
      display: flex; justify-content: space-between;
      font-size: 13px; color: var(--soft); margin-bottom: 5px;
    }
    .tot-row.grand {
      font-size: 17px; font-weight: 700; color: var(--text);
      margin-top: 8px; padding-top: 8px;
      border-top: 1px solid var(--border);
    }
    .tot-row.grand .gamt { color: var(--gold); }
    .gratuity-note {
      margin-top: 10px; padding: 8px 12px;
      background: rgba(245,166,35,0.05);
      border: 1px dashed rgba(245,166,35,0.2);
      border-radius: 8px;
      font-size: 11.5px; color: var(--muted);
      display: flex; align-items: center; gap: 7px;
    }

    /* ══ RIGHT — Payment panel ══ */
    .right {
      grid-column: 2; grid-row: 2;
      background: var(--dark);
      border-left: 1px solid var(--border);
      display: flex; flex-direction: column;
      overflow-y: auto;
    }

    .pay-header {
      padding: 16px 18px;
      border-bottom: 1px solid var(--border);
    }
    .pay-title {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 18px; letter-spacing: 1px;
    }
    .pay-amount {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 40px; letter-spacing: 1px;
      color: var(--gold); line-height: 1; margin-top: 4px;
    }
    .pay-sub { font-size: 12px; color: var(--muted); margin-top: 2px; }

    /* Split indicator */
    .split-bar {
      margin-top: 10px; padding: 8px 12px;
      background: rgba(74,158,255,0.08);
      border: 1px solid rgba(74,158,255,0.2);
      border-radius: 8px;
      font-size: 12px; color: var(--info);
      display: none; align-items: center; gap: 7px;
    }
    .split-bar.show { display: flex; }

    /* Payment methods */
    .pay-methods { padding: 14px 18px; border-bottom: 1px solid var(--border); }
    .method-title { font-size: 11px; font-weight: 600; letter-spacing: 0.15em; text-transform: uppercase; color: var(--muted); margin-bottom: 10px; }

    .methods-grid {
      display: grid; grid-template-columns: 1fr 1fr;
      gap: 7px; margin-bottom: 7px;
    }
    .method-btn {
      padding: 11px 8px;
      background: var(--card);
      border: 1.5px solid var(--border);
      border-radius: var(--radius);
      color: var(--muted);
      font-family: 'Outfit', sans-serif;
      font-size: 12px; font-weight: 600;
      cursor: pointer; transition: all 0.15s;
      display: flex; flex-direction: column;
      align-items: center; gap: 5px;
    }
    .method-btn .m-ico { font-size: 18px; }
    .method-btn:hover { border-color: rgba(245,166,35,0.35); color: var(--text); }
    .method-btn.active {
      background: rgba(245,166,35,0.08);
      border-color: var(--gold); color: var(--gold);
    }

    /* Cash input */
    .cash-section { display: none; padding: 12px 18px; border-bottom: 1px solid var(--border); }
    .cash-section.show { display: block; }
    .cash-label { font-size: 11px; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; color: var(--muted); margin-bottom: 8px; }
    .cash-input-wrap { position: relative; }
    .cash-prefix {
      position: absolute; left: 12px; top: 50%;
      transform: translateY(-50%);
      font-size: 14px; font-weight: 700; color: var(--gold);
    }
    .cash-input {
      width: 100%; padding: 12px 12px 12px 30px;
      background: var(--card); border: 1.5px solid var(--border);
      border-radius: var(--radius); color: var(--text);
      font-family: 'Outfit', sans-serif; font-size: 20px; font-weight: 700;
      outline: none; transition: border-color 0.15s;
    }
    .cash-input:focus { border-color: var(--gold); }
    .change-display {
      margin-top: 10px; padding: 10px 14px;
      background: rgba(62,184,122,0.08);
      border: 1px solid rgba(62,184,122,0.25);
      border-radius: var(--radius);
      display: flex; justify-content: space-between;
      align-items: center;
      font-size: 14px;
    }
    .change-label { color: var(--soft); font-weight: 500; }
    .change-amt   { font-family: 'Bebas Neue', sans-serif; font-size: 22px; color: var(--success); letter-spacing: 1px; }
    .change-neg   { color: var(--danger); }

    /* Quick cash buttons */
    .quick-cash { display: flex; gap: 6px; margin-top: 8px; flex-wrap: wrap; }
    .qc-btn {
      padding: 5px 12px;
      background: var(--card); border: 1px solid var(--border);
      border-radius: 20px; color: var(--soft);
      font-family: 'Outfit', sans-serif; font-size: 12px; font-weight: 600;
      cursor: pointer; transition: all 0.15s;
    }
    .qc-btn:hover { border-color: var(--gold); color: var(--gold); }

    /* Reference field (card/EFT/online) */
    .ref-section { display: none; padding: 12px 18px; border-bottom: 1px solid var(--border); }
    .ref-section.show { display: block; }
    .ref-input {
      width: 100%; padding: 11px 13px;
      background: var(--card); border: 1.5px solid var(--border);
      border-radius: var(--radius); color: var(--text);
      font-family: 'Outfit', sans-serif; font-size: 14px;
      outline: none; transition: border-color 0.15s;
    }
    .ref-input::placeholder { color: var(--muted); }
    .ref-input:focus { border-color: var(--gold); }

    /* Receipt options */
    .receipt-section { padding: 12px 18px; border-bottom: 1px solid var(--border); }
    .receipt-opts { display: flex; flex-direction: column; gap: 7px; margin-top: 8px; }
    .receipt-chk {
      display: flex; align-items: center; gap: 9px;
      font-size: 13px; color: var(--soft); cursor: pointer;
    }
    .receipt-chk input[type="checkbox"] {
      width: 15px; height: 15px; accent-color: var(--gold); cursor: pointer; padding: 0;
    }
    .receipt-chk.active { color: var(--text); }
    .receipt-sub-input {
      width: 100%; padding: 9px 12px; margin-top: 5px;
      background: var(--card); border: 1px solid var(--border);
      border-radius: 8px; color: var(--text);
      font-family: 'Outfit', sans-serif; font-size: 13px; outline: none;
      transition: border-color 0.15s; display: none;
    }
    .receipt-sub-input.show { display: block; }
    .receipt-sub-input:focus { border-color: var(--gold); }

    /* Action buttons */
    .pay-actions { padding: 14px 18px; margin-top: auto; }
    .btn-pay {
      width: 100%; padding: 15px;
      background: linear-gradient(135deg, var(--gold), var(--orange));
      border: none; border-radius: var(--radius);
      color: #080a0d; font-family: 'Outfit', sans-serif;
      font-size: 15px; font-weight: 700; cursor: pointer;
      transition: all 0.15s; margin-bottom: 8px;
    }
    .btn-pay:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(245,166,35,0.25); }
    .btn-pay:disabled { opacity: 0.4; pointer-events: none; }
    .btn-print {
      width: 100%; padding: 11px;
      background: transparent; border: 1px solid var(--border);
      border-radius: var(--radius); color: var(--soft);
      font-family: 'Outfit', sans-serif; font-size: 13px; font-weight: 600;
      cursor: pointer; transition: all 0.15s;
    }
    .btn-print:hover { border-color: var(--gold); color: var(--gold); }
    .btn-print:disabled { opacity: 0.3; pointer-events: none; }

    /* ══ SUCCESS MODAL ══ */
    .overlay {
      position: fixed; inset: 0; z-index: 100;
      background: rgba(8,10,13,0.88);
      backdrop-filter: blur(8px);
      display: none; align-items: center; justify-content: center;
    }
    .overlay.show { display: flex; }
    .success-modal {
      background: var(--dark);
      border: 1px solid var(--border);
      border-radius: 18px; padding: 36px 32px;
      width: 100%; max-width: 400px;
      text-align: center;
      animation: popIn 0.3s cubic-bezier(0.34,1.56,0.64,1);
    }
    @keyframes popIn {
      from { opacity: 0; transform: scale(0.85); }
      to   { opacity: 1; transform: scale(1); }
    }
    .success-ico { font-size: 54px; margin-bottom: 14px; }
    .success-title {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 28px; letter-spacing: 1px; color: var(--success);
      margin-bottom: 6px;
    }
    .success-amt {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 42px; color: var(--gold); letter-spacing: 1px; margin-bottom: 4px;
    }
    .success-table { font-size: 14px; color: var(--soft); margin-bottom: 20px; }
    .success-change {
      padding: 10px 16px; margin-bottom: 20px;
      background: rgba(62,184,122,0.1);
      border: 1px solid rgba(62,184,122,0.25);
      border-radius: 10px; font-size: 14px; color: var(--success);
      display: none;
    }
    .success-change.show { display: block; }
    .success-btns { display: flex; flex-direction: column; gap: 8px; }
    .btn-done {
      width: 100%; padding: 13px;
      background: linear-gradient(135deg, var(--gold), var(--orange));
      border: none; border-radius: var(--radius);
      color: #080a0d; font-family: 'Outfit', sans-serif;
      font-size: 14px; font-weight: 700; cursor: pointer;
    }
    .btn-new {
      width: 100%; padding: 11px;
      background: transparent; border: 1px solid var(--border);
      border-radius: var(--radius); color: var(--soft);
      font-family: 'Outfit', sans-serif; font-size: 13px; cursor: pointer;
      transition: all 0.15s;
    }
    .btn-new:hover { border-color: var(--gold); color: var(--gold); }

    /* Toast */
    .toast {
      position: fixed; bottom: 24px; left: 50%;
      transform: translateX(-50%) translateY(20px);
      padding: 11px 22px; background: var(--card2);
      border: 1px solid var(--border); border-radius: 30px;
      font-size: 13px; font-weight: 500; color: var(--text);
      z-index: 200; opacity: 0; transition: all 0.28s;
      white-space: nowrap; box-shadow: 0 8px 30px rgba(0,0,0,0.4);
    }
    .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
    .toast.ok  { border-color: rgba(62,184,122,0.4); color: var(--success); }
    .toast.err { border-color: rgba(224,85,85,0.4);  color: var(--danger); }

    /* Scrollbar */
    ::-webkit-scrollbar { width: 4px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

    /* Responsive */
    @media (max-width: 820px) {
      .app { grid-template-columns: 1fr; grid-template-rows: 58px auto auto; }
      html, body { overflow: auto; }
      .right { border-left: none; border-top: 1px solid var(--border); }
    }
  </style>
</head>
<body>

<!-- Toast -->
<div class="toast" id="toast"></div>

<!-- Success modal -->
<div class="overlay" id="overlay">
  <div class="success-modal">
    <div class="success-ico">✅</div>
    <div class="success-title">Payment Complete</div>
    <div class="success-amt" id="s-amt">R0.00</div>
    <div class="success-table" id="s-table">Table —</div>
    <div class="success-change" id="s-change"></div>
    <div class="success-btns">
      <button class="btn-done" onclick="closePaid()">Print / Send Receipt & Close</button>
      <button class="btn-new"  onclick="closePaid()">Done — Next Bill</button>
    </div>
  </div>
</div>

<!-- App -->
<div class="app">

  <!-- Header -->
  <header class="header">
    <div class="logo">
      <div class="logo-box">🍽️</div>
      <div class="logo-name">Byte<span>Savor</span></div>
    </div>
    <div class="hdiv"></div>
    <div class="hscreen">Payment</div>
    <div class="hright">
      <div class="staff-pill">
        <div class="sdot"></div>
        <span id="staff-name">Cashier</span>
      </div>
      <button class="btn-out" onclick="window.location.href='../logout.php'">Sign Out</button>
    </div>
  </header>

  <!-- LEFT: Open bills + bill detail -->
  <div class="left">

    <!-- Open bills -->
    <section>
      <p class="sec-title">Open Bills — Select to Pay</p>
      <div class="bills-grid" id="bills-grid"></div>
    </section>

    <!-- Bill detail (shown after selection) -->
    <div class="bill-box" id="bill-box">
      <div class="bill-box-head">
        <span class="bbh-table" id="bbh-table">—</span>
        <span class="bbh-type" id="bbh-type"></span>
        <span class="bbh-waiter" id="bbh-waiter">👤 —</span>
      </div>
      <div class="bill-items" id="bill-items"></div>
      <div class="bill-totals">
        <div class="tot-row"><span>Subtotal</span><span id="tot-sub">R0.00</span></div>
        <div class="tot-row"><span>VAT (15%)</span><span id="tot-vat">R0.00</span></div>
        <div class="tot-row grand">
          <span>Total Due</span>
          <span class="gamt" id="tot-grand">R0.00</span>
        </div>
        <div class="gratuity-note">
          ✏️ <span>Gratuity space is printed on receipt — customer fills in their own tip amount</span>
        </div>
      </div>
    </div>

  </div>

  <!-- RIGHT: Payment panel -->
  <div class="right">

    <div class="pay-header">
      <div class="pay-title">Amount Due</div>
      <div class="pay-amount" id="pay-amount">R0.00</div>
      <div class="pay-sub" id="pay-sub">Select a bill to begin</div>
      <div class="split-bar" id="split-bar">✂️ Split bill active — showing selected items only</div>
    </div>

    <!-- Payment methods -->
    <div class="pay-methods">
      <div class="method-title">Payment Method</div>
      <div class="methods-grid">
        <button class="method-btn" onclick="setMethod('cash')">
          <span class="m-ico">💵</span>Cash
        </button>
        <button class="method-btn" onclick="setMethod('card')">
          <span class="m-ico">💳</span>Card
        </button>
        <button class="method-btn" onclick="setMethod('tap')">
          <span class="m-ico">📲</span>Tap / NFC
        </button>
        <button class="method-btn" onclick="setMethod('eft')">
          <span class="m-ico">🏦</span>EFT
        </button>
        <button class="method-btn" onclick="setMethod('snapscan')">
          <span class="m-ico">📷</span>SnapScan
        </button>
        <button class="method-btn" onclick="setMethod('zapper')">
          <span class="m-ico">⚡</span>Zapper
        </button>
        <button class="method-btn" onclick="setMethod('ubereats')">
          <span class="m-ico">🚗</span>Uber Eats
        </button>
        <button class="method-btn" onclick="setMethod('mrd')">
          <span class="m-ico">🛵</span>Mr D
        </button>
      </div>
    </div>

    <!-- Cash: amount tendered + change -->
    <div class="cash-section" id="cash-section">
      <div class="cash-label">Amount Tendered</div>
      <div class="cash-input-wrap">
        <span class="cash-prefix">R</span>
        <input class="cash-input" type="number" id="cash-input"
               placeholder="0.00" step="0.01" min="0"
               oninput="calcChange()"/>
      </div>
      <div class="quick-cash" id="quick-cash"></div>
      <div class="change-display" id="change-display" style="display:none">
        <span class="change-label">Change Due</span>
        <span class="change-amt" id="change-amt">R0.00</span>
      </div>
    </div>

    <!-- Reference number (card / EFT / digital) -->
    <div class="ref-section" id="ref-section">
      <div class="cash-label" id="ref-label">Reference / Approval Number</div>
      <input class="ref-input" type="text" id="ref-input"
             placeholder="Enter reference number (optional)"/>
    </div>

    <!-- Receipt options -->
    <div class="receipt-section">
      <div class="method-title">Receipt Options</div>
      <div class="receipt-opts">
        <label class="receipt-chk">
          <input type="checkbox" id="chk-print" checked onchange="toggleReceipt('print')">
          🖨️ Print Receipt
        </label>
        <label class="receipt-chk">
          <input type="checkbox" id="chk-email" onchange="toggleReceipt('email')">
          📧 Email Receipt
        </label>
        <input class="receipt-sub-input" type="email" id="email-input"
               placeholder="customer@email.com"/>
        <label class="receipt-chk">
          <input type="checkbox" id="chk-wa" onchange="toggleReceipt('wa')">
          💬 WhatsApp Receipt
        </label>
        <input class="receipt-sub-input" type="tel" id="wa-input"
               placeholder="+27 82 000 0000"/>
      </div>
    </div>

    <!-- Actions -->
    <div class="pay-actions">
      <button class="btn-pay" id="btn-pay" onclick="processPayment()" disabled>
        Process Payment
      </button>
      <button class="btn-print" id="btn-print" onclick="printBill()" disabled>
        🖨️ Print Bill Only (No Payment)
      </button>
    </div>

  </div>

</div>

<script>
// ════════════════════════════════════════════
//  ByteSavor — Cashier / Payment Screen
//
//  SECURITY RULE:
//  A bill can only be CLOSED by the staff member
//  who created it. Others can VIEW and PRINT only.
//  Manager can see all bills but cannot close them.
//  This is enforced by comparing bill.staffId to
//  the current logged-in staff ID from the session.
//  In production: currentStaffId comes from PHP session.
// ════════════════════════════════════════════

// ── Current logged-in staff (from PHP session in production) ──
// <?php
//   session_start();
//   $staffId = $_SESSION['staff_id'] ?? '';
//   $staffName = $_SESSION['user_name'] ?? 'Staff';
//   $role = $_SESSION['role'] ?? '';
// ?>
// const CURRENT_STAFF_ID   = "<?= $staffId ?>";
// const CURRENT_STAFF_NAME = "<?= $staffName ?>";
// const CURRENT_ROLE       = "<?= $role ?>";

// DEMO values — replace with PHP above when deploying
const CURRENT_STAFF_ID   = 'W001';
const CURRENT_STAFF_NAME = 'Amahle';
const CURRENT_ROLE       = 'waiter';

document.getElementById('staff-name').textContent = CURRENT_STAFF_NAME;

// ── Demo open bills ──
// In production: fetched from api/orders.php?status=open
let BILLS = [
  {
    id: 2001, table: 'T2', type: 'dine_in',
    staffId: 'W001', staffName: 'Amahle',
    items: [
      { name: 'Beef Burger',    qty: 2, price: 145, takeaway: false, note: 'no onions' },
      { name: 'Chips',          qty: 2, price: 45,  takeaway: false, note: '' },
      { name: 'Soft Drink',     qty: 2, price: 35,  takeaway: false, note: '' },
    ]
  },
  {
    id: 2002, table: 'T4', type: 'dine_in',
    staffId: 'W002', staffName: 'Sipho',
    items: [
      { name: 'Beef Steak 300g', qty: 1, price: 265, takeaway: false, note: 'medium rare' },
      { name: 'Pasta Carbonara', qty: 1, price: 135, takeaway: false, note: '' },
      { name: 'Side Salad',      qty: 1, price: 55,  takeaway: false, note: '' },
    ]
  },
  {
    id: 2003, table: null, type: 'takeaway',
    staffId: 'W001', staffName: 'Amahle',
    items: [
      { name: 'BBQ Chicken Pizza', qty: 1, price: 145, takeaway: true, note: '' },
      { name: 'Onion Rings',       qty: 1, price: 50,  takeaway: true, note: '' },
      { name: 'Soft Drink',        qty: 2, price: 35,  takeaway: true, note: 'no ice' },
    ]
  },
  {
    id: 2004, table: null, type: 'online',
    staffId: 'SYSTEM', staffName: 'Online Order',
    items: [
      { name: 'Meat Lovers Pizza', qty: 2, price: 155, takeaway: true, note: '' },
      { name: 'Chips',             qty: 2, price: 45,  takeaway: true, note: '' },
    ]
  },
];

// ── State ──
let selectedBill   = null;
let selectedMethod = null;
let splitActive    = false;
let splitSelected  = new Set(); // indices of items selected for split

// ── Helpers ──
function billTotal(bill) {
  const sub = bill.items.reduce((s, i) => s + i.price * i.qty, 0);
  return { sub, vat: sub * 0.15, grand: sub * 1.15 };
}

function splitTotal() {
  let sub = 0;
  splitSelected.forEach(idx => {
    const i = selectedBill.items[idx];
    sub += i.price * i.qty;
  });
  return { sub, vat: sub * 0.15, grand: sub * 1.15 };
}

function fmt(n) { return 'R' + n.toFixed(2); }

function isMine(bill) {
  // Online orders (SYSTEM) can be closed by cashier or manager
  if (bill.staffId === 'SYSTEM') return CURRENT_ROLE === 'cashier' || CURRENT_ROLE === 'admin' || CURRENT_ROLE === 'manager';
  return bill.staffId === CURRENT_STAFF_ID;
}

// ── Render open bills ──
function renderBills() {
  const grid = document.getElementById('bills-grid');
  grid.innerHTML = BILLS.map(b => {
    const { grand } = billTotal(b);
    const mine = isMine(b);
    const label = b.table ? b.table : b.type === 'takeaway' ? '🥡 TW' : '📱 Online';
    return `
      <div class="bill-btn ${mine ? 'mine' : 'locked'} ${selectedBill?.id === b.id ? 'selected' : ''}"
           onclick="${mine ? `selectBill(${b.id})` : `lockedBill()`}">
        ${!mine ? `<span class="b-lock">🔒</span>` : ''}
        <div class="b-table">${label}</div>
        <div class="b-items">${b.items.length} items</div>
        <div class="b-total">${fmt(grand)}</div>
        <div class="b-owner">👤 ${b.staffName}</div>
      </div>
    `;
  }).join('');
}

function lockedBill() {
  showToast('🔒 This bill was opened by another staff member', 'err');
}

// ── Select a bill ──
function selectBill(id) {
  selectedBill  = BILLS.find(b => b.id === id);
  selectedMethod = null;
  splitActive   = false;
  splitSelected = new Set();
  renderBills();
  renderBillDetail();
  updatePayPanel();
  document.getElementById('bill-box').classList.add('show');
  document.getElementById('cash-input').value = '';
  document.getElementById('change-display').style.display = 'none';
  document.getElementById('ref-input').value = '';
  document.querySelectorAll('.method-btn').forEach(b => b.classList.remove('active'));
}

// ── Render bill detail ──
function renderBillDetail() {
  const b = selectedBill;
  const { sub, vat, grand } = splitActive ? splitTotal() : billTotal(b);
  const typeLabel = b.type === 'dine_in' ? '🍽️ Dine In' : b.type === 'takeaway' ? '🥡 Takeaway' : '📱 Online';
  const typeCls   = b.type === 'dine_in' ? 'type-dine' : b.type === 'takeaway' ? 'type-tw' : 'type-online';

  document.getElementById('bbh-table').textContent = b.table ? 'Table ' + b.table : (b.type === 'takeaway' ? 'Takeaway' : 'Online Order');
  document.getElementById('bbh-type').className    = 'bbh-type ' + typeCls;
  document.getElementById('bbh-type').textContent  = typeLabel;
  document.getElementById('bbh-waiter').textContent = '👤 ' + b.staffName;

  document.getElementById('bill-items').innerHTML = b.items.map((item, idx) => `
    <div class="bi-row">
      <div class="bi-qty">${item.qty}</div>
      <div class="bi-name">${item.name}${item.note ? ` <span style="font-size:11px;color:var(--muted);font-style:italic">(${item.note})</span>` : ''}</div>
      ${item.takeaway ? '<span class="bi-tw">🥡</span>' : ''}
      <div class="bi-price">${fmt(item.price * item.qty)}</div>
      <div class="bi-split ${splitSelected.has(idx) ? 'on' : ''}"
           onclick="toggleSplit(${idx})" title="Split this item">✂</div>
    </div>
  `).join('');

  document.getElementById('tot-sub').textContent   = fmt(sub);
  document.getElementById('tot-vat').textContent   = fmt(vat);
  document.getElementById('tot-grand').textContent = fmt(grand);
  document.getElementById('split-bar').classList.toggle('show', splitActive && splitSelected.size > 0);
}

// ── Split bill ──
function toggleSplit(idx) {
  if (splitSelected.has(idx)) {
    splitSelected.delete(idx);
  } else {
    splitSelected.add(idx);
  }
  splitActive = splitSelected.size > 0;
  renderBillDetail();
  updatePayPanel();
}

// ── Update payment panel amounts ──
function updatePayPanel() {
  if (!selectedBill) return;
  const { grand } = splitActive ? splitTotal() : billTotal(selectedBill);
  document.getElementById('pay-amount').textContent = fmt(grand);
  document.getElementById('pay-sub').textContent    = splitActive
    ? `Split: ${splitSelected.size} of ${selectedBill.items.length} items`
    : `${selectedBill.items.length} items · ${selectedBill.staffName}`;
  document.getElementById('btn-pay').disabled   = !selectedMethod;
  document.getElementById('btn-print').disabled = false;
  calcChange();
  generateQuickCash(grand);
}

// ── Payment method ──
function setMethod(method) {
  if (!selectedBill) { showToast('Please select a bill first', 'err'); return; }
  selectedMethod = method;
  document.querySelectorAll('.method-btn').forEach(b => b.classList.remove('active'));
  event.currentTarget.classList.add('active');
  document.getElementById('btn-pay').disabled = false;

  // Show/hide cash section
  const isCash = method === 'cash';
  const needsRef = ['card','eft','tap','snapscan','zapper'].includes(method);
  const isDelivery = ['ubereats','mrd'].includes(method);

  document.getElementById('cash-section').classList.toggle('show', isCash);
  document.getElementById('ref-section').classList.toggle('show', needsRef);

  // Update ref label
  const refLabels = {
    card: 'Card Approval Number',
    eft:  'EFT Reference Number',
    tap:  'Transaction Reference',
    snapscan: 'SnapScan Reference',
    zapper:   'Zapper Reference',
  };
  if (refLabels[method]) {
    document.getElementById('ref-label').textContent = refLabels[method];
    document.getElementById('ref-input').placeholder = 'Enter reference (optional)';
  }

  if (isDelivery) {
    document.getElementById('ref-section').classList.add('show');
    document.getElementById('ref-label').textContent = method === 'ubereats' ? 'Uber Eats Order ID' : 'Mr D Order ID';
    document.getElementById('ref-input').placeholder = 'Enter order ID';
  }
}

// ── Cash change calculator ──
function calcChange() {
  if (selectedMethod !== 'cash' || !selectedBill) return;
  const tendered = parseFloat(document.getElementById('cash-input').value) || 0;
  const { grand } = splitActive ? splitTotal() : billTotal(selectedBill);
  const change = tendered - grand;
  const el = document.getElementById('change-display');
  const amt = document.getElementById('change-amt');

  if (tendered > 0) {
    el.style.display = 'flex';
    amt.textContent = fmt(Math.abs(change));
    if (change < 0) {
      amt.className = 'change-amt change-neg';
      document.getElementById('change-display').style.borderColor = 'rgba(224,85,85,0.25)';
      document.getElementById('change-display').style.background  = 'rgba(224,85,85,0.06)';
    } else {
      amt.className = 'change-amt';
      document.getElementById('change-display').style.borderColor = 'rgba(62,184,122,0.25)';
      document.getElementById('change-display').style.background  = 'rgba(62,184,122,0.08)';
    }
  } else {
    el.style.display = 'none';
  }
}

// ── Quick cash buttons ──
function generateQuickCash(grand) {
  const rounds = [50,100,150,200,300,500].filter(v => v >= grand);
  const nearest = Math.ceil(grand / 10) * 10;
  const options = [...new Set([nearest, ...rounds])].slice(0, 5);
  document.getElementById('quick-cash').innerHTML = options.map(v =>
    `<button class="qc-btn" onclick="setTendered(${v})">R${v}</button>`
  ).join('');
}

function setTendered(amt) {
  document.getElementById('cash-input').value = amt;
  calcChange();
}

// ── Receipt toggles ──
function toggleReceipt(type) {
  if (type === 'email') {
    const checked = document.getElementById('chk-email').checked;
    document.getElementById('email-input').classList.toggle('show', checked);
  }
  if (type === 'wa') {
    const checked = document.getElementById('chk-wa').checked;
    document.getElementById('wa-input').classList.toggle('show', checked);
  }
}

// ── Process payment ──
function processPayment() {
  if (!selectedBill || !selectedMethod) return;
  const { grand } = splitActive ? splitTotal() : billTotal(selectedBill);

  // Cash: check enough tendered
  if (selectedMethod === 'cash') {
    const tendered = parseFloat(document.getElementById('cash-input').value) || 0;
    if (tendered < grand) {
      showToast('⚠️ Amount tendered is less than total', 'err');
      return;
    }
  }

  const change = selectedMethod === 'cash'
    ? (parseFloat(document.getElementById('cash-input').value) || 0) - grand
    : 0;

  // Show success modal
  document.getElementById('s-amt').textContent   = fmt(grand);
  document.getElementById('s-table').textContent = selectedBill.table
    ? `Table ${selectedBill.table} · ${selectedBill.staffName}`
    : `${selectedBill.type === 'takeaway' ? 'Takeaway' : 'Online'} · ${selectedBill.staffName}`;

  const sc = document.getElementById('s-change');
  if (selectedMethod === 'cash' && change >= 0) {
    sc.textContent = `Change to return: ${fmt(change)}`;
    sc.classList.add('show');
  } else {
    sc.classList.remove('show');
  }

  document.getElementById('overlay').classList.add('show');

  // In production: POST to api/payments.php
  // Remove bill from list
  BILLS = BILLS.filter(b => b.id !== selectedBill.id);
}

function closePaid() {
  document.getElementById('overlay').classList.remove('show');
  selectedBill   = null;
  selectedMethod = null;
  splitActive    = false;
  splitSelected  = new Set();
  renderBills();
  document.getElementById('bill-box').classList.remove('show');
  document.getElementById('pay-amount').textContent = 'R0.00';
  document.getElementById('pay-sub').textContent    = 'Select a bill to begin';
  document.getElementById('btn-pay').disabled   = true;
  document.getElementById('btn-print').disabled = true;
  document.getElementById('split-bar').classList.remove('show');
  document.querySelectorAll('.method-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('cash-section').classList.remove('show');
  document.getElementById('ref-section').classList.remove('show');
}

// ── Print bill only ──
function printBill() {
  if (!selectedBill) return;
  showToast('🖨️ Sending to printer...', 'ok');
  // In production: POST to api/print.php
}

// ── Toast ──
let toastTimer;
function showToast(msg, type) {
  const t = document.getElementById('toast');
  t.textContent = msg; t.className = 'toast show ' + (type || '');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => { t.className = 'toast'; }, 2800);
}

// ── Initial render ──
renderBills();
</script>

</body>
</html>
