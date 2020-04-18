<?php
/**
 * The template part for displaying single posts
 *
 * @package WordPress
 * @subpackage Twenty_Sixteen
 * @since Twenty Sixteen 1.0
 */
?>
<?php
if (!empty(preg_replace('/\s+/', '', get_post_meta($post->ID,'pre-chapter_notes',true )))){
?>
  <ul class="section collapsible margin">
	<li>
	  <div class="collapsible-header"><h4>Pre-Chapter Notes</h4></div>
	  <div class="collapsible-body"><span><?php echo get_post_meta($post->ID,'pre-chapter_notes',true ); ?> </span></div>
	</li>
  </ul>
<?php } ?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	
	<?php the_title( '<h1 style="display:block;" class="mobile-margin page-title section">', '</h1>' ); ?>
	<div class="entry-content chapter-content" >
		<?php
		    $content = explode('</p>',$post->post_content);
			unset($content[count($content)-1]);
		    $para_num = 0;
		    foreach($content as $para){
		        $para_num += 1;
		        ?><div id="p-<?php echo $para_num; ?>" style="margin-bottom:0;" class="row"><?php
		        $para_extra = str_replace('<p','',explode('>',$para)[0]);
		        $para = $para . '</p>';
				$para = preg_replace('~<p[^>]*>~', '<p style="margin-bottom:0;"' . $para_extra . ' class="col s11">', $para);
		        echo $para;
		        if (chapter_bookmark_exists($post->ID,$para_num)){
		            $active = ' active ';
		        }
		        else{
		            $active = '';
		        }
		        echo '<div class="col s1"><div class="bookmark-wrapper"><i onclick="bookmark_this(' . $para_num . ',\'' . $post->ID . '\')" class="' . $active . 'btn-bookmark btn-favorite far fa-bookmark"></i></div></div>';
		        ?></div><?php
		    }
		?>
	</div><!-- .entry-content -->
<style>
.btn-bookmark {
    display: none !important;
}

[id^=p-]:hover .btn-bookmark, .btn-bookmark:hover {
    display: inline-block !important;
}
</style>
</article><!-- #post-<?php the_ID(); ?> -->
<?php
	if (!empty(preg_replace('/\s+/', '', get_post_meta($post->ID,'post-chapter_notes',true )))){
?>
  <ul class="collapsible margin">
	<li>
	  <div class="collapsible-header"><h4>Post-Chapter Notes</h4></div>
	  <div class="collapsible-body"><span><?php echo get_post_meta($post->ID,'post-chapter_notes',true ); ?> </span></div>
	</li>
  </ul>
<?php } ?>
<script>
	function bookmark_this(para,chapter){
		jQuery('#p-' + para + ' .bookmark-wrapper')[0].innerHTML = '<div class="loader" style="width:21px;height:21px;"></div>';
		jQuery.ajax({
			url: '/wp-content/themes/book-writer/php/bookmark_this.php',
			type: 'post',
			data: {ajax:1,para:para,chapter:chapter},
			success: function(response){
				jQuery('#p-' + para + ' .bookmark-wrapper')[0].innerHTML = '<i onclick="bookmark_this(' + para + ',\'' + chapter + '\')" class="btn-bookmark btn-favorite far fa-bookmark"></i>';
				if (response == 0){
					jQuery('#p-' + para + ' .btn-bookmark').removeClass('active');
				}
				else if (response == 1){
					jQuery('#p-' + para + ' .btn-bookmark').addClass('active');
				}
				else if (response == 3){
				    jQuery('#p-' + para + ' .btn-bookmark').removeClass('active');
				    jQuery('#login-modal').modal('open');
				}
			}
		});			
	}
</script>