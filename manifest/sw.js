const OFFLINE_VERSION = 2;
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
  "/content/static/chapters.css",
  "/content/static/chapters.js",
  "/content/static/book.css",
  "/content/static/book.js",
  "/content/static/css/fonts/icons.woff",
  OFFLINE_URL
];
self.addEventListener('install', (event) => {
  event.waitUntil((async () => {
    const cache = await caches.open(CACHE_NAME);
    // Setting {cache: 'reload'} in the new request will ensure that the response
    // isn't fulfilled from the HTTP cache; i.e., it will be from the network.
    for (let i = 0; i < permCaching.length; i++) {
      await cache.add( new Request(permCaching[i], {cache: 'reload'}) );
    }
  })());
});

self.addEventListener('activate', (event) => {
  event.waitUntil((async () => {
    // Enable navigation preload if it's supported.
    // See https://developers.google.com/web/updates/2017/02/navigation-preload
    if ('navigationPreload' in self.registration) {
      await self.registration.navigationPreload.enable();
    }
  })());

  // Tell the active service worker to take control of the page immediately.
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  // We only want to call event.respondWith() if this is a navigation request
  // for an HTML page.
  if ( ['navigate','no-cors','cors'].includes(event.request.mode) ) {
    event.respondWith((async () => {
      try {
        // First, try to use the navigation preload response if it's supported.
        const preloadResponse = await event.preloadResponse;
        if (preloadResponse) {
          return preloadResponse;
        }
      const networkResponse = await fetch(event.request);
        return networkResponse;
      } catch (error) {
        // catch is only triggered if an exception is thrown, which is likely
        // due to a network error.
        // If fetch() returns a valid HTTP response with a response code in
        // the 4xx or 5xx range, the catch() will NOT be called.
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
// Re