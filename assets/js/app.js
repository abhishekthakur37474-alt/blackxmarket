(function () {
  'use strict';

  var cfg = window.BXM_APP || {};
  var base = cfg.base || '';
  var currency = cfg.currency || '\u20B9';
  var fbReady = window.BXM_FIREBASE_READY === true;
  var auth = fbReady ? window.BXM_FB.auth : null;
  var db = fbReady ? window.BXM_FB.db : null;

  var state = {
    user: null,
    profile: null,
    cart: {},
    wishlist: {},
    notifications: {},
    unread: 0,
    authResolved: false
  };
  var authWaiters = [];

  function url(path) {
    return base + '/' + String(path || '').replace(/^\//, '');
  }

  function api(path) {
    return url('api/' + String(path || '').replace(/^\//, ''));
  }

  function escapeHtml(value) {
    return String(value === null || value === undefined ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function toNumber(value) {
    var n = parseFloat(value);
    return isNaN(n) ? 0 : n;
  }

  function money(value) {
    return currency + toNumber(value).toLocaleString('en-IN', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }

  function plainNumber(value) {
    return toNumber(value).toLocaleString('en-IN', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }

  function toast(message, type, timeout) {
    var stack = document.getElementById('bxmToastStack');
    if (!stack) {
      return;
    }
    var icons = {
      success: 'bi-check-circle-fill',
      danger: 'bi-x-circle-fill',
      warning: 'bi-exclamation-triangle-fill',
      info: 'bi-info-circle-fill'
    };
    var el = document.createElement('div');
    el.className = 'bxm-toast toast-' + (type || 'info');
    el.innerHTML = '<i class="bi ' + (icons[type] || icons.info) + '"></i><div>' + escapeHtml(message) + '</div>';
    stack.appendChild(el);
    setTimeout(function () {
      el.style.opacity = '0';
      el.style.transform = 'translateX(20px)';
      el.style.transition = 'all .25s ease';
      setTimeout(function () {
        if (el.parentNode) {
          el.parentNode.removeChild(el);
        }
      }, 250);
    }, timeout || 4000);
  }

  function loader(show) {
    var el = document.getElementById('bxmLoader');
    if (el) {
      el.classList.toggle('show', !!show);
    }
  }

  function copyText(text) {
    var value = String(text || '');
    if (navigator.clipboard && window.isSecureContext) {
      return navigator.clipboard.writeText(value).then(function () {
        toast('Copied to clipboard', 'success');
      });
    }
    var ta = document.createElement('textarea');
    ta.value = value;
    ta.style.position = 'fixed';
    ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.select();
    try {
      document.execCommand('copy');
      toast('Copied to clipboard', 'success');
    } catch (e) {
      toast('Copy failed', 'danger');
    }
    document.body.removeChild(ta);
    return Promise.resolve();
  }

  function confirmDialog(message, options) {
    options = options || {};
    return new Promise(function (resolve) {
      var id = 'bxmConfirm' + Date.now();
      var wrap = document.createElement('div');
      wrap.className = 'modal fade';
      wrap.id = id;
      wrap.tabIndex = -1;
      wrap.innerHTML =
        '<div class="modal-dialog modal-dialog-centered">' +
        '<div class="modal-content bxm-modal-content">' +
        '<div class="modal-header"><h5 class="modal-title">' + escapeHtml(options.title || 'Please confirm') + '</h5>' +
        '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>' +
        '<div class="modal-body"><p class="mb-0 text-secondary">' + escapeHtml(message) + '</p></div>' +
        '<div class="modal-footer">' +
        '<button type="button" class="bxm-btn bxm-btn-outline" data-bs-dismiss="modal">' + escapeHtml(options.cancelText || 'Cancel') + '</button>' +
        '<button type="button" class="bxm-btn ' + (options.danger ? 'bxm-btn-danger' : 'bxm-btn-primary') + '" data-confirm>' + escapeHtml(options.confirmText || 'Confirm') + '</button>' +
        '</div></div></div>';
      document.body.appendChild(wrap);
      var modal = new bootstrap.Modal(wrap);
      var decided = false;
      wrap.querySelector('[data-confirm]').addEventListener('click', function () {
        decided = true;
        modal.hide();
        resolve(true);
      });
      wrap.addEventListener('hidden.bs.modal', function () {
        if (!decided) {
          resolve(false);
        }
        wrap.remove();
      });
      modal.show();
    });
  }

  function statusBadge(status) {
    var map = {
      pending: ['bxm-badge-pending', 'Pending'],
      payment_submitted: ['bxm-badge-pending', 'Payment Submitted'],
      under_review: ['bxm-badge-pending', 'Under Review'],
      approved: ['bxm-badge-success', 'Approved'],
      completed: ['bxm-badge-success', 'Completed'],
      rejected: ['bxm-badge-danger', 'Rejected'],
      cancelled: ['bxm-badge-info', 'Cancelled'],
      active: ['bxm-badge-success', 'Active'],
      inactive: ['bxm-badge-info', 'Inactive'],
      out_of_stock: ['bxm-badge-danger', 'Out of Stock'],
      open: ['bxm-badge-pending', 'Open'],
      replied: ['bxm-badge-success', 'Replied'],
      closed: ['bxm-badge-info', 'Closed']
    };
    var item = map[String(status)] || ['bxm-badge-info', String(status || 'Unknown')];
    return '<span class="bxm-badge ' + item[0] + '">' + escapeHtml(item[1]) + '</span>';
  }

  function discountPercent(product) {
    if (product.discountPercent !== undefined && product.discountPercent !== null && product.discountPercent !== '') {
      return toNumber(product.discountPercent);
    }
    var original = toNumber(product.originalPrice);
    var discounted = toNumber(product.discountedPrice);
    if (original <= 0) {
      return 0;
    }
    return Math.round(((original - discounted) / original) * 100);
  }

  function productCardHTML(product) {
    var id = product.id;
    var percent = discountPercent(product);
    var wished = !!state.wishlist[id];
    var status = product.status || 'active';
    var outOfStock = status === 'out_of_stock';
    var href = url('product-details.php?id=' + encodeURIComponent(id));
    var html = '';
    html += '<div class="bxm-product-card bxm-fade-in">';
    html += '<div class="bxm-product-thumb">';
    html += '<a href="' + href + '"><img src="' + escapeHtml(product.thumbnailUrl || url('assets/images/logo/logo.jpeg')) + '" alt="' + escapeHtml(product.title || 'Product') + '" loading="lazy"></a>';
    if (percent > 0) {
      html += '<span class="bxm-discount-badge">-' + percent + '%</span>';
    }
    html += '<button type="button" class="bxm-icon-btn bxm-wish-btn' + (wished ? ' active' : '') + '" data-bxm-wish="' + escapeHtml(id) + '" aria-label="Wishlist"><i class="bi ' + (wished ? 'bi-heart-fill' : 'bi-heart') + '"></i></button>';
    html += '</div>';
    html += '<div class="bxm-product-body">';
    html += '<a href="' + href + '" class="bxm-product-title">' + escapeHtml(product.title || 'Untitled') + '</a>';
    html += '<div class="bxm-product-cat">' + escapeHtml(product.category || 'Digital') + '</div>';
    html += '<div class="mb-3 d-flex align-items-center">';
    html += '<span class="bxm-price-current">' + money(product.discountedPrice || product.originalPrice) + '</span>';
    if (toNumber(product.originalPrice) > toNumber(product.discountedPrice)) {
      html += '<span class="bxm-price-original">' + money(product.originalPrice) + '</span>';
    }
    html += '</div>';
    html += '<div class="mt-auto d-flex gap-2">';
    html += '<button type="button" class="bxm-btn bxm-btn-primary flex-grow-1' + (outOfStock ? ' disabled' : '') + '" data-bxm-buy-now="' + escapeHtml(id) + '"' + (outOfStock ? ' disabled' : '') + '>';
    html += '<i class="bi bi-lightning-charge"></i> ' + (outOfStock ? 'Out of Stock' : 'Buy Now');
    html += '</button>';
    html += '<a href="' + href + '" class="bxm-btn bxm-btn-outline" aria-label="View details"><i class="bi bi-arrow-right"></i></a>';
    html += '</div></div></div>';
    return html;
  }

  function renderProducts(container, products) {
    if (!container) {
      return;
    }
    if (!products || !products.length) {
      container.innerHTML = '<div class="col-12"><div class="bxm-state-box text-center"><i class="bi bi-search bxm-state-icon"></i><h5>No products found</h5><p class="text-secondary mb-0">Try another search or browse all products.</p></div></div>';
      return;
    }
    var html = '';
    products.forEach(function (p) {
      html += '<div class="col-6 col-md-4 col-lg-3">' + productCardHTML(p) + '</div>';
    });
    container.innerHTML = html;
  }

  function requireLogin(redirectPath) {
    if (state.user) {
      return true;
    }
    toast('Please login to continue', 'warning');
    var target = redirectPath || (window.location.pathname + window.location.search);
    setTimeout(function () {
      window.location.href = url('auth/login.php?redirect=' + encodeURIComponent(target));
    }, 700);
    return false;
  }

  function getToken() {
    if (!auth || !auth.currentUser) {
      return Promise.resolve('');
    }
    return auth.currentUser.getIdToken().catch(function () {
      return '';
    });
  }

  function csrfToken() {
    return (window.BXM_APP && window.BXM_APP.csrf) || '';
  }

  function csrfHeaders(extra) {
    var headers = extra || {};
    var token = csrfToken();
    if (token) {
      headers['X-CSRF-TOKEN'] = token;
    }
    return headers;
  }

  function apiFetch(path, options) {
    options = options || {};
    return getToken().then(function (token) {
      var headers = csrfHeaders(options.headers || {});
      if (token) {
        headers.Authorization = 'Bearer ' + token;
      }
      if (options.json) {
        headers['Content-Type'] = 'application/json';
      }
      return fetch(api(path), {
        method: options.method || 'GET',
        headers: headers,
        body: options.json ? JSON.stringify(options.json) : options.body
      }).then(function (res) {
        return res.json().catch(function () {
          return { ok: false, error: 'Invalid server response' };
        });
      });
    });
  }

  function uploadImage(file, folder) {
    var fd = new FormData();
    fd.append('image', file);
    fd.append('folder', folder || 'misc');
    return getToken().then(function (token) {
      var headers = csrfHeaders({});
      if (token) {
        headers.Authorization = 'Bearer ' + token;
      }
      return fetch(api('upload-image.php'), { method: 'POST', headers: headers, body: fd }).then(function (res) {
        return res.json().catch(function () {
          return { ok: false, error: 'Upload failed' };
        });
      });
    });
  }

  function addToCart(productId, options) {
    options = options || {};
    if (!requireLogin()) {
      return Promise.resolve(false);
    }
    if (!productId) {
      return Promise.resolve(false);
    }
    var ref = db.ref('carts/' + state.user.uid + '/' + productId);
    return ref.transaction(function (current) {
      if (current) {
        current.quantity = (current.quantity || 0) + 1;
        current.addedAt = Date.now();
        return current;
      }
      return { quantity: 1, addedAt: Date.now() };
    }).then(function () {
      if (!options.silent) {
        toast('Added to cart', 'success');
      }
      return true;
    }).catch(function () {
      toast(options.errorMessage || 'Could not add to cart', 'danger');
      return false;
    });
  }

  function buyNow(productId) {
    if (!requireLogin()) {
      return;
    }
    if (!productId || !state.user) {
      return;
    }
    var payload = {};
    payload[productId] = { quantity: 1, addedAt: Date.now() };
    db.ref('carts/' + state.user.uid).set(payload).then(function () {
      window.location.href = url('payment.php');
    }).catch(function () {
      toast('Could not start payment', 'danger');
    });
  }

  function setCartQuantity(productId, quantity) {
    if (!state.user) {
      return Promise.resolve();
    }
    var ref = db.ref('carts/' + state.user.uid + '/' + productId);
    if (quantity <= 0) {
      return ref.remove();
    }
    return ref.update({ quantity: quantity });
  }

  function removeFromCart(productId) {
    if (!state.user) {
      return Promise.resolve();
    }
    return db.ref('carts/' + state.user.uid + '/' + productId).remove().then(function () {
      toast('Removed from cart', 'info');
    });
  }

  function toggleWishlist(productId) {
    if (!requireLogin()) {
      return Promise.resolve();
    }
    var ref = db.ref('wishlists/' + state.user.uid + '/' + productId);
    return ref.once('value').then(function (snap) {
      if (snap.exists()) {
        return ref.remove().then(function () {
          toast('Removed from wishlist', 'info');
        });
      }
      return ref.set(true).then(function () {
        toast('Added to wishlist', 'success');
      });
    });
  }

  function moveToCart(productId) {
    if (!state.user) {
      return;
    }
    db.ref('wishlists/' + state.user.uid + '/' + productId).remove();
    buyNow(productId);
  }

  function updateCartCount() {
    var count = 0;
    Object.keys(state.cart || {}).forEach(function (key) {
      count += toNumber(state.cart[key] && state.cart[key].quantity) || 1;
    });
    document.querySelectorAll('[data-bxm-cart-count]').forEach(function (el) {
      el.textContent = count;
      el.hidden = count === 0;
    });
  }

  function updateWishlistCount() {
    var count = Object.keys(state.wishlist || {}).length;
    document.querySelectorAll('[data-bxm-wishlist-count]').forEach(function (el) {
      el.textContent = count;
      el.hidden = count === 0;
    });
  }

  function attachCartListener() {
    if (!state.user) {
      return;
    }
    db.ref('carts/' + state.user.uid).on('value', function (snap) {
      state.cart = snap.val() || {};
      updateCartCount();
      document.dispatchEvent(new CustomEvent('bxm:cart', { detail: state.cart }));
    });
  }

  function attachWishlistListener() {
    if (!state.user) {
      return;
    }
    db.ref('wishlists/' + state.user.uid).on('value', function (snap) {
      state.wishlist = snap.val() || {};
      updateWishlistCount();
      document.dispatchEvent(new CustomEvent('bxm:wishlist', { detail: state.wishlist }));
    });
  }

  function notifIcon(type) {
    var map = {
      success: 'bi-check-circle-fill text-success',
      danger: 'bi-x-circle-fill text-danger',
      warning: 'bi-exclamation-triangle-fill text-warning',
      info: 'bi-info-circle-fill text-info'
    };
    return map[type] || map.info;
  }

  function updateNotifCount() {
    document.querySelectorAll('[data-bxm-notif-count]').forEach(function (el) {
      el.textContent = state.unread > 99 ? '99+' : state.unread;
      el.hidden = state.unread === 0;
    });
  }

  function timeAgo(ts) {
    var diff = Date.now() - Number(ts || 0);
    if (!ts || diff < 0) {
      return 'just now';
    }
    var mins = Math.floor(diff / 60000);
    if (mins < 1) return 'just now';
    if (mins < 60) return mins + 'm ago';
    var hours = Math.floor(mins / 60);
    if (hours < 24) return hours + 'h ago';
    var days = Math.floor(hours / 24);
    if (days < 30) return days + 'd ago';
    return new Date(Number(ts)).toLocaleDateString();
  }

  function renderNotifications() {
    var list = Object.keys(state.notifications).map(function (id) {
      var n = state.notifications[id] || {};
      n.id = id;
      return n;
    }).sort(function (a, b) { return Number(b.createdAt || 0) - Number(a.createdAt || 0); });

    state.unread = list.filter(function (n) { return !n.read; }).length;
    updateNotifCount();

    document.querySelectorAll('[data-bxm-notif-list]').forEach(function (host) {
      if (!list.length) {
        host.innerHTML = '<div class="bxm-notif-empty"><i class="bi bi-bell-slash"></i><span>No notifications yet.</span></div>';
        return;
      }
      host.innerHTML = list.slice(0, 10).map(function (n) {
        var unread = !n.read;
        return '<a class="bxm-notif-item' + (unread ? ' unread' : '') + '" href="' + escapeHtml(n.link || '#') + '" data-bxm-notif-read="' + escapeHtml(n.id) + '">' +
          '<span class="bxm-notif-icon"><i class="bi ' + notifIcon(n.type) + '"></i></span>' +
          '<span class="flex-grow-1 min-w-0"><span class="bxm-notif-title">' + escapeHtml(n.title || 'Notification') + '</span>' +
          '<span class="bxm-notif-msg">' + escapeHtml(n.message || '') + '</span>' +
          '<span class="bxm-notif-time">' + escapeHtml(timeAgo(n.createdAt)) + '</span></span>' +
          (unread ? '<span class="bxm-notif-dot"></span>' : '') +
          '</a>';
      }).join('');
    });
  }

  function markNotificationRead(id) {
    if (!state.user || !id) {
      return;
    }
    var n = state.notifications[id];
    if (!n || n.read) {
      return;
    }
    db.ref('notifications/' + state.user.uid + '/' + id + '/read').set(true);
  }

  function markAllNotificationsRead() {
    if (!state.user) {
      return;
    }
    var updates = {};
    Object.keys(state.notifications).forEach(function (id) {
      if (!state.notifications[id].read) {
        updates['notifications/' + state.user.uid + '/' + id + '/read'] = true;
      }
    });
    if (Object.keys(updates).length) {
      db.ref().update(updates);
      toast('All notifications marked as read', 'success');
    }
  }

  function pushNotification(uid, data) {
    if (!db || !uid) {
      return Promise.resolve();
    }
    var payload = {
      title: data.title || 'Notification',
      message: data.message || '',
      type: data.type || 'info',
      link: data.link || '',
      read: false,
      createdAt: Date.now()
    };
    return db.ref('notifications/' + uid).push(payload).catch(function () {});
  }

  function attachNotificationsListener() {
    if (!state.user) {
      return;
    }
    db.ref('notifications/' + state.user.uid).orderByChild('createdAt').limitToLast(30).on('value', function (snap) {
      state.notifications = snap.val() || {};
      renderNotifications();
      document.dispatchEvent(new CustomEvent('bxm:notifications', { detail: state.notifications }));
    });
  }

  function updateNavAuth() {
    var loggedIn = !!state.user;
    var role = state.profile && state.profile.role;
    document.querySelectorAll('[data-bxm-guest]').forEach(function (el) {
      if (!state.authResolved || loggedIn) {
        el.setAttribute('hidden', '');
        el.style.display = 'none';
      } else {
        el.removeAttribute('hidden');
        el.style.display = '';
      }
    });
    document.querySelectorAll('[data-bxm-user-menu]').forEach(function (el) {
      el.hidden = !loggedIn;
    });
    document.querySelectorAll('[data-bxm-admin-link]').forEach(function (el) {
      el.hidden = role !== 'admin';
    });
    if (loggedIn) {
      var name = (state.profile && state.profile.name) || state.user.displayName || state.user.email || 'Account';
      document.querySelectorAll('[data-bxm-user-name]').forEach(function (el) {
        el.textContent = name;
      });
    }
  }

  function syncSession(user) {
    return getToken().then(function (token) {
      if (!user || !token) {
        if (cfg.role === 'admin') {
          return Promise.resolve();
        }
        return fetch(api('session.php'), { method: 'DELETE', credentials: 'same-origin', headers: csrfHeaders({}) }).catch(function () {});
      }
      return fetch(api('session.php'), {
        method: 'POST',
        credentials: 'same-origin',
        headers: csrfHeaders({ 'Content-Type': 'application/json' }),
        body: JSON.stringify({ idToken: token })
      }).catch(function () {});
    });
  }

  function loadProfile(uid) {
    if (!db || !uid) {
      updateNavAuth();
      return Promise.resolve(null);
    }
    return db.ref('users/' + uid).once('value').then(function (snap) {
      state.profile = snap.val();
      updateNavAuth();
      document.dispatchEvent(new CustomEvent('bxm:profile', { detail: state.profile }));
      return state.profile;
    }).catch(function () {
      state.profile = state.profile || null;
      updateNavAuth();
      return state.profile;
    });
  }

  function resolveAuth(user) {
    state.user = user;
    updateNavAuth();
    if (!state.authResolved) {
      state.authResolved = true;
      authWaiters.forEach(function (cb) {
        cb(user);
      });
      authWaiters = [];
    }
  }

  function onAuth(cb) {
    if (state.authResolved) {
      cb(state.user);
    } else {
      authWaiters.push(cb);
    }
  }

  function initAuth() {
    if (!fbReady || !auth) {
      resolveAuth(null);
      return;
    }
    auth.onAuthStateChanged(function (user) {
      if (user) {
        state.user = user;
        updateNavAuth();
        loadProfile(user.uid).then(function () {
          resolveAuth(user);
          try { attachCartListener(); } catch (e) {}
          try { attachWishlistListener(); } catch (e) {}
          try { attachNotificationsListener(); } catch (e) {}
          syncSession(user);
        }).catch(function () {
          updateNavAuth();
          resolveAuth(user);
        });
      } else {
        state.profile = null;
        state.cart = {};
        state.wishlist = {};
        state.notifications = {};
        state.unread = 0;
        updateCartCount();
        updateWishlistCount();
        updateNotifCount();
        renderNotifications();
        updateNavAuth();
        resolveAuth(null);
        syncSession(null);
      }
    });
  }

  function initGlobalEvents() {
    document.addEventListener('click', function (e) {
      var buy = e.target.closest('[data-bxm-buy-now]');
      if (buy) {
        e.preventDefault();
        if (buy.disabled || buy.classList.contains('disabled')) {
          return;
        }
        buyNow(buy.getAttribute('data-bxm-buy-now'));
        return;
      }
      var wish = e.target.closest('[data-bxm-wish]');
      if (wish) {
        e.preventDefault();
        toggleWishlist(wish.getAttribute('data-bxm-wish'));
        return;
      }
      var copy = e.target.closest('[data-bxm-copy]');
      if (copy) {
        e.preventDefault();
        copyText(copy.getAttribute('data-bxm-copy'));
        return;
      }
      var notifRead = e.target.closest('[data-bxm-notif-read]');
      if (notifRead) {
        markNotificationRead(notifRead.getAttribute('data-bxm-notif-read'));
        return;
      }
      var notifAll = e.target.closest('[data-bxm-notif-all]');
      if (notifAll) {
        e.preventDefault();
        markAllNotificationsRead();
        return;
      }
      var logout = e.target.closest('[data-bxm-logout]');
      if (logout) {
        e.preventDefault();
        confirmDialog('Are you sure you want to logout?', { confirmText: 'Logout' }).then(function (ok) {
          if (!ok) {
            return;
          }
          fetch(api('session.php'), { method: 'DELETE', headers: csrfHeaders({}) }).finally(function () {
            if (auth) {
              auth.signOut().then(function () {
                window.location.href = url('index.php');
              });
            } else {
              window.location.href = url('index.php');
            }
          });
        });
      }
    });
  }

  window.BXM = {
    url: url,
    api: api,
    escapeHtml: escapeHtml,
    money: money,
    plainNumber: plainNumber,
    toNumber: toNumber,
    toast: toast,
    loader: loader,
    copyText: copyText,
    confirmDialog: confirmDialog,
    statusBadge: statusBadge,
    discountPercent: discountPercent,
    productCardHTML: productCardHTML,
    renderProducts: renderProducts,
    requireLogin: requireLogin,
    getToken: getToken,
    apiFetch: apiFetch,
    uploadImage: uploadImage,
    addToCart: addToCart,
    buyNow: buyNow,
    setCartQuantity: setCartQuantity,
    removeFromCart: removeFromCart,
    toggleWishlist: toggleWishlist,
    moveToCart: moveToCart,
    pushNotification: pushNotification,
    markNotificationRead: markNotificationRead,
    markAllNotificationsRead: markAllNotificationsRead,
    renderNotifications: renderNotifications,
    timeAgo: timeAgo,
    onAuth: onAuth,
    state: state,
    get db() { return db; },
    get auth() { return auth; },
    get firebaseReady() { return fbReady; },
    get user() { return state.user; },
    get profile() { return state.profile; },
     get isAdmin() { return !!(state.profile && state.profile.role === 'admin') || (cfg.role === 'admin'); }
  };

  function boot() {
    initGlobalEvents();
    initAuth();
    if (!fbReady) {
      var host = document.getElementById('bxmConfigNotice');
      if (host) {
        host.innerHTML = '<div class="alert alert-warning border-0" style="background:#1c1405;color:#fbbf60">' +
          '<i class="bi bi-exclamation-triangle me-2"></i>Firebase is not configured yet. Set your Firebase Web API key in <code>includes/config.php</code> to load live data.</div>';
      }
    }
    document.querySelectorAll('[data-bxm-year]').forEach(function (el) {
      el.textContent = new Date().getFullYear();
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
