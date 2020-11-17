<?php
function global_bundle($name){
    $_bundle = new bundle($name);
    $_bundle->js("service");
    $_bundle->mix('idb');
    $_bundle->mix('global_new');
    $_bundle->mix('intro');
    return $_bundle;
}
class bundle {
    protected static $default_index = [
        'css'       => [],
        'js'        => [],
        'mix'       => []
    ];
    function __construct ($name) {
        // Statics
        $this->static_url       = self::$static_url;
        $this->static_dir       = self::$static_dir;
        $this->bundles_url      = $this->static_url . 'bundles/';
        $this->bundles_dir      = $this->static_dir . 'bundles/';

        $this->name = $name;
        if (! file_exists($this->bundles_dir)){
            mkdir($this->bundles_dir);
        }
        $this->bundle = self::$index[$this->name] ?? self::$default_index;
    }
    function clear() {
        $this->bundle = self::$default_index;
    }
    function css($file) {
        $type = "css";
        $i = array_search($file,$this->bundle[$type]);
        if ($i !== false){
            unset($this->bundle[$type][$i]);
            $this->bundle[$type] = array_values($this->bundle[$type]);
        }
        $this->bundle[$type][] = $file;
    }
    function js($file){
        $type = "js";
        $i = array_search($file,$this->bundle[$type]);
        if ($i !== false){
            unset($this->bundle[$type][$i]);
            $this->bundle[$type] = array_values($this->bundle[$type][$i]);
        }
        $this->bundle[$type][] = $file;
    }
    function mix($file){
        $type = "mix";
        $i = array_search($file,$this->bundle[$type]);
        if ($i !== false){
            unset($this->bundle[$type][$i]);
            $this->bundle[$type] = array_values($this->bundle[$type][$i]);
        }
        $this->bundle[$type][] = $file;
    }
    protected function hash($type) {
        return $this->name . "-" . $this->bundle[$type . "_hash"] . "." . $type;
    }
    protected function close() {
        self::$index[$this->name] = $this->bundle;
        if (DEV()) {
            file_put_contents($this->static_dir . '/index.json',json_encode(self::$index,JSON_PRETTY_PRINT));
        }
    }
    function print(){
        $this->close();
        $raw_urls = $this->get_raw_urls();
        $type = isset($this->script_type) ? 'type="' . $this->script_type . '"' : "";
        if (DEV()){
            foreach ($raw_urls['css'] as $i => $filename) {
                $name = 'bundle_' . $this->name . '_css_' . $i;
                $url_loc = substr($filename ,0,3) === '://' ? 'https' : $this->static_url;
                $url = $url_loc . $filename . '.css';
                ?><link rel="stylesheet" name="<?= $name; ?>" href="<?= $url; ?>"><?php
            }
            foreach ($raw_urls['js'] as $i => $filename) {
                $name = 'bundle_' . $this->name . '_js_' . $i;
                $url_loc = substr($filename ,0,3) === '://' ? 'https' : $this->static_url;
                $url = $url_loc . $filename . '.js';
                ?><script <?= $type; ?> name="<?= $name; ?>" src="<?= $url; ?>"></script><?php
            }
            return;
        }
        if (!empty($raw_urls['css'])){
            ?><link rel="stylesheet" name="<?= 'bundle_' . $this->name . '_css'; ?>" href="<?= $this->bundles_url . $this->hash('css');; ?>"><?php
        }
        if (!empty($raw_urls['js'])) {
            ?><script <?= $type; ?> name="<?= 'bundle_' . $this->name . '_js'; ?>" src="<?= $this->bundles_url . $this->hash('js'); ?>"></script><?php
        }
    }
    public function get_raw_urls(){
        return self::raw_urls($this->bundle);
    }
    protected static function raw_urls($bundle) {
        $raw_urls = [
            'css'   => [],
            'js'    => []
        ];
        foreach ($bundle['mix'] as $mix_name) {
            $raw_urls['css'] = array_merge($raw_urls['css'], self::$mix[$mix_name]['css'] ?? []);
            $raw_urls['js'] = array_merge($raw_urls['js'], self::$mix[$mix_name]['js'] ?? []);
        }
        $raw_urls['css'] = array_merge($raw_urls['css'],$bundle['css'] ?? []);
        $raw_urls['js'] = array_merge($raw_urls['js'],$bundle['js'] ?? []);
        return $raw_urls;
    }
    // Base
    protected static $static_dir;
    protected static $static_url;
    protected static $mix;
    public static $index;

    static function init () {
        self::$static_dir = MAIN_DIR . 'content/static/';
        self::$static_url = '/content/static/';
        $index_file = self::$static_dir . 'index.json';
        $mix_file = self::$static_dir . 'mix.json';
        if (! file_exists($index_file)){
            file_put_contents($index_file,json_encode([]));
        }
        if (! file_exists($mix_file)){
            file_put_contents($mix_file,json_encode([]));
        }
        self::$mix = json_decode(file_get_contents($mix_file),true);
        self::$index = json_decode(file_get_contents($index_file),true);
    }
}
bundle::init();