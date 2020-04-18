<div class="row">
    <div class="col s12">
        <div class="card-panel">
            <div class="section" style="color:var(--mid-theme-color);"><h3>Comments from last week.</h3></div>
            <?php
            $date_query = array(
                'relation' => 'AND',
                array(
					'column'		=> 'post_modified',
                    'after'         => 'last week',
                    'inclusive'     => true,
                ),
            );
            $args = array(
            	'post_author'    => get_current_user_id(),
            	'order'          => 'DESC',
            	'orderby'        => 'comment_date',
            	'date_query'     => $date_query,
            	'author__not_in' => array(get_current_user_id()),
            );
            
            $comment_query = new WP_Comment_Query( $args );
            if ($comment_query->found_comments == 0){
                $books = get_posts(array('post_type'=>'book','author'=>get_current_user_id(),'post_status'=>'publish'));
                $books_with_comments = get_posts(array('post_type'=>'book','author'=>get_current_user_id(),'post_status'=>'publish','comment_status'=>'open'));
                if (empty($books)){
                    echo "You don't have any published books yet.";
                }
                else if (empty($books_with_comments)){
                    echo 'Comments for all of your books are disabled.';
                }
                else{
                    echo "No comments from last week.";
                }
            }
            $comments = $comment_query->comments;
            foreach($comments as $comment){
            ?>
                <div class="row">
                    <div class="col s12">
                        <div class="grey-text" style="font-size: 16px;"><?php echo 'A comment by ' . $comment->comment_author . ' on "' . get_post($comment->comment_post_ID)->post_title . '":'; ?></div>
                        <div style="font-size: 14px;padding-left:20px;"><?php echo '"' . $comment->comment_content . '"'; ?></div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
<div class="row">
    <div class="col s12">
        <div class="card-panel">
            <div class="section" style="color:var(--mid-theme-color);"><h3>New Messages</h3></div>
            <div class="row">
                <div class="col s12 m12">
                <?php
                $users = find_current_user_chats();
                $unread_all = 0;
                foreach($users as $user_id){
                    $user = get_user_by('id',$user_id);
                    $unread = get_unread_messages_with($user_id);
                    if ($unread != 0){
                        $unread_all += 1;
                        ?>
                        <div class="collection">
                            <a onclick="load_page('chat','<?php echo $user->ID; ?>')" class="collection-item"><?php echo $user->display_name; logged_in_status($user); ?><span data-badge-caption="New Messages" class="new badge red"><?php echo $unread; ?></span></a>
                        </div>
                        <?php
                    }
                }
                if ($unread_all == 0){
                    echo "No new messages.";
                }
                ?>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col s12">
        <div class="card-panel">
            <div class="section" style="color:var(--mid-theme-color);"><h3>Announcements</h3></div>
            <?php
			$query = new WP_Query( array(
				'post_type' => 'post',
				'author'    =>12,
				'orderby'	=> 'modified',
				'posts_per_page' => -1,
				'date_query'     => $date_query,
			));
			$posts = $query->posts;
			foreach($posts as $post){
    			?>
                <div class="row">
                    <div style="padding-left:40px;" class="col s12 m12">
                        <h4><?php echo $post->post_title; ?></h4>
                        <p>
                            <?php the_excerpt(); ?>
                        </p>
                    </div>
                </div>
                <?php
            }
            if (! empty($posts) && count($posts) > 2){
    			?>
    			<!--
                <div class="row">
                    <div style="padding-left:40px;" class="col s12 m12">
                        <h4>Read more here</h4>
                    </div>
                </div>
                -->
                <?php                        
            }
            ?>
        </div>
    </div>
</div>
