window.addEventListener('load',() => {
    let Track = JSON.parse(localStorage.getItem('chapter_track-' + book_id));
    if ( Track !== null && (Date.now() - parseInt(Track.timestamp)) > 1000*60*60*1 ){
        localStorage.setItem('chapter_track_follow',0);
    }
    const chapter_num = document.querySelector('chapter').getAttribute('num');
    if (Track !== null && localStorage.getItem('chapter_track_follow') !== book_id.toString() ){
        confirmation("Do you want to continue reading where you left of?").then(v => {
            if (!v){return;}
            window.location.href = "/story/" + book_id + "/" + Track.chapter_num + "#" + Track.paragraph
        });
    }
    localStorage.setItem('chapter_track_follow',book_id);
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
            localStorage.setItem('chapter_track-' + book_id,JSON.stringify({
                chapter_num,
                paragraph: paraI,
                timestamp: Date.now()
            }));
        });    
    },10000);
});