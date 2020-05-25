<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');

if (! current_user_can( 'administrator' )){
    exit();
}


$vfs = $_POST['vfs'];
global $wpdb;
$table_name = 'custom_stats';
$result = $wpdb->get_results ( "
    SELECT * FROM $table_name
    WHERE cookie_id = '$vfs'
    ORDER BY timestamp ASC
" );
$zero_result = array(
    'stat'      => $result[0]->stat,
    'type'      => $result[0]->type,
    'link'      => '<a href="' . _link($result[0]->type_id,$result[0]->type) . '">' . _title($result[0]->type_id,$result[0]->type) . '</a>',
    'timestamp' => $result[0]->timestamp
);
$return = [
    [//One for each session
        [//One for each type
            $zero_result
        ]
    ]
];
foreach ($result as $key => $stat) {
    if ($key <= 0){
        continue;
    }
    $new_stat = array(
        'stat'      => $stat->stat,
        'type'      => $stat->type,
        'link'      => '<a href="' . _link($stat->type_id,$stat->type) . '">' . _title($stat->type_id,$stat->type) . '</a>',
        'timestamp' => $stat->timestamp
    );
    $prev_stat = $result[$key-1];
    if (
        ($new_stat['timestamp'] - $prev_stat->timestamp) > 1800
    ){
        $return[] = array(
            array(
                $new_stat
            )
        );
    }
    else if (
        $new_stat['stat'] == $prev_stat->stat &&
        $new_stat['type'] == $prev_stat->type
    ){
        $return[count($return)-1][count($return[count($return)-1])-1][] = $new_stat;
    }
    else{
        $return[count($return)-1][] = array(
            $new_stat
        );
    }
}
echo json_encode($return);
exit();