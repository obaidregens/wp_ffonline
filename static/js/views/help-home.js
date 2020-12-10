const search_help = new autocomplete (DOM.q('input'),[],{async: "get_help_results",preview:false,none_found_msg: "Nothing to help you here. <a href='/help/faq'>Ask us</a> instead."});
search_help.select = v => {
    if (v.type === "faq") {
        window.location.href = "/help/faq/" + v.value;
        return;
    }
    new tour(v.value);
}