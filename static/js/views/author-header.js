(() => {
    const follows_el = DOM.qa("author-nav .author-follow,floater .author-follow");
    if (follows_el.length > 0) {
        follows_el.forEach(ele => ele.addEventListener('click',() => {
            if (! logged_in) {
                new toast('Login to follow author');
                prompt_login();
                return;
            }
            api('follow_user',{
                data: {
                    user_id: DOM.q('author-name').getAttribute('user_id')
                },
            })
            .then(response => {
                if (response.code === 10) {
                    new toast("You can't follow yourself!");
                    return;
                }
                if (response.code > 5) {
                    new toast("An error occured");
                    return;
                }
                if (response.code === 2) {
                    follows_el.forEach(el => el.classList.remove('followed'));
                    new toast('Author unfollowed');
                }
                else if (response.code === 1){
                    follows_el.forEach(el => el.classList.add('followed'));
                    new toast('Author followed');
                }
            });
        }));
    }
})();