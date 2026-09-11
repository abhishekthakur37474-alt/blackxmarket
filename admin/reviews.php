<?php
$adminTitle = 'Manage Reviews';
$adminActive = 'reviews';
require_once __DIR__ . '/../includes/admin-header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div>
    <h1 class="bxm-admin-page-title">Reviews</h1>
    <p class="bxm-admin-page-sub mb-0">Moderate customer reviews before they appear publicly.</p>
  </div>
  <div class="bxm-tabs d-flex gap-2">
    <button class="bxm-btn bxm-btn-outline bxm-btn-sm" data-rfilter="pending">Pending</button>
    <button class="bxm-btn bxm-btn-outline bxm-btn-sm" data-rfilter="approved">Approved</button>
    <button class="bxm-btn bxm-btn-outline bxm-btn-sm" data-rfilter="rejected">Rejected</button>
    <button class="bxm-btn bxm-btn-primary bxm-btn-sm" data-rfilter="all">All</button>
  </div>
</div>

<div class="bxm-table-wrap">
  <div class="table-responsive">
    <table class="table bxm-table align-middle">
      <thead><tr><th>User</th><th>Product</th><th>Rating</th><th>Review</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody id="reviewsTable"><tr><td colspan="7" class="text-center text-muted py-4">Loading...</td></tr></tbody>
    </table>
  </div>
</div>
<div class="bxm-pager d-flex justify-content-center gap-1 mt-3" id="reviewsPager"></div>
<?php
$inlineScript = <<<'HTML'
<script>
window.BXMAdmin.ready(function () {
  var db = window.BXM.db;
  var reviews = [];
  var filter = 'pending';
  var page = 1;
  var PER_PAGE = 15;

  function stars(r) {
    var out = '';
    for (var i = 1; i <= 5; i++) out += '<i class="bi ' + (i <= r ? 'bi-star-fill text-warning' : 'bi-star text-secondary') + '"></i>';
    return out;
  }

  function render() {
    var list = reviews.filter(function (r) { return filter === 'all' || r.status === filter; });
    var slice = window.BXMAdmin.pageSlice(list, page, PER_PAGE);
    page = slice.page;
    var host = document.getElementById('reviewsTable');
    if (!slice.items.length) { host.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No reviews in this category.</td></tr>'; }
    else host.innerHTML = slice.items.map(function (r) {
      var actions = '<div class="bxm-table-actions">' +
        '<button class="bxm-copy-btn text-success" data-approve="' + r.id + '" title="Approve"><i class="bi bi-check-lg"></i></button>' +
        '<button class="bxm-copy-btn text-warning" data-reject="' + r.id + '" title="Reject"><i class="bi bi-x-lg"></i></button>' +
        '<button class="bxm-copy-btn text-danger" data-del="' + r.id + '" title="Delete"><i class="bi bi-trash"></i></button>' +
        '</div>';
      return '<tr>' +
        '<td>' + window.BXM.escapeHtml(r.userName || 'Customer') + '</td>' +
        '<td class="text-truncate" style="max-width:160px">' + window.BXM.escapeHtml(r.productTitle || '-') + '</td>' +
        '<td>' + stars(Number(r.rating) || 0) + '</td>' +
        '<td class="text-truncate" style="max-width:240px">' + window.BXM.escapeHtml(r.comment || '') + '</td>' +
        '<td>' + window.BXMAdmin.fmtDate(r.createdAt) + '</td>' +
        '<td>' + window.BXM.statusBadge(r.status || 'pending') + '</td>' +
        '<td>' + actions + '</td></tr>';
    }).join('');
    window.BXMAdmin.renderPager('reviewsPager', slice.page, slice.pages, function (p) { page = p; render(); });
  }

  document.getElementById('reviewsTable').addEventListener('click', function (e) {
    var btn = e.target.closest('button');
    if (!btn) return;
    var id = btn.getAttribute('data-approve') || btn.getAttribute('data-reject') || btn.getAttribute('data-del');
    if (!id) return;
    if (btn.hasAttribute('data-approve')) {
      db.ref('reviews/' + id).update({ status: 'approved' }).then(function () {
        var r = reviews.filter(function (x) { return x.id === id; })[0];
        if (r && r.uid) {
          window.BXM.pushNotification(r.uid, {
            title: 'Review approved',
            message: 'Your review for ' + (r.productTitle || 'a product') + ' is now live.',
            type: 'success',
            link: window.BXM.url('reviews.php')
          });
        }
        window.BXM.toast('Review approved', 'success');
        load();
      });
    } else if (btn.hasAttribute('data-reject')) {
      db.ref('reviews/' + id).update({ status: 'rejected' }).then(function () {
        var r = reviews.filter(function (x) { return x.id === id; })[0];
        if (r && r.uid) {
          window.BXM.pushNotification(r.uid, {
            title: 'Review not published',
            message: 'Your review for ' + (r.productTitle || 'a product') + ' was not approved.',
            type: 'warning',
            link: window.BXM.url('reviews.php')
          });
        }
        window.BXM.toast('Review rejected', 'info');
        load();
      });
    } else if (btn.hasAttribute('data-del')) {
      window.BXM.confirmDialog('Delete this review permanently?', { confirmText: 'Delete', danger: true }).then(function (ok) {
        if (!ok) return;
        db.ref('reviews/' + id).remove().then(function () { window.BXM.toast('Review deleted', 'info'); load(); });
      });
    }
  });

  document.querySelectorAll('[data-rfilter]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      filter = btn.getAttribute('data-rfilter');
      page = 1;
      document.querySelectorAll('[data-rfilter]').forEach(function (b) { b.classList.remove('active', 'bxm-btn-primary'); b.classList.add('bxm-btn-outline'); });
      btn.classList.add('active', 'bxm-btn-primary');
      render();
    });
  });

  function load() {
    db.ref('reviews').once('value').then(function (s) {
      reviews = [];
      s.forEach(function (c) { var r = c.val() || {}; r.id = c.key; reviews.push(r); });
      reviews.sort(function (a, b) { return (b.createdAt || 0) - (a.createdAt || 0); });
      render();
    });
  }

  load();
});
</script>
HTML;
require_once __DIR__ . '/../includes/admin-footer.php';
?>
