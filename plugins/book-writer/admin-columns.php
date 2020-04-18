<?php
//Chapter Columns
add_filter( 'manage_chapter_posts_columns', 'add_chapter_columns' );
function add_chapter_columns( $columns )
{
	$columns['book'] = __( 'Book' );
	$columns['author'] = __( 'Author' );
	return $columns;
}


add_action( 'manage_chapter_posts_custom_column', 'chapter_columns', 10, 2);
function chapter_columns( $column, $post_id )
{
	// Populate book column
	if ( 'book' === $column )
	{
		$book_id = wp_get_post_parent_id($post_id);
		if ($book_id === 0)
		{
			_e( 'No Book' );  
		}
		else
		{
			echo get_the_title($book_id);
		}
	}
	// Populate author column
	if ( 'author' === $column )
	{
		$author = get_the_author_meta('display_name', get_post_meta($post_id, 'author'));
		echo $author;
	}
}

add_filter( 'manage_edit-chapter_sortable_columns', 'chapter_columns_sortable');
function chapter_columns_sortable($columns)
{
	$columns['book'] = 'post_parent';
	return $columns;
}


//Book Columns
add_filter( 'manage_book_posts_columns', 'add_book_columns' );
function add_book_columns( $columns )
{
	$columns['author'] = __( 'Author' );
	return $columns;
}


add_action( 'manage_book_posts_custom_column', 'book_columns', 10, 2);
function book_columns( $column, $post_id )
{

	// Populate author column
	if ( 'author' === $column )
	{
		$author = get_the_author_meta('display_name', get_post_meta($post_id, 'author'));
		echo $author;
	}
}