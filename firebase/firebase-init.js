(function () {
  var cfg = window.BXM_FIREBASE_CONFIG || {};
  window.BXM_FIREBASE_READY = false;
  window.BXM_FB = null;

  if (!cfg.apiKey || String(cfg.apiKey).indexOf('REPLACE') === 0) {
    console.warn('[BLACK X MARKET] Firebase apiKey is not configured. Edit includes/config.php and set the real Firebase Web API key.');
    return;
  }

  try {
    firebase.initializeApp(cfg);
    window.BXM_FB = {
      app: firebase.app(),
      auth: firebase.auth(),
      db: firebase.database()
    };
    window.BXM_FIREBASE_READY = true;
  } catch (err) {
    console.error('[BLACK X MARKET] Firebase init failed:', err);
  }
})();
