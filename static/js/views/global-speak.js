window.addEventListener('load',() => {
    if (!'speechSynthesis' in window) {
        document.querySelector('acs-options').appendChild(DOM.create('button',{
            classes: ['listen'],
            listeners: {
                click: () => new toast("Read aloud isn't supported on your device.")
            }
        }));
        return;
    }
    const speak_inst = new speak(document.querySelector('.acs-elem'),false);
    const cs = 'playing';
    const pause_button = DOM.create('button',{
        classes: ['pause'],
        listeners: {
            click: () => {
                const speaking = pause_button.classList.contains(cs);
                speaking ? pause_button.classList.remove(cs) : pause_button.classList.add(cs);
                speaking ? speak_inst.pause() : speak_inst.resume();
            }
        },
    });
    speak_inst.onend = () => pause_button.classList.remove(cs);
    const panel = DOM.create("speak-panel",{
        children: [
            DOM.create('button',{
                classes: ['prev'],
                listeners: {
                    click: () => {
                        speak_inst.prev.bind(speak_inst)();
                        pause_button.classList.add(cs);
                    }
                },
            }),
            pause_button,
            DOM.create('button',{
                classes: ['next'],
                listeners: {
                    click: () => {
                        speak_inst.next.bind(speak_inst)();
                        pause_button.classList.add(cs);
                    }
                },
            }),
        ]
    });
    document.documentElement.appendChild(panel);
    // MMMMMMMMMM
    const htmlEl = document.querySelector('html');
    document.querySelector('acs-options').appendChild(DOM.create('button',{
        classes: ['listen'],
        listeners: {
            click: ({target}) => {
                htmlEl.classList.remove('show-acs');
                const a = target.classList.contains('active');
                target.classList.toggle('active');
                a ? panel.classList.remove('show') : panel.classList.add('show');

                const b = pause_button.classList.contains('playing')
                if ( (a && b) || !a ) {
                    pause_button.dispatchEvent(new Event('click'));
                }
            }
        }
    }));
});