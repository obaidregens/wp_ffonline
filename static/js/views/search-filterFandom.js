(async () => {
    const inst = new autocomplete(
        DOM.q('fandom-filter > input'),
    Object.entries(tags_data.fandom).filter(([value,{count}]) => parseInt(count) > 0).map(([value,{name}]) => {
        return {name,value};
    }),{
        none_found_msg: "No fandoms"
    });
    inst.select = (v) => {
        inst.out();
        inst.input.blur();
        DOM.q("select-tag[name='fandom']").setAttribute('selected',JSON.stringify({
            included: [v.value],
            excluded: []
        }));
        DOM.q('[label="Search"]').dispatchEvent(new Event("click"));
    }
})();