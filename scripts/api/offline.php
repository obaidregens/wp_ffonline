<?php
function api_offline() {
    required_params('action');
    $d = &$_POST['data'];
    $a = $d['action'];
    if (in_array($a,['borrow','return'])){
        required_params('book_id');
        $book = story::get($d['book_id'],false);
        if (!$book ) {
            return ['code'=>10];
        }
        $key = offline_stats::key($d['key'] ?? "");
        offline_stats::new([
            'type'          => $a,
            'key'           => $key,
            'landing_id'    => $_POST['landing_id'],
            'story_id'      => $book->ID,
            'stat_millitime'=> millitime(),
        ]);
        return ['code'=>1,'key'=> $key];
    } else if ($a === "renew") {
        required_params('key');
        $key = $d['key'];
        global $wpdb;
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM offline_stats WHERE `key` = %s ORDER BY stat_millitime ASC"
        ,[ $key ]));
        $ids = array_column($rows,'story_id');
        $rows = array_combine($ids,$rows);

        $stories = empty($ids) ? [] : $wpdb->get_results($wpdb->prepare(
            "SELECT stories.ID, stories.post_title as title, users.display_name as author, stories.post_status, stories.post_modified, COUNT(chapters.ID) as count
            FROM wp_posts as stories
            INNER JOIN wp_posts as chapters ON stories.ID = chapters.post_parent
            INNER JOIN wp_users as users ON stories.post_author = users.ID
            WHERE 1
            AND stories.post_type = 'book'
            AND chapters.post_type = 'chapter'
            AND stories.ID IN (" . sqlPlaceholder($ids,"%d") . ")
            GROUP BY chapters.post_parent"
        ,$ids),OBJECT_K);

        $re = [
            'code'      => 2,
            'key'       => $key,
            'update'    => [],
            'current'   => [],
            'delete'    => [],
        ];
        foreach ($rows as $story_id => $row) {
            if (!in_array($row->type,["borrow","update"])) {
                continue;
            }
            $story = $stories[$story_id] ?? null;
            if ( $story === null || $story->post_status !== "publish" ) {
                $re['delete'][] = [
                    'id'        => $story_id
                ];
            } else if ( strtotime($story->post_modified) >= (intval($row->stat_millitime)/1000) ) {
                $re['update'][] = [
                    'id'        => $story_id,
                    'chapters'  => intval($story->count)
                ];
            } else {
                $re['current'][] = [
                    'id'        => $story_id,
                    'chapters'  => intval($story->count),
                    "title"     => $story->title,
                    "author"    => $story->author
                ];
            }
        }
        $_SESSION["offline_renewal"] = $re;
        return $re;
    } else if ($a === "confirm") {
        required_params('key');
        $re = &$_SESSION["offline_renewal"];
        if ($re['key'] !== $d['key']) {
            return ['code'=>10];
        }
        if ( empty(array_merge(
            $re['delete'],
            $re['update']
        )) ) {
            return ['code'=>11];
        }
        $db = new db('offline_stats',[
            'type',
            'story_id',
            'chapter_id'        => 0,
            'chapter_num'       => 0,
            'added_millitime'   => millitime(),
            'stat_millitime'    => millitime(),
            'landing_id'        => $_POST['landing_id'],
            'key'               => $re['key'],
        ]);
        foreach (['update','delete'] as $a) {
            foreach ($re[$a] as $story ) {
                $db->insert([
                    'type'      => $a,
                    'story_id'  => $story['id'],
                ]);
            }
        }
        $e = $db->close();
        return ['code'=>3];
    }
    return ['code'=>9];
}