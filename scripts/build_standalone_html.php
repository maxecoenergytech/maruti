<?php

$logoPath = __DIR__ . '/../public/images/maruti-logo.png';
$logoBase64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : '';

$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Maruti Sales & Services — Offline Quotation Maker</title>
  <link rel="icon" href="data:image/png;base64,{$logoBase64}">
  <style>
    :root {
      --primary: #BE9223;
      --primary-dark: #967215;
      --secondary: #3F527D;
      --secondary-dark: #2B3A5A;
      --bg: #F8FAFC;
      --card-bg: #FFFFFF;
      --text: #1E293B;
      --text-muted: #64748B;
      --border: #CBD5E1;
      --danger: #DC2626;
      --success: #16A34A;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
    body { background-color: var(--bg); color: var(--text); padding-bottom: 60px; }
    .no-print { display: block; }
    .print-only { display: none; }

    /* App Header / Navbar */
    .navbar { background: linear-gradient(135deg, var(--secondary-dark), var(--secondary)); color: #fff; padding: 14px 24px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .nav-brand { display: flex; align-items: center; gap: 12px; }
    .nav-brand img { height: 42px; background: #fff; border-radius: 6px; padding: 2px; }
    .nav-title { font-size: 1.25rem; font-weight: 700; letter-spacing: 0.5px; }
    .nav-subtitle { font-size: 0.75rem; color: #CBD5E1; }
    .nav-actions { display: flex; gap: 10px; }

    /* Buttons */
    .btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 6px; font-size: 0.875rem; font-weight: 600; cursor: pointer; border: 1px solid transparent; transition: all 0.2s; text-decoration: none; }
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

    /* Container & Layout */
    .container { max-width: 1200px; margin: 24px auto; padding: 0 16px; }
    .grid { display: grid; grid-template-columns: 1fr; gap: 20px; }
    @media(min-width: 900px) {
      .grid-3 { grid-template-columns: repeat(3, 1fr); }
      .grid-4 { grid-template-columns: repeat(4, 1fr); }
      .grid-2 { grid-template-columns: 1fr 1fr; }
    }

    /* Cards */
    .card { background: var(--card-bg); border-radius: 8px; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 20px; }
    .card-header { padding: 12px 18px; background: #F1F5F9; border-bottom: 1px solid var(--border); font-weight: 600; font-size: 0.95rem; display: flex; justify-content: space-between; align-items: center; color: var(--secondary); }
    .card-body { padding: 18px; }

    /* Form Fields */
    .form-group { margin-bottom: 14px; }
    .form-label { display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px; color: var(--text-muted); }
    .form-control { width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; font-size: 0.875rem; outline: none; transition: border-color 0.2s; background: #fff; }
    .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 2px rgba(190, 146, 35, 0.2); }
    select.form-control { cursor: pointer; }

    /* Items Table */
    .table-container { overflow-x: auto; margin-top: 10px; }
    table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.85rem; }
    th { background: #F1F5F9; padding: 10px 12px; font-weight: 600; color: var(--secondary); border-bottom: 2px solid var(--border); }
    td { padding: 8px 12px; border-bottom: 1px solid var(--border); vertical-align: top; }
    tr:hover td { background-color: #F8FAFC; }

    /* Calculation Totals Summary Box */
    .calc-box { background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 8px; padding: 16px; margin-top: 16px; }
    .calc-row { display: flex; justify-content: space-between; padding: 4px 0; font-size: 0.875rem; }
    .calc-row.total { border-top: 2px solid var(--primary); margin-top: 8px; padding-top: 8px; font-size: 1.15rem; font-weight: 700; color: var(--secondary); }
    .badge { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; }
    .badge-info { background: #E0E7FF; color: #3730A3; }
    .badge-success { background: #DCFCE7; color: #166534; }

    /* Print Preview View (Exact Maruti Letterhead Style) */
    @media print {
      body { background: #fff; color: #000; padding: 0; }
      .no-print { display: none !important; }
      .print-only { display: block !important; }
      .container { max-width: 100%; margin: 0; padding: 0; }
      .invoice-page { padding: 15mm 15mm; }
      table { border-collapse: collapse; width: 100%; }
      th { background-color: #3F527D !important; color: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; border: 1px solid #3F527D; }
      td { border: 1px solid #CBD5E1; }
      .print-header { border-bottom: 2px solid #BE9223; padding-bottom: 12px; margin-bottom: 16px; }
      .print-logo { max-width: 130px; }
    }

    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; }
    .modal.active { display: flex; }
    .modal-content { background: #fff; width: 95%; max-width: 650px; border-radius: 8px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
    .modal-header { padding: 14px 20px; background: var(--secondary); color: #fff; font-weight: 600; display: flex; justify-content: space-between; }
    .modal-body { padding: 20px; max-height: 80vh; overflow-y: auto; }
    .modal-footer { padding: 12px 20px; background: #F8FAFC; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 10px; }
  </style>
</head>
<body>

  <!-- Top Navbar -->
  <header class="navbar no-print">
    <div class="nav-brand">
      <img src="data:image/png;base64,{$logoBase64}" alt="Maruti Logo">
      <div>
        <div class="nav-title">MARUTI SALES & SERVICES</div>
        <div class="nav-subtitle">(A Complete IT Solution) &bull; Standalone Local Edition</div>
      </div>
    </div>
    <div class="nav-actions">
      <button class="btn btn-outline" style="color:#fff; border-color:rgba(255,255,255,0.4);" onclick="openHistoryModal()">📂 Saved Quotes (<span id="savedCount">0</span>)</button>
      <button class="btn btn-outline" style="color:#fff; border-color:rgba(255,255,255,0.4);" onclick="openSettingsModal()">⚙️ Firm Profile</button>
      <button class="btn btn-primary" onclick="newQuotation()">+ New Quotation</button>
    </div>
  </header>

  <!-- Interactive Local Form (No Server Needed) -->
  <main class="container no-print">
    <!-- Action Bar -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:10px;">
      <div>
        <h2 style="font-size:1.3rem; color:var(--secondary-dark); font-weight:700;">Quotation Generator</h2>
        <p style="font-size:0.85rem; color:var(--text-muted);">Self-contained offline application. All data is saved safely in your browser storage.</p>
      </div>
      <div style="display:flex; gap:8px;">
        <button class="btn btn-secondary" onclick="window.print()">🖨️ Print / Save as PDF</button>
        <button class="btn btn-success" onclick="shareWhatsApp()">💬 WhatsApp</button>
        <button class="btn btn-outline" onclick="copyQuotationText()">📋 Copy Details</button>
        <button class="btn btn-primary" onclick="saveQuotationToStorage()">💾 Save</button>
      </div>
    </div>

    <!-- Quotation & Client Details Grid -->
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
              <input type="text" id="quoteNumber" class="form-control" value="MSS/2026-27/0001">
            </div>
            <div class="form-group">
              <label class="form-label">Quotation Date</label>
              <input type="date" id="quoteDate" class="form-control">
            </div>
          </div>
          <div class="grid grid-2">
            <div class="form-group">
              <label class="form-label">Valid Until</label>
              <input type="date" id="validUntil" class="form-control">
            </div>
            <div class="form-group">
              <label class="form-label">Tax Treatment</label>
              <select id="taxType" class="form-control" onchange="recalculate()">
                <option value="intra">Intra-State (Assam - CGST + SGST 50/50)</option>
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
          <button class="btn btn-sm btn-outline" onclick="loadSampleClient()">Pre-fill Demo Client</button>
        </div>
        <div class="card-body">
          <div class="grid grid-2">
            <div class="form-group">
              <label class="form-label">Customer / Firm Name *</label>
              <input type="text" id="clientName" class="form-control" placeholder="e.g. Pragjyotish Tech Solutions">
            </div>
            <div class="form-group">
              <label class="form-label">Contact Person</label>
              <input type="text" id="contactPerson" class="form-control" placeholder="e.g. Mr. Bikram Das">
            </div>
          </div>
          <div class="grid grid-3">
            <div class="form-group">
              <label class="form-label">Phone (WhatsApp)</label>
              <input type="text" id="clientPhone" class="form-control" placeholder="+91 98640 12345">
            </div>
            <div class="form-group">
              <label class="form-label">GSTIN (15 Digits)</label>
              <input type="text" id="clientGstin" class="form-control" placeholder="18AABCP1234M1Z5" onblur="validateGstin(this.value)">
            </div>
            <div class="form-group">
              <label class="form-label">State & Code</label>
              <input type="text" id="clientState" class="form-control" value="Assam (18)">
            </div>
          </div>
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Billing Address</label>
            <input type="text" id="clientAddress" class="form-control" placeholder="e.g. G.S. Road, Christian Basti, Guwahati - 781005">
          </div>
        </div>
      </div>
    </div>

    <!-- Items Section -->
    <div class="card">
      <div class="card-header">
        <span>📦 Items & Services</span>
        <div style="display:flex; gap:10px; align-items:center;">
          <select id="catalogPicker" class="form-control" style="width: auto; max-width: 280px;" onchange="addItemFromCatalog(this.value)">
            <option value="">⚡ Quick Add From Catalog...</option>
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
              <option value="10">Cisco Catalyst 24-Port Gigabit Switch (₹ 18,500 | 18% GST)</option>
              <option value="11">TP-Link Omada EAP610 Wi-Fi 6 AP (₹ 7,200 | 18% GST)</option>
              <option value="12">D-Link 9U Wall Mount Server Rack (₹ 4,800 | 18% GST)</option>
            </optgroup>
            <optgroup label="Software & Antivirus">
              <option value="13">Microsoft Windows 11 Pro 64-bit (₹ 12,500 | 18% GST)</option>
              <option value="14">Quick Heal Total Security 3-User (₹ 2,100 | 18% GST)</option>
            </optgroup>
            <optgroup label="Services & AMC">
              <option value="15">Annual Maintenance Contract (AMC) (₹ 18,000 | 18% GST)</option>
              <option value="16">CCTV Installation & Cabling Service (₹ 3,500 | 18% GST)</option>
              <option value="17">Structured Network Cabling Point (₹ 450 | 18% GST)</option>
            </optgroup>
          </select>
          <button class="btn btn-sm btn-primary" onclick="addNewBlankRow()">+ Add Blank Line</button>
        </div>
      </div>
      <div class="card-body" style="padding:0;">
        <div class="table-container">
          <table id="itemsTable">
            <thead>
              <tr>
                <th style="width:40px;">#</th>
                <th style="min-width:240px;">Item Description</th>
                <th style="width:100px;">HSN/SAC</th>
                <th style="width:80px;">Qty</th>
                <th style="width:80px;">Unit</th>
                <th style="width:110px;">Rate (₹)</th>
                <th style="width:80px;">Disc %</th>
                <th style="width:80px;">GST %</th>
                <th style="width:120px; text-align:right;">Total (₹)</th>
                <th style="width:50px; text-align:center;">Action</th>
              </tr>
            </thead>
            <tbody id="itemsBody">
              <!-- Dynamic Rows inserted here -->
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Calculations, Notes & Remittance Box -->
    <div class="grid grid-2">
      <!-- Terms & Notes -->
      <div class="card">
        <div class="card-header">Terms & Conditions</div>
        <div class="card-body">
          <textarea id="termsConditions" class="form-control" rows="5">1. Quotation validity: 15 days from the date of issue.
2. Payment terms: 50% advance along with confirmed work order, balance upon delivery and installation.
3. Warranty: Standard manufacturer warranty applies to all hardware products.
4. Taxes: GST is extra as applicable at prevailing government rates.
5. Delivery: Within 3 to 7 working days upon receipt of confirmed order.
6. Jurisdiction: Subject to Guwahati jurisdiction.</textarea>
        </div>
      </div>

      <!-- Real-time GST Totals -->
      <div class="calc-box">
        <div style="font-weight:700; color:var(--secondary); font-size:1rem; margin-bottom:12px; border-bottom:1px solid #FDE68A; padding-bottom:6px;">
          Calculation & Tax Breakdown
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
  </main>

  <!-- PRINT ONLY TEMPLATE (Matches Official Maruti Quotation PDF Layout) -->
  <div class="print-only invoice-page">
    <div class="print-header" style="display:flex; justify-content:space-between; align-items:flex-start;">
      <div style="display:flex; gap:16px; align-items:center;">
        <img src="data:image/png;base64,{$logoBase64}" class="print-logo" alt="Logo">
        <div>
          <h1 style="font-size:1.4rem; color:#3F527D; font-weight:800; margin-bottom:2px;" id="p_firmName">MARUTI SALES & SERVICES</h1>
          <div style="font-size:0.85rem; font-weight:600; color:#BE9223; margin-bottom:4px;" id="p_tagline">(A Complete IT Solution)</div>
          <div style="font-size:0.75rem; color:#333; line-height:1.3;" id="p_address">
            Mahabir Market, Ground Floor, opp. Hotel Siroy Lily, Solapara Road, Paltan Bazar, Guwahati - 781008, Assam
          </div>
          <div style="font-size:0.75rem; color:#333;">
            Phone: <span id="p_phone">+91 70022 53021</span> | Email: <span id="p_email">smarutisales@gmail.com</span> | GSTIN: <strong id="p_gstin">18ABGFM5224K1Z2</strong>
          </div>
        </div>
      </div>
      <div style="text-align:right;">
        <h2 style="font-size:1.5rem; color:#3F527D; font-weight:800; letter-spacing:1px;">QUOTATION</h2>
        <div style="font-size:0.85rem; font-weight:700;" id="p_quoteNo">MSS/2026-27/0001</div>
        <div style="font-size:0.75rem;">Date: <span id="p_quoteDate"></span></div>
        <div style="font-size:0.75rem;">Valid Until: <span id="p_validUntil"></span></div>
      </div>
    </div>

    <!-- Client Box -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:14px; font-size:0.8rem; background:#F8FAFC; padding:10px; border:1px solid #CBD5E1; border-radius:4px;">
      <div>
        <div style="font-weight:700; color:#3F527D; text-transform:uppercase; margin-bottom:4px;">Quotation For (Client):</div>
        <div style="font-size:0.95rem; font-weight:700;" id="p_clientName">M/s Customer Name</div>
        <div id="p_contactPerson">Attn: Contact Person</div>
        <div id="p_clientAddress">Billing Address</div>
      </div>
      <div style="text-align:right;">
        <div>Client GSTIN: <strong id="p_clientGstin">Unregistered</strong></div>
        <div>State & Code: <span id="p_clientState">Assam (18)</span></div>
        <div>Phone: <span id="p_clientPhone">-</span></div>
      </div>
    </div>

    <!-- Items Table Print -->
    <table style="width:100%; margin-bottom:14px; font-size:0.75rem;">
      <thead>
        <tr>
          <th style="width:30px; text-align:center;">#</th>
          <th>Description of Goods / Services</th>
          <th style="width:60px; text-align:center;">HSN/SAC</th>
          <th style="width:40px; text-align:center;">Qty</th>
          <th style="width:40px; text-align:center;">Unit</th>
          <th style="width:70px; text-align:right;">Rate (₹)</th>
          <th style="width:45px; text-align:right;">Disc %</th>
          <th style="width:45px; text-align:right;">GST %</th>
          <th style="width:85px; text-align:right;">Total (₹)</th>
        </tr>
      </thead>
      <tbody id="p_itemsBody">
      </tbody>
    </table>

    <!-- Totals Table -->
    <div style="display:grid; grid-template-columns:1.2fr 0.8fr; gap:16px; font-size:0.75rem; margin-bottom:16px;">
      <div>
        <div style="margin-bottom:8px;">
          <strong>Amount in Words:</strong><br>
          <span id="p_amountWords" style="font-weight:700; color:#1E293B; font-style:italic;">Rupees Zero Only</span>
        </div>
        <div style="border:1px solid #CBD5E1; padding:8px; border-radius:4px; background:#F8FAFC; margin-bottom:8px;">
          <strong style="color:#3F527D;">Bank Remittance Details:</strong><br>
          Bank: <span id="p_bankName">State Bank of India</span> | Branch: <span id="p_bankBranch">G.S. Road, Guwahati</span><br>
          A/C Name: <span id="p_bankAcName">MARUTI SALES & SERVICES</span><br>
          A/C No: <strong id="p_bankAcNo">38472910482</strong> | IFSC: <strong id="p_bankIfsc">SBIN0003295</strong>
        </div>
        <div>
          <strong style="color:#3F527D;">Terms & Conditions:</strong>
          <pre id="p_terms" style="font-family:inherit; white-space:pre-wrap; font-size:0.7rem; color:#475569; margin-top:2px;"></pre>
        </div>
      </div>
      <div>
        <table style="width:100%; font-size:0.8rem;">
          <tr><td>Taxable Value:</td><td style="text-align:right; font-weight:600;" id="p_taxable">₹ 0.00</td></tr>
          <tr id="p_cgstRow"><td>CGST (Central Tax):</td><td style="text-align:right;" id="p_cgst">₹ 0.00</td></tr>
          <tr id="p_sgstRow"><td>SGST (Assam State Tax):</td><td style="text-align:right;" id="p_sgst">₹ 0.00</td></tr>
          <tr id="p_igstRow" style="display:none;"><td>IGST (Integrated Tax):</td><td style="text-align:right;" id="p_igst">₹ 0.00</td></tr>
          <tr><td>Round Off:</td><td style="text-align:right;" id="p_roundOff">₹ 0.00</td></tr>
          <tr style="background:#F1F5F9; font-size:0.95rem; font-weight:800; color:#3F527D;">
            <td>Grand Total (₹):</td><td style="text-align:right;" id="p_grandTotal">₹ 0.00</td>
          </tr>
        </table>
        
        <div style="margin-top:40px; text-align:right; font-size:0.75rem;">
          <p>For <strong>MARUTI SALES & SERVICES</strong></p>
          <div style="height:45px;"></div>
          <p style="font-weight:700;">Authorized Signatory</p>
        </div>
      </div>
    </div>
  </div>

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
        <h4 style="margin:12px 0 8px; font-size:0.85rem; color:var(--secondary);">Bank Details</h4>
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
    <div class="modal-content" style="max-width:800px;">
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

    // Initialize on Load
    window.addEventListener('DOMContentLoaded', () => {
      loadSettings();
      initDates();
      updateSavedCount();
      
      // Seed default items for quick demonstration
      addItemFromCatalog(1);
      addItemFromCatalog(6);
      addItemFromCatalog(16);
    });

    function initDates() {
      const today = new Date();
      document.getElementById('quoteDate').value = today.toISOString().split('T')[0];
      const valid = new Date();
      valid.setDate(valid.getDate() + 15);
      document.getElementById('validUntil').value = valid.toISOString().split('T')[0];
    }

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
          <td>\${i + 1}</td>
          <td><input type="text" class="form-control" value="\${escapeHtml(item.name)}" placeholder="Item description" onchange="updateItem(\${i}, 'name', this.value)"></td>
          <td><input type="text" class="form-control" value="\${item.hsn}" placeholder="HSN/SAC" onchange="updateItem(\${i}, 'hsn', this.value)"></td>
          <td><input type="number" class="form-control" value="\${item.qty}" min="1" step="any" oninput="updateItem(\${i}, 'qty', this.value)"></td>
          <td><input type="text" class="form-control" value="\${item.unit}" onchange="updateItem(\${i}, 'unit', this.value)"></td>
          <td><input type="number" class="form-control" value="\${item.rate}" min="0" step="any" oninput="updateItem(\${i}, 'rate', this.value)"></td>
          <td><input type="number" class="form-control" value="\${item.discount}" min="0" max="100" oninput="updateItem(\${i}, 'discount', this.value)"></td>
          <td>
            <select class="form-control" onchange="updateItem(\${i}, 'gst', this.value)">
              <option value="0" \${item.gst == 0 ? 'selected':''}>0%</option>
              <option value="5" \${item.gst == 5 ? 'selected':''}>5%</option>
              <option value="12" \${item.gst == 12 ? 'selected':''}>12%</option>
              <option value="18" \${item.gst == 18 ? 'selected':''}>18%</option>
              <option value="28" \${item.gst == 28 ? 'selected':''}>28%</option>
            </select>
          </td>
          <td style="text-align:right; font-weight:600; padding-top:14px;">₹ \${formatCurrency(total)}</td>
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

      // Sync Print Layout
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
          <td style="text-align:center;">\${item.hsn || '-'}</td>
          <td style="text-align:center;">\${item.qty}</td>
          <td style="text-align:center;">\${item.unit}</td>
          <td style="text-align:right;">₹ \${formatCurrency(item.rate)}</td>
          <td style="text-align:right;">\${item.discount > 0 ? item.discount + '%' : '-'}</td>
          <td style="text-align:right;">\${item.gst}%</td>
          <td style="text-align:right; font-weight:600;">₹ \${formatCurrency(total)}</td>
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

    function shareWhatsApp() {
      const phone = (document.getElementById('clientPhone').value || '').replace(/[^0-9]/g, '');
      const cleanPhone = phone.length === 10 ? '91' + phone : phone;
      const text = generateQuotationSummaryText();
      const url = `https://wa.me/\${cleanPhone}?text=\${encodeURIComponent(text)}`;
      window.open(url, '_blank');
    }

    function copyQuotationText() {
      const text = generateQuotationSummaryText();
      navigator.clipboard.writeText(text).then(() => {
        alert("Quotation summary copied to clipboard! You can now paste it into WhatsApp or an email.");
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
      alert("Quotation " + quote.number + " saved successfully to your browser storage!");
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
            <td style="text-align:right; font-weight:600;">₹ \${formatCurrency(Math.round(total))}</td>
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
echo "Generated Maruti-Quotation-Maker.html (" . strlen($html) . " bytes)\n";
