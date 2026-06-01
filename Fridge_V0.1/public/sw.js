/* Fridge — Service Worker */
const CACHE_NAME = 'fridge-v1';
const STATIC_CACHE = 'fridge-static-v1';

const PAGES_TO_PRECACHE = ['/', '/planning', '/mes-recettes', '/stock'];

const STATIC_EXTENSIONS = ['.css', '.js', '.woff', '.woff2', '.ttf', '.otf', '.png', '.jpg', '.jpeg', '.webp', '.svg', '.ico'];

function isStaticAsset(url) {
  return STATIC_EXTENSIONS.some(ext => url.pathname.endsWith(ext));
}

/* Install — précache les pages clés */
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      return Promise.allSettled(
        PAGES_TO_PRECACHE.map(url =>
          fetch(url).then(res => { if (res.ok) cache.put(url, res); }).catch(() => {})
        )
      );
    }).then(() => self.skipWaiting())
  );
});

/* Activate — nettoyage des anciens caches */
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(
        keys.filter(k => k !== CACHE_NAME && k !== STATIC_CACHE).map(k => caches.delete(k))
      )
    ).then(() => self.clients.claim())
  );
});

/* Fetch */
self.addEventListener('fetch', event => {
  const url = new URL(event.request.url);

  /* Ignore les requêtes non-HTTP(S) et cross-origin hors assets statiques */
  if (!event.request.url.startsWith('http')) return;

  if (isStaticAsset(url)) {
    /* Cache First pour les assets statiques */
    event.respondWith(
      caches.open(STATIC_CACHE).then(cache =>
        cache.match(event.request).then(cached => {
          if (cached) return cached;
          return fetch(event.request).then(res => {
            if (res && res.ok) cache.put(event.request, res.clone());
            return res;
          }).catch(() => cached);
        })
      )
    );
  } else if (event.request.mode === 'navigate' || event.request.headers.get('accept')?.includes('text/html')) {
    /* Network First, Cache Fallback pour les pages */
    event.respondWith(
      fetch(event.request)
        .then(res => {
          if (res && res.ok) {
            caches.open(CACHE_NAME).then(cache => cache.put(event.request, res.clone()));
          }
          return res;
        })
        .catch(() => caches.match(event.request))
    );
  }
});
