<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"/>
  <title>ByteSavor — Kitchen Display</title>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --black:      #060809;
      --dark:       #0c0f14;
      --card:       #101419;
      --card2:      #141a21;
      --border:     #1a2030;
      --gold:       #f5a623;
      --orange:     #e8520a;
      --text:       #eceae4;
      --muted:      #4a5568;
      --soft:       #7a8494;
      --new:        #4a9eff;       /* Blue  — New */
      --preparing:  #f5a623;       /* Gold  — Preparing */
      --ready:      #3eb87a;       /* Green — Ready */
      --collected:  #4a5568;       /* Grey  — Collected */
      --tw:         #e8520a;       /* Orange — Takeaway */
      --radius:     12px;
    }

    html, body {
      height: 100%; overflow: hidden;
      background: var(--black);
      color: var(--text);
      font-family: 'Outfit', sans-serif;
      -webkit-tap-highlight-color: transparent;
    }

    /* ══════════════════
       HEADER
    ══════════════════ */
    .header {
      height: 58px;
      display: flex; align-items: center;
      padding: 0 20px; gap: 16px;
      background: var(--dark);
      border-bottom: 1px solid var(--border);
      position: relative; z-index: 10;
    }
    .logo {
      display: flex; align-items: center; gap: 9px; flex-shrink: 0;
    }
    .logo-box {
      width: 32px; height: 32px;
      background: linear-gradient(135deg, var(--gold), var(--orange));
      border-radius: 8px; display: grid; place-items: center; font-size: 15px;
    }
    .logo-name {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 20px; letter-spacing: 1px;
    }
    .logo-name span { color: var(--gold); }

    .header-div { width: 1px; height: 24px; background: var(--border); }

    .header-title {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 16px; letter-spacing: 0.15em;
      color: var(--soft); text-transform: uppercase;
    }

    /* Live clock */
    .clock {
      margin-left: auto;
      font-family: 'Bebas Neue', sans-serif;
      font-size: 22px; letter-spacing: 2px; color: var(--gold);
    }

    /* Order counts in header */
    .header-counts {
      display: flex; gap: 10px; align-items: center;
    }
    .h-count {
      display: flex; align-items: center; gap: 6px;
      padding: 4px 12px;
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: 20px;
      font-size: 12px; font-weight: 600;
    }
    .h-count .dot {
      width: 7px; height: 7px; border-radius: 50%;
    }
    .dot-new        { background: var(--new);      box-shadow: 0 0 6px rgba(74,158,255,0.6);  }
    .dot-preparing  { background: var(--preparing); box-shadow: 0 0 6px rgba(245,166,35,0.6);  }
    .dot-ready      { background: var(--ready);     box-shadow: 0 0 6px rgba(62,184,122,0.6);  }

    /* ══════════════════
       TAB BAR
    ══════════════════ */
    .tabs {
      display: flex; gap: 0;
      background: var(--dark);
      border-bottom: 1px solid var(--border);
      padding: 0 20px;
    }
    .tab {
      padding: 12px 22px;
      font-size: 13px; font-weight: 600;
      color: var(--muted); cursor: pointer;
      border-bottom: 2px solid transparent;
      transition: all 0.15s;
      display: flex; align-items: center; gap: 8px;
      user-select: none;
    }
    .tab:hover { color: var(--soft); }
    .tab.active { color: var(--gold); border-bottom-color: var(--gold); }
    .tab-badge {
      padding: 2px 8px;
      border-radius: 10px;
      font-size: 11px; font-weight: 700;
      background: var(--card); color: var(--soft);
    }
    .tab.active .tab-badge { background: rgba(245,166,35,0.15); color: var(--gold); }

    /* ══════════════════
       MAIN GRID
    ══════════════════ */
    .main {
      height: calc(100vh - 58px - 45px);
      overflow-y: auto;
      padding: 16px 20px;
    }

    /* Status columns */
    .columns {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 14px;
      min-height: 100%;
    }

    .col-header {
      display: flex; align-items: center; gap: 8px;
      padding: 10px 14px;
      border-radius: var(--radius) var(--radius) 0 0;
      margin-bottom: 10px;
      border-bottom: 2px solid;
    }
    .col-header .col-dot {
      width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;
    }
    .col-header .col-title {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 16px; letter-spacing: 0.15em;
      flex: 1;
    }
    .col-header .col-count {
      font-size: 12px; font-weight: 700;
      padding: 2px 8px;
      border-radius: 10px;
    }

    /* Column colours */
    .col-new      .col-header { background: rgba(74,158,255,0.06);   border-color: var(--new);      }
    .col-new      .col-dot    { background: var(--new); box-shadow: 0 0 8px rgba(74,158,255,0.5);  }
    .col-new      .col-title  { color: var(--new);   }
    .col-new      .col-count  { background: rgba(74,158,255,0.12);  color: var(--new);      }

    .col-prep     .col-header { background: rgba(245,166,35,0.06);   border-color: var(--preparing); }
    .col-prep     .col-dot    { background: var(--preparing); box-shadow: 0 0 8px rgba(245,166,35,0.5); }
    .col-prep     .col-title  { color: var(--preparing); }
    .col-prep     .col-count  { background: rgba(245,166,35,0.12);  color: var(--preparing); }

    .col-ready    .col-header { background: rgba(62,184,122,0.06);   border-color: var(--ready);    }
    .col-ready    .col-dot    { background: var(--ready); box-shadow: 0 0 8px rgba(62,184,122,0.5); }
    .col-ready    .col-title  { color: var(--ready);  }
    .col-ready    .col-count  { background: rgba(62,184,122,0.12);  color: var(--ready);    }

    .col-done     .col-header { background: rgba(74,85,104,0.06);    border-color: var(--collected); }
    .col-done     .col-dot    { background: var(--collected); }
    .col-done     .col-title  { color: var(--soft);   }
    .col-done     .col-count  { background: rgba(74,85,104,0.15);   color: var(--soft);     }

    /* ══════════════════
       ORDER CARDS
    ══════════════════ */
    .order-card {
      background: var(--card);
      border: 1.5px solid var(--border);
      border-radius: var(--radius);
      margin-bottom: 10px;
      overflow: hidden;
      animation: cardIn 0.25s ease;
      transition: border-color 0.2s;
    }
    .order-card:hover { border-color: rgba(245,166,35,0.25); }
    @keyframes cardIn {
      from { opacity: 0; transform: translateY(8px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    /* Card top bar — colour coded by status */
    .card-bar {
      height: 3px;
      width: 100%;
    }
    .status-new       .card-bar { background: var(--new);      }
    .status-preparing .card-bar { background: var(--preparing); }
    .status-ready     .card-bar { background: var(--ready);    }
    .status-collected .card-bar { background: var(--collected); }

    .card-head {
      padding: 11px 14px 8px;
      display: flex; align-items: center; gap: 8px;
    }
    .card-order-num {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 20px; letter-spacing: 1px; color: var(--text);
    }
    .card-type {
      padding: 3px 10px;
      border-radius: 20px;
      font-size: 11px; font-weight: 700;
      letter-spacing: 0.05em; text-transform: uppercase;
    }
    .type-dine    { background: rgba(74,158,255,0.12); color: var(--new);  border: 1px solid rgba(74,158,255,0.25); }
    .type-takeaway{ background: rgba(232,82,10,0.12);  color: var(--tw);   border: 1px solid rgba(232,82,10,0.25); }
    .type-online  { background: rgba(62,184,122,0.12); color: var(--ready);border: 1px solid rgba(62,184,122,0.25);}

    .card-table {
      margin-left: auto;
      font-size: 12px; color: var(--soft); font-weight: 500;
    }

    /* Timer */
    .card-timer {
      padding: 0 14px 8px;
      font-size: 12px; color: var(--muted);
      display: flex; align-items: center; gap: 6px;
    }
    .timer-val { font-weight: 600; }
    .timer-val.warn  { color: var(--preparing); }
    .timer-val.urgent{ color: var(--tw); }
    .timer-val.crit  { color: #e05555; animation: pulse 1s ease infinite; }
    @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.5} }

    /* Items list */
    .card-items {
      padding: 0 14px 10px;
      display: flex; flex-direction: column; gap: 5px;
    }
    .card-item {
      display: flex; align-items: flex-start; gap: 8px;
      font-size: 13px;
    }
    .ci-qty {
      width: 22px; height: 22px;
      background: var(--card2); border-radius: 6px;
      color: var(--gold); font-size: 12px; font-weight: 700;
      display: grid; place-items: center; flex-shrink: 0;
    }
    .ci-name { flex: 1; line-height: 1.3; font-weight: 500; }
    .ci-tw {
      font-size: 10px; color: var(--tw);
      background: rgba(232,82,10,0.1);
      border-radius: 4px; padding: 1px 5px;
      flex-shrink: 0;
    }
    .ci-note { font-size: 11px; color: var(--muted); font-style: italic; margin-top: 1px; }

    /* Order note */
    .card-note {
      margin: 0 14px 10px;
      padding: 7px 10px;
      background: var(--card2);
      border-left: 2px solid var(--preparing);
      border-radius: 0 6px 6px 0;
      font-size: 11.5px; color: var(--soft);
      font-style: italic;
    }

    /* Action button */
    .card-action {
      padding: 0 14px 14px;
    }
    .btn-advance {
      width: 100%; padding: 10px;
      border: none; border-radius: 8px;
      font-family: 'Outfit', sans-serif;
      font-size: 13px; font-weight: 700;
      cursor: pointer; transition: all 0.15s;
      letter-spacing: 0.02em;
    }
    /* Button colours per status */
    .status-new       .btn-advance { background: rgba(74,158,255,0.15); color: var(--new);      border: 1px solid rgba(74,158,255,0.3); }
    .status-new       .btn-advance:hover { background: var(--new); color: #060809; }
    .status-preparing .btn-advance { background: rgba(245,166,35,0.15); color: var(--preparing); border: 1px solid rgba(245,166,35,0.3); }
    .status-preparing .btn-advance:hover { background: var(--preparing); color: #060809; }
    .status-ready     .btn-advance { background: rgba(62,184,122,0.15); color: var(--ready);    border: 1px solid rgba(62,184,122,0.3); }
    .status-ready     .btn-advance:hover { background: var(--ready); color: #060809; }
    .status-collected .btn-advance { background: var(--card2); color: var(--muted); border: 1px solid var(--border); cursor: default; }

    /* Waiter-called badge */
    .card-waiter-ping {
      margin: 0 14px 8px;
      padding: 5px 10px;
      background: rgba(62,184,122,0.08);
      border: 1px solid rgba(62,184,122,0.25);
      border-radius: 6px;
      font-size: 11px; color: var(--ready);
      display: flex; align-items: center; gap: 6px;
    }

    /* Empty column */
    .col-empty {
      padding: 30px 14px;
      text-align: center;
      color: var(--muted); font-size: 13px; line-height: 1.7;
    }
    .col-empty .e-ico { font-size: 28px; opacity: 0.2; margin-bottom: 8px; }

    /* Scrollbar */
    ::-webkit-scrollbar { width: 4px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

    /* Alert bell animation */
    .bell { animation: bell 0.5s ease 3; }
    @keyframes bell {
      0%,100%{ transform: rotate(0); }
      25%    { transform: rotate(-15deg); }
      75%    { transform: rotate(15deg); }
    }

    /* Responsive */
    @media (max-width: 900px) {
      .columns { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 560px) {
      .columns { grid-template-columns: 1fr; }
      .tabs    { overflow-x: auto; }
    }
  </style>
</head>
<body>

<!-- ══ HEADER ══ -->
<div class="header">
  <div class="logo">
    <div class="logo-box">🍽️</div>
    <div class="logo-name">Byte<span>Savor</span></div>
  </div>
  <div class="header-div"></div>
  <div class="header-title">Kitchen Display</div>

  <div class="header-counts" id="header-counts"></div>
  <div class="clock" id="clock">00:00:00</div>
</div>

<!-- ══ TABS ══ -->
<div class="tabs">
  <div class="tab active" onclick="setTab('all')"    id="tab-all">
    All Orders <span class="tab-badge" id="tbadge-all">0</span>
  </div>
  <div class="tab" onclick="setTab('dine_in')"  id="tab-dine_in">
    🍽️ Dine In <span class="tab-badge" id="tbadge-dine_in">0</span>
  </div>
  <div class="tab" onclick="setTab('takeaway')" id="tab-takeaway">
    🥡 Takeaway <span class="tab-badge" id="tbadge-takeaway">0</span>
  </div>
  <div class="tab" onclick="setTab('online')"   id="tab-online">
    📱 Online <span class="tab-badge" id="tbadge-online">0</span>
  </div>
</div>

<!-- ══ COLUMNS ══ -->
<div class="main">
  <div class="columns">
    <div class="col-new"   id="col-new">
      <div class="col-header">
        <div class="col-dot"></div>
        <div class="col-title">New</div>
        <div class="col-count" id="cnt-new">0</div>
      </div>
      <div id="cards-new"></div>
    </div>
    <div class="col-prep"  id="col-prep">
      <div class="col-header">
        <div class="col-dot"></div>
        <div class="col-title">Preparing</div>
        <div class="col-count" id="cnt-prep">0</div>
      </div>
      <div id="cards-prep"></div>
    </div>
    <div class="col-ready" id="col-ready">
      <div class="col-header">
        <div class="col-dot"></div>
        <div class="col-title">Ready</div>
        <div class="col-count" id="cnt-ready">0</div>
      </div>
      <div id="cards-ready"></div>
    </div>
    <div class="col-done"  id="col-done">
      <div class="col-header">
        <div class="col-dot"></div>
        <div class="col-title">Collected</div>
        <div class="col-count" id="cnt-done">0</div>
      </div>
      <div id="cards-done"></div>
    </div>
  </div>
</div>

<script>
// ════════════════════════════════════════════
//  ByteSavor — Kitchen Display
//  Demo data below. In production this will
//  poll api/orders.php every few seconds
//  and update live without page refresh.
// ════════════════════════════════════════════

// ── Status flow ──
const STATUS_FLOW = {
  new:       { next: 'preparing', label: '▶ Start Preparing' },
  preparing: { next: 'ready',     label: '✓ Mark as Ready'   },
  ready:     { next: 'collected', label: '📦 Mark Collected'  },
  collected: { next: null,        label: 'Collected'          },
};

// ── Demo orders ──
// In production: fetched from api/orders.php
let ORDERS = [
  {
    id: 1001, orderNum: '#1001', table: 'T2',
    type: 'dine_in', status: 'new',
    createdAt: Date.now() - (3 * 60 * 1000),
    note: 'No onions on the burger please',
    items: [
      { name: 'Beef Burger',   qty: 2, note: '',            takeaway: false },
      { name: 'Chips',         qty: 2, note: 'extra crispy',takeaway: false },
      { name: 'Soft Drink',    qty: 2, note: '',            takeaway: false },
    ]
  },
  {
    id: 1002, orderNum: '#1002', table: 'T4',
    type: 'dine_in', status: 'preparing',
    createdAt: Date.now() - (8 * 60 * 1000),
    note: '',
    items: [
      { name: 'Beef Steak 300g', qty: 1, note: 'medium rare', takeaway: false },
      { name: 'Side Salad',      qty: 1, note: '',            takeaway: false },
      { name: 'Pasta Carbonara', qty: 1, note: 'extra cheese',takeaway: false },
    ]
  },
  {
    id: 1003, orderNum: '#1003', table: 'T6',
    type: 'dine_in', status: 'new',
    createdAt: Date.now() - (1 * 60 * 1000),
    note: '',
    items: [
      { name: 'Grilled Chicken', qty: 1, note: '',    takeaway: true  },
      { name: 'Chips',           qty: 1, note: '',    takeaway: true  },
      { name: 'Margherita',      qty: 1, note: '',    takeaway: false },
      { name: 'Coffee',          qty: 2, note: 'oat milk', takeaway: false },
    ]
  },
  {
    id: 1004, orderNum: '#1004', table: null,
    type: 'takeaway', status: 'preparing',
    createdAt: Date.now() - (12 * 60 * 1000),
    note: 'Customer name: Sipho',
    items: [
      { name: 'BBQ Chicken Pizza', qty: 1, note: '',          takeaway: true },
      { name: 'Onion Rings',       qty: 1, note: '',          takeaway: true },
      { name: 'Soft Drink',        qty: 2, note: 'no ice',   takeaway: true },
    ]
  },
  {
    id: 1005, orderNum: '#1005', table: null,
    type: 'online', status: 'ready',
    createdAt: Date.now() - (20 * 60 * 1000),
    note: 'Delivery: 14 Main St',
    items: [
      { name: 'Meat Lovers Pizza', qty: 2, note: '',  takeaway: true },
      { name: 'Garlic Bread',      qty: 1, note: '',  takeaway: true },
    ]
  },
  {
    id: 1006, orderNum: '#1006', table: 'T1',
    type: 'dine_in', status: 'ready',
    createdAt: Date.now() - (18 * 60 * 1000),
    note: '',
    items: [
      { name: 'Calamari',         qty: 1, note: '', takeaway: false },
      { name: 'Fish & Chips',     qty: 1, note: '', takeaway: false },
      { name: 'Cheesecake',       qty: 2, note: '', takeaway: false },
    ]
  },
];

// ── State ──
let activeTab = 'all';

// ── Clock ──
function updateClock() {
  const now = new Date();
  const h = String(now.getHours()).padStart(2,'0');
  const m = String(now.getMinutes()).padStart(2,'0');
  const s = String(now.getSeconds()).padStart(2,'0');
  document.getElementById('clock').textContent = `${h}:${m}:${s}`;
}
setInterval(updateClock, 1000);
updateClock();

// ── Tab switching ──
function setTab(tab) {
  activeTab = tab;
  document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
  document.getElementById('tab-' + tab).classList.add('active');
  render();
}

// ── Advance order status ──
function advanceStatus(orderId) {
  const order = ORDERS.find(o => o.id === orderId);
  if (!order) return;
  const flow = STATUS_FLOW[order.status];
  if (!flow.next) return;
  order.status = flow.next;
  render();
}

// ── Get elapsed time string ──
function elapsed(createdAt) {
  const mins = Math.floor((Date.now() - createdAt) / 60000);
  if (mins < 1)  return { label: 'Just now',      cls: '' };
  if (mins < 10) return { label: `${mins}m`,       cls: '' };
  if (mins < 15) return { label: `${mins}m ⚠️`,   cls: 'warn' };
  if (mins < 20) return { label: `${mins}m 🔥`,   cls: 'urgent' };
  return               { label: `${mins}m 🚨`,     cls: 'crit' };
}

// ── Render a single order card ──
function renderCard(order) {
  const flow   = STATUS_FLOW[order.status];
  const time   = elapsed(order.createdAt);
  const typeLabel = order.type === 'dine_in'   ? '🍽️ Dine In'
                  : order.type === 'takeaway' ? '🥡 Takeaway'
                  : '📱 Online';
  const typeClass = order.type === 'dine_in'   ? 'type-dine'
                  : order.type === 'takeaway' ? 'type-takeaway'
                  : 'type-online';

  return `
    <div class="order-card status-${order.status}" id="card-${order.id}">
      <div class="card-bar"></div>
      <div class="card-head">
        <span class="card-order-num">${order.orderNum}</span>
        <span class="card-type ${typeClass}">${typeLabel}</span>
        ${order.table ? `<span class="card-table">🪑 ${order.table}</span>` : ''}
      </div>
      <div class="card-timer">
        ⏱ <span class="timer-val ${time.cls}">${time.label}</span>
      </div>
      <div class="card-items">
        ${order.items.map(i => `
          <div class="card-item">
            <div class="ci-qty">${i.qty}</div>
            <div>
              <div class="ci-name">
                ${i.name}
                ${i.takeaway ? '<span class="ci-tw">🥡 TW</span>' : ''}
              </div>
              ${i.note ? `<div class="ci-note">${i.note}</div>` : ''}
            </div>
          </div>
        `).join('')}
      </div>
      ${order.note ? `<div class="card-note">📋 ${order.note}</div>` : ''}
      <div class="card-action">
        <button class="btn-advance"
                onclick="${flow.next ? `advanceStatus(${order.id})` : ''}"
                ${!flow.next ? 'disabled' : ''}>
          ${flow.label}
        </button>
      </div>
    </div>
  `;
}

// ── Main render ──
function render() {
  // Filter by active tab
  const visible = activeTab === 'all'
    ? ORDERS
    : ORDERS.filter(o => o.type === activeTab);

  // Split by status
  const byStatus = {
    new:       visible.filter(o => o.status === 'new'),
    preparing: visible.filter(o => o.status === 'preparing'),
    ready:     visible.filter(o => o.status === 'ready'),
    collected: visible.filter(o => o.status === 'collected'),
  };

  // Render each column
  const cols = [
    { key: 'new',       el: 'cards-new',   cnt: 'cnt-new'   },
    { key: 'preparing', el: 'cards-prep',  cnt: 'cnt-prep'  },
    { key: 'ready',     el: 'cards-ready', cnt: 'cnt-ready' },
    { key: 'collected', el: 'cards-done',  cnt: 'cnt-done'  },
  ];

  cols.forEach(({ key, el, cnt }) => {
    const orders = byStatus[key];
    document.getElementById(cnt).textContent = orders.length;
    document.getElementById(el).innerHTML = orders.length === 0
      ? `<div class="col-empty">
           <div class="e-ico">${key==='new'?'🆕':key==='preparing'?'👨‍🍳':key==='ready'?'✅':'📦'}</div>
           No ${key} orders
         </div>`
      : orders.map(renderCard).join('');
  });

  // Update tab badges
  const allTypes = ['dine_in','takeaway','online'];
  document.getElementById('tbadge-all').textContent = visible.filter(o => o.status !== 'collected').length;
  allTypes.forEach(t => {
    const count = ORDERS.filter(o => o.type === t && o.status !== 'collected').length;
    document.getElementById('tbadge-' + t).textContent = count;
  });

  // Header counts
  document.getElementById('header-counts').innerHTML = `
    <div class="h-count"><div class="dot dot-new"></div> ${byStatus.new.length} New</div>
    <div class="h-count"><div class="dot dot-preparing"></div> ${byStatus.preparing.length} Preparing</div>
    <div class="h-count"><div class="dot dot-ready"></div> ${byStatus.ready.length} Ready</div>
  `;
}

// ── Initial render ──
render();

// ── Auto-refresh every 30 seconds ──
// In production: replace render() with a fetch() to api/orders.php
setInterval(() => {
  render(); // re-renders timers too
}, 30000);

// ── Tick timers every 60 seconds ──
setInterval(render, 60000);
</script>

</body>
</html>
