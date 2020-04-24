<?php
/**
 * The template for displaying the header
 *
 * Displays all of the head element and everything up until the "site-content" div.
 *
 * @package WordPress
 * @subpackage Twenty_Sixteen
 * @since Twenty Sixteen 1.0
 */
if (! headers_sent() && ! isset($_SESSION) ){
	session_start();
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<!--Let browser know website is optimized for mobile-->
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php meta_desc(); ?>
	<?php if ( is_singular() && pings_open( get_queried_object() ) ) : ?>
	<?php endif; ?>
    <link rel="dns-prefetch" href="//use.fontawesome.com">
	<?php wp_head(); ?>
      <!--Import materialize.css in functions.php-->
	  <script src='https://www.google.com/recaptcha/api.js' async defer></script>

	  <script>
		  function recaptchaOnload(){
			  if (document.getElementById('reCAPTCHA_div')){
				  grecaptcha.render("reCAPTCHA_div", {
					  sitekey: '6Lc_ROEUAAAAAE2WALbN67FKxK284OnW7jSxEBth',
				  });				  
			  }
		  }
	  </script>
</head>
	<style>
		#share_modal .share_title{
			margin-bottom:20px;
		}
		#share_modal .book_description{
			margin:0;
		}
		#share_modal .book_box{
			margin-bottom:15px;
		}
		.nav-content{
			display:block;
			text-align:right;
			width:auto;
			overflow:hidden;
		}
		.nav-content > li{
			display:inline-block;
			margin: 5px;
		}
		.nav-content > li > a{
			padding:10px;
		}
		.nav-logo{
			display:block;
			float:left;
			width:fit-content;
		}
		.home-desc{
			float:left;
			width:65%;
			max-width:750px;
			margin-right:5%;
		}
		.home-tags{
			width:auto;
			overflow:hidden;
		}
		.each-tag.icon-tag{
			margin-right:10px;
			color:var(--theme-color);
		}
		.each-tag.icon-tag i{
			margin-right:5px;
		}
		.mainsearch-item .book-options{
			position: absolute;
			top: 0;
			right: 0;
		}
		.mainsearch-item .book-options + ul{
			height: fit-content !important;
			width: fit-content !important;
		}
		.entry-title{
			font-size:24px !important;
		}
		.home-tags,.search-summary,.update-time{
			font-size:14px !important;
		}
		.entry-author{
			font-size: 13px !important;
		}
		.home-tags .each-tag:not(.icon-tag){
			display:block;
		}
		@media only screen and (max-width: 992px) {
			.home-desc{
				width:100%;
				margin-right:0%;
			}
			.home-tags{
				width:100%;
			}
			.home-tags .each-tag:not(.icon-tag){
				display:inline;
			}
			.nav-content {
				background-color:var(--background-color);
				height:40px;
				text-align:center;
				position:fixed;
				bottom:0;
				left:0;
				z-index:11;
				width:100%;
				box-shadow:rgb(220, 220, 220) 0px 2px 10px;
			}
			html{
				margin-bottom: 40px;
			}
			.nav-logo{
				width:100%;
				text-align:center;
			}
			.entry-title{
				font-size:20px !important;
			}
			.home-tags,.search-summary,.update-time{
				font-size:13px !important;
			}
			.entry-author{
				font-size: 12px !important;
			}
		}
		@media only screen and (max-width: 520px) {
			.entry-title{
				font-size:17px !important;
			}
			.home-tags,.search-summary,.update-time{
				font-size:12px !important;
			}
			.entry-author{
				font-size: 11px !important;
			}
		}
		@media only screen and (max-width: 380px) {
			.entry-title{
				font-size:14px !important;
			}
			.home-tags,.search-summary,.update-time{
				font-size:11.5px !important;
			}
			.entry-author{
				font-size: 10.5px !important;
			}
		}
		@media only screen and (max-width: 320px) {
			.entry-title{
				font-size:13px !important;
			}
			.home-tags,.search-summary,.update-time{
				font-size:10.0px !important;
			}
			.entry-author{
				font-size: 9.5px !important;
			}
		}
		.btn-floating{
			padding:0 !important;
		}

		/* Make the badge float in the top right corner of the button */
		.notification-badge {
			background-color: #fa3e3e;
			border-radius: 50%;
			color: white;
			line-height: 1;
			z-index:9;
			padding: 3px 5px; /* Add some padding so it looks nice */
			font-size: 10px;

			position: absolute; /* Position the badge within the relatively positioned button */
			top: 2px;
			right: 2px;
			user-select: none;
			-moz-user-select: none;
			-khtml-user-select: none;
			-webkit-user-select: none;
			-o-user-select: none;
		}
		.tabs,.site-header,.paginationm{
			user-select: none;
			-moz-user-select: none;
			-khtml-user-select: none;
			-webkit-user-select: none;
			-o-user-select: none;		
		}
		.notification-badge:empty{
			display:none;
		}

	</style>
<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
	<?php if (is_user_logged_in()){ ?>
	<ul style="user-select: none;-moz-user-select: none;-khtml-user-select: none;-webkit-user-select: none;-o-user-select: none;width:55%;" id="notification-sidenav" class="sidenav">
		<?php get_template_part('template-parts/header','notifications'); ?>
	</ul>
	<?php } ?>
<div id="page" class="site">
	<div class="site-inner">
		<a class="skip-link screen-reader-text" href="#content"><?php _e( 'Skip to content', 'twentysixteen' ); ?></a>
		<header id="masthead" class="site-header" role="banner">
			<div class="site-header-main">
				<nav class="nav-wrapper" style="width:100%">
					<div class="nav-logo">						
						<a href="/" ><?php include(explode('wp-content',__FILE__)[0] . 'wp-content/uploads/logo.svg'); ?></a>
					</div>
					<div class="nav-content">
						<li><a <?php if ($pagename == 'search' || $pagename == ''){echo 'class="pagenow active"';} ?> target="_self" href="/">Read</a></li>
						<li><a <?php if ($pagename == 'collection'){echo 'class="pagenow active"';} ?>  target="_self" href="/collection">Collections</a></li>
						<li><a <?php if ($pagename == 'dashboard'){echo 'class="pagenow active"';} ?> target="_self" href="/dashboard">Dashboard</a></li>
					    <?php if (is_user_logged_in()) { ?>
						<li style="position:relative;">
							<a data-target="notification-sidenav" class="sidenav-trigger btn-hover btn-floating"><i class="fas fa-bell"></i></a>
							<span class="pulse notification-badge"></span></li>
	                    <?php } ?>
					</div>
				</nav>				
			</div><!-- .site-header-main -->
			<?php if ( get_header_image() ) : ?>
				<?php
					/**
					 * Filter the default twentysixteen custom header sizes attribute.
					 *
					 * @since Twenty Sixteen 1.0
					 *
					 * @param string $custom_header_sizes sizes attribute
					 * for Custom Header. Default '(max-width: 709px) 85vw,
					 * (max-width: 909px) 81vw, (max-width: 1362px) 88vw, 1200px'.
					 */
					$custom_header_sizes = apply_filters( 'twentysixteen_custom_header_sizes', '(max-width: 709px) 85vw, (max-width: 909px) 81vw, (max-width: 1362px) 88vw, 1200px' );
				?>
				<div class="header-image">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
						<img src="<?php header_image(); ?>" srcset="<?php echo esc_attr( wp_get_attachment_image_srcset( get_custom_header()->attachment_id ) ); ?>" sizes="<?php echo esc_attr( $custom_header_sizes ); ?>" width="<?php echo esc_attr( get_custom_header()->width ); ?>" height="<?php echo esc_attr( get_custom_header()->height ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>">
					</a>
				</div><!-- .header-image -->
			<?php endif; // End header image check. ?>
		</header><!-- .site-header -->

		<div id="content" class="site-content">
