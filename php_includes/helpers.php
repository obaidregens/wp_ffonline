<?php
function logging(...$datas) {
    foreach ($datas as $data ) {
        file_put_contents(MAIN_DIR . '/logging.txt', json_encode($data) . "\r\n",FILE_APPEND);
    }
    file_put_contents(MAIN_DIR . '/logging.txt', "\r\n",FILE_APPEND);
}
function timer($logtext,$echo = false){
    global $lastlogtime;
    $now = microtime(true);
    $diff = $now - ($lastlogtime ?? $now);
	file_put_contents(MAIN_DIR . '/content/time_logger.txt', $logtext . ": " . $diff . "\r\n", FILE_APPEND );
	if ($echo === true){
		echo $logtext . ": " . $diff . "<br>";
	}
    $lastlogtime = microtime(true);
}
class arr {
    public static function non_empty ($arr,$reindex = true){
        foreach($arr as $k => $v){
            if ($v === ''){
                unset($arr[$k]);
            }
        }
        return $reindex ? array_values($arr) : $arr;
    }
    static function remove(&$arr,$val,bool $strict = false,bool $reindex = true) {
        $i = array_search($val,$arr,$strict);
        if ($i === false){
            return;
        }
        unset($arr[$i]);
        return array_values($arr);
    }
}
class str {
    static function is_lower($txt) {
        return strtolower($txt) === $txt;
    }
}
function roundToNextHour($stamp) {
    $date = new DateTime( );
    $date->setTimestamp( intval($stamp) );

    $minutes = $date->format('i');
    if ($minutes > 0) {
        $date->modify("+1 hour");
        $date->modify('-'.$minutes.' minutes');
    }
    return intval($date->getTimestamp());
}
function DEV() {
    return defined('GLOBAL_ENV') && GLOBAL_ENV === 'DEV';
}
function STATIC_URL() {
    return defined("STATIC_URL") ? rtrim(STATIC_URL,'/') . '/' : "https://static.fanfiction.online/";
}
function is_test_story($id) {
    $a = get_post($id);
    if ($a->post_type === "chapter") {
        $a = get_post($a->post_parent);
    }
    $admin = intval(user::get_by('login','admin')->ID);
    return
        intval($a->post_author) === $admin &&
        $a->post_type === "book" &&
        $a->post_status === "draft" &&
        $a->post_title === "Test Story";
}