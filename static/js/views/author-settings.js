
document.querySelector('h2[label="Account"] + input + label + collapsible > button').addEventListener('click',function(){
    const pass = this.previousElementSibling.previousElementSibling.querySelector('input').value;
    const confirm_pass = this.previousElementSibling.querySelector('input').value;
    api('change_password',{
        data: {
            pass,
            confirm_pass
        },
        dataType: 'JSON',
        callback: (response) => {
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