DOM.qa('single > cell:last-child > a').forEach(ela => {
    ela.addEventListener('click',async () => {
        const res = await confirmation("Sure?");
        if (!res) {
            return;
        }
        const {code} = await api("verify_confirm",{data:{
            id: ela.getAttribute('cid')
        }});
        if (code > 5) {
            return new toast("An error occured");
        }
        window.location.reload();
    });
});