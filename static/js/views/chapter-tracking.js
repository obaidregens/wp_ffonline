window.addEventListener('load',() => {
    let Track = JSON.parse(localStorage.getItem('chapter_track-' + book_id));
    // HasBeenLeft => 10 min
    let cStory = {};
    try {
        cStory = JSON.parse(localStorage.getItem('chapter_track_follow')) || {};
    } catch { cStory = {}; }
    const chapter_num = document.querySelector('chapter').getAttribute('num');
    if (Track !== null) {
        const hasBeenLeft = (Date.now() - Math.max(parseInt(cStory.timestamp || 0),parseInt(Track.timestamp)) ) > 1000*60*10;
        if (  ( hasBeenLeft || (cStory.book_id || 0).toString() !== book_id.toString() ) ){
            confirmation("Do you want to continue reading where you left of?").then(v => {
                if (!v){return;}
                window.location.href = "/story/" + book_id + "/" + Track.chapter_num + "#" + Track.paragraph
            });
        }    
    }
    localStorage.setItem('chapter_track_follow',JSON.stringify({
        book_id,
        timestamp: Date.now()
    }));
    setTimeout(() => {
        _.scrollEnd(() => {
            const paras = document.querySelectorAll('chapter > content > p');
            let paraI = null;
            for (let i = 0; i < paras.length; i++) {
                const co = paras[i].getBoundingClientRect().y+100;
                if (co > 0){
                    paraI = i+1;
                    break;
                }
            }
            localStorage.setItem('chapter_track_follow',JSON.stringify({
                book_id,
                timestamp: Date.now()
            }));        
            localStorage.setItem('chapter_track-' + book_id,JSON.stringify({
                chapter_num,
                paragraph: paraI,
                timestamp: Date.now(),
                chapterProgress: parseFloat(( (chapter_num/document.querySelectorAll('popup.chapter-index').length)*100 ).toFixed(1))
            }));
        });    
    },10000);
});