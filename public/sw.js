const CACHE_NAME = 'acufara-pwa-v1';
const URLS_TO_CACHE = [
    '/',
    '/manifest.json',
    '/pwa-192x192.png',
    '/pwa-512x512.png',
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                return cache.addAll(URLS_TO_CACHE);
            })
    );
});

self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                if (response) {
                    return response;
                }
                return fetch(event.request);
            })
    );
});
