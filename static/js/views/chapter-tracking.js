window.addEventListener('load',() => {
    setTimeout(() => {
        PollFilters.push(datal => {
            const paras = DOM.qa('chapter > content > *:not(author-notes)');
            let paraI = paras.length;
            for (let i = 0; i < paras.length; i++) {
                const co = paras[i].getBoundingClientRect().y+100;
                if (co > 0){
                    paraI = i+1;
                    break;
                }
            }
            track_obj = {
                chapter: chapter_id,
                para: paraI
            };
            if (!window.is_online) {
                const perc = (parseInt(DOM.q('chapter').getAttribute("num"))/DOM.qa("chapter-index > a").length*100).toFixed(1) + "%";
                track_obj.progress = perc;
                idbKeyval.set(`offline_track-${book_id}`,track_obj);
                return datal;
            }
            datal.track = [track_obj];
            return datal;
        });    
    },7000);
    async function paraFromHash(){
        const rawHash = window.location.hash.substr(1);
        const paraNum = parseInt(rawHash);
        if (! paraNum){
            return;
        }
        const paraTo = DOM.qa(`chapter > content > *:not(author-notes)`)[paraNum-1];
        _scroll.to(paraTo);
    }
    window.addEventListener('hashchange',paraFromHash);
    paraFromHash();
});