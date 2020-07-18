<?php
/* Template Name: Dashboard */ 
admin_only();
global $bundle;
$bundle = global_bundle('manage');
$bundle->js('js/manage');
$bundle->css('css/components/index');
$bundle->enqueue();
get_header();

global $wpdb;
$old_landings = $wpdb->get_results("SELECT * FROM stats_landings WHERE referrer_host IS NOT NULL AND referrer_host != 'local' ORDER BY ID ASC",ARRAY_A);
$actions = $wpdb->get_results("SELECT * FROM stats_actions WHERE landing_id IN(" . implode(',',array_keys($old_landings)) . ")",ARRAY_A);
$gactions = [];
foreach ($actions as $action ) {
    $landing_id = $action['landing_id'];
    if (! isset($gactions[$landing_id])){
        $gactions[$landing_id] = [];
    }
    $action['timestamp'] = (int) $action['timestamp'];
    $gactions[$landing_id][] = $action;
}
$stat = [];
$session_start = $old_landings[0]['timestamp'];
foreach ($old_landings as $key => $landing ) {
    $landing_id = $landing['ID'];
    $vfs = $landing['vfs'];
    if (! isset($gactions[$landing_id])){
        continue;
    }
    $landing['actions'] = $gactions[$landing_id];
    // Now for Data from $landing
    $start_timestamp = $landing['timestamp'];
    if ($key !== 0 && $start_timestamp - $end_timestamp >= 30*60){
        $stat[$landing['referrer_host']][] = array(
            'timespan'      => $end_timestamp - $session_start,
            'vfs'           => $old_landings[$key-1]['vfs'],
            'user_id'       => $old_landings[$key-1]['user_id']
        );
        $session_start = $start_timestamp;
    }
    $action_stamps = array_column($landing['actions'],'timestamp');
    rsort($action_stamps);
    $end_timestamp = $action_stamps[0];
}
$to_echo = [];
foreach ($stat as $referrer => $visits) {
    $timestamps = array_column($visits,'timespan');
    $timespan = array_sum($timestamps)/count($timestamps);
    $to_echo[$referrer] = human_time_diff(0,$timespan);
}
?>
<h1>Average time by referrer.</h1>
<index>
    <li>
        <cell>Referrer</cell>
        <cell>Average Time</cell>
    </li>
    <?php foreach( $to_echo as $first => $second) { ?>
    <li>
        <cell><?= $first; ?></cell>
        <cell><?= $second; ?></cell>
    </li>
    <?php } ?>
</index>
<?php
get_footer( );
