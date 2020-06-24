<?php
/**
 * The template for displaying comments
 *
 * The area of the page that contains both current comments
 * and the comment form.
 *
 * @package WordPress
 * @subpackage Twenty_Sixteen
 * @since Twenty Sixteen 1.0
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
$book = get_post($post->post_parent);
?>

<div id="comments" class="mobile-margin">
	<h1 class="section">Reviews</h1>
	<?php if ( $post->comment_count != 0 ) { ?>
		<?php
			function print_comment($comment,$level = 1){
				$post = get_post($comment->comment_post_ID);
				$comment_author = get_userdata($comment->user_id);
				$more = 0;
				?>
				<article style="<?php if($level == 2){echo 'margin-left:8%;';} ?>line-height:1;padding-top:0 !important;margin-bottom:0 !important;padding-bottom:0;" id="comment-<?php echo $comment->comment_ID; ?>" class="comment-body">
					<!-- Dropdown Structure -->
					<ul id='more-<?php echo $comment->comment_ID; ?>' class='dropdown-content'>
						<?php if ($post->post_author == get_current_user_id() && $level == 1) { $more = 1; ?> <li><a onclick="reply_to(<?php echo $comment->comment_ID; ?>)"><i class="fas fa-reply"></i>Reply</a></li><?php } ?>
						<?php if (get_current_user_id() == $comment->user_id) { $more = 1; ?><li><a onclick="delete_comment(<?php echo $comment->comment_ID; ?>)"><i class="fas fa-trash"></i>Delete</a></li><?php } ?>
					</ul>
					<footer style="padding-bottom: 8px;">
						<span class="comment-author"><?php echo $comment_author->display_name; if($comment_author->ID == $post->post_author){echo '<label> (Book Author)</label>';} ?></span>
						<?php if ($more == 1){ ?>
						<a class="right btn-hover btn-floating dropdown-trigger" data-target="more-<?php echo $comment->comment_ID; ?>"><i style="font-size:1rem;" class="fas fa-ellipsis-v"></i></a>
						<?php } ?>
						<div><label><time><?php echo human_time_diff(get_comment_time('U')) . ' ago'; ?></time></label></div>
					</footer>
					<div>
						<p class="comment-content"><?php echo $comment->comment_content; ?></p>
					</div>				
				</article>
				<?php
			}
		$comments = get_comments(array(
			'post_id' => $post->ID,
			'hierarchical' => 'threaded',
		));
		foreach($comments as $comment){
			print_comment($comment);
			$child_comments = get_comments(array(
				'parent'	=> $comment->comment_ID,
				'hierarchical' => 'flat'
			));
			foreach($child_comments as $child_comment){
				print_comment($child_comment,2);
			}
			?>
		<?php } //Loop through comments ?>
	<?php } // Check if has comments ?>

	<?php
		// If comments are closed and there are comments, let's leave a little note, shall we?
	if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) :
		?>
	<p class="no-comments"><?php _e( 'Comments are closed.', 'twentysixteen' ); ?></p>
	<?php endif; ?>
	<div id="respond" class="comment-respond">
		<?php if (is_user_logged_in() || get_post_meta($book->ID,'anon_review',true) == 'true'){ ?>
			<div style="padding-top:0px;" class="comment-form">
				<div id="reply-wrapper">
				<!--<div style="padding-bottom:2px;">Replying to Fanfiction Online <a style="padding-left:4px;" href="#">Cancel</a></div>
				<blockquote style="margin:0;font-size:14px;">
					This is an example quotation that uses the blockquote tag.
				</blockquote>-->
				</div>
				<input type="hidden" id="reply_id" value="0">
				<div class="row">
					<div class="input-field col s12">
						<textarea required id="comment" class="materialize-textarea"></textarea>
						<label for="comment">Leave a review</label>
					</div>
				</div>
				<div id="submit-comment-wrapper" class="right"><button onclick="submit_comment()" class="waves-effect waves-light btn">Submit</button></div>
			</div>
			<div id="reCAPTCHA_div" style="margin:0 20px 20px;" class="g-recaptcha" data-sitekey="6Lc_ROEUAAAAAE2WALbN67FKxK284OnW7jSxEBth"></div>
		<?php } else {?>
			<a class="modal-trigger" data-target="login-modal">Anonymous reviews are disabled by the author. Login to add a review.</a>
		<?php } ?>
		
	</div>

</div><!-- .comments-area -->