<?php
function global_bundle($name){
    $_js_bundle = new js_bundle($name);
    $_js_bundle->add('jquery');
    $_js_bundle->add('materialize/extras/nouislider',true);
    $_js_bundle->add('materialize/js/materialize.min',true);
    $_js_bundle->add('helpers');
    $_js_bundle->add('global');
    $_js_bundle->add('login');
    $_js_bundle->add('survey');
    return $_js_bundle;
}
class js_bundle {
    public static function reWrite($name){
        $_js_bundle = new js_bundle($name);
        $_js_bundle->write();
    }
    public static function reWriteAll() {
        $bundles_dir = explode('wp-content',__FILE__)[0] . 'wp-content/themes/book-writer/js/bundles';
        $files = scandir($bundles_dir);
        foreach($files as $file){
            if ($file == 'index.idn'){
                continue;
            }
            $name = str_replace('.js','',$file);
            js_bundle::reWrite($name);
        }
    }
    function __construct($name){
        $this->name = $name;
        $this->theme_url = get_stylesheet_directory_uri();
        $this->theme_dir = explode('wp-content',__FILE__)[0] . 'wp-content/themes/book-writer';
        $this->js_dir = $this->theme_dir . '/js';
        $this->bundles_dir =  $this->js_dir . '/bundles';
        if (! file_exists($this->js_dir)){
            mkdir($this->js_dir);
        }
        if (! file_exists($this->bundles_dir)){
            mkdir($this->bundles_dir);
        }
        $index_file = $this->bundles_dir . '/index.idn';
        if (! file_exists($index_file)){
            file_put_contents($index_file,json_encode(
                array()
            ));
        }
        $this->bundle_exists();
    }
    function enqueue($mode = 'production'){
        if (! isset($this->file) || ! $this->file || $this->index[$this->name] !== $this->bundle){
            $this->write();
        }
        if ($mode == 'dev'){
            $enqueued = [];
            foreach ($this->bundle as $key => $filename) {
                wp_enqueue_script( 'bundle_' . $this->name . '_' . $key, $this->theme_url . '/' . $filename . '.js',$enqueued,null,true);
                $enqueued[] = 'bundle_' . $this->name . '_' . $key;
            }
            return;
        }
        wp_enqueue_script( 'bundle_' . $this->name,$this->file,array(),null,true);
    }
    function bundle_exists(){
        $this->index = json_decode(file_get_contents($this->bundles_dir . '/index.idn'),true);
        if (! isset($this->index[$this->name])){
            return false;
        }
        $this->bundle = $this->index[$this->name];
        $this->file = $this->theme_url . '/js/bundles/' . $this->name . '.js';
        return true;
    }
    function add($file,$full = false){
        if (! isset($this->bundle)){
            $this->bundle = array();
        }
        if ($full == false){
            $file = 'js/' . $file;
        }
        if (! in_array($file,$this->bundle)){
            $this->bundle[] = $file;
        }
        return;
    }
    function clear(){
        $this->bundles = array();
    }
    function write(){
        
        if (! isset($this->bundle) || empty($this->bundle)){
            return false;
        }
        $total_js = '';
        foreach ($this->bundle as $file) {
            $total_js .= file_get_contents($this->theme_dir . '/' .  $file . '.js');
            $total_js .= "\n\r";
        }
        file_put_contents($this->theme_dir . '/total_js.error',$total_js);
        $url = 'https://javascript-minifier.com/raw';
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ["Content-Type: application/x-www-form-urlencoded"],
            CURLOPT_POSTFIELDS => http_build_query([ "input" => $total_js ]),
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        $minified = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        file_put_contents ($this->bundles_dir . '/' . $this->name . '.js',$minified);
        
        $this->index[$this->name] = $this->bundle;
        file_put_contents($this->bundles_dir . '/index.idn',json_encode($this->index));
        $this->file = $this->theme_url . '/js/bundles/' . $this->name . '.js';
    }
}