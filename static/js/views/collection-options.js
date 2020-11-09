function follow_collection(collection_id,follow = true){
    if (! logged_in) {
        prompt_login();
        new toast("Login to follow collection.");
        return;
    }
    api('follow_collection',{
        data: {
            follow,
            collection_id,
        }
    })
    .then(response => {
        if (response.code > 5){
            new toast('An error occured.');
        }
        if (response.code === 1){
            new toast('Followed Collection.');
        }
        else if (response.code === 2){
            new toast('Unfollowed Collection.');
        }
    });
}
if (DOM.q('collection-bar > button[label="Create New"]')) {
    DOM.q('collection-bar > button[label="Create New"]').addEventListener('click',event => {
        if (! logged_in) {
            prompt_login();
            new toast('Login to create collection.');
            return;
        }
        collections.edit('new');
    });    
}
if (DOM.q('collection-bar > button[label="My Collections"]')) {
    DOM.q('collection-bar > button[label="My Collections"]').addEventListener('click',event => {
        if (! logged_in) {
            prompt_login();
            new toast('Login to create and add books to your collections.');
            return;
        }
        window.location.href = '/@me/collections'
    });
}
// Load Collections
(async () => {
    const res = await api("get_collections",{
        data: {book_ids: []}
    });
    collections.book_collections = res.book_collections;
    collections.collections_data = res.collections_data;
})();