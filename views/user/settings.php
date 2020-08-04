<?php
$app->template('/views/user/header');
$app->bundle->css('/css/components/collapsible');
$app->bundle->js('/js/components/switch');
$app->bundle->css('/css/js-components/switch');
$app->bundle->css('/css/views/author-settings');
$app->bundle->js('/js/views/author-settings');
$app->bundle->enqueue();
$ffn_user = c_user::current();
$books = (new WP_Query([
    'post_type'              => array( 'book' ),
    'post_status'            => array( 'publish','draft' ),
    'posts_per_page'		 => -1,
    'author__in'             => [get_current_user_id()]
]))->posts;
?>
<h2 label="Account"></h2>
<input class="collapsible" name="settings" type="radio">
<label>Change Password</label>
<collapsible>
    <text-input input_type="password" label="Password"></text-input>
    <text-input input_type="password" label="Confirm Password"></text-input>
    <button label="Change"></button>
</collapsible>
<?php if ($ffn_user !== false) { ?>
<h2 label="Books"></h2>
<input class="collapsible" name="settings" type="radio">
<label>Hide</label>
<collapsible>
<?php foreach($books as $book) { $status = $book->post_status === 'publish' ? '' : 'checked'; ?>
    <switch <?= $status; ?> book_id="<?= $book->ID; ?>" label="<?= $book->post_title; ?>"></switch>
<?php } if (empty($books)) { ?>
    No Books
<?php } ?>
</collapsible>
<?php } //FFN USER ?>