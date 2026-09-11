<?php
require_once __DIR__ . '/includes/auth-check.php';
$pageTitle = 'My Profile';
$pageDescription = 'Manage your account details.';
$noIndex = true;
require_once __DIR__ . '/includes/header.php';
?>
<div class="container" id="bxmConfigNotice"></div>

<section class="bxm-section-sm">
  <div class="container">
    <h1 class="bxm-section-title mb-4">My Profile</h1>
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="bxm-card p-4 text-center">
          <img id="profilePhoto" src="<?= bxm_url('assets/images/logo/logo.jpeg') ?>" alt="Profile" class="bxm-brand-logo mx-auto mb-3" style="width:96px;height:96px">
          <h5 class="mb-1" id="profileName"><?= bxm_e($currentUser['name'] ?: 'User') ?></h5>
          <p class="text-secondary small mb-3" id="profileEmail"><?= bxm_e($currentUser['email']) ?></p>
          <span class="bxm-badge" id="profileRole">User</span>
          <hr class="bxm-divider my-4">
          <div class="row text-center g-3">
            <div class="col-4"><div class="fw-bold" id="statOrders">0</div><div class="small text-muted">Orders</div></div>
            <div class="col-4"><div class="fw-bold" id="statApproved">0</div><div class="small text-muted">Approved</div></div>
            <div class="col-4"><div class="fw-bold" id="statWishlist">0</div><div class="small text-muted">Wishlist</div></div>
          </div>
        </div>
      </div>
      <div class="col-lg-8">
        <div class="bxm-card p-4 mb-4">
          <h6 class="mb-3">Account Information</h6>
          <form id="profileForm">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Full Name</label>
                <input type="text" class="form-control" id="inputName" placeholder="Your name">
              </div>
              <div class="col-md-6">
                <label class="form-label">Phone</label>
                <input type="text" class="form-control" id="inputPhone" placeholder="Optional">
              </div>
              <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" id="inputEmail" disabled>
              </div>
              <div class="col-md-6">
                <label class="form-label">Profile Picture</label>
                <input type="file" class="form-control" id="inputPhoto" accept="image/jpeg,image/png,image/webp">
              </div>
              <div class="col-12">
                <button type="submit" class="bxm-btn bxm-btn-primary"><i class="bi bi-check2"></i> Save Changes</button>
              </div>
            </div>
          </form>
        </div>

        <div class="bxm-card p-4">
          <h6 class="mb-3">Change Password</h6>
          <form id="passwordForm">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">New Password</label>
                <input type="password" class="form-control" id="newPassword" minlength="6" placeholder="Minimum 6 characters">
              </div>
              <div class="col-md-6">
                <label class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="confirmPassword" minlength="6">
              </div>
              <div class="col-12">
                <button type="submit" class="bxm-btn bxm-btn-outline"><i class="bi bi-shield-lock"></i> Update Password</button>
                <a href="#" class="bxm-btn bxm-btn-ghost text-danger ms-2" data-bxm-logout><i class="bi bi-box-arrow-right"></i> Logout</a>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
<?php
$inlineScript = <<<'HTML'
<script>
(function () {
  var profile = null;

  function fill(p) {
    profile = p || {};
    document.getElementById('inputName').value = profile.name || '';
    document.getElementById('inputPhone').value = profile.phone || '';
    document.getElementById('inputEmail').value = profile.email || '';
    document.getElementById('profileName').textContent = profile.name || 'User';
    document.getElementById('profileEmail').textContent = profile.email || '';
    document.getElementById('profileRole').textContent = (profile.role === 'admin' ? 'Admin' : 'User');
    if (profile.photoUrl) document.getElementById('profilePhoto').src = profile.photoUrl;
    if (profile.createdAt) {
      var d = new Date(Number(profile.createdAt));
      if (!isNaN(d.getTime())) document.getElementById('profileEmail').textContent = (profile.email || '') + ' - joined ' + d.toLocaleDateString();
    }
  }

  document.getElementById('profileForm').addEventListener('submit', function (e) {
    e.preventDefault();
    var user = window.BXM.user;
    if (!user) return;
    var name = document.getElementById('inputName').value.trim();
    var phone = document.getElementById('inputPhone').value.trim();
    var file = document.getElementById('inputPhoto').files[0];
    window.BXM.loader(true);
    var photoPromise = file ? window.BXM.uploadImage(file, 'profile') : Promise.resolve(null);
    photoPromise.then(function (up) {
      if (up && !up.ok) throw new Error(up.error || 'Image upload failed');
      var update = { name: name, phone: phone, updatedAt: Date.now() };
      if (up && up.url) update.photoUrl = up.url;
      return window.BXM.db.ref('users/' + user.uid).update(update).then(function () {
        var authUpdate = {};
        if (name) authUpdate.displayName = name;
        if (up && up.url) authUpdate.photoURL = up.url;
        if (Object.keys(authUpdate).length) return user.updateProfile(authUpdate);
      });
    }).then(function () {
      window.BXM.loader(false);
      window.BXM.toast('Profile updated successfully', 'success');
      document.getElementById('inputPhoto').value = '';
      window.BXM.db.ref('users/' + user.uid).once('value').then(function (s) { fill(s.val()); });
    }).catch(function (err) {
      window.BXM.loader(false);
      window.BXM.toast(err.message || 'Could not update profile', 'danger');
    });
  });

  document.getElementById('passwordForm').addEventListener('submit', function (e) {
    e.preventDefault();
    var user = window.BXM.user;
    var pass = document.getElementById('newPassword').value;
    var confirm = document.getElementById('confirmPassword').value;
    if (pass.length < 6) { window.BXM.toast('Password must be at least 6 characters', 'warning'); return; }
    if (pass !== confirm) { window.BXM.toast('Passwords do not match', 'warning'); return; }
    window.BXM.loader(true);
    user.updatePassword(pass).then(function () {
      window.BXM.loader(false);
      window.BXM.toast('Password updated successfully', 'success');
      document.getElementById('passwordForm').reset();
    }).catch(function (err) {
      window.BXM.loader(false);
      if (err.code === 'auth/requires-recent-login') {
        window.BXM.toast('Please login again before changing your password', 'warning');
      } else {
        window.BXM.toast(err.message || 'Could not update password', 'danger');
      }
    });
  });

  window.BXM.onAuth(function (user) {
    if (!window.BXM.firebaseReady || !user) return;
    window.BXM.db.ref('users/' + user.uid).once('value').then(function (s) { fill(s.val()); });
    window.BXM.db.ref('users/' + user.uid + '/orders').once('value').then(function (s) {
      var total = 0;
      var keys = [];
      s.forEach(function (c) { total++; keys.push(c.key); });
      document.getElementById('statOrders').textContent = total;
      Promise.all(keys.map(function (id) {
        return window.BXM.db.ref('orders/' + id).once('value').then(function (os) {
          var o = os.val();
          return (o && (o.status === 'approved' || o.status === 'completed')) ? 1 : 0;
        }).catch(function () { return 0; });
      })).then(function (results) {
        document.getElementById('statApproved').textContent = results.reduce(function (a, b) { return a + b; }, 0);
      });
    });
    window.BXM.db.ref('wishlists/' + user.uid).once('value').then(function (s) {
      var count = 0; s.forEach(function () { count++; });
      document.getElementById('statWishlist').textContent = count;
    });
  });
})();
</script>
HTML;
include __DIR__ . '/includes/footer.php';
?>
