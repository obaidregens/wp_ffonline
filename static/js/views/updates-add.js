if (document.querySelector('.add-update')){
    document.querySelector('.add-update').addEventListener('click',function(){
        let __pop = document.querySelector('popup[add-update]');
        if (__pop){
            popup.open(__pop);
            return;
        }
        const txt_update = create_text_input({
            type: "textarea",
            label: 'Add Update'
        });
        __pop = DOM.create('popup',{
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
        popup.create(__pop);
        popup.open(__pop);
    });
}