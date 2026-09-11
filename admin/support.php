<?php
$adminTitle = 'Manage Support';
$adminActive = 'support';
require_once __DIR__ . '/../includes/admin-header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div>
    <h1 class="bxm-admin-page-title">Support Tickets</h1>
    <p class="bxm-admin-page-sub mb-0">Respond to customer questions and issues.</p>
  </div>
</div>

<div class="bxm-table-wrap">
  <div class="table-responsive">
    <table class="table bxm-table align-middle">
      <thead><tr><th>Ticket ID</th><th>User</th><th>Order</th><th>Subject</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody id="ticketsTable"><tr><td colspan="7" class="text-center text-muted py-4">Loading...</td></tr></tbody>
    </table>
  </div>
</div>
<div class="bxm-pager d-flex justify-content-center gap-1 mt-3" id="supportPager"></div>

<div class="modal fade" id="ticketModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content bxm-modal-content">
      <div class="modal-header"><h5 class="modal-title">Ticket Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body" id="ticketModalBody"></div>
      <div class="modal-footer">
        <button type="button" class="bxm-btn bxm-btn-outline" data-bs-dismiss="modal">Close</button>
        <button type="button" class="bxm-btn bxm-btn-primary" id="saveTicketBtn">Save Reply</button>
      </div>
    </div>
  </div>
</div>
<?php
$inlineScript = <<<'HTML'
<script>
window.BXMAdmin.ready(function () {
  var db = window.BXM.db;
  var tickets = [];
  var activeTicket = null;
  var page = 1;
  var PER_PAGE = 15;

  function render() {
    var slice = window.BXMAdmin.pageSlice(tickets, page, PER_PAGE);
    page = slice.page;
    var host = document.getElementById('ticketsTable');
    if (!slice.items.length) { host.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No tickets yet.</td></tr>'; }
    else host.innerHTML = slice.items.map(function (t) {
      return '<tr>' +
        '<td>' + window.BXM.escapeHtml(t.id) + '</td>' +
        '<td>' + window.BXM.escapeHtml(t.userName || t.email || '') + '</td>' +
        '<td>' + window.BXM.escapeHtml(t.orderId || '-') + '</td>' +
        '<td class="text-truncate" style="max-width:200px">' + window.BXM.escapeHtml(t.subject || '') + '</td>' +
        '<td>' + window.BXMAdmin.fmtDate(t.createdAt) + '</td>' +
        '<td>' + window.BXM.statusBadge(t.status || 'open') + '</td>' +
        '<td><button class="bxm-copy-btn" data-view="' + t.id + '"><i class="bi bi-eye"></i> View</button></td></tr>';
    }).join('');
    window.BXMAdmin.renderPager('supportPager', slice.page, slice.pages, function (p) { page = p; render(); });
  }

  function open(t) {
    activeTicket = t;
    document.getElementById('ticketModalBody').innerHTML =
      '<div class="bxm-delivery-row"><span class="bxm-delivery-key">User</span><span class="bxm-delivery-val">' + window.BXM.escapeHtml(t.userName || '') + ' (' + window.BXM.escapeHtml(t.email || '') + ')</span></div>' +
      '<div class="bxm-delivery-row"><span class="bxm-delivery-key">Order</span><span class="bxm-delivery-val">' + window.BXM.escapeHtml(t.orderId || '-') + '</span></div>' +
      '<h6 class="mt-3">Subject</h6><p>' + window.BXM.escapeHtml(t.subject || '') + '</p>' +
      '<h6>Message</h6><div class="bxm-instruction-box mb-3">' + window.BXM.escapeHtml(t.message || '') + '</div>' +
      '<label class="form-label">Reply</label><textarea class="form-control mb-3" id="ticketReply" rows="4">' + window.BXM.escapeHtml(t.reply || '') + '</textarea>' +
      '<label class="form-label">Status</label><select class="form-select" id="ticketStatus">' +
      ['open','replied','closed'].map(function (s) { return '<option value="' + s + '"' + (t.status === s ? ' selected' : '') + '>' + s.charAt(0).toUpperCase() + s.slice(1) + '</option>'; }).join('') +
      '</select>';
    new bootstrap.Modal(document.getElementById('ticketModal')).show();
  }

  document.getElementById('ticketsTable').addEventListener('click', function (e) {
    var btn = e.target.closest('[data-view]');
    if (!btn) return;
    var t = tickets.filter(function (x) { return x.id === btn.getAttribute('data-view'); })[0];
    if (t) open(t);
  });

  document.getElementById('saveTicketBtn').addEventListener('click', function () {
    if (!activeTicket) return;
    var reply = document.getElementById('ticketReply').value.trim();
    var status = document.getElementById('ticketStatus').value;
    db.ref('supportTickets/' + activeTicket.id).update({ reply: reply, status: status, updatedAt: Date.now() }).then(function () {
      if (activeTicket.uid && (reply !== (activeTicket.reply || '') || status === 'replied')) {
        window.BXM.pushNotification(activeTicket.uid, {
          title: status === 'closed' ? 'Support ticket closed' : 'Support replied',
          message: 'Your ticket "' + (activeTicket.subject || '') + '" has a new update.',
          type: 'info',
          link: window.BXM.url('support.php')
        });
      }
      bootstrap.Modal.getInstance(document.getElementById('ticketModal')).hide();
      window.BXM.toast('Ticket updated', 'success');
      load();
    }).catch(function () { window.BXM.toast('Could not update ticket', 'danger'); });
  });

  function load() {
    db.ref('supportTickets').once('value').then(function (s) {
      tickets = [];
      s.forEach(function (c) { var t = c.val() || {}; t.id = c.key; tickets.push(t); });
      tickets.sort(function (a, b) { return (b.createdAt || 0) - (a.createdAt || 0); });
      render();
    }).catch(function () {
      document.getElementById('ticketsTable').innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">Ticket list requires root-level admin read rules. Update your Realtime Database rules.</td></tr>';
    });
  }

  load();
});
</script>
HTML;
require_once __DIR__ . '/../includes/admin-footer.php';
?>
