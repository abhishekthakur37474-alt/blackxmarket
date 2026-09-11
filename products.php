<?php
$pageTitle = 'Products';
$pageDescription = 'Browse premium digital gaming products, keys, guides and assets at BLACK X MARKET.';
$activePage = 'products';
require_once __DIR__ . '/includes/header.php';
$initialQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
$initialCategory = isset($_GET['category']) ? trim($_GET['category']) : '';
?>
<div class="container" id="bxmConfigNotice"></div>

<section class="bxm-section-sm">
  <div class="container">
    <div class="mb-4">
      <h1 class="bxm-section-title">All Products</h1>
      <p class="bxm-section-sub mb-0">Browse our complete digital collection.</p>
    </div>

    <div class="bxm-card p-3 p-md-4 mb-4">
      <div class="row g-3 align-items-end">
        <div class="col-12 col-lg-4">
          <label class="form-label">Search</label>
          <input type="search" class="form-control" id="filterSearch" placeholder="Search digital products..." value="<?= bxm_e($initialQuery) ?>">
        </div>
        <div class="col-6 col-lg-2">
          <label class="form-label">Category</label>
          <select class="form-select" id="filterCategory">
            <option value="">All</option>
          </select>
        </div>
        <div class="col-6 col-lg-2">
          <label class="form-label">Sort</label>
          <select class="form-select" id="filterSort">
            <option value="newest">Newest</option>
            <option value="price_asc">Price: Low to High</option>
            <option value="price_desc">Price: High to Low</option>
            <option value="popular">Most Popular</option>
            <option value="discount">Highest Discount</option>
          </select>
        </div>
        <div class="col-6 col-lg-2">
          <label class="form-label">Min Price</label>
          <input type="number" min="0" class="form-control" id="filterMin" placeholder="0">
        </div>
        <div class="col-6 col-lg-2">
          <label class="form-label">Max Price</label>
          <input type="number" min="0" class="form-control" id="filterMax" placeholder="9999">
        </div>
      </div>
      <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="filterDiscount">
          <label class="form-check-label text-secondary" for="filterDiscount">Discounted only</label>
        </div>
        <button class="bxm-btn bxm-btn-ghost bxm-btn-sm" type="button" id="filterReset"><i class="bi bi-arrow-counterclockwise"></i> Reset</button>
      </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
      <span class="text-secondary small" id="resultCount">Loading products...</span>
    </div>

    <div class="row g-3" id="productsGrid">
      <?php for ($i = 0; $i < 8; $i++): ?>
        <div class="col-6 col-md-4 col-lg-3"><div class="bxm-skeleton" style="height:280px"></div></div>
      <?php endfor; ?>
    </div>

    <div class="text-center mt-4">
      <button class="bxm-btn bxm-btn-outline" id="loadMoreBtn" hidden>Load More</button>
    </div>
  </div>
</section>
<?php
$inlineScript = <<<'HTML'
<script>
(function () {
  var all = [];
  var filtered = [];
  var shown = 0;
  var PAGE = 8;
  var initialQuery = document.getElementById('filterSearch').value || '';

  function applyFilters() {
    var q = (document.getElementById('filterSearch').value || '').toLowerCase().trim();
    var cat = document.getElementById('filterCategory').value;
    var min = parseFloat(document.getElementById('filterMin').value);
    var max = parseFloat(document.getElementById('filterMax').value);
    var onlyDiscount = document.getElementById('filterDiscount').checked;
    var sort = document.getElementById('filterSort').value;

    filtered = all.filter(function (p) {
      var title = (p.title || '').toLowerCase();
      var category = (p.category || '').toLowerCase();
      var desc = (p.description || '').toLowerCase();
      if (q && title.indexOf(q) === -1 && category.indexOf(q) === -1 && desc.indexOf(q) === -1) return false;
      if (cat && p.category !== cat) return false;
      var price = window.BXM.toNumber(p.discountedPrice || p.originalPrice);
      if (!isNaN(min) && price < min) return false;
      if (!isNaN(max) && price > max) return false;
      if (onlyDiscount && window.BXM.discountPercent(p) <= 0) return false;
      return true;
    });

    filtered.sort(function (a, b) {
      var pa = window.BXM.toNumber(a.discountedPrice || a.originalPrice);
      var pb = window.BXM.toNumber(b.discountedPrice || b.originalPrice);
      if (sort === 'price_asc') return pa - pb;
      if (sort === 'price_desc') return pb - pa;
      if (sort === 'discount') return window.BXM.discountPercent(b) - window.BXM.discountPercent(a);
      if (sort === 'popular') return window.BXM.toNumber(b.soldCount || 0) - window.BXM.toNumber(a.soldCount || 0);
      return window.BXM.toNumber(b.createdAt || 0) - window.BXM.toNumber(a.createdAt || 0);
    });

    shown = 0;
    document.getElementById('productsGrid').innerHTML = '';
    loadMore();
  }

  function loadMore() {
    var grid = document.getElementById('productsGrid');
    var next = filtered.slice(shown, shown + PAGE);
    if (shown === 0 && !filtered.length) {
      window.BXM.renderProducts(grid, []);
      document.getElementById('resultCount').textContent = '0 products found';
      document.getElementById('loadMoreBtn').hidden = true;
      return;
    }
    var html = '';
    next.forEach(function (p) {
      html += '<div class="col-6 col-md-4 col-lg-3">' + window.BXM.productCardHTML(p) + '</div>';
    });
    grid.insertAdjacentHTML('beforeend', html);
    shown += next.length;
    document.getElementById('resultCount').textContent = shown + ' of ' + filtered.length + ' products';
    document.getElementById('loadMoreBtn').hidden = shown >= filtered.length;
  }

  ['filterSearch','filterCategory','filterSort','filterMin','filterMax','filterDiscount'].forEach(function (id) {
    var el = document.getElementById(id);
    el.addEventListener(id === 'filterSearch' ? 'input' : 'change', function () {
      if (id === 'filterSearch') { clearTimeout(window.__bxmT); window.__bxmT = setTimeout(applyFilters, 250); }
      else applyFilters();
    });
  });

  document.getElementById('loadMoreBtn').addEventListener('click', loadMore);
  document.getElementById('filterReset').addEventListener('click', function () {
    document.getElementById('filterSearch').value = '';
    document.getElementById('filterCategory').value = '';
    document.getElementById('filterSort').value = 'newest';
    document.getElementById('filterMin').value = '';
    document.getElementById('filterMax').value = '';
    document.getElementById('filterDiscount').checked = false;
    applyFilters();
  });

  window.BXM.onAuth(function () {
    if (!window.BXM.firebaseReady) return;
    window.BXM.db.ref('products').once('value').then(function (snap) {
      snap.forEach(function (child) {
        var p = child.val() || {};
        p.id = child.key;
        if (p.status === 'active') all.push(p);
      });
      var cats = {};
      all.forEach(function (p) { if (p.category) cats[p.category] = true; });
      var sel = document.getElementById('filterCategory');
      Object.keys(cats).sort().forEach(function (c) {
        var opt = document.createElement('option');
        opt.value = c;
        opt.textContent = c;
        sel.appendChild(opt);
      });
      if (initialQuery) document.getElementById('filterSearch').value = initialQuery;
      applyFilters();
    });
  });
})();
</script>
HTML;
include __DIR__ . '/includes/footer.php';
?>
