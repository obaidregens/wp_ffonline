const full_url_parts = window.location.href.split('/').filter((el) => el !== '');
if (document.querySelector('chat-list').children.length === 0 && full_url_parts[full_url_parts.length-1] === 'inbox') {
    window.addEventListener('load',() => {
        document.querySelector('user-info').remove();
        document.querySelector('send-message').remove();
        const pp = DOM.create('popup',{
            children: [
                DOM.create('h3',{
                    innerText: 'No messages yet.'
                }),
                DOM.create('p',{
                    innerText: 'To message someone, visit their profile and click on the chat button in the bottom-right corner.'
                }),
            ]
        });
        popup.create(pp);
        popup.open(pp);
        const pOver = popup.overlay;
        pOver.style.pointerEvents = 'none';
        pp.style.maxWidth = '600px';
    });
}
else {
    if (full_url_parts[full_url_parts.length-1] !== 'inbox'){
        const username = full_url_parts[full_url_parts.length-1];
        const existing_user_elem = document.querySelector(`chat-list > [username="${username}"]`);
        if (existing_user_elem){
            existing_user_elem.remove();
        }
        const chat_elem = DOM.create('chat',{
            attributes: {
                username,
                unread: "0"
            }
        });
        document.querySelector('chat-list').prepend(chat_elem);
    }
    // Back Button
    document.querySelector('user-info > button.back').addEventListener('click',() => {
        document.querySelector('chat-list').classList.add('show');
    })
    // Block Button
    document.querySelector('user-info > button.block').addEventListener('click',function(){
        this.classList.toggle('blocked');
        api('block',{
            dataType: 'JSON',
            data: {
                block: document.querySelector('user-info > button.block').classList.contains('blocked'),
                username: document.querySelector('messages').getAttribute('username').substr(1),
            }
        });
    });

    document.querySelector('chat-list').addEventListener('click',function(event){
        if (event.target.tagName.toLowerCase() !== 'chat'){
            return;
        }
        const username = event.target.getAttribute('username').substr(1);
        for (let i = 0; i < this.children.length; i++) {
            this.children[i].removeAttribute('active');
        }
        event.target.setAttribute('active','');
        event.target.setAttribute('unread','0');

        const messages_wrapper = document.querySelector('messages');
        messages_wrapper.classList.add('loading');
        messages_wrapper.setAttribute('username','@' + username);
        DOM.update(document.querySelector('user-info > a'),{
            innerText: '@' + username,
            attributes: {
                href: '/@' + username,
            }
        });
        load_chat(username,function(){
            document.querySelector('chat-list').classList.remove('show');
            setTimeout(() => {
                const new_messages_tab = messages_wrapper.querySelector('new-messages');
                if (new_messages_tab){
                    new_messages_tab.scrollIntoView();
                    messages_wrapper.scrollTop = messages_wrapper.scrollTop - 30;
                }
                else {
                    console.log(messages_wrapper.scrollTop,messages_wrapper.scrollHeight);
                    messages_wrapper.scrollTop = messages_wrapper.scrollHeight;
                }    
            });
        });
    });
    // document.querySelector('chat-list > chat:first-child').dispatchEvent(new Event('click', {bubbles: true}));

    // Message Validation
    document.querySelector('send-message > text-input > input').addEventListener('input',function(){
        if (this.value.length > 150){
            this.value = this.value.substr(0,150);
        }
        _.prop(document.querySelector('send-message > button'),'disabled',this.value === '');
    });  

    // Submit Messages with enter key shortcut
    document.querySelector('send-message > text-input > input').addEventListener('keyup',function(event){
        if (event.keyCode !== 13){
            return;
        }
        document.querySelector('send-message > button').dispatchEvent(new Event('click'));
    });
    document.querySelector('send-message > button').addEventListener('click',function(){
        const messages_wrapper = document.querySelector('messages');
        const messages_input = this.previousElementSibling.querySelector('input');
        const messages_content = messages_input.value;
        if (messages_content === ''){
            return;
        }

        messages_input.value = '';
        messages_wrapper.appendChild(DOM.create('message',{
            innerText: messages_content,
            attributes: {
                time: 'Just Now',
                status: 'sent',
                my: ""
            }
        }));
        messages_wrapper.scrollTop = messages_wrapper.scrollHeight;
        api('send_message',{
            dataType: 'JSON',
            data: {
                to: messages_wrapper.getAttribute('username').substr(1),
                message: messages_content
            }
        });
    });
    // Retrieve Messages.
    function load_chat(username,callback = function(){}){
        if (username === true) {
            username = document.querySelector('messages').getAttribute('username').substr(1);
        }
        new Promise(function(resolve, reject){
            api('get_chat',{
                dataType: 'JSON',
                data: {
                    username
                },
                callback: (response) => {
                    const messages_wrapper = document.querySelector('messages');
                    if (messages_wrapper.getAttribute('username').substr(1) !== username){
                        reject('Changed Username');
                        return;
                    }
                    // Add Messages
                    const frag = document.createDocumentFragment();
                    for (let i = 0; i < response.messages.length; i++) {
                        const msg = response.messages[i];
                        if (msg.new){
                            frag.appendChild(DOM.create('new-messages'));
                        }
                        const attr = {
                            time: _t.local(new Date(parseInt(msg.time))),
                        };
                        attr[msg.from] = '';
                        frag.appendChild(DOM.create('message',{
                            innerText: msg.message,
                            attributes: attr
                        }));
                    }
                    messages_wrapper.innerText = '';
                    messages_wrapper.appendChild(frag);
                    messages_wrapper.classList.remove('loading');

                    // Blocking
                    const blocked_button = document.querySelector('user-info > button.block');
                    const message_input = document.querySelector('send-message > text-input > input');
                    _.prop(message_input,'disabled',response.chat_blocked);
                    response.blocked ? blocked_button.classList.add('blocked') : blocked_button.classList.remove('blocked');
                    resolve('Loaded');
                }
            })
        })
        .then(callback,function(){});
    }
    // Interval to refresh chat
    const lfg = setInterval(load_chat,8000,
        true,() => {
            const messages_wrapper = document.querySelector('messages');
            const messagesHeight = parseInt(getComputedStyle(messages_wrapper).getPropertyValue('height'));
            const new_messages_tab = messages_wrapper.querySelector('new-messages');
            if (! new_messages_tab || messages_wrapper.scrollTop >= (new_messages_tab.offsetTop - messagesHeight) ){
                return;
            }
            const new_messages_prompt = DOM.create('new-messages-prompt',{
                listeners: {
                    click: function(){
                        new_messages_tab.scrollIntoView();
                        messages_wrapper.scrollTop = messages_wrapper.scrollTop - 30;
                        this.remove();
                    }
                }
            });
            messages_wrapper.appendChild(new_messages_prompt);
            setTimeout(function(){
                new_messages_prompt.classList.add('show');
            });
        }
    );
    document.querySelector('messages').addEventListener('scroll',function(){
        const new_messages_prompt = this.querySelector("new-messages-prompt");
        if (! new_messages_prompt){
            return;
        }
        const new_messages_tab = this.querySelector('new-messages');
        const messagesHeight = parseInt(getComputedStyle(this).getPropertyValue('height'));
        if (this.scrollTop >= (new_messages_tab.offsetTop - messagesHeight) ){
            new_messages_prompt.remove();
        }
        
    },{passive: true});
}