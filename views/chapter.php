<?php
$app->bundle = new bundle('chapters');
$app->bundle->mix('jquery');
$app->bundle->mix('global_new');
$app->bundle->mix('intro');
$app->bundle->css('css/js-components/checkbox');
$app->bundle->css('css/components/index');
$app->bundle->css('css/js-components/confirmation');
$app->bundle->js('js/components/confirmation');
$app->bundle->css('css/components/loader');
$app->bundle->css('css/components/tooltips');
$app->bundle->css('css/components/slider');
$app->bundle->mix('search-options');
$app->bundle->css('css/views/chapter-main');
$app->bundle->css('css/views/chapter-reviews');
$app->bundle->js('js/views/chapter-main');
$app->bundle->css('css/views/chapter-acs');
$app->bundle->js('js/views/chapter-acs');
$app->bundle->css('css/views/chapter-search');
$app->bundle->js('js/views/chapter-search');
$app->bundle->js('js/views/reviews-list');
$app->bundle->js('js/views/chapter-tracking');
$app->bundle->js('js/views/story-offlineAPI');
$app->bundle->js('js/views/chapter-offline');
$app->bundle->enqueue();

$chapter = $app->chapter;
$author = get_user_by( 'ID', $chapter->post_author );
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
?>
<chapter chapter_id="<?= $chapter->ID; ?>" num="<?= $next_chapter_num-1 ?>">
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
		<script>
			window.collections_data = <?= script_json(json_encode(collection_helpers::js_data())); ?>;
			window.book_collections = <?= script_json(json_encode(collection_helpers::query_by_book(array_column($book_query->books,'ID')))); ?>;
		</script>
		<button <?= is_current_user($chapter->post_author) ? 'disabled' : ''; ?> class="book-vote <?= vote::exists('chapter',$chapter->ID) ? 'active' : '' ?>"></button>
		<button class="book-collections"></button>
		<button class="book-share"></button>
		<button class="book-offline"></button>
	</book-options>
</chapter>
<reviews-wrapper></reviews-wrapper>
<?php if ( reviews::can_review($chapter->ID)  ){	?>
	<write-review>
		<reply-to hidden review_id="0"></reply-to>
		<text-input type="multi" label="Write Review"></text-input>
		<reCAPTCHA></reCAPTCHA>
		<button label="Submit"></button>
	</write-review>
<?php }	else if ($comments_open && ! $is_user_logged_in) { ?>
	<a onclick="prompt_login();">Anonymous reviews have been disabled. Login to review.</a>
<?php }	else if (! $comments_open) { ?>
	<text>Reviews are closed.</text>
<?php } ?>
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
$bundle->enqueue();
$bundle->print();
