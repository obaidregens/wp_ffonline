<?php
$users = find_current_user_chats();
foreach($users as $user_id){
    $user = get_user_by('id',$user_id);
    $unread = get_unread_messages_with($user_id);
    ?>
    <div class="collection">
        <a onclick="load_page('chat','<?php echo $user->ID; ?>')" class="collection-item"><?php echo $user->display_name; logged_in_status($user); ?><?php if ($unread != 0){ ?><span data-badge-caption="New Messages" class="new badge red"><?php echo $unread; ?></span><?php } ?></a>
    </div>
    <?php
}
if (empty($users)){
    ?>
    <ul class="collection">
        <li class="collection-item">No Messages</li>
    </ul>
    <?php
}
?>