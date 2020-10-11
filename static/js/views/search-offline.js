(() => {
    const existing = JSON.parse(localStorage.getItem('offline_stories')) || {};
    document.querySelectorAll('book').forEach(el => {
        const book_id = el.getAttribute('book_id');
        if (existing[book_id]) {
            el.querySelector('options > [label="Offline"]').classList.add('active');
        }    
    });
})();
document.querySelectorAll('book > options > [label="Offline"]').forEach(el => {
    el.addEventListener('click',async ({target}) => {
        const book = target.closest('book');
        const book_id = book.getAttribute('book_id');
        const total_chapters = parseInt(book.getAttribute('chapters'));
        const urls = [
            "/story/" + book_id
        ];
        for (let i = 1; i <= total_chapters; i++) {
            urls.push("/story/" + book_id + "/" + i);
        }
        const existing = JSON.parse(localStorage.getItem('offline_stories')) || {};
        const cache = await caches.open('offline');
        target.setAttribute('progress',"");
        if (existing[book_id]) {
            confirmation("Remove from your offline stories?").then(v => {
                if (!v) {return;}
                urls.forEach(ur => cache.delete(ur) );
                delete existing[book_id];
                localStorage.setItem('offline_stories',JSON.stringify(existing));
                target.classList.remove('active');
            });
            target.removeAttribute('progress');
            return;
        }
        existing[book_id] = {
            ID: book_id,
            title: book.querySelector('.title').innerText,
            author: book.querySelector('.author > a').innerText,
            chapters: total_chapters,
            time_added: Date.now()
        };
        target.setAttribute('progress','Saving story');
        const chapter_length = urls.length-1;
        for (let i = 0; i < chapter_length+1; i++) {
            const ur = urls[i];
            await cache.put(ur, await fetch( ur, { credentials: 'omit' } ) );
            if (i<1){continue;}
            target.setAttribute('progress', `${i} of ${chapter_length}` );
        }
        await api('offline_chapter',{ data: {book_id} });
        localStorage.setItem('offline_stories',JSON.stringify(existing));
        target.removeAttribute('progress');
        target.classList.add('active');
    });
});