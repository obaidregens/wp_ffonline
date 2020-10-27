const offlineAPI = async args => {
    // Extract
    const bid = args.book_id;
    const b_title = args.title;
    const b_author = args.author;
    const b_total_chapters = args.total_chapters;
    const b_elems = args.elems || [];
    const b_hooks = args.hooks || {
        started_saving: () => {},
        save_chapter: (chapter_num,total_chapters) => {},
        finished_saving: () => {}
    };
    if (!bid || !b_total_chapters || b_elems.length < 1) {
        return false;
    }
    // Init
    if ((JSON.parse(await idbKeyval.get('offline_stories')) || {stories: {}}).stories[bid]) {
        b_elems.forEach(el => el.classList.add('active'));
    }
    // Prepare Data
    const urls = ["/story/" + bid];
    for (let i = 1; i <= b_total_chapters; i++) {
        urls.push("/story/" + bid + "/" + i);
    }
    const listener_click = async ({target}) => {
        const existing = JSON.parse(await idbKeyval.get('offline_stories')) || {stories: {}};
        const cache = await caches.open('offline');
        b_elems.forEach( el => el.setAttribute('disabled','') );
        if (existing.stories[bid]) {
            confirmation("Remove from your offline stories?").then(async v => {
                if (!v) {return;}
                urls.forEach(ur => cache.delete(ur) );
                delete existing.stories[bid];
                const key_res = await api('offline',{ data: {
                    action: "return",
                    key: existing.key || "",
                    book_id: bid
                } });
                existing.key = key_res.key;
                await idbKeyval.set('offline_stories',JSON.stringify(existing));
                b_elems.forEach( el => el.classList.remove('active') );
            });
            b_elems.forEach( el => el.removeAttribute('disabled') );
            return;
        }
        existing.stories[bid] = {
            ID: bid,
            title: b_title,
            author: b_author,
            chapters: b_total_chapters,
            time_added: Date.now()
        };
        // Hook
        b_hooks.started_saving();
        const chapter_length = urls.length-1;
        for (let i = 0; i <= chapter_length; i++) {
            const ur = urls[i];
            await cache.put(ur, await fetch( ur, { credentials: 'omit' } ) );
            if (i<1){continue;}
            b_hooks.save_chapter(i,chapter_length);
        }
        const response = await api('offline',{ data: {
            action: "borrow",
            key: existing.key || "",
            book_id: bid
        } });
        existing.key = response.key;
        await idbKeyval.set('offline_stories',JSON.stringify(existing));
        b_elems.forEach( el => {
            el.removeAttribute('disabled');
            el.classList.add('active');    
        });
        b_hooks.finished_saving();
    }
    // Reacting
    b_elems.forEach(el => el.addEventListener('click',listener_click));
}