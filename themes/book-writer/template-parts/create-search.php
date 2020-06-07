<?php
//Find Args from Url
$args = unpack_search($_GET);

$placeholder = _landing::get_type();
$args = type_args($args,$placeholder);
if (err::is($args)){
    _404();
}
//Max Count
$max_count = max_search_words($args);
?>
<span id="prev_ss" style="display:none;"><?= ctrk_encrypt($args); ?></span>
<span id="max_count" style="display:none;"><?= $max_count; ?></span>
<style>
    [type=checkbox].cross:checked+span:not(.lever):before{
        top: -3px;
        font-size: 22px;
        left: 2px;
        color: red;
        font-family: "Font Awesome 5 Free";
        font-weight: 900;
        content: "\f00d";
        border: none !important;
        transform: rotate(0deg) !important;
    }
    .btn-include,.btn-exclude{
        background-color:transparent;
        color: var(--text-color);
    }
    .btn-include.active,.btn-include:hover,.btn-include:focus{
        background-color:var(--theme-color);
        color:white;
    }
    .btn-exclude.active,.btn-exclude:hover,.btn-exclude:focus{
        background-color:#f44336;
        color:white;
    }
    .btn-clear:hover{
        color:var(--background-color);
        background-color:var(--text-color);
    }
    .btn-clear:focus{
        background-color:transparent;
        color:var(--text-color);
    }
    .included_chips .chip{
        background-color:#f0f8ff;
    }
    .excluded_chips .chip{
        background-color:#fcd7d7;
    }
    .all_label div {
        font-weight: 400;
        padding-bottom: 10px;
        padding-left: 8px;
        border-bottom: 1px solid #9e9e9e;
        font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",font-family;
    }
    [type=checkbox]:checked+span:not(.lever):before{
        transition:none!important;
    }
	.noUi-tooltip span {
		width: fit-content;
		text-align: center;
		color: var(--text-color);
		font-size: 12px;
		opacity: 0;
		position: absolute;
		top: 40px;
		left: -50px;
		transition: opacity .25s cubic-bezier(.215,.61,.355,1);
    }
    .filters-button-wrapper{
        text-align:right;
        margin-bottom: 50px;
        margin-top:20px;
    }
</style>
<?php
$default_query = new WP_Query( $args );
$original_query = $wp_query;
$wp_query = null;
$wp_query = $default_query;
?>
<div class="filters-button-wrapper mobile-margin"><button class="waves-effect waves-light btn-small" onclick="search_settings('open')">Filters</button></div>
<div class="row" id="search-display"><div id="box" class="col s12">
	<?php
	if (have_posts()){
        get_template_part('template-parts/modal','collection');
		// Start the loop.
		while (have_posts() ) :
			the_post();
			get_template_part( 'template-parts/content', 'search' );
			
			// End the loop.
		endwhile;
		
	}
	else {
		get_template_part( 'template-parts/content', 'noresult' );
	}
	?>
</div></div>
<div id="pagination-wrapper">
<?php get_template_part( 'template-parts/content', 'bookpaginate' ); ?>
</div>
<?php
//Reset Data
//This is because we dont want our meddling of the $wp-query to affect the whole site
$wp_query = null;
$wp_query = $original_query;
wp_reset_postdata();

get_template_part( 'template-parts/content', 'searchmodal' );

global $js_bundle;
$js_bundle = global_bundle('create_search');
$js_bundle->add('create_search');
$js_bundle->add('book-options');
$js_bundle->enqueue();
