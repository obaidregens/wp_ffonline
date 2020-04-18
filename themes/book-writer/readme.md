Important Notes

When Updating Wordpress
1) Replace wp-admin/user-edit.php's contents with
/////////////////////////////////////////// SNIPPET ////////////////////////////////////////////////////////////
    <?php
    
    /**
     * Edit user administration panel.
     *
     * @package WordPress
     * @subpackage Administration
     */
    
    /** WordPress Administration Bootstrap */
    require_once( dirname( __FILE__ ) . '/admin.php' );
    
    wp_reset_vars( array( 'action', 'user_id', 'wp_http_referer' ) );
    
    //Include Custom file with edits
    include('/home3/eshaatco/public_html/fic/wp-content/themes/book-writer/main-edits/user-edit.php');
/////////////////////////////////////////// END-SNIPPET ////////////////////////////////////////////////////////

1) Replace wp-admin/comment.php's contents with
/////////////////////////////////////////// SNIPPET ////////////////////////////////////////////////////////////
/**
 * Comment Management Screen
 *
 * @package WordPress
 * @subpackage Administration
 */

/** Load WordPress Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

$parent_file  = 'edit-comments.php';
$submenu_file = 'edit-comments.php';

//Include Custom file with edits
include('/home3/eshaatco/public_html/fic/wp-content/themes/book-writer/main-edits/comment.php');

/////////////////////////////////////////// END-SNIPPET ////////////////////////////////////////////////////////