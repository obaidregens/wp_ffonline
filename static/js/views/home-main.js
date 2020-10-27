const slide = new Glide(DOM.q('div.glide'),{
    type: 'slider',
    perView: 1,
});
slide.mount();
let doneOnce = false;
slide.on('move',() => doneOnce = true);

setInterval(() => {
    if (doneOnce) {
        return;
    }
    slide.go('>');
    doneOnce = false;
}, 4000);