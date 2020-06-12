function validateoption(){
	if(jQuery('input[name=contact_options]:checked').attr('value') == undefined || jQuery('input[name=contact_options]:checked').attr('value') == false){
		var element = jQuery("input[name='contact_options']")[0];
		element.setCustomValidity('Please select an option.');
	}
	else{
		var element = jQuery("input[name='contact_options']")[0];
		element.setCustomValidity('');
	}
}
jQuery("form[name='message']").submit(function(event) {
	event.preventDefault();
	var element = jQuery("input[name='contact_options']")[0];
	element.setCustomValidity('');
	var email = jQuery('#email').val();
	var message = jQuery('#message').val();
	var label = jQuery('input[name=contact_options]:checked').attr('value');
	api('send_mail',{
		data: {email:email,message:message,label:label},
		callback: function(response){
			if (response != '1'){
				M.toast({html: 'Your message was not sent.'});
			}
			else{
				jQuery("#message_btn" ).text("Sent!");
				jQuery("#message_btn").addClass("disabled");
			}
		}
	});
});