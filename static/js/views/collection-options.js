OPT_BOOK_IN_COLLECTIONS = true;
function follow_collection(collection_id,follow = true){
    if (! logged_in) {
        prompt_login();
        new toast("Login to follow collection.");
        return;
    }
    api('follow_collection',{
        dataType: 'JSON',
        data: {
            follow,
            collection_id,
        },
        callback: function(response){
            if (response.code > 5){
                new toast('An error occured.');
            }
            if (response.code === 1){
                new toast('Followed Collection.');
            }
            else if (response.code === 2){
                new toast('Unfollowed Collection.');
            }
        }
    });
}
if (document.querySelector('collection-bar > button[label="Create New"]')) {
    document.querySelector('collection-bar > button[label="Create New"]').addEventListener('click',event => {
        if (! logged_in) {
            prompt_login();
            new toast('Login to create collection.');
            return;
        }
        create_collection_open('new');
    });    
}
if (document.querySelector('collection-bar > button[label="My Collections"]')) {
    document.querySelector('collection-bar > button[label="My Collections"]').addEventListener('click',event => {
        if (! logged_in) {
            prompt_login();
            new toast('Login to create and add books to your collections.');
            return;
        }
        window.location.href = '@me/collections'
    });
}