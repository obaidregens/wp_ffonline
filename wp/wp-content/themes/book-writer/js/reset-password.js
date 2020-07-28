jQuery("form[name='resetpassform']").submit(function(event) {
    event.preventDefault();
	var pass1 = jQuery('#pass1').val();
	var pass2 = jQuery('#pass2').val();
	if (pass1 != pass2){
		jQuery('#pass2').addClass('invalid');
		return;
	}
	if (pass1.length < 8){
	    jQuery('#pass2').addClass('invalid');
	    M.toast({html: 'Password should have at least 8 characters.'});
	}
	else{
	    var urlParams = new URLSearchParams(location.search);
	    var key = urlParams.get('key');
	    var login = urlParams.get('login');
		api('set_password',{
			reCAPTCHA:grecaptcha.getResponse(),
			data: {key:key,login:login,password:pass2},
			callback: function(response){
			    grecaptcha.reset();
				if (response == '0' || response == '2'){
					M.toast({html: 'An unknown error occured'});
				}
				else if (response == '1'){
				    M.toast({html: 'Your password has been reset.'});
					jQuery('#resetpass-button').prop('disabled','true');
					window.location.href = "https://fanfiction.online/dashboard";
				}
				else if (response == '8'){
					M.Toast.dismissAll();
					M.toast({html: 'Please verify yourself by clicking on the \"I\'m not a robot\" checkbox.'});
				}
			},
		});			
	}
});
