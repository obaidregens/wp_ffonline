class poll {
    static voteOn (poll_id,el,option_id) {
        if (el.querySelector('poll-options').getAttribute('results') !== null) {
            return;
        }
        if (! poll.logged_in) {
            new toast("Login to vote on poll");
            prompt_login();
            return;
        }
        poll.refresh(poll_id,el,option_id)
    }
    static updateResults (el,options) {
        el.querySelector('poll-options').setAttribute('results',"");
        options.forEach(({ID,votes}) => {
            el.querySelector('poll-option[poll-id="' + ID +'"]').style.setProperty("--percentage",votes);
        });
    }
    static updateMeta (el,response) {
        el.querySelector('poll-meta').innerText = "";
        el.querySelector('poll-meta').appendChild(DOM.create('single',{
            innerText: response.count + " votes"
        }));
        el.querySelector('poll-meta').appendChild(DOM.create('single',{
            innerText: response.poll_data.expire_in
        }));
    }
    static refresh(poll_id,el,voteOn = 0) {
        api('get_poll',{
            data: {
                poll_id,
                voteOn
            }
        })
        .then(response => {
            poll.updateMeta(el,response);
            if (response.has_voted) {
                poll.updateResults(el,response.options);
            }
            poll.logged_in = response.logged_in;
        });
    }
    static root(el,poll_id) {
        api('get_poll',{
            data: {
                poll_id
            }
        })
        .then(response => {
            el.innerText = "";
            el.appendChild(DOM.create('poll-description',{
                innerText: response.poll_data.description
            }));
            el.appendChild(DOM.create('poll-options',{
                children: response.options.map(opt => {
                    return DOM.create('poll-option',{
                        innerText: opt.title,
                        attributes: {
                            "poll-id": opt.ID,
                        },
                        listeners: {
                            click: poll.voteOn.bind(null,poll_id,el,opt.ID)
                        }
                    });
                }),
            }));
            el.appendChild(DOM.create('poll-meta'));
            poll.updateMeta(el,response);
            if (response.has_voted) {
                poll.updateResults(el,response.options);
            }
            poll.logged_in = response.logged_in;
        });
        setInterval(poll.refresh.bind(null,poll_id,el),15000);
    }
    static init() {
        document.querySelectorAll('poll[poll-id]').forEach(el => {
            poll.root(el,el.getAttribute('poll-id'));
        });
    }
}
poll.logged_in = true;
poll.init();