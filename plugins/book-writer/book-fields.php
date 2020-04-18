<?php

//Add Meta Boxes for selection
function add_desc_box() {
	add_meta_box( 'book-description', 'Detailed Description', 'detailed_description', 'book', 'normal', 'low' );
}
add_action( 'add_meta_boxes', 'add_desc_box' );
function detailed_description( $post ) {
	$post_type_object = get_post_type_object( $post->post_type );
	$pages = wp_dropdown_pages( array( 'post_type' => 'book', 'selected' => $post->post_parent, 'name' => 'parent_id', 'show_option_none' => __( '(No Book)' ), 'sort_column'=> 'menu_order, post_title', 'echo' => 0, 'authors' => get_current_user_id(), 'post_status' => array('publish','draft'),));
	if ( ! empty( $pages ) ) {
		echo $pages;
	}
	else {
		echo 'You have no books, please <a href="/wp-admin/post-new.php?post_type=book">create a book</a> first.';
	}
}

add_action('publish_book', 'pairing_to_character', 11,2);
function pairing_to_character($postid, $post_obj)
{
	$characters = array();
	$pairings = get_the_terms($postid, 'pairing');
	if (!is_wp_error($pairings) && $pairings != false){
		foreach ($pairings as $pairing)
		{
			$characters = array_merge($characters,explode("/",$pairing->name));
		}
		wp_set_object_terms($postid,$characters,'character',true);
	}
}

function label_excerpt_summary( $translation, $original ) {
	global $max_summary_length;
    if ( 'Excerpt' == $original ) {
        return __( 'Summary' );
    } elseif ( false !== strpos( $original, 'Excerpts are optional hand-crafted summaries of your' ) ) {
        return __( 'This summary will be shown in searches.' );
    }
    return $translation;
}
add_filter( 'gettext', 'label_excerpt_summary', 10, 2 );

function post_update_messages($messages)
{
    $messages['book'] = array
	(
     0 => '', // Unused. Messages start at index 1.
	 1 => __( 'Book updated.' ) . $view_post_link_html,
	 2 => __( 'Custom field updated.' ),
	 3 => __( 'Custom field deleted.' ),
	 4 => __( 'Book updated.' ),
	/* translators: %s: date and time of the revision */
	 5 => isset( $_GET['revision']) ? sprintf( __( 'Book restored to revision from %s.' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
	 6 => __( 'Book published.' ) . $view_post_link_html,
	 7 => __( 'Book saved.' ),
	 8 => __( 'Book submitted.' ) . $preview_post_link_html,
	 9 => sprintf( __( 'Book scheduled for: %s.' ), '<strong>' . $scheduled_date . '</strong>' ) . $scheduled_post_link_html,
	10 => __( 'Book draft updated.' ) . $preview_post_link_html,
    );
	
	$messages['chapter'] = array
	(
     0 => '', // Unused. Messages start at index 1.
	 1 => __( 'Chapter updated.' ) . $view_post_link_html,
	 2 => __( 'Custom field updated.' ),
	 3 => __( 'Custom field deleted.' ),
	 4 => __( 'Chapter updated.' ),
	/* translators: %s: date and time of the revision */
	 5 => isset( $_GET['revision']) ? sprintf( __( 'Chapter restored to revision from %s.' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
	 6 => __( 'Chapter published.' ) . $view_post_link_html,
	 7 => __( 'Chapter saved.' ),
	 8 => __( 'Chapter submitted.' ) . $preview_post_link_html,
	 9 => sprintf( __( 'Chapter scheduled for: %s.' ), '<strong>' . $scheduled_date . '</strong>' ) . $scheduled_post_link_html,
	10 => __( 'Chapter draft updated.' ) . $preview_post_link_html,
    );
    return $messages;
}
add_filter('post_updated_messages','post_update_messages',1,1);


add_filter( 'radio_buttons_for_taxonomies_no_term_rating', '__return_FALSE' );
add_filter( 'radio_buttons_for_taxonomies_no_term_language', '__return_FALSE' );
add_filter( 'radio_buttons_for_taxonomies_no_term_status', '__return_FALSE' );


//Discussion Box, Creating New Box to remove [✓] Allow pingback...
add_action( 'admin_menu', 'remove_discussion_meta_box' );
add_action( 'add_meta_boxes', 'add_comments_meta_box' );

function remove_discussion_meta_box() {
    remove_meta_box('commentstatusdiv', 'book', 'normal');
}

function add_comments_meta_box() {
        add_meta_box(
            'comments_metabox',
            __( 'Comments' ),
            'comments_meta_box',
            'book'
        );
}

function comments_meta_box($post) {
?>
<input name="advanced_view" type="hidden" value="1" />
<p class="meta-options">
        <label for="comment_status" class="selectit">
            <input name="comment_status" type="checkbox" id="comment_status" 
                   value="open" <?php checked($post->comment_status, 'open'); ?> /> 
            <?php _e( 'Allow readers to add comments for book.' ) ?>
        </label>

         <input name="ping_status" type="hidden" id="ping_status" 
                    value="<?php echo $post->ping_status;?>" />

        <?php do_action('post_comment_status_meta_box-options', $post); ?>
</p>
<?php
}