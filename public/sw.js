/* SubSync service worker — offline shell + static asset caching.
   Conservative by design: never caches API/streaming responses or non-GET requests. */
const CACHE = 'subsync-shell-v2';
const SHELL = ['./offline.html'];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE).then((cache) => cache.addAll(SHELL)).then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys()
      .then((keys) => Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k))))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET') return;

  const url = new URL(req.url);
  if (url.origin !== self.location.origin) return;
  // Never cache dynamic endpoints or the SSE stream.
  if (url.pathname.includes('/api/') || url.pathname.includes('/stream/')) return;

  // Static assets: cache-first (they are fingerprinted with ?v=<mtime>).
  if (/\.(css|js|mjs|svg|png|jpe?g|webp|gif|ico|woff2?)$/i.test(url.pathname)) {
    event.respondWith(
      caches.match(req).then((hit) => hit || fetch(req).then((res) => {
        const copy = res.clone();
        caches.open(CACHE).then((cache) => cache.put(req, copy));
        return res;
      }).catch(() => hit))
    );
    return;
  }

  // Navigations: network-first, fall back to the offline shell.
  if (req.mode === 'navigate') {
    event.respondWith(fetch(req).catch(() => caches.match('./offline.html')));
  }
});
