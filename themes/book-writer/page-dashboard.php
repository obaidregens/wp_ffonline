<?php
/* Template Name: Dashboard */ 
?>
<?php
//page_header
login_only();
wp_enqueue_style( 'dashboard_css', get_stylesheet_directory_uri() .'/css/dashboard.css');
get_header();
?>
<?php $books = get_pages(array(
	'authors'		=> get_current_user_id(),
	'post_type'		=> 'book',
	'sort_column'	=> 'post_modified',
	'post_status'	=> array('publish','draft'),
	'sort_order'	=> 'DESC'
));?>
    <ul id="dashboard-sidenav" class="sidenav sidenav-fixed">
        <li><a class="subheader"></a></li>
        <li class="dashboard_li selected"><a onclick="load_page('dashboard')"><i style="margin:0;" class="fas fa-tachometer-alt"></i>Dashboard</a></li>
        <li class="edit-book_li edit-chapter_li write_li"><a onclick="load_page('write')"><i style="margin:0;" class="fas fa-pencil-alt"></i>Write</a></li>
        <li class="profile_li"><a onclick="load_page('profile')"><i style="margin:0;" class="fas fa-user"></i>Profile</a></li>
        <?php if (!empty($books)){
            ?><li class="stats_li"><a onclick="load_page('stats')"><i style="margin:0;" class="fas fa-chart-line"></i>Book Stats</a></li><?php
        }
        ?>
        <li class="messages_li chat_li"><a onclick="load_page('messages')"><i style="margin:0;" class="fas fa-envelope"></i>Messages</a></li>
        <li class="bookmarks_li"><a onclick="load_page('bookmarks')"><i style="margin:0;" class="fas fa-bookmark"></i>Bookmarks</a></li>
        <li class="collections_li"><a onclick="load_page('collections')"><i style="margin:0;" class="fas fa-list-ul"></i>Collections</a></li>
        <li class="settings_li"><a onclick="load_page('settings')"><i style="margin:0;" class="fas fa-cog"></i>Settings</a></li>
        <li class="logout_li"><a href="/dashboard?logged_out=true" onclick="return confirm('Do you want to logout?')">Logout</a></li>
    </ul>
    <a href="#" data-target="dashboard-sidenav" class="sidenav-trigger"><i class="material-icons">menu</i></a>
    <div id="page-main"><?php get_template_part('dashboard/part','dashboard');?></div>
<?php get_footer(); ?>
