(() => {
    const existing = JSON.parse(localStorage.getItem('offline_stories')) || {};
    if (existing[book_id]) {
        document.querySelector('chapter > book-options > .book-offline').classList.add('active');
    }
})();
document.querySelector('chapter > book-options > .book-offline').addEventListener('click',async ({target}) => {
    const total_chapters = document.querySelectorAll('chapter-header + popup > index > a').length;
    const urls = [
        "/story/" + book_id
    ];
    for (let i = 1; i <= total_chapters; i++) {
        urls.push("/story/" + book_id + "/" + i);
    }
    const existing = JSON.parse(localStorage.getItem('offline_stories')) || {};
    const cache = await caches.open('offline');
    target.setAttribute('disabled',"");
    if (existing[book_id]) {
        confirmation("Remove from your offline stories?").then(v => {
            if (!v) {return;}
            urls.forEach(ur => cache.delete(ur) );
            delete existing[book_id];
            localStorage.setItem('offline_stories',JSON.stringify(existing));
            target.classList.remove('active');
        });
        target.removeAttribute('disabled');
        return;
    }
    existing[book_id] = {
        ID: book_id,
        title: document.querySelector('book-info > a.title').innerText,
        author: document.querySelector('book-info > author > a').innerText,
        chapters: total_chapters,
        time_added: Date.now()
    };
    for (let i = 0; i < urls.length; i++) {
        const ur = urls[i];
        await cache.put(ur, await fetch( ur, { credentials: 'omit' } ) );
    }
    await api('offline_chapter',{
        data: {chapter_id}
    });
    localStorage.setItem('offline_stories',JSON.stringify(existing));
    target.removeAttribute('disabled');
    target.classList.add('active');
});