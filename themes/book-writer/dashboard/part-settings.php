<main class="mobile-margin">
    <div class="row">
        <h1>Emails</h1>
    	<div class="input-field col s12">
    		<div class="switch center-block" style="padding-left:10px;">
    			<label>
    				<input id="emails_notifications" type='checkbox' value='true' <?php if(get_user_meta(get_current_user_id(),'email_me_notifications',true) == 'true' || get_user_meta(get_current_user_id(),'email_me_notifications',true) === ''){echo 'checked';}?>>
    				<span class='lever'></span>
    				<label>Enable email notifications.</label>
    			</label>
    		</div>
    	</div>
    </div>
    <div id="button-wrapper" style="padding-right: 15px;" class="right"><button onclick="save_settings()" class="waves-effect waves-light btn-small" type="submit">Save</button></div>
</main>
<script>
function save_settings(){
	spin("#button-wrapper");
	api('save_settings',{
		data: {
			emails_notifications: document.querySelector("#emails_notifications").checked
		},
		callback: function(response){
			document.getElementById("button-wrapper").innerHTML = '<button onclick="save_settings()" class="waves-effect waves-light btn-small" type="submit">Save</button>';
			M.Toast.dismissAll();
			M.toast({html: 'Your settings have been saved.'});
		}
	});    
}
</script>