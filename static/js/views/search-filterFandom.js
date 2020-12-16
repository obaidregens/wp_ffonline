(async () => {
    const inst = new autocomplete(
        DOM.q('fandom-filter > input'),
        [],{
            async: "load_tags",
            async_data: {
                tag: "fandom",
                prev: DOM.q('prev_ss').innerText,
                all: true
            },
            none_found_msg: "No fandoms",
            preview: true,
            name: "fandomFilter"
        }
    );
    inst.select = (v) => {
        inst.out();
        inst.input.blur();
        const selected = {
            included: [v.value],
            excluded: []
        };
        DOM.q('select-tag[name="fandom"]').innerText = JSON.stringify(selected);
        DOM.q('[label="Search"]').dispatchEvent(new Event("click"));
        setSelectedTags('fandom',selected)
        .then(() => {
            const fandom_name = [...DOM.q('select-tag[name="fandom"]').children].map(tag_el => tag_el.innerText).join("/");
            setFilterFandom(fandom_name);
        });
    }
})();