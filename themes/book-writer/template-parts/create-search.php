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

global $book_query;
$book_query = $query;

$collections = collection::query(array(
    'authors'  => array(get_current_user_id()),
));

?>
<collections_data hidden><?= json_encode($collections); ?></collections_data>
<tags_data hidden><?= json_encode(tags_data($query)); ?></tags_data>
<prev_ss hidden><?= ctrk_encrypt($query->args); ?></prev_ss>

<filter-books>
    <button class="next-screen">
        Filters
    </button>
    <next-screen>
        <div>
            <text-input label="Search"></text-input>
            <select>
                <option value="updated/DESC">Last Updated</option>
                <option value="date/DESC">Book Published</option>
                <option value="favorites/DESC">Favorites</option>
                <option value="words/DESC">Words</option>
            </select>
            <label label="Sort"></label>
            <select-tag label="Fandom" name="fandom"></select-tag>
            <select-tag label="Rating" name="rating"></select-tag>
            <select-tag label="Language" name="language"></select-tag>
            <select-tag label="Status" name="Status"></select-tag>
            <select-tag label="Genre" name="genre"></select-tag>
            <select-tag label="Characters" name="character"></select-tag>
            <select-tag label="Pairings" name="pairing"></select-tag>
            <select-tag label="Tags" name="tag"></select-tag>
            <words-slider></words-slider>
            <button label="Reset"></button>
            <button label="Search" theme></button>
            <button class="close_next-screen" label="Close"></button>
        </div>
    </next-screen>
</filter-books>

<loader xl></loader>

<books-container>
<book_collections hidden><?= json_encode(collection::query_by_book(array_column($book_query->books,'ID'),'ID')); ?></book_collections>
<?php
if ( $book_query->has() ){
    global $book;
    foreach ($book_query->books as $book) {
        get_template_part( 'template-parts/content' , 'search' );
    }
}
else {
    get_template_part( 'template-parts/content', 'noresult' );
}
?>
</books-container>
<pagination>
<?php get_template_part( 'template-parts/content', 'bookpaginate' ); ?>
</pagination>
<?php
global $bundle;
$bundle = new bundle('create_search_custom');
$bundle->mix('jquery');
$bundle->mix('global_new');
$bundle->mix('create_search_new');
$bundle->enqueue();