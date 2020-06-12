jQuery(document).ready(function(){
    jQuery('#dashboard-sidenav').sidenav();
    let page_chain = window.location.href.replace(window.location.origin + '/dashboard','').replace('/','');
    if (page_chain === ''){
        page_chain = 'dashboard';
    }
    load_page(page_chain);
});
function reInitializeOnLoad(page_chain_arr){
    if(jQuery('[data-target=dashboard-sidenav]').css('display') != 'none'){
	    jQuery('#dashboard-sidenav').sidenav('close');
    }
    let page_chain = page_chain_arr.join('/');
    if (page_chain_arr[0] === 'dashboard'){
        page_chain = '';
    }
    window.history.pushState("object or string", ucfirst(page_chain_arr[0]) + ' - Fanfiction Online','/dashboard/' + page_chain);
    jQuery('#dashboard-sidenav').children().removeClass("active");
    jQuery("." + page_chain_arr[0] + "_li").addClass("active");
    jQuery('.collapsible').collapsible();
    jQuery('.modal').modal();
    M.updateTextFields();
    recaptchaOnload();
}
var intervalID;
//<messages>
function get_messages() {
    api('get_messages',{
        data: {},
        dataType: 'JSON',
        callback: function(response){
            let construct = '';
            for (let i = 0; i < response.length; i++) {
                const user = response[i];
                construct += '<a onclick="load_page(\'chat/' + user.username + '\')" class="collection-item">' + user.username;
                if (user.unread > 0){
                    construct += '<span data-badge-caption="' + user.unread + ' New Messages" class="new badge red"></span>';
                }
                construct += '</a>';
            }
            if (response.length == 0){
                construct += '<div class="collection-item">No Messages</div>';
            }
            jQuery('.messages-collection').children().remove();
            jQuery('.messages-collection').append(construct);
        }
    });
}
function load_messages() {
    clearInterval(intervalID);
    jQuery("#page-main").html('<main><div class="messages-collection collection"></div></main>');
    get_messages();
    reInitializeOnLoad(['chat']);
    intervalID = setInterval(get_messages, 10000);
}
// </messages>
// <chat>
function load_chat(page_chain_arr){
    reInitializeOnLoad(page_chain_arr);
    clearInterval(intervalID);
    const username = page_chain_arr[1] || null;
    jQuery("#page-main").html(`
    <main>
        <a onclick="load_page(\'chat\')" class="btn-hover btn-floating btn-small waves-effect waves-light"><i class="fas fa-arrow-left"></i></a>
        <div class="chat">
            <div id="user_info" style="padding:20px;" class="row">
                <span class="username"></span>
                <a class="right waves-light waves-effect btn-hover block_user btn-floating"><i class="fas fa-ban"></i></a>
            </div>
            <ul class="chat-window"></ul>
            <div class="row"><div class="input-field col m11 s10"><input autocomplete="new-password" placeholder="Type a message..." id="message" type="text"></div><div class="input-field col m1 s2"><button disabled id="send_message" class="btn-hover btn-floating waves-effect waves-light" type="submit" name="action"><i class="material-icons">send</i></button></div></div>
        </div>
    </main>`
    );
    const chat_window = jQuery('.chat-window')[0];
    function get_chat(){
        api('get_chat',{
            data: {
                username
            },
            dataType: 'JSON',
            callback: function(response){
                if (response.messages === false){
                    load_messages();
                    return;
                }
                jQuery('#user_info .username')[0].innerText = response.user.name;
                if (response.chat_blocked === true){
                    jQuery('#message').prop('disabled',true);
                    jQuery('#message').attr('placeholder','Messaging is unavailable.');
                    jQuery('#send_message').prop('disabled',true);
                }
                else{
                    jQuery('#message').prop('disabled',false);
                    jQuery('#message').attr('placeholder','Type a message...');
                    jQuery('#message').change();
                }
                jQuery('.block_user').addClass(response.blocked);
                const old_messages = jQuery('.message-block').length;
                const new_messages = response.messages.length - old_messages;
                let scroll_pos = chat_window.scrollTop;
                let construct = '';
                for (let i = 0; i < response.messages.length; i++) {
                    const message = response.messages[i];
                    const message_status = message.from === 'my' ? `<span class="message-status ${message.status}"><i class="fa fa-check fa-1" aria-hidden="true"></i></span>` : '';
                    construct +=
                    `<li class="message-block ${message.from}">
                        <div class="message-data">
                            ${message_status}
                            <span class="message-time">${utc_dt_to_local(message.date)}</span>
                        </div>
                        <div class="message">${message.message}</div>
                    </li>`;
                }
                jQuery('.chat-window').children().remove();
                jQuery('.chat-window').append(construct);
                if (old_messages === 0){
                    scroll_pos = chat_window.scrollHeight;
                }
                else if (new_messages > 0){
                    jQuery('.chat').append(`<div id="new_message_blob" class="new-messages valign-wrapper" style="
                        display: flex;
                        z-index: 5;
                        flex-direction: column;
                        justify-content: center;
                        align-items: center;
                        background-color: var(--theme-color);
                        border-radius: 50px;
                        user-select: none;
                        position:absolute;
                        width:25px;
                        height: 25px;
                        right: 70px;
                        bottom: 90px;
                    "
                    onclick="
                        document.querySelector('.chat-window').scrollTop = document.querySelector('.chat-window').scrollHeight;
                        this.remove();
                    "
                    >
                    ${new_messages}
                    </div>`);
                    jQuery(chat_window).on('scroll.new_msg_blob',function(event){
                        if ((chat_window.scrollTop + 5) >= (chat_window.scrollHeight - jQuery(chat_window).outerHeight())){
                            jQuery('#new_message_blob')[0].remove();
                            jQuery(chat_window).off('scroll.new_msg_blob');
                        }
                    });
                }
                chat_window.scrollTop = scroll_pos;
            }
        });
    }
    get_chat();
    intervalID = setInterval(get_chat, 5000);
    jQuery('.block_user').click(function(){
        progress(true);
        api('block_user',{
            dataType: 'JSON',
            data: {
                username,
                action: jQuery(this).hasClass('blocked') ? 'unblock' : 'block'
            },
            callback: (response) => {
                progress(false);
                if (response.success === true){
                    jQuery('.block_user').toggleClass('blocked');
                }
            }
        });
    });
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
            return;
        }
        const message = jQuery("#message").val();
        const to = username;
        jQuery(".chat-window").append(
            `<li class="message-block my">
                <div class="message-data">
                    <span class="message-status sent"></span>
                    <span class="message-time">Just Now</span>
                </div>
                <div class="message">${htmlspecialchars(message)}</div>
            </li>`
        );
        jQuery("#message").val('');
        jQuery(".chat-window").scrollTop(jQuery(".chat-window")[0].scrollHeight);
        api('send_message',{
            data: {
                to,
                message
            }
        });            
    });
}
// </chat>
function load_page(page_chain){
    var page_chain_arr = page_chain.split('/').filter( (elem) => {
        if (elem != ""){
            return elem;
        }
    });
    const pages = ['write','profile','stats','chat','settings','bookmarks','collections','dashboard'];
    if (! pages.includes(page_chain_arr[0])){
        return;
    }
    page_chain = page_chain_arr.join('/');
    if (page_chain_arr[0] === 'chat'){
        load_chat(page_chain_arr);
        return;
    }
    jQuery("#page-main").html('<main></main>');
    spin('#page-main main','center');
    clearInterval(intervalID);
	api('load_page',{
		data: {page_chain},
		callback: function(response){
            jQuery("#page-main").html(response);
            reInitializeOnLoad(page_chain_arr);
		}
	});
}