// Service Worker for Carib Code
const CACHE_NAME = 'carib-code-pwa-v5';
const ASSETS_TO_CACHE = [
  '/',
  '/index.html',
  '/manifest.json',
  '/apple-touch-icon.png',
  '/carib-code-logo.svg',
  '/admin/assets/__FCC_VERSION__/claude_suite.css',
  '/admin/assets/__FCC_VERSION__/claude_suite.js',
  '/admin/assets/__FCC_VERSION__/code_sessions.css',
  '/admin/assets/__FCC_VERSION__/code_sessions.js',
  '/admin/assets/__FCC_VERSION__/session_layout.css'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(ASSETS_TO_CACHE).catch((err) => {
        console.warn('PWA Asset precache warning:', err);
      });
    })
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k))
      );
    })
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  // Pass through API and stream requests
  if (
    event.request.url.includes('/api.php') ||
    event.request.url.includes('/api/') ||
    event.request.url.includes('/admin/api/') ||
    event.request.method !== 'GET'
  ) {
    return;
  }

  // Network-first for JavaScript, CSS, and HTML to guarantee instant UI updates
  if (
    event.request.url.includes('.js') ||
    event.request.url.includes('.css') ||
    event.request.mode === 'navigate'
  ) {
    event.respondWith(
      fetch(event.request)
        .then((networkResponse) => {
          if (networkResponse && networkResponse.status === 200) {
            const clone = networkResponse.clone();
            caches.open(CACHE_NAME).then((cache) => cache.put(event.request, clone));
          }
          return networkResponse;
        })
        .catch(() => caches.match(event.request))
    );
    return;
  }

  event.respondWith(
    caches.match(event.request).then((cachedResponse) => {
      if (cachedResponse) {
        fetch(event.request).then((networkResponse) => {
          if (networkResponse && networkResponse.status === 200) {
            caches.open(CACHE_NAME).then((cache) => cache.put(event.request, networkResponse));
          }
        }).catch(() => {});
        return cachedResponse;
      }
      return fetch(event.request);
    })
  );
});
