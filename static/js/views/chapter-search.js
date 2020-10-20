const search_popup = document.querySelector('popup.search-story');
search_popup.addEventListener('onOpen',() => {
    window.acsSwipeEnabled = false;
    search_popup.querySelector('form > text-input:first-child > input').focus();
});
search_popup.addEventListener('onAfterClose',() => window.acsSwipeEnabled = true );
window.addEventListener('keydown',function(event){
    if (event.key.toLowerCase() !== 'f' || !_.cmd(event) ){
        return;
    }
    event.preventDefault();
    if (search_popup.classList.contains('show')){
        popup.close();
        return;
    }
    popup.open(search_popup);
});
function paraFromHash(){
    const rawHash = window.location.hash.substr(1);
    if (rawHash === "progress") {
        const Track = JSON.parse(localStorage.getItem('chapter_track-' + book_id));
        if (Track === null) {
            return;
        }
        window.location.href = `/story/${book_id}/${Track.chapter_num}#${Track.paragraph}`;
    }
    const paraNum = parseInt(rawHash);
    if (! paraNum){
        return;
    }
    const paraTo = document.querySelector(`chapter > content > p:nth-of-type(${paraNum})`);
    paraTo.scrollIntoView();
    const ht = document.querySelector('html');
    ht.scrollTop = ht.scrollTop - 55;
}
window.addEventListener('hashchange',paraFromHash);
paraFromHash();
document.querySelector('popup.search-story > form').addEventListener('submit',function(event){
    event.preventDefault();
	if (typeof grecaptcha === 'undefined') {
		new toast("You're offline.");
		return;
    }
    const results_elem = document.querySelector('popup.search-story > results');
    results_elem.classList.add('loading');
    api('search_book_contents',{
		data: {
            chapter_id,
            s: this.querySelector('text-input > input').value
        },
        dataType: 'JSON',
		callback: function(response){
            const all_results = document.createDocumentFragment();
            for (let i = 0; i < response.results.length; i++) {
                const result = response.results[i];
                const result_wrapper = document.createElement('a');
                result_wrapper.href = result.link;
                result_wrapper.setAttribute('result','');

                const result_title = document.createElement('result-title');
                result_title.innerText = result.title;
                result_wrapper.appendChild(result_title);
                
                const result_excerpt = document.createElement('result-excerpt');
                result_excerpt.innerHTML = result.excerpt;
                result_wrapper.appendChild(result_excerpt);
                all_results.appendChild(result_wrapper);
            }
            results_elem.innerText = '';
            results_elem.appendChild(all_results);
            results_elem.classList.remove('loading');
            if (response.exceeded){
                results_elem.classList.add('exceeded');
            }
            else{
                results_elem.classList.remove('exceeded');
            }
		}
	});
});