<?php
class book_stats extends stats {
    function __construct($book_id,$da = false) {
        if ($da === false) {
            global $wpdb;
            // Landings
            $landing_table = self::$landing_table;
            $landing_query = $wpdb->prepare("SELECT * FROM $landing_table WHERE type = %s AND type_id = %s ORDER BY ID DESC",['story',$book_id]);
            $landings = $wpdb->get_results($landing_query,ARRAY_A);
            if (empty($landings)) {
                $this->by_vfs = [];
                $this->landings = []; 
                return;
            }
    
            $landing_ids = array_column($landings,'ID');
    
            // Actions
            $actions_table = self::$actions_table;
            $actions = $wpdb->get_results("SELECT * FROM stats_actions WHERE landing_id IN(" . implode(',',$landing_ids) . ")",ARRAY_A);
            $gactions = [];
            foreach ($actions as $action ) {
                $landing_id = $action['landing_id'];
                if (! isset($gactions[$landing_id])){
                    $gactions[$landing_id] = [];
                }
                $action['timestamp'] = (int) $action['timestamp'];
                $gactions[$landing_id][] = $action;
            }    
        }
        else {
            $landings = $da['landings'];
            $gactions = $da['gactions'];
        }

        $real_landings = [];
        $by_vfs = [];
        foreach ($landings as $key => $landing ) {
            if ( $da !== false && intval($landing['type_id']) !== intval($book_id) ) {
                continue;
            }
            $landing_id = $landing['ID'];
            if (! isset($gactions[$landing_id])){
                continue;
            }
            $landing['actions'] = $gactions[$landing_id];
            $real_landings[$landing_id] = $landing;
            // VFS
            $vfs = $landing['vfs'];
            if (! isset($by_vfs[$vfs])){
                $by_vfs[$vfs] = [];
            }
            $by_vfs[$vfs][] = $landing;
        }
        $this->by_vfs = $by_vfs;
        $this->landings = $real_landings; 
    }
    function views($from = 'all') {
        if (!$from instanceof DateTime && $from !== 'all') {
            return false;
        }
        $t = $from === 'all' ? 0 : $from->getTimestamp();
        if ($from === 'all' ) {
            return ($this->landings);
        }
        $filtered = [];
        foreach ($this->landings as $landing_id => $landing) {
            if ($landing['timestamp'] < $t) {
            break;
            }
            $filtered[$landing_id] = $landing;
        }
        return ($filtered);
    }
    function view_count($from = 'all'){
        $r = $this->views($from);
        return $r === false ? false : count($r);
    }
    static function multiple($book_ids) {
        global $wpdb;
        // Landings
        $landing_table = self::$landing_table;
        $landing_query = $wpdb->prepare("SELECT * FROM $landing_table WHERE type = 'story' AND type_id IN(" . implode(',',array_fill(0,count($book_ids),'%s')) . ") ORDER BY ID DESC",$book_ids);
        $landings = $wpdb->get_results($landing_query,ARRAY_A);
        if (empty($landings)) {
            $instances = [];
            foreach ( $book_ids as $book_id ) {
                $instances[$book_id] = new book_stats($book_id, [
                    'landings'  => [],
                    'gactions'  => [],
                ]);
            }
            return $instances;
        }

        $landing_ids = array_column($landings,'ID');

        // Actions
        $actions_table = self::$actions_table;
        $actions = $wpdb->get_results("SELECT * FROM stats_actions WHERE landing_id IN(" . implode(',',$landing_ids) . ")",ARRAY_A);
        $gactions = [];
        foreach ($actions as $action ) {
            $landing_id = $action['landing_id'];
            if (! isset($gactions[$landing_id])){
                $gactions[$landing_id] = [];
            }
            $action['timestamp'] = (int) $action['timestamp'];
            $gactions[$landing_id][] = $action;
        }    

        $instances = [];
        foreach ( $book_ids as $book_id ) {
            $instances[$book_id] = new book_stats($book_id, [
                'landings'  => $landings,
                'gactions'  => $gactions,
            ]);
        }
        return $instances;
    }
}