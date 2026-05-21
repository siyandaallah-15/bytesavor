<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"/>
  <title>ByteSavor — Admin</title>
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
      --purple:  #9b72ff;
      --teal:    #2dd4bf;
      --radius:  10px;
    }

    html, body {
      height: 100%; background: var(--black);
      color: var(--text); font-family: 'Outfit', sans-serif;
      overflow: hidden;
    }

    /* ══ LAYOUT ══ */
    .app {
      display: grid;
      grid-template-rows: 58px 44px 1fr;
      height: 100vh;
    }

    /* ══ HEADER ══ */
    .header {
      display: flex; align-items: center;
      padding: 0 20px; gap: 16px;
      background: var(--dark); border-bottom: 1px solid var(--border); z-index: 20;
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
    .hscreen { font-family: 'Bebas Neue', sans-serif; font-size: 16px; letter-spacing: 0.15em; color: var(--soft); }
    .hright { margin-left: auto; display: flex; align-items: center; gap: 10px; }
    .staff-pill {
      display: flex; align-items: center; gap: 7px; padding: 5px 13px;
      background: var(--card); border: 1px solid var(--border);
      border-radius: 20px; font-size: 12px; color: var(--soft);
    }
    .sdot { width: 7px; height: 7px; border-radius: 50%; background: var(--orange); box-shadow: 0 0 6px rgba(232,82,10,0.6); }
    .btn-out {
      padding: 5px 13px; background: transparent;
      border: 1px solid var(--border); border-radius: 8px;
      color: var(--soft); font-family: 'Outfit', sans-serif;
      font-size: 12px; cursor: pointer; transition: all 0.15s;
    }
    .btn-out:hover { border-color: var(--danger); color: var(--danger); }

    /* ══ TABS ══ */
    .tabs {
      display: flex; background: var(--dark);
      border-bottom: 1px solid var(--border);
      padding: 0 20px; overflow-x: auto; gap: 0;
    }
    .tab {
      padding: 0 18px; height: 44px;
      display: flex; align-items: center; gap: 7px;
      font-size: 13px; font-weight: 600; color: var(--muted);
      cursor: pointer; border-bottom: 2px solid transparent;
      transition: all 0.15s; white-space: nowrap; user-select: none;
    }
    .tab:hover { color: var(--soft); }
    .tab.on { color: var(--gold); border-bottom-color: var(--gold); }

    /* ══ MAIN ══ */
    .main { overflow-y: auto; padding: 20px; display: none; }
    .main.on { display: block; }

    /* ══ SHARED ══ */
    .sec {
      font-family: 'Bebas Neue', sans-serif; font-size: 12px;
      letter-spacing: 0.2em; color: var(--muted); text-transform: uppercase;
      margin-bottom: 12px;
    }
    .sec-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
    .card {
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--radius); overflow: hidden;
    }
    .card-head {
      padding: 12px 16px; background: var(--card2);
      border-bottom: 1px solid var(--border);
      display: flex; align-items: center; justify-content: space-between;
    }
    .card-head-title { font-size: 13px; font-weight: 700; }

    /* Toolbar */
    .toolbar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 16px; }
    .btn-primary {
      padding: 9px 18px;
      background: linear-gradient(135deg, var(--gold), var(--orange));
      border: none; border-radius: var(--radius);
      color: #080a0d; font-family: 'Outfit', sans-serif;
      font-size: 13px; font-weight: 700; cursor: pointer; transition: all 0.15s;
    }
    .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 16px rgba(245,166,35,0.25); }
    .btn-secondary {
      padding: 9px 16px; background: transparent;
      border: 1px solid var(--border); border-radius: var(--radius);
      color: var(--soft); font-family: 'Outfit', sans-serif;
      font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.15s;
    }
    .btn-secondary:hover { border-color: var(--gold); color: var(--gold); }
    .search-input {
      flex: 1; padding: 9px 13px; min-width: 160px;
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--radius); color: var(--text);
      font-family: 'Outfit', sans-serif; font-size: 13px; outline: none;
      transition: border-color 0.15s;
    }
    .search-input::placeholder { color: var(--muted); }
    .search-input:focus { border-color: var(--gold); }

    /* Generic table */
    .g-table { width: 100%; }
    .g-head {
      display: grid; padding: 10px 16px;
      background: var(--card2); border-bottom: 1px solid var(--border);
      font-size: 11px; font-weight: 600; letter-spacing: 0.1em;
      text-transform: uppercase; color: var(--muted); gap: 10px;
    }
    .g-row {
      display: grid; padding: 12px 16px; gap: 10px;
      border-bottom: 1px solid var(--border);
      font-size: 13px; align-items: center; transition: background 0.15s;
    }
    .g-row:last-child { border-bottom: none; }
    .g-row:hover { background: var(--card2); }

    /* Action buttons in rows */
    .acts { display: flex; gap: 6px; flex-wrap: wrap; }
    .act {
      padding: 4px 10px; border-radius: 6px; font-size: 11px;
      font-weight: 600; cursor: pointer; border: 1px solid;
      transition: all 0.15s; font-family: 'Outfit', sans-serif;
    }
    .act.edit   { border-color: rgba(245,166,35,0.3); color: var(--gold);    background: rgba(245,166,35,0.08); }
    .act.edit:hover   { background: var(--gold);    color: #080a0d; }
    .act.del    { border-color: rgba(224,85,85,0.3);  color: var(--danger);  background: rgba(224,85,85,0.08); }
    .act.del:hover    { background: var(--danger);  color: #fff; }
    .act.view   { border-color: var(--border); color: var(--soft); background: var(--card2); }
    .act.view:hover   { border-color: var(--info); color: var(--info); }
    .act.toggle { border-color: rgba(62,184,122,0.3); color: var(--success); background: rgba(62,184,122,0.08); }
    .act.toggle:hover { background: var(--success); color: #080a0d; }

    /* Status badges */
    .badge {
      display: inline-block; padding: 2px 10px; border-radius: 20px;
      font-size: 11px; font-weight: 600;
    }
    .badge.active   { background: rgba(62,184,122,0.12); color: var(--success); }
    .badge.inactive { background: rgba(224,85,85,0.12);  color: var(--danger); }
    .badge.single   { background: rgba(74,158,255,0.12); color: var(--info); }
    .badge.multi    { background: rgba(155,114,255,0.12); color: var(--purple); }
    .badge.low      { background: rgba(224,85,85,0.12);  color: var(--danger); }
    .badge.ok       { background: rgba(62,184,122,0.12); color: var(--success); }
    .badge.medium   { background: rgba(245,166,35,0.12); color: var(--gold); }

    /* ════════════════════════
       TAB 1 — RESTAURANTS
    ════════════════════════ */
    .rest-cols { grid-template-columns: 40px 1fr 100px 80px 80px 120px 140px; }

    /* ════════════════════════
       TAB 2 — MENU BUILDER
    ════════════════════════ */
    .menu-layout {
      display: grid; grid-template-columns: 220px 1fr; gap: 16px;
    }
    .cat-panel { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; }
    .cat-panel-head {
      padding: 12px 14px; background: var(--card2);
      border-bottom: 1px solid var(--border);
      font-size: 12px; font-weight: 700; color: var(--soft);
      display: flex; align-items: center; justify-content: space-between;
    }
    .cat-add { font-size: 18px; cursor: pointer; color: var(--gold); line-height: 1; }
    .cat-item {
      padding: 11px 14px; display: flex; align-items: center; gap: 9px;
      cursor: pointer; transition: background 0.15s;
      border-bottom: 1px solid var(--border); font-size: 13px;
    }
    .cat-item:last-child { border-bottom: none; }
    .cat-item:hover { background: var(--card2); }
    .cat-item.on { background: rgba(245,166,35,0.08); color: var(--gold); }
    .cat-emoji { font-size: 18px; }
    .cat-name  { flex: 1; font-weight: 500; }
    .cat-count { font-size: 11px; color: var(--muted); }

    /* Items panel */
    .items-panel { display: flex; flex-direction: column; gap: 12px; }
    .items-toolbar { display: flex; gap: 10px; flex-wrap: wrap; }
    .menu-item-cols { grid-template-columns: 40px 1fr 120px 90px 80px 80px 120px; }
    .item-img {
      width: 34px; height: 34px; border-radius: 8px;
      background: var(--card2); display: grid; place-items: center; font-size: 18px;
    }
    .item-name-cell { display: flex; flex-direction: column; gap: 2px; }
    .item-name  { font-weight: 600; font-size: 13px; }
    .item-desc  { font-size: 11px; color: var(--muted); }
    .item-price { font-family: 'Bebas Neue', sans-serif; font-size: 18px; color: var(--gold); letter-spacing: 0.5px; }

    /* CSV import zone */
    .import-zone {
      border: 2px dashed var(--border); border-radius: var(--radius);
      padding: 28px; text-align: center;
      transition: all 0.15s; cursor: pointer;
      background: var(--card);
    }
    .import-zone:hover { border-color: rgba(245,166,35,0.4); background: rgba(245,166,35,0.03); }
    .import-zone.dragover { border-color: var(--gold); background: rgba(245,166,35,0.06); }
    .import-ico { font-size: 32px; margin-bottom: 8px; opacity: 0.5; }
    .import-text { font-size: 13px; color: var(--muted); line-height: 1.6; }
    .import-text strong { color: var(--soft); }

    /* ════════════════════════
       TAB 3 — STOCK
    ════════════════════════ */
    .stock-cols { grid-template-columns: 1fr 100px 80px 100px 80px 120px 120px; }
    .stock-bar-wrap { flex: 1; }
    .stock-bar-bg { height: 5px; background: var(--border); border-radius: 3px; overflow: hidden; }
    .stock-bar-fill { height: 100%; border-radius: 3px; transition: width 0.3s; }

    /* ════════════════════════
       TAB 4 — REPORTS
    ════════════════════════ */
    .report-cards {
      display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
      gap: 12px; margin-bottom: 24px;
    }
    .rep-card {
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--radius); padding: 16px;
      position: relative; overflow: hidden;
    }
    .rep-card::before {
      content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px;
    }
    .rep-card.gold::before   { background: var(--gold); }
    .rep-card.green::before  { background: var(--success); }
    .rep-card.blue::before   { background: var(--info); }
    .rep-card.purple::before { background: var(--purple); }
    .rep-card.teal::before   { background: var(--teal); }
    .rep-card.orange::before { background: var(--orange); }
    .rep-lbl { font-size: 11px; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; color: var(--muted); margin-bottom: 8px; }
    .rep-val { font-family: 'Bebas Neue', sans-serif; font-size: 30px; letter-spacing: 1px; line-height: 1; }
    .rep-card.gold   .rep-val { color: var(--gold); }
    .rep-card.green  .rep-val { color: var(--success); }
    .rep-card.blue   .rep-val { color: var(--info); }
    .rep-card.purple .rep-val { color: var(--purple); }
    .rep-card.teal   .rep-val { color: var(--teal); }
    .rep-card.orange .rep-val { color: var(--orange); }
    .rep-sub { font-size: 11px; color: var(--muted); margin-top: 4px; }

    /* Date filter */
    .date-filter { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 18px; }
    .date-btn {
      padding: 6px 14px; background: var(--card);
      border: 1px solid var(--border); border-radius: 20px;
      color: var(--muted); font-family: 'Outfit', sans-serif;
      font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.15s;
    }
    .date-btn:hover { border-color: var(--gold); color: var(--gold); }
    .date-btn.on { background: rgba(245,166,35,0.1); border-color: var(--gold); color: var(--gold); }

    /* ════════════════════════
       TAB 5 — AUDIT TRAIL
    ════════════════════════ */
    .audit-cols { grid-template-columns: 160px 80px 100px 80px 1fr; }
    .audit-type {
      display: inline-flex; align-items: center; gap: 5px;
      padding: 3px 10px; border-radius: 20px;
      font-size: 11px; font-weight: 600;
    }
    .audit-login  { background: rgba(74,158,255,0.12); color: var(--info); }
    .audit-logout { background: rgba(74,85,104,0.2);   color: var(--soft); }
    .audit-void   { background: rgba(224,85,85,0.12);  color: var(--danger); }
    .audit-order  { background: rgba(62,184,122,0.12); color: var(--success); }
    .audit-payment{ background: rgba(245,166,35,0.12); color: var(--gold); }
    .audit-change { background: rgba(155,114,255,0.12); color: var(--purple); }
    .audit-pin    { background: rgba(45,212,191,0.12); color: var(--teal); }
    .audit-menu   { background: rgba(232,82,10,0.12);  color: var(--orange); }

    /* ════════════════
       MODALS — shared
    ════════════════ */
    .overlay {
      position: fixed; inset: 0; z-index: 100;
      background: rgba(8,10,13,0.9); backdrop-filter: blur(8px);
      display: none; align-items: center; justify-content: center; padding: 20px;
    }
    .overlay.show { display: flex; }
    .modal {
      background: var(--dark); border: 1px solid var(--border);
      border-radius: 16px; width: 100%; max-width: 520px;
      max-height: 90vh; overflow-y: auto;
      animation: popIn 0.22s ease;
    }
    .modal.wide { max-width: 680px; }
    @keyframes popIn {
      from { opacity: 0; transform: scale(0.95) translateY(8px); }
      to   { opacity: 1; transform: scale(1)    translateY(0); }
    }
    .m-head {
      padding: 18px 22px 14px; border-bottom: 1px solid var(--border);
      display: flex; align-items: center; justify-content: space-between;
    }
    .m-title { font-family: 'Bebas Neue', sans-serif; font-size: 20px; letter-spacing: 1px; }
    .m-close {
      width: 30px; height: 30px; background: var(--card);
      border: 1px solid var(--border); border-radius: 7px;
      color: var(--soft); font-size: 14px; cursor: pointer;
      display: grid; place-items: center; transition: all 0.15s;
    }
    .m-close:hover { border-color: var(--danger); color: var(--danger); }
    .m-body { padding: 20px 22px; }
    .m-foot { padding: 14px 22px 18px; border-top: 1px solid var(--border); display: flex; gap: 10px; }

    /* Form */
    .f-group { margin-bottom: 14px; }
    .f-row   { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .f-row3  { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; }
    .f-label {
      display: block; font-size: 11px; font-weight: 600;
      letter-spacing: 0.12em; text-transform: uppercase;
      color: var(--muted); margin-bottom: 6px;
    }
    .f-input, .f-select, .f-textarea {
      width: 100%; padding: 10px 12px;
      background: var(--card); border: 1.5px solid var(--border);
      border-radius: var(--radius); color: var(--text);
      font-family: 'Outfit', sans-serif; font-size: 14px; outline: none;
      transition: border-color 0.15s;
    }
    .f-input::placeholder { color: var(--muted); }
    .f-input:focus, .f-select:focus, .f-textarea:focus { border-color: var(--gold); }
    .f-select { appearance: none; cursor: pointer; }
    .f-textarea { resize: vertical; min-height: 70px; }
    .f-hint { font-size: 11px; color: var(--muted); margin-top: 4px; line-height: 1.5; }

    .btn-m-primary {
      flex: 2; padding: 12px;
      background: linear-gradient(135deg, var(--gold), var(--orange));
      border: none; border-radius: var(--radius);
      color: #080a0d; font-family: 'Outfit', sans-serif;
      font-size: 14px; font-weight: 700; cursor: pointer; transition: all 0.15s;
    }
    .btn-m-primary:hover { box-shadow: 0 4px 16px rgba(245,166,35,0.25); }
    .btn-m-secondary {
      flex: 1; padding: 12px; background: transparent;
      border: 1px solid var(--border); border-radius: var(--radius);
      color: var(--soft); font-family: 'Outfit', sans-serif;
      font-size: 13px; cursor: pointer; transition: all 0.15s;
    }
    .btn-m-secondary:hover { border-color: var(--gold); color: var(--gold); }
    .info-box {
      padding: 10px 14px; background: rgba(74,158,255,0.07);
      border: 1px solid rgba(74,158,255,0.2); border-radius: 8px;
      font-size: 12px; color: var(--info); line-height: 1.6; margin-bottom: 14px;
    }

    /* Toast */
    .toast {
      position: fixed; bottom: 24px; left: 50%;
      transform: translateX(-50%) translateY(20px);
      padding: 11px 22px; background: var(--card2);
      border: 1px solid var(--border); border-radius: 30px;
      font-size: 13px; font-weight: 500; color: var(--text);
      z-index: 300; opacity: 0; transition: all 0.28s;
      white-space: nowrap; box-shadow: 0 8px 30px rgba(0,0,0,0.4);
    }
    .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
    .toast.ok  { border-color: rgba(62,184,122,0.4); color: var(--success); }
    .toast.err { border-color: rgba(224,85,85,0.4);  color: var(--danger); }

    ::-webkit-scrollbar { width: 4px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

    @media (max-width: 900px) {
      .menu-layout { grid-template-columns: 1fr; }
      .cat-panel { display: none; }
    }
    @media (max-width: 600px) {
      html, body { overflow: auto; }
      .app { grid-template-rows: 58px 44px auto; height: auto; }
      .main { min-height: calc(100vh - 102px); }
      .f-row, .f-row3 { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

<div class="toast" id="toast"></div>

<!-- ══ MODAL: Add/Edit Restaurant ══ -->
<div class="overlay" id="modal-rest">
  <div class="modal">
    <div class="m-head">
      <span class="m-title" id="rest-modal-title">Add Restaurant</span>
      <button class="m-close" onclick="closeModal('modal-rest')">✕</button>
    </div>
    <div class="m-body">
      <div class="info-box">Each restaurant gets its own database on Afrihost. Fill in the details below — the system will set up a clean environment for this client.</div>
      <div class="f-row">
        <div class="f-group">
          <label class="f-label">Restaurant Name</label>
          <input class="f-input" id="r-name" placeholder="e.g. The Braai House"/>
        </div>
        <div class="f-group">
          <label class="f-label">Type</label>
          <select class="f-select" id="r-type">
            <option value="single">Single Branch</option>
            <option value="multi">Multi-Branch</option>
          </select>
        </div>
      </div>
      <div class="f-row">
        <div class="f-group">
          <label class="f-label">Owner / Contact Name</label>
          <input class="f-input" id="r-owner" placeholder="e.g. John Smith"/>
        </div>
        <div class="f-group">
          <label class="f-label">Contact Email</label>
          <input class="f-input" type="email" id="r-email" placeholder="owner@restaurant.co.za"/>
        </div>
      </div>
      <div class="f-row">
        <div class="f-group">
          <label class="f-label">Phone</label>
          <input class="f-input" id="r-phone" placeholder="011 000 0000"/>
        </div>
        <div class="f-group">
          <label class="f-label">City / Location</label>
          <input class="f-input" id="r-city" placeholder="e.g. Johannesburg"/>
        </div>
      </div>
      <div class="f-group">
        <label class="f-label">Address</label>
        <input class="f-input" id="r-address" placeholder="1 Main Street, Sandton"/>
      </div>
      <div class="f-row">
        <div class="f-group">
          <label class="f-label">Database Name (Afrihost)</label>
          <input class="f-input" id="r-db" placeholder="e.g. mysite_braaihouse"/>
          <p class="f-hint">Must match exactly what you created in cPanel MySQL Databases.</p>
        </div>
        <div class="f-group">
          <label class="f-label">Number of Tables</label>
          <input class="f-input" type="number" id="r-tables" value="10" min="1" max="200"/>
        </div>
      </div>
    </div>
    <div class="m-foot">
      <button class="btn-m-secondary" onclick="closeModal('modal-rest')">Cancel</button>
      <button class="btn-m-primary" onclick="saveRestaurant()">Save Restaurant</button>
    </div>
  </div>
</div>

<!-- ══ MODAL: Add/Edit Menu Item ══ -->
<div class="overlay" id="modal-item">
  <div class="modal wide">
    <div class="m-head">
      <span class="m-title" id="item-modal-title">Add Menu Item</span>
      <button class="m-close" onclick="closeModal('modal-item')">✕</button>
    </div>
    <div class="m-body">
      <div class="f-row">
        <div class="f-group">
          <label class="f-label">Item Name</label>
          <input class="f-input" id="i-name" placeholder="e.g. Beef Burger"/>
        </div>
        <div class="f-group">
          <label class="f-label">Category</label>
          <select class="f-select" id="i-cat"></select>
        </div>
      </div>
      <div class="f-group">
        <label class="f-label">Description</label>
        <textarea class="f-textarea" id="i-desc" placeholder="Brief description shown on the POS screen..."></textarea>
      </div>
      <div class="f-row3">
        <div class="f-group">
          <label class="f-label">Price (R)</label>
          <input class="f-input" type="number" id="i-price" placeholder="0.00" step="0.01" min="0"/>
        </div>
        <div class="f-group">
          <label class="f-label">Emoji / Icon</label>
          <input class="f-input" id="i-emoji" placeholder="🍔" maxlength="2"/>
        </div>
        <div class="f-group">
          <label class="f-label">Available</label>
          <select class="f-select" id="i-avail">
            <option value="1">Yes — show on POS</option>
            <option value="0">No — hide from POS</option>
          </select>
        </div>
      </div>
      <div class="f-group">
        <label class="f-label">Image URL (optional)</label>
        <input class="f-input" id="i-img" placeholder="https://yoursite.co.za/images/burger.jpg"/>
        <p class="f-hint">Upload images to your Afrihost File Manager and paste the URL here.</p>
      </div>
    </div>
    <div class="m-foot">
      <button class="btn-m-secondary" onclick="closeModal('modal-item')">Cancel</button>
      <button class="btn-m-primary" onclick="saveItem()">Save Item</button>
    </div>
  </div>
</div>

<!-- ══ MODAL: Add Category ══ -->
<div class="overlay" id="modal-cat">
  <div class="modal">
    <div class="m-head">
      <span class="m-title">Add Category</span>
      <button class="m-close" onclick="closeModal('modal-cat')">✕</button>
    </div>
    <div class="m-body">
      <div class="f-row">
        <div class="f-group">
          <label class="f-label">Category Name</label>
          <input class="f-input" id="c-name" placeholder="e.g. Starters"/>
        </div>
        <div class="f-group">
          <label class="f-label">Emoji</label>
          <input class="f-input" id="c-emoji" placeholder="🥗" maxlength="2"/>
        </div>
      </div>
    </div>
    <div class="m-foot">
      <button class="btn-m-secondary" onclick="closeModal('modal-cat')">Cancel</button>
      <button class="btn-m-primary" onclick="saveCat()">Add Category</button>
    </div>
  </div>
</div>

<!-- ══ MODAL: CSV Import ══ -->
<div class="overlay" id="modal-csv">
  <div class="modal wide">
    <div class="m-head">
      <span class="m-title">Import Menu from CSV</span>
      <button class="m-close" onclick="closeModal('modal-csv')">✕</button>
    </div>
    <div class="m-body">
      <div class="info-box">
        Your CSV must have these columns in order:<br/>
        <strong>category, name, description, price, emoji, available (1 or 0)</strong><br/>
        Example row: <code style="color:var(--gold)">Mains,Beef Burger,200g patty with chips,145.00,🍔,1</code>
      </div>
      <div class="import-zone" id="import-zone"
           onclick="document.getElementById('csv-file').click()"
           ondragover="dragOver(event)" ondrop="dropCSV(event)" ondragleave="dragLeave()">
        <div class="import-ico">📂</div>
        <div class="import-text">
          <strong>Click to select CSV file</strong> or drag and drop here<br/>
          Accepted: .csv files only
        </div>
      </div>
      <input type="file" id="csv-file" accept=".csv" style="display:none" onchange="readCSV(this)"/>
      <div id="csv-preview" style="margin-top:14px"></div>
    </div>
    <div class="m-foot">
      <button class="btn-m-secondary" onclick="closeModal('modal-csv')">Cancel</button>
      <button class="btn-m-primary" id="btn-import-confirm" onclick="confirmImport()" disabled>Import Items</button>
    </div>
  </div>
</div>

<!-- ══ MODAL: Add Stock Item ══ -->
<div class="overlay" id="modal-stock">
  <div class="modal">
    <div class="m-head">
      <span class="m-title" id="stock-modal-title">Add Stock Item</span>
      <button class="m-close" onclick="closeModal('modal-stock')">✕</button>
    </div>
    <div class="m-body">
      <div class="f-row">
        <div class="f-group">
          <label class="f-label">Item Name</label>
          <input class="f-input" id="s-name" placeholder="e.g. Beef Patties"/>
        </div>
        <div class="f-group">
          <label class="f-label">Unit</label>
          <select class="f-select" id="s-unit">
            <option>kg</option><option>g</option><option>L</option>
            <option>ml</option><option>units</option><option>boxes</option><option>bottles</option>
          </select>
        </div>
      </div>
      <div class="f-row3">
        <div class="f-group">
          <label class="f-label">Current Stock</label>
          <input class="f-input" type="number" id="s-qty" placeholder="0" min="0"/>
        </div>
        <div class="f-group">
          <label class="f-label">Low Stock Alert</label>
          <input class="f-input" type="number" id="s-low" placeholder="10" min="0"/>
        </div>
        <div class="f-group">
          <label class="f-label">Cost per Unit (R)</label>
          <input class="f-input" type="number" id="s-cost" placeholder="0.00" step="0.01"/>
        </div>
      </div>
      <div class="f-group">
        <label class="f-label">Supplier (optional)</label>
        <input class="f-input" id="s-supplier" placeholder="e.g. Fresh Foods SA"/>
      </div>
    </div>
    <div class="m-foot">
      <button class="btn-m-secondary" onclick="closeModal('modal-stock')">Cancel</button>
      <button class="btn-m-primary" onclick="saveStock()">Save Stock Item</button>
    </div>
  </div>
</div>

<!-- ══ APP ══ -->
<div class="app">
  <header class="header">
    <div class="logo">
      <div class="logo-box">🍽️</div>
      <div class="logo-name">Byte<span>Savor</span></div>
    </div>
    <div class="hdiv"></div>
    <div class="hscreen">Admin</div>
    <div class="hright">
      <div class="staff-pill"><div class="sdot"></div><span id="admin-name">Admin</span></div>
      <button class="btn-out" onclick="window.location.href='../logout.php'">Sign Out</button>
    </div>
  </header>

  <div class="tabs">
    <div class="tab on"  onclick="showTab('restaurants')" id="tab-restaurants">🏢 Restaurants</div>
    <div class="tab"     onclick="showTab('menu')"         id="tab-menu">🍽️ Menu Builder</div>
    <div class="tab"     onclick="showTab('stock')"        id="tab-stock">📦 Stock</div>
    <div class="tab"     onclick="showTab('reports')"      id="tab-reports">📊 Reports</div>
    <div class="tab"     onclick="showTab('audit')"        id="tab-audit">🔍 Audit Trail</div>
  </div>

  <!-- ══ TAB 1: RESTAURANTS ══ -->
  <div class="main on" id="main-restaurants">
    <div class="toolbar">
      <button class="btn-primary" onclick="openModal('modal-rest')">+ Add Restaurant</button>
      <input class="search-input" placeholder="Search restaurants..." oninput="filterRest(this.value)"/>
    </div>
    <div class="card">
      <div class="g-head rest-cols">
        <div>#</div><div>Restaurant</div><div>Type</div>
        <div>Tables</div><div>Staff</div><div>Status</div><div>Actions</div>
      </div>
      <div id="rest-body"></div>
    </div>
  </div>

  <!-- ══ TAB 2: MENU BUILDER ══ -->
  <div class="main" id="main-menu">
    <div class="menu-layout">
      <!-- Categories panel -->
      <div class="cat-panel">
        <div class="cat-panel-head">
          Categories
          <span class="cat-add" onclick="openModal('modal-cat')" title="Add category">＋</span>
        </div>
        <div id="cat-list"></div>
      </div>
      <!-- Items panel -->
      <div class="items-panel">
        <div class="items-toolbar">
          <button class="btn-primary" onclick="openAddItem()">+ Add Item</button>
          <button class="btn-secondary" onclick="openModal('modal-csv')">📂 Import CSV</button>
          <input class="search-input" placeholder="Search items..." oninput="filterItems(this.value)"/>
        </div>
        <div class="card">
          <div class="card-head">
            <span class="card-head-title" id="items-panel-title">All Items</span>
          </div>
          <div class="g-head menu-item-cols">
            <div></div><div>Item</div><div>Category</div>
            <div>Price</div><div>Available</div><div>Sort</div><div>Actions</div>
          </div>
          <div id="items-body"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- ══ TAB 3: STOCK ══ -->
  <div class="main" id="main-stock">
    <div class="toolbar">
      <button class="btn-primary" onclick="openModal('modal-stock')">+ Add Stock Item</button>
      <button class="btn-secondary" onclick="showToast('Export coming soon','')">📤 Export CSV</button>
      <input class="search-input" placeholder="Search stock..." oninput="filterStock(this.value)"/>
    </div>
    <div class="card">
      <div class="g-head stock-cols">
        <div>Item</div><div>Unit</div><div>In Stock</div>
        <div>Low Alert</div><div>Status</div><div>Supplier</div><div>Actions</div>
      </div>
      <div id="stock-body"></div>
    </div>
  </div>

  <!-- ══ TAB 4: REPORTS ══ -->
  <div class="main" id="main-reports">
    <div class="date-filter" id="date-filter">
      <button class="date-btn on" onclick="setDate(this,'today')">Today</button>
      <button class="date-btn" onclick="setDate(this,'week')">This Week</button>
      <button class="date-btn" onclick="setDate(this,'month')">This Month</button>
      <button class="date-btn" onclick="setDate(this,'custom')">Custom Range</button>
    </div>
    <div class="report-cards">
      <div class="rep-card gold">  <div class="rep-lbl">Total Revenue</div><div class="rep-val" id="rep-rev">R12,840</div><div class="rep-sub">incl. VAT</div></div>
      <div class="rep-card green"> <div class="rep-lbl">Transactions</div><div class="rep-val" id="rep-tx">28</div><div class="rep-sub">bills closed</div></div>
      <div class="rep-card blue">  <div class="rep-lbl">Dine In</div><div class="rep-val" id="rep-dine">18</div><div class="rep-sub">orders</div></div>
      <div class="rep-card purple"><div class="rep-lbl">Takeaway</div><div class="rep-val" id="rep-tw">7</div><div class="rep-sub">orders</div></div>
      <div class="rep-card teal">  <div class="rep-lbl">Online</div><div class="rep-val" id="rep-online">3</div><div class="rep-sub">orders</div></div>
      <div class="rep-card orange"><div class="rep-lbl">Avg Bill</div><div class="rep-val" id="rep-avg">R458</div><div class="rep-sub">per transaction</div></div>
    </div>
    <p class="sec">Top Selling Items Today</p>
    <div class="card" style="margin-bottom:20px">
      <div class="g-head" style="grid-template-columns:1fr 80px 100px 100px">
        <div>Item</div><div>Qty Sold</div><div>Revenue</div><div>% of Sales</div>
      </div>
      <div id="top-items-body"></div>
    </div>
    <p class="sec">Payment Method Breakdown</p>
    <div class="card">
      <div class="g-head" style="grid-template-columns:1fr 80px 100px 120px">
        <div>Method</div><div>Count</div><div>Total</div><div>Share</div>
      </div>
      <div id="payment-body"></div>
    </div>
  </div>

  <!-- ══ TAB 5: AUDIT TRAIL ══ -->
  <div class="main" id="main-audit">
    <div class="toolbar">
      <select class="f-select" style="width:160px" id="audit-filter" onchange="renderAudit()">
        <option value="all">All Events</option>
        <option value="login">Logins</option>
        <option value="logout">Logouts</option>
        <option value="void">Voids</option>
        <option value="payment">Payments</option>
        <option value="order">Orders</option>
        <option value="change">System Changes</option>
        <option value="pin">PIN Changes</option>
        <option value="menu">Menu Changes</option>
      </select>
      <input class="search-input" placeholder="Search by staff or description..." oninput="filterAudit(this.value)"/>
      <button class="btn-secondary" onclick="showToast('Export coming soon','')">📤 Export</button>
    </div>
    <div class="card">
      <div class="g-head audit-cols">
        <div>Timestamp</div><div>Type</div><div>Staff ID</div><div>Staff</div><div>Detail</div>
      </div>
      <div id="audit-body"></div>
    </div>
  </div>
</div>

<script>
// ══════════════════════════════════════
//  ByteSavor — Admin Dashboard
//  Demo data. In production all data
//  fetched from api/*.php endpoints.
// ══════════════════════════════════════

document.getElementById('admin-name').textContent = 'System Admin';

// ── DEMO DATA ──

let RESTAURANTS = [
  { id:1, name:'The Braai House',    type:'single', city:'Johannesburg', tables:12, staff:8,  db:'mysite_braaihouse', active:true  },
  { id:2, name:'Ocean Basket Sandton',type:'multi', city:'Sandton',      tables:20, staff:15, db:'mysite_oceanbasket',active:true  },
  { id:3, name:'Mama\'s Kitchen',    type:'single', city:'Soweto',       tables:8,  staff:5,  db:'mysite_mamaskitchen',active:true },
  { id:4, name:'Rooftop Bistro',     type:'single', city:'Cape Town',    tables:16, staff:10, db:'mysite_rooftop',    active:false },
];

let CATEGORIES = [
  { id:1, name:'Starters', emoji:'🥗' },
  { id:2, name:'Mains',    emoji:'🍽️' },
  { id:3, name:'Pizzas',   emoji:'🍕' },
  { id:4, name:'Sides',    emoji:'🍟' },
  { id:5, name:'Drinks',   emoji:'🥤' },
  { id:6, name:'Desserts', emoji:'🍰' },
];

let ITEMS = [
  { id:101, catId:1, name:'Chicken Wings',    desc:'Peri-peri or BBQ',      price:89,  emoji:'🍗', avail:true  },
  { id:102, catId:1, name:'Calamari',         desc:'Fried with tartare',    price:95,  emoji:'🦑', avail:true  },
  { id:201, catId:2, name:'Beef Burger',      desc:'200g patty, chips',     price:145, emoji:'🍔', avail:true  },
  { id:202, catId:2, name:'Grilled Chicken',  desc:'Roast veg & rice',      price:165, emoji:'🍗', avail:true  },
  { id:203, catId:2, name:'Beef Steak 300g',  desc:'Sirloin, sauce, sides', price:265, emoji:'🥩', avail:true  },
  { id:301, catId:3, name:'Margherita',       desc:'Tomato & mozzarella',   price:120, emoji:'🍕', avail:true  },
  { id:302, catId:3, name:'BBQ Chicken',      desc:'BBQ base, peppers',     price:145, emoji:'🍕', avail:true  },
  { id:401, catId:4, name:'Chips',            desc:'Crispy golden chips',   price:45,  emoji:'🍟', avail:true  },
  { id:501, catId:5, name:'Soft Drink',       desc:'330ml can',             price:35,  emoji:'🥤', avail:true  },
  { id:502, catId:5, name:'Coffee',           desc:'Espresso/flat white',   price:48,  emoji:'☕', avail:true  },
  { id:601, catId:6, name:'Chocolate Cake',   desc:'Lava cake, ice cream',  price:85,  emoji:'🍫', avail:true  },
];

let STOCK = [
  { id:1, name:'Beef Patties',  unit:'kg',    qty:42,  low:10, cost:85,  supplier:'Fresh Foods SA' },
  { id:2, name:'Chicken Fillets',unit:'kg',   qty:28,  low:8,  cost:65,  supplier:'Poultry Direct' },
  { id:3, name:'Pizza Bases',   unit:'units', qty:60,  low:20, cost:12,  supplier:'Bakery Plus'    },
  { id:4, name:'Coke 330ml',    unit:'units', qty:8,   low:24, cost:8,   supplier:'Coca-Cola SA'   },
  { id:5, name:'Cooking Oil',   unit:'L',     qty:15,  low:5,  cost:32,  supplier:'Food Corp'      },
  { id:6, name:'Mozzarella',    unit:'kg',    qty:6,   low:4,  cost:120, supplier:'Dairy Farm'     },
];

const AUDIT_LOG = [
  { ts:'2025-05-21 08:14:02', type:'login',   staffId:'M001', staff:'Thabo',  detail:'Manager login from 192.168.1.10' },
  { ts:'2025-05-21 08:16:44', type:'login',   staffId:'W001', staff:'Amahle', detail:'Waiter login' },
  { ts:'2025-05-21 08:17:01', type:'login',   staffId:'W002', staff:'Sipho',  detail:'Waiter login' },
  { ts:'2025-05-21 09:02:15', type:'order',   staffId:'W001', staff:'Amahle', detail:'Order #1001 placed — Table T2 — R450.00' },
  { ts:'2025-05-21 09:14:33', type:'order',   staffId:'W002', staff:'Sipho',  detail:'Order #1002 placed — Table T4 — R522.75' },
  { ts:'2025-05-21 09:45:10', type:'payment', staffId:'W001', staff:'Amahle', detail:'Payment R450.00 — Cash — Table T2' },
  { ts:'2025-05-21 10:02:00', type:'void',    staffId:'M001', staff:'Thabo',  detail:'Order #1003 voided — Reason: Customer cancelled' },
  { ts:'2025-05-21 10:15:44', type:'pin',     staffId:'M001', staff:'Thabo',  detail:'PIN reset for W003 Lesego Khumalo' },
  { ts:'2025-05-21 10:30:00', type:'menu',    staffId:'A001', staff:'Admin',  detail:'Item added: Veggie Wrap — R95.00 — Mains' },
  { ts:'2025-05-21 11:00:22', type:'change',  staffId:'M001', staff:'Thabo',  detail:'Table T5 reserved — Smith Birthday — 19:00' },
  { ts:'2025-05-21 11:45:00', type:'payment', staffId:'W002', staff:'Sipho',  detail:'Payment R522.75 — Card — Table T4 — Ref: 4521' },
  { ts:'2025-05-21 12:10:00', type:'login',   staffId:'C001', staff:'Zanele', detail:'Cashier login' },
  { ts:'2025-05-21 12:30:00', type:'order',   staffId:'W001', staff:'Amahle', detail:'Order #1004 placed — Takeaway — R299.25' },
  { ts:'2025-05-21 13:00:00', type:'menu',    staffId:'A001', staff:'Admin',  detail:'Price updated: Beef Steak 300g R245 → R265' },
  { ts:'2025-05-21 13:45:00', type:'logout',  staffId:'W002', staff:'Sipho',  detail:'Waiter logout' },
];

const TOP_ITEMS = [
  { name:'Beef Burger',    qty:14, rev:2030, pct:15.8 },
  { name:'Chips',          qty:22, rev:990,  pct:7.7  },
  { name:'Margherita',     qty:9,  rev:1080, pct:8.4  },
  { name:'Grilled Chicken',qty:8,  rev:1320, pct:10.3 },
  { name:'Soft Drink',     qty:30, rev:1050, pct:8.2  },
];

const PAYMENTS = [
  { method:'💳 Card',         count:12, total:6240,  pct:48.6 },
  { method:'💵 Cash',         count:8,  total:3200,  pct:24.9 },
  { method:'📲 Tap / NFC',    count:4,  total:1800,  pct:14.0 },
  { method:'📷 SnapScan',     count:2,  total:900,   pct:7.0  },
  { method:'🚗 Uber Eats',    count:1,  total:400,   pct:3.1  },
  { method:'⚡ Zapper',       count:1,  total:300,   pct:2.3  },
];

// ── Tab switching ──
function showTab(tab) {
  ['restaurants','menu','stock','reports','audit'].forEach(t => {
    document.getElementById('tab-' + t).classList.toggle('on', t === tab);
    document.getElementById('main-' + t).classList.toggle('on', t === tab);
  });
  if (tab === 'restaurants') renderRestaurants();
  if (tab === 'menu')        renderMenu();
  if (tab === 'stock')       renderStock();
  if (tab === 'reports')     renderReports();
  if (tab === 'audit')       renderAudit();
}

// ════════════════
//  RESTAURANTS
// ════════════════
function renderRestaurants(filter) {
  let list = filter ? RESTAURANTS.filter(r => r.name.toLowerCase().includes(filter.toLowerCase())) : RESTAURANTS;
  document.getElementById('rest-body').innerHTML = list.map(r => `
    <div class="g-row rest-cols">
      <div style="color:var(--muted);font-size:12px">${r.id}</div>
      <div>
        <div style="font-weight:600">${r.name}</div>
        <div style="font-size:11px;color:var(--muted)">${r.city} · ${r.db}</div>
      </div>
      <div><span class="badge ${r.type}">${r.type}</span></div>
      <div style="color:var(--soft)">${r.tables}</div>
      <div style="color:var(--soft)">${r.staff}</div>
      <div><span class="badge ${r.active?'active':'inactive'}">${r.active?'Active':'Inactive'}</span></div>
      <div class="acts">
        <button class="act edit" onclick="showToast('Edit restaurant coming soon','')">Edit</button>
        <button class="act view" onclick="showToast('Opening ${r.name} dashboard...','ok')">Open</button>
        <button class="act ${r.active?'del':'toggle'}"
                onclick="toggleRest(${r.id})">${r.active?'Deactivate':'Activate'}</button>
      </div>
    </div>
  `).join('');
}
function filterRest(v) { renderRestaurants(v); }
function toggleRest(id) {
  const r = RESTAURANTS.find(r => r.id === id);
  r.active = !r.active;
  renderRestaurants();
  showToast(r.name + (r.active ? ' activated' : ' deactivated') + ' ✓', 'ok');
}
function saveRestaurant() {
  const name = document.getElementById('r-name').value.trim();
  if (!name) { showToast('Please enter a restaurant name', 'err'); return; }
  RESTAURANTS.push({
    id: RESTAURANTS.length + 1,
    name, type: document.getElementById('r-type').value,
    city: document.getElementById('r-city').value || '—',
    tables: parseInt(document.getElementById('r-tables').value) || 10,
    staff: 0,
    db: document.getElementById('r-db').value || 'pending',
    active: true,
  });
  closeModal('modal-rest');
  renderRestaurants();
  showToast(name + ' added ✓', 'ok');
}
renderRestaurants();

// ════════════════
//  MENU BUILDER
// ════════════════
let activeCatId = null;

function renderMenu() {
  // Categories panel
  document.getElementById('cat-list').innerHTML = CATEGORIES.map(c => `
    <div class="cat-item ${activeCatId === c.id ? 'on' : ''}" onclick="selectCat(${c.id})">
      <span class="cat-emoji">${c.emoji}</span>
      <span class="cat-name">${c.name}</span>
      <span class="cat-count">${ITEMS.filter(i => i.catId === c.id).length}</span>
    </div>
  `).join('');
  renderItems();
}

function selectCat(id) {
  activeCatId = id;
  renderMenu();
}

function renderItems(filter) {
  const cat = activeCatId ? CATEGORIES.find(c => c.id === activeCatId) : null;
  document.getElementById('items-panel-title').textContent = cat ? cat.name + ' items' : 'All Items';
  let list = activeCatId ? ITEMS.filter(i => i.catId === activeCatId) : ITEMS;
  if (filter) list = list.filter(i => i.name.toLowerCase().includes(filter.toLowerCase()));
  document.getElementById('items-body').innerHTML = list.map(item => {
    const cat = CATEGORIES.find(c => c.id === item.catId);
    return `
      <div class="g-row menu-item-cols">
        <div class="item-img">${item.emoji}</div>
        <div class="item-name-cell">
          <div class="item-name">${item.name}</div>
          <div class="item-desc">${item.desc}</div>
        </div>
        <div style="font-size:12px;color:var(--soft)">${cat?.name || '—'}</div>
        <div class="item-price">R${item.price.toFixed(2)}</div>
        <div><span class="badge ${item.avail ? 'active' : 'inactive'}">${item.avail ? 'Yes' : 'No'}</span></div>
        <div style="font-size:12px;color:var(--muted)">—</div>
        <div class="acts">
          <button class="act edit" onclick="openEditItem(${item.id})">Edit</button>
          <button class="act ${item.avail ? 'del' : 'toggle'}" onclick="toggleItem(${item.id})">
            ${item.avail ? 'Hide' : 'Show'}
          </button>
        </div>
      </div>
    `;
  }).join('');
}

function filterItems(v) { renderItems(v); }

function openAddItem() {
  document.getElementById('item-modal-title').textContent = 'Add Menu Item';
  document.getElementById('i-name').value  = '';
  document.getElementById('i-desc').value  = '';
  document.getElementById('i-price').value = '';
  document.getElementById('i-emoji').value = '';
  document.getElementById('i-img').value   = '';
  document.getElementById('i-avail').value = '1';
  document.getElementById('i-cat').innerHTML = CATEGORIES.map(c =>
    `<option value="${c.id}">${c.emoji} ${c.name}</option>`
  ).join('');
  openModal('modal-item');
}

function openEditItem(id) {
  const item = ITEMS.find(i => i.id === id);
  document.getElementById('item-modal-title').textContent = 'Edit: ' + item.name;
  document.getElementById('i-name').value  = item.name;
  document.getElementById('i-desc').value  = item.desc;
  document.getElementById('i-price').value = item.price;
  document.getElementById('i-emoji').value = item.emoji;
  document.getElementById('i-avail').value = item.avail ? '1' : '0';
  document.getElementById('i-cat').innerHTML = CATEGORIES.map(c =>
    `<option value="${c.id}" ${c.id === item.catId ? 'selected' : ''}>${c.emoji} ${c.name}</option>`
  ).join('');
  openModal('modal-item');
}

function saveItem() {
  const name = document.getElementById('i-name').value.trim();
  if (!name)  { showToast('Please enter an item name', 'err'); return; }
  const price = parseFloat(document.getElementById('i-price').value);
  if (!price) { showToast('Please enter a valid price', 'err'); return; }
  ITEMS.push({
    id: ITEMS.length + 200, catId: parseInt(document.getElementById('i-cat').value),
    name, desc: document.getElementById('i-desc').value,
    price, emoji: document.getElementById('i-emoji').value || '🍽️',
    avail: document.getElementById('i-avail').value === '1',
  });
  closeModal('modal-item');
  renderMenu();
  showToast(name + ' added to menu ✓', 'ok');
}

function toggleItem(id) {
  const item = ITEMS.find(i => i.id === id);
  item.avail = !item.avail;
  renderItems();
  showToast(item.name + (item.avail ? ' shown on POS' : ' hidden from POS') + ' ✓', 'ok');
}

function saveCat() {
  const name  = document.getElementById('c-name').value.trim();
  const emoji = document.getElementById('c-emoji').value || '🍽️';
  if (!name) { showToast('Please enter a category name', 'err'); return; }
  CATEGORIES.push({ id: CATEGORIES.length + 10, name, emoji });
  closeModal('modal-cat');
  renderMenu();
  showToast(name + ' category added ✓', 'ok');
}

// ── CSV Import ──
let csvRows = [];
function dragOver(e) { e.preventDefault(); document.getElementById('import-zone').classList.add('dragover'); }
function dragLeave()  { document.getElementById('import-zone').classList.remove('dragover'); }
function dropCSV(e)   { e.preventDefault(); dragLeave(); readCSV({ files: e.dataTransfer.files }); }

function readCSV(input) {
  const file = input.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = e => {
    const lines = e.target.result.split('\n').filter(l => l.trim());
    csvRows = lines.map(l => l.split(',').map(c => c.trim().replace(/^"|"$/g,'')));
    const preview = csvRows.slice(0, 5);
    document.getElementById('csv-preview').innerHTML = `
      <p style="font-size:12px;color:var(--soft);margin-bottom:8px">${csvRows.length} rows found — preview:</p>
      <div class="card" style="font-size:12px">
        ${preview.map(r => `
          <div style="padding:7px 12px;border-bottom:1px solid var(--border);color:var(--soft)">
            ${r.join(' · ')}
          </div>`).join('')}
      </div>
    `;
    document.getElementById('btn-import-confirm').disabled = false;
  };
  reader.readAsText(file);
}

function confirmImport() {
  let added = 0;
  csvRows.forEach(r => {
    if (r.length < 4) return;
    const [catName, name, desc, price, emoji, avail] = r;
    let cat = CATEGORIES.find(c => c.name.toLowerCase() === catName.toLowerCase());
    if (!cat) { cat = { id: CATEGORIES.length + 10, name: catName, emoji: '🍽️' }; CATEGORIES.push(cat); }
    ITEMS.push({
      id: ITEMS.length + 300 + added,
      catId: cat.id, name, desc: desc || '',
      price: parseFloat(price) || 0,
      emoji: emoji || '🍽️',
      avail: avail !== '0',
    });
    added++;
  });
  closeModal('modal-csv');
  renderMenu();
  showToast(added + ' items imported ✓', 'ok');
}

// ════════════════
//  STOCK
// ════════════════
function renderStock(filter) {
  let list = filter ? STOCK.filter(s => s.name.toLowerCase().includes(filter.toLowerCase())) : STOCK;
  document.getElementById('stock-body').innerHTML = list.map(s => {
    const pct  = Math.min(100, Math.round((s.qty / (s.low * 3)) * 100));
    const status = s.qty <= 0 ? 'low' : s.qty <= s.low ? 'medium' : 'ok';
    const barColor = status === 'ok' ? 'var(--success)' : status === 'medium' ? 'var(--gold)' : 'var(--danger)';
    return `
      <div class="g-row stock-cols">
        <div style="font-weight:600">${s.name}</div>
        <div style="color:var(--muted);font-size:12px">${s.unit}</div>
        <div>
          <div style="font-weight:700;margin-bottom:4px">${s.qty}</div>
          <div class="stock-bar-bg"><div class="stock-bar-fill" style="width:${pct}%;background:${barColor}"></div></div>
        </div>
        <div style="color:var(--muted);font-size:12px">${s.low} ${s.unit}</div>
        <div><span class="badge ${status}">${status === 'ok' ? 'OK' : status === 'medium' ? 'Low' : 'Critical'}</span></div>
        <div style="font-size:12px;color:var(--soft)">${s.supplier}</div>
        <div class="acts">
          <button class="act edit" onclick="openEditStock(${s.id})">Update</button>
          <button class="act del"  onclick="deleteStock(${s.id})">Remove</button>
        </div>
      </div>
    `;
  }).join('');
}
function filterStock(v) { renderStock(v); }
function saveStock() {
  const name = document.getElementById('s-name').value.trim();
  if (!name) { showToast('Please enter a stock item name', 'err'); return; }
  STOCK.push({
    id: STOCK.length + 1, name,
    unit:     document.getElementById('s-unit').value,
    qty:      parseInt(document.getElementById('s-qty').value) || 0,
    low:      parseInt(document.getElementById('s-low').value) || 10,
    cost:     parseFloat(document.getElementById('s-cost').value) || 0,
    supplier: document.getElementById('s-supplier').value || '—',
  });
  closeModal('modal-stock');
  renderStock();
  showToast(name + ' added to stock ✓', 'ok');
}
function openEditStock(id) {
  const s = STOCK.find(s => s.id === id);
  document.getElementById('stock-modal-title').textContent = 'Update: ' + s.name;
  document.getElementById('s-name').value     = s.name;
  document.getElementById('s-unit').value     = s.unit;
  document.getElementById('s-qty').value      = s.qty;
  document.getElementById('s-low').value      = s.low;
  document.getElementById('s-cost').value     = s.cost;
  document.getElementById('s-supplier').value = s.supplier;
  openModal('modal-stock');
}
function deleteStock(id) {
  STOCK = STOCK.filter(s => s.id !== id);
  renderStock();
  showToast('Stock item removed ✓', 'ok');
}

// ════════════════
//  REPORTS
// ════════════════
function setDate(btn, period) {
  document.querySelectorAll('.date-btn').forEach(b => b.classList.remove('on'));
  btn.classList.add('on');
  showToast('Showing data for: ' + period, '');
}
function renderReports() {
  document.getElementById('top-items-body').innerHTML = TOP_ITEMS.map(i => `
    <div class="g-row" style="grid-template-columns:1fr 80px 100px 100px">
      <div style="font-weight:600">${i.name}</div>
      <div style="color:var(--soft)">${i.qty}</div>
      <div style="color:var(--gold);font-weight:700">R${i.rev.toLocaleString()}</div>
      <div style="color:var(--muted)">${i.pct}%</div>
    </div>
  `).join('');
  document.getElementById('payment-body').innerHTML = PAYMENTS.map(p => `
    <div class="g-row" style="grid-template-columns:1fr 80px 100px 120px">
      <div style="font-weight:600">${p.method}</div>
      <div style="color:var(--soft)">${p.count}</div>
      <div style="color:var(--gold);font-weight:700">R${p.total.toLocaleString()}</div>
      <div>
        <div style="font-size:11px;color:var(--muted);margin-bottom:3px">${p.pct}%</div>
        <div style="height:4px;background:var(--border);border-radius:2px;overflow:hidden">
          <div style="height:100%;width:${p.pct}%;background:var(--gold);border-radius:2px"></div>
        </div>
      </div>
    </div>
  `).join('');
}

// ════════════════
//  AUDIT TRAIL
// ════════════════
function renderAudit(filter) {
  const type = document.getElementById('audit-filter').value;
  let list = type === 'all' ? AUDIT_LOG : AUDIT_LOG.filter(a => a.type === type);
  if (filter) list = list.filter(a =>
    a.staff.toLowerCase().includes(filter.toLowerCase()) ||
    a.detail.toLowerCase().includes(filter.toLowerCase())
  );
  document.getElementById('audit-body').innerHTML = [...list].reverse().map(a => `
    <div class="g-row audit-cols">
      <div style="font-size:11px;color:var(--soft);font-family:monospace">${a.ts}</div>
      <div><span class="audit-type audit-${a.type}">${a.type}</span></div>
      <div style="font-family:'Bebas Neue',sans-serif;font-size:16px;color:var(--gold)">${a.staffId}</div>
      <div style="font-size:12px;color:var(--soft)">${a.staff}</div>
      <div style="font-size:12px;color:var(--text)">${a.detail}</div>
    </div>
  `).join('');
}
function filterAudit(v) { renderAudit(v); }

// ── Modal helpers ──
function openModal(id)  { document.getElementById(id).classList.add('show'); }
function closeModal(id) { document.getElementById(id).classList.remove('show'); }
document.querySelectorAll('.overlay').forEach(el => {
  el.addEventListener('click', e => { if (e.target === el) el.classList.remove('show'); });
});

// ── Toast ──
let toastTimer;
function showToast(msg, type) {
  const t = document.getElementById('toast');
  t.textContent = msg; t.className = 'toast show ' + (type||'');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => t.className = 'toast', 2800);
}
</script>
</body>
</html>
