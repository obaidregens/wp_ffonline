<?php
$app->bundle = global_bundle('feed-home');
$app->bundle->mix('autocomplete');
$app->bundle->mix('story-options');
$app->bundle->mix('search-content');
$app->bundle->css('css/js-components/checkbox');
$app->bundle->js('js/views/feed-autoload');
$app->bundle->js('js/views/feed-form');
$app->bundle->css('css/views/feed-form');
$app->bundle->css('css/views/feed-home');
$app->bundle->js('js/views/feed-home');

global $book_query;
$book_query = feed::get("reading");
?>
<feed-form>
    <div class="fandom">
        <p>Select all the fandoms you want to be recommended stories of.</p>
        <select-tag></select-tag>
        <text-input label="Search for fandom"></text-input>
        <div class="select-list"></div>
    </div>
    <div class="rating">
        <p>Select which ratings the stories can be of.</p>
        <select-tag></select-tag>
        <div class="select-list"></div>
    </div>
    <div class="language">
        <p>Which languages do you read in?.</p>
        <select-tag></select-tag>
        <div class="select-list"></div>
    </div>
    <button label="Save"></button>
</feed-form>
<cancel></cancel>

<ul class="tab">
<li class="waves-hover active">Reading</li>
<li class="waves-hover">Feed</li>
</ul>
<books-container feed_type="reading" page="<?=$book_query->page ?? 1;?>" pages="<?=$book_query->pages ?? 1;?>">
<?php
if ( $book_query->has() ){
    global $book;
    foreach ($book_query->books as $book) {
        $app->template( '/subviews/story-single' );
    }
}
else {
    $app->template('/subviews/reading-empty');
}
?>
</books-container>
