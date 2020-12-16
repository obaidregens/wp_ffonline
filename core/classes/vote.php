<?php
class vote {
    public static $table = 'votes';
    protected static $types = ['chapter'];
    static function new($args) {
        $e = new err;
        $e->is_required([
            'type',
            'type_id',
            'landing_id'
        ],$args);
        $e->one_of('type',$args['type'],self::$types);
        $e->numeric('type_id',$args['type_id']);
        $e->numeric('landing_id',$args['landing_id']);
        $e->numeric('user_id',$args['user_id'] ?? 0);
        if ($e->has()){
            return $e;
        }
        $args = array_replace([
            'user_id'       => get_current_user_id()
        ],$args);
        $args['voted_time'] = microtime(true);
        ob_start();
        global $wpdb;
        $wpdb->insert(
            self::$table,
            $args
        );
        $err = ob_get_contents();
        ob_end_clean();
        if ($err === ""){
            $inst = new notifications_insert;
            $inst->vote($args['type_id'],$args['user_id']);
        }
        return true;
    }
    static function unvote ($type, $type_id, $user = null) {
        $user = $user === null ? get_current_user_id() : $user;
        global $wpdb;
        $wpdb->delete(
            self::$table,
            [
                'type'            => $type,
                'type_id'         => $type_id,
                'user_id'         => $user
            ]
        );
    }
    static function exists($type, $type_id, $user = null) {
        $user = $user === null ? get_current_user_id() : $user;
        $table = self::$table;
        global $wpdb;
        $sql = $wpdb->prepare("SELECT * FROM $table WHERE type = %s AND type_id = %d AND user_id = %d",[$type,$type_id,$user]);
        $r = $wpdb->get_results($sql);
        return empty($r) ? false : intval($r[0]->voted_time);
    }
    static function query_by($type, $field, $value) {
        $e = new err();
        global $wpdb;
        if ($type === 'story' && $field = 'type_id') {
            $sql = 
            "SELECT votes.type,votes.type_id,votes.user_id,votes.landing_id,voted_time
            FROM votes
            INNER JOIN wp_posts ON votes.type_id = wp_posts.ID
            WHERE votes.type = 'chapter'
            AND wp_posts.post_parent = %d
            AND wp_posts.post_type = 'chapter'
            AND wp_posts.post_status = 'publish'";
            return $wpdb->get_results($wpdb->prepare($sql,[$value]));
        }
        $e->one_of('$type',$type,self::$types ?? []);
        $e->one_of('$field',$field,['type_id','user_id']);
        if ($e->has()) {
            return $e;
        }
        $table = self::$table;
        $sql = $wpdb->prepare("SELECT * FROM $table WHERE $field = %s",[$value]);
        $results = $wpdb->get_results($sql);

        return $results;
    }
    static function all_votes($user) {
        $sql =
        "
        SELECT votes.type,votes.type_id,votes.user_id,votes.landing_id,voted_time
        FROM votes
        INNER JOIN wp_posts ON votes.type_id = wp_posts.ID
        WHERE votes.type = 'chapter'
        AND wp_posts.post_author = %d
        AND wp_posts.post_type = 'chapter'
        AND wp_posts.post_status = 'publish'
        ";
        global $wpdb;
        return ($wpdb->get_results($wpdb->prepare($sql,[$user])));

    }
}