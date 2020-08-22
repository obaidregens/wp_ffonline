<?php
$user = $app->user;
$href = rtrim(get_author_posts_url($user->ID),'/') . '/';
$is_current_author = intval(get_current_user_id()) === intval($user->ID);
$updates_query = new WP_Query(array(
    'post_type'         => array( 'post' ),
    'orderby'           => 'modified',
    'posts_per_page'    => $app->updates_count,
    'order'             => 'DESC',
    'author'            => $user->ID
));
$updates_count = intval($updates_query->found_posts);
$updates = $updates_query->posts;
?>
<?php if (! empty($updates) || $is_current_author){ ?>
    <author-updates <?= empty($updates) ? 'empty' : '' ?>>
        <?php foreach ($updates as $update ) { ?>
            <update class="divider" <?= is_sticky( $update->ID ) ? 'pinned' : '' ?> update_id="<?= $update->ID; ?>">
                <update-time><?= get_the_time( 'G', $update ); ?> ago</update-time>
                <?php if ($is_current_author) { ?>
                    <button class="dropdown">
                        <dropdown class="right">
                            <li label="Pin"></li>
                            <li label="Delete"></li>
                        </dropdown>
                    </button>
                <?php } ?>
                <content>
                    <?= $update->post_content; ?>
                </content>
            </update>
        <?php } ?>
        <?php if ($updates_count > ($app->updates_count === -1 ? pow(9,10) : $app->updates_count) ){ ?>
            <a href="<?= $href; ?>updates" class="button more"></a>
        <?php } ?>
        <?php if ($is_current_author){ ?>
            <button class="add-update"></button>
        <?php } ?>
    </author-updates>
<?php } ?>