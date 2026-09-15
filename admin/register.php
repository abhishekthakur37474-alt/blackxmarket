<?php
require_once __DIR__ . '/../includes/helpers.php';

if (bxm_is_admin()) {
    bxm_redirect(bxm_url('admin/index.php'));
}

if (bxm_has_admin()) {
    bxm_redirect(bxm_url('admin/login.php'));
}

$pageTitle = 'Create Admin Account';
$pageDescription = 'Create the first BLACK X MARKET admin account.';
$noIndex = true;
require_once __DIR__ . '/../includes/header.php';
?>
<section class="bxm-auth-wrap">
  <div class="container">
    <div class="bxm-auth-card">
      <div class="text-center mb-4">
        <img src="<?= bxm_url('assets/images/logo/logo.jpeg') ?>" alt="BLACK X MARKET" width="56" height="56" class="bxm-brand-logo mb-3">
        <h1 class="h4 mb-1">Create Admin Account</h1>
        <p class="text-secondary small mb-0">No admin exists yet. Register the first admin.</p>
      </div>
      <form id="adminRegisterForm" novalidate>
        <div class="mb-3">
          <label class="form-label">Full Name</label>
          <input type="text" class="form-control" id="adminRegName" placeholder="Admin name" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" id="adminRegEmail" placeholder="admin@example.com" required>
        </div>
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <label class="form-label">Password</label>
            <input type="password" class="form-control" id="adminRegPassword" minlength="6" placeholder="Min 6 characters" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Confirm Password</label>
            <input type="password" class="form-control" id="adminRegConfirm" minlength="6" required>
          </div>
        </div>
        <button type="submit" class="bxm-btn bxm-btn-primary w-100" id="adminRegisterBtn">Create Admin</button>
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

  document.getElementById('adminRegisterForm').addEventListener('submit', function (e) {
    e.preventDefault();
    var name = document.getElementById('adminRegName').value.trim();
    var email = document.getElementById('adminRegEmail').value.trim();
    var pass = document.getElementById('adminRegPassword').value;
    var confirm = document.getElementById('adminRegConfirm').value;
    var btn = document.getElementById('adminRegisterBtn');

    if (!name || !email || !pass) { window.BXM.toast('Please fill in all fields', 'warning'); return; }
    if (pass.length < 6) { window.BXM.toast('Password must be at least 6 characters', 'warning'); return; }
    if (pass !== confirm) { window.BXM.toast('Passwords do not match', 'warning'); return; }
    if (!window.BXM.firebaseReady) { window.BXM.toast('Firebase is not configured yet', 'danger'); return; }

    btn.disabled = true;
    window.BXM.db.ref('users').once('value').then(function (snap) {
      if (anyAdmin(snap.val())) {
        window.location.href = window.BXM.url('admin/login.php');
        throw new Error('An admin already exists.');
      }
      return window.BXM.auth.createUserWithEmailAndPassword(email, pass);
    }).then(function (cred) {
      var user = cred.user;
      return user.updateProfile({ displayName: name }).catch(function () {}).then(function () {
        return window.BXM.db.ref('users/' + user.uid).set({
          name: name,
          email: email,
          phone: '',
          role: 'admin',
          status: 'active',
          createdAt: Date.now()
        });
      }).then(function () {
        return user.getIdToken();
      });
    }).then(function (token) {
      return fetch(window.BXM.api('session.php'), {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': (window.BXM_APP && window.BXM_APP.csrf) || '' },
        body: JSON.stringify({ idToken: token })
      }).then(function (r) {
        return r.json().then(function (data) { return data || {}; }).catch(function () { return {}; });
      }).then(function (data) {
        if (!data.ok || !data.user || data.user.role !== 'admin') {
          throw new Error(data.error || 'Admin session could not be created.');
        }
        return data;
      });
    }).then(function () {
      window.BXM.toast('Admin account created', 'success');
      window.location.replace(window.BXM.url('admin/index.php'));
    }).catch(function (err) {
      btn.disabled = false;
      var msg = err.message || 'Registration failed';
      if (err.code === 'auth/email-already-in-use') msg = 'This email is already registered. Use a different email for admin.';
      else if (err.code === 'auth/invalid-email') msg = 'Please enter a valid email address.';
      else if (err.code === 'auth/weak-password') msg = 'Password must be at least 6 characters.';
      window.BXM.toast(msg, 'danger');
    });
  });
})();
</script>
HTML;
include __DIR__ . '/../includes/footer.php';
?>
