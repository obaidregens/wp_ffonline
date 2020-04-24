<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');
if( isset($_POST['ajax']) && isset($_POST['s']) ){
	function strpos_r($haystack, $needle){
		if(strlen($needle) > strlen($haystack)){
			trigger_error(sprintf("%s: length of argument 2 must be <= argument 1", __FUNCTION__), E_USER_WARNING);
		}
		$seeks = array();
		while($seek = strripos($haystack, $needle)){
			array_push($seeks, $seek);
			$haystack = substr($haystack, 0, $seek);
		}
		return $seeks;
	}
	global $index_total;
	$index_total = 0;
	function cut_and_echo($input,$s,$format = 'count',$name,$link){
		//Format can be '<p>' || 'count'
		if (stripos($input,$s) !== false){
			global $index_total;
			$strpos_r = array_reverse(strpos_r($input,$s));
			$p_tags = array_reverse(strpos_r($input,'</p>'));
			$current = '';
			$prev = false;
			foreach($strpos_r as $strpos){
				if ($index_total >= 150){
					break;
				}
				
				if ($format == 'count'){
					$start = max($strpos-30,0);
					$end = min($start+60,strlen($input));
					$current = substr($input,$start,$end-$start);
				}
				else if ($format == '<p>'){
					$p_before = array();
					$p_after = array();
					foreach($p_tags as $p_tag){
						if ($p_tag <= $strpos){
							$p_before[] = $p_tag;
						}
						if ($p_tag >= $strpos){
							$p_after[] = $p_tag;
						}
					}
					$start = 0;
					if (! empty($p_before)){
						$start = max($p_before);
					}
					$end = strlen($input);
					if (! empty($p_after)){
						$end = min($p_after);
					}
					
					//Plus 1 because of zero indexing, Plus 1 because </p> is for the previous
					$link_this = $link . '#p-' . (array_search($start,$p_tags)+2);
					$current = str_replace(array('<p>','</p>'),'',substr($input,$start,$end-$start));
				}
				if ($current != $prev){
					?>
					<div class="row search-item btn-hover" onclick="window.open('<?php echo $link_this; ?>', '_blank')">
						<span><?php echo $name; ?></span>
						<p><?php echo preg_replace('/(' . $s . ')+/i','<mark>$1</mark>',$current); ?></p>
					</div>
					<?php
					$prev = $current;
					$index_total += 1;
				}
			}
		}
	}
	$s = $_POST['s'];
    $book_id = get_post(intval($_POST['chapter_id']))->post_parent;
	$args = array(
		'post_type'              => array( 'book' ),
		'post_status'            => array( 'publish' ),
		'posts_per_page'		 => 1,
		'post__in'	 => array($book_id),
	);
    $query = new WP_Query( $args );
	$title = $query->posts[0]->post_title;
	$strpos = stripos($title,$s);
	if ($strpos !== false){
		$index_total += 1;
		?>
		<div class="row search-item btn-hover" onclick="window.open('<?php echo get_permalink($query->posts[0]->ID); ?>', '_blank')">
			<span>Book Title</span>
			<p><?php echo preg_replace('/(' . $s . ')+/i','<mark>$1</mark>',$title); ?></p>
		</div>
		<?php
	}
	$excerpt = $query->posts[0]->post_excerpt;
	$strpos = stripos($excerpt,$s);
	if ($strpos !== false){
		$index_total += 1;
		?>
		<div class="row search-item btn-hover" onclick="window.open('<?php echo get_permalink($query->posts[0]->ID); ?>', '_blank')">
			<span>Book Summary</span>
			<p><?php echo preg_replace('/(' . $s . ')+/i','<mark>$1</mark>',$excerpt); ?></p>
		</div>
		<?php
	}
	cut_and_echo($query->posts[0]->post_content,$s,'<p>','Book Description',get_permalink($query->posts[0]->ID));
	$args = array(
		'post_type'              => array( 'chapter' ),
		'post_status'            => array( 'publish' ),
		'posts_per_page'		 => -1,
		'order'					 => 'ASC',
		'post_parent'			 => $book_id,
	);
    $query = new WP_Query( $args );
	foreach ($query->posts as $chapter){
		$title = $chapter->post_title;
		$strpos = stripos($title,$s);
		if ($strpos !== false){
			$index_total += 1;
			?>
			<div class="row search-item btn-hover" onclick="window.open('<?php echo get_permalink($chapter->ID); ?>', '_blank')">
				<span>#<?php echo get_post_meta($chapter->ID,'chapter_order',true); ?> - Chapter Title</span>
				<p><?php echo preg_replace('/(' . $s . ')+/i','<mark>$1</mark>',$title); ?></p>
			</div>
			<?php
		}
		cut_and_echo($chapter->post_content,$s,'<p>','#' . get_post_meta($chapter->ID,'chapter_order',true) . ' - ' . $chapter->post_title,get_permalink($chapter->ID));
		cut_and_echo(get_post_meta($chapter->ID,'pre-chapter_notes',true),$s,'count','#' . get_post_meta($chapter->ID,'chapter_order',true) . ' - Author Notes',get_permalink($chapter->ID));
		cut_and_echo(get_post_meta($chapter->ID,'post-chapter_notes',true),$s,'count','#' . get_post_meta($chapter->ID,'chapter_order',true) . ' - Author Notes',get_permalink($chapter->ID));
	}
	if($index_total == 0){
		echo '<div>No results found</div>';
	}
	else if ($index_total >= 150){
		echo '<div class="section"></div>';
		echo '<div>Some results have been hidden because your search was too broad.</div>';
	}
	exit;
}