var staticCacheName = "pwa-v" + new Date().getTime();
var filesToCache = [
    '/offline',
    '/css/app.css',
    '/js/app.js',
    "/storage/01JH518HRVKWHK6Y0X4XF53RD6.png",
    "/storage/01JH518HRX32FB3PWPSN5DNPPH.png",
    "/storage/01JH518HRZCWXEETW2MDYZJB35.png",
    "/storage/01JH518HS0N5YBTYZVCXAJ4E77.png",
    "/storage/01JH518HS3BV6PZ4VYGHAPG3Y4.png",
    "/storage/01JH518HS5ZNKHZ77FZ8E59ZP0.png",
    "/storage/01JH518HS7FY3NK3PD8HR241V2.png",
    "/storage/01JH518HS9BJMDZKG4R6A2FR01.png"
];

// Cache on install
self.addEventListener("install", event => {
    this.skipWaiting();
    event.waitUntil(
        caches.open(staticCacheName)
            .then(cache => {
                return cache.addAll(filesToCache);
            })
    )
});

// Clear cache on activate
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames
                    .filter(cacheName => (cacheName.startsWith("pwa-")))
                    .filter(cacheName => (cacheName !== staticCacheName))
                    .map(cacheName => caches.delete(cacheName))
            );
        })
    );
});

// Serve from Cache
self.addEventListener("fetch", event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                return response || fetch(event.request);
            })
            .catch(() => {
                return caches.match('offline');
            })
    )
});
