<?php
// Find Args from Url
$query = new book_query;
$query->args_from_url();
$placeholder = _landing::get_type();
$query->args = type_args($query->args,$placeholder);
if (err::is($query->args)){
    $app->_404();
}
$query->query();
global $book_query;
$book_query = $query;
?>
<collections_data hidden><?= json_encode(collection_helpers::js_data()) ?></collections_data>
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
                <option value="date/DESC">Story Published</option>
                <option value="words/DESC">Words</option>
            </select>
            <label label="Sort"></label>
            <select-tag label="Fandom" name="fandom"></select-tag>
            <select-tag label="Rating" name="rating"></select-tag>
            <select-tag label="Language" name="language"></select-tag>
            <select-tag label="Status" name="status"></select-tag>
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

<books-container class="grid">
<book_collections hidden><?= json_encode(collection_helpers::query_by_book(array_column($book_query->books,'ID'))); ?></book_collections>
<?php
if ( $book_query->has() ){
    global $book;
    foreach ($book_query->books as $book) {
        get_template_part( 'template-parts/content' , 'search' );
    }
}
else {
    $app->template('/subviews/no-books');
}
?>
</books-container>
<pagination>
<?php get_template_part( 'template-parts/content', 'bookpaginate' ); ?>
</pagination>
<?php
if (! isset($app->bundle)){
    $app->bundle = new bundle('search');
}
$app->bundle->mix('jquery');
$app->bundle->mix('global_new');
$app->bundle->mix('intro');
$app->bundle->mix('glide_js');
$app->bundle->mix('create_search_new');
$app->bundle->enqueue();