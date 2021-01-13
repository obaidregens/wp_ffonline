let page_ids = {};
let loading_page = false;
let setFeed;
(() => {
    const intMap = num => parseInt(num);

    const start_page = parseInt(DOM.q("books-container").getAttribute("page"));
    page_ids[start_page] = [...DOM.qa('books-container a.book')].map(bel => bel.getAttribute("book_id"));

    let lastChange = 0;
    const loadPage = async (page,on) => {
        lastChange++;
        const currentChange = lastChange;

        const refresh = on === "refresh";
        const books_cont = DOM.q('books-container');
        if (refresh) {
            books_cont.innerText = "";
            collections.book_collections = {};
            page_ids = {};
            on = "before";
        }
        books_cont.insertAdjacentHTML(on === "before" ? "beforebegin" : "afterend",loading_component);
        const response = await api('feed',{
            data: {page,type: books_cont.getAttribute('feed_type')}
        });
        if (currentChange !== lastChange) {
            return;
        }
        if (refresh) {
            DOM.q('books-container').setAttribute('pages',response.pages);
        }
        collections.book_collections = Object.assign(
            collections.book_collections || {},
            response.book_collections
        );
        let toRemove = -1;
    
        const scrollPos = _scroll.documentTop;
        if (on === "after") {
            toRemove = page-3;
            books_cont.insertAdjacentHTML("beforeend", response.output);
        }
        else if (on === "before") {
            toRemove = page+3;
            books_cont.insertAdjacentHTML("afterbegin", response.output);
        }
    
        page_ids[page] = response.ids;
        
        reChapterProgress();
        reHookOffline();
    
        setTimeout(() => {
            let scrollRemove = 0;
            let scrollAdd = 0;
            if (page_ids[toRemove]) {
                // Keep Scroll
                DOM.qa('a.book').forEach(book => {
                    const b_id = book.getAttribute('book_id');
                    if (response.ids.includes(b_id)) {
                        scrollAdd += book.parentElement.parentElement.scrollHeight;
                    }
                    else if (page_ids[toRemove].includes(b_id)) {
                        scrollRemove += book.parentElement.parentElement.scrollHeight;
                        book.parentElement.parentElement.remove();
                    }
                });
                page_ids[toRemove].forEach(removeBook => delete collections.book_collections[removeBook]);
                delete page_ids[toRemove];
            }
            _scroll.documentTop = on === "before" ? (scrollPos + scrollAdd) : (scrollPos - scrollRemove);
            loading_page = false;
            DOM.qa("autoload-wrapper").forEach(loader => loader.remove());
        });
    }
    setFeed = async type => {
        const remove_disabled = [];
        DOM.qa(".tab > li").forEach(tab => {
            type = type === "feed" ? "feed" : "reading";
            const action = tab.innerText.toLowerCase() === type ? "add" : "remove";
            if (action === "remove") {
                remove_disabled.push(tab);
                tab.setAttribute("disabled","");
            }
            tab.classList[action]('active');
        });
        const books_cont = DOM.q('books-container');
        books_cont.setAttribute("feed_type",type);
        await loadPage(1,"refresh");
        if (type === "feed" && !books_cont.querySelector('a.book')) {
            FeedOptions("open");
        }
        
        remove_disabled.forEach(tab => tab.removeAttribute('disabled'));
    };
    window.addEventListener("scroll",evt => {
        if (loading_page) {
            return;
        }
        const max_pages = parseInt(DOM.q("books-container").getAttribute("pages"));
        if (Object.keys(page_ids).filter(pg => intMap(pg) > 0 && pg <= max_pages).length < 1) {
            return;
        }
    
        loading_page = true;
        const books_in_view = im('books').map(intMap);
        let pages_in_view = [];
        books_in_view.forEach(book_id_in_view => {
            Object.entries(page_ids).forEach( ([page_in,book_ids_of_page]) => {
                book_ids_of_page = book_ids_of_page.map(intMap);
                if ( book_ids_of_page.includes(book_id_in_view) ) {
                    pages_in_view.push(intMap(page_in));
                }
            });
        });
        const MaxPage = Math.max(...pages_in_view);
        if (!page_ids[MaxPage-1] && MaxPage > 1) {
            return loadPage(MaxPage-1,"before");
        }
        else if (!page_ids[MaxPage+1] && MaxPage < max_pages) {
            return loadPage(MaxPage+1,"after");
        }    
        loading_page = false;
    },{ passive: true });
    const loading_component = `<autoload-wrapper><autoloader><div class="dot-loader"></div><div class="dot-loader dot-loader--2"></div><div class="dot-loader dot-loader--3"></div></autoloader><style>autoload-wrapper{display:flex;justify-content:center;}autoloader{display:flex;justify-content:center;margin: 10px 0}.dot-loader{height:20px;width:20px;border-radius:50%;background-color:var(--theme-color);position:relative;-webkit-animation:1.2s grow ease-in-out infinite;animation:1.2s grow ease-in-out infinite}.dot-loader--2{-webkit-animation:1.2s grow ease-in-out infinite .15555s;animation:1.2s grow ease-in-out infinite .15555s;margin:0 20px}.dot-loader--3{-webkit-animation:1.2s grow ease-in-out infinite .3s;animation:1.2s grow ease-in-out infinite .3s}@-webkit-keyframes grow{0%,100%,40%{-webkit-transform:scale(0);transform:scale(0)}40%{-webkit-transform:scale(1);transform:scale(1)}}@keyframes grow{0%,100%,40%{-webkit-transform:scale(0);transform:scale(0)}40%{-webkit-transform:scale(1);transform:scale(1)}}</style></autoload-wrapper>`;
    
})();