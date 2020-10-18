const thesaurus = document.documentElement.appendChild(DOM.create('thesaurus',{
    children: [
        DOM.create('loader',{
            attributes: {
                xxs: ''
            }
        }),
        DOM.create('words')
    ]
}));
document.documentElement.addEventListener('click',({target}) => {
    if ( !thesaurus.contains(target) && thesaurus.classList.contains('open') ) {
        thesaurus.classList.remove('open');
    }
});
function isWordBreak(st) {
    return [' ',',','.',':',';','?','(',')'].includes(st);
}
document.querySelector('editor').addEventListener('keydown',event => {
    if (['Escape','ArrowRight','ArrowLeft'].includes(event.key) && thesaurus.classList.contains('open') ) {
        thesaurus.classList.remove('open');
    }
    if ( !_.cmd(event) || event.key !== 'd') {
        return;
    }
    event.preventDefault();
    const sel = window.getSelection();
    const anchor = sel.anchorNode;
    const text = anchor.textContent;
    let b = sel.anchorOffset-1;
    let c = sel.anchorOffset;
    while ( text[b] && ! isWordBreak(text[b]) ) {
        b--;
    }
    while ( text[c] && ! isWordBreak(text[c]) ) {
        c++;
    }
    b++;
    const word = text.slice(b,c);
    const rSel = JSON.parse(JSON.stringify(DraftEditor.selection));

    const cRange = document.createRange();
    cRange.setStart(anchor,b);
    cRange.setEnd(anchor,c);
    rSel.anchor.offset = b;
    rSel.focus.offset = c;

    const co_ordinates = cRange.getBoundingClientRect();

    const left_pos = co_ordinates.left + co_ordinates.width;
    const top_pos = co_ordinates.top + co_ordinates.height;
    thesaurus.classList.remove('empty');
    thesaurus.classList.add('open');
    thesaurus.classList.add('loading');
    thesaurus.style.top = Math.min(top_pos,window.innerHeight-200) + 'px';
    thesaurus.style.left = Math.min(left_pos,window.innerWidth-150) + 'px';
    api('get_synonym',{
        data: {
            word
        },
        dataType: 'JSON',
        callback: response => {
            response = response.synonyms;
            thesaurus.classList.remove('loading');
            if (response.length === 0) {
                thesaurus.classList.add('empty');
                return;
            }
            const words = thesaurus.querySelector('words');
            words.innerText = '';
            for (let i = 0; i < response.length; i++) {
                words.appendChild(DOM.create('li',{
                    innerText: response[i],
                    attributes: {
                        tabindex: i+1
                    },
                    listeners: {
                        click: ({target}) => {
                            const newWord = target.innerText;
                            const lenDiff = word.length - newWord.length;
                            window.getSelection().collapse(anchor,Math.min(c+lenDiff,anchor.length));

                            thesaurus.classList.remove('open');
                            DraftEditor.selection = rSel;
                            insertEditorText(newWord);
                        },
                        keydown: (event) => {
                            if (['Escape','ArrowRight','ArrowLeft'].includes(event.key)) {
                                event.preventDefault();
                                thesaurus.classList.remove('open');
                                window.getSelection().collapse(anchor,c);
                            }
                            else if (event.key === 'Enter') {
                                event.preventDefault();
                                event.target.dispatchEvent(new Event('click'));
                            }
                            else if (event.key === 'ArrowDown') {
                                event.preventDefault();
                                const currentTabIndex =  parseInt(event.target.getAttribute('tabindex'));
                                const nextTabIndex = currentTabIndex >= response.length ? 1 : currentTabIndex+1;
                                words.querySelector('[tabindex="' + nextTabIndex + '"]').focus();
                            }
                            else if (event.key === 'ArrowUp') {
                                event.preventDefault();
                                const currentTabIndex =  parseInt(event.target.getAttribute('tabindex'));
                                const nextTabIndex = currentTabIndex <= 1 ? response.length : currentTabIndex-1;
                                words.querySelector('[tabindex="' + nextTabIndex + '"]').focus();
                            }
                        }
                    }
                }));
            }
            words.querySelector('[tabindex="1"]').focus();
        }
    });
});
