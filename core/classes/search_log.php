<?php
class search_log {
    static $table = "search_log";
    static $param_table = "search_param_log";

    protected static function add_param(&$prep,$key,$key_spec,$value) {
        $prep[] = $key;
        $prep[] = $key_spec;
        $prep[] = $value;
        return "(@last_id_for_search,%s,%s,%s)";
    }
    function __construct($core_args) {
        $prep = [get_current_user_id(),landing_id(),millitime()];

        $table = self::$table;
        $param_table = self::$param_table;
        
        $psql = "
        INSERT INTO $table
        (`user_id`, `landing_id`, `millitime`)
        VALUES (%s, %s, %s);
        SET @last_id_for_search = LAST_INSERT_ID();";
        $vals = [];
        foreach ($core_args as $k => $value) {
            if ($k === "words") {
                foreach ($value as $spec => $count) {
                    $vals[] = self::add_param($prep,'words',$spec,$count);
                }
            }
            else if (in_array($k,['included','excluded'])) {
                foreach ($value as $key => $valss) {
                    foreach ($valss as $val ) {
                        $vals[] = self::add_param($prep,$key,$k,$val);
                    }
                }
            }
            else if ($k === "order") {}
            else if ($k === "orderby") {
                $vals[] = self::add_param($prep,"order",$core_args["order"] ?? "DESC",$value);
            }
            else {
                $vals[] = self::add_param($prep,$k,"",$value);
            }
        }

        global $wpdb;
        if (!empty($vals)) {
            $psql .= "
            INSERT INTO $param_table
            (`search_id`,`key`,`key_spec`,`value`)
            VALUES
            " . implode(", 
            ",$vals);
        }
        $psql .= ";";
        $sql = $wpdb->prepare($psql,$prep);

        if (count($prep) > 3) {
            $mysqli = &$wpdb->dbh;

            if ( $mysqli->multi_query( $sql ) ) {
                do {
                    if ($result = $mysqli->store_result()) {
                        $result->free_result();
                    }
                }
                while ($mysqli->next_result());
            }    
        }
    }
}