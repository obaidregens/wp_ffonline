<?php
global $this_user;
$user = $this_user;
if(get_current_user_id() == $user->ID){
    $terms = array(strval($user->ID));
}
else{
    $terms = array(strval(get_current_user_id()),strval($user->ID));
}
$tax_query = array(
	'relation' => 'AND',
	array(
		'taxonomy'         => 'message_between',
		'terms'            => $terms,
		'field'            => 'name',
		'operator'         => 'AND',
		'include_children' => false,
	),
);
$cats = get_terms( array(
	'taxonomy' => 'message_between',
) );
$excluded_cats = array_diff(array_column($cats, 'name'),$terms);
$tax_query[] = array(
	'taxonomy'			=> 'message_between',
	'terms'				=> $excluded_cats,
	'field'				=> 'name',
	'operator'			=> 'NOT IN',		
);
// WP_Query arguments
$args = array(
	'post_type'              => array( 'message' ),
	'post_status'            => array( 'publish' ),
	'tax_query'              => $tax_query,
	'order'                  => 'ASC',
	'orderby'                => 'date',
	'posts_per_page'         => -1,
);

// The Query
$query = new WP_Query( $args );
$messages = $query->posts;
?>
<div id="user_info" value="<?php echo $user->ID; ?>" style="padding:20px;" class="row"><?php echo $user->display_name;?><i style="padding-left:10px;" onclick="block_this(<?php echo $user->ID; ?>)" class="<?php if(is_in_block_list($user->ID)){echo 'active ';} ?> fas fa-ban btn-block"></i><?php logged_in_status($user,true); ?></div>
<ul class="chat-window">
    <?php foreach($messages as $message) {
        if (get_user_meta($user->ID,'read_receipts',true) != 'hide' && get_user_meta(get_current_user_id(),'read_receipts',true) != 'hide'){
            if ($message->post_author != get_current_user_id()){
                wp_set_object_terms($message->ID,'Read', 'message_status');
            }
        }
        ?>
        <li id="<?php echo $message->ID; ?>" class="message-block <?php if ($message->post_author == get_current_user_id()){echo 'my';}else{echo 'other';} ?>">
            <div class="message-data">
                <?php
                $status_span = '<span class="message-status ' .  get_the_terms($message->ID,'message_status')[0]->slug  . '"><i class="fa fa-check fa-1" aria-hidden="true"></i></span>';
                $time_span = '<span class="message-time">' . $message->post_date . '</span>';
                if ($message->post_author == get_current_user_id()){echo $time_span . $status_span;}
                else{echo $status_span . $time_span;}
                ?>
            </div>
            <div class="message">
                <?php echo $message->post_content; ?>
            </div>
        </li>
        <?php
    } ?>
</ul>

<div class="row">
    <?php
    if((is_in_block_list(get_current_user_id(),$user->ID) || get_user_meta($user->ID,'allow_messaging',true) == 'disable') && $user->ID != get_current_user_id()){
        ?>
        <div class="input-field col m12 s12">
            <input disabled placeholder="Messaging is unavailable." id="message" type="text">
        </div>
        <?php
    }
    else { ?>
        <div class="input-field col m11 s10">
            <input autocomplete="new-password" placeholder="Type a message..." id="message" type="text">
        </div>
        <div class="input-field col m1 s2">
            <button disabled id="send_message" class="btn-hover btn-floating waves-effect waves-light" type="submit" name="action"><i class="material-icons">send</i></button>
        </div>
    <?php } ?>
</div>