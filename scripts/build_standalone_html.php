<?php

$logoPath = __DIR__ . '/../public/images/maruti-logo.png';
$logoBase64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : '';

$sigPath = __DIR__ . '/../public/images/maruti-signature.png';
$sigBase64 = file_exists($sigPath) ? base64_encode(file_get_contents($sigPath)) : '';

$html2pdfPath = __DIR__ . '/../public/js/html2pdf.bundle.min.js';
$html2pdfBase64 = file_exists($html2pdfPath) ? base64_encode(file_get_contents($html2pdfPath)) : '';

$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
  <title>Maruti Sales & Services — Offline Quotation Maker</title>
  <link rel="icon" href="data:image/png;base64,{$logoBase64}">
  <!-- Client-side PDF Generation Engine (html2pdf.js CDN + Offline Base64 Fallback) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
  <script>
    if (typeof html2pdf === 'undefined' && '{$html2pdfBase64}' !== '') {
      try {
        const s = document.createElement('script');
        s.textContent = atob('{$html2pdfBase64}');
        document.head.appendChild(s);
      } catch (e) {
        console.warn('Offline html2pdf injection error:', e);
      }
    }
  </script>
  <style>
    :root {
      --primary: #BE9223;
      --primary-dark: #967215;
      --secondary: #3F527D;
      --secondary-dark: #2B3A5A;
      --bg: #F1F5F9;
      --card-bg: #FFFFFF;
      --text: #1E293B;
      --text-muted: #64748B;
      --border: #CBD5E1;
      --danger: #DC2626;
      --success: #16A34A;
      --radius: 8px;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
    body { background-color: var(--bg); color: var(--text); padding-bottom: 60px; min-height: 100vh; }

    /* Utilities */
    .no-print { display: block; }
    .print-only { display: none; }
    .nowrap { white-space: nowrap; }

    /* Top Navigation Bar */
    .navbar {
      background: linear-gradient(135deg, var(--secondary-dark), var(--secondary));
      color: #fff;
      padding: 12px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-shadow: 0 2px 12px rgba(0,0,0,0.15);
      position: sticky;
      top: 0;
      z-index: 100;
    }
    .nav-brand { display: flex; align-items: center; gap: 10px; }
    .nav-brand img { height: 38px; width: auto; background: #fff; border-radius: 6px; padding: 2px; }
    .nav-title { font-size: 1.15rem; font-weight: 700; letter-spacing: 0.3px; line-height: 1.2; }
    .nav-subtitle { font-size: 0.72rem; color: #E2E8F0; opacity: 0.9; }
    .nav-actions { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }

    /* Buttons */
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      padding: 8px 14px;
      border-radius: var(--radius);
      font-size: 0.85rem;
      font-weight: 600;
      cursor: pointer;
      border: 1px solid transparent;
      transition: all 0.2s ease;
      text-decoration: none;
      white-space: nowrap;
    }
    .btn-primary { background-color: var(--primary); color: #fff; }
    .btn-primary:hover { background-color: var(--primary-dark); }
    .btn-secondary { background-color: var(--secondary); color: #fff; }
    .btn-secondary:hover { background-color: var(--secondary-dark); }
    .btn-success { background-color: var(--success); color: #fff; }
    .btn-success:hover { background-color: #15803d; }
    .btn-outline { background-color: transparent; border-color: var(--border); color: var(--text); }
    .btn-outline:hover { background-color: #E2E8F0; }
    .btn-danger { background-color: var(--danger); color: #fff; }
    .btn-sm { padding: 4px 8px; font-size: 0.75rem; }

    /* View Switcher Bar (Tabs) */
    .view-switcher-bar {
      background: #FFFFFF;
      border-bottom: 1px solid var(--border);
      padding: 8px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 10px;
    }
    .tab-group { display: inline-flex; background: #E2E8F0; padding: 3px; border-radius: var(--radius); }
    .tab-btn {
      padding: 6px 14px;
      border: none;
      background: transparent;
      font-size: 0.8rem;
      font-weight: 600;
      color: var(--text-muted);
      border-radius: 6px;
      cursor: pointer;
      transition: all 0.2s;
    }
    .tab-btn.active {
      background: #FFFFFF;
      color: var(--secondary);
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .action-buttons-grid {
      display: flex;
      gap: 8px;
      align-items: center;
      flex-wrap: wrap;
    }

    /* Toast Notifications */
    .toast-container {
      position: fixed;
      bottom: 24px;
      right: 24px;
      z-index: 99999;
      display: flex;
      flex-direction: column;
      gap: 10px;
      pointer-events: none;
    }
    .toast {
      background: #1E293B;
      color: #fff;
      padding: 12px 18px;
      border-radius: 8px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.25);
      font-size: 0.85rem;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 10px;
      pointer-events: auto;
      transition: opacity 0.3s ease, transform 0.3s ease;
      animation: slideInUp 0.3s ease;
    }
    .toast.toast-success { background: #15803D !important; }
    .toast.toast-info { background: #1D4ED8 !important; }
    .toast.toast-warning { background: #B45309 !important; }
    @keyframes slideInUp {
      from { opacity: 0; transform: translateY(16px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* Container & Layout */
    .container {
      max-width: 1400px;
      margin: 16px auto;
      padding: 0 16px;
      transition: all 0.3s ease;
    }

    /* Workspace Modes */
    .workspace-split {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      align-items: start;
    }
    .workspace-single {
      display: block;
    }

    /* Grids */
    .grid { display: grid; grid-template-columns: 1fr; gap: 16px; }
    .grid-2 { grid-template-columns: 1fr; }
    .grid-3 { grid-template-columns: 1fr; }
    .grid-4 { grid-template-columns: 1fr; }

    /* Responsive Breakpoints */
    @media (min-width: 640px) {
      .grid-2 { grid-template-columns: repeat(2, 1fr); }
      .grid-3 { grid-template-columns: repeat(2, 1fr); }
      .grid-4 { grid-template-columns: repeat(2, 1fr); }
    }
    @media (min-width: 960px) {
      .grid-3 { grid-template-columns: repeat(3, 1fr); }
      .grid-4 { grid-template-columns: repeat(4, 1fr); }
    }
    @media (max-width: 1100px) {
      .split-btn { display: none !important; }
      .workspace-split { grid-template-columns: 1fr; }
    }

    /* Cards */
    .card {
      background: var(--card-bg);
      border-radius: var(--radius);
      border: 1px solid var(--border);
      box-shadow: 0 1px 4px rgba(0,0,0,0.04);
      margin-bottom: 16px;
      overflow: hidden;
    }
    .card-header {
      padding: 10px 16px;
      background: #F8FAFC;
      border-bottom: 1px solid var(--border);
      font-weight: 600;
      font-size: 0.9rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      color: var(--secondary);
    }
    .card-body { padding: 16px; }

    /* Form Fields */
    .form-group { margin-bottom: 12px; }
    .form-label { display: block; font-size: 0.78rem; font-weight: 600; margin-bottom: 4px; color: var(--text-muted); }
    .form-control {
      width: 100%;
      padding: 8px 10px;
      border: 1px solid var(--border);
      border-radius: 6px;
      font-size: 0.85rem;
      outline: none;
      transition: border-color 0.2s, box-shadow 0.2s;
      background: #fff;
    }
    .form-control:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 2px rgba(190, 146, 35, 0.2);
    }

    /* Table Container with Touch Scroll */
    .table-container {
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
      border-radius: 0 0 var(--radius) var(--radius);
    }
    table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.82rem; }
    th {
      background: #F8FAFC;
      padding: 9px 10px;
      font-weight: 600;
      color: var(--secondary);
      border-bottom: 2px solid var(--border);
      white-space: nowrap;
    }
    td { padding: 7px 10px; border-bottom: 1px solid var(--border); vertical-align: middle; }
    tr:hover td { background-color: #F8FAFC; }

    /* Calculation Totals Summary Box */
    .calc-box {
      background: #FFFBEB;
      border: 1px solid #FDE68A;
      border-radius: var(--radius);
      padding: 14px 16px;
    }
    .calc-row {
      display: flex;
      justify-content: space-between;
      padding: 4px 0;
      font-size: 0.84rem;
      font-variant-numeric: tabular-nums;
    }
    .calc-row.total {
      border-top: 2px solid var(--primary);
      margin-top: 8px;
      padding-top: 8px;
      font-size: 1.1rem;
      font-weight: 700;
      color: var(--secondary);
    }
    .badge { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 0.72rem; font-weight: 600; }
    .badge-info { background: #E0E7FF; color: #3730A3; }
    .badge-success { background: #DCFCE7; color: #166534; }

    /* On-Screen Document Preview Sheet Container */
    .document-preview-pane {
      background: #475569;
      padding: 20px 12px;
      border-radius: var(--radius);
      display: flex;
      flex-direction: column;
      align-items: center;
      overflow-x: auto;
      min-height: 500px;
    }
    .doc-scale-wrapper {
      transform-origin: top center;
      transition: transform 0.2s ease;
      width: 100%;
      display: flex;
      justify-content: center;
    }
    .invoice-sheet {
      background: #FFFFFF;
      width: 100%;
      max-width: 820px;
      min-height: 1050px;
      padding: 40px 40px 30px;
      box-shadow: 0 8px 30px rgba(0,0,0,0.3);
      color: #000;
      border-radius: 2px;
      margin: 0 auto;
    }

    /* Print View (Exact Maruti Letterhead Style) */
    @media print {
      @page {
        size: A4 portrait;
        margin: 8mm 10mm;
      }
      *, *::before, *::after {
        box-shadow: none !important;
        text-shadow: none !important;
      }
      html, body {
        background: #ffffff !important;
        color: #000000 !important;
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
        height: auto !important;
        min-height: auto !important;
        overflow: visible !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
      }
      /* Hide navigation header, switcher bar, controls, and form editor */
      .no-print,
      header,
      .navbar,
      .view-switcher-bar,
      #editorPane,
      .doc-controls,
      .modal,
      .split-btn {
        display: none !important;
      }
      .print-only {
        display: block !important;
      }
      /* Force Main & Preview containers to display cleanly in print */
      #mainContainer,
      #workspaceLayout {
        display: block !important;
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        border: none !important;
      }
      #previewPane {
        display: block !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        background: transparent !important;
        border: none !important;
      }
      .document-preview-pane {
        background: transparent !important;
        padding: 0 !important;
        margin: 0 !important;
        display: block !important;
        min-height: auto !important;
        overflow: visible !important;
      }
      .doc-scale-wrapper {
        transform: none !important;
        height: auto !important;
        display: block !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: visible !important;
      }
      .invoice-sheet {
        display: block !important;
        width: 100% !important;
        max-width: 100% !important;
        min-height: auto !important;
        box-shadow: none !important;
        border: none !important;
        padding: 0 !important;
        margin: 0 !important;
        transform: none !important;
        page-break-after: avoid !important;
        page-break-inside: avoid !important;
      }
      table {
        border-collapse: collapse !important;
        width: 100% !important;
        page-break-inside: auto !important;
      }
      tr {
        page-break-inside: avoid !important;
      }
      th {
        background-color: #3F527D !important;
        color: #ffffff !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        border: 1px solid #3F527D !important;
      }
      td {
        border: 1px solid #CBD5E1 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
      }
      .print-header {
        border-bottom: 2px solid #BE9223 !important;
        padding-bottom: 10px !important;
        margin-bottom: 12px !important;
      }
    }

    /* Mobile Enhancements (< 640px) */
    @media (max-width: 640px) {
      .navbar {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
        padding: 10px 14px;
      }
      .nav-actions { width: 100%; justify-content: space-between; }
      .nav-actions .btn { font-size: 0.75rem; padding: 6px 10px; flex: 1 1 auto; }
      .view-switcher-bar { flex-direction: column; align-items: stretch; padding: 8px 12px; }
      .tab-group { width: 100%; display: flex; }
      .tab-btn { flex: 1; text-align: center; }
      .action-buttons-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        width: 100%;
      }
      .action-buttons-grid .btn { width: 100%; justify-content: center; }
      .invoice-sheet { padding: 18px 12px; }
      .doc-scale-wrapper { width: 100%; overflow-x: auto; }
    }

    /* Modals */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.55);
      align-items: center;
      justify-content: center;
      padding: 12px;
    }
    .modal.active { display: flex; }
    .modal-content {
      background: #fff;
      width: 100%;
      max-width: 640px;
      border-radius: var(--radius);
      overflow: hidden;
      box-shadow: 0 12px 30px rgba(0,0,0,0.25);
    }
    .modal-header {
      padding: 12px 18px;
      background: var(--secondary);
      color: #fff;
      font-weight: 600;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .modal-body { padding: 16px 18px; max-height: 80vh; overflow-y: auto; }
    .modal-footer {
      padding: 10px 18px;
      background: #F8FAFC;
      border-top: 1px solid var(--border);
      display: flex;
      justify-content: flex-end;
      gap: 10px;
    }
  </style>
</head>
<body>

  <!-- Top Navigation Header -->
  <header class="navbar no-print">
    <div class="nav-brand">
      <img src="data:image/png;base64,{$logoBase64}" alt="Maruti Logo">
      <div>
        <div class="nav-title">MARUTI SALES & SERVICES</div>
        <div class="nav-subtitle">(A Complete IT Solution) &bull; Standalone Edition</div>
      </div>
    </div>
    <div class="nav-actions">
      <button class="btn btn-outline" style="color:#fff; border-color:rgba(255,255,255,0.4);" onclick="openHistoryModal()">📂 Saved (<span id="savedCount">0</span>)</button>
      <button class="btn btn-outline" style="color:#fff; border-color:rgba(255,255,255,0.4);" onclick="openSettingsModal()">⚙️ Profile</button>
      <button class="btn btn-primary" onclick="newQuotation()">+ New Quote</button>
    </div>
  </header>

  <!-- View Mode Switcher Bar -->
  <div class="view-switcher-bar no-print">
    <div class="tab-group">
      <button class="tab-btn active" id="tabEditor" onclick="switchView('editor')">📝 Form Editor</button>
      <button class="tab-btn" id="tabPreview" onclick="switchView('preview')">👁️ Live Quotation Document</button>
      <button class="tab-btn split-btn" id="tabSplit" onclick="switchView('split')">⚡ Split View (Side-by-Side)</button>
    </div>

    <!-- Quick Action Bar -->
    <div class="action-buttons-grid">
      <button class="btn btn-secondary btn-sm" onclick="printQuotation()">🖨️ Print / PDF</button>
      <button class="btn btn-outline btn-sm" onclick="downloadQuotationPdf()">📥 Download PDF</button>
      <button class="btn btn-success btn-sm" id="btnWhatsApp" onclick="shareWhatsAppWithPdf()">💬 WhatsApp</button>
      <button class="btn btn-outline btn-sm" onclick="copyQuotationText()">📋 Copy Text</button>
      <button class="btn btn-primary btn-sm" onclick="saveQuotationToStorage()">💾 Save</button>
    </div>
  </div>

  <!-- Main Container -->
  <main class="container" id="mainContainer">
    <div class="workspace-single" id="workspaceLayout">

      <!-- LEFT COLUMN: Form Editor -->
      <section id="editorPane">
        
        <!-- Metadata & Client Grid -->
        <div class="grid grid-2">
          <!-- Quote Metadata Card -->
          <div class="card">
            <div class="card-header">
              <span>📄 Quotation Reference</span>
              <span class="badge badge-info" id="statusBadge">Draft</span>
            </div>
            <div class="card-body">
              <div class="grid grid-2">
                <div class="form-group">
                  <label class="form-label">Quotation Number</label>
                  <input type="text" id="quoteNumber" class="form-control" value="MSS/2026-27/0001" oninput="recalculate()">
                </div>
                <div class="form-group">
                  <label class="form-label">Quotation Date</label>
                  <input type="date" id="quoteDate" class="form-control" onchange="recalculate()">
                </div>
              </div>
              <div class="grid grid-2">
                <div class="form-group">
                  <label class="form-label">Valid Until</label>
                  <input type="date" id="validUntil" class="form-control" onchange="recalculate()">
                </div>
                <div class="form-group">
                  <label class="form-label">Tax Treatment</label>
                  <select id="taxType" class="form-control" onchange="recalculate()">
                    <option value="intra">Intra-State (Assam: CGST + SGST 50/50)</option>
                    <option value="inter">Inter-State (IGST 100%)</option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          <!-- Client Details Card -->
          <div class="card">
            <div class="card-header">
              <span>🏢 Client / Customer Details</span>
              <button class="btn btn-sm btn-outline" onclick="loadSampleClient()">Demo Client</button>
            </div>
            <div class="card-body">
              <div class="grid grid-2">
                <div class="form-group">
                  <label class="form-label">Customer / Firm Name *</label>
                  <input type="text" id="clientName" class="form-control" placeholder="e.g. Pragjyotish Tech Solutions" oninput="recalculate()">
                </div>
                <div class="form-group">
                  <label class="form-label">Contact Person</label>
                  <input type="text" id="contactPerson" class="form-control" placeholder="e.g. Mr. Bikram Das" oninput="recalculate()">
                </div>
              </div>
              <div class="grid grid-3">
                <div class="form-group">
                  <label class="form-label">Phone (WhatsApp)</label>
                  <input type="text" id="clientPhone" class="form-control" placeholder="+91 98640 12345" oninput="recalculate()">
                </div>
                <div class="form-group">
                  <label class="form-label">GSTIN (15 Digits)</label>
                  <input type="text" id="clientGstin" class="form-control" placeholder="18AABCP1234M1Z5" onblur="validateGstin(this.value)">
                </div>
                <div class="form-group">
                  <label class="form-label">State & Code</label>
                  <input type="text" id="clientState" class="form-control" value="Assam (18)" oninput="recalculate()">
                </div>
              </div>
              <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Billing Address</label>
                <input type="text" id="clientAddress" class="form-control" placeholder="e.g. G.S. Road, Christian Basti, Guwahati - 781005" oninput="recalculate()">
              </div>
            </div>
          </div>
        </div>

        <!-- Items Card -->
        <div class="card">
          <div class="card-header" style="flex-wrap: wrap; gap: 8px;">
            <span>📦 Products & Services Catalog</span>
            <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap; width:100%; max-width:480px; justify-content:flex-end;">
              <select id="catalogPicker" class="form-control" style="flex:1 1 200px; min-width:180px;" onchange="addItemFromCatalog(this.value)">
                <option value="">⚡ Quick Add Item From Catalog...</option>
                <optgroup label="Computer Hardware">
                  <option value="1">HP ProBook 440 G10 Laptop (₹ 62,500 | 18% GST)</option>
                  <option value="2">Dell OptiPlex 7010 Desktop PC (₹ 48,000 | 18% GST)</option>
                  <option value="3">HP LaserJet Pro M404dn Printer (₹ 24,500 | 18% GST)</option>
                  <option value="4">Seagate IronWolf 4TB NAS HDD (₹ 9,200 | 18% GST)</option>
                  <option value="5">Crucial 16GB DDR4 3200MHz RAM (₹ 3,400 | 18% GST)</option>
                </optgroup>
                <optgroup label="CCTV & Security">
                  <option value="6">Hikvision 2MP IP Bullet Camera (₹ 2,400 | 18% GST)</option>
                  <option value="7">Hikvision 2MP IP Dome Camera (₹ 2,200 | 18% GST)</option>
                  <option value="8">CP Plus 8-Channel 4K NVR (₹ 7,500 | 18% GST)</option>
                  <option value="9">Hikvision Cat6 UTP 305m Cable (₹ 7,800 | 18% GST)</option>
                </optgroup>
                <optgroup label="Networking">
                  <option value="10">Cisco Catalyst 24-Port Switch (₹ 18,500 | 18% GST)</option>
                  <option value="11">TP-Link Omada EAP610 Wi-Fi 6 AP (₹ 7,200 | 18% GST)</option>
                  <option value="12">D-Link 9U Wall Mount Server Rack (₹ 4,800 | 18% GST)</option>
                </optgroup>
                <optgroup label="Software & Antivirus">
                  <option value="13">Microsoft Windows 11 Pro 64-bit (₹ 12,500 | 18% GST)</option>
                  <option value="14">Quick Heal Total Security 3-User (₹ 2,100 | 18% GST)</option>
                </optgroup>
                <optgroup label="Services & AMC">
                  <option value="15">Annual Maintenance Contract (AMC) (₹ 18,000 | 18% GST)</option>
                  <option value="16">CCTV Installation & Testing Service (₹ 3,500 | 18% GST)</option>
                  <option value="17">Structured Cat6 LAN Cabling Point (₹ 450 | 18% GST)</option>
                </optgroup>
              </select>
              <button class="btn btn-sm btn-primary" onclick="addNewBlankRow()">+ Blank Line</button>
            </div>
          </div>
          <div class="card-body" style="padding:0;">
            <div class="table-container">
              <table id="itemsTable">
                <thead>
                  <tr>
                    <th style="width:36px; text-align:center;">#</th>
                    <th style="min-width:220px;">Item Description</th>
                    <th style="width:90px; text-align:center;">HSN/SAC</th>
                    <th style="width:70px; text-align:center;">Qty</th>
                    <th style="width:70px; text-align:center;">Unit</th>
                    <th style="width:115px; text-align:right;">Rate (₹)</th>
                    <th style="width:70px; text-align:center;">Disc %</th>
                    <th style="width:75px; text-align:center;">GST %</th>
                    <th style="width:125px; text-align:right;">Total (₹)</th>
                    <th style="width:40px; text-align:center;">Del</th>
                  </tr>
                </thead>
                <tbody id="itemsBody">
                  <!-- Dynamic Rows -->
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Terms & Totals Grid -->
        <div class="grid grid-2">
          <!-- Terms -->
          <div class="card">
            <div class="card-header">Terms & Conditions</div>
            <div class="card-body">
              <textarea id="termsConditions" class="form-control" rows="5" oninput="recalculate()">1. Quotation validity: 15 days from the date of issue.
2. Payment terms: 50% advance along with confirmed work order, balance upon delivery and installation.
3. Warranty: Standard manufacturer warranty applies to all hardware products.
4. Taxes: GST is extra as applicable at prevailing government rates.
5. Delivery: Within 3 to 7 working days upon receipt of confirmed order.
6. Jurisdiction: Subject to Guwahati jurisdiction.</textarea>
            </div>
          </div>

          <!-- Real-time Tax Totals Box -->
          <div class="calc-box">
            <div style="font-weight:700; color:var(--secondary); font-size:0.95rem; margin-bottom:10px; border-bottom:1px solid #FDE68A; padding-bottom:6px;">
              Live Tax Breakdown & Totals
            </div>
            <div class="calc-row"><span>Gross Subtotal:</span><span id="subtotalVal">₹ 0.00</span></div>
            <div class="calc-row"><span>Total Discount:</span><span id="discountVal" style="color:var(--danger);">- ₹ 0.00</span></div>
            <div class="calc-row" style="font-weight:600;"><span>Taxable Value:</span><span id="taxableVal">₹ 0.00</span></div>
            
            <div id="intraTaxRows">
              <div class="calc-row"><span>CGST (Central Tax):</span><span id="cgstVal">₹ 0.00</span></div>
              <div class="calc-row"><span>SGST (Assam State Tax):</span><span id="sgstVal">₹ 0.00</span></div>
            </div>
            <div id="interTaxRow" style="display:none;">
              <div class="calc-row"><span>IGST (Integrated Tax):</span><span id="igstVal">₹ 0.00</span></div>
            </div>

            <div class="calc-row"><span>Round Off:</span><span id="roundOffVal">₹ 0.00</span></div>
            <div class="calc-row total"><span>Grand Total:</span><span id="grandTotalVal">₹ 0.00</span></div>
            <div style="font-size:0.75rem; color:var(--text-muted); margin-top:8px; font-style:italic;" id="amountWordsVal">
              Amount in words: Rupees Zero Only
            </div>
          </div>
        </div>

      </section>

      <!-- RIGHT COLUMN: Responsive Document Preview Pane -->
      <section id="previewPane" style="display:none;">
        <div class="document-preview-pane">
          <!-- Scale Controls Bar -->
          <div class="doc-controls no-print" style="margin-bottom:12px; display:flex; gap:8px; align-items:center; background:rgba(0,0,0,0.3); padding:6px 12px; border-radius:6px; color:#fff; font-size:0.75rem;">
            <span>Scale View:</span>
            <button class="btn btn-sm btn-outline" style="color:#fff; border-color:rgba(255,255,255,0.4);" onclick="setDocScale(0.7)">70%</button>
            <button class="btn btn-sm btn-outline" style="color:#fff; border-color:rgba(255,255,255,0.4);" onclick="setDocScale(0.85)">85%</button>
            <button class="btn btn-sm btn-outline" style="color:#fff; border-color:rgba(255,255,255,0.4);" onclick="setDocScale(1.0)">100%</button>
            <button class="btn btn-sm btn-primary" onclick="fitDocToWidth()">↔️ Fit Screen</button>
            <button class="btn btn-sm btn-secondary" onclick="printQuotation()">🖨️ Print Now</button>
          </div>

          <!-- Document Sheet (Printable Layout) -->
          <div class="doc-scale-wrapper" id="docScaleWrapper">
            <div class="invoice-sheet" id="invoiceSheet">
              
              <!-- Letterhead Header -->
              <div class="print-header" style="display:flex; justify-content:space-between; align-items:flex-start; border-bottom: 2px solid #BE9223; padding-bottom: 12px; margin-bottom: 14px; gap: 12px;">
                <div style="display:flex; gap:14px; align-items:center;">
                  <img src="data:image/png;base64,{$logoBase64}" style="max-width:120px; height:auto;" alt="Logo">
                  <div>
                    <h1 style="font-size:1.35rem; color:#3F527D; font-weight:800; margin-bottom:2px;" id="p_firmName">MARUTI SALES & SERVICES</h1>
                    <div style="font-size:0.8rem; font-weight:600; color:#BE9223; margin-bottom:4px;" id="p_tagline">(A Complete IT Solution)</div>
                    <div style="font-size:0.72rem; color:#333; line-height:1.3;" id="p_address">
                      Mahabir Market, Ground Floor, opp. Hotel Siroy Lily, Solapara Road, Paltan Bazar, Guwahati - 781008, Assam
                    </div>
                    <div style="font-size:0.72rem; color:#333;">
                      Phone: <span id="p_phone">+91 70022 53021</span> | Email: <span id="p_email">smarutisales@gmail.com</span> | GSTIN: <strong id="p_gstin">18ABGFM5224K1Z2</strong>
                    </div>
                  </div>
                </div>
                <div style="text-align:right; min-width:160px;">
                  <h2 style="font-size:1.4rem; color:#3F527D; font-weight:800; letter-spacing:1px; margin-bottom:4px;">QUOTATION</h2>
                  <div style="font-size:0.85rem; font-weight:700;" id="p_quoteNo">MSS/2026-27/0001</div>
                  <div style="font-size:0.72rem;">Date: <span id="p_quoteDate"></span></div>
                  <div style="font-size:0.72rem;">Valid Until: <span id="p_validUntil"></span></div>
                </div>
              </div>

              <!-- Client Box -->
              <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:12px; font-size:0.78rem; background:#F8FAFC; padding:10px 12px; border:1px solid #CBD5E1; border-radius:4px;">
                <div>
                  <div style="font-weight:700; color:#3F527D; text-transform:uppercase; margin-bottom:4px; font-size:0.72rem;">Quotation For (Client):</div>
                  <div style="font-size:0.92rem; font-weight:700;" id="p_clientName">M/s Customer Name</div>
                  <div id="p_contactPerson">Attn: Contact Person</div>
                  <div id="p_clientAddress">Billing Address</div>
                </div>
                <div style="text-align:right;">
                  <div>Client GSTIN: <strong id="p_clientGstin">Unregistered</strong></div>
                  <div>State & Code: <span id="p_clientState">Assam (18)</span></div>
                  <div>Phone: <span id="p_clientPhone">-</span></div>
                </div>
              </div>

              <!-- Items Table -->
              <table style="width:100%; margin-bottom:12px; font-size:0.74rem; border-collapse:collapse;">
                <thead>
                  <tr>
                    <th style="width:28px; text-align:center;">#</th>
                    <th style="text-align:left;">Description of Goods / Services</th>
                    <th style="width:70px; text-align:center; white-space:nowrap;">HSN/SAC</th>
                    <th style="width:38px; text-align:center; white-space:nowrap;">Qty</th>
                    <th style="width:38px; text-align:center; white-space:nowrap;">Unit</th>
                    <th style="width:95px; text-align:right; white-space:nowrap;">Rate (₹)</th>
                    <th style="width:45px; text-align:center; white-space:nowrap;">Disc %</th>
                    <th style="width:45px; text-align:center; white-space:nowrap;">GST %</th>
                    <th style="width:105px; text-align:right; white-space:nowrap;">Total (₹)</th>
                  </tr>
                </thead>
                <tbody id="p_itemsBody">
                  <!-- Rendered -->
                </tbody>
              </table>

              <!-- Totals Breakdown & Bank Details -->
              <div style="display:grid; grid-template-columns:1.1fr 0.9fr; gap:16px; font-size:0.74rem; margin-bottom:14px;">
                <div>
                  <div style="margin-bottom:8px;">
                    <strong>Amount in Words:</strong><br>
                    <span id="p_amountWords" style="font-weight:700; color:#1E293B; font-style:italic;">Rupees Zero Only</span>
                  </div>
                  <div style="border:1px solid #CBD5E1; padding:8px 10px; border-radius:4px; background:#F8FAFC; margin-bottom:8px;">
                    <strong style="color:#3F527D;">Bank Remittance Details:</strong><br>
                    Bank: <span id="p_bankName">State Bank of India</span> | Branch: <span id="p_bankBranch">G.S. Road, Guwahati</span><br>
                    A/C Name: <span id="p_bankAcName">MARUTI SALES & SERVICES</span><br>
                    A/C No: <strong id="p_bankAcNo">38472910482</strong> | IFSC: <strong id="p_bankIfsc">SBIN0003295</strong>
                  </div>
                  <div>
                    <strong style="color:#3F527D;">Terms & Conditions:</strong>
                    <pre id="p_terms" style="font-family:inherit; white-space:pre-wrap; font-size:0.68rem; color:#475569; margin-top:2px;"></pre>
                  </div>
                </div>
                <div>
                  <table style="width:100%; font-size:0.78rem; border-collapse:collapse;">
                    <tr>
                      <td style="padding:4px 6px; white-space:nowrap;">Taxable Value:</td>
                      <td style="text-align:right; font-weight:600; white-space:nowrap; padding:4px 6px; font-variant-numeric:tabular-nums;" id="p_taxable">₹ 0.00</td>
                    </tr>
                    <tr id="p_cgstRow">
                      <td style="padding:4px 6px; white-space:nowrap;">CGST (Central Tax):</td>
                      <td style="text-align:right; white-space:nowrap; padding:4px 6px; font-variant-numeric:tabular-nums;" id="p_cgst">₹ 0.00</td>
                    </tr>
                    <tr id="p_sgstRow">
                      <td style="padding:4px 6px; white-space:nowrap;">SGST (Assam State Tax):</td>
                      <td style="text-align:right; white-space:nowrap; padding:4px 6px; font-variant-numeric:tabular-nums;" id="p_sgst">₹ 0.00</td>
                    </tr>
                    <tr id="p_igstRow" style="display:none;">
                      <td style="padding:4px 6px; white-space:nowrap;">IGST (Integrated Tax):</td>
                      <td style="text-align:right; white-space:nowrap; padding:4px 6px; font-variant-numeric:tabular-nums;" id="p_igst">₹ 0.00</td>
                    </tr>
                    <tr>
                      <td style="padding:4px 6px; white-space:nowrap;">Round Off:</td>
                      <td style="text-align:right; white-space:nowrap; padding:4px 6px; font-variant-numeric:tabular-nums;" id="p_roundOff">₹ 0.00</td>
                    </tr>
                    <tr style="background:#F1F5F9; font-size:0.88rem; font-weight:800; color:#3F527D;">
                      <td style="padding:6px 6px; white-space:nowrap;">Grand Total (₹):</td>
                      <td style="text-align:right; white-space:nowrap; padding:6px 6px; font-variant-numeric:tabular-nums; font-size:0.92rem;" id="p_grandTotal">₹ 0.00</td>
                    </tr>
                  </table>
                  
                  <!-- Authorized Signature Block -->
                  <div style="margin-top:20px; text-align:right; font-size:0.75rem;">
                    <p style="font-size:0.8rem; font-weight:700; color:#3F527D;">For <strong>MARUTI SALES & SERVICES</strong></p>
                    <div style="margin:4px 0 2px 0;">
                      <img src="data:image/png;base64,{$sigBase64}" style="max-height:55px; max-width:130px; object-fit:contain;" alt="Authorized Stamp & Signature">
                    </div>
                    <p style="font-weight:700; border-top:1px solid #374151; display:inline-block; padding-top:2px; min-width:140px;">Authorized Signatory</p>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>
      </section>

    </div>
  </main>

  <!-- Firm Settings Modal -->
  <div class="modal" id="settingsModal">
    <div class="modal-content">
      <div class="modal-header">
        <span>⚙️ Firm Letterhead Settings</span>
        <span style="cursor:pointer;" onclick="closeSettingsModal()">&times;</span>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Firm Name</label>
          <input type="text" id="s_firmName" class="form-control" value="MARUTI SALES & SERVICES">
        </div>
        <div class="form-group">
          <label class="form-label">Tagline</label>
          <input type="text" id="s_tagline" class="form-control" value="(A Complete IT Solution)">
        </div>
        <div class="form-group">
          <label class="form-label">Address</label>
          <input type="text" id="s_address" class="form-control" value="Mahabir Market, Ground Floor, opp. Hotel Siroy Lily, Solapara Road, Paltan Bazar, Guwahati - 781008, Assam">
        </div>
        <div class="grid grid-3">
          <div class="form-group"><label class="form-label">Phone</label><input type="text" id="s_phone" class="form-control" value="+91 70022 53021"></div>
          <div class="form-group"><label class="form-label">Email</label><input type="text" id="s_email" class="form-control" value="smarutisales@gmail.com"></div>
          <div class="form-group"><label class="form-label">GSTIN</label><input type="text" id="s_gstin" class="form-control" value="18ABGFM5224K1Z2"></div>
        </div>
        <h4 style="margin:12px 0 8px; font-size:0.85rem; color:var(--secondary);">Bank Remittance Details</h4>
        <div class="grid grid-2">
          <div class="form-group"><label class="form-label">Bank Name</label><input type="text" id="s_bankName" class="form-control" value="State Bank of India"></div>
          <div class="form-group"><label class="form-label">Account No</label><input type="text" id="s_bankAcNo" class="form-control" value="38472910482"></div>
          <div class="form-group"><label class="form-label">IFSC Code</label><input type="text" id="s_bankIfsc" class="form-control" value="SBIN0003295"></div>
          <div class="form-group"><label class="form-label">Branch</label><input type="text" id="s_bankBranch" class="form-control" value="G.S. Road, Guwahati"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline" onclick="closeSettingsModal()">Cancel</button>
        <button class="btn btn-primary" onclick="saveSettings()">Save Settings</button>
      </div>
    </div>
  </div>

  <!-- Saved Quotations Modal -->
  <div class="modal" id="historyModal">
    <div class="modal-content" style="max-width:750px;">
      <div class="modal-header">
        <span>📂 Saved Local Quotations</span>
        <span style="cursor:pointer;" onclick="closeHistoryModal()">&times;</span>
      </div>
      <div class="modal-body" style="padding:0;">
        <table style="width:100%;">
          <thead>
            <tr>
              <th>Quote No</th>
              <th>Date</th>
              <th>Customer</th>
              <th style="text-align:right;">Amount (₹)</th>
              <th style="text-align:center;">Action</th>
            </tr>
          </thead>
          <tbody id="historyTableBody">
            <!-- Rendered by JS -->
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline" onclick="closeHistoryModal()">Close</button>
      </div>
    </div>
  </div>

  <!-- Save Success Dialog Modal -->
  <div class="modal" id="saveSuccessModal">
    <div class="modal-content" style="max-width:500px;">
      <div class="modal-header" style="background:#16A34A; color:#fff;">
        <span style="font-weight:700;">✅ Quotation Saved!</span>
        <span style="cursor:pointer; font-size:1.4rem;" onclick="closeSaveSuccessModal()">&times;</span>
      </div>
      <div class="modal-body" style="padding:22px 20px; text-align:center;">
        <div style="font-size:3rem; margin-bottom:8px;">💾</div>
        <h3 style="font-size:1.2rem; color:#1E293B; margin-bottom:6px;">Quotation <span id="saveSuccessQuoteNo">MSS/2026-27/0001</span> Saved!</h3>
        <p style="font-size:0.85rem; color:#64748B; margin-bottom:20px;">
          Saved to your browser's local database. Would you like to send the official quotation PDF to your customer via WhatsApp now?
        </p>

        <div style="display:flex; flex-direction:column; gap:10px;">
          <button class="btn btn-success" style="padding:12px; font-size:0.95rem; justify-content:center;" onclick="closeSaveSuccessModal(); shareWhatsAppWithPdf();">
            💬 Send PDF Directly via WhatsApp
          </button>
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
            <button class="btn btn-outline" style="justify-content:center;" onclick="closeSaveSuccessModal(); downloadQuotationPdf();">
              📥 Download PDF
            </button>
            <button class="btn btn-secondary" style="justify-content:center;" onclick="closeSaveSuccessModal(); printQuotation();">
              🖨️ Print / PDF
            </button>
          </div>
        </div>
      </div>
      <div class="modal-footer" style="justify-content:center;">
        <button class="btn btn-outline" onclick="closeSaveSuccessModal()">Keep Editing</button>
      </div>
    </div>
  </div>

  <!-- WhatsApp Desktop Guide Modal -->
  <div class="modal" id="whatsappGuideModal">
    <div class="modal-content" style="max-width:540px;">
      <div class="modal-header" style="background:#25D366; color:#fff;">
        <span style="font-weight:700;">💬 WhatsApp Quotation PDF Ready</span>
        <span style="cursor:pointer; font-size:1.4rem;" onclick="closeWhatsAppGuideModal()">&times;</span>
      </div>
      <div class="modal-body" style="padding:20px;">
        <div style="text-align:center; margin-bottom:16px;">
          <div style="font-size:2.8rem; margin-bottom:6px;">📄 ➡️ 💬</div>
          <h3 style="font-size:1.15rem; color:#1E293B; margin-bottom:4px;">PDF Downloaded &amp; WhatsApp Opened!</h3>
          <p style="font-size:0.82rem; color:#64748B;">
            The official quotation PDF has been downloaded to your computer and WhatsApp Web is now open.
          </p>
        </div>

        <div style="background:#F0FDF4; border:1px solid #BBF7D0; border-radius:8px; padding:14px; margin-bottom:16px; font-size:0.84rem; color:#166534;">
          <div style="font-weight:700; margin-bottom:8px;">🚀 Quick Attachment Instructions:</div>
          <div style="display:flex; gap:10px; align-items:flex-start; margin-bottom:8px;">
            <span style="background:#22C55E; color:#fff; border-radius:50%; width:22px; height:22px; display:inline-flex; align-items:center; justify-content:center; font-size:0.75rem; font-weight:700; flex-shrink:0;">1</span>
            <span>Switch to the opened <strong>WhatsApp Web</strong> tab. Your client's chat and summary text are already waiting.</span>
          </div>
          <div style="display:flex; gap:10px; align-items:flex-start;">
            <span style="background:#22C55E; color:#fff; border-radius:50%; width:22px; height:22px; display:inline-flex; align-items:center; justify-content:center; font-size:0.75rem; font-weight:700; flex-shrink:0;">2</span>
            <span>Click the <strong>📎 (Paperclip)</strong> icon &rarr; select <strong>Document</strong> &rarr; choose <strong id="waGuideFileName">Quotation.pdf</strong> (or simply <strong>drag &amp; drop</strong> the downloaded file from your browser's download bar into the chat)!</span>
          </div>
        </div>

        <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:6px; padding:10px 14px; font-size:0.8rem; display:flex; justify-content:space-between; align-items:center;">
          <div>
            <div style="font-weight:600; color:#334155;">File: <span id="waModalFileName">Quotation.pdf</span></div>
            <div style="color:#64748B; font-size:0.72rem;">Saved to your default Downloads folder</div>
          </div>
          <button class="btn btn-sm btn-outline" onclick="redownloadCurrentPdf()">📥 Re-Download</button>
        </div>
      </div>
      <div class="modal-footer" style="display:flex; justify-content:space-between; align-items:center;">
        <button class="btn btn-outline" onclick="reopenWhatsAppTab()">💬 Open WhatsApp Web Again</button>
        <button class="btn btn-success" onclick="closeWhatsAppGuideModal()">✅ Done</button>
      </div>
    </div>
  </div>

  <script>
    // Product Catalog Database
    const CATALOG = {
      1: { name: "HP ProBook 440 G10 Laptop (i5 13th Gen, 16GB, 512GB SSD, Win 11)", hsn: "84713010", unit: "Pcs", rate: 62500, gst: 18 },
      2: { name: "Dell OptiPlex 7010 Desktop PC (Core i5, 8GB RAM, 512GB SSD)", hsn: "84715000", unit: "Set", rate: 48000, gst: 18 },
      3: { name: "HP LaserJet Pro M404dn Monochrome Duplex Network Printer", hsn: "84433210", unit: "Pcs", rate: 24500, gst: 18 },
      4: { name: "Seagate IronWolf 4TB NAS Surveillance Internal SATA HDD", hsn: "84717020", unit: "Pcs", rate: 9200, gst: 18 },
      5: { name: "Crucial 16GB DDR4 3200MHz Desktop RAM Module", hsn: "84733030", unit: "Pcs", rate: 3400, gst: 18 },
      6: { name: "Hikvision 2MP IP Bullet Outdoor Night Vision Camera (DS-2CD1023G0E-I)", hsn: "85258900", unit: "Pcs", rate: 2400, gst: 18 },
      7: { name: "Hikvision 2MP IP Dome Indoor Audio Security Camera", hsn: "85258900", unit: "Pcs", rate: 2200, gst: 18 },
      8: { name: "CP Plus 8-Channel 4K Ultra HD Network Video Recorder (NVR)", hsn: "85219090", unit: "Set", rate: 7500, gst: 18 },
      9: { name: "Hikvision Cat6 UTP 4-Pair Pure Copper Solid 305m Cable Roll", hsn: "85444999", unit: "Lot", rate: 7800, gst: 18 },
      10: { name: "Cisco Catalyst 24-Port Gigabit Managed Layer-2 Network Switch", hsn: "85176290", unit: "Pcs", rate: 18500, gst: 18 },
      11: { name: "TP-Link Omada EAP610 Wi-Fi 6 Dual-Band Ceiling Mount Access Point", hsn: "85176290", unit: "Pcs", rate: 7200, gst: 18 },
      12: { name: "D-Link 9U Wall Mount Server/Network Rack with PDU and Trays", hsn: "85381010", unit: "Pcs", rate: 4800, gst: 18 },
      13: { name: "Microsoft Windows 11 Professional 64-bit OEM License Key", hsn: "997331", unit: "Pcs", rate: 12500, gst: 18 },
      14: { name: "Quick Heal Total Security Antivirus 3-User 1-Year Retail Pack", hsn: "997331", unit: "Pcs", rate: 2100, gst: 18 },
      15: { name: "Annual Maintenance Contract (AMC) for Office IT Infrastructure (Comprehensive)", hsn: "998713", unit: "Year", rate: 18000, gst: 18 },
      16: { name: "CCTV Installation, Cabling, Conduit & Complete Testing Service", hsn: "998719", unit: "Set", rate: 3500, gst: 18 },
      17: { name: "Structured Cat6 LAN Cabling Point Termination with Faceplate & I/O", hsn: "998719", unit: "Lot", rate: 450, gst: 18 },
    };

    let items = [];
    let currentScale = 1.0;

    // Initialize on Load
    window.addEventListener('DOMContentLoaded', () => {
      loadSettings();
      initDates();
      updateSavedCount();
      
      // Seed default items
      addItemFromCatalog(1);
      addItemFromCatalog(6);
      addItemFromCatalog(16);

      // Handle screen resizing
      window.addEventListener('resize', autoAdjustViewOnResize);
      autoAdjustViewOnResize();
    });

    function initDates() {
      const today = new Date();
      document.getElementById('quoteDate').value = today.toISOString().split('T')[0];
      const valid = new Date();
      valid.setDate(valid.getDate() + 15);
      document.getElementById('validUntil').value = valid.toISOString().split('T')[0];
    }

    // View Switching Logic (Form, Preview, Split)
    function switchView(mode) {
      const editor = document.getElementById('editorPane');
      const preview = document.getElementById('previewPane');
      const layout = document.getElementById('workspaceLayout');

      document.getElementById('tabEditor').classList.remove('active');
      document.getElementById('tabPreview').classList.remove('active');
      if (document.getElementById('tabSplit')) document.getElementById('tabSplit').classList.remove('active');

      if (mode === 'editor') {
        layout.className = 'workspace-single';
        editor.style.display = 'block';
        preview.style.display = 'none';
        document.getElementById('tabEditor').classList.add('active');
      } else if (mode === 'preview') {
        layout.className = 'workspace-single';
        editor.style.display = 'none';
        preview.style.display = 'block';
        document.getElementById('tabPreview').classList.add('active');
        fitDocToWidth();
      } else if (mode === 'split') {
        layout.className = 'workspace-split';
        editor.style.display = 'block';
        preview.style.display = 'block';
        document.getElementById('tabSplit').classList.add('active');
        fitDocToWidth();
      }
    }

    function autoAdjustViewOnResize() {
      const width = window.innerWidth;
      const isSplitActive = document.getElementById('tabSplit')?.classList.contains('active');
      if (width < 1100 && isSplitActive) {
        switchView('editor');
      }
      if (document.getElementById('previewPane').style.display !== 'none') {
        fitDocToWidth();
      }
    }

    function setDocScale(scale) {
      currentScale = scale;
      const sheet = document.getElementById('invoiceSheet');
      const wrapper = document.getElementById('docScaleWrapper');
      
      if (scale === 1.0) {
        sheet.style.transform = 'none';
        wrapper.style.height = 'auto';
      } else {
        sheet.style.transform = `scale(\${scale})`;
        sheet.style.transformOrigin = 'top center';
        wrapper.style.height = (sheet.offsetHeight * scale) + 40 + 'px';
      }
    }

    function fitDocToWidth() {
      const previewPane = document.getElementById('previewPane');
      const availableWidth = previewPane.clientWidth - 40;
      const sheetWidth = 820;

      if (availableWidth < sheetWidth) {
        const scale = Math.max(0.4, Math.min(1.0, availableWidth / sheetWidth));
        setDocScale(scale);
      } else {
        setDocScale(1.0);
      }
    }

    function printQuotation() {
      recalculate();
      window.print();
    }

    window.addEventListener('beforeprint', () => {
      recalculate();
      const sheet = document.getElementById('invoiceSheet');
      if (sheet) {
        sheet.dataset.prevTransform = sheet.style.transform || '';
        sheet.style.transform = 'none';
      }
    });

    window.addEventListener('afterprint', () => {
      const sheet = document.getElementById('invoiceSheet');
      if (sheet && sheet.dataset.prevTransform !== undefined) {
        sheet.style.transform = sheet.dataset.prevTransform;
      }
      if (document.getElementById('previewPane')?.style.display !== 'none') {
        fitDocToWidth();
      }
    });

    function addItemFromCatalog(id) {
      if (!id || !CATALOG[id]) return;
      const cat = CATALOG[id];
      items.push({
        name: cat.name,
        hsn: cat.hsn,
        qty: 1,
        unit: cat.unit,
        rate: cat.rate,
        discount: 0,
        gst: cat.gst
      });
      document.getElementById('catalogPicker').value = "";
      renderTable();
    }

    function addNewBlankRow() {
      items.push({
        name: "",
        hsn: "",
        qty: 1,
        unit: "Pcs",
        rate: 0,
        discount: 0,
        gst: 18
      });
      renderTable();
    }

    function removeItem(index) {
      items.splice(index, 1);
      renderTable();
    }

    function updateItem(index, field, value) {
      if (['qty', 'rate', 'discount', 'gst'].includes(field)) {
        items[index][field] = parseFloat(value) || 0;
      } else {
        items[index][field] = value;
      }
      recalculate();
    }

    function renderTable() {
      const tbody = document.getElementById('itemsBody');
      tbody.innerHTML = '';

      items.forEach((item, i) => {
        const taxable = (item.qty * item.rate) - ((item.qty * item.rate) * (item.discount / 100));
        const total = taxable + (taxable * (item.gst / 100));

        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td style="text-align:center;">\${i + 1}</td>
          <td><input type="text" class="form-control" value="\${escapeHtml(item.name)}" placeholder="Item description" onchange="updateItem(\${i}, 'name', this.value)"></td>
          <td><input type="text" class="form-control" style="text-align:center;" value="\${item.hsn}" placeholder="HSN/SAC" onchange="updateItem(\${i}, 'hsn', this.value)"></td>
          <td><input type="number" class="form-control" style="text-align:center;" value="\${item.qty}" min="1" step="any" oninput="updateItem(\${i}, 'qty', this.value)"></td>
          <td><input type="text" class="form-control" style="text-align:center;" value="\${item.unit}" onchange="updateItem(\${i}, 'unit', this.value)"></td>
          <td><input type="number" class="form-control" style="text-align:right;" value="\${item.rate}" min="0" step="any" oninput="updateItem(\${i}, 'rate', this.value)"></td>
          <td><input type="number" class="form-control" style="text-align:center;" value="\${item.discount}" min="0" max="100" oninput="updateItem(\${i}, 'discount', this.value)"></td>
          <td>
            <select class="form-control" style="text-align:center;" onchange="updateItem(\${i}, 'gst', this.value)">
              <option value="0" \${item.gst == 0 ? 'selected':''}>0%</option>
              <option value="5" \${item.gst == 5 ? 'selected':''}>5%</option>
              <option value="12" \${item.gst == 12 ? 'selected':''}>12%</option>
              <option value="18" \${item.gst == 18 ? 'selected':''}>18%</option>
              <option value="28" \${item.gst == 28 ? 'selected':''}>28%</option>
            </select>
          </td>
          <td style="text-align:right; font-weight:700; white-space:nowrap; padding-top:10px; font-variant-numeric:tabular-nums;">₹&nbsp;\${formatCurrency(total)}</td>
          <td style="text-align:center;"><button class="btn btn-sm btn-danger" onclick="removeItem(\${i})">&times;</button></td>
        `;
        tbody.appendChild(tr);
      });

      recalculate();
    }

    function recalculate() {
      let subtotal = 0;
      let totalDiscount = 0;
      let taxableTotal = 0;
      let cgstTotal = 0;
      let sgstTotal = 0;
      let igstTotal = 0;

      const isInterstate = document.getElementById('taxType').value === 'inter';

      items.forEach(item => {
        const lineGross = (item.qty || 0) * (item.rate || 0);
        const lineDisc = lineGross * ((item.discount || 0) / 100);
        const lineTaxable = lineGross - lineDisc;

        subtotal += lineGross;
        totalDiscount += lineDisc;
        taxableTotal += lineTaxable;

        if (isInterstate) {
          igstTotal += lineTaxable * ((item.gst || 0) / 100);
        } else {
          cgstTotal += lineTaxable * ((item.gst || 0) / 200);
          sgstTotal += lineTaxable * ((item.gst || 0) / 200);
        }
      });

      const totalTax = isInterstate ? igstTotal : (cgstTotal + sgstTotal);
      const exactGrand = taxableTotal + totalTax;
      const roundedGrand = Math.round(exactGrand);
      const roundOff = roundedGrand - exactGrand;

      // Update Form Displays
      document.getElementById('subtotalVal').innerText = "₹ " + formatCurrency(subtotal);
      document.getElementById('discountVal').innerText = "- ₹ " + formatCurrency(totalDiscount);
      document.getElementById('taxableVal').innerText = "₹ " + formatCurrency(taxableTotal);
      document.getElementById('cgstVal').innerText = "₹ " + formatCurrency(cgstTotal);
      document.getElementById('sgstVal').innerText = "₹ " + formatCurrency(sgstTotal);
      document.getElementById('igstVal').innerText = "₹ " + formatCurrency(igstTotal);
      document.getElementById('roundOffVal').innerText = (roundOff >= 0 ? "+ " : "- ") + "₹ " + formatCurrency(Math.abs(roundOff));
      document.getElementById('grandTotalVal').innerText = "₹ " + formatCurrency(roundedGrand);

      document.getElementById('intraTaxRows').style.display = isInterstate ? 'none' : 'block';
      document.getElementById('interTaxRow').style.display = isInterstate ? 'block' : 'none';

      const words = convertNumberToWords(roundedGrand);
      document.getElementById('amountWordsVal').innerText = "Amount in words: " + words;

      // Sync Live Document Sheet
      syncPrintLayout(subtotal, totalDiscount, taxableTotal, cgstTotal, sgstTotal, igstTotal, roundOff, roundedGrand, words, isInterstate);
    }

    function syncPrintLayout(subtotal, discount, taxable, cgst, sgst, igst, roundOff, grand, words, isInterstate) {
      document.getElementById('p_quoteNo').innerText = document.getElementById('quoteNumber').value;
      document.getElementById('p_quoteDate').innerText = formatDate(document.getElementById('quoteDate').value);
      document.getElementById('p_validUntil').innerText = formatDate(document.getElementById('validUntil').value);

      document.getElementById('p_clientName').innerText = document.getElementById('clientName').value || "M/s Valued Customer";
      document.getElementById('p_contactPerson').innerText = document.getElementById('contactPerson').value ? "Attn: " + document.getElementById('contactPerson').value : "";
      document.getElementById('p_clientAddress').innerText = document.getElementById('clientAddress').value || "Guwahati, Assam";
      document.getElementById('p_clientGstin').innerText = document.getElementById('clientGstin').value || "Unregistered";
      document.getElementById('p_clientState').innerText = document.getElementById('clientState').value || "Assam (18)";
      document.getElementById('p_clientPhone').innerText = document.getElementById('clientPhone').value || "-";

      document.getElementById('p_taxable').innerText = "₹ " + formatCurrency(taxable);
      document.getElementById('p_cgst').innerText = "₹ " + formatCurrency(cgst);
      document.getElementById('p_sgst').innerText = "₹ " + formatCurrency(sgst);
      document.getElementById('p_igst').innerText = "₹ " + formatCurrency(igst);
      document.getElementById('p_roundOff').innerText = "₹ " + formatCurrency(roundOff);
      document.getElementById('p_grandTotal').innerText = "₹ " + formatCurrency(grand);
      document.getElementById('p_amountWords').innerText = words;
      document.getElementById('p_terms').innerText = document.getElementById('termsConditions').value;

      document.getElementById('p_cgstRow').style.display = isInterstate ? 'none' : 'table-row';
      document.getElementById('p_sgstRow').style.display = isInterstate ? 'none' : 'table-row';
      document.getElementById('p_igstRow').style.display = isInterstate ? 'table-row' : 'none';

      const pTbody = document.getElementById('p_itemsBody');
      pTbody.innerHTML = '';
      items.forEach((item, i) => {
        const taxable = (item.qty * item.rate) - ((item.qty * item.rate) * (item.discount / 100));
        const total = taxable + (taxable * (item.gst / 100));
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td style="text-align:center;">\${i+1}</td>
          <td><strong>\${escapeHtml(item.name)}</strong></td>
          <td style="text-align:center; white-space:nowrap;">\${item.hsn || '—'}</td>
          <td style="text-align:center; white-space:nowrap;">\${item.qty}</td>
          <td style="text-align:center; white-space:nowrap;">\${item.unit}</td>
          <td style="text-align:right; white-space:nowrap; font-variant-numeric:tabular-nums;">₹&nbsp;\${formatCurrency(item.rate)}</td>
          <td style="text-align:center; white-space:nowrap;">\${item.discount > 0 ? item.discount + '%' : '—'}</td>
          <td style="text-align:center; white-space:nowrap;">\${item.gst}%</td>
          <td style="text-align:right; font-weight:700; white-space:nowrap; font-variant-numeric:tabular-nums;">₹&nbsp;\${formatCurrency(total)}</td>
        `;
        pTbody.appendChild(tr);
      });
    }

    function validateGstin(gstin) {
      gstin = (gstin || '').trim().toUpperCase();
      document.getElementById('clientGstin').value = gstin;
      if (gstin.length >= 2) {
        const code = gstin.substring(0, 2);
        if (code === '18') {
          document.getElementById('clientState').value = "Assam (18)";
          document.getElementById('taxType').value = "intra";
        } else {
          document.getElementById('clientState').value = "Other State (" + code + ")";
          document.getElementById('taxType').value = "inter";
        }
        recalculate();
      }
    }

    function loadSampleClient() {
      document.getElementById('clientName').value = "Brahmaputra Infrastructure Ltd";
      document.getElementById('contactPerson').value = "Mr. Ankur Barman";
      document.getElementById('clientPhone').value = "+91 94350 48291";
      document.getElementById('clientGstin').value = "18AAACB1234F1Z9";
      document.getElementById('clientAddress').value = "Mahapurush Srimanta Sankaradeva Path, Christian Basti, Guwahati - 781005";
      validateGstin("18AAACB1234F1Z9");
    }

    // Toast Notification Utility
    function showToast(message, type = 'info', duration = 3500) {
      let container = document.getElementById('toastContainer');
      if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container no-print';
        document.body.appendChild(container);
      }

      const toast = document.createElement('div');
      toast.className = `toast toast-\${type}`;
      toast.innerHTML = `<span>\${message}</span>`;
      container.appendChild(toast);

      setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(16px)';
        setTimeout(() => toast.remove(), 350);
      }, duration);
    }

    // PDF Generation Engine using html2pdf
    async function generateQuotationPdfBlob(filename) {
      recalculate();

      const sourceSheet = document.getElementById('invoiceSheet');
      
      // Clone element into off-screen container for 100% vector scale rendering
      const clone = sourceSheet.cloneNode(true);
      clone.id = 'tempPdfRenderSheet';
      clone.style.transform = 'none';
      clone.style.width = '794px';
      clone.style.maxWidth = '794px';
      clone.style.minHeight = 'auto';
      clone.style.boxShadow = 'none';
      clone.style.borderRadius = '0';
      clone.style.padding = '24px 28px';
      clone.style.position = 'fixed';
      clone.style.left = '-9999px';
      clone.style.top = '0';
      clone.style.background = '#ffffff';
      clone.style.zIndex = '-9999';
      document.body.appendChild(clone);

      const opt = {
        margin: [6, 8, 6, 8],
        filename: filename,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: {
          scale: 2,
          useCORS: true,
          letterRendering: true,
          backgroundColor: '#ffffff',
          windowWidth: 794
        },
        jsPDF: {
          unit: 'mm',
          format: 'a4',
          orientation: 'portrait'
        }
      };

      try {
        if (typeof html2pdf === 'undefined') {
          throw new Error('PDF Engine (html2pdf) is not loaded.');
        }
        const pdfBlob = await html2pdf().set(opt).from(clone).outputPdf('blob');
        clone.remove();
        return pdfBlob;
      } catch (err) {
        clone.remove();
        console.error("PDF generation error:", err);
        throw err;
      }
    }

    function downloadBlob(blob, filename) {
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = filename;
      document.body.appendChild(a);
      a.click();
      setTimeout(() => {
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
      }, 2500);
    }

    async function downloadQuotationPdf() {
      const quoteNo = (document.getElementById('quoteNumber').value || 'MSS-0001').replace(/[^a-zA-Z0-9_-]/g, '-');
      const filename = `Quotation_\${quoteNo}.pdf`;

      showToast("⏳ Generating Quotation PDF...", "info", 4000);
      try {
        const blob = await generateQuotationPdfBlob(filename);
        downloadBlob(blob, filename);
        showToast(`✅ Saved \${filename} to your Downloads!`, "success", 4000);
      } catch (err) {
        console.error("Direct PDF download fallback to print:", err);
        window.print();
      }
    }

    // WhatsApp Direct Sharing with PDF Engine
    window.currentQuotationPdfBlob = null;
    window.currentQuotationPdfFileName = null;
    window.currentClientPhone = '';
    window.currentWhatsAppText = '';

    async function shareWhatsAppWithPdf() {
      const quoteNo = document.getElementById('quoteNumber').value || 'MSS-0001';
      const cleanQuoteNo = quoteNo.replace(/[^a-zA-Z0-9_-]/g, '-');
      const filename = `Quotation_\${cleanQuoteNo}.pdf`;
      const phone = (document.getElementById('clientPhone').value || '').replace(/[^0-9]/g, '');
      const cleanPhone = phone.length === 10 ? '91' + phone : phone;
      const whatsappText = generateQuotationSummaryText();

      const waBtn = document.getElementById('btnWhatsApp');
      const originalText = waBtn ? waBtn.innerHTML : '💬 WhatsApp';
      if (waBtn) {
        waBtn.innerHTML = '⏳ Generating PDF...';
        waBtn.disabled = true;
      }

      showToast("⏳ Preparing Quotation PDF for WhatsApp...", "info", 3500);

      try {
        const pdfBlob = await generateQuotationPdfBlob(filename);
        const pdfFile = new File([pdfBlob], filename, { type: 'application/pdf', lastModified: Date.now() });

        window.currentQuotationPdfBlob = pdfBlob;
        window.currentQuotationPdfFileName = filename;
        window.currentClientPhone = cleanPhone;
        window.currentWhatsAppText = whatsappText;

        // 1. Mobile Web Share API Level 2 (Sends file directly into WhatsApp on Android / iOS)
        if (navigator.canShare && navigator.canShare({ files: [pdfFile] })) {
          try {
            await navigator.share({
              title: `Quotation \${quoteNo} - Maruti Sales & Services`,
              text: whatsappText,
              files: [pdfFile]
            });
            showToast("✅ Quotation PDF sent via WhatsApp!", "success");
            if (waBtn) { waBtn.innerHTML = originalText; waBtn.disabled = false; }
            return;
          } catch (shareErr) {
            if (shareErr.name === 'AbortError') {
              if (waBtn) { waBtn.innerHTML = originalText; waBtn.disabled = false; }
              return;
            }
            console.warn("navigator.share failed, continuing to download & web fallback:", shareErr);
          }
        }

        // 2. Desktop Fallback:
        // Automatically save the PDF file to Downloads
        downloadBlob(pdfBlob, filename);

        // Open WhatsApp Web with client phone and message pre-filled
        reopenWhatsAppTab();

        // Display user instruction guide modal
        openWhatsAppGuideModal(filename, cleanPhone);

      } catch (err) {
        console.error("PDF generation for WhatsApp failed, fallback to summary text:", err);
        const waUrl = cleanPhone 
          ? `https://wa.me/\${cleanPhone}?text=\${encodeURIComponent(whatsappText)}`
          : `https://wa.me/?text=\${encodeURIComponent(whatsappText)}`;
        window.open(waUrl, '_blank');
        showToast("Opened WhatsApp with summary. Use 'Print / PDF' to save sheet.", "warning", 5000);
      } finally {
        if (waBtn) {
          waBtn.innerHTML = originalText;
          waBtn.disabled = false;
        }
      }
    }

    function shareWhatsApp() {
      shareWhatsAppWithPdf();
    }

    function openWhatsAppGuideModal(filename, phone) {
      document.getElementById('waModalFileName').innerText = filename;
      document.getElementById('waGuideFileName').innerText = filename;
      document.getElementById('whatsappGuideModal').classList.add('active');
    }

    function closeWhatsAppGuideModal() {
      document.getElementById('whatsappGuideModal').classList.remove('active');
    }

    function redownloadCurrentPdf() {
      if (window.currentQuotationPdfBlob && window.currentQuotationPdfFileName) {
        downloadBlob(window.currentQuotationPdfBlob, window.currentQuotationPdfFileName);
        showToast("📥 PDF re-downloaded to your Downloads folder!", "success");
      } else {
        downloadQuotationPdf();
      }
    }

    function reopenWhatsAppTab() {
      const cleanPhone = window.currentClientPhone || (document.getElementById('clientPhone').value || '').replace(/[^0-9]/g, '');
      const text = window.currentWhatsAppText || generateQuotationSummaryText();
      const phoneParam = cleanPhone ? (cleanPhone.length === 10 ? '91' + cleanPhone : cleanPhone) : '';
      
      const waUrl = phoneParam
        ? `https://web.whatsapp.com/send?phone=\${phoneParam}&text=\${encodeURIComponent(text)}`
        : `https://web.whatsapp.com/send?text=\${encodeURIComponent(text)}`;
      
      window.open(waUrl, '_blank');
    }

    function copyQuotationText() {
      const text = generateQuotationSummaryText();
      navigator.clipboard.writeText(text).then(() => {
        showToast("📋 Quotation summary copied to clipboard!", "success");
      });
    }

    function generateQuotationSummaryText() {
      const quoteNo = document.getElementById('quoteNumber').value;
      const date = document.getElementById('quoteDate').value;
      const client = document.getElementById('clientName').value || "Client";
      const total = document.getElementById('grandTotalVal').innerText;

      let msg = `Dear \${client},\\n\\n`;
      msg += `Greetings from MARUTI SALES & SERVICES (Guwahati).\\n`;
      msg += `Please find the quotation summary below:\\n\\n`;
      msg += `📄 *Quote No:* \${quoteNo}\\n`;
      msg += `📅 *Date:* \${date}\\n`;
      msg += `💰 *Grand Total:* \${total} (Incl. GST)\\n\\n`;
      msg += `📦 *Key Items:*\\n`;
      items.forEach((it, idx) => {
        msg += `  \${idx+1}. \${it.name} (Qty: \${it.qty} \${it.unit})\\n`;
      });
      msg += `\\n🏦 *Remittance Bank Details:*\\n`;
      msg += `• Bank: State Bank of India\\n`;
      msg += `• A/C: 38472910482 (IFSC: SBIN0003295)\\n`;
      msg += `• Branch: G.S. Road, Guwahati\\n\\n`;
      msg += `For orders or queries, call us at +91 70022 53021. Thank you!`;
      return msg;
    }

    function saveQuotationToStorage() {
      const quote = {
        number: document.getElementById('quoteNumber').value,
        date: document.getElementById('quoteDate').value,
        validUntil: document.getElementById('validUntil').value,
        taxType: document.getElementById('taxType').value,
        client: {
          name: document.getElementById('clientName').value,
          contact: document.getElementById('contactPerson').value,
          phone: document.getElementById('clientPhone').value,
          gstin: document.getElementById('clientGstin').value,
          state: document.getElementById('clientState').value,
          address: document.getElementById('clientAddress').value
        },
        items: items,
        terms: document.getElementById('termsConditions').value,
        savedAt: new Date().toISOString()
      };

      let history = JSON.parse(localStorage.getItem('maruti_quotes') || '[]');
      const existingIdx = history.findIndex(q => q.number === quote.number);
      if (existingIdx >= 0) {
        history[existingIdx] = quote;
      } else {
        history.unshift(quote);
      }
      localStorage.setItem('maruti_quotes', JSON.stringify(history));
      updateSavedCount();

      // Show interactive save modal with instant WhatsApp option
      openSaveSuccessModal(quote.number);
    }

    function openSaveSuccessModal(quoteNo) {
      document.getElementById('saveSuccessQuoteNo').innerText = quoteNo;
      document.getElementById('saveSuccessModal').classList.add('active');
    }

    function closeSaveSuccessModal() {
      document.getElementById('saveSuccessModal').classList.remove('active');
    }

    function newQuotation() {
      if (confirm("Start a new quotation? Unsaved changes will be cleared.")) {
        items = [];
        document.getElementById('clientName').value = "";
        document.getElementById('contactPerson').value = "";
        document.getElementById('clientPhone').value = "";
        document.getElementById('clientGstin').value = "";
        document.getElementById('clientAddress').value = "";
        initDates();
        const rand = Math.floor(1000 + Math.random() * 9000);
        document.getElementById('quoteNumber').value = "MSS/2026-27/" + rand;
        renderTable();
      }
    }

    function updateSavedCount() {
      const history = JSON.parse(localStorage.getItem('maruti_quotes') || '[]');
      document.getElementById('savedCount').innerText = history.length;
    }

    function openHistoryModal() {
      const history = JSON.parse(localStorage.getItem('maruti_quotes') || '[]');
      const tbody = document.getElementById('historyTableBody');
      tbody.innerHTML = '';
      if (history.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding:20px; color:#64748B;">No quotations saved yet.</td></tr>';
      } else {
        history.forEach((q, idx) => {
          let total = 0;
          (q.items || []).forEach(it => {
            const tax = (it.qty * it.rate) - ((it.qty * it.rate) * ((it.discount || 0) / 100));
            total += tax + (tax * ((it.gst || 0) / 100));
          });
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td><strong>\${q.number}</strong></td>
            <td>\${q.date}</td>
            <td>\${q.client?.name || 'Walk-in'}</td>
            <td style="text-align:right; font-weight:600; font-variant-numeric:tabular-nums;">₹ \${formatCurrency(Math.round(total))}</td>
            <td style="text-align:center;">
              <button class="btn btn-sm btn-primary" onclick="loadQuoteFromHistory(\${idx})">Load</button>
              <button class="btn btn-sm btn-danger" onclick="deleteQuoteFromHistory(\${idx})">&times;</button>
            </td>
          `;
          tbody.appendChild(tr);
        });
      }
      document.getElementById('historyModal').classList.add('active');
    }

    function closeHistoryModal() {
      document.getElementById('historyModal').classList.remove('active');
    }

    function loadQuoteFromHistory(idx) {
      const history = JSON.parse(localStorage.getItem('maruti_quotes') || '[]');
      const q = history[idx];
      if (!q) return;

      document.getElementById('quoteNumber').value = q.number;
      document.getElementById('quoteDate').value = q.date;
      document.getElementById('validUntil').value = q.validUntil;
      document.getElementById('taxType').value = q.taxType || 'intra';
      document.getElementById('clientName').value = q.client?.name || '';
      document.getElementById('contactPerson').value = q.client?.contact || '';
      document.getElementById('clientPhone').value = q.client?.phone || '';
      document.getElementById('clientGstin').value = q.client?.gstin || '';
      document.getElementById('clientState').value = q.client?.state || 'Assam (18)';
      document.getElementById('clientAddress').value = q.client?.address || '';
      document.getElementById('termsConditions').value = q.terms || '';

      items = q.items || [];
      renderTable();
      closeHistoryModal();
    }

    function deleteQuoteFromHistory(idx) {
      let history = JSON.parse(localStorage.getItem('maruti_quotes') || '[]');
      history.splice(idx, 1);
      localStorage.setItem('maruti_quotes', JSON.stringify(history));
      updateSavedCount();
      openHistoryModal();
    }

    function openSettingsModal() {
      document.getElementById('settingsModal').classList.add('active');
    }

    function closeSettingsModal() {
      document.getElementById('settingsModal').classList.remove('active');
    }

    function saveSettings() {
      const settings = {
        name: document.getElementById('s_firmName').value,
        tagline: document.getElementById('s_tagline').value,
        address: document.getElementById('s_address').value,
        phone: document.getElementById('s_phone').value,
        email: document.getElementById('s_email').value,
        gstin: document.getElementById('s_gstin').value,
        bankName: document.getElementById('s_bankName').value,
        bankAcNo: document.getElementById('s_bankAcNo').value,
        bankIfsc: document.getElementById('s_bankIfsc').value,
        bankBranch: document.getElementById('s_bankBranch').value,
      };
      localStorage.setItem('maruti_firm_settings', JSON.stringify(settings));
      loadSettings();
      closeSettingsModal();
      alert("Firm profile updated!");
    }

    function loadSettings() {
      const s = JSON.parse(localStorage.getItem('maruti_firm_settings') || 'null');
      if (!s) return;
      document.getElementById('p_firmName').innerText = s.name;
      document.getElementById('p_tagline').innerText = s.tagline;
      document.getElementById('p_address').innerText = s.address;
      document.getElementById('p_phone').innerText = s.phone;
      document.getElementById('p_email').innerText = s.email;
      document.getElementById('p_gstin').innerText = s.gstin;
      document.getElementById('p_bankName').innerText = s.bankName;
      document.getElementById('p_bankAcNo').innerText = s.bankAcNo;
      document.getElementById('p_bankIfsc').innerText = s.bankIfsc;
      document.getElementById('p_bankBranch').innerText = s.bankBranch;
    }

    // Helper: Indian Currency Number to Words
    function convertNumberToWords(amount) {
      if (!amount || amount === 0) return "Rupees Zero Only";
      const ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
      const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

      function convertGroup(n) {
        let str = '';
        if (n >= 100) {
          str += ones[Math.floor(n / 100)] + ' Hundred ';
          n %= 100;
        }
        if (n >= 20) {
          str += tens[Math.floor(n / 10)] + (n % 10 ? '-' + ones[n % 10] : '') + ' ';
        } else if (n > 0) {
          str += ones[n] + ' ';
        }
        return str.trim();
      }

      let n = Math.floor(amount);
      const crore = Math.floor(n / 10000000);
      n %= 10000000;
      const lakh = Math.floor(n / 100000);
      n %= 100000;
      const thousand = Math.floor(n / 1000);
      n %= 1000;
      const remainder = n;

      let res = '';
      if (crore > 0) res += convertGroup(crore) + ' Crore ';
      if (lakh > 0) res += convertGroup(lakh) + ' Lakh ';
      if (thousand > 0) res += convertGroup(thousand) + ' Thousand ';
      if (remainder > 0) res += convertGroup(remainder) + ' ';

      return "Rupees " + res.trim() + " Only";
    }

    function formatCurrency(val) {
      return (val || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function formatDate(dStr) {
      if (!dStr) return '';
      const p = dStr.split('-');
      return p.length === 3 ? `\${p[2]}-\${p[1]}-\${p[0]}` : dStr;
    }

    function escapeHtml(str) {
      return (str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
  </script>
</body>
</html>
HTML;

file_put_contents(__DIR__ . '/../Maruti-Quotation-Maker.html', $html);
file_put_contents(__DIR__ . '/../public/maruti-offline.html', $html);
file_put_contents(__DIR__ . '/../index.html', $html);
if (!is_dir(__DIR__ . '/../docs')) {
    mkdir(__DIR__ . '/../docs', 0777, true);
}
file_put_contents(__DIR__ . '/../docs/index.html', $html);
echo "Generated multi-display responsive Maruti-Quotation-Maker.html (" . strlen($html) . " bytes)\n";
