document.querySelector('h2[label="Account"] ~ collapsible:nth-of-type(1) > button').addEventListener('click',function(){
    const pass = this.previousElementSibling.previousElementSibling.querySelector('input').value;
    const confirm_pass = this.previousElementSibling.querySelector('input').value;
    api('change_password',{
        data: {
            pass,
            confirm_pass
        },
        callback: response => {
            if (response.code === 1){
                new toast('Password Changed.');
            }
            else if (response.code === 8) {
                new toast('Passwords entered are not the same.');
            }
            else if (response.code === 9) {
                new toast('Password length must be at least 6 and at most, 30 characters.');
            }
            else if (response.code > 5) {
                new toast('An error occured.');
            }
        }
    });
});
document.querySelector('h2[label="Account"] ~ collapsible:nth-of-type(2) > button').addEventListener('click',function(){
    const new_username = this.previousElementSibling.previousElementSibling.querySelector('input').value;
    api('change_username',{
        data: {
            new_username
        },
        callback: response => {
            if (response.code === 1){
                new toast('Username Changed.');
                setTimeout( () => window.location.href = "/@" + new_username + "/settings",1000 );
            }
            else if (response.code === 10) {
                const error_keys = Object.keys(response.errors);
                for (let i = 0; i < error_keys.length; i++) {
                    new toast(_.ucfirst(error_keys[i]) + ' - ' + response.errors[error_keys[i]]);
                }
            }
            else if (response.code > 5) {
                new toast('An error occured.');
            }
        }
    });
});