<?php
global $book_query;
global $book;
?>
<article book_id="<?= $book->ID; ?>" id="book-<?= $book->ID; ?>" class="mainsearch-item" >
	<a class='dropdown-trigger book-options btn-hover waves-effect waves-light btn-floating' data-target='book-options-<?= $book->ID; ?>'><i class="fas fa-angle-down"></i></a>
	<!-- Dropdown Structure -->
	<ul id='book-options-<?= $book->ID; ?>' class='dropdown-content'>
		<?php if ($book->post_author === get_current_user_id()) { ?>
			<li><a target="_blank" href="/dashboard/write/<?= $book->ID; ?>" class="book-edit"><i class="fas fa-pencil-alt"></i>Edit</a></li>
		<?php } ?>
		<li><a class="book-share"><i class="fas fa-share-alt"></i>Share</a></li>
		<li><a class="book-favorite"></a></li>
		<li><a class="book-hide"></a></li>
		<li><a <?php if (! is_user_logged_in()){echo ' disabled ';} ?> class="book-collections"><i class="fas fa-list-ul"></i>Collections</a></li>

	  </ul>
	<div class="home-desc">
	<header>
		<?php
		echo '<h2 class="entry-title" style="font-weight:500;display: inline-block;margin-bottom:0em;"><a href="' . get_permalink($book->ID) . '" rel="bookmark">';
        if( isset($book_query->args['search']) && $book_query->args['search'] !== ''){
            $title = $book->post_title;
			$title = preg_replace('/(' . $book_query->args['search'] . ')+/i','<mark>$1</mark>',$title);
            echo $title;
        }
        else{
            echo $book->post_title;
        }
		echo '</a></h2>';
		?>
		<?php
		echo '<h5 class="entry-author" style="margin-bottom:1.05em;">by ' . author_href($book->ID) . '</h5>';
		?>
	</header><!-- .entry-header -->
		
	<div class="search-summary">
		<p>
		<?php
		if (preg_replace('/\s+/', '',$book->post_excerpt) != ''){
            if( isset($book_query->args['search']) && $book_query->args['search'] !== ''){
                $excerpt = $book->post_excerpt;
				$excerpt = preg_replace('/(' . $book_query->args['search'] . ')+/i','<mark>$1</mark>',$excerpt);
                echo $excerpt;
            }
            else{
                echo $book->post_excerpt;
            }
		}
		?>
		</p>
	</div>
	</div>
	<?php get_template_part( 'template-parts/header', 'taxonomy' ); ?>
</article>
