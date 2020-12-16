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
    function filled_string($name,$v) {
        return is_string($v) && trim($v) !== "" ? $this : $this->add($name,"$name can't be empty");
    }
    function is_required($params,$args){
        foreach ($params as $param) {
            if (!isset($args[$param])) {
                $this->add($param,"$param is required");
            }
        }
        return $this;
    }
    function one_of($name,$var,$from) {
        return in_array($var,$from) ? $this : $this->add($name,"$name must be one of " . implode(',',$from));
    }
    function numeric($name,$var) {
        return is_numeric($var) ? $this : $this->add($name,"$name must be numeric");
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
    function array() {
        return array_column($this->errors,'error');
    }
    function k_array() {
        return array_column($this->errors,'error','name');
    }
    function j_array() {
        $errs = [];
        foreach ($this->errors as $err) {
            $errs[] = $err["name"] . " - " . $err["error"];
        }
        return $errs;
    }
}
