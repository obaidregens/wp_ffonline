function follow_collection(collection_id,follow = true){
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