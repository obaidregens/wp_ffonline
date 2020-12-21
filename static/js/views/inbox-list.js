"use strict";

function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }

(() => {
  const Smiley = ({
    size = 10
  }) => {
    return /*#__PURE__*/React.createElement("svg", {
      xmlns: "http://www.w3.org/2000/svg",
      viewBox: "0 0 45 45",
      width: size,
      height: size
    }, /*#__PURE__*/React.createElement("defs", null, /*#__PURE__*/React.createElement("clipPath", {
      id: "a"
    }, /*#__PURE__*/React.createElement("path", {
      d: "M0 36h36V0H0v36z"
    }))), /*#__PURE__*/React.createElement("g", {
      "clip-path": "url(#a)",
      transform: "matrix(1.25 0 0 -1.25 0 45)"
    }, /*#__PURE__*/React.createElement("path", {
      d: "M36 18c0-9.941-8.059-18-18-18S0 8.059 0 18s8.059 18 18 18 18-8.059 18-18",
      fill: "#ffcc4d"
    }), /*#__PURE__*/React.createElement("path", {
      d: "M10.515 12.379c.045-.18 1.168-4.38 7.485-4.38 6.318 0 7.44 4.2 7.485 4.38a.499.499 0 0 1-.836.477c-.02-.02-1.954-1.856-6.65-1.856-4.693 0-6.63 1.837-6.647 1.856a.505.505 0 0 1-.598.08.5.5 0 0 1-.24-.557M14.5 22.5c0-1.934-1.119-3.5-2.5-3.5s-2.5 1.566-2.5 3.5c0 1.933 1.119 3.5 2.5 3.5s2.5-1.567 2.5-3.5M26.5 22.5c0-1.934-1.119-3.5-2.5-3.5s-2.5 1.566-2.5 3.5c0 1.933 1.119 3.5 2.5 3.5s2.5-1.567 2.5-3.5",
      fill: "#664500"
    })));
  };

  const MessageIcon = () => {
    return /*#__PURE__*/React.createElement("span", {
      className: "message-icon"
    });
  };

  const {
    useCallback,
    useState,
    createRef,
    useEffect
  } = React;
  let refreshChatsWith, loadChat, send, scrl, unScopedUsername, unScopedChatlist;
  const pendingChanges = {};
  let new_message_key = 0;

  function Chat() {
    const [chat_list, setChatList] = useState([]);
    const [message, setMessage] = useState("");
    const [username, setUsername] = useState("");
    const [messages, setMessages] = useState([]);
    const [blocked, setBlocked] = useState(false);
    const [chat_blocked, setBlockedChat] = useState(false);
    const messagesWrapper = createRef();
    unScopedUsername = username;
    unScopedChatlist = chat_list;

    scrl = () => {
      const msw = messagesWrapper.current;
      const newMsg = msw.querySelector('new-messages');

      if (newMsg) {
        const messagesHeight = parseInt(getComputedStyle(msw).getPropertyValue('height'));

        if (msw.scrollTop >= newMsg.offsetTop - messagesHeight) {
          return;
        }

        msw.scrollTop = newMsg.offsetTop - 20;
        return;
      }

      msw.scrollTop = msw.scrollHeight;
    };

    useEffect(scrl, [messages]);
    loadChat = useCallback(async (userN = null) => {
      if (userN === null) {
        userN = unScopedUsername;
      }

      pendingChanges[userN] = 1;
      unScopedChatlist.forEach((chat, k) => {
        if (chat.username === userN) {
          unScopedChatlist[k].unread = 0;
        }
      });
      setChatList(unScopedChatlist);
      const res = await api('get_chat', {
        data: {
          username: userN
        }
      });
      delete pendingChanges[userN];

      if (Object.keys(pendingChanges).length > 0) {
        return;
      }

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

      api('send_message', {
        data: {
          to: username,
          message
        }
      });
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

      if (msg.new) {
        msgEl.push( /*#__PURE__*/React.createElement("new-messages", null));
      }

      const attr = {
        time: msg.time ? _t.local(new Date(parseInt(msg.time))) : "Just Now"
      };
      attr[msg.from] = '';
      msgEl.push( /*#__PURE__*/React.createElement("message", _extends({
        key: msg.ID
      }, attr), msg.message));
    }

    return /*#__PURE__*/React.createElement("chats-container", null, /*#__PURE__*/React.createElement("chat-list", {
      class: username === "" ? "show" : ""
    }, chat_list.map(v => /*#__PURE__*/React.createElement("chat", {
      key: v.ID,
      username: v.username,
      unread: v.unread,
      onClick: loadChat.bind(null, v.username),
      active: v.username === username ? "" : null
    }))), /*#__PURE__*/React.createElement("chats", null, username !== "" ? null : /*#__PURE__*/React.createElement("preview", null, /*#__PURE__*/React.createElement("h3", null, /*#__PURE__*/React.createElement("span", null, "Your inbox  "), /*#__PURE__*/React.createElement(Smiley, {
      size: 18
    })), /*#__PURE__*/React.createElement("text", null, "To message someone, click on ", /*#__PURE__*/React.createElement(MessageIcon, null), " in the bottom-right corner of their profile.")), /*#__PURE__*/React.createElement("user-info", null, /*#__PURE__*/React.createElement("button", {
      className: "back",
      onClick: setUsername.bind(null, "")
    }), /*#__PURE__*/React.createElement("a", {
      href: username === "" ? null : "/" + username,
      target: "_blank"
    }, username === "" ? "" : username), /*#__PURE__*/React.createElement("button", {
      "tooltip-bottom": "Block",
      className: "block" + (blocked ? " blocked" : ""),
      onClick: async () => {
        await api('block', {
          data: {
            block: !blocked,
            username
          }
        });

        if (!blocked) {
          setBlockedChat(true);
        }

        setBlocked(!blocked);
      }
    })), /*#__PURE__*/React.createElement("messages", {
      ref: messagesWrapper
    }, msgEl), /*#__PURE__*/React.createElement("send-message", null, /*#__PURE__*/React.createElement("input", {
      value: message,
      placeholder: "Message " + username,
      onChange: e => setMessage(e.target.value),
      onKeyDown: e => {
        if (e.key.toLowerCase() === "enter") {
          send();
        }
      },
      disabled: chat_blocked
    }), /*#__PURE__*/React.createElement("button", {
      disabled: message.trim() === "" || chat_blocked,
      onClick: send,
      class: "waves-effect"
    }))));
  }

  ReactDOM.render( /*#__PURE__*/React.createElement(Chat, null), DOM.q('main'));
  refreshChatsWith();
  window.addEventListener('new-messages', refreshChatsWith);
  let refreshChatId = setInterval(loadChat, 10 * 1000);

  _.interact(_.debounce(() => {
    clearInterval(refreshChatId);
    refreshChatId = null;
  }, 0.1 * 60 * 1000));

  _.interact(() => {
    if (refreshChatId !== null) {
      return;
    }

    loadChat();
    refreshChatId = setInterval(loadChat, 10 * 1000);
  }); // URL


  const url_parts = window.location.pathname.split("/").filter(va => va !== "").slice(1);

  if (url_parts.length > 0) {
    loadChat(url_parts[0]);
  }
})();