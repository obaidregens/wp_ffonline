<?php
class err {
    public static function is($var){
        if ($var instanceof err) {
            return true;
        }
        return false;
    }
    function __construct(){
        $this->errors = [];
    }
    function add($name,$error){
        $build_error = array(
            'name'      => $name,
            'error'     => $error,
            'stack'     => debug_backtrace()
        );
        if (! in_array($build_error,$this->errors)){
            $this->errors[] = $build_error;
        }
        return $this;
    }
    function has(){
        if (empty($this->errors)){
            return false;
        }
        return true;
    }
    function merge($error_obj){
        if (err::is($error_obj)){
            $this->errors = array_merge($this->errors,$error_obj->errors);
        }
        return $this->errors;
    }
}
