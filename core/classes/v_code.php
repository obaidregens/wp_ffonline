<?php

class v_code {
    public static $table = 'verification_codes';
    protected static $hours = 0.5;
    function __construct(){
        $code = bin2hex(random_bytes(4));
        global $wpdb;
        $wpdb->insert(
            self::$table,
            array(
                'code'      => $code,
                'issued'    => time()
            )
        );
        $this->code = $code;
        $this->ID = $wpdb->insert_id;
    }
    public static function is($ID,$code){
        $table = self::$table;
        global $wpdb;
        $sql = $wpdb->prepare(
            "SELECT * FROM $table WHERE ID = %d AND code = %s",
            array(intval($ID),$code)
        );
        $results = $wpdb->get_results($sql);
        if (empty($results) || (time() - intval($results[0]->issued)) > 60*60 * self::$hours){
            return false;
        }
        return true;
    }
    public static function delete($ID){
        global $wpdb;
        $wpdb->delete(
            self::$table,
            array(
                'ID'    => intval($ID)
            )
        );
    }
}