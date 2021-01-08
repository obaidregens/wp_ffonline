window.addEventListener('load',() => {
    const finishedReading = DOM.q('.next-chapter:not([href])');

    let waitForFinish = Date.now();
    const waitTime = 10000;
    PollFilters.push(datal => {
        if (Date.now() - waitForFinish <= waitTime ) {
            return;
        }
        finishedReading && finishedReading.classList.remove("finished");
        const paras = DOM.qa('chapter > content > *:not(author-notes)');
        let paraI = paras.length;
        for (let i = 0; i < paras.length; i++) {
            const co = paras[i].getBoundingClientRect().y+100;
            if (co > 0){
                paraI = i+1;
                break;
            }
        }
        const track_obj = {
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
    finishedReading && finishedReading.addEventListener('click',() => {
        if (finishedReading.classList.contains("finished")) {
            return;
        }
        finishedReading.classList.add("finished");
        waitForFinish = Date.now();
        const track_obj = {
            finished: true,
            story: book_id
        };
        if (!window.is_online) {
            const perc = "100%";
            track_obj.progress = perc;
            idbKeyval.set(`offline_track-${book_id}`,track_obj);
            return;
        }
        api('track_chapter',{data: {
            track: [track_obj]
        }});
        waitForFinish = Date.now();
    });
    async function paraFromHash(){
        const rawHash = window.location.hash.substr(1);

        const paraNum = parseInt(rawHash) || 0;
        const allParas = DOM.qa(`chapter > content > *:not(author-notes)`);
        const paraTo = allParas[(rawHash === "end" ? allParas.length : paraNum)-1];
        if (! paraTo){
            return;
        }
        _scroll.to(paraTo);
    }
    window.addEventListener('hashchange',paraFromHash);
    paraFromHash();
});