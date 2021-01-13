DOM.qa(".tab > li").forEach(tab => tab.addEventListener('click',() => {
    setFeed(tab.innerText.toLowerCase());
}));