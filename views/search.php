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
<script>
    window.collections_data = <?= script_json(json_encode(collection_helpers::js_data())); ?>;
    window.tags_data = <?= script_json(json_encode(tags_data($query))); ?>;
    window.book_collections = <?= script_json(json_encode(collection_helpers::query_by_book(array_column($book_query->books,'ID')))); ?>;
</script>
<prev_ss hidden><?= ctrk_encrypt($query->args); ?></prev_ss>

<filter-books>
    <button class="next-screen">
        Filters
    </button>
    <next-screen>
        <div>
            <text-input label="Search"></text-input>
            <text-input label="Search by author"></text-input>
            <select>
                <option value="updated/DESC">Last Updated</option>
                <option value="date/DESC">Story Published</option>
                <option value="words/DESC">Words</option>
                <option value="votes/DESC">Votes</option>
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

<books-container>
<?php
if ( $book_query->has() ){
    global $book;
    foreach ($book_query->books as $book) {
        $app->template( '/subviews/story-single' );
    }
}
else {
    $app->template('/subviews/no-books');
}
?>
</books-container>
<pagination>
<?php $app->template( '/subviews/paginate-stories' ); ?>
</pagination>
<?php
if (! isset($app->bundle)){
    $app->bundle = global_bundle('search');
}
$app->bundle->mix('glide_js');
$app->bundle->mix('confirmation');
$app->bundle->css('css/components/loader');
$app->bundle->css('css/components/select');
$app->bundle->css('css/components/tooltips');
$app->bundle->css('css/js-components/checkbox');
$app->bundle->js("js/components/checkbox");
$app->bundle->css('css/js-components/switch');
$app->bundle->js("js/components/switch");
$app->bundle->js("external/noUiSlider/nouislider");
$app->bundle->css('external/noUiSlider/nouislider');
$app->bundle->css('css/views/search-tags');
$app->bundle->css('css/views/search-content');
$app->bundle->js("js/views/search-content");
$app->bundle->css('css/views/search-filters');
$app->bundle->js("js/views/search-filters");
$app->bundle->css('css/views/search-options');
$app->bundle->js("js/views/search-options");
$app->bundle->css('css/views/search-updateCollection');
$app->bundle->js("js/views/search-updateCollection");
$app->bundle->js('js/views/story-offlineAPI');
$app->bundle->js("js/views/search-offline");
$app->bundle->enqueue();