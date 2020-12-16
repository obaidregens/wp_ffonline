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