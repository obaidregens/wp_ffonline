<?php
if(isset($_POST['id']) && preg_replace('/[0-9]+/', '', $_POST['id']) == ''){
    $user = get_user_by('id',explode('-',$_POST['id'])[0])->data;
    if(! $user){
        
        get_template_part('dashboard/part','messages');
    }
    else{
        ?>
        <main>
        <a onclick="load_page('messages')" class="btn-hover btn-floating btn-small waves-effect waves-light"><i class="fas fa-arrow-left"></i></a>
        <div class="chat">
            <?php
            global $this_user;
            $this_user = $user;
            get_template_part('dashboard/part','chatdiv');
            ?>
        </div>
        </main>
        <script>
		function block_this(user){
        	api('block_user',{
        		data: {user:user},
        		callback: function(response){
					if (response == 0){
						jQuery('.btn-block').addClass('active');
					}
					else if(response == 1){
						jQuery('.btn-block').removeClass('active');
					}
        		}
        	});
		}
        function get_formatted_time(localTime = null){
            var months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
            if (localTime == null){
                localTime = new Date();
            }
            var now = new Date();
            var minute = localTime.getMinutes().toString();
            if (minute.length == 1){
                var minute = '0' + minute;
            }
            var twelveHour = localTime.getHours();
            if (twelveHour >= 12){
                if ((twelveHour - 12) == 0){
                    var calc = 12;
                }
                else{
                    var calc = (twelveHour - 12);
                }
                twelveHour = calc + ':' + minute + ' PM';
            }
            else{
                if (twelveHour == 0){
                    var calc = 12;
                }
                else{
                    var calc = twelveHour;
                }
                twelveHour = calc + ':' + minute + ' AM';
            }
            diffInHours = (now - localTime)/3600000;
            if (diffInHours <= 24){
                var formatted = 'Today ' + twelveHour;
            }
            else if (diffInHours <= 48){
                var formatted = 'Yesterday ' + twelveHour;
            }
            else{
                var formatted = (localTime.getDate()) + ' ' + months[localTime.getMonth()] + ' ' + localTime.getFullYear();
            }
            return formatted;
        }
        
        function refresh_chat () {
            var to = document.getElementById("user_info").getAttribute('value');
        	api('resend_chat',{
        		data: {to:to},
        		callback: function(response){
                    var scroll = jQuery(".chat-window").scrollTop();
                    var message = jQuery("#message").val();
                    var btn_status = jQuery("#send_message").prop('disabled');
                    var focus = jQuery("#message").is(":focus");
                    var messages_length = jQuery('.message-block').length;
        		    jQuery(jQuery('.chat')[0]).html(response);
        		    if (jQuery('.message-block').length > messages_length){
                        var newMsg = jQuery('.message-block')[messages_length-1].offsetTop;
                        jQuery(".chat-window").scrollTop(newMsg);
        		    }
        		    else{
        		        jQuery(".chat-window").scrollTop(scroll);
        		    }
        		    if (focus == true){
        		        jQuery("#message").focus();
        		    }
        		    jQuery("#message").val(message);
                    jQuery("#send_message").prop('disabled',btn_status);
                    var elems = jQuery(".message-time");
                    for (i = 0; i < elems.length; i++) {
                        var localTime = new Date(elems[i].innerHTML + ' UTC');
                        var formatted = get_formatted_time(localTime);
                        elems[i].innerHTML = formatted;
                    }
        		}
        	});            
        }
        
        var intervalID = setInterval(refresh_chat, 10000);
        var elems = jQuery(".message-time");
        for (i = 0; i < elems.length; i++) {
            var localTime = new Date(elems[i].innerHTML + ' UTC');
            var formatted = get_formatted_time(localTime);
            elems[i].innerHTML = formatted;
        }
        
        jQuery(".chat-window").scrollTop(jQuery(".chat-window")[0].scrollHeight);
        jQuery(".chat").on("change paste keyup","#message",function(event){
            if (event.keyCode === 13) {
                document.getElementById("send_message").click();
            }            
          if (jQuery("#message").val() == '' || jQuery("#message").val().length > 150){
              jQuery("#send_message").prop('disabled', true);
          }
          else{
              jQuery("#send_message").prop('disabled', false);
          }
        });

        jQuery(".chat").on("click","#send_message",function(){
          if (jQuery("#message").val() == ''){
              jQuery("#send_message").prop('disabled', true);
          }
          else{
            var status = '<div class="loader"></div>';
            var message = jQuery("#message").val();
            var to = document.getElementById("user_info").getAttribute('value');
            var temp_id = jQuery('[id^=temp_id-]').length + 1;
            jQuery(".chat-window").append('<li id="temp_id-' + temp_id + '" class="message-block my"><div class="message-data"><span class="message-time">Just Now</span><span class="message-status sent">' + status + '</span></div><div class="message">' + message + '</div></li>');
            jQuery("#message").val('');
            jQuery("#send_message").prop('disabled', true);
            jQuery(".chat-window").scrollTop(jQuery(".chat-window")[0].scrollHeight);
        	api('send_mail',{
        		data: {to:to,message:message},
        		callback: function(response){
        		    var msg_status = jQuery('#temp_id-' + temp_id + ' .message-status')[0];
        		    if (typeof msg_status !== "undefined"){
            		    msg_status.innerHTML = '<i class="fa fa-check fa-1" aria-hidden="true">';
                        jQuery(msg_status).addClass('sent');
                        response_arr = response.split(',');
                        var time = new Date(response_arr[1] + ' UTC');
                        jQuery('#temp_id-' + temp_id + ' .message-time')[0].innerHTML = get_formatted_time(time);
                        jQuery('#temp_id-' + temp_id).attr('id',response_arr[0]);
        		    }
        		}
        	});            
          }
        });
        </script>
        <?php
    }
}
else{
    get_template_part('dashboard/part','messages');
}
?>