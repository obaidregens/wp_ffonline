if (document.querySelector('.author-follow')) {
    document.querySelector('.author-follow').addEventListener('click',({target}) => {
        if (! logged_in) {
            new toast('Login to follow author');
            prompt_login();
            return;
        }
        api('follow_user',{
            data: {
                user_id: document.querySelector('author-main').getAttribute('user_id')
            },
            callback: response => {
                if (response.code > 5) {
                    new toast("An error occured");
                    return;
                }
                if (response.code === 2) {
                    target.classList.remove('followed');
                    new toast('Author unfollowed');
                }
                else if (response.code === 1){
                    target.classList.add('followed');
                    new toast('Author followed');
                }
            }
        });
    });
}