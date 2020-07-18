<?php
function global_bundle($name){
    $_bundle = new bundle($name);
    $_bundle->mix('jquery');
    $_bundle->mix('materialize');
    $_bundle->mix('global');
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
    public static function reWrite() {
        $theme_dir = explode('wp-content',__FILE__)[0] . 'wp-content/themes/book-writer/';
        $bundles_dir = $theme_dir . 'bundles/';
        $index = json_decode(file_get_contents($bundles_dir . 'index.idn'),true);
        foreach ($index as $bundle_name => $bundle) {
            $bundle_wo_suffix = $bundles_dir . $bundle_name;
            $last_edited = max(array(filemtime($bundle_wo_suffix . '.css'),filemtime($bundle_wo_suffix . '.js')));
            $raw_files = self::raw_urls($bundle);
            foreach ($raw_files as $extension => $files) {
                foreach ($files as $file) {
                    $file_full = $theme_dir . $file . '.' . $extension;
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
        $this->theme_url     = get_stylesheet_directory_uri() . '/';
        $this->theme_dir     = explode('wp-content',__FILE__)[0] . 'wp-content/themes/book-writer/';
        $this->bundles_url   = $this->theme_url . 'bundles/';
        $this->bundles_dir   = $this->theme_dir . 'bundles/';
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
    function js($file){
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
    function link($mix){
        if (in_array( $mix, $this->bundle['mix'] )){
            return;
        }
        $this->bundle['mix'][] = $mix;
    }
    function write(){
        $raw_urls = $this->get_raw_urls();
        $total_css = '';
        foreach ($raw_urls['css'] as $file) {
            $total_css .= file_get_contents($this->theme_dir .  $file . '.css');
            $total_css .= "\r\n";
        }
        $minified_css = curl_minify( $total_css, 'https://cssminifier.com/raw' );

        $total_js = '';
        foreach ($raw_urls['js'] as $file) {
            $total_js .= file_get_contents($this->theme_dir .  $file . '.js');
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
            $enqueued = [];
            foreach ($raw_urls['css'] as $key => $filename) {
                $queue_name = 'bundle_' . $this->name . '_css_' . $key;
                wp_enqueue_style( $queue_name, $this->theme_url . $filename . '.css', $enqueued, null );
                $enqueued[] = $queue_name;
            }
            $enqueued = [];
            foreach ($raw_urls['js'] as $key => $filename) {
                $queue_name = 'bundle_' . $this->name . '_js_' . $key;
                wp_enqueue_script( $queue_name, $this->theme_url . $filename . '.js',$enqueued, null, true);
                $enqueued[] = $queue_name;
            }
            return;
        }
        wp_enqueue_style  ( 'bundle_' . $this->name . '_css', $this->css_file, array(), null      );
        wp_enqueue_script ( 'bundle_' . $this->name . '_js' , $this->js_file , array(), null, true);
    }
    protected function get_bundle(){
        $this->index = json_decode(file_get_contents($this->index_file),true);
        $this->bundle = $this->index[$this->name] ?? self::$default_index;
        $this->css_file = $this->bundles_url . $this->name . '.css';
        $this->js_file = $this->bundles_url . $this->name . '.js';
        return true;
    }
    protected function get_raw_urls(){
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
                'js/jquery',
            )
        ),
        'materialize' => array(
            'css'   => array(
                'materialize/extras/nouislider',
                'materialize/css/materialize-input.min',
            ),
            'js'    => array(
                'materialize/extras/nouislider',
                'materialize/js/materialize.min',
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
                'css/js-components/text-input',
                'css/js-components/toast',
                'css/js-components/popup',
                "css/views/global-login",
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
                'js/views/global-login',
            ),
        ),
        'create_search_new' => array(
            'css'   => array(
                'css/components/loader',
                'css/components/select',
                'css/components/tooltips',
                'css/js-components/checkbox',
                'css/js-components/switch',
                'css/components/dropdown',
                'css/js-components/next-screen',
                'css/views/search-tags',
                'css/views/search-content',
                'css/views/search-filters',
                'css/views/search-options',
                'external/noUiSlider/nouislider',        
            ),
            'js'    => array(
                "js/components/next-screen",
                "js/components/checkbox",
                "js/components/switch",
                "external/noUiSlider/nouislider",
                "js/views/search-filters",
                "js/views/search-content",
                "js/views/search-options"
            ),
        ),
        'search-content'    => array(
            'css'   => array(
                'css/components/dropdown',
                'css/components/tooltips',
                'css/js-components/switch',
                'css/views/search-tags',
                'css/views/search-content',
                'css/views/search-options',
            ),
            'js'    => array(
                "js/components/switch",
                "js/views/search-content",
                "js/views/search-options",
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
            ),
            'js'    => array(
                "js/components/switch",
                "js/views/search-options",
            ),
        ),
        'next-screen'      => array(
            'css'   => array(
                'css/js-components/next-screen'
            ),
            'js'    => array(
                'js/components/next-screen'
            )
        )
    ];
}