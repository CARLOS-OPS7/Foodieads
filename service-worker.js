const CACHE_NAME = 'foodieads-v1';
const FILES_TO_CACHE = [
  '/',
  '/index.php',
  '/manifest.json',
  '/icons/spoon.svg',
  '/icons/fork.svg',
  '/offline.html',
  '/restaurants.html',
  '/add_restaurant.html',
  '/assets/styles.css',
  '/icons/icon-192x192.png',
  '/icons/icon-512x512.png'
  ,'/icons/spoon.svg','/icons/fork.svg'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(FILES_TO_CACHE);
    })
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k))
      );
    })
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  // navigation requests -> return cached offline page on failure
  if (event.request.mode === 'navigate') {
    event.respondWith(
      fetch(event.request).catch(() => caches.match('/offline.html'))
    );
    return;
  }

  // For other requests, try cache first then network
  event.respondWith(
    caches.match(event.request).then((response) => {
      return response || fetch(event.request).then((resp) => {
        // optionally cache new requests
        return resp;
      });
    }).catch(() => {
      // fallback for images
      if (event.request.destination === 'image') {
        return caches.match('/icons/icon-192x192.png');
      }
    })
  );
});
