<?php
$adminTitle = 'Settings';
$adminActive = 'settings';
require_once __DIR__ . '/../includes/admin-header.php';
?>
<div class="mb-4">
  <h1 class="bxm-admin-page-title">Settings</h1>
  <p class="bxm-admin-page-sub mb-0">Manage payment details and site information shown to users.</p>
</div>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="bxm-form-card mb-4">
      <h6 class="mb-3">Payment Settings</h6>
      <form id="paymentSettingsForm">
        <div class="mb-3">
          <label class="form-label">UPI ID</label>
          <input type="text" class="form-control" id="sUpi" placeholder="example@upi">
        </div>
        <div class="mb-3">
          <label class="form-label">Payment Display Name</label>
          <input type="text" class="form-control" id="sName" placeholder="BLACK X MARKET">
        </div>
        <div class="mb-3">
          <label class="form-label">Payment Instructions</label>
          <textarea class="form-control" id="sInstructions" rows="4" placeholder="Scan the QR code or use the UPI ID below..."></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label">QR Code</label>
          <input type="file" class="form-control" id="sQr" accept="image/*">
        </div>
        <div class="mb-3">
          <img id="sQrPreview" class="bxm-upload-preview" style="max-width:220px" hidden alt="QR preview">
          <button type="button" class="bxm-copy-btn mt-2" id="removeQrBtn" hidden><i class="bi bi-trash"></i> Remove QR</button>
        </div>
        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" id="sActive" checked>
          <label class="form-check-label" for="sActive">Payment method active</label>
        </div>
        <button type="submit" class="bxm-btn bxm-btn-primary"><i class="bi bi-check2"></i> Save Payment Settings</button>
      </form>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="bxm-form-card mb-4">
      <h6 class="mb-3">Site Information</h6>
      <form id="siteSettingsForm">
        <div class="mb-3"><label class="form-label">Site Name</label><input type="text" class="form-control" id="siName"></div>
        <div class="mb-3"><label class="form-label">Tagline</label><input type="text" class="form-control" id="siTagline"></div>
        <div class="mb-3"><label class="form-label">Support Email</label><input type="email" class="form-control" id="siEmail"></div>
        <div class="mb-3"><label class="form-label">Currency Symbol</label><input type="text" class="form-control" id="siCurrency" maxlength="3"></div>
        <button type="submit" class="bxm-btn bxm-btn-primary"><i class="bi bi-check2"></i> Save Site Info</button>
      </form>
    </div>

    <div class="bxm-form-card">
      <h6 class="mb-3">Preview</h6>
      <div id="paymentPreview" class="text-secondary small">Loading payment preview...</div>
    </div>
  </div>
</div>
<?php
$inlineScript = <<<'HTML'
<script>
window.BXMAdmin.ready(function () {
  var db = window.BXM.db;
  var qrCodeUrl = '';

  function previewPayment() {
    var host = document.getElementById('paymentPreview');
    var upi = document.getElementById('sUpi').value.trim();
    var name = document.getElementById('sName').value;
    var active = document.getElementById('sActive').checked;
    var html = '';
    if (!active) html += '<p class="text-warning">Payment method is inactive.</p>';
    if (qrCodeUrl) html += '<div class="bxm-qr-box mb-2"><img src="' + window.BXM.escapeHtml(qrCodeUrl) + '" alt="QR"></div>';
    if (upi) html += '<div class="mb-1">UPI ID: <b class="text-white">' + window.BXM.escapeHtml(upi) + '</b></div>';
    if (name) html += '<div class="mb-1">Pay to: <b class="text-white">' + window.BXM.escapeHtml(name) + '</b></div>';
    host.innerHTML = html || '<p class="mb-0">No payment information configured.</p>';
  }

  db.ref('settings/payment').once('value').then(function (s) {
    var p = s.val() || {};
    document.getElementById('sUpi').value = p.upiId || '';
    document.getElementById('sName').value = p.paymentDisplayName || '';
    document.getElementById('sInstructions').value = p.instructions || '';
    document.getElementById('sActive').checked = p.active !== false;
    qrCodeUrl = p.qrCodeUrl || '';
    var prev = document.getElementById('sQrPreview');
    if (qrCodeUrl) { prev.src = qrCodeUrl; prev.hidden = false; document.getElementById('removeQrBtn').hidden = false; }
    previewPayment();
  });

  db.ref('settings/siteInfo').once('value').then(function (s) {
    var p = s.val() || {};
    document.getElementById('siName').value = p.siteName || 'BLACK X MARKET';
    document.getElementById('siTagline').value = p.tagline || '';
    document.getElementById('siEmail').value = p.supportEmail || '';
    document.getElementById('siCurrency').value = p.currency || '₹';
  });

  document.getElementById('sQr').addEventListener('change', function (e) {
    var file = e.target.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function (ev) { var p = document.getElementById('sQrPreview'); p.src = ev.target.result; p.hidden = false; };
    reader.readAsDataURL(file);
    document.getElementById('removeQrBtn').hidden = false;
  });

  document.getElementById('removeQrBtn').addEventListener('click', function () {
    qrCodeUrl = '';
    document.getElementById('sQr').value = '';
    document.getElementById('sQrPreview').hidden = true;
    document.getElementById('removeQrBtn').hidden = true;
    db.ref('settings/payment/qrCodeUrl').remove();
    window.BXM.toast('QR code removed', 'info');
    previewPayment();
  });

  ['sUpi', 'sName', 'sActive'].forEach(function (id) {
    document.getElementById(id).addEventListener('input', previewPayment);
    document.getElementById(id).addEventListener('change', previewPayment);
  });

  document.getElementById('paymentSettingsForm').addEventListener('submit', function (e) {
    e.preventDefault();
    var file = document.getElementById('sQr').files[0];
    window.BXM.loader(true);
    var qrPromise = file ? window.BXM.uploadImage(file, 'payment-qr') : Promise.resolve(null);
    qrPromise.then(function (up) {
      if (up && !up.ok) throw new Error(up.error || 'QR upload failed');
      var data = {
        upiId: document.getElementById('sUpi').value.trim(),
        paymentDisplayName: document.getElementById('sName').value.trim(),
        instructions: document.getElementById('sInstructions').value.trim(),
        active: document.getElementById('sActive').checked,
        updatedAt: Date.now()
      };
      if (up && up.url) { data.qrCodeUrl = up.url; qrCodeUrl = up.url; }
      else if (!qrCodeUrl) { data.qrCodeUrl = ''; }
      return db.ref('settings/payment').update(data);
    }).then(function () {
      window.BXM.loader(false);
      window.BXM.toast('Payment settings saved', 'success');
      var prev = document.getElementById('sQrPreview');
      if (qrCodeUrl) { prev.src = qrCodeUrl; prev.hidden = false; }
      previewPayment();
    }).catch(function (err) {
      window.BXM.loader(false);
      window.BXM.toast(err.message || 'Could not save settings', 'danger');
    });
  });

  document.getElementById('siteSettingsForm').addEventListener('submit', function (e) {
    e.preventDefault();
    db.ref('settings/siteInfo').update({
      siteName: document.getElementById('siName').value.trim(),
      tagline: document.getElementById('siTagline').value.trim(),
      supportEmail: document.getElementById('siEmail').value.trim(),
      currency: document.getElementById('siCurrency').value.trim() || '₹',
      updatedAt: Date.now()
    }).then(function () { window.BXM.toast('Site information saved', 'success'); })
      .catch(function () { window.BXM.toast('Could not save site info', 'danger'); });
  });
});
</script>
HTML;
require_once __DIR__ . '/../includes/admin-footer.php';
?>
