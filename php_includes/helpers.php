<?php
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
}