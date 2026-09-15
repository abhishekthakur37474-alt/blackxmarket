<?php
require_once __DIR__ . '/../includes/helpers.php';

if (bxm_is_admin()) {
    bxm_redirect(bxm_url('admin/index.php'));
}

if (!bxm_has_admin()) {
    bxm_redirect(bxm_url('admin/register.php'));
}

$pageTitle = 'Admin Login';
$pageDescription = 'Login to the BLACK X MARKET admin panel.';
$noIndex = true;
require_once __DIR__ . '/../includes/header.php';
?>
<section class="bxm-auth-wrap">
  <div class="container">
    <div class="bxm-auth-card">
      <div class="text-center mb-4">
        <img src="<?= bxm_url('assets/images/logo/logo.jpeg') ?>" alt="BLACK X MARKET" width="56" height="56" class="bxm-brand-logo mb-3">
        <h1 class="h4 mb-1">Admin Login</h1>
        <p class="text-secondary small mb-0">Sign in to manage BLACK X MARKET.</p>
      </div>
      <form id="adminLoginForm" novalidate>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" id="adminEmail" placeholder="admin@example.com" required>
        </div>
        <div class="mb-4">
          <label class="form-label">Password</label>
          <input type="password" class="form-control" id="adminPassword" placeholder="Your password" required>
        </div>
        <button type="submit" class="bxm-btn bxm-btn-primary w-100" id="adminLoginBtn">Login as Admin</button>
      </form>
    </div>
  </div>
</section>
<?php
$inlineScript = <<<'HTML'
<script>
(function () {
  function anyAdmin(users) {
    if (!users) return false;
    var keys = Object.keys(users);
    for (var i = 0; i < keys.length; i++) {
      if ((users[keys[i]] || {}).role === 'admin') return true;
    }
    return false;
  }

  window.BXM.onAuth(function () {
    if (!window.BXM.firebaseReady) return;
    window.BXM.db.ref('users').once('value').then(function (snap) {
      if (!anyAdmin(snap.val())) {
        window.location.href = window.BXM.url('admin/register.php');
      }
    }).catch(function () {});
  });

  document.getElementById('adminLoginForm').addEventListener('submit', function (e) {
    e.preventDefault();
    var email = document.getElementById('adminEmail').value.trim();
    var password = document.getElementById('adminPassword').value;
    var btn = document.getElementById('adminLoginBtn');
    if (!email || !password) { window.BXM.toast('Please fill in all fields', 'warning'); return; }
    if (!window.BXM.firebaseReady) { window.BXM.toast('Firebase is not configured yet', 'danger'); return; }
    btn.disabled = true;
    window.BXM.auth.signInWithEmailAndPassword(email, password).then(function (cred) {
      var user = cred.user;
      return window.BXM.db.ref('users/' + user.uid).once('value').then(function (snap) {
        var profile = snap.val() || {};
        if (profile.role !== 'admin') {
          return window.BXM.auth.signOut().then(function () {
            throw new Error('This account is not an admin.');
          });
        }
        return user.getIdToken();
      });
    }).then(function (token) {
      if (!token) throw new Error('Could not create admin session.');
      return fetch(window.BXM.api('session.php'), {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': (window.BXM_APP && window.BXM_APP.csrf) || '' },
        body: JSON.stringify({ idToken: token })
      }).then(function (r) {
        return r.json().then(function (data) {
          return { status: r.status, data: data || {} };
        }).catch(function () {
          return { status: r.status, data: {} };
        });
      }).then(function (res) {
        var data = res.data || {};
        if (!data.ok || !data.user || data.user.role !== 'admin') {
          return window.BXM.auth.signOut().then(function () {
            throw new Error(data.error || 'Admin session could not be created.');
          });
        }
        return data;
      });
    }).then(function () {
      window.BXM.toast('Welcome, Admin', 'success');
      window.location.replace(window.BXM.url('admin/index.php'));
    }).catch(function (err) {
      btn.disabled = false;
      var msg = err.message || 'Login failed';
      if (err.code === 'auth/user-not-found' || err.code === 'auth/wrong-password' || err.code === 'auth/invalid-credential') {
        msg = 'Invalid admin email or password';
      }
      window.BXM.toast(msg, 'danger');
    });
  });
})();
</script>
HTML;
include __DIR__ . '/../includes/footer.php';
?>
