DOM.qa('author-single .author-follow').forEach(el => el.addEventListener('click',async () => {
    const user_id = el.getAttribute('user_id');
    const {code} = await api('follow_user',{data: {
        user_id
    }});
    if (code > 5) {
        return new toast("An error occured");
    }
    code === 2 ? el.classList.remove('followed') : el.classList.add('followed');
}));