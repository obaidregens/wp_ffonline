DOM.qa('guide').forEach(el => el.addEventListener('click',() => {
    new tour(el.getAttribute("name"));
}));