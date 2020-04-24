<main id="main" class="site-main" role="main">
<?php
$bookmarks = get_chapter_bookmarks();
?>
<ul class="collection">
<?php
foreach($bookmarks as $bookmark){
    $chapter = get_post($bookmark[0]);
    $book = get_post($chapter->post_parent);
    $content = explode('</p>',str_replace('<p>','',$chapter->post_content));
    ?>
    <li onclick="window.location = '<?php echo get_permalink($chapter->ID) . '#p-' . $bookmark[1]; ?>'" id="<?php echo 'li-' . $bookmark[0] . '_' . $bookmark[1]; ?>" style="color:var(--text-color) !important;padding-left:20px;border-radius:0%;" class="btn-hover collection-item avatar">
        <span class="title"><?php echo $chapter->post_title . ' - ' . $book->post_title; ?></span>
        <p><?php echo $content[$bookmark[1]-1]; ?></p>
        <a class="secondary-content"><i onclick="bookmark_this(<?php echo $bookmark[1]; ?>,<?php echo $bookmark[0]; ?>)" class="btn-bookmark btn-favorite active far fa-bookmark"></i></a>
    </li>
    <?php
}
?>
</ul>
<script>
	function bookmark_this(para,chapter){
		jQuery('#li-' + chapter + '_' + para + ' .secondary-content')[0].innerHTML = '<div class="loader" style="width:1rem;height:1rem;"></div>';
		jQuery.ajax({
			url: '/wp-content/themes/book-writer/php/bookmark_this.php',
			type: 'post',
			data: {ajax:1,para:para,chapter:chapter},
			success: function(response){
				jQuery('#li-' + chapter + '_' + para + ' .secondary-content')[0].innerHTML = '<i onclick="bookmark_this(' + para + ',' + chapter + ')" class="btn-bookmark btn-favorite far fa-bookmark"></i>';
				if (response == 0){
					jQuery('#li-' + chapter + '_' + para + ' .btn-bookmark').removeClass('active');
				}
				else if (response == 1){
					jQuery('#li-' + chapter + '_' + para + ' .btn-bookmark').addClass('active');
				}
				else if (response == 3){
				    jQuery('#li-' + chapter + '_' + para + ' .btn-bookmark').removeClass('active');
				    jQuery('#login-modal').modal('open');
				}
			}
		});			
	}
</script>
</main><!-- .site-main -->