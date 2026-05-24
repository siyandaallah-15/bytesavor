<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"/>
  <title>ByteSavor — Manager</title>
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
      background: var(--dark);
      border-bottom: 1px solid var(--border);
      z-index: 20;
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
      display: flex; align-items: center; gap: 7px;
      padding: 5px 13px; background: var(--card);
      border: 1px solid var(--border); border-radius: 20px;
      font-size: 12px; color: var(--soft);
    }
    .sdot { width: 7px; height: 7px; border-radius: 50%; background: var(--purple); box-shadow: 0 0 6px rgba(155,114,255,0.6); }
    .btn-out {
      padding: 5px 13px; background: transparent;
      border: 1px solid var(--border); border-radius: 8px;
      color: var(--soft); font-family: 'Outfit', sans-serif;
      font-size: 12px; cursor: pointer; transition: all 0.15s;
    }
    .btn-out:hover { border-color: var(--danger); color: var(--danger); }

    /* ══ TAB BAR ══ */
    .tabs {
      display: flex; background: var(--dark);
      border-bottom: 1px solid var(--border);
      padding: 0 20px; overflow-x: auto;
    }
    .tab {
      padding: 0 20px; height: 44px;
      display: flex; align-items: center; gap: 7px;
      font-size: 13px; font-weight: 600; color: var(--muted);
      cursor: pointer; border-bottom: 2px solid transparent;
      transition: all 0.15s; white-space: nowrap; user-select: none;
    }
    .tab:hover { color: var(--soft); }
    .tab.on { color: var(--gold); border-bottom-color: var(--gold); }

    /* ══ MAIN ══ */
    .main {
      overflow-y: auto; padding: 20px;
      display: none;
    }
    .main.on { display: block; }

    /* ══ SECTION TITLE ══ */
    .sec {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 12px; letter-spacing: 0.2em;
      color: var(--muted); text-transform: uppercase;
      margin-bottom: 12px;
    }

    /* ════════════════════════
       TAB 1 — OVERVIEW
    ════════════════════════ */
    .overview-cards {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 14px; margin-bottom: 28px;
    }
    .ov-card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 18px 20px;
      position: relative; overflow: hidden;
    }
    .ov-card::before {
      content: ''; position: absolute;
      top: 0; left: 0; right: 0; height: 2px;
    }
    .ov-card.gold::before  { background: var(--gold); }
    .ov-card.green::before { background: var(--success); }
    .ov-card.blue::before  { background: var(--info); }
    .ov-card.purple::before{ background: var(--purple); }
    .ov-card.orange::before{ background: var(--orange); }

    .ov-label {
      font-size: 11px; font-weight: 600;
      letter-spacing: 0.15em; text-transform: uppercase;
      color: var(--muted); margin-bottom: 10px;
    }
    .ov-val {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 36px; letter-spacing: 1px; line-height: 1;
    }
    .ov-card.gold   .ov-val { color: var(--gold); }
    .ov-card.green  .ov-val { color: var(--success); }
    .ov-card.blue   .ov-val { color: var(--info); }
    .ov-card.purple .ov-val { color: var(--purple); }
    .ov-card.orange .ov-val { color: var(--orange); }
    .ov-sub { font-size: 12px; color: var(--muted); margin-top: 4px; }

    /* Staff on shift list */
    .shift-list {
      display: flex; flex-direction: column; gap: 8px;
      margin-top: 14px;
    }
    .shift-row {
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--radius); padding: 10px 14px;
      display: flex; align-items: center; gap: 12px;
      font-size: 13px;
    }
    .shift-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
    .shift-name { font-weight: 600; flex: 1; }
    .shift-role {
      padding: 2px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;
    }
    .role-waiter  { background: rgba(74,158,255,0.12); color: var(--info); }
    .role-cashier { background: rgba(245,166,35,0.12); color: var(--gold); }
    .role-manager { background: rgba(155,114,255,0.12); color: var(--purple); }
    .shift-id { font-size: 11px; color: var(--muted); }
    .shift-since { font-size: 11px; color: var(--muted); margin-left: auto; }

    /* ════════════════════════
       TAB 2 — TABLES
    ════════════════════════ */
    .tables-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
      gap: 10px; margin-bottom: 24px;
    }
    .tbl-btn {
      aspect-ratio: 1; background: var(--card);
      border: 1.5px solid var(--border); border-radius: var(--radius);
      cursor: pointer; transition: all 0.15s;
      display: flex; flex-direction: column;
      align-items: center; justify-content: center; gap: 4px;
      position: relative;
    }
    .tbl-num { font-family: 'Bebas Neue', sans-serif; font-size: 24px; line-height: 1; color: var(--text); }
    .tbl-cap { font-size: 10px; color: var(--muted); }
    .tbl-status-dot { position: absolute; top: 7px; right: 7px; width: 8px; height: 8px; border-radius: 50%; }
    .tbl-owner { font-size: 9px; color: var(--muted); margin-top: 1px; }

    .tbl-btn.available .tbl-status-dot { background: var(--success); box-shadow: 0 0 5px rgba(62,184,122,0.5); }
    .tbl-btn.occupied  .tbl-status-dot { background: var(--danger);  box-shadow: 0 0 5px rgba(224,85,85,0.5); }
    .tbl-btn.reserved  .tbl-status-dot { background: var(--gold);    box-shadow: 0 0 5px rgba(245,166,35,0.5); }
    .tbl-btn.cleaning  .tbl-status-dot { background: var(--info);    box-shadow: 0 0 5px rgba(74,158,255,0.5); }

    .tbl-btn.reserved { background: rgba(245,166,35,0.06); border-color: rgba(245,166,35,0.3); }
    .tbl-btn.occupied { border-color: rgba(224,85,85,0.3); }
    .tbl-btn:hover { border-color: rgba(245,166,35,0.4); transform: translateY(-2px); }

    /* Table legend */
    .tbl-legend {
      display: flex; gap: 16px; flex-wrap: wrap;
      margin-bottom: 18px;
    }
    .leg-item { display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--soft); }
    .leg-dot  { width: 8px; height: 8px; border-radius: 50%; }

    /* Open bills table */
    .bills-table {
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--radius); overflow: hidden;
    }
    .bt-head {
      display: grid;
      grid-template-columns: 80px 1fr 100px 100px 80px 100px 120px;
      padding: 10px 16px; background: var(--card2);
      border-bottom: 1px solid var(--border);
      font-size: 11px; font-weight: 600;
      letter-spacing: 0.1em; text-transform: uppercase; color: var(--muted);
      gap: 8px;
    }
    .bt-row {
      display: grid;
      grid-template-columns: 80px 1fr 100px 100px 80px 100px 120px;
      padding: 12px 16px; gap: 8px;
      border-bottom: 1px solid var(--border);
      font-size: 13px; align-items: center;
      transition: background 0.15s;
    }
    .bt-row:last-child { border-bottom: none; }
    .bt-row:hover { background: var(--card2); }
    .bt-table { font-family: 'Bebas Neue', sans-serif; font-size: 18px; color: var(--text); }
    .bt-waiter { color: var(--soft); font-size: 12px; }
    .bt-items  { color: var(--soft); font-size: 12px; }
    .bt-total  { font-weight: 700; color: var(--gold); }
    .bt-time   { font-size: 11px; color: var(--muted); }
    .bt-acts   { display: flex; gap: 6px; }
    .bt-act {
      padding: 4px 10px; border-radius: 6px; font-size: 11px;
      font-weight: 600; cursor: pointer; border: 1px solid;
      transition: all 0.15s; font-family: 'Outfit', sans-serif;
    }
    .bt-act.move  { border-color: rgba(74,158,255,0.3); color: var(--info); background: rgba(74,158,255,0.08); }
    .bt-act.move:hover  { background: var(--info); color: #080a0d; }
    .bt-act.void  { border-color: rgba(224,85,85,0.3); color: var(--danger); background: rgba(224,85,85,0.08); }
    .bt-act.void:hover  { background: var(--danger); color: #fff; }
    .bt-act.print { border-color: var(--border); color: var(--soft); background: var(--card2); }
    .bt-act.print:hover { border-color: var(--gold); color: var(--gold); }

    /* ════════════════════════
       TAB 3 — STAFF
    ════════════════════════ */
    .staff-toolbar {
      display: flex; align-items: center; gap: 10px;
      margin-bottom: 16px; flex-wrap: wrap;
    }
    .btn-add-staff {
      padding: 9px 18px;
      background: linear-gradient(135deg, var(--gold), var(--orange));
      border: none; border-radius: var(--radius);
      color: #080a0d; font-family: 'Outfit', sans-serif;
      font-size: 13px; font-weight: 700; cursor: pointer;
      transition: all 0.15s;
    }
    .btn-add-staff:hover { transform: translateY(-1px); box-shadow: 0 4px 16px rgba(245,166,35,0.25); }
    .staff-search {
      flex: 1; padding: 9px 13px;
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--radius); color: var(--text);
      font-family: 'Outfit', sans-serif; font-size: 13px; outline: none;
      transition: border-color 0.15s; min-width: 160px;
    }
    .staff-search::placeholder { color: var(--muted); }
    .staff-search:focus { border-color: var(--gold); }

    .staff-table {
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--radius); overflow: hidden;
    }
    .st-head {
      display: grid;
      grid-template-columns: 80px 1fr 90px 80px 100px 80px 160px;
      padding: 10px 16px; background: var(--card2);
      border-bottom: 1px solid var(--border);
      font-size: 11px; font-weight: 600;
      letter-spacing: 0.1em; text-transform: uppercase; color: var(--muted);
      gap: 8px;
    }
    .st-row {
      display: grid;
      grid-template-columns: 80px 1fr 90px 80px 100px 80px 160px;
      padding: 12px 16px; gap: 8px;
      border-bottom: 1px solid var(--border);
      font-size: 13px; align-items: center;
      transition: background 0.15s;
    }
    .st-row:last-child { border-bottom: none; }
    .st-row:hover { background: var(--card2); }
    .st-id { font-family: 'Bebas Neue', sans-serif; font-size: 17px; color: var(--gold); }
    .st-name { font-weight: 600; }
    .st-role {
      padding: 3px 10px; border-radius: 20px;
      font-size: 11px; font-weight: 600; display: inline-block;
    }
    .st-status-badge {
      padding: 3px 10px; border-radius: 20px;
      font-size: 11px; font-weight: 600;
    }
    .st-status-badge.active   { background: rgba(62,184,122,0.12); color: var(--success); }
    .st-status-badge.inactive { background: rgba(224,85,85,0.12);  color: var(--danger); }
    .st-bio {
      font-size: 11px; color: var(--muted);
      display: flex; align-items: center; gap: 4px;
    }
    .st-bio.enrolled { color: var(--success); }
    .st-acts { display: flex; gap: 5px; flex-wrap: wrap; }
    .st-act {
      padding: 4px 9px; border-radius: 6px; font-size: 11px;
      font-weight: 600; cursor: pointer; border: 1px solid;
      transition: all 0.15s; font-family: 'Outfit', sans-serif;
      white-space: nowrap;
    }
    .st-act.pin    { border-color: rgba(245,166,35,0.3); color: var(--gold); background: rgba(245,166,35,0.08); }
    .st-act.pin:hover    { background: var(--gold); color: #080a0d; }
    .st-act.bio    { border-color: rgba(74,158,255,0.3); color: var(--info); background: rgba(74,158,255,0.08); }
    .st-act.bio:hover    { background: var(--info); color: #080a0d; }
    .st-act.deact  { border-color: rgba(224,85,85,0.3); color: var(--danger); background: rgba(224,85,85,0.08); }
    .st-act.deact:hover  { background: var(--danger); color: #fff; }
    .st-act.activ  { border-color: rgba(62,184,122,0.3); color: var(--success); background: rgba(62,184,122,0.08); }
    .st-act.activ:hover  { background: var(--success); color: #080a0d; }

    /* ════════════════════
       MODALS — shared
    ════════════════════ */
    .overlay {
      position: fixed; inset: 0; z-index: 100;
      background: rgba(8,10,13,0.88);
      backdrop-filter: blur(8px);
      display: none; align-items: center; justify-content: center;
      padding: 20px;
    }
    .overlay.show { display: flex; }
    .modal {
      background: var(--dark); border: 1px solid var(--border);
      border-radius: 16px; width: 100%; max-width: 480px;
      max-height: 90vh; overflow-y: auto;
      animation: popIn 0.25s ease;
    }
    @keyframes popIn {
      from { opacity: 0; transform: scale(0.95) translateY(8px); }
      to   { opacity: 1; transform: scale(1)    translateY(0); }
    }
    .modal-head {
      padding: 18px 22px 14px;
      border-bottom: 1px solid var(--border);
      display: flex; align-items: center; justify-content: space-between;
    }
    .modal-title { font-family: 'Bebas Neue', sans-serif; font-size: 20px; letter-spacing: 1px; }
    .modal-close {
      width: 30px; height: 30px; background: var(--card);
      border: 1px solid var(--border); border-radius: 7px;
      color: var(--soft); font-size: 14px; cursor: pointer;
      display: grid; place-items: center; transition: all 0.15s;
    }
    .modal-close:hover { border-color: var(--danger); color: var(--danger); }
    .modal-body { padding: 20px 22px; }
    .modal-foot {
      padding: 14px 22px 18px;
      border-top: 1px solid var(--border);
      display: flex; gap: 10px;
    }

    /* Form fields inside modals */
    .f-group { margin-bottom: 16px; }
    .f-label {
      display: block; font-size: 11px; font-weight: 600;
      letter-spacing: 0.12em; text-transform: uppercase;
      color: var(--muted); margin-bottom: 7px;
    }
    .f-input, .f-select {
      width: 100%; padding: 11px 13px;
      background: var(--card); border: 1.5px solid var(--border);
      border-radius: var(--radius); color: var(--text);
      font-family: 'Outfit', sans-serif; font-size: 14px;
      outline: none; transition: border-color 0.15s;
    }
    .f-input::placeholder { color: var(--muted); }
    .f-input:focus, .f-select:focus { border-color: var(--gold); }
    .f-select { appearance: none; cursor: pointer; }
    .f-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .f-hint { font-size: 11px; color: var(--muted); margin-top: 5px; line-height: 1.5; }

    /* PIN keypad in modal */
    .pin-dots { display: flex; justify-content: center; gap: 12px; margin: 10px 0 16px; }
    .pin-dot {
      width: 16px; height: 16px; border-radius: 50%;
      border: 2px solid var(--border); background: transparent;
      transition: all 0.15s;
    }
    .pin-dot.on { background: var(--gold); border-color: var(--gold); box-shadow: 0 0 8px rgba(245,166,35,0.4); }
    .keypad {
      display: grid; grid-template-columns: repeat(3, 1fr);
      gap: 8px; margin-bottom: 14px;
    }
    .key {
      padding: 14px; background: var(--card2);
      border: 1px solid var(--border); border-radius: var(--radius);
      color: var(--text); font-family: 'Outfit', sans-serif;
      font-size: 18px; font-weight: 600; cursor: pointer;
      transition: all 0.12s; display: grid; place-items: center;
      user-select: none;
    }
    .key:hover { border-color: var(--gold); color: var(--gold); }
    .key:active { transform: scale(0.94); }
    .key.zero { grid-column: 2; }
    .key.clr  { font-size: 13px; color: var(--soft); }
    .key.del  { font-size: 16px; color: var(--soft); }
    .key.clr:hover, .key.del:hover { border-color: var(--danger); color: var(--danger); }

    /* Biometric section in modal */
    .bio-section {
      padding: 16px; background: var(--card);
      border: 1px dashed var(--border); border-radius: var(--radius);
      margin-bottom: 14px; text-align: center;
    }
    .bio-icon { font-size: 32px; margin-bottom: 8px; opacity: 0.5; }
    .bio-text { font-size: 13px; color: var(--muted); line-height: 1.6; margin-bottom: 10px; }
    .bio-btn-enroll {
      padding: 8px 20px;
      background: rgba(74,158,255,0.1);
      border: 1px solid rgba(74,158,255,0.3);
      border-radius: 8px; color: var(--info);
      font-family: 'Outfit', sans-serif; font-size: 13px;
      font-weight: 600; cursor: pointer; transition: all 0.15s;
    }
    .bio-btn-enroll:hover { background: var(--info); color: #080a0d; }

    /* Action buttons */
    .btn-primary {
      flex: 2; padding: 12px;
      background: linear-gradient(135deg, var(--gold), var(--orange));
      border: none; border-radius: var(--radius);
      color: #080a0d; font-family: 'Outfit', sans-serif;
      font-size: 14px; font-weight: 700; cursor: pointer;
      transition: all 0.15s;
    }
    .btn-primary:hover { box-shadow: 0 4px 16px rgba(245,166,35,0.25); }
    .btn-primary:disabled { opacity: 0.4; pointer-events: none; }
    .btn-secondary {
      flex: 1; padding: 12px; background: transparent;
      border: 1px solid var(--border); border-radius: var(--radius);
      color: var(--soft); font-family: 'Outfit', sans-serif;
      font-size: 13px; cursor: pointer; transition: all 0.15s;
    }
    .btn-secondary:hover { border-color: var(--gold); color: var(--gold); }
    .btn-danger {
      flex: 1; padding: 12px; background: rgba(224,85,85,0.1);
      border: 1px solid rgba(224,85,85,0.3); border-radius: var(--radius);
      color: var(--danger); font-family: 'Outfit', sans-serif;
      font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.15s;
    }
    .btn-danger:hover { background: var(--danger); color: #fff; }

    /* Info box */
    .info-box {
      padding: 10px 14px; background: rgba(74,158,255,0.07);
      border: 1px solid rgba(74,158,255,0.2); border-radius: 8px;
      font-size: 12px; color: var(--info); line-height: 1.6;
      margin-bottom: 14px;
    }
    .warn-box {
      padding: 10px 14px; background: rgba(224,85,85,0.07);
      border: 1px solid rgba(224,85,85,0.2); border-radius: 8px;
      font-size: 12px; color: var(--danger); line-height: 1.6;
      margin-bottom: 14px;
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

    /* Scrollbar */
    ::-webkit-scrollbar { width: 4px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

    /* Responsive */
    @media (max-width: 900px) {
      .bt-head, .bt-row { grid-template-columns: 70px 1fr 80px 80px 120px; }
      .bt-head > *:nth-child(5),
      .bt-row  > *:nth-child(5) { display: none; }
      .st-head, .st-row { grid-template-columns: 70px 1fr 80px 80px 140px; }
      .st-head > *:nth-child(5),
      .st-head > *:nth-child(6),
      .st-row  > *:nth-child(5),
      .st-row  > *:nth-child(6) { display: none; }
    }
    @media (max-width: 600px) {
      html, body { overflow: auto; }
      .app { grid-template-rows: 58px 44px auto; height: auto; }
      .main { min-height: calc(100vh - 102px); }
    }
  </style>
</head>
<body>

<!-- Toast -->
<div class="toast" id="toast"></div>

<!-- ══ MODAL: Add/Edit Staff ══ -->
<div class="overlay" id="modal-staff">
  <div class="modal">
    <div class="modal-head">
      <span class="modal-title" id="staff-modal-title">Add Staff Member</span>
      <button class="modal-close" onclick="closeModal('modal-staff')">✕</button>
    </div>
    <div class="modal-body">
      <div class="info-box">
        Staff IDs are auto-generated based on role. The manager sets the PIN and optionally enrolls a fingerprint. The staff member uses these to log in.
      </div>
      <div class="f-row">
        <div class="f-group">
          <label class="f-label">Full Name</label>
          <input class="f-input" type="text" id="sf-name" placeholder="e.g. Sipho Dlamini"/>
        </div>
        <div class="f-group">
          <label class="f-label">Role</label>
          <select class="f-select" id="sf-role" onchange="updateStaffId()">
            <option value="waiter">Waiter</option>
            <option value="cashier">Cashier</option>
            <option value="manager">Manager</option>
            <option value="admin">Admin</option>
          </select>
        </div>
      </div>
      <div class="f-group">
        <label class="f-label">Staff ID (Auto-assigned)</label>
        <input class="f-input" type="text" id="sf-id" readonly
               style="background:var(--card2);color:var(--gold);font-weight:700;letter-spacing:0.1em"/>
        <p class="f-hint">Format: W001 = Waiter, C001 = Cashier, M001 = Manager, A001 = Admin</p>
      </div>
      <div class="f-group">
        <label class="f-label">Set 4-Digit PIN</label>
        <div class="pin-dots">
          <div class="pin-dot" id="md0"></div>
          <div class="pin-dot" id="md1"></div>
          <div class="pin-dot" id="md2"></div>
          <div class="pin-dot" id="md3"></div>
        </div>
        <div class="keypad">
          <button class="key" onclick="modalPin('1')">1</button>
          <button class="key" onclick="modalPin('2')">2</button>
          <button class="key" onclick="modalPin('3')">3</button>
          <button class="key" onclick="modalPin('4')">4</button>
          <button class="key" onclick="modalPin('5')">5</button>
          <button class="key" onclick="modalPin('6')">6</button>
          <button class="key" onclick="modalPin('7')">7</button>
          <button class="key" onclick="modalPin('8')">8</button>
          <button class="key" onclick="modalPin('9')">9</button>
          <button class="key clr" onclick="clearModalPin()">CLR</button>
          <button class="key zero" onclick="modalPin('0')">0</button>
          <button class="key del" onclick="delModalPin()">⌫</button>
        </div>
        <p class="f-hint">Manager sets the initial PIN. Staff can request a PIN reset later.</p>
      </div>
      <!-- Biometric enrolment -->
      <div class="bio-section">
        <div class="bio-icon">👆</div>
        <div class="bio-text">
          Fingerprint enrolment requires a connected fingerprint scanner.<br/>
          Connect the scanner then click Enroll Fingerprint.
        </div>
        <button class="bio-btn-enroll" onclick="enrollBio()">Enroll Fingerprint</button>
      </div>
    </div>
    <div class="modal-foot">
      <button class="btn-secondary" onclick="closeModal('modal-staff')">Cancel</button>
      <button class="btn-primary" onclick="saveStaff()">Save Staff Member</button>
    </div>
  </div>
</div>

<!-- ══ MODAL: Reset PIN ══ -->
<div class="overlay" id="modal-pin">
  <div class="modal">
    <div class="modal-head">
      <span class="modal-title">Reset PIN</span>
      <button class="modal-close" onclick="closeModal('modal-pin')">✕</button>
    </div>
    <div class="modal-body">
      <div class="warn-box" id="pin-reset-name">Setting new PIN for staff member.</div>
      <label class="f-label">New 4-Digit PIN</label>
      <div class="pin-dots">
        <div class="pin-dot" id="pd0"></div>
        <div class="pin-dot" id="pd1"></div>
        <div class="pin-dot" id="pd2"></div>
        <div class="pin-dot" id="pd3"></div>
      </div>
      <div class="keypad">
        <button class="key" onclick="resetPin('1')">1</button>
        <button class="key" onclick="resetPin('2')">2</button>
        <button class="key" onclick="resetPin('3')">3</button>
        <button class="key" onclick="resetPin('4')">4</button>
        <button class="key" onclick="resetPin('5')">5</button>
        <button class="key" onclick="resetPin('6')">6</button>
        <button class="key" onclick="resetPin('7')">7</button>
        <button class="key" onclick="resetPin('8')">8</button>
        <button class="key" onclick="resetPin('9')">9</button>
        <button class="key clr" onclick="clearResetPin()">CLR</button>
        <button class="key zero" onclick="resetPin('0')">0</button>
        <button class="key del" onclick="delResetPin()">⌫</button>
      </div>
    </div>
    <div class="modal-foot">
      <button class="btn-secondary" onclick="closeModal('modal-pin')">Cancel</button>
      <button class="btn-primary" id="btn-save-pin" onclick="savePinReset()" disabled>Approve & Save PIN</button>
    </div>
  </div>
</div>

<!-- ══ MODAL: Reserve Table ══ -->
<div class="overlay" id="modal-table">
  <div class="modal">
    <div class="modal-head">
      <span class="modal-title" id="tbl-modal-title">Table —</span>
      <button class="modal-close" onclick="closeModal('modal-table')">✕</button>
    </div>
    <div class="modal-body">
      <div class="f-group">
        <label class="f-label">Action</label>
        <select class="f-select" id="tbl-action">
          <option value="reserve">Reserve Table</option>
          <option value="unreserve">Remove Reservation</option>
          <option value="available">Force Available (override cleaning)</option>
        </select>
      </div>
      <div class="f-group" id="res-name-wrap">
        <label class="f-label">Reserved For (Name / Event)</label>
        <input class="f-input" type="text" id="res-name" placeholder="e.g. Smith — Birthday Party"/>
      </div>
      <div class="f-group" id="res-time-wrap">
        <label class="f-label">Reservation Time</label>
        <input class="f-input" type="time" id="res-time"/>
      </div>
    </div>
    <div class="modal-foot">
      <button class="btn-secondary" onclick="closeModal('modal-table')">Cancel</button>
      <button class="btn-primary" onclick="saveTableAction()">Confirm</button>
    </div>
  </div>
</div>

<!-- ══ MODAL: Move Bill ══ -->
<div class="overlay" id="modal-move">
  <div class="modal">
    <div class="modal-head">
      <span class="modal-title">Move Bill</span>
      <button class="modal-close" onclick="closeModal('modal-move')">✕</button>
    </div>
    <div class="modal-body">
      <div class="info-box" id="move-info">Moving bill from Table —</div>
      <div class="f-group">
        <label class="f-label">Move To Table</label>
        <select class="f-select" id="move-target"></select>
      </div>
      <p class="f-hint">The bill ownership stays with the original staff member. Only the table assignment changes.</p>
    </div>
    <div class="modal-foot">
      <button class="btn-secondary" onclick="closeModal('modal-move')">Cancel</button>
      <button class="btn-primary" onclick="confirmMove()">Move Bill</button>
    </div>
  </div>
</div>

<!-- ══ MODAL: Void Order ══ -->
<div class="overlay" id="modal-void">
  <div class="modal">
    <div class="modal-head">
      <span class="modal-title">Void Order</span>
      <button class="modal-close" onclick="closeModal('modal-void')">✕</button>
    </div>
    <div class="modal-body">
      <div class="warn-box" id="void-info">You are about to void an order. This cannot be undone.</div>
      <div class="f-group">
        <label class="f-label">Reason for Void (Required)</label>
        <input class="f-input" type="text" id="void-reason" placeholder="e.g. Customer cancelled, Wrong order, Duplicate"/>
      </div>
      <p class="f-hint">All voids are logged with your staff ID, timestamp and reason for audit purposes.</p>
    </div>
    <div class="modal-foot">
      <button class="btn-secondary" onclick="closeModal('modal-void')">Cancel</button>
      <button class="btn-danger" onclick="confirmVoid()">Void Order</button>
    </div>
  </div>
</div>

<!-- ══ APP ══ -->
<div class="app">

  <!-- Header -->
  <header class="header">
    <div class="logo">
      <div class="logo-box">🍽️</div>
      <div class="logo-name">Byte<span>Savor</span></div>
    </div>
    <div class="hdiv"></div>
    <div class="hscreen">Manager</div>
    <div style="font-family:'Bebas Neue',sans-serif;font-size:13px;letter-spacing:0.2em;color:var(--purple);text-transform:uppercase;background:rgba(155,114,255,0.10);border:1px solid rgba(155,114,255,0.25);padding:4px 12px;border-radius:20px;flex-shrink:0">📊 Manager Console</div>
    <div class="hright">
      <div class="staff-pill">
        <div class="sdot"></div>
        <span id="mgr-name">Manager</span>
      </div>
      <button class="btn-out" onclick="window.location.href='../logout.php'">Sign Out</button>
    </div>
  </header>

  <!-- Tabs -->
  <div class="tabs">
    <div class="tab on"  onclick="showTab('overview')" id="tab-overview">📊 Overview</div>
    <div class="tab"     onclick="showTab('tables')"   id="tab-tables">🪑 Tables & Bills</div>
    <div class="tab"     onclick="showTab('staff')"    id="tab-staff">👥 Staff</div>
    <div class="tab"     onclick="showTab('payroll')" id="tab-payroll">💰 Payroll</div>
  </div>

  <!-- ══ TAB: OVERVIEW ══ -->
  <div class="main on" id="main-overview">
    <p class="sec">Today's Summary</p>
    <div class="overview-cards">
      <div class="ov-card gold">
        <div class="ov-label">Sales Today</div>
        <div class="ov-val" id="ov-sales">R0</div>
        <div class="ov-sub" id="ov-sales-sub">0 transactions</div>
      </div>
      <div class="ov-card green">
        <div class="ov-label">Open Tables</div>
        <div class="ov-val" id="ov-open">0</div>
        <div class="ov-sub" id="ov-open-sub">of 12 tables</div>
      </div>
      <div class="ov-card blue">
        <div class="ov-label">Active Orders</div>
        <div class="ov-val" id="ov-orders">0</div>
        <div class="ov-sub">in kitchen now</div>
      </div>
      <div class="ov-card purple">
        <div class="ov-label">Staff on Shift</div>
        <div class="ov-val" id="ov-staff">0</div>
        <div class="ov-sub">clocked in</div>
      </div>
      <div class="ov-card orange">
        <div class="ov-label">Avg Order Value</div>
        <div class="ov-val" id="ov-avg">R0</div>
        <div class="ov-sub">per bill today</div>
      </div>
    </div>

    <p class="sec">Staff Currently on Shift</p>
    <div class="shift-list" id="shift-list"></div>
  </div>

  <!-- ══ TAB: TABLES & BILLS ══ -->
  <div class="main" id="main-tables">
    <p class="sec">Table Overview — Tap to Manage</p>
    <div class="tbl-legend">
      <div class="leg-item"><div class="leg-dot" style="background:var(--success)"></div>Available</div>
      <div class="leg-item"><div class="leg-dot" style="background:var(--danger)"></div>Occupied</div>
      <div class="leg-item"><div class="leg-dot" style="background:var(--gold)"></div>Reserved</div>
      <div class="leg-item"><div class="leg-dot" style="background:var(--info)"></div>Cleaning</div>
    </div>
    <div class="tables-grid" id="mgr-tables-grid"></div>

    <p class="sec" style="margin-top:24px">All Open Bills</p>
    <div class="bills-table">
      <div class="bt-head">
        <div>Table</div>
        <div>Waiter</div>
        <div>Items</div>
        <div>Total</div>
        <div>Type</div>
        <div>Time</div>
        <div>Actions</div>
      </div>
      <div id="bills-body"></div>
    </div>
  </div>

  <!-- ══ TAB: STAFF ══ -->
  <div class="main" id="main-staff">
    <div class="staff-toolbar">
      <button class="btn-add-staff" onclick="openAddStaff()">+ Add Staff Member</button>
      <input class="staff-search" type="text" placeholder="Search by name or ID..."
             oninput="filterStaff(this.value)"/>
    </div>
    <div class="staff-table">
      <div class="st-head">
        <div>ID</div>
        <div>Name</div>
        <div>Role</div>
        <div>Status</div>
        <div>PIN</div>
        <div>Biometric</div>
        <div>Actions</div>
      </div>
      <div id="staff-body"></div>
    </div>
  </div>

  <!-- ══ TAB: PAYROLL ══ -->
  <div class="main" id="main-payroll">

    <!-- Date range + set pay modal trigger -->
    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:20px">
      <div style="display:flex;align-items:center;gap:8px">
        <span style="font-size:12px;color:var(--muted);font-weight:600;letter-spacing:0.1em;text-transform:uppercase">From</span>
        <input type="date" id="pay-from" style="padding:8px 12px;background:var(--card);border:1px solid var(--border);border-radius:var(--radius);color:var(--text);font-family:'Outfit',sans-serif;font-size:13px;outline:none"/>
      </div>
      <div style="display:flex;align-items:center;gap:8px">
        <span style="font-size:12px;color:var(--muted);font-weight:600;letter-spacing:0.1em;text-transform:uppercase">To</span>
        <input type="date" id="pay-to" style="padding:8px 12px;background:var(--card);border:1px solid var(--border);border-radius:var(--radius);color:var(--text);font-family:'Outfit',sans-serif;font-size:13px;outline:none"/>
      </div>
      <button onclick="renderPayroll()" style="padding:8px 18px;background:linear-gradient(135deg,var(--gold),var(--orange));border:none;border-radius:var(--radius);color:#080a0d;font-family:'Outfit',sans-serif;font-size:13px;font-weight:700;cursor:pointer">Calculate</button>
    </div>

    <!-- Summary card -->
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;margin-bottom:20px" id="pay-summary-cards"></div>

    <!-- Payroll table -->
    <div class="card">
      <div class="card-head">
        <span class="card-head-title">Staff Payroll Breakdown</span>
        <span style="font-size:11px;color:var(--muted)">Click a row to view shifts or set pay rate</span>
      </div>
      <div style="display:grid;grid-template-columns:80px 1fr 90px 80px 80px 100px 120px 120px;padding:10px 16px;background:var(--card2);border-bottom:1px solid var(--border);font-size:11px;font-weight:600;letter-spacing:0.1em;text-transform:uppercase;color:var(--muted);gap:8px">
        <div>ID</div><div>Name</div><div>Role</div>
        <div>Pay Type</div><div>Rate</div>
        <div>Shifts</div><div>Hours</div><div>Gross Pay</div>
      </div>
      <div id="payroll-body"></div>
    </div>
  </div>

  <!-- ══ MODAL: Set Pay Rate ══ -->
  <div class="overlay" id="modal-pay">
    <div class="modal">
      <div class="modal-head">
        <span class="modal-title" id="pay-modal-name">Set Pay Rate</span>
        <button class="modal-close" onclick="closeModal('modal-pay')">✕</button>
      </div>
      <div class="modal-body">
        <div class="info-box">Setting the pay type and rate for this staff member. This is used to calculate their salary from their shift hours.</div>
        <div class="f-group">
          <label class="f-label">Pay Type</label>
          <select class="f-select" id="pay-type" onchange="togglePayFields()">
            <option value="hourly">Hourly Rate</option>
            <option value="salary">Fixed Monthly Salary</option>
          </select>
        </div>
        <div class="f-group" id="hourly-wrap">
          <label class="f-label">Hourly Rate (R per hour)</label>
          <input class="f-input" type="number" id="hourly-rate" placeholder="e.g. 45.00" step="0.01" min="0"/>
        </div>
        <div class="f-group" id="salary-wrap" style="display:none">
          <label class="f-label">Monthly Salary (R)</label>
          <input class="f-input" type="number" id="monthly-salary" placeholder="e.g. 8500.00" step="0.01" min="0"/>
        </div>
      </div>
      <div class="modal-foot">
        <button class="btn-secondary" onclick="closeModal('modal-pay')">Cancel</button>
        <button class="btn-primary" onclick="savePayRate()">Save Pay Rate</button>
      </div>
    </div>
  </div>

  <!-- ══ MODAL: Shift History ══ -->
  <div class="overlay" id="modal-shifts">
    <div class="modal">
      <div class="modal-head">
        <span class="modal-title" id="shifts-modal-name">Shift History</span>
        <button class="modal-close" onclick="closeModal('modal-shifts')">✕</button>
      </div>
      <div class="modal-body">
        <div id="shifts-list"></div>
        <div style="margin-top:14px;padding:12px 16px;background:var(--card);border-radius:var(--radius);display:flex;justify-content:space-between;font-size:14px;font-weight:600">
          <span style="color:var(--soft)">Total Hours</span>
          <span style="color:var(--gold)" id="shifts-total-hours">0h</span>
        </div>
      </div>
      <div class="modal-foot">
        <button class="btn-primary" onclick="closeModal('modal-shifts')">Close</button>
      </div>
    </div>
  </div>


</div>

<script>
// ════════════════════════════════════════════
//  ByteSavor — Manager Dashboard
// ════════════════════════════════════════════

// Demo data — in production fetched from PHP/MySQL
const MGR_NAME = 'Thabo';
document.getElementById('mgr-name').textContent = MGR_NAME;

// ── Tables ──
let TABLES = [
  { id:1,  num:'T1',  cap:2,  status:'available', reservedFor:'',        cleaningSince:null },
  { id:2,  num:'T2',  cap:4,  status:'occupied',  reservedFor:'',        cleaningSince:null },
  { id:3,  num:'T3',  cap:4,  status:'available', reservedFor:'',        cleaningSince:null },
  { id:4,  num:'T4',  cap:6,  status:'occupied',  reservedFor:'',        cleaningSince:null },
  { id:5,  num:'T5',  cap:2,  status:'reserved',  reservedFor:'Smith — 19:00', cleaningSince:null },
  { id:6,  num:'T6',  cap:8,  status:'available', reservedFor:'',        cleaningSince:null },
  { id:7,  num:'T7',  cap:4,  status:'cleaning',  reservedFor:'',        cleaningSince:Date.now()-(2*60*1000) },
  { id:8,  num:'T8',  cap:4,  status:'available', reservedFor:'',        cleaningSince:null },
  { id:9,  num:'VIP1',cap:8,  status:'available', reservedFor:'',        cleaningSince:null },
  { id:10, num:'VIP2',cap:10, status:'reserved',  reservedFor:'Ntombi — Birthday', cleaningSince:null },
  { id:11, num:'BAR1',cap:4,  status:'available', reservedFor:'',        cleaningSince:null },
  { id:12, num:'BAR2',cap:4,  status:'occupied',  reservedFor:'',        cleaningSince:null },
];

// ── Bills ──
let BILLS = [
  { id:2001, table:'T2',  type:'dine_in',  staffId:'W001', staffName:'Amahle', items:3, total:450.00, createdAt: Date.now()-(25*60*1000) },
  { id:2002, table:'T4',  type:'dine_in',  staffId:'W002', staffName:'Sipho',  items:3, total:522.75, createdAt: Date.now()-(42*60*1000) },
  { id:2003, table:null,  type:'takeaway', staffId:'W001', staffName:'Amahle', items:3, total:299.25, createdAt: Date.now()-(10*60*1000) },
  { id:2004, table:null,  type:'online',   staffId:'SYS',  staffName:'Online', items:2, total:400.00, createdAt: Date.now()-(5*60*1000)  },
  { id:2005, table:'BAR2',type:'dine_in',  staffId:'W003', staffName:'Lesego', items:2, total:155.00, createdAt: Date.now()-(8*60*1000)  },
];

// ── Staff ──
let STAFF = [
  { id:1, staffId:'M001', name:'Thabo Mokoena', role:'manager', active:true,  pinSet:true,  bioEnrolled:false },
  { id:2, staffId:'W001', name:'Amahle Dube',   role:'waiter',  active:true,  pinSet:true,  bioEnrolled:true  },
  { id:3, staffId:'W002', name:'Sipho Nkosi',   role:'waiter',  active:true,  pinSet:true,  bioEnrolled:false },
  { id:4, staffId:'W003', name:'Lesego Khumalo',role:'waiter',  active:true,  pinSet:true,  bioEnrolled:false },
  { id:5, staffId:'C001', name:'Zanele Motha',  role:'cashier', active:true,  pinSet:true,  bioEnrolled:false },
  { id:6, staffId:'W004', name:'Bongani Zulu',  role:'waiter',  active:false, pinSet:false, bioEnrolled:false },
];

// ── Tab switching ──
function showTab(tab) {
  ['overview','tables','staff','payroll'].forEach(t => {
    document.getElementById('tab-' + t).classList.toggle('on', t === tab);
    document.getElementById('main-' + t).classList.toggle('on', t === tab);
  });
  if (tab === 'overview') renderOverview();
  if (tab === 'tables')   renderTables();
  if (tab === 'staff')    renderStaff();
  if (tab === 'payroll')  renderPayroll();
}

// ════════════════════
//  OVERVIEW
// ════════════════════
function renderOverview() {
  const totalSales = 12840.50;
  const txCount    = 28;
  const openTables = TABLES.filter(t => t.status === 'occupied').length;
  const activeOrders = 4;
  const onShift    = STAFF.filter(s => s.active).length;
  const avg        = totalSales / txCount;

  document.getElementById('ov-sales').textContent     = 'R' + totalSales.toLocaleString('en-ZA', {minimumFractionDigits:2});
  document.getElementById('ov-sales-sub').textContent = txCount + ' transactions';
  document.getElementById('ov-open').textContent      = openTables;
  document.getElementById('ov-open-sub').textContent  = 'of ' + TABLES.length + ' tables';
  document.getElementById('ov-orders').textContent    = activeOrders;
  document.getElementById('ov-staff').textContent     = onShift;
  document.getElementById('ov-avg').textContent       = 'R' + avg.toFixed(2);

  const colors = { manager:'var(--purple)', cashier:'var(--gold)', waiter:'var(--info)' };
  document.getElementById('shift-list').innerHTML = STAFF.filter(s => s.active).map(s => `
    <div class="shift-row">
      <div class="shift-dot" style="background:${colors[s.role]||'var(--soft)'}; box-shadow:0 0 5px ${colors[s.role]||'var(--soft)'}"></div>
      <div class="shift-name">${s.name}</div>
      <div class="shift-role role-${s.role}">${s.role}</div>
      <div class="shift-id">${s.staffId}</div>
      <div class="shift-since">On shift</div>
    </div>
  `).join('');
}
renderOverview();

// ════════════════════
//  TABLES & BILLS
// ════════════════════
let selectedTableId  = null;
let selectedBillId   = null;

function renderTables() {
  document.getElementById('mgr-tables-grid').innerHTML = TABLES.map(t => `
    <div class="tbl-btn ${t.status}" onclick="openTableModal(${t.id})">
      <div class="tbl-status-dot"></div>
      <div class="tbl-num">${t.num}</div>
      <div class="tbl-cap">${t.cap} seats</div>
      ${t.reservedFor ? `<div class="tbl-owner" style="font-size:9px;color:var(--gold);text-align:center;padding:0 4px">${t.reservedFor}</div>` : ''}
    </div>
  `).join('');
  renderBills();
}

function openTableModal(id) {
  selectedTableId = id;
  const t = TABLES.find(t => t.id === id);
  document.getElementById('tbl-modal-title').textContent = 'Table ' + t.num + ' — ' + t.status.charAt(0).toUpperCase() + t.status.slice(1);
  const sel = document.getElementById('tbl-action');
  sel.value = t.status === 'reserved' ? 'unreserve' : 'reserve';
  toggleResFields();
  sel.onchange = toggleResFields;
  openModal('modal-table');
}

function toggleResFields() {
  const v = document.getElementById('tbl-action').value;
  document.getElementById('res-name-wrap').style.display = v === 'reserve' ? 'block' : 'none';
  document.getElementById('res-time-wrap').style.display = v === 'reserve' ? 'block' : 'none';
}

function saveTableAction() {
  const t      = TABLES.find(t => t.id === selectedTableId);
  const action = document.getElementById('tbl-action').value;
  if (action === 'reserve') {
    t.status      = 'reserved';
    t.reservedFor = document.getElementById('res-name').value || 'Reserved';
  } else if (action === 'unreserve') {
    t.status      = 'available';
    t.reservedFor = '';
  } else {
    t.status      = 'available';
    t.cleaningSince = null;
  }
  closeModal('modal-table');
  renderTables();
  showToast('Table ' + t.num + ' updated ✓', 'ok');
}

function renderBills() {
  function elapsed(ts) {
    const m = Math.floor((Date.now() - ts) / 60000);
    return m < 60 ? m + 'm ago' : Math.floor(m/60) + 'h ' + (m%60) + 'm ago';
  }
  document.getElementById('bills-body').innerHTML = BILLS.map(b => `
    <div class="bt-row">
      <div class="bt-table">${b.table || (b.type === 'takeaway' ? '🥡' : '📱')}</div>
      <div class="bt-waiter">${b.staffName}<br/><span style="font-size:10px;color:var(--muted)">${b.staffId}</span></div>
      <div class="bt-items">${b.items} items</div>
      <div class="bt-total">R${b.total.toFixed(2)}</div>
      <div style="font-size:11px;color:var(--soft)">${b.type.replace('_',' ')}</div>
      <div class="bt-time">${elapsed(b.createdAt)}</div>
      <div class="bt-acts">
        ${b.table ? `<button class="bt-act move" onclick="openMoveModal(${b.id})">Move</button>` : ''}
        <button class="bt-act print" onclick="showToast('🖨️ Printing...','ok')">Print</button>
        <button class="bt-act void" onclick="openVoidModal(${b.id})">Void</button>
      </div>
    </div>
  `).join('');
}

// ── Move bill ──
function openMoveModal(billId) {
  selectedBillId = billId;
  const b = BILLS.find(b => b.id === billId);
  document.getElementById('move-info').textContent = 'Moving bill from ' + (b.table || 'Takeaway') + ' (R' + b.total.toFixed(2) + ') — waiter: ' + b.staffName;
  const available = TABLES.filter(t => t.status === 'available' && t.num !== b.table);
  document.getElementById('move-target').innerHTML = available.map(t =>
    `<option value="${t.num}">${t.num} (${t.cap} seats)</option>`
  ).join('');
  openModal('modal-move');
}

function confirmMove() {
  const b      = BILLS.find(b => b.id === selectedBillId);
  const target = document.getElementById('move-target').value;
  const from   = b.table;
  // Mark old table available, new table occupied
  TABLES.forEach(t => {
    if (t.num === from)   t.status = 'available';
    if (t.num === target) t.status = 'occupied';
  });
  b.table = target;
  closeModal('modal-move');
  renderTables();
  showToast('Bill moved from ' + from + ' → ' + target + ' ✓', 'ok');
}

// ── Void order ──
function openVoidModal(billId) {
  selectedBillId = billId;
  const b = BILLS.find(b => b.id === billId);
  document.getElementById('void-info').textContent = 'Voiding order for ' + (b.table || b.type) + ' · R' + b.total.toFixed(2) + ' · ' + b.staffName + '. This cannot be undone.';
  document.getElementById('void-reason').value = '';
  openModal('modal-void');
}

function confirmVoid() {
  const reason = document.getElementById('void-reason').value.trim();
  if (!reason) { showToast('Please enter a reason for the void', 'err'); return; }
  BILLS = BILLS.filter(b => b.id !== selectedBillId);
  closeModal('modal-void');
  renderBills();
  showToast('Order voided and logged ✓', 'ok');
}

// ════════════════════
//  STAFF
// ════════════════════
let editingStaffId  = null;
let modalPinVal     = '';
let resetPinVal     = '';
let resetTargetId   = null;

function renderStaff(filter) {
  const roleColors = { manager:'var(--purple)', cashier:'var(--gold)', waiter:'var(--info)', admin:'var(--orange)' };
  const roleBg     = { manager:'rgba(155,114,255,0.12)', cashier:'rgba(245,166,35,0.12)', waiter:'rgba(74,158,255,0.12)', admin:'rgba(232,82,10,0.12)' };
  let list = STAFF;
  if (filter) list = list.filter(s => s.name.toLowerCase().includes(filter.toLowerCase()) || s.staffId.toLowerCase().includes(filter.toLowerCase()));
  document.getElementById('staff-body').innerHTML = list.map(s => `
    <div class="st-row">
      <div class="st-id">${s.staffId}</div>
      <div class="st-name">${s.name}</div>
      <div>
        <span class="st-role" style="background:${roleBg[s.role]};color:${roleColors[s.role]}">${s.role}</span>
      </div>
      <div>
        <span class="st-status-badge ${s.active ? 'active' : 'inactive'}">${s.active ? 'Active' : 'Inactive'}</span>
      </div>
      <div style="font-size:12px;color:${s.pinSet ? 'var(--success)' : 'var(--danger)'}">
        ${s.pinSet ? '✓ Set' : '✗ Not set'}
      </div>
      <div class="st-bio ${s.bioEnrolled ? 'enrolled' : ''}">
        ${s.bioEnrolled ? '👆 ✓' : '👆 —'}
      </div>
      <div class="st-acts">
        <button class="st-act pin"  onclick="openPinReset(${s.id})">Reset PIN</button>
        <button class="st-act bio"  onclick="openBioEnroll(${s.id})">Fingerprint</button>
        <button class="st-act ${s.active ? 'deact' : 'activ'}"
                onclick="toggleStaffActive(${s.id})">
          ${s.active ? 'Deactivate' : 'Activate'}
        </button>
      </div>
    </div>
  `).join('');
}
renderStaff();

function filterStaff(val) { renderStaff(val); }

// ── Auto-generate staff ID ──
function updateStaffId() {
  const role = document.getElementById('sf-role').value;
  const prefix = { waiter:'W', cashier:'C', manager:'M', admin:'A' }[role];
  const existing = STAFF.filter(s => s.role === role).length + 1;
  document.getElementById('sf-id').value = prefix + String(existing).padStart(3,'0');
}

// ── Open add staff modal ──
function openAddStaff() {
  editingStaffId = null;
  modalPinVal = '';
  document.getElementById('staff-modal-title').textContent = 'Add Staff Member';
  document.getElementById('sf-name').value = '';
  document.getElementById('sf-role').value = 'waiter';
  updateStaffId();
  updateModalDots();
  openModal('modal-staff');
}

// ── Modal PIN keypad ──
function modalPin(n) {
  if (modalPinVal.length >= 4) return;
  modalPinVal += n;
  updateModalDots();
}
function delModalPin()   { modalPinVal = modalPinVal.slice(0,-1); updateModalDots(); }
function clearModalPin() { modalPinVal = ''; updateModalDots(); }
function updateModalDots() {
  for (let i = 0; i < 4; i++)
    document.getElementById('md' + i).classList.toggle('on', i < modalPinVal.length);
}

// ── Save new staff ──
function saveStaff() {
  const name = document.getElementById('sf-name').value.trim();
  const role = document.getElementById('sf-role').value;
  const id   = document.getElementById('sf-id').value;
  if (!name)               { showToast('Please enter a name', 'err'); return; }
  if (modalPinVal.length < 4) { showToast('Please set a 4-digit PIN', 'err'); return; }
  STAFF.push({ id: STAFF.length + 1, staffId: id, name, role, active: true, pinSet: true, bioEnrolled: false });
  closeModal('modal-staff');
  renderStaff();
  showToast(name + ' added as ' + role + ' (' + id + ') ✓', 'ok');
}

// ── Reset PIN ──
function openPinReset(id) {
  resetTargetId = id;
  resetPinVal   = '';
  const s = STAFF.find(s => s.id === id);
  document.getElementById('pin-reset-name').textContent = 'Setting new PIN for ' + s.name + ' (' + s.staffId + ')';
  updateResetDots();
  document.getElementById('btn-save-pin').disabled = true;
  openModal('modal-pin');
}
function resetPin(n) {
  if (resetPinVal.length >= 4) return;
  resetPinVal += n;
  updateResetDots();
  if (resetPinVal.length === 4) document.getElementById('btn-save-pin').disabled = false;
}
function delResetPin()   { resetPinVal = resetPinVal.slice(0,-1); updateResetDots(); document.getElementById('btn-save-pin').disabled = resetPinVal.length < 4; }
function clearResetPin() { resetPinVal = ''; updateResetDots(); document.getElementById('btn-save-pin').disabled = true; }
function updateResetDots() {
  for (let i = 0; i < 4; i++)
    document.getElementById('pd' + i).classList.toggle('on', i < resetPinVal.length);
}
function savePinReset() {
  const s = STAFF.find(s => s.id === resetTargetId);
  s.pinSet = true;
  closeModal('modal-pin');
  renderStaff();
  showToast('PIN updated for ' + s.name + ' ✓', 'ok');
  // In production: POST to api/staff.php with hashed PIN
}

// ── Toggle active ──
function toggleStaffActive(id) {
  const s = STAFF.find(s => s.id === id);
  s.active = !s.active;
  renderStaff();
  showToast(s.name + (s.active ? ' activated' : ' deactivated') + ' ✓', 'ok');
}

// ── Biometric enroll placeholder ──
function enrollBio() {
  showToast('👆 Scanner not connected — wire up hardware SDK here', 'err');
}
function openBioEnroll(id) {
  const s = STAFF.find(s => s.id === id);
  showToast('👆 Fingerprint enrolment for ' + s.name + ' — connect scanner to proceed', '');
  // In production: trigger biometric SDK for this staffId
}

// ── Modal helpers ──
function openModal(id)  { document.getElementById(id).classList.add('show'); }
function closeModal(id) { document.getElementById(id).classList.remove('show'); }

// Close on overlay click
document.querySelectorAll('.overlay').forEach(el => {
  el.addEventListener('click', e => { if (e.target === el) el.classList.remove('show'); });
});

// ── Toast ──
let toastTimer;
function showToast(msg, type) {
  const t = document.getElementById('toast');
  t.textContent = msg; t.className = 'toast show ' + (type || '');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => { t.className = 'toast'; }, 2800);
}
</script>
</body>
</html>
