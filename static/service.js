(async () => {
    // Upgrade
    if ( (await idbKeyval.keys()).length > 0 ) {
        return;
    }
    const StorageEnt = Object.entries(localStorage);
    const startsWith = "chapter_track";
    StorageEnt.forEach(([key,value]) => {
        if (key.substr(0,startsWith.length) === startsWith || key === "intro") {
            localStorage.removeItem(key);
            idbKeyval.set(key,value);    
        }
    });
    // Delete Offline
    let off = JSON.parse(localStorage.getItem('offline_stories')) || [];
    if (off.stories) {
        off = off.stories;
    }
    off.forEach(async story => {
        const cache = await caches.open('offline'); 
        cache.delete("/story/" + story.ID);
        for (let i = 1; i <= story.chapters; i++) {
            const ur = "/story/" + story.ID + "/" + i;
            cache.delete(ur);
        }
    });
    localStorage.removeItem('offline_stories');
})();
// Check for updates
(async () => {
    const syncUpdates = async () => {
        const existing = JSON.parse(await idbKeyval.get('offline_stories')) || {};
        existing.stories = existing.stories || {};
        const response = await api('offline',{ data: {
            action: "renew",
            key: existing.key || ""
        } });
        const cache = await caches.open('offline');
        for (let j = 0; j < response.current.length; j++) {
            const {id,chapters, title, author} = response.current[j];
            if (!existing.stories[id]) {
                existing.stories[id] = {
                    "ID": id,
                    "title": title,
                    "author": author,
                    "chapters": chapters,
                    "time_added": Date.now()
                };
            }
            const base_ur = "/story/" + id;
            if (!await cache.match(base_ur)) {
                await cache.add(base_ur);
            }
            for (let i = 1; i <= chapters; i++) {
                const ur = base_ur + "/" + i;
                if (!await cache.match(ur)) {
                    await cache.add(ur);
                }
            }
        }
        for (let j = 0; j < response.update.length; j++) {
            const {id,chapters} = response.update[j];
            const base_ur = "/story/" + id;
            await cache.add( base_ur );
            for (let i = 1; i <= chapters; i++) {
                await cache.add( base_ur + "/" + i );
            }
        }
        for (let j = 0; j < response.delete.length; j++) {
            const {id} = response.delete[j];
            delete existing.stories[id];
            const base_ur = "/story/" + id;
            await cache.delete( base_ur );
            for (let i = 1; ; i++) {
                const ur = base_ur + "/" + i;
                if (!await cache.match(ur)) {
                    break;
                }
                await cache.delete( ur );
            }
        }
        await idbKeyval.set('offline_stories',JSON.stringify(existing));
        const r = await api('offline',{ data: {
            action: "confirm",
            key: existing.key || ""
        } });
        await idbKeyval.set('offline_last_sync',Date.now());
    }
    const hr = 1000*60*60;
    setInterval(syncUpdates,hr);
    const syncTimeLast = await idbKeyval.get('offline_last_sync') || 0;
    if (((Date.now()-syncTimeLast) > hr || (syncTimeLast>Date.now())) ) {
        syncUpdates();
    }
})();
