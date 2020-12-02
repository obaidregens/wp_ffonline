<?php
$app->bundle = global_bundle('chapters');
$app->bundle->mix('speak');
$app->bundle->mix('confirmation');
$app->bundle->mix('story-options');
$app->bundle->js('external/nosleep/NoSleep');
$app->bundle->css('css/js-components/checkbox');
$app->bundle->css('css/components/loader');
$app->bundle->css('css/components/tooltips');
$app->bundle->css('css/components/slider');
$app->bundle->css('css/views/chapter-index');
$app->bundle->css('css/views/chapter-main');
$app->bundle->css('css/views/chapter-reviews');
$app->bundle->css('css/views/chapter-writeReview');
$app->bundle->js('js/views/chapter-reviews');
$app->bundle->js('js/views/chapter-main');
$app->bundle->css('css/views/chapter-acs');
$app->bundle->js('js/views/chapter-acs');
$app->bundle->css('css/views/chapter-search');
$app->bundle->js('js/views/chapter-search');
$app->bundle->js('js/views/reviews-list');
$app->bundle->js('js/views/chapter-tracking');
$app->bundle->js('js/views/story-offlineAPI');
$app->bundle->js('js/views/chapter-offline');

// Options
$app->bundle->css('css/js-components/menu');
$app->bundle->js('js/components/menu');
$app->bundle->js('js/views/chapter-options');

$chapter = $app->chapter;
$book = $app->story;
$all_chapters = published_chapters($book->ID);
$comments_open = comments_open( $book->ID );
$is_user_logged_in = is_user_logged_in(  );
$anon_review = get_post_meta($book->ID,'anon_review',true) === 'true';

$app->selected_chapter = $chapter->ID;
$next_chapter_num = intval(get_post_meta( $chapter->ID, 'chapter_order', true )) + 1;
$query = (new WP_Query(array(
	'post_type' => array('chapter'),
	'meta_query' => array(
		array(
			'key'       => 'chapter_order',
			'value'     => $next_chapter_num,
			'type'      => 'NUMERIC',
		),
	),
	'post_parent__in'   => array($book->ID)
)))->posts;
$next_chapter_link = empty($query) ? false : get_permalink( $query[0]->ID );
$is_test = $app->type === "test-chapter";
?>
<chapter class="<?= $is_test ? "test-story" : ""; ?>" chapter_id="<?= $chapter->ID; ?>" num="<?= $next_chapter_num-1 ?>">
	<chapter-header tabindex="1" >
		<book-info book_id="<?= $book->ID; ?>">
			<a class="title" href="<?= get_permalink( $book->ID ); ?>"><?= htmlspecialchars($book->post_title); ?></a>
			<author><?= author_href($chapter->post_parent); ?></author>
			<book-description hidden><?= htmlspecialchars($book->post_excerpt); ?></book-description>
		</book-info>
		<div class="down-arrow"></div>
	</chapter-header>
	<chapter-title><?= htmlspecialchars($chapter->post_title); ?></chapter-title>
	<content class="acs-elem"><author-notes><?= htmlspecialchars(get_post_meta( $chapter->ID, 'pre_author_note', true )); ?></author-notes><?= $chapter->post_content; ?><author-notes><?= htmlspecialchars(get_post_meta( $chapter->ID, 'post_author_note', true )); ?></author-notes></content>
	<a <?= $next_chapter_link ? 'href="' . $next_chapter_link . '"': ""; ?> theme class="button next-chapter"></a>
	<book-options>
		<button <?= is_current_user($chapter->post_author) ? 'disabled' : ''; ?> class="book-vote <?= vote::exists('chapter',$chapter->ID) ? 'active' : '' ?>"></button>
		<button <?= $is_test ? "disabled" : "" ?> class="book-collections"></button>
		<button <?= $is_test ? "disabled" : "" ?> class="book-share"></button>
		<button <?= $is_test ? "disabled" : "" ?> class="book-offline"></button>
	</book-options>
</chapter>
<?php if ( reviews::can_review($chapter->ID)  ){	?>
	<write-review>
		<reply-to hidden review_id="0"></reply-to>
		<quote></quote>
		<cancel></cancel>
		<text-input type="multi" label="Leave a review"></text-input>
		<submission>
			<reCAPTCHA></reCAPTCHA>
			<button label="Submit"></button>
		</submission>
	</write-review>
<?php }	else if ($comments_open && ! $is_user_logged_in) { ?>
	<a onclick="window.expose.prompt_login();">Anonymous reviews have been disabled. Login to review.</a>
<?php }	else if (! $comments_open) { ?>
	<text>Reviews are closed.</text>
<?php } ?>
<reviews-wrapper></reviews-wrapper>
<popup class="chapter-index">
	<?= $app->template('/subviews/chapter-index'); ?>
</popup>
<popup class="search-story">
	<form>
		<text-input label="Search"></text-input>
		<button></button>
	</form>
	<results></results>
	<loader medium></loader>
</popup>
<?php
$bundle = new bundle('react-ps');
$bundle->mix('react');
$bundle->print();
