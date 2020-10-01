const pi = DOM.create('popup',{
    attributes: {
        change_question: ""
    },
    children: [
        create_text_input({type: 'textarea',label: 'Change Question'}),
        DOM.create('button',{
            attributes: {
                label: "Save"
            },
            listeners: {
                click: ({target}) => {
                    document.querySelector('reply > blockquote').innerText =
                    target.previousElementSibling.querySelector('textarea').value;
                    target.previousElementSibling.querySelector('textarea').dispatchEvent(new Event('change'));
                    popup.close();
                }
            }
        })
    ]
});
popup.create(pi);
document.querySelector('question-wrapper > a').addEventListener('click',({target}) => {
    document.querySelector('reply').classList.add('show');
    document.querySelector('reply').setAttribute('question-id',target.parentElement.getAttribute('question-id'));
    document.querySelector('reply > blockquote').innerText = target.previousElementSibling.innerText;
});
document.querySelector('reply > a').addEventListener('click',({target}) => {
    popup.open(pi);
    pi.querySelector('textarea').value = target.previousElementSibling.innerText;
    pi.querySelector('textarea').dispatchEvent(new Event('change'));
});
document.querySelector('button[label="Reply"]').addEventListener('click',({target}) => {
    api('reply_to_question',{
        data: {
            question_id: target.parentElement.getAttribute('question-id'),
            question: target.parentElement.querySelector('blockquote').innerText,
            answer: target.parentElement.querySelector('text-input > textarea').value,
        },
        callback: response => {
            if (response.code > 5) {
                new toast('An error occured');
                return;
            }
            new toast("Replied");
            setTimeout(() => window.location.reload(),1000);
        }
    });
});