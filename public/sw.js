/* Nubia Inventory — Service Worker */
/* Cache version: bump with app releases */
const CACHE_VERSION = 'nubia-pwa-v1.2.0';
const STATIC_CACHE = CACHE_VERSION + '-static';
const RUNTIME_CACHE = CACHE_VERSION + '-runtime';

const PRECACHE_URLS = [
    '/offline.html',
    '/manifest.webmanifest',
    '/assets/icons/icon-192.png',
    '/assets/icons/icon-512.png',
    '/assets/icons/apple-touch-icon.png',
    '/assets/icons/favicon-32.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => cache.addAll(PRECACHE_URLS.map((u) => new Request(u, { cache: 'reload' }))))
            .then(() => self.skipWaiting())
            .catch(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys
                    .filter((key) => key.startsWith('nubia-pwa-') && key !== STATIC_CACHE && key !== RUNTIME_CACHE)
                    .map((key) => caches.delete(key))
            )
        ).then(() => self.clients.claim())
    );
});

function isApiOrMutation(request, url) {
    if (request.method !== 'GET') return true;
    if (url.pathname.startsWith('/api/')) return true;
    if (url.pathname.startsWith('/logout')) return true;
    return false;
}

function isStaticAsset(url) {
    return (
        url.pathname.startsWith('/assets/') ||
        url.pathname.endsWith('.webmanifest') ||
        url.pathname === '/offline.html'
    );
}

function isCdn(url) {
    return (
        url.hostname.includes('cdn.jsdelivr.net') ||
        url.hostname.includes('cdnjs.cloudflare.com') ||
        url.hostname.includes('fonts.googleapis.com') ||
        url.hostname.includes('fonts.gstatic.com') ||
        url.hostname.includes('cdn.datatables.net')
    );
}

async function networkFirst(request) {
    try {
        const fresh = await fetch(request);
        if (fresh && fresh.ok && request.method === 'GET') {
            const cache = await caches.open(RUNTIME_CACHE);
            cache.put(request, fresh.clone());
        }
        return fresh;
    } catch (err) {
        const cached = await caches.match(request);
        if (cached) return cached;
        if (request.mode === 'navigate') {
            const offline = await caches.match('/offline.html');
            if (offline) return offline;
        }
        throw err;
    }
}

async function cacheFirst(request) {
    const cached = await caches.match(request);
    if (cached) {
        // Stale-while-revalidate for static assets
        fetch(request).then((res) => {
            if (res && res.ok) {
                caches.open(STATIC_CACHE).then((cache) => cache.put(request, res));
            }
        }).catch(() => {});
        return cached;
    }
    const fresh = await fetch(request);
    if (fresh && fresh.ok) {
        const cache = await caches.open(STATIC_CACHE);
        cache.put(request, fresh.clone());
    }
    return fresh;
}

self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // Only handle same-origin + known CDNs
    const sameOrigin = url.origin === self.location.origin;
    if (!sameOrigin && !isCdn(url)) return;

    if (isApiOrMutation(request, url)) {
        return; // network only
    }

    if (request.mode === 'navigate') {
        event.respondWith(networkFirst(request));
        return;
    }

    if (sameOrigin && isStaticAsset(url)) {
        event.respondWith(cacheFirst(request));
        return;
    }

    if (isCdn(url)) {
        event.respondWith(cacheFirst(request));
        return;
    }

    if (sameOrigin) {
        event.respondWith(networkFirst(request));
    }
});

self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});
