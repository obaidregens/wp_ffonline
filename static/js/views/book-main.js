const book_id = document.querySelector('book').getAttribute('book_id');
document.querySelector('.book-collections').addEventListener('click',function(){
    collections_open(book_id);
});
document.querySelector('.book-share').addEventListener('click',function(){
    const book_title = document.querySelector('book-title');
    share_open({
        title: book_title.innerText,
        href: window.location.href,
        author: document.querySelector('author > a:first-child').innerText,
        desc: document.querySelector('book-description').innerText
    });
});
// Load Chapter Reading State
window.addEventListener('load',() => {
    let Track = JSON.parse(localStorage.getItem('chapter_track-' + book_id));
    if ( Track !== null && (Date.now() - parseInt(Track.timestamp)) > 1000*60*60*1 ){
        localStorage.setItem('chapter_track_follow',0);
    }
    if (Track !== null && localStorage.getItem('chapter_track_follow') !== book_id.toString() ){
        confirmation("Do you want to continue reading where you left of?").then(v => {
            if (!v){return;}
            window.location.href = "/story/" + book_id + "/" + Track.chapter_num + "#" + Track.paragraph
        });
    }
    localStorage.setItem('chapter_track_follow',book_id);
});