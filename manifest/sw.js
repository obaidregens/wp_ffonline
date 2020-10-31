const OFFLINE_VERSION = 12;
const CACHE_NAME = 'offline';
const OFFLINE_URL = '/offline';
const permCaching = [
  "https://fonts.googleapis.com/css2?family=Varela+Round&display=swap",
  "https://fonts.gstatic.com/s/varelaround/v13/w8gdH283Tvk__Lua32TysjIfp8uP.woff2",
  "https://fonts.googleapis.com/css2?family=Montserrat&family=Open+Sans&family=Pangolin&family=Merriweather&family=Raleway&family=Roboto&display=swap",
  "https://fonts.gstatic.com/s/merriweather/v22/u-440qyriQwlOrhSvowK_l5-fCZM.woff2",
  "https://fonts.gstatic.com/s/opensans/v18/mem8YaGs126MiZpBA-UFVZ0b.woff2",
  "https://fonts.gstatic.com/s/roboto/v20/KFOmCnqEu92Fr1Mu4mxK.woff2",
  "https://fonts.gstatic.com/s/montserrat/v15/JTUSjIg1_i6t8kCHKm459Wlhyw.woff2",
  "https://fonts.gstatic.com/s/pangolin/v6/cY9GfjGcW0FPpi-tWMfN79w.woff2",
  "https://fonts.gstatic.com/s/raleway/v18/1Ptxg8zYS_SKggPN4iEgvnHyvveLxVvaorCIPrE.woff2",
  "/content/static/css/fonts/icons.woff",
  OFFLINE_URL
];
const dynamicCaching = [
  "/content/static/chapters.css",
  "/content/static/chapters.js",
  "/content/static/book.css",
  "/content/static/book.js",
];
self.addEventListener('install', (event) => {
  self.skipWaiting();

  event.waitUntil((async () => {
    const cache = await caches.open(CACHE_NAME);
    for (let i = 0; i < permCaching.length; i++) {
      await cache.add( new Request(permCaching[i], {cache: 'reload'}) );
    }
    for (let i = 0; i < dynamicCaching.length; i++) {
      const response = await fetch(dynamicCaching[i], {cache: 'reload'} )
      if (!response.ok) {
        throw new TypeError('bad response status');
      }
      await cache.put(response.url, response);
    }
  })());
});

self.addEventListener('activate', (event) => {
  event.waitUntil((async () => {
    if ('navigationPreload' in self.registration) {
      await self.registration.navigationPreload.enable();
    }
  })());
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  if ( ['navigate','no-cors','cors'].includes(event.request.mode) ) {
    event.respondWith((async () => {
      try {
        const preloadResponse = await event.preloadResponse;
        if (preloadResponse) {
          return preloadResponse;
        }
        const networkResponse = await fetch(event.request);
        return networkResponse;
      } catch (error) {
        const urlObj = new URL(event.request.url);
        const cache = await caches.open(CACHE_NAME);
        const existsResponse = await cache.match(event.request.url,{ignoreSearch: urlObj.origin === location.origin});
        if (existsResponse) {
          console.log('Cached: ' + event.request.url);
          return existsResponse;
        }
        console.log('Offline: ' + event.request.url);
        const cachedResponse = await cache.match(OFFLINE_URL,{ignoreSearch: true});
        return cachedResponse;
      }
    })());
  }
});
