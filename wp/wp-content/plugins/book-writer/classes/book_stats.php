<?php
class book_stats extends stats {
    function __construct($book_id) {
        global $wpdb;
        // Landings
        $landing_table = self::$landing_table;
        $landing_query = $wpdb->prepare("SELECT * FROM $landing_table WHERE type = %s AND type_id = %s ORDER BY ID DESC",['book',$book_id]);
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

        $real_landings = [];
        $by_vfs = [];
        foreach ($landings as $key => $landing ) {
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
}