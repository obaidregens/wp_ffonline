<?php
$app->bundle = new bundle('chapters');
$app->bundle->mix('jquery');
$app->bundle->mix('global_new');
$app->bundle->mix('intro');
$app->bundle->css('css/js-components/checkbox');
$app->bundle->css('css/components/index');
$app->bundle->css('css/js-components/confirmation');
$app->bundle->js('js/components/confirmation');
$app->bundle->css('css/components/dropdown');
$app->bundle->css('css/components/loader');
$app->bundle->css('css/components/tooltips');
$app->bundle->mix('search-options');
$app->bundle->css('css/views/chapter-main');
$app->bundle->css('css/views/chapter-reviews');
$app->bundle->js('js/views/chapter-main');
$app->bundle->css('css/views/chapter-acs');
$app->bundle->js('js/views/chapter-acs');
$app->bundle->css('css/views/chapter-search');
$app->bundle->js('js/views/chapter-search');
$app->bundle->mix('next-screen');
$app->bundle->mix('react');
$app->bundle->js('js/views/reviews-list');
$app->bundle->enqueue();

$chapter = $app->chapter;
$author = get_user_by( 'ID', $chapter->post_author );
$book = $app->story;
$all_chapters = published_chapters($book->ID);
$comments_open = comments_open( $book->ID );
$is_user_logged_in = is_user_logged_in(  );
$anon_review = get_post_meta($book->ID,'anon_review',true) === 'true';
$next_chapter_link = rtrim(get_permalink( $book->ID ),'/') . '/' . (intval(get_post_meta( $chapter->ID, 'chapter_order', true )) + 1);
?>
<main>
	<chapter chapter_id="<?= $chapter->ID; ?>">
		<chapter-header tabindex="1" class="popup">
			<book-info book_id="<?= $book->ID; ?>">
				<a class="title" href="<?= get_permalink( $book->ID ); ?>"><?= $book->post_title; ?></a>
				<author><?= author_href($chapter->post_parent); ?></author>
				<book-description hidden><?= $book->post_excerpt; ?></book-description>
			</book-info>
			<div class="down-arrow"></div>
		</chapter-header>
		<popup>
			<?= $app->template('/subviews/chapter-index'); ?>
		</popup>
		<chapter-title><?= $chapter->post_title; ?></chapter-title>
		<content class="acs-elem"><?= $chapter->post_content; ?></content>
		<a href="<?= $next_chapter_link; ?>" theme class="button next-chapter"></a>
		<book-options>
			<collections_data hidden>
				<?= json_encode(collection_helpers::js_data()) ?>
			</collections_data>
			<book_collections hidden>
				<?= json_encode( collection_helpers::query_by_book( array($book->ID) ) ); ?>
			</book_collections>
			<button class="book-collections"></button>
			<button class="book-share"></button>
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
	<button class="search-button popup"></button>
	<popup>
		<form>
			<text-input label="Search"></text-input>
			<button></button>
		</form>
		<results></results>
		<loader medium></loader>
	</popup>
</main>