function share_open(opts){
    opts.share_title = `I found a great fanfiction by ${opts.author} '${opts.title}'!`;
    opts.url = opts.href;
    opts.callOnCopy = () => new toast('Copied!');
    shareAPI(opts);
}