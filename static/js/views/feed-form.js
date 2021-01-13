const FeedOptions = v => {
    const classes = DOM.q('feed-form').classList;
    if (v === "switch") {
        v = classes.contains('open') ? "close" : "open";
    }
    v = v === "close" ? "close" : "open";
    classes[v === "close" ? "remove" : "add"]('open');
    document.documentElement.style.setProperty("overflow",v === "close" ? "auto" : "hidden");
}

const AddSelectTag = (SelectTag,text,value) => {
    const selected = JSON.parse(SelectTag.getAttribute("selected")) || [];
    if (selected.includes(value)) {
        return;
    }
    selected.push(value);
    SelectTag.setAttribute("selected",JSON.stringify(selected));
    SelectTag.appendChild(DOM.create('tag',{
        children: [
            DOM.create('tag-text',{
                innerText: text
            }),
            DOM.create('tag-cancel',{
                listeners: {
                    click: ({target}) => {
                        target.parentElement.remove();
                        const _selected = (JSON.parse(SelectTag.getAttribute("selected")) || []).filter(v => v !== value);
                        SelectTag.setAttribute("selected",JSON.stringify(_selected));
                    }
                }
            })
        ]
    }));
}
function checkboxFrag (SelectTag,list) {
    const fragment = document.createDocumentFragment();
    list.forEach(({name,count,value}) => {
        fragment.appendChild(DOM.create('single',{
            innerText: `${name} (${count})`,
            listeners: {
                click: () => {
                    AddSelectTag(SelectTag,`${name} (${count})`,value)
                }
            }
        }));
    });
    this.drop.innerText = "";
    this.drop.appendChild(fragment);
    return fragment;
}
(() => {
    const Fandom = DOM.q('.fandom');
    const ac = new autocomplete(Fandom.querySelector('input'),[],{
        name: "fandomFilter",
        async: "load_tags",
        async_data: {
            tag: "fandom",
            prev: "",
            all: true
        },
        select: false,
        outOnMove: false,
        renderList: function () {
            this.drop = Fandom.querySelector('.select-list');
        }
    });
    ac.render = checkboxFrag.bind(ac,Fandom.querySelector('select-tag'));
    Fandom.querySelector('input').dispatchEvent(new Event('focus'));
})();
(() => {
    const tagNames = ['rating','language'];
    tagNames.forEach(async tagName => {
        const Tag = DOM.q(`.${tagName}`);

        const response = await api("load_tags",{data: {
            tag: tagName,
            prev: "",
            search: "",
            all: true
        }});
        checkboxFrag.bind({drop: Tag.querySelector('.select-list')})(Tag.querySelector('select-tag'),response.result.list);
    })
})();
DOM.q('cancel').addEventListener('click',() => {
    FeedOptions("switch");
});
DOM.q('feed-form > button').addEventListener('click',async () => {
    const data = {};
    ['fandom','rating','language'].forEach(tagName => {
        data[tagName] = JSON.parse(DOM.q(`.${tagName} > select-tag`).getAttribute('selected')) || [];
    });
    FeedOptions("close");
    const {code} = await api("save_feed_settings",{data});
    if (code > 5) {
        return new toast("An error occured");
    }
    setFeed("feed");
    new toast("Saved Settings");
});
DOM.q('books-container').addEventListener('click',({target}) => {
    const _is = target.classList.contains("feed-settings");
    if (!_is) {
        return;
    }
    FeedOptions("open");
});
// Load Saved
(async () => {
    const settings = (await api('get_feed_settings')).settings;
    Object.entries(settings).forEach(([tagName,tags]) => {
        tags.forEach(({ID,count,name}) => {
            AddSelectTag(DOM.q(`.${tagName} > select-tag`),`${name} (${count})`,ID);
        });
    });
})();