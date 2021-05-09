if (DOM.q('.add-update')){
    DOM.q('.add-update').addEventListener('click',function(){
        let __pop = DOM.q('popup_s[add-update]');
        if (__pop){
            popup_s.open(__pop);
            return;
        }
        const txt_update = create_text_input({
            type: "textarea",
            label: 'Add Update'
        });
        __pop = DOM.create('popup_s',{
            attributes: {
                "add-update": ""
            },
            children: [
                txt_update,
                DOM.create('button',{
                    attributes: {
                        label: 'Post'
                    },
                    listeners: {
                        click: function(){
                            api('add_update',{
                                data: {
                                    update: txt_update.querySelector('textarea').value,
                                }
                            })
                            .then(response => {
                                if (response.code > 5){
                                    new toast('An error occured.');
                                    return;
                                }
                                window.location.reload();
                            });
                        }
                    }
                })
            ]
        });
        popup_s.create(__pop);
        popup_s.open(__pop);
    });
}