<li style="background-color:var(--light-theme-color);"><a class="subheader">Notifications</a></li>
<?php
$notifications = get_stats_of('notifications',get_current_user_id(),'last week');
foreach($notifications as $notification){
	$href = get_permalink($notification['id']);
    if ($notification['type'] == 'message'){
        $icon = 'fas fa-envelope';
        $message = 'You\'ve received a message from ' .  get_the_author_meta('display_name',get_post($notification['id'])->post_author) . '.';
    }
    else if ($notification['type'] == 'followed'){
        $icon = 'fas fa-book';
		$chapter = get_post($notification['id']);
		$book = get_post($chapter->post_parent);
		if ($book->post_status != 'publish' ){
			$href="/404";
		}
        if ($notification['extra'] == 'favorites' || $notification['extra'] == 'hidden'){
            $message = '\'' . $book->post_title . '\' from your private collection \'' . ucfirst($notification['extra']) . '\' has been updated.';
        }
        else{
            $message = '\'' . get_post(get_post($notification['id'])->post_parent)->post_title . '\' from your ' . strtolower(get_term_meta($notification['extra'],'public_collection',true)) . ' collection \'' . get_term($notification['extra'])->name . '\' has been updated.';
        }
    }
    else if ($notification['type'] == 'comment'){
		$book = get_post(get_post(get_comment($notification['id'])->comment_post_ID)->post_parent);
        $icon = 'fas fa-comment-alt';
        $message = 'You\'ve received a comment on your book \'' . $book->post_title . '\'.';
		$href = get_comment_link($notification['id']);
		if ($book->post_status != 'publish'){
			$href="/404";
		}
    }
    else if ($notification['type'] == 'favorited'){
        $icon = 'fas fa-heart';
        $message = 'Your book \'' . get_post($notification['id'])->post_title . '\' has been favorited by \'' . get_the_author_meta('display_name',$notification['extra']) . '\'.';
		if (get_post($notification['id'])->post_status != 'publish' ){
			$href="/404";
		}
    }
    ?>
	<li onclick="window.open('<?php echo $href; ?>', '_blank');" class="row btn-hover" style="cursor:pointer;margin-bottom:0;">
		<div class="center col s2 m1">
			<i class="<?php echo $icon; ?>"></i>
		</div>
		<div class="col s10 m11">
			<p style="padding-top:10px;line-height:22px;color:#9e9e9e !important;"><?php echo $message; ?></p>
		</div>
	</li>
<?php } ?>
<?php if (empty($notifications)){ ?>
    <li><a class="subheader">No notifications.</a></li>
<?php } ?>