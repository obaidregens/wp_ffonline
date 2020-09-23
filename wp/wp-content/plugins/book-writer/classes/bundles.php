<?php
function global_bundle($name){
    $_bundle = new bundle($name);
    $_bundle->mix('jquery');
    $_bundle->mix('global_new');
    $_bundle->mix('intro');
    return $_bundle;
}
function curl_minify($post,$url){
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ["Content-Type: application/x-www-form-urlencoded"],
        CURLOPT_POSTFIELDS => http_build_query([ "input" => $post ]),
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    $minified = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    return $minified;
}
class bundle {
    public static $version = "29";
    public static function reset() {
        $static_dir = explode('wp',__FILE__,2)[0] . 'static/';
        $bundles_dir = $static_dir . 'bundles/';
        $f - scandir($bundles_dir);
        $t = time() - 10000;
        foreach ($dirs as $b) {
            if (in_array($b,['.','..'])){continue;}
            touch(MAIN_DIR . 'content/static/bundles/' . $b,$t);
        }
    }
    public static function reWrite() {
        $static_dir = explode('wp',__FILE__,2)[0] . 'static/';
        $bundles_dir = $static_dir . 'bundles/';
        $index = json_decode(file_get_contents($bundles_dir . 'index.idn'),true);
        foreach ($index as $bundle_name => $bundle) {
            $bundle_wo_suffix = $bundles_dir . $bundle_name;
            $last_edited = max(array(filemtime($bundle_wo_suffix . '.css'),filemtime($bundle_wo_suffix . '.js')));
            $raw_files = self::raw_urls($bundle);
            foreach ($raw_files as $extension => $files) {
                foreach ($files as $file) {
                    if (substr ($file ,0,3) === '://'){
                        continue;
                    }
                    $file_full = $static_dir . $file . '.' . $extension;
                    if ( filemtime($file_full) >= $last_edited ) {
                        $bundle = new bundle($bundle_name);
                        $bundle->write();
                        break 2;
                    }
                }
            }
        }
    }
    protected static $default_index = array(
        'css'       => array(),
        'js'        => array(),
        'mix'       => array()
    );
    function __construct ($name) {
        // Statics
        $this->static_url     = '/content/static/';
        $this->static_dir     = explode('wp',__FILE__,2)[0] . 'static/';
        $this->bundles_url   = $this->static_url . 'bundles/';
        $this->bundles_dir   = $this->static_dir . 'bundles/';
        $this->index_file    = $this->bundles_dir . 'index.idn';

        $this->name = $name;
        if (! file_exists($this->bundles_dir)){
            mkdir($this->bundles_dir);
        }
        if (! file_exists($this->index_file)){
            file_put_contents($this->index_file,json_encode(
                array()
            ));
        }
        $this->get_bundle();
    }
    function clear(){
        $this->bundle = self::$default_index;
    }
    function css($file) {
        if (in_array($file,$this->bundle['css'])){
            return;
        }
        $this->bundle['css'][] = $file;
    }
    function js($file,$type = null){
        if (in_array($file,$this->bundle['js'])){
            return;
        }
        $this->bundle['js'][] = $file;
    }
    function mix($mix){
        if (in_array( $mix, $this->bundle['mix'] )){
            return;
        }
        $this->bundle['mix'][] = $mix;
    }
    function write(){
        $raw_urls = $this->get_raw_urls();
        $total_css = '';
        foreach ($raw_urls['css'] as $file) {
            if (substr ($file ,0,3) === '://'){
                $total_css .= file_get_contents('https' . $file . '.css');
            }
            else {
                $total_css .= file_get_contents($this->static_dir .  $file . '.css');
            }
            $total_css .= "\r\n";
        }
        $minified_css = curl_minify( $total_css, 'https://cssminifier.com/raw' );

        $total_js = '';
        foreach ($raw_urls['js'] as $file) {
            if (substr ($file ,0,3) === '://'){
                $total_js .= file_get_contents('https' . $file . '.js');
            }
            else {
                $total_js .= file_get_contents($this->static_dir .  $file . '.js');
            }
            $total_js .= "\r\n";
        }
        $minified_js = curl_minify( $total_js, 'https://javascript-minifier.com/raw' );

        file_put_contents ($this->bundles_dir . $this->name . '.css', $minified_css);
        file_put_contents ($this->bundles_dir . $this->name . '.js', $minified_js);
        
        $this->index[$this->name] = $this->bundle;
        file_put_contents($this->bundles_dir . 'index.idn', json_encode($this->index) );
        $this->css_file = $this->bundles_url . $this->name . '.css';
        $this->js_file = $this->bundles_url . $this->name . '.js';
    }
    function enqueue($mode = 'production'){
        if (
            ! isset($this->css_file) || ! isset($this->css_file) ||
            ! $this->css_file || ! $this->js_file ||
            ! isset($this->index[$this->name]) ||
            $this->index[$this->name] !== $this->bundle
        ){
            $this->write();
        }
        if ($mode === 'dev'){
            $raw_urls = $this->get_raw_urls();
            $this->enqueued = [];
            foreach ($raw_urls['css'] as $key => $filename) {
                $queue_name = 'bundle_' . $this->name . '_css_' . $key;
                $url_loc = substr($filename ,0,3) === '://' ? 'https' : $this->static_url;
                $this->enqueued_css[$queue_name] = $url_loc . $filename . '.css';
            }
            $this->enqueued = [];
            foreach ($raw_urls['js'] as $key => $filename) {
                $queue_name = 'bundle_' . $this->name . '_js_' . $key;
                $url_loc = substr($filename ,0,3) === '://' ? 'https' : $this->static_url;
                $this->enqueued_js[$queue_name] = $url_loc . $filename . '.js';
            }
            return;
        }
        $this->enqueued_css = [
            'bundle_' . $this->name . '_css'    => $this->css_file,
        ];
        $this->enqueued_js = [
            'bundle_' . $this->name . '_js'     => $this->js_file
        ];
    }
    function print(){
        $type = isset($this->script_type) ? 'type="' . $this->script_type . '"' : "";
        $addon = (self::$version ?? null) === null ? "" : '?v=' . self::$version;
        foreach ($this->enqueued_css ?? [] as $name => $url) {
            ?><link rel="stylesheet" name="<?= $name; ?>" href="<?= $url . $addon; ?>"><?php
        }
        foreach ($this->enqueued_js ?? [] as $name => $url) {
            ?><script <?= $type; ?> name="<?= $name; ?>" src="<?= $url . $addon; ?>"></script><?php
        }
    }
    protected function get_bundle(){
        $this->index = json_decode(file_get_contents($this->index_file),true);
        $this->bundle = $this->index[$this->name] ?? self::$default_index;
        $this->css_file = $this->bundles_url . $this->name . '.css';
        $this->js_file = $this->bundles_url . $this->name . '.js';
        return true;
    }
    public function get_raw_urls(){
        return self::raw_urls($this->bundle);
    }
    protected static function raw_urls($bundle) {
        $raw_urls = array(
            'css'   => array(),
            'js'    => array()
        );
        foreach ($bundle['mix'] as $mix_name) {
            $raw_urls['css'] = array_merge($raw_urls['css'], self::$mix[$mix_name]['css']);
            $raw_urls['js'] = array_merge($raw_urls['js'], self::$mix[$mix_name]['js']);
        }
        $raw_urls['css'] = array_merge($raw_urls['css'],$bundle['css']);
        $raw_urls['js'] = array_merge($raw_urls['js'],$bundle['js']);
        return $raw_urls;
    }
    protected static $mix = [
        'jquery'    => array(
            'css'   => array(),
            'js'    => array(
                'external/jquery/jquery',
            )
        ),
        'materialize' => array(
            'css'   => array(
                'external/materialize/extras/nouislider',
                'external/materialize/materialize.min',
            ),
            'js'    => array(
                'external/materialize/extras/nouislider',
                'external/materialize/materialize.min',
            )
        ),
        'global' => array(
            'js'    => array(
                'js/normalize',
                'js/_helpers',
                'js/classes',
                'js/helpers',
                'js/global',
                "js/components/recaptcha",
                "js/components/text-input",
                "js/components/toast",
                "js/components/popup",
                "js/views/global-login",
            ),
            'css'   => array(
                'style',
                'css/style',
                'css/resolve',
                'css/fonts',
                'css/js-components/recaptcha',
                'css/components/buttons',
                'css/js-components/text-input',
                'css/js-components/toast',
                'css/js-components/popup',
                'css/views/global-login',
            )
        ),
        'dashboard' => array(
            'js'    => array(
                'js/draggable.bundle',
                'js/dashboard',
                'js/components/inputs',
            ),
            'css'   => array(
                'css/dashboard'
            )
        ),
        'global_new'    => array(
            'css'   => array(
                'css/normalize',
                'css/style',
                'css/fonts',
                'css/helpers',
                'css/js-components/recaptcha',
                'css/components/buttons',
                'css/components/dropdown',
                'css/js-components/text-input',
                'css/js-components/toast',
                'css/js-components/popup',
                'css/js-components/next-screen',
                "css/views/global-login",
                "css/views/global-share",
                "external/waves/waves",
            ),
            'js'    => array(
                'js/normalize',
                'js/_helpers',
                'js/helpers',
                'js/classes',
                'js/global',
                "js/components/recaptcha",
                "js/components/text-input",
                "js/components/toast",
                "js/components/popup",
                'js/components/next-screen',
                'js/views/global-login',
                'js/views/global-share',
                "external/waves/waves",
            ),
        ),
        'create_search_new' => array(
            'css'   => array(
                'css/components/grid',
                'css/components/loader',
                'css/components/select',
                'css/components/tooltips',
                'css/js-components/checkbox',
                'css/js-components/switch',
                'css/views/search-tags',
                'css/views/search-content',
                'css/views/search-filters',
                'css/views/search-options',
                'external/noUiSlider/nouislider',
                'css/views/search-updateCollection'      
            ),
            'js'    => array(
                "js/components/checkbox",
                "js/components/switch",
                "external/noUiSlider/nouislider",
                "js/views/search-filters",
                "js/views/search-content",
                "js/views/search-options",
                "js/views/search-updateCollection"
            ),
        ),
        'search-content'    => array(
            'css'   => array(
                'css/components/tooltips',
                'css/components/grid',
                'css/views/search-tags',
                'css/views/search-content',
            ),
            'js'    => array(
                "js/views/search-content",
            ),
        ),
        'search-filters'    => array(
            'css'   => array(
                'css/views/search-filters',
            ),
            'js'    => array(
                "js/views/search-filters",
            ),
        ),
        'search-options'    => array(
            'css'   => array(
                'css/components/select',
                'css/js-components/switch',
                'css/views/search-options',
                'css/views/search-updateCollection'
            ),
            'js'    => array(
                "js/components/switch",
                "js/views/search-options",
                "js/views/search-updateCollection"
            ),
        ),
        'glide_js'      => array(
            'css'   => array(
                'external/glide/glide.core',
            ),
            'js'    => array(
                'external/glide/glide'
            )
        ),
        'react'   => array(
            'js'   => array(
                'external/react/react.production.min',
                'external/react/react-dom.production.min'
            ),
            'css'   => []
        ),
        'react_dev' => [
            'js'    => array(
                'external/react_dev/babel.min'
            ),
            'css' => []
        ],
        'slate'    => [
            'js'    => [
                'external/slate/slate',
                'external/slate/slate-react',
                'external/slate/slate-history',
            ],
            'css'   => []
        ],
        'intro'    => [
            'js'    => [
                'external/intro-js/intro.min',
                'js/views/global-intro'
            ],
            'css'   => [
                'external/intro-js/introjs.min',
                'css/views/global-intro'
            ]
        ]
    ];
}