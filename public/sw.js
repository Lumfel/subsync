/* SubSync service worker — offline shell + static asset caching.
   Conservative by design: never caches API/streaming responses or non-GET requests,
   and never stores error responses (a transient 404/500 must not stick). */
const CACHE = 'subsync-shell-v7';
const SHELL = ['./offline.html'];

const OFFLINE_FALLBACK = () => caches.match('./offline.html').then(
  (hit) => hit || new Response('Offline', { status: 503, headers: { 'Content-Type': 'text/plain' } })
);

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
      caches.match(req).then((hit) => {
        if (hit) return hit;

        return fetch(req).then((res) => {
          // Only cache successful, same-origin responses.
          if (res && res.ok && res.type === 'basic') {
            const copy = res.clone();
            caches.open(CACHE).then((cache) => cache.put(req, copy));
          }
          return res;
        }).catch(() => new Response('', { status: 504, statusText: 'Offline' }));
      })
    );
    return;
  }

  // Navigations: network-first, fall back to the offline shell.
  if (req.mode === 'navigate') {
    event.respondWith(fetch(req).catch(() => OFFLINE_FALLBACK()));
  }
});
