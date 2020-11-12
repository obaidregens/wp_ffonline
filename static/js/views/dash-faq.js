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
                    DOM.q('reply > blockquote').innerText =
                    target.previousElementSibling.querySelector('textarea').value;
                    target.previousElementSibling.querySelector('textarea').dispatchEvent(new Event('change'));
                    popup.close();
                }
            }
        })
    ]
});
popup.create(pi);
DOM.qa('question-wrapper > a:first-of-type').forEach(el => {
    el.addEventListener('click',({target}) => {
        DOM.q('reply').classList.add('show');
        DOM.q('reply').setAttribute('question-id',target.parentElement.getAttribute('question-id'));
        DOM.q('reply > blockquote').innerText = target.previousElementSibling.innerText;
    });
});
DOM.qa('question-wrapper > a:last-of-type').forEach(el => {
    el.addEventListener('click',async ({target}) => {
        await api('archive_question',{data: {id: target.parentElement.getAttribute('question-id')}});
        new toast("Archived");
        window.location.reload();
    });
});
DOM.q('reply > a').addEventListener('click',({target}) => {
    popup.open(pi);
    pi.querySelector('textarea').value = target.previousElementSibling.innerText;
    pi.querySelector('textarea').dispatchEvent(new Event('change'));
});
DOM.q('button[label="Reply"]').addEventListener('click',({target}) => {
    api('reply_to_question',{
        data: {
            category: DOM.q('text-input > input').value,
            question_id: target.parentElement.getAttribute('question-id'),
            question: target.parentElement.querySelector('blockquote').innerText,
            answer: target.parentElement.querySelector('text-input:nth-of-type(2) > textarea').value,
            link: target.previousElementSibling.querySelector('input').value,
        }
    })
    .then(response => {
        if (response.code > 5) {
            new toast('An error occured');
            return;
        }
        new toast("Replied");
        setTimeout(() => window.location.reload(),1000);
    });
});