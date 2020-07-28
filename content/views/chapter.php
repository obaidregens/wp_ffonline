<link href="https://fonts.googleapis.com/css2?family=Montserrat&family=Open+Sans&family=Pangolin&family=Raleway&family=Roboto&display=swap" rel="stylesheet">
<?php

$app->bundle = new bundle('new_chapters_bundle');
$app->bundle->mix('jquery');
$app->bundle->mix('global_new');
$app->bundle->css('css/components/more');
$app->bundle->css('css/components/dropdown');
$app->bundle->css('css/components/loader');
$app->bundle->css('css/components/tooltips');
$app->bundle->mix('search-options');
$app->bundle->css('css/views/chapter-main');
$app->bundle->js('js/views/chapter-main');
$app->bundle->css('css/views/chapter-acs');
$app->bundle->js('js/views/chapter-acs');
$app->bundle->css('css/views/chapter-search');
$app->bundle->js('js/views/chapter-search');
$app->bundle->mix('next-screen');
$app->bundle->enqueue();

collection::create_default(get_current_user_id());
$chapter = $app->chapter;
$author = get_user_by( 'ID', $chapter->post_author );
$book = $app->book;
$all_chapters = published_chapters($book->ID);
$comments_open = comments_open( $chapter->ID );
$is_user_logged_in = is_user_logged_in(  );
$anon_review = get_post_meta($book->ID,'anon_review',true) === 'true';
?>
<main>
	<chapter chapter_id="<?= $chapter->ID; ?>">
		<chapter-header tabindex="1" class="dropdown">
			<book-info book_id="<?= $book->ID; ?>">
				<a class="title" href="<?= get_permalink( $book->ID ); ?>"><?= $book->post_title; ?></a>
				<author><?= author_href($chapter->post_parent); ?></author>
				<book-description hidden><?= $book->post_excerpt; ?></book-description>
			</book-info>
			<div class="down-arrow"></div>
			<dropdown>
				<?php foreach ($all_chapters as $key => $link_chapter ) { ?>
					<?php
					$attr = 'href="' . get_permalink( $link_chapter->ID ) . '"';
					if ($link_chapter->ID === $chapter->ID) {
						$next_chapter_href = '';
						if (isset($all_chapters[$key+1])){
							$next_chapter_href = 'href="' . get_permalink($all_chapters[$key+1]->ID) . '"';
						}
						$attr = 'selected';
					}
					?>
					<a <?= $attr; ?>><?= $link_chapter->post_title; ?></a>
				<?php } ?>
			</dropdown>
		</chapter-header>
		<chapter-title><?= $chapter->post_title; ?></chapter-title>
		<content><?= $chapter->post_content; ?></content>
		<a <?= $next_chapter_href; ?> theme class="button next-chapter"></a>
		<book-options>
			<collections_data hidden>
				<?= json_encode(collection::js_data()) ?>
			</collections_data>
			<book_collections hidden>
				<?= json_encode( collection::query_by_book( array($book->ID), 'ID' ) ); ?>
			</book_collections>
			<button class="book-collections"></button>
			<button class="book-share"></button>
		</book-options>
	</chapter>
	<reviews>
		<?php get_template_part('comments'); ?>
	</reviews>
	<?php if ( $comments_open && ( $is_user_logged_in || $anon_review ) ){	?>
		<write-review>
			<reply-to hidden review_id="0"></reply-to>
			<text-input type="multi" label="Write Review">
			</text-input>
			<reCAPTCHA></reCAPTCHA>
			<button label="Submit"></button>
		</write-review>
	<?php }	else if ($comments_open && ! $is_user_logged_in) { ?>
		<a onclick="prompt_login();">Anonymous reviews have been disabled. Login to review.</a>
	<?php }	else if (! $comments_open) { ?>
		<text>Comments are closed.</text>
	<?php } ?>
	<button class="acs-button popup"></button>
	<popup>
		<change-options action="font">
			<button theme class="dropdown">
				Font
				<dropdown class="right">
					<li></li>
					<li></li>
					<li></li>
					<li></li>
					<li></li>
					<li></li>
				</dropdown>
			</button>
		</change-options>
		<change-options action="fontSize">
			<button></button>
			<button></button>
		</change-options>
		<change-options action="lineHeight">
			<button></button>
			<button></button>
		</change-options>
		<change-options action="paragraphHeight">
			<button></button>
			<button></button>
		</change-options>
		<change-options action="width">
			<button></button>
			<button></button>
		</change-options>
		<change-options action="theme">
			<div color="light"></div>
			<div color="dark"></div>
			<div color="peach"></div>
		</change-options>
	</popup>
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