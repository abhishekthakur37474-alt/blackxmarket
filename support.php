<?php
require_once __DIR__ . '/includes/auth-check.php';
$pageTitle = 'Customer Support';
$pageDescription = 'Get help with your orders and account.';
$activePage = 'support';
require_once __DIR__ . '/includes/header.php';
$prefillOrder = isset($_GET['order']) ? preg_replace('/[^A-Za-z0-9_-]/', '', $_GET['order']) : '';
?>
<div class="container" id="bxmConfigNotice"></div>

<section class="bxm-section-sm">
  <div class="container">
    <div class="mb-4">
      <h1 class="bxm-section-title">Customer Support</h1>
      <p class="bxm-section-sub mb-0">How can we help? Submit a ticket and our team will respond.</p>
    </div>

    <div class="row g-4">
      <div class="col-lg-5">
        <div class="bxm-card p-4">
          <h6 class="mb-3">Submit a Ticket</h6>
          <form id="ticketForm">
            <div class="mb-3">
              <label class="form-label">Order ID (optional)</label>
              <input type="text" class="form-control" id="ticketOrder" placeholder="BXM-2026-XXXXXX" value="<?= bxm_e($prefillOrder) ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Subject</label>
              <input type="text" class="form-control" id="ticketSubject" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Message</label>
              <textarea class="form-control" id="ticketMessage" rows="5" required></textarea>
            </div>
            <button type="submit" class="bxm-btn bxm-btn-primary w-100"><i class="bi bi-send"></i> Submit Ticket</button>
          </form>
        </div>
      </div>
      <div class="col-lg-7">
        <h6 class="mb-3">My Tickets</h6>
        <div id="ticketsList"><div class="bxm-skeleton" style="height:120px"></div></div>
      </div>
    </div>
  </div>
</section>
<?php
$inlineScript = <<<'HTML'
<script>
(function () {
  function renderTickets(tickets) {
    var host = document.getElementById('ticketsList');
    if (!tickets.length) {
      host.innerHTML = '<div class="bxm-state-box text-center"><i class="bi bi-life-preserver bxm-state-icon"></i><p class="text-secondary mb-0">No support tickets yet.</p></div>';
      return;
    }
    host.innerHTML = tickets.map(function (t) {
      return '<div class="bxm-card p-3 mb-3">' +
        '<div class="d-flex justify-content-between align-items-start gap-2">' +
        '<div><div class="fw-semibold">' + window.BXM.escapeHtml(t.subject || 'Ticket') + '</div>' +
        '<div class="small text-muted">' + (t.orderId ? 'Order: ' + window.BXM.escapeHtml(t.orderId) + ' - ' : '') + new Date(t.createdAt || Date.now()).toLocaleString() + '</div></div>' +
        window.BXM.statusBadge(t.status === 'replied' ? 'replied' : (t.status || 'open')) + '</div>' +
        '<p class="text-secondary small mt-2 mb-1">' + window.BXM.escapeHtml(t.message || '') + '</p>' +
        (t.reply ? '<div class="bxm-instruction-box mt-2"><b class="text-white">Support Reply:</b>\n' + window.BXM.escapeHtml(t.reply) + '</div>' : '') +
        '</div>';
    }).join('');
  }

  function loadTickets(user) {
    window.BXM.db.ref('users/' + user.uid + '/tickets').once('value').then(function (snap) {
      var ids = [];
      snap.forEach(function (c) { ids.push(c.key); });
      if (!ids.length) { renderTickets([]); return; }
      Promise.all(ids.map(function (id) {
        return window.BXM.db.ref('supportTickets/' + id).once('value').then(function (s) {
          var t = s.val();
          if (t) { t.id = id; return t; }
          return null;
        }).catch(function () { return null; });
      })).then(function (list) {
        list = list.filter(Boolean).sort(function (a, b) { return (b.createdAt || 0) - (a.createdAt || 0); });
        renderTickets(list);
      });
    });
  }

  document.getElementById('ticketForm').addEventListener('submit', function (e) {
    e.preventDefault();
    var user = window.BXM.user;
    if (!user) return;
    var subject = document.getElementById('ticketSubject').value.trim();
    var message = document.getElementById('ticketMessage').value.trim();
    var orderId = document.getElementById('ticketOrder').value.trim();
    if (!subject || !message) { window.BXM.toast('Please fill in subject and message', 'warning'); return; }
    var name = (window.BXM.profile && window.BXM.profile.name) || user.displayName || 'Customer';
    window.BXM.loader(true);
    var ref = window.BXM.db.ref('supportTickets').push();
    var ticket = {
      uid: user.uid,
      userName: name,
      email: user.email,
      orderId: orderId,
      subject: subject,
      message: message,
      status: 'open',
      reply: '',
      createdAt: Date.now()
    };
    ref.set(ticket).then(function () {
      return window.BXM.db.ref('users/' + user.uid + '/tickets/' + ref.key).set(true);
    }).then(function () {
      window.BXM.loader(false);
      document.getElementById('ticketForm').reset();
      window.BXM.toast('Ticket submitted successfully', 'success');
      loadTickets(user);
    }).catch(function () {
      window.BXM.loader(false);
      window.BXM.toast('Could not submit ticket', 'danger');
    });
  });

  window.BXM.onAuth(function (user) {
    if (!window.BXM.firebaseReady || !user) return;
    loadTickets(user);
  });
})();
</script>
HTML;
include __DIR__ . '/includes/footer.php';
?>
