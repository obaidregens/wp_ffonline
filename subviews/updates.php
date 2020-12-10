<?php
$user = $app->user;
$href = rtrim(get_author_posts_url($user->ID),'/') . '/';
$is_current_author = intval(get_current_user_id()) === intval($user->ID);
$app->updates_count = ($app->updates_count ?? 3) === -1 ? pow(9,10) : intval($app->updates_count ?? 3);
$updates_query = new updates ([
    'author'    => $user->ID,
    'count'     => $app->updates_count
]);
$updates = $updates_query->updates;
?>
<?php if (! empty($updates) || $is_current_author){ ?>
    <author-updates <?= empty($updates) ? 'empty' : '' ?>>
        <?php foreach ($updates as $update ) { ?>
            <update class="divider" <?= is_sticky( $update->ID ) ? 'pinned' : '' ?> update_id="<?= $update->ID; ?>">
                <update-time><?= get_the_time( 'G', $update ); ?> ago</update-time>
                <?php if ($is_current_author) { ?>
                    <button class="dropdown">
                        <dropdown class="right">
                            <li tabindex="0" label="Pin"></li>
                            <li tabindex="0" label="Delete"></li>
                        </dropdown>
                    </button>
                <?php } ?>
                <content><?= !in_array($user->user_login,["ffonline",'admin']) ? htmlspecialchars($update->post_content) : strip_tags($update->post_content,'<p><span><a><ul><li><ol><h1><h2><h3><h4><h5><h6><strong>'); ?></content>
            </update>
        <?php } ?>
        <?php if ($updates_query->total > $app->updates_count ){ ?>
            <a href="<?= $href; ?>updates" class="button more"></a>
        <?php } ?>
        <?php if ($is_current_author){ ?>
            <button class="add-update"></button>
        <?php } ?>
    </author-updates>
<?php } ?>