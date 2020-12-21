(() => {
    const Smiley = ({size = 10}) => {
        return (
            <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 45 45"
            width={size}
            height={size}
            >
                <defs><clipPath id="a"><path d="M0 36h36V0H0v36z"/></clipPath></defs>
                <g clip-path="url(#a)" transform="matrix(1.25 0 0 -1.25 0 45)">
                    <path d="M36 18c0-9.941-8.059-18-18-18S0 8.059 0 18s8.059 18 18 18 18-8.059 18-18" fill="#ffcc4d"/>
                    <path d="M10.515 12.379c.045-.18 1.168-4.38 7.485-4.38 6.318 0 7.44 4.2 7.485 4.38a.499.499 0 0 1-.836.477c-.02-.02-1.954-1.856-6.65-1.856-4.693 0-6.63 1.837-6.647 1.856a.505.505 0 0 1-.598.08.5.5 0 0 1-.24-.557M14.5 22.5c0-1.934-1.119-3.5-2.5-3.5s-2.5 1.566-2.5 3.5c0 1.933 1.119 3.5 2.5 3.5s2.5-1.567 2.5-3.5M26.5 22.5c0-1.934-1.119-3.5-2.5-3.5s-2.5 1.566-2.5 3.5c0 1.933 1.119 3.5 2.5 3.5s2.5-1.567 2.5-3.5" fill="#664500"/>
                </g>
            </svg>
        )
    };
    const MessageIcon = () => {
        return <span className="message-icon"></span>;
    }
    const {useCallback,useState,createRef,useEffect} = React;
    let refreshChatsWith,
        loadChat,
        send,
        scrl,
        unScopedUsername,
        unScopedChatlist,
        messagesUpdateOn;
    const pendingChanges = {};
    let new_message_key = 0;

    function Chat () {
        const [chat_list,setChatList] = useState([]);
        const [message,setMessage] = useState("");
        const [username,setUsername] = useState("");
        const [messages,setMessages] = useState([]);
        const [blocked,setBlocked] = useState(false);
        const [chat_blocked,setBlockedChat] = useState(false);
        const messagesWrapper = createRef();
    
        unScopedUsername = username;
        unScopedChatlist = chat_list;
    

        scrl = useCallback((mode = "normal") => {
            const msw = messagesWrapper.current;
            const fuzz = 150;
            if (mode === "fuzzed" && (msw.scrollHeight - _.scrollBottom(msw) > fuzz)) {
                return;
            }
            const newMsg = msw.querySelector('new-messages');
            if (newMsg) {
                msw.scrollTop = _.offsetTop(newMsg)-20;
                return;
            }
            msw.scrollTop = msw.scrollHeight;
        });
        useEffect(useCallback(() => {
            if (messagesUpdateOn === "send") {
                scrl("bottom");
            }
            else if (messagesUpdateOn === "changeChat") {
                scrl();
            }
            else if (messagesUpdateOn === "refreshChat") {
                scrl("fuzzed");
            }
        }), [messages]);
        loadChat = useCallback(async (userN = null) => {
            if (userN === null) {
                userN = unScopedUsername;
            }
            pendingChanges[userN] = 1;
            unScopedChatlist.forEach((chat,k) => {
                if (chat.username === userN) {
                    unScopedChatlist[k].unread = 0;
                }
            });
            setChatList(unScopedChatlist);
            const res = await api('get_chat',{
                data: {username: userN}
            });
            delete pendingChanges[userN];
            if (Object.keys(pendingChanges).length > 0) {
                return;
            }
            
            messagesUpdateOn = userN === unScopedUsername ? "refreshChat" : "changeChat";
            setUsername(res.username);
            setBlocked(res.blocked);
            setBlockedChat(res.chat_blocked);
            setMessages(res.messages);
        });
        refreshChatsWith = useCallback(async () => {
            const res = await api('chats_with');
            setChatList(res.with);
        });
        send = useCallback(async () => {
            if (message.trim() === "") {
                return;
            }
            api('send_message',{
                data: {
                    to: username,
                    message
                }
            });
            messagesUpdateOn = "send";
            setMessages(messages.concat([{
                ID: "new-" + new_message_key,
                from: "my",
                time: null,
                message
            }]));
            new_message_key += 1;
            setMessage("");
        });
        const msgEl = [];
        for (let i = 0; i < messages.length; i++) {
            const msg = messages[i];
            if (msg.new){
                msgEl.push(
                    <new-messages
                    onClick={scrl}
                    />
                );
            }
            const attr = {
                time: msg.time ? _t.local(new Date(parseInt(msg.time))) : "Just Now"
            };
            attr[msg.from] = '';
            msgEl.push(
                <message key={msg.ID} {...attr}>
                    {msg.message}
                </message>
            );
        }
        return (
            <chats-container>
                <chat-list
                class={username === "" ? "show" : ""}
                >
                    {chat_list.map(v => (
                        <chat
                        key={v.ID}
                        username={v.username}
                        unread={v.unread}
                        onClick={loadChat.bind(null,v.username)}
                        active={v.username === username ? "" : null}
                        />
                    ))}
                </chat-list>
                <chats>
                    {username !== "" ? null : (
                    <preview>
                        <h3><span>Your inbox  </span><Smiley size={18}/></h3>
                        <text>To message someone, click on <MessageIcon/> in the bottom-right corner of their profile.</text>
                    </preview>
                    )}
                    <user-info>
                        <button
                        className="back"
                        onClick={setUsername.bind(null,"")}
                        />
                        <a
                        href={username === "" ? null : "/" + username}
                        target="_blank"
                        >
                            {username === "" ? "" : username}
                        </a>
                        <button
                        tooltip-bottom="Block"
                        className={"block" + (blocked ? " blocked" : "")}
                        onClick={async () => {
                            await api('block',{
                                data: {
                                    block: !blocked,
                                    username,
                                }
                            });
                            if (!blocked) {
                                setBlockedChat(true);
                            }
                            setBlocked(!blocked);
                        }}
                        />
                    </user-info>
                    <messages
                    ref={messagesWrapper}
                    >
                        {msgEl}
                    </messages>
                    <send-message>
                        <input
                        value={message}
                        placeholder={"Message " + username}
                        onChange={e => setMessage(e.target.value)}
                        onKeyDown={e => {
                            if (e.key.toLowerCase() === "enter") {
                                send();
                            }
                        }}
                        disabled={chat_blocked}
                        />
                        <button
                        disabled={message.trim() === "" || chat_blocked}
                        onClick={send}
                        class="waves-effect"
                        />
                    </send-message>
                </chats>
            </chats-container>
        );
    }
    ReactDOM.render(
        <Chat/>,
        DOM.q('main')
    );
    refreshChatsWith();
    window.addEventListener('new-messages',refreshChatsWith);
    let refreshChatId = setInterval(loadChat,10*1000);
    _.interact(_.debounce(() => {
        clearInterval(refreshChatId);
        refreshChatId = null;
    },5*60*1000));
    _.interact(() => {
        if (refreshChatId !== null) {
            return;
        }
        loadChat();
        refreshChatId = setInterval(loadChat,10*1000);
    });
    // URL
    const url_parts = window.location.pathname.split("/").filter(va => va !== "").slice(1);
    if (url_parts.length > 0) {
        loadChat(url_parts[0]);
    }
})();