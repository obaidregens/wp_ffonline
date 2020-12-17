<?php
class cache {
    protected static $current = null;
    function set($key,$value) {
        $this->{$key} = $value;
    }
    function ref($key,$ref) {
        $this->{$key} = &$this->{$ref};
    }
    function get($key) {
        return $this->{$key} ?? null;
    }
    function delete ($key) {
        $this->{$key} = null;
    }
    static function now() {
        if (self::$current === null) {
            self::$current = new cache;
        }
        return self::$current;
    }
}
class dcache {
    protected static $current = null;
    function __construct() {
        $this->dir = MAIN_DIR . '/cached/';
        mkdir ( $this->dir , 0777 , true );
    }
    function set($key,$value,$expire_in_hour = 1) {
        // Overriding Value for memory 
        $value = [
            'cached'    => time(),
            'expire_in' => $expire_in_hour*60*60,
            'value'     => $value
        ];
        file_put_contents($this->dir . "c-$key.cache",serialize($value));
    }
    function get($key) {
        ob_start();
        $data = file_get_contents($this->dir . "c-$key.cache");
        ob_end_clean();
        if ($data === false) {
            return null;
        }
        $value = unserialize($data);
        $data = null;
        if ( ($value['cached'] + $value['expire_in']) - time() <= 0 ) {
            return null;
        }
        return $value['value'];
    }
    static function now() {
        if (self::$current === null) {
            self::$current = new dcache;
        }
        return self::$current;
    }
}