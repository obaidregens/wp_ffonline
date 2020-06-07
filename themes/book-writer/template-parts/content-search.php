
<article book_id="<?php the_ID(); ?>" id="book-<?php the_ID(); ?>" class="mainsearch-item" <?php //post_class(); ?>>
	<a class='dropdown-trigger book-options btn-hover btn-floating' data-target='book-options-<?php the_ID(); ?>'><i class="fas fa-angle-down"></i></a>
	<!-- Dropdown Structure -->
	<ul id='book-options-<?php the_ID(); ?>' class='dropdown-content'>
		<?php if ($post->post_author == get_current_user_id()) { ?>
			<li><a target="_blank" href="/dashboard/write/<?php the_ID(); ?>" class="book-edit"><i class="fas fa-pencil-alt"></i>Edit</a></li>
		<?php } ?>
		<li><a class="book-share"><i class="fas fa-share-alt"></i>Share</a></li>
		<li><a class="book-favorite"></a></li>
		<li><a class="book-hide"></a></li>
		<li><a <?php if (! is_user_logged_in()){echo ' disabled ';} ?> class="book-collections"><i class="fas fa-list-ul"></i>Collections</a></li>

	  </ul>
	<div class="home-desc">
	<header>
		<?php
		echo '<h2 class="entry-title" style="font-weight:500;display: inline-block;margin-bottom:0em;"><a href="' . get_permalink() . '" rel="bookmark">';
        if( isset($wp_query->query_vars['search_key']) && $wp_query->query_vars['search_key'] != ''){
            $title = $post->post_title;
			$title = preg_replace('/(' . $wp_query->query_vars['search_key'] . ')+/i','<mark>$1</mark>',$title);
            echo $title;
        }
        else{
            echo $post->post_title;
        }
		echo '</a></h2>';
		?>
		<?php
		echo '<h5 class="entry-author" style="margin-bottom:1.05em;">by ' . author_href($post->ID) . '</h5>';
		?>
	</header><!-- .entry-header -->
		
	<div class="search-summary">
		<p>
		<?php if (preg_replace('/\s+/', '',$post->post_excerpt) != ''){
            if( isset($wp_query->query_vars['search_key']) && $wp_query->query_vars['search_key'] != ''){
                $excerpt = $post->post_excerpt;
				$excerpt = preg_replace('/(' . $wp_query->query_vars['search_key'] . ')+/i','<mark>$1</mark>',$excerpt);
                echo $excerpt;
            }
            else{
                echo $post->post_excerpt;
            }
		} ?>
		</p>
	</div>
	</div>
	<?php get_template_part( 'template-parts/header', 'taxonomy' ); ?>
</article>