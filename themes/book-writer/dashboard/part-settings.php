<main class="mobile-margin">
    <div class="row">
    <h1>Messages</h1>
    	<div class="input-field col s12">
    		<div class="switch center-block" style="padding-left:10px;">
    			<label>
    				<input id="messages_status" type='checkbox' value='enable' <?php if(get_user_meta(get_current_user_id(),'allow_messaging',true) == 'enable' || get_user_meta(get_current_user_id(),'allow_messaging',true) === ''){echo 'checked';}?>>
    				<span class='lever'></span>
    				<label>Disable Messaging.</label>
    			</label>
    		</div>
    	</div>
    	<div class="input-field col s12">
    		<div class="switch center-block" style="padding-left:10px;">
    			<label>
    				<input id="messages_online" type='checkbox' value='show' <?php if(get_user_meta(get_current_user_id(),'online_status',true) == 'show' || get_user_meta(get_current_user_id(),'online_status',true) === ''){echo 'checked';}?>>
    				<span class='lever'></span>
    				<label>Show online/offline status for chats.</label>
    			</label>
    		</div>
    	</div>
    	<div class="input-field col s12">
    		<div class="switch center-block" style="padding-left:10px;">
    			<label>
    				<input id="messages_read" type='checkbox' value='show' <?php if(get_user_meta(get_current_user_id(),'read_receipts',true) == 'show' || get_user_meta(get_current_user_id(),'read_receipts',true) === ''){echo 'checked';}?>>
    				<span class='lever'></span>
    				<label>Show read status for messages.</label>
    			</label>
    		</div>
    	</div>
    </div>
    <div class="row">
        <h1>Collections</h1>
    	<div class="input-field col s12">
    		<div class="switch center-block" style="padding-left:10px;">
    			<label>
    				<input id="emails_notifications" type='checkbox' value='true' <?php if(get_user_meta(get_current_user_id(),'email_me_notifications',true) == 'true' || get_user_meta(get_current_user_id(),'email_me_notifications',true) === ''){echo 'checked';}?>>
    				<span class='lever'></span>
    				<label>Enable email notifications for collections.</label>
    			</label>
    		</div>
    	</div>
    </div>
    <div id="button-wrapper" style="padding-right: 15px;" class="right"><button onclick="save_settings()" class="waves-effect waves-light btn-small" type="submit">Save</button></div>
</main>
<script>
function save_settings(){
	if (document.querySelector("#messages_status").checked == true){
		var messages_status = 'enable';
	}
	else{
		var messages_status = 'disable';
	}
	if (document.querySelector("#messages_online").checked == true){
		var messages_online = 'show';
	}
	else{
		var messages_online = 'hide';
	}
	if (document.querySelector("#messages_read").checked == true){
		var messages_read = 'show';
	}
	else{
		var messages_read = 'hide';
	}
	if (document.querySelector("#emails_notifications").checked == true){
		var emails_notifications = true;
	}
	else{
		var emails_notifications = false;
	}
	document.getElementById("button-wrapper").innerHTML = '<div class="preloader-wrapper big active"><div class="spinner-layer spinner-blue-only"><div class="circle-clipper left"><div class="circle"></div></div><div class="gap-patch"><div class="circle"></div></div><div class="circle-clipper right"><div class="circle"></div></div></div></div>';
	api('save_settings',{
		data: {messages_status:messages_status,messages_online:messages_online,messages_read:messages_read,emails_notifications:emails_notifications},
		callback: function(response){
			document.getElementById("button-wrapper").innerHTML = '<button onclick="save_settings()" class="waves-effect waves-light btn-small" type="submit">Save</button>';
			M.Toast.dismissAll();
			M.toast({html: 'Your settings have been saved.'});
		}
	});    
}
</script>