<?php

function get_surveys($args = array()){

    $base_sql = "SELECT * FROM surveys WHERE 1";
    $prepare = array();
    if (isset($args['ids'])){
        $base_sql .= ' AND ID IN (%s)';
        $args['ids'] = (array) $args['ids'];
        $prepare[] = implode(', ',$args['ids']);
    }
    if (isset($args['user_ids'])){
        $base_sql .= ' AND user_id IN (%s)';
        $args['user_ids'] = (array) $args['user_ids'];
        $prepare[] = implode(', ',$args['user_ids']);
    }
    if (isset($args['vfs'])){
        $base_sql .= ' AND vfs IN (%s)';
        $args['vfs'] = (array) $args['vfs'];
        $prepare[] = implode(', ', $args['vfs']);
    }
    if (isset($args['time'])){
        $base_sql .= ' AND timestamp >= %d AND timestamp <= %d';
        $prepare[] = intval($args['time']['from']) ? intval($args['time']['from']) : 0;
        $prepare[] = intval($args['time']['to']) ? intval($args['time']['to']) : current_time('timestamp',true);
    }
    if (isset($args['suggestion'])){
        $base_sql .= ' AND suggestion LIKE %s';
        $args['suggestion'] = (string) $args['suggestion'];
        $prepare[] = '%' . $args['suggestion'] . '%';
    }
    if (isset($args['email'])){
        $base_sql .= ' AND email LIKE %s';
        $args['email'] = (string) $args['email'];
        $prepare[] = '%' . $args['email'] . '%';
    }
    if (isset($args['rating'])){
        $base_sql .= ' AND rating >= %d AND rating <= %d';
        $prepare[] = intval($args['rating']['from']) ? intval($args['rating']['from']) : 0;
        $prepare[] = intval($args['rating']['to']) ? intval($args['rating']['to']) : 100;
    }

    global $wpdb;
    $sql = $wpdb->prepare( $base_sql, $prepare );
    $results = $wpdb->get_results( $sql ,ARRAY_A );
    return $results;

}