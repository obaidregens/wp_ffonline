<?php
global $app;
$post = $app->chapter;
$comments = reviews::get($post->ID);
function print_comment($comment) {
	$is_book_author = intval($comment->user_id) === intval($post->post_author);
	$can_reply = get_current_user_id() === intval($post->post_author) && intval($comment->user_id) !== get_current_user_id();
	$can_delete = get_current_user_id() === intval($comment->user_id);
	?>
	<?php $comment_author = get_user_by( 'ID', $comment->user_id ); ?>
	<review review_id="<?= $comment->comment_ID; ?>">
		<a <?= $is_book_author ? 'tooltip-top="Book Author"' : ''; ?> class="author <?= $is_book_author ? 'book-author' : ''; ?>"><?= $comment_author->display_name; ?></a>
		<review-time><?= human_time_diff(get_comment_time('U')) . ' ago'; ?></review-time>
		<review-content more tabindex="0"><?= $comment->comment_content; ?></review-content>
		<?php if ($can_reply || $can_delete) { ?>
			<button class="dropdown">
				<dropdown class="right">
					<?php if ( $can_reply ) { ?>
						<li label="Reply"></li>
					<?php } ?>
					<?php if ( $can_delete ) { ?>
						<li label="Delete"></li>
					<?php } ?>
				</dropdown>
			</button>
		<?php } ?>
		<?php
		$children = $comment->get_children();
		foreach ($children as $child ) {
			print_comment($child);
		}
		?>
	</review>
	<?php
}
foreach ($comments as $comment ) {
	print_comment($comment);
}