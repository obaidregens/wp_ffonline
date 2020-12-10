class tour {
    constructor (name) {
        tour.new(name);
    }
    static async new (name) {
        const {code,steps = []} = await api('get_tour',{data: {
            tour: name,
            url: window.location.pathname,
            isMobile: window.matchMedia("(max-width: 500px)").matches
        }});
        if (code === 6) {
            new toast("You need to be logged in to take this tour.");
            return false;
        }
        if (code > 5) {
            new toast("An error occured");
            return false;
        }
        Shepherd.activeTour && Shepherd.activeTour.cancel();
        idbKeyval.set("ongoingTour",name);
        const tour = new Shepherd.Tour({
            defaultStepOptions: {
                scrollTo: true
            }
        });
        steps.forEach(({intro,element = null,forward = false,repeat = false},i) => {
            const isLast = i === steps.length-1;
            const Finished = isLast && !forward;
            const buttons = [];
            if (repeat) {
                buttons.push({
                    text: "Repeat",
                    action: repeat === true ? window.location.reload : () => window.location.href = repeat
                });
            }
            if (!Finished) {
                buttons.push({
                    text: "Cancel",
                    action: tour.cancel
                });
            }
            if (forward) {
                buttons.push({
                    text: "Continue",
                    action: forward === true ? () => DOM.q(element).click() : () =>  window.location.href = forward
                })
            }
            else {
                buttons.push({
                    text: Finished ? "Got it" : "Next",
                    action: tour.next
                })    
            }
            tour.addStep({
                text: intro,
                attachTo: {
                    element,
                    on: 'bottom'
                },
                buttons
            });
            ['complete', 'cancel'].forEach(event => tour.on(event, () => {
                idbKeyval.del('ongoingTour');
             }));
            tour.start();
        });
        return true;
    }
    static async resume () {
        const existing = await idbKeyval.get('ongoingTour');
        if (existing) {
            tour.new(existing);
        }
    }
    static async Welcome() {
        const welcomed = (await idbKeyval.get('welcomed') || false);
        if (welcomed) {
            return;
        }
        await tour.new('welcome');
        idbKeyval.set("welcomed",true);
    }
}
tour.resume();
tour.Welcome();
