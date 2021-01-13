<?php
// Find Args from Url
global $book_query;
$book_query = $app->book_query;
$a = $book_query->args['included']['fandom'] ?? [];
$terms = empty($a) ? [] : get_terms([
    'taxonomy'  => 'category',
    'include'   => $book_query->args['included']['fandom']
]);
$fandoms = [];
foreach( $terms as $term ) {
    if (intval($term->parent) === 0) {
        continue;
    }
    $fandoms[] = $term->name;
}
$fandoms = implode('/',$fandoms);
?>
<prev_ss hidden><?= ctrk_encrypt($book_query->args); ?></prev_ss>

<loader xl></loader>

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
                <option value="top/DESC">Top</option>
                <option value="votes/DESC">Votes</option>
                <option value="words/DESC">Words</option>
                <option value="date/DESC">Story Published</option>
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
    <fandom-filter>
    <showing class="<?= $book_query->is_default ? "" : "show" ?>">
        <fandom><?= empty($fandoms) ? "All" : $fandoms ?></fandom>
        <span> stories by</span>
        <select>
            <option value="updated/DESC">Last Updated</option>
            <option value="top/DESC">Top</option>
            <option value="votes/DESC">Votes</option>
            <option value="words/DESC">Words</option>
            <option value="date/DESC">Story Published</option>
        </select>
    </showing>
    <input type="text" placeholder="Filter by fandom">
    </fandom-filter>
</filter-books>
<books-container page="<?=$book_query->page;?>" pages="<?=$book_query->pages;?>">
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
<?php
if (! isset($app->bundle)){
    $app->bundle = global_bundle('search');
}
$app->bundle->mix('glide_js');
$app->bundle->mix('confirmation');
$app->bundle->mix('story-options');
$app->bundle->mix('autocomplete');
$app->bundle->css('css/components/loader');
$app->bundle->css('css/components/tooltips');
$app->bundle->css('css/js-components/checkbox');
$app->bundle->js("js/components/checkbox");
$app->bundle->js("external/noUiSlider/nouislider");
$app->bundle->css('external/noUiSlider/nouislider');
$app->bundle->css('css/views/search-tags');
$app->bundle->css('css/views/search-content');
$app->bundle->js("js/views/search-content");
$app->bundle->css('css/views/search-filters');
$app->bundle->js("js/views/search-filters");
$app->bundle->js("js/views/search-offline");
$app->bundle->css('css/views/search-filterFandom');
$app->bundle->js("js/views/search-filterFandom");
$app->bundle->js("js/views/search-autoload");