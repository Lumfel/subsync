/* ─────────────────────────────────────────────
   Shared request context for every page.

   Patches fetch/EventSource once so that:
     - the base-path prefix is applied for subdirectory installs,
     - "ngrok-skip-browser-warning" is always sent (free tunnels answer with
       an interstitial page otherwise),
     - "X-Portal" (window.__PORTAL) pins the per-portal session cookie so
       ResolvePortalSession never has to guess from the Referer.

   Load AFTER window.APP_BASE is set, and BEFORE ui.js (its SSE polyfill
   reads the stream through the already-patched fetch).
   ───────────────────────────────────────────── */
(function () {
  var base = window.APP_BASE || '';

  function prefix(url) {
    if (base && typeof url === 'string' && url.charAt(0) === '/' && url.charAt(1) !== '/') return base + url;
    return url;
  }

  function withHeaders(headers) {
    var h = new Headers(headers || {});
    h.set('ngrok-skip-browser-warning', '1');
    if (window.__PORTAL) h.set('X-Portal', window.__PORTAL);
    return h;
  }

  var origFetch = window.fetch;
  window.fetch = function (url, opts) {
    opts = opts || {};
    opts.headers = withHeaders(opts.headers);
    return origFetch.call(this, prefix(url), opts);
  };

  var OrigES = window.EventSource;
  if (typeof OrigES === 'function') {
    window.EventSource = function (url, cfg) {
      var u = prefix(url);
      if (window.__PORTAL) u += (u.indexOf('?') < 0 ? '?' : '&') + 'portal=' + window.__PORTAL;
      return new OrigES(u, cfg);
    };
    window.EventSource.prototype = OrigES.prototype;
    window.EventSource.CONNECTING = 0;
    window.EventSource.OPEN = 1;
    window.EventSource.CLOSED = 2;
  }
})();
