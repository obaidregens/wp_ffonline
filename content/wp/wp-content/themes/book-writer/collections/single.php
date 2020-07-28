<style>
	.email-notifications-menu{
		width: 482px;
		position: absolute;
		background-color: var(--background-accent);
		display:none !important;
		padding: 20px;
		min-height: 100px;
		border-radius: 5px;
		z-index: 996;
	}
	@media screen and (max-width: 500px){
		.email-notifications-menu{
			width: calc(100vw - 5%) !important;
		}
	}
</style>
<?php

global $collection;
$collection_notifications = collection::notifications()[$collection['ID']] ?? false;
get_header();
if (is_author()){
	get_template_part( 'author/header' );	
}
?>
<span style="display:none;" id="collection_notifications"><?= json_encode($collection_notifications); ?></span>
<div class="valign-wrapper email-notifications-menu">
	<div class="switch">
		<label>
			<input on_label="We'll send you an email when any book in this collection is updated." off_label="We won't send you any email notifications for this collection." name="anonymous_reviews" type="checkbox">
			<span class="lever"></span>
			<label></label>
		</label>
	</div>
</div>
<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<wrap style="text-align:center;">
			<?php get_template_part('collections/content'); ?>
			<div><button class="follow-button waves-effect waves-light btn-small"><i style="margin-right:5px;font-size:13px;" class="fas fa-plus"></i><span>Follow</span></button></div>
		</wrap>
		<?php get_template_part('template-parts/create','search'); ?>
	</main>
</div>
<?php
$js_b = new bundle('collection-single');
$js_b->js('js/collection-single');
$js_b->enqueue();
get_footer();
exit();