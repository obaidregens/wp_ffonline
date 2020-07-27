document.querySelector('collections-container').addEventListener('click',function(event){
    if (! event.target.classList.contains('follow-collection')){
        return;
    }
    event.target.classList.toggle('followed');
    const collection_id = event.target.parentElement.getAttribute('collection_id');
    const follow = event.target.classList.contains('followed');
    follow_collection(collection_id,follow);
});