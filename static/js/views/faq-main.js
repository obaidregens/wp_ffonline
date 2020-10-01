document.querySelector('button[label="Ask"]').addEventListener('click',() => {
    const Widget = (document.querySelector('recaptcha').getAttribute('widget-id'));
    const email = document.querySelector('text-input:nth-of-type(1) > input');
    const question = document.querySelector('text-input:nth-of-type(2) > textarea');
    document.querySelector('notice').innerText = "";
    api('ask_question',{
        data: {
            email: email.value,
            question: question.value,
        },
        reCAPTCHA: grecaptcha.getResponse(Widget),
        callback: response => {
            grecaptcha.reset(Widget);
            if (response.code === 997) {
                new toast('Verify reCAPTCHA.');
                return;
            }
            if (response.code === 7) {
                new toast('Enter a valid email');
                return;
            }
            if (response.code === 8) {
                new toast("Your question must be of at least 10 characters.");
                return;
            }
            if (response.code > 5) {
                new toast('An error occured');
                return;
            }
            let msg = "Your question has been submitted. "
            if (response.code === 2) {
                msg += "Keep checking this page for the answer to your question";
            }
            if (response.code === 1) {
                msg += "You'll be notified by email when your question is answered.";
            }
            msg += "\n\nFeel free to keep reading & writing on Fanfiction Online, and if you have any more questions, ask away!";
            document.querySelector('notice').innerText = msg;
            email.value = "";
            question.value = "";
        }
    });
});