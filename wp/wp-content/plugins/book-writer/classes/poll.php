<?php
class poll {
    protected static $table = 'polls';
    protected static $vote_table = 'poll_votes';
    protected static $options_table = 'poll_options';
    static function new($args) {
        $e = new err;
        $args = array_replace([
            'user_id'       => get_current_user_id(),
            'description'   => "",
            'status'        => "publish",
            "expire_in"     => 3*24*60*60*1000, // 3 days
            'options'       => []
        ],$args);
        $e->filled_string('description',$args['description']);
        $e->one_of('status',$args['status'],['publish']);
        $e->numeric('user_id',$args['user_id']);
        $e->numeric('expire_in',$args['expire_in']);
        if (count($args['options']) < 2){
            $e->add('options',"Poll must have at least two options.");
        }
        if ($e->has()){
            return $e;
        }
        $options = $args['options'];
        unset($args['options']);
        $args['deleted_milli'] = 0;
        $args['created_milli'] = millitime();
        global $wpdb;
        $wpdb->insert(
            self::$table,
            $args
        );
        $id = intval($wpdb->insert_id);
        foreach ($options as $option ) {
            self::insert_option($id,$option);
        }
        return $id;
    }
    protected static function insert_option($id,$option) {
        global $wpdb;
        $wpdb->insert(
            self::$options_table,
            [
                'poll_id'   => $id,
                'title'     => $option
            ]
        );
    }
    static function delete($poll_id) {
        global $wpdb;
        $wpdb->update(
            self::$table,
            [
                'status'        => 'deleted',
                'deleted_milli' => millitime()
            ],
            ['ID' => $poll_id]
        );
    }
    static function vote($poll_id,$option_id,$user = null) {
        $user = $user === null ? get_current_user_id() : $user;
        global $wpdb;
        $wpdb->insert(
            self::$vote_table,
            [
                'poll_id'           => $poll_id,
                'option_id'         => $option_id,
                'user_id'           => $user,
                'voted_millitime'   => millitime()
            ]
        );
    }
    static function query($args) {
        $args = array_replace([
            'users'         => [get_current_user_id()],
            'status'        => ['publish'],
            'show_expired'  => true
        ],$args);
        $table = self::$table;
        global $wpdb;
        $sql = "SELECT * FROM $table WHERE 1";
        $prep = [];
        if (!$args['show_expired']){
            $sql.= " AND SUM(created_milli+expire_in) < %d";
            $prep[] = millitime();
        }
        if ( empty($args['users']) || empty($args['status'])) {
            return [];
        }
        $sql .= " AND status IN(" . sqlPlaceholder($args['status']) . ")";
        $prep = array_merge($prep,$arg['status']);

        $sql .= " AND user_id IN(" . sqlPlaceholder($args['users']) . ")";
        $prep = array_merge($prep,$arg['users'],'%d');

        $r = $wpdb->get_results($wpdb->prepare($sql,$prep));
        return $r;
    }
    static function get($poll_id) {
        $sql =
        "SELECT * FROM polls
        INNER JOIN poll_options ON poll_options.poll_id = polls.ID
        WHERE polls.ID = %d
        ORDER BY poll_options.ID ASC";
        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare($sql,[$poll_id]));
        if (empty($results)){
            return false;
        }
        $r = $results[0];
        $r->options = array_column($results,'title','ID');
        return $r;
    }
    static function results($poll_id) {
        global $wpdb;
        $table = self::$table;
        $poll_options = array_column($wpdb->get_results($wpdb->prepare(
            "SELECT * FROM poll_options WHERE poll_id = %d",[$poll_id]
        )),'title','ID');
        $poll_votes = array_column($wpdb->get_results($wpdb->prepare(
            "SELECT * FROM poll_votes WHERE poll_id = %d",[$poll_id]
        )),'option_id','user_id');
        $option_votes = array_count_values($poll_votes);
        $total_votes = count($poll_votes);
        $has_voted = is_user_logged_in() && isset($poll_votes[strval(get_current_user_id())]);
        $options = [];
        foreach ($poll_options as $id => $title) {
            $c = $option_votes[$id] ?? 0;
            $options[] = [
                'ID'            => $id,
                'title'         => $title,
                'votes'         => $has_voted ? intval( ($c/$total_votes) * 100) : null,
            ];
        }
        $poll_data = self::get($poll_id);
        $milli = millitime();
        $expiry = intval($poll_data->created_milli) + intval($poll_data->expire_in);
        $expire_in = $expiry - $milli;
        return [
            'poll_data'     => [
                'ID'            => $poll_data->ID,
                'user_id'       => $poll_data->user_id,
                'description'   => $poll_data->description,
                'expire_in'     => $expire_in < 0 ? null : human_time_diff(0,$expire_in/1000) . ' left',
            ],
            'count'             => $total_votes,
            'options'           => $options,
            'has_voted'         => $has_voted,
        ];
    }
}