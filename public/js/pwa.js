/* Registers the service worker for offline support (PWA). */
(function () {
  if (!('serviceWorker' in navigator)) return;
  window.addEventListener('load', function () {
    var base = window.APP_BASE || '';
    navigator.serviceWorker.register(base + '/sw.js').catch(function () { /* offline support unavailable */ });
  });
})();
