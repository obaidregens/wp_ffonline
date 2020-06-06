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

<article class="acs-main">
	
	<?php the_title( '<h1 style="display:block;" class="section">', '</h1>' ); ?>
	<div class="acs-content" >
		<?php
		    $content = explode('</p>',$post->post_content);
			unset($content[count($content)-1]);
			$para_num = 0;
		    foreach($content as $para){
		        $para_num += 1;
		        ?><div id="p-<?php echo $para_num; ?>" style="position:relative;"><?php
				$para = $para . '</p>';
		        echo $para;
		        if (chapter_bookmark_exists($post->ID,$para_num)){
		            $active = ' active ';
		        }
		        else{
		            $active = '';
		        }
		        echo '<div style="position:absolute;top:0;right:0;"><div class="bookmark-wrapper"><i onclick="bookmark_this(' . $para_num . ',\'' . $post->ID . '\')" class="' . $active . 'btn-bookmark btn-favorite far fa-bookmark"></i></div></div>';
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