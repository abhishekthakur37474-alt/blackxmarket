<?php
$adminTitle = 'Manage Carousels';
$adminActive = 'carousels';
require_once __DIR__ . '/../includes/admin-header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div>
    <h1 class="bxm-admin-page-title">Carousels</h1>
    <p class="bxm-admin-page-sub mb-0">Manage the homepage banners and featured slides.</p>
  </div>
  <button class="bxm-btn bxm-btn-primary" id="addCarouselBtn"><i class="bi bi-plus-lg"></i> Add Slide</button>
</div>

<div class="row g-3" id="carouselList">
  <div class="col-12"><div class="bxm-skeleton" style="height:160px"></div></div>
</div>

<div class="modal fade" id="carouselModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content bxm-modal-content">
      <div class="modal-header"><h5 class="modal-title" id="carouselModalTitle">Add Slide</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <input type="hidden" id="cId">
        <div class="row g-3">
          <div class="col-12"><label class="form-label">Title</label><input type="text" class="form-control" id="cTitle"></div>
          <div class="col-12"><label class="form-label">Subtitle</label><input type="text" class="form-control" id="cDescription"></div>
          <div class="col-md-6"><label class="form-label">Button Text</label><input type="text" class="form-control" id="cButtonText" placeholder="Explore Now"></div>
          <div class="col-md-6"><label class="form-label">Button Link</label><input type="text" class="form-control" id="cLink" placeholder="products.php"></div>
          <div class="col-md-6"><label class="form-label">Sort Order</label><input type="number" class="form-control" id="cOrder" value="0"></div>
          <div class="col-md-6"><label class="form-label">Status</label><select class="form-select" id="cStatus"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
          <div class="col-12"><label class="form-label">Thumbnail</label><input type="file" class="form-control" id="cImage" accept="image/*"></div>
          <div class="col-12"><img id="cPreview" class="bxm-upload-preview" hidden alt="Preview"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="bxm-btn bxm-btn-outline" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="bxm-btn bxm-btn-primary" id="saveCarouselBtn">Save Slide</button>
      </div>
    </div>
  </div>
</div>
<?php
$inlineScript = <<<'HTML'
<script>
window.BXMAdmin.ready(function () {
  var db = window.BXM.db;
  var slides = [];
  var editingId = null;
  var imageUrl = '';

  function render() {
    var host = document.getElementById('carouselList');
    if (!slides.length) { host.innerHTML = '<div class="col-12"><div class="bxm-state-box text-center"><i class="bi bi-images bxm-state-icon"></i><p class="text-secondary mb-0">No carousel slides yet.</p></div></div>'; return; }
    host.innerHTML = slides.map(function (s) {
      return '<div class="col-lg-6"><div class="bxm-card p-3 d-flex gap-3 align-items-center">' +
        '<img src="' + window.BXM.escapeHtml(s.imageUrl || '') + '" class="bxm-table-thumb" style="width:90px;height:60px" alt="">' +
        '<div class="flex-grow-1 min-w-0"><div class="fw-semibold text-truncate">' + window.BXM.escapeHtml(s.title || '') + '</div>' +
        '<div class="small text-muted text-truncate">' + window.BXM.escapeHtml(s.description || '') + '</div>' +
        '<div class="mt-1">' + window.BXM.statusBadge(s.status) + ' <span class="small text-muted ms-1">Order ' + window.BXM.toNumber(s.order) + '</span></div></div>' +
        '<div class="bxm-table-actions flex-column">' +
        '<button class="bxm-copy-btn" data-edit="' + s.id + '"><i class="bi bi-pencil"></i></button>' +
        '<button class="bxm-copy-btn" data-toggle="' + s.id + '"><i class="bi ' + (s.status === 'active' ? 'bi-eye-slash' : 'bi-eye') + '"></i></button>' +
        '<button class="bxm-copy-btn text-danger" data-del="' + s.id + '"><i class="bi bi-trash"></i></button>' +
        '</div></div></div>';
    }).join('');
  }

  function load() {
    db.ref('carousels').once('value').then(function (s) {
      slides = [];
      s.forEach(function (c) { var v = c.val() || {}; v.id = c.key; slides.push(v); });
      slides.sort(function (a, b) { return window.BXM.toNumber(a.order) - window.BXM.toNumber(b.order); });
      render();
    });
  }

  function openModal(slide) {
    editingId = slide ? slide.id : null;
    imageUrl = slide ? (slide.imageUrl || '') : '';
    document.getElementById('carouselModalTitle').textContent = slide ? 'Edit Slide' : 'Add Slide';
    document.getElementById('cTitle').value = slide ? (slide.title || '') : '';
    document.getElementById('cDescription').value = slide ? (slide.description || '') : '';
    document.getElementById('cButtonText').value = slide ? (slide.buttonText || '') : '';
    document.getElementById('cLink').value = slide ? (slide.link || '') : '';
    document.getElementById('cOrder').value = slide ? window.BXM.toNumber(slide.order) : slides.length;
    document.getElementById('cStatus').value = slide ? (slide.status || 'active') : 'active';
    document.getElementById('cImage').value = '';
    var prev = document.getElementById('cPreview');
    if (imageUrl) { prev.src = imageUrl; prev.hidden = false; } else prev.hidden = true;
    new bootstrap.Modal(document.getElementById('carouselModal')).show();
  }

  document.getElementById('addCarouselBtn').addEventListener('click', function () { openModal(null); });
  document.getElementById('cImage').addEventListener('change', function (e) {
    var file = e.target.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function (ev) { var p = document.getElementById('cPreview'); p.src = ev.target.result; p.hidden = false; };
    reader.readAsDataURL(file);
  });

  document.getElementById('carouselList').addEventListener('click', function (e) {
    var btn = e.target.closest('button');
    if (!btn) return;
    if (btn.hasAttribute('data-edit')) {
      var s = slides.filter(function (x) { return x.id === btn.getAttribute('data-edit'); })[0];
      if (s) openModal(s);
    } else if (btn.hasAttribute('data-toggle')) {
      var item = slides.filter(function (x) { return x.id === btn.getAttribute('data-toggle'); })[0];
      db.ref('carousels/' + item.id).update({ status: item.status === 'active' ? 'inactive' : 'active' }).then(function () { window.BXM.toast('Status updated', 'success'); load(); });
    } else if (btn.hasAttribute('data-del')) {
      var id = btn.getAttribute('data-del');
      window.BXM.confirmDialog('Delete this carousel slide?', { confirmText: 'Delete', danger: true }).then(function (ok) {
        if (!ok) return;
        db.ref('carousels/' + id).remove().then(function () { window.BXM.toast('Slide deleted', 'info'); load(); });
      });
    }
  });

  document.getElementById('saveCarouselBtn').addEventListener('click', function () {
    var title = document.getElementById('cTitle').value.trim();
    if (!title) { window.BXM.toast('Title is required', 'warning'); return; }
    var file = document.getElementById('cImage').files[0];
    var btn = document.getElementById('saveCarouselBtn');
    btn.disabled = true;
    window.BXM.loader(true);
    var imgPromise = file ? window.BXM.uploadImage(file, 'carousel') : Promise.resolve(null);
    imgPromise.then(function (up) {
      if (up && !up.ok) throw new Error(up.error || 'Image upload failed');
      var data = {
        title: title,
        description: document.getElementById('cDescription').value.trim(),
        buttonText: document.getElementById('cButtonText').value.trim(),
        link: document.getElementById('cLink').value.trim(),
        order: window.BXM.toNumber(document.getElementById('cOrder').value),
        status: document.getElementById('cStatus').value,
        updatedAt: Date.now()
      };
      if (up && up.url) data.imageUrl = up.url;
      if (!editingId) { data.createdAt = Date.now(); data.imageUrl = data.imageUrl || ''; }
      return editingId ? db.ref('carousels/' + editingId).update(data) : db.ref('carousels').push(data);
    }).then(function () {
      window.BXM.loader(false);
      btn.disabled = false;
      bootstrap.Modal.getInstance(document.getElementById('carouselModal')).hide();
      window.BXM.toast(editingId ? 'Slide updated' : 'Slide created', 'success');
      load();
    }).catch(function (err) {
      window.BXM.loader(false);
      btn.disabled = false;
      window.BXM.toast(err.message || 'Could not save slide', 'danger');
    });
  });

  load();
});
</script>
HTML;
require_once __DIR__ . '/../includes/admin-footer.php';
?>
