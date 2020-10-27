DOM.q('collections-container').addEventListener('click',function(event){
    if ( event.target.classList.contains('follow-collection') ){
        event.target.classList.toggle('followed');
        const collection_id = event.target.parentElement.getAttribute('collection_id');
        const follow = event.target.classList.contains('followed');
        follow_collection(collection_id,follow);
    }
    else if ( event.target.classList.contains('share-collection') ){
        const collection_elem = event.target.closest('collection');
        shareAPI({
            url: collection_elem.querySelector('a.title').href,
            title: collection_elem.querySelector('a.title').innerText,
            share_title: "'" + collection_elem.querySelector('a.title').innerText + "' is a great collection of fanfics.%0A%0ACheck it out:",
            author: collection_elem.querySelector('a.author').innerText,
            desc: "",
            callOnCopy: () => new toast("Copied!")
        });
    }
});