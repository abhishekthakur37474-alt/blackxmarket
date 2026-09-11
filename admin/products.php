<?php
$adminTitle = 'Manage Products';
$adminActive = 'products';
require_once __DIR__ . '/../includes/admin-header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div>
    <h1 class="bxm-admin-page-title">Products</h1>
    <p class="bxm-admin-page-sub mb-0">Create, edit and manage your digital products.</p>
  </div>
  <button class="bxm-btn bxm-btn-primary" id="addProductBtn"><i class="bi bi-plus-lg"></i> Add Product</button>
</div>

<div class="bxm-form-card mb-3">
  <div class="row g-3">
    <div class="col-md-6"><input type="search" class="form-control" id="productSearch" placeholder="Search products..."></div>
    <div class="col-md-3"><select class="form-select" id="productStatusFilter"><option value="">All Statuses</option><option value="active">Active</option><option value="inactive">Inactive</option><option value="out_of_stock">Out of Stock</option></select></div>
    <div class="col-md-3"><select class="form-select" id="productSort"><option value="newest">Newest</option><option value="title">Title A-Z</option><option value="price">Price</option></select></div>
  </div>
</div>

<div class="bxm-table-wrap">
  <div class="table-responsive">
    <table class="table bxm-table align-middle">
      <thead><tr><th>Thumbnail</th><th>Title</th><th>Category</th><th>Original</th><th>Discounted</th><th>Discount</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
      <tbody id="productsTable"><tr><td colspan="9" class="text-center text-muted py-4">Loading...</td></tr></tbody>
    </table>
  </div>
</div>
<div class="bxm-pager d-flex justify-content-center gap-1 mt-3" id="productsPager"></div>

<div class="modal fade" id="productModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content bxm-modal-content">
      <div class="modal-header"><h5 class="modal-title" id="productModalTitle">Add Product</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <input type="hidden" id="productId">
        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label">Title</label>
            <input type="text" class="form-control" id="pTitle" placeholder="Product title">
          </div>
          <div class="col-md-4">
            <label class="form-label">Category</label>
            <input type="text" class="form-control" id="pCategory" placeholder="e.g. Game Keys">
          </div>
          <div class="col-md-6">
            <label class="form-label">Original Price</label>
            <input type="number" min="0" step="0.01" class="form-control" id="pOriginal" placeholder="1000">
          </div>
          <div class="col-md-6">
            <label class="form-label">Discounted Price</label>
            <input type="number" min="0" step="0.01" class="form-control" id="pDiscounted" placeholder="700">
          </div>
          <div class="col-md-4">
            <label class="form-label">Discount %</label>
            <input type="text" class="form-control" id="pPercent" readonly>
          </div>
          <div class="col-md-4">
            <label class="form-label">Status</label>
            <select class="form-select" id="pStatus"><option value="active">Active</option><option value="inactive">Inactive</option><option value="out_of_stock">Out of Stock</option></select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Thumbnail</label>
            <input type="file" class="form-control" id="pThumbnail" accept="image/*">
          </div>
          <div class="col-12"><img id="pThumbPreview" class="bxm-upload-preview" hidden alt="Thumbnail preview"></div>
          <div class="col-12">
            <label class="form-label">Description</label>
            <textarea class="form-control" id="pDescription" rows="4" placeholder="Product description"></textarea>
          </div>
          <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <label class="form-label mb-0">Additional Details</label>
              <button type="button" class="bxm-btn bxm-btn-outline bxm-btn-sm" id="addDetailBtn"><i class="bi bi-plus"></i> Add Field</button>
            </div>
            <div id="detailsList"></div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="bxm-btn bxm-btn-outline" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="bxm-btn bxm-btn-primary" id="saveProductBtn">Save Product</button>
      </div>
    </div>
  </div>
</div>
<?php
$inlineScript = <<<'HTML'
<script>
window.BXMAdmin.ready(function () {
  var db = window.BXM.db;
  var products = [];
  var editingId = null;
  var thumbnailUrl = '';
  var page = 1;
  var PER_PAGE = 15;

  function discount(orig, disc) {
    orig = Number(orig); disc = Number(disc);
    if (!orig) return 0;
    return Math.round(((orig - disc) / orig) * 100);
  }

  function detailRow(name, value) {
    var div = document.createElement('div');
    div.className = 'bxm-detail-field';
    div.innerHTML = '<i class="bi bi-grip-vertical drag"></i>' +
      '<input type="text" class="form-control form-control-sm" placeholder="Field name" value="' + window.BXM.escapeHtml(name || '') + '" data-detail-name>' +
      '<input type="text" class="form-control form-control-sm" placeholder="Value" value="' + window.BXM.escapeHtml(value || '') + '" data-detail-value>' +
      '<button type="button" class="bxm-copy-btn" data-remove-detail><i class="bi bi-trash"></i></button>';
    document.getElementById('detailsList').appendChild(div);
  }

  function render() {
    var q = (document.getElementById('productSearch').value || '').toLowerCase();
    var status = document.getElementById('productStatusFilter').value;
    var sort = document.getElementById('productSort').value;
    var list = products.filter(function (p) {
      if (status && p.status !== status) return false;
      if (q && (p.title || '').toLowerCase().indexOf(q) === -1 && (p.category || '').toLowerCase().indexOf(q) === -1) return false;
      return true;
    });
    if (sort === 'title') list.sort(function (a, b) { return (a.title || '').localeCompare(b.title || ''); });
    else if (sort === 'price') list.sort(function (a, b) { return window.BXM.toNumber(a.discountedPrice) - window.BXM.toNumber(b.discountedPrice); });
    else list.sort(function (a, b) { return (b.createdAt || 0) - (a.createdAt || 0); });

    var slice = window.BXMAdmin.pageSlice(list, page, PER_PAGE);
    page = slice.page;
    var host = document.getElementById('productsTable');
    if (!slice.items.length) { host.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-4">No products found.</td></tr>'; }
    else host.innerHTML = slice.items.map(function (p) {
      var active = p.status === 'active';
      return '<tr>' +
        '<td><img src="' + window.BXM.escapeHtml(p.thumbnailUrl || '') + '" class="bxm-table-thumb" alt=""></td>' +
        '<td class="text-truncate" style="max-width:200px">' + window.BXM.escapeHtml(p.title || '') + '</td>' +
        '<td>' + window.BXM.escapeHtml(p.category || '-') + '</td>' +
        '<td>' + window.BXM.money(p.originalPrice) + '</td>' +
        '<td>' + window.BXM.money(p.discountedPrice) + '</td>' +
        '<td>' + (window.BXM.toNumber(p.discountPercent) || discount(p.originalPrice, p.discountedPrice)) + '%</td>' +
        '<td>' + window.BXM.statusBadge(p.status || 'active') + '</td>' +
        '<td>' + window.BXMAdmin.fmtDate(p.createdAt) + '</td>' +
        '<td><div class="bxm-table-actions">' +
        '<button class="bxm-copy-btn" data-edit="' + p.id + '" title="Edit"><i class="bi bi-pencil"></i></button>' +
        '<button class="bxm-copy-btn" data-dup="' + p.id + '" title="Duplicate"><i class="bi bi-files"></i></button>' +
        '<button class="bxm-copy-btn" data-toggle="' + p.id + '" title="' + (active ? 'Deactivate' : 'Activate') + '"><i class="bi ' + (active ? 'bi-eye-slash' : 'bi-eye') + '"></i></button>' +
        '<button class="bxm-copy-btn text-danger" data-del="' + p.id + '" title="Delete"><i class="bi bi-trash"></i></button>' +
        '</div></td></tr>';
    }).join('');
    window.BXMAdmin.renderPager('productsPager', slice.page, slice.pages, function (p) { page = p; render(); });
  }

  function loadProducts() {
    db.ref('products').once('value').then(function (s) {
      products = [];
      s.forEach(function (c) { var p = c.val() || {}; p.id = c.key; products.push(p); });
      render();
    });
  }

  function openModal(product) {
    editingId = product ? product.id : null;
    thumbnailUrl = product ? (product.thumbnailUrl || '') : '';
    document.getElementById('productModalTitle').textContent = product ? 'Edit Product' : 'Add Product';
    document.getElementById('pTitle').value = product ? (product.title || '') : '';
    document.getElementById('pCategory').value = product ? (product.category || '') : '';
    document.getElementById('pOriginal').value = product ? (product.originalPrice || '') : '';
    document.getElementById('pDiscounted').value = product ? (product.discountedPrice || '') : '';
    document.getElementById('pDescription').value = product ? (product.description || '') : '';
    document.getElementById('pStatus').value = product ? (product.status || 'active') : 'active';
    document.getElementById('pThumbnail').value = '';
    document.getElementById('detailsList').innerHTML = '';
    var prev = document.getElementById('pThumbPreview');
    if (thumbnailUrl) { prev.src = thumbnailUrl; prev.hidden = false; } else { prev.hidden = true; }
    if (product && product.additionalDetails) {
      var fields = Object.keys(product.additionalDetails).map(function (k) { return product.additionalDetails[k]; });
      fields.sort(function (a, b) { return window.BXM.toNumber(a.order) - window.BXM.toNumber(b.order); });
      fields.forEach(function (f) { detailRow(f.name, f.value); });
    }
    updatePercent();
    new bootstrap.Modal(document.getElementById('productModal')).show();
  }

  function updatePercent() {
    var o = document.getElementById('pOriginal').value;
    var d = document.getElementById('pDiscounted').value;
    document.getElementById('pPercent').value = (o && d !== '') ? discount(o, d) + '%' : '';
  }

  document.getElementById('pOriginal').addEventListener('input', updatePercent);
  document.getElementById('pDiscounted').addEventListener('input', updatePercent);
  document.getElementById('addProductBtn').addEventListener('click', function () { openModal(null); });
  document.getElementById('addDetailBtn').addEventListener('click', function () { detailRow('', ''); });
  document.getElementById('detailsList').addEventListener('click', function (e) {
    if (e.target.closest('[data-remove-detail]')) e.target.closest('.bxm-detail-field').remove();
  });
  document.getElementById('pThumbnail').addEventListener('change', function (e) {
    var file = e.target.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function (ev) { var p = document.getElementById('pThumbPreview'); p.src = ev.target.result; p.hidden = false; };
    reader.readAsDataURL(file);
  });

  ['productSearch', 'productStatusFilter', 'productSort'].forEach(function (id) {
    document.getElementById(id).addEventListener(id === 'productSearch' ? 'input' : 'change', function () { page = 1; render(); });
  });

  document.getElementById('productsTable').addEventListener('click', function (e) {
    var btn = e.target.closest('button');
    if (!btn) return;
    if (btn.hasAttribute('data-edit')) {
      var p = products.filter(function (x) { return x.id === btn.getAttribute('data-edit'); })[0];
      if (p) openModal(p);
    } else if (btn.hasAttribute('data-dup')) {
      var src = products.filter(function (x) { return x.id === btn.getAttribute('data-dup'); })[0];
      if (!src) return;
      var copy = Object.assign({}, src);
      delete copy.id;
      copy.title = (src.title || '') + ' (Copy)';
      copy.createdAt = Date.now();
      copy.updatedAt = Date.now();
      db.ref('products').push(copy).then(function () { window.BXM.toast('Product duplicated', 'success'); loadProducts(); });
    } else if (btn.hasAttribute('data-toggle')) {
      var item = products.filter(function (x) { return x.id === btn.getAttribute('data-toggle'); })[0];
      if (!item) return;
      var next = item.status === 'active' ? 'inactive' : 'active';
      db.ref('products/' + item.id).update({ status: next, updatedAt: Date.now() }).then(function () { window.BXM.toast('Status updated', 'success'); loadProducts(); });
    } else if (btn.hasAttribute('data-del')) {
      var id = btn.getAttribute('data-del');
      window.BXM.confirmDialog('Delete this product permanently?', { confirmText: 'Delete', danger: true }).then(function (ok) {
        if (!ok) return;
        db.ref('products/' + id).remove().then(function () { window.BXM.toast('Product deleted', 'info'); loadProducts(); });
      });
    }
  });

  document.getElementById('saveProductBtn').addEventListener('click', function () {
    var title = document.getElementById('pTitle').value.trim();
    var original = document.getElementById('pOriginal').value;
    var discounted = document.getElementById('pDiscounted').value;
    if (!title) { window.BXM.toast('Title is required', 'warning'); return; }
    if (original === '' || discounted === '') { window.BXM.toast('Both prices are required', 'warning'); return; }

    var details = {};
    var order = 0;
    document.querySelectorAll('#detailsList .bxm-detail-field').forEach(function (row) {
      var name = row.querySelector('[data-detail-name]').value.trim();
      var value = row.querySelector('[data-detail-value]').value.trim();
      if (name) { details['f' + (order + 1)] = { name: name, value: value, order: order }; order++; }
    });

    var file = document.getElementById('pThumbnail').files[0];
    var btn = document.getElementById('saveProductBtn');
    btn.disabled = true;
    window.BXM.loader(true);

    var thumbPromise = file ? window.BXM.uploadImage(file, 'product') : Promise.resolve(null);
    thumbPromise.then(function (up) {
      if (up && !up.ok) throw new Error(up.error || 'Thumbnail upload failed');
      var now = Date.now();
      var data = {
        title: title,
        category: document.getElementById('pCategory').value.trim(),
        description: document.getElementById('pDescription').value.trim(),
        originalPrice: window.BXM.toNumber(original),
        discountedPrice: window.BXM.toNumber(discounted),
        discountPercent: discount(original, discounted),
        status: document.getElementById('pStatus').value,
        additionalDetails: details,
        updatedAt: now
      };
      if (up && up.url) data.thumbnailUrl = up.url;
      if (!editingId) { data.createdAt = now; data.thumbnailUrl = data.thumbnailUrl || ''; }
      var op = editingId ? db.ref('products/' + editingId).update(data) : db.ref('products').push(data);
      return op;
    }).then(function () {
      window.BXM.loader(false);
      btn.disabled = false;
      bootstrap.Modal.getInstance(document.getElementById('productModal')).hide();
      window.BXM.toast(editingId ? 'Product updated' : 'Product created', 'success');
      loadProducts();
    }).catch(function (err) {
      window.BXM.loader(false);
      btn.disabled = false;
      window.BXM.toast(err.message || 'Could not save product', 'danger');
    });
  });

  loadProducts();
});
</script>
HTML;
require_once __DIR__ . '/../includes/admin-footer.php';
?>
