(() => {
    const feedback_pop = DOM.create('popup',{
        classes: ['bottom'],
        attributes: {
            feedback: ""
        },
        children: [
            create_text_input({label: "Message @beta",attributes: {
                maxlength: 150
            },keydown: ({target,key}) => {
                if (key.toLowerCase() !== "enter") {
                    return;
                }
                target.parentElement.nextElementSibling.dispatchEvent(new Event('click'))
            }}),
            DOM.create('button',{
                attributes: {
                    label: "Send"
                },
                listeners: {
                    click: async ({target}) => {
                        popup.close();
                        const message_input = target.previousElementSibling.querySelector('input');
                        const {code} = await api('send_beta_feedback',{
                            data: {
                                message: message_input.value
                            }
                        });
                        if (code > 5) {
                            new toast("An error occured");
                            return;
                        }
                        message_input.value = "";
                        new toast("Feedback Sent! Anything else?");
                    }
                }
            })
        ],
        listeners: {
            onOpen: ({target}) => target.querySelector('input').focus()
        }
    });
    popup.create(feedback_pop);
    const bar = document.documentElement.appendChild(DOM.create('beta-bar',{
        children: [
            DOM.create('text',{
                innerHTML: "You're in beta. Switch to <a>main site.</a>"
            }),
            DOM.create('a',{
                innerHTML: "Feedback",
                listeners: {
                    click: () => popup.open(feedback_pop)
                }
            }),
            DOM.create('button',{
                listeners: {
                    click: ({target}) => target.parentElement.remove()
                }
            })
        ]
    }));
})();