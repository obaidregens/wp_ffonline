<?php
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