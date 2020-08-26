<?php
define('WP_USE_THEMES', false);
$maindir = rtrim(explode('content',__DIR__,2)[0],'/\\') . '/';
$wp_dir = $maindir . '/content/wp/';
require( $wp_dir . 'wp-load.php');

function pull_from_autosave_db() {
    $maindir = rtrim(explode('content',__DIR__,2)[0],'/\\') . '/';
    global $wpdb;
    $inserts_as_revisions = [];
    $sql = $wpdb->prepare("SELECT * FROM drafts WHERE branch_type = 'autosave' AND branch != 0 ORDER BY updated ASC");
    $autosaves = $wpdb->get_results($sql);
    foreach ($autosaves as $as) {
        $inserts_as_revisions[] = [
            'autoSaveID'=> $as->ID,
            'user_id'   => $as->user_id,
            'draft_id'  => $as->branch,
            'content'   => $as->content,
            'hash'      => sha1($as->content),
            'edited'    => $as->updated
        ];
    }
    file_put_contents($maindir . '/migraterData',json_encode($inserts_as_revisions));
}
function push_as_reviews_to_db() {
    $maindir = rtrim(explode('content',__DIR__,2)[0],'/\\') . '/';
    $raw = file_get_contents($maindir . '/migraterData');
    $reviews = json_decode($raw,true);
    global $wpdb;
    foreach ($reviews as $review ) {
        unset($review['autoSaveID']);
        $wpdb->insert(
            'draft_revisions',
            $review
        );
    }
    echo 'Completed';
}
function delete_autosaves_pushed_from_db() {
    global $wpdb;
    $wpdb->delete(
        'drafts',
        [
            'branch_type'   => 'autosave'
        ]
    );
}
function switch_from_updated_to_created() {
    global $wpdb;
    $wpdb->query("UPDATE drafts SET created=updated");
}
function add_current_draft_content_as_last_review() {
    global $wpdb;
    $sql = $wpdb->prepare("SELECT * FROM drafts WHERE branch_type IS NULL");
    $drafts = $wpdb->get_results($sql);
    foreach ($drafts as $draft ) {
        $wpdb->insert(
            'draft_revisions',
            [
                'user_id'   => $draft->user_id,
                'draft_id'  => $draft->ID,
                'content'   => $draft->content,
                'hash'      => sha1($draft->content),
                'edited'    => $draft->updated
            ]
        );
    }
}
add_current_draft_content_as_last_review();