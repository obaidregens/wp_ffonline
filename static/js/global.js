const logged_in = DOM.q('logged_in').getAttribute('value') === 'true';
const im = type => {
	const selector = type === 'collections' ? 'collections-container > collection' : 'books-container .book';
	const attr = type === 'collections' ? 'collection_id' : 'book_id';
	const height = window.innerHeight;
	const objs = DOM.qa(selector);
	let im__ = [];
	for (let i = 0; i < objs.length; i++) {
		const id = objs[i].getAttribute(attr);
		const co_ordinates = objs[i].getBoundingClientRect();
		const pos = (co_ordinates.top + co_ordinates.bottom)/2;
		if (pos >= 0 && pos <= height){
			im__.push(id);
		}
	}
	return im__;
}

const PollFilters = [];

let im_books = [];
let im_collections = [];
let lastSend = Date.now() - 40000;
let lastOpen = 0;
_.interact(function(event){
	if (! event || event.isTrusted !== true){
		return;
	}
	if (Date.now() - lastSend < 15000){
        return;
	}
	im_books = _.array_unique(im_books.concat(im('books')));
	im_collections = _.array_unique(im_collections.concat(im('collections')));
	lastSend = Date.now();
	let datal = {
		im_books,
		im_collections,
		lastOpen
	};
	for (let i = 0; i < PollFilters.length; i++) {
		datal = PollFilters[i](datal);		
	}
	api('poll',{
		data: datal
	})
	.then(response => {
		let notificationsWrapper = DOM.q('next-screen[notifications] > notifications');
		if (! notificationsWrapper) {
			const ns = DOM.create('next-screen',{
				attributes: {
					notifications: ""
				},
				children: [
					DOM.create('notifications')
				],
				listeners: {
					onOpen: () => {
						DOM.q('.notification-pulse').classList.remove('show');
						lastOpen = Date.now();
						lastSend = lastSend - 20000;
					},
					onClose: () => {
						DOM.q('.notification-pulse').classList.remove('show');
						lastOpen = Date.now();
					},
				}
			});
			next_screen.create(ns);
			DOM.q('nav > drop > dropdown > .notifications').addEventListener('click',() => next_screen.open(ns));
			document.documentElement.appendChild(DOM.create('button',{
				classes: ['notification-pulse'],
				listeners: {
					click: () => next_screen.open(ns)
				}
			}));
		}
		notificationsWrapper = DOM.q('next-screen[notifications] > notifications');
		if (notificationsWrapper.querySelector('.new-messages')) {
			notificationsWrapper.querySelector('.new-messages').remove();
		}
		for (let i = notificationsWrapper.children.length; i < response.notifications.length; i++) {
			const n = response.notifications[i];
			notificationsWrapper.appendChild(DOM.create('a',{
				innerText: n.message,
				attributes: {
					href: n.link,
					time: _t.local(new Date(n.time)),
				}
			}));
		}
		if (response.unread > 0) {
			DOM.q('.notification-pulse').classList.add('show')
		}
		if (response.new_messages.unread > 0) {
			window.dispatchEvent(new Event("new-messages"));
			notificationsWrapper.appendChild(DOM.create('a',{
				classes: ['new-messages'],
				innerText: `You have ${response.new_messages.unread} unread message${response.new_messages.unread > 1 ? "s" : ""}.`,
				attributes: {
					href: '/inbox',
					time: "",
				}
			}));
			if (response.new_messages.last >= lastOpen){
				DOM.q('.notification-pulse').classList.add('show');
			}
		}
		lastOpen = 0;
	});
});