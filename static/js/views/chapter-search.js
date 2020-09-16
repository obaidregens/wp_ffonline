const search_popup = document.querySelector('button.search-button + popup');
popup.create(search_popup,{onOpen: function(){
    search_popup.querySelector('form > text-input:first-child > input').focus();
}});
window.addEventListener('keydown',function(event){
    if (event.keyCode !== 70 || (! event.ctrlKey && !event.metaKey) ){
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
    const paraNum = parseInt(window.location.hash.substr(1));
    if (! paraNum){
        return;
    }
    const paraTo = document.querySelector(`chapter > content > p:nth-child(${paraNum})`);
    paraTo.scrollIntoView();
}
window.addEventListener('hashchange',paraFromHash);
paraFromHash();
document.querySelector('button.search-button + popup > form').addEventListener('submit',function(event){
    event.preventDefault();
    const results_elem = document.querySelector('button.search-button + popup > results');
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