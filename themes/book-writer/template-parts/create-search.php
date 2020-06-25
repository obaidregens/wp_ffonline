<?php
// Find Args from Url
$query = new book_query;
$query->args_from_url();
$placeholder = _landing::get_type();
$query->args = type_args($query->args,$placeholder);
if (err::is($query->args)){
    _404();
}
$query->query();
?>
<span id="tags_data" style="display:none;"><?= json_encode(tags_data($query)); ?></span>
<span id="prev_ss" style="display:none;"><?= ctrk_encrypt($query->args); ?></span>
<style>
    #box{
        position: relative;
    }
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
    [type=checkbox]+span:not(.lever):before{
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
global $book_query;
$book_query = $query;
?>
<div class="filters-button-wrapper mobile-margin"><button class="waves-effect waves-light btn-small" onclick="search_settings('open')">Filters</button></div>
<div class="row" id="search-display"><div id="box" class="col s12">
	<?php
	if ( $book_query->has() ){
        get_template_part('template-parts/modal','collection');
        global $book;
        foreach ($book_query->books as $book) {
            get_template_part( 'template-parts/content' , 'search' );
        }
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
get_template_part( 'template-parts/content', 'searchmodal' );
global $js_bundle;
$js_bundle = global_bundle('create_search');
$js_bundle->add('create_search');
$js_bundle->add('book-options');
$js_bundle->enqueue();