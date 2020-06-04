const email_menu_elem = jQuery('.email-notifications-menu')[0];
const follow_button = jQuery('.follow-button')[0];

const init_notifications = JSON.parse(document.getElementById('collection_notifications').innerHTML) === false ? false : true;
const init_emails = document.getElementById('collection_notifications').innerHTML === 'all' ? true : false;
const switch_email_ntfy = jQuery('.email-notifications-menu input')[0];

switch_email_ntfy.checked = init_emails;
follow_button_state(init_notifications);


function follow_button_state(state){
    if (state === true){
        jQuery(follow_button).addClass('active');
        jQuery('.follow-button i').addClass('fa-check');
        jQuery('.follow-button i').removeClass('fa-plus');
        jQuery('.follow-button span').html('Followed');
    }
    else if (state === false){
        jQuery(follow_button).removeClass('active');
        jQuery('.follow-button i').addClass('fa-plus');
        jQuery('.follow-button i').removeClass('fa-check');
        jQuery('.follow-button span').html('Follow');
    }
    else if (state === 'get'){
        return jQuery(follow_button).hasClass('active') ? true : false;
    }
    else if (state == 'toggle'){    
        follow_button_state(! follow_button_state('get'));
    }
}
function email_notifications_menu(state) {
    if (state === true){
        email_menu_elem.style.setProperty('display','flex','important');

        const email_menu_co_ordinates = email_menu_elem.getBoundingClientRect();
        const btn_co_ordinates = jQuery(follow_button)[0].getBoundingClientRect();
        const page_margin = parseInt(window.getComputedStyle(jQuery('#page')[0]).margin.replace('px',''));
        const top_padding = 20;
        
        email_menu_elem.style.left = btn_co_ordinates.left + pageXOffset - (email_menu_co_ordinates.width/2) + (btn_co_ordinates.width/2) + 'px';
        email_menu_elem.style.top = btn_co_ordinates.top + pageYOffset + (btn_co_ordinates.height) - page_margin + top_padding + 'px';
    }
    else if (state === false){
        email_menu_elem.style.setProperty('display','none','important');
    }
}
jQuery(follow_button).on('click',function(){
    follow_button_state('toggle');
    switch_email_ntfy.checked = true;
    email_notifications_menu(follow_button_state('get'));
});
jQuery(document).on('click',function(event){
    if (! (email_menu_elem.contains(event.target) || email_menu_elem === event.target) &&
        ! (follow_button.contains(event.target) || follow_button === event.target)){
        email_notifications_menu(false);
    }
});
function follow_collection(){
    const data_submit = {
        collection_id: jQuery('article.collection-content')[0].getAttribute('collection_id'),
        follow: follow_button_state('get'),
        email_ntfy: switch_email_ntfy.checked
    };
    progress(true);
    jQuery.ajax({
		url: '/wp-content/themes/book-writer/php/follow_collection.php',
        type: 'post',
        dataType: 'JSON',
        data: data_submit,
		success: function(response){
            if (response.code === 6){
                prompt_login();
                follow_button_state(false);
            }
            progress(false);
		}
	});

}
jQuery(follow_button).click(follow_collection);
jQuery(switch_email_ntfy).click(follow_collection);
jQuery(window).resize(function(){
    email_notifications_menu(false);
});