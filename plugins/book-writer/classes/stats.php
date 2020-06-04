<?php
//Takes in $args,$fields,$base_sql
//Gives out $error or $sql
function query_mec($fields,$args,$base_sql){
    $error = new err();

    $prepare = array();
    $text_protocol = (isset($args['text_search_protocol']) && $args['text_search_protocol'] === 'lenient') ? '%' : '';
    foreach ($fields as $key => $field) {
        $data_type = $field['data_type'] === 'int' ? '%d' : '%s';
        if ($field['type'] === 'option'){
            $value = isset($args[$field['arg']]) ? $args[$field['arg']] : null;
            $value = isset($args['include_' . $field['arg']]) ? $args['include_' . $field['arg']] : $value;
            if (isset($value)){
                $value = (array) $value;
                if (empty($value)){
                    return array();
                }
                $fill = implode(', ',array_fill(0,count($value),$data_type));
                $base_sql .= ' AND ' . $field['field'] . ' IN (' . $fill . ')';
                $prepare = array_merge($prepare,$value);
            }

            $arg = 'exclude_' . $field['arg'];
            $value = isset($args[$arg]) ? (array) $args[$arg] : array();
            if (! empty($value)){
                $fill = implode(', ',array_fill(0,count($value),$data_type));
                $base_sql .= ' AND ' . $field['field'] . ' NOT IN (' . $fill . ')';
                $prepare = array_merge($prepare,$value);
            }
    
        }
        else if ($field['type'] === 'text'){
            $arg = $field['arg'];
            if (isset($args[$arg])){
                $base_sql .= ' AND ' . $field['field'] . ' LIKE %s';
                $args[$arg] = (string) $args[$arg];
                $prepare[] = $text_protocol . $args[$arg] . $text_protocol;
            }        
        }
        else if ($field['type'] === 'scale'){
            $arg = $field['arg'];
            if (isset($args[$arg])){
                $base_sql .= ' AND ' . $field['field'] . ' >= ' . $data_type . ' AND ' . $field['field'] . ' <= ' . $data_type;
                $prepare[] = isset($args[$arg]['from']) ? intval($args[$arg]['from']) : $field['default_from'];
                $prepare[] = isset($args[$arg]['to']) ? intval($args[$arg]['to']) : $field['default_to'];
            }
        }
    }
    $args['orderby'] = isset($args['orderby']) ? $args['orderby'] : 'ID';
    $args['order'] = isset($args['order']) ? strtoupper(strval($args['order'])) : 'ASC';

    if (! in_array($args['orderby'],array_column($fields,'field'))){
        $error->add('orderby','Field doesn\'t exist.');
    }
    if (! in_array($args['order'],array('ASC','DESC'))){
        $error->add('order','Pass ASC or DESC.');
    }
    $base_sql .= ' ORDER BY ' . $args['orderby'] . ' ' . $args['order'];

    $args['limit'] = isset($args['limit']) ? intval($args['limit']) : 10;
    $base_sql .= ' LIMIT %d';
    $prepare[] = $args['limit'];

    $args['page'] = isset($args['page']) ? intval($args['page']) : 1;
    $base_sql .= ' OFFSET %d';
    $prepare[] = ($args['page'] - 1) * $args['limit'];

    if ($error->has()){
        return $error;
    }
    global $wpdb;
    $sql = $wpdb->prepare( $base_sql, $prepare );
    return $sql;
}
class stats {
    protected static $landing_table = 'stats_landings';
    protected static $actions_table = 'stats_actions';
    protected function validate(){
        return stats::_validate($this->type,$this->type_id);
    }
    protected static function _validate($type,$type_id){
        $error = new err();
        if ( in_array($type,array('book','chapter','home','page')) ){
            $type_obj = get_post($type_id);
            if ($type_obj !== null && $type !== 'home' && $type !== $type_obj->post_type){
                $type_obj = null;
            }
            $type_obj = $type === 'home' ? true : $type_obj;
        }
        else if ( in_array($type,array('collection')) ){
            $collection_query = collection::query(array(
                'ids'       => array($type_id)
            ));
            $type_obj = empty($collection_query) ? null : $collection_query[0];
        }
        else if ( in_array($type,array('collection-index','404')) ){
            $type_obj = true;
        }        
        else if ( in_array($type,array('author','author-books','author-collections','author-updates')) ){
            $type_obj = get_userdata($type_id);
            $type_obj = $type_obj === false ? null : $type_obj;
        }
        else{
            $error->add('type','Unknown Type passed.');
            return $error;            
        }
        if ($type_obj === null){
            $error->add('type_id','Invalid ID.');
            return $error;
        }
        
        return true;

    }
    protected static function set_cookie($vfs){
        setcookie('vfs', $vfs, time() + (86400 * 5475), "/");
    }
    public static function last_online($args = array()){
        unset($args['orderby']);
        unset($args['order']);
        unset($args['limit']);

        $args = array_merge(array(
            'order'             => 'DESC',
            'orderby'           => 'timestamp',
            'limit'             => 1
        ),$args);
        $query = _action::query($args);
        return empty($query) ? 0 : intval($query[0]['timestamp']);
    }
}
class _landing extends stats {
    function __construct(){
        $error = new err();
        $error->merge($this->type());
        if ($error->has()){
            return $error;
        }
        $error->merge($this->validate());
        if ($error->has()){
            return $error;
        }

        $this->ip = $_SERVER['REMOTE_ADDR'];
        $this->vfs = _landing::vfs();
        $this->referrer = _landing::referrer();
        $this->log();
    }
    private function type(){
        $error = new err();
        global $template;
        if ($template === false){
            $error->add('template','Template is false');
            return $error;
        }
        $template_file = str_replace('/themes/book-writer/','',explode('wp-content',$template)[1]);
        //First off = 404's
        if (strpos($template_file,'404.php') !== false){
            $type = '404';
            $type_id = 0;
        }
        else if (in_array($template_file,array('author.php','author-ffonline.php'))){
            global $author;
            $type = 'author';
            $type_id = $author;
        }
        else if (in_array($template_file,array('author/books.php'))){
            global $author;
            $type = 'author-books';
            $type_id = $author;
        }
        else if (in_array($template_file,array('author/updates.php'))){
            global $author;
            $type = 'author-updates';
            $type_id = $author;
        }
        else if (in_array($template_file,array('author/collections.php'))){
            global $author;
            $type = 'author-collections';
            $type_id = $author;
        }
        else if (in_array($template_file,array('collections/single.php'))){
            global $collection;
            $type = 'collection';
            $type_id = $collection['ID'];
        }
        else if (in_array($template_file,array('collections/index.php'))){
            $type = 'collection-index';
            $type_id = 0;
        }
        else if ($template_file == 'page-search.php'){
            global $post;
            $type = 'home';
            $type_id = $post->ID;
        }
        else if (in_array($template_file,array(
            'page-dashboard.php',
            'page-contact.php',
            'page-reset-password.php',
            'page-create-cat.php'
        ))){
            global $post;
            $type = 'page';
            $type_id = $post->ID;
        }
        else if ($template_file == 'single-book.php'){
            global $post;
            $type = 'book';
            $type_id = $post->ID;
        }
        else if ($template_file == 'single-chapter.php'){
            global $post;
            $type = 'chapter';
            $type_id = $post->ID;
        }
        else{
            $error->add('template','Unknown Template: "' . $template_file . '"');
            return $error;
        }
        $this->type = $type;
        $this->type_id = $type_id;
        return true;
    
    }
    private function log(){
        $error = new err();
        $input_data = array(
            'type'                  => $this->type,
            'type_id'               => $this->type_id,
            'timestamp'             => current_time('timestamp',true),
            'user_id'               => get_current_user_id(),
            'IP'                    => $this->ip,
            'vfs'                   => $this->vfs,
            'referrer_host'         => $this->referrer['host'],
            'referrer_path'         => $this->referrer['path'],
            'platform'              => 'Not Implemented',
            'browser'               => 'Not Implemented',
            'browser_version'       => 'Not Implemented',
        );
        global $wpdb;
        $response = $wpdb->insert(
            self::$landing_table,
            $input_data
        );
        if ($response === false){
            $error->add('db_error',$wpdb->last_error);
            return $error;
        }
        $this->landing_id = $wpdb->insert_id;
        return $wpdb->insert_id;
    }
    public static function vfs(){
        $table_name = self::$landing_table;
        global $wpdb;
        if( isset($_COOKIE['vfs'])) {
            $result = $wpdb->get_results( $wpdb->prepare( "
                SELECT * FROM $table_name WHERE vfs = %s LIMIT 1
            ",$_COOKIE['vfs']));
            if (! is_null($result) && ! empty($result)){
                $vfs = $result[0]->vfs;
                stats::set_cookie($vfs);
                return $vfs;
            }
        }
        if (is_user_logged_in()){
            $result = $wpdb->get_results( $wpdb->prepare( "
                SELECT * FROM $table_name WHERE user_id = %d LIMIT 1
            ",get_current_user_id()));
            if (! is_null($result) && ! empty($result)){
                $vfs = $result[0]->vfs;
                stats::set_cookie($vfs);
                return $vfs;
            }
        }
        $result = $wpdb->get_results( $wpdb->prepare( "
            SELECT * FROM $table_name WHERE IP = %s LIMIT 1
        ",$_SERVER['REMOTE_ADDR']));
        if (! is_null($result) && ! empty($result)){
            $vfs = $result[0]->vfs;
            stats::set_cookie($vfs);
            return $vfs;
        }
        $vfs = bin2hex(random_bytes(39));
        stats::set_cookie($vfs);
        return $vfs;
    }
    private static function referrer(){
        if (! isset($_SERVER['HTTP_REFERER'])){
            return array(
                'host'  => null,
                'path'  => null
            );
        }
        $referrer = parse_url($_SERVER['HTTP_REFERER']);
        if ($referrer['host'] == 'fanfiction.online' || $referrer['host'] == '192.168.100.33'){
            $referrer['host'] = 'local';
        }
        return $referrer;
    }
    function encrypt(){
        return ctrk_encrypt(array(
            'type'          => $this->type,
            'type_id'       => $this->type_id,
            'landing_id'    => $this->landing_id
        ));
    }
    public static function decrypt($hash){
        return ctrk_decrypt($hash);
    }
    public static function query($args = array()){
        $fields = array(
            array(
                'field'     => 'ID',
                'type'      => 'option',
                'arg'       => 'ids',
                'data_type' => 'int',
            ),
            array(
                'field'     => 'vfs',
                'type'      => 'option',
                'arg'       => 'vfs',
                'data_type' => 'string',
            ),
            array(
                'field'     => 'user_id',
                'type'      => 'option',
                'arg'       => 'user_ids',
                'data_type' => 'int',
            ),
            array(
                'field'     => 'type',
                'type'      => 'option',
                'arg'       => 'types',
                'data_type' => 'string',
            ),
            array(
                'field'     => 'type_id',
                'type'      => 'option',
                'arg'       => 'type_ids',
                'data_type' => 'int',
            ),
            array(
                'field'         => 'timestamp',
                'type'          => 'scale',
                'arg'           => 'timestamp',
                'data_type'     => 'int',
                'default_from'  => 0,
                'default_to'    => current_time('timestamp',true)
            ),
            array(
                'field'     => 'IP',
                'type'      => 'text',
                'arg'       => 'ips',
                'data_type' => 'string'
            )
        );
        $error = new err();
        $base_sql =
        "SELECT *,(
            SELECT COUNT(*) 
            FROM " . stats::$actions_table . "
            WHERE " . stats::$landing_table . ".ID = " . stats::$actions_table . ".landing_id
        ) AS actions
        FROM " . stats::$landing_table . "
        WHERE 1";
        $sql = query_mec($fields,$args,$base_sql);
        $error->merge($sql);
        if ($error->has()){
            return $error;
        }
        global $wpdb;
        $results = $wpdb->get_results( $sql ,ARRAY_A );
        return $results;
    }
}
class _action extends stats {
    function __construct($landing_id){
       $error = new err();
       $unprepared = "SELECT ID FROM " . self::$landing_table . " WHERE ID = %d LIMIT 1";
       global $wpdb;
       $sql = $wpdb->prepare( $unprepared, array(intval($landing_id)) );
       $results = $wpdb->get_results( $sql ,ARRAY_A );
       if (empty($results)){
           $error->add('landing_id','does not exists');
           return $error;
       }
       $this->landing_id = $landing_id;
    }
    function log_view($type,$type_id){
        $error = new err();
        $validate_return =  stats::_validate($type,$type_id);
        if (err::is($validate_return)){
            return $validate_return;
        }
        $input_data = array(
            'landing_id'        => $this->landing_id,
            'stat'              => 'view',
            'type'              => $type,
            'type_id'           => $type_id,
            'timestamp'         => current_time('timestamp',true),
        );
        global $wpdb;
        $response = $wpdb->insert(
            self::$actions_table,
            $input_data
        );
        if ($response === false){
            $error->add('db_error',$wpdb->last_error);
            return $error;
        }
        return $wpdb->insert_id;
    }
    function log_notifications($type,$type_id){
        $error = new err();
        $validate_return =  stats::_validate($type,$type_id);
        if (err::is($validate_return)){
            return $validate_return;
        }
        $input_data = array(
            'landing_id'        => $this->landing_id,
            'stat'              => 'notifications',
            'type'              => $type,
            'type_id'           => $type_id,
            'timestamp'         => current_time('timestamp',true),
        );
        global $wpdb;
        $response = $wpdb->insert(
            self::$actions_table,
            $input_data
        );
        if ($response === false){
            $error->add('db_error',$wpdb->last_error);
            return $error;
        }
        return $wpdb->insert_id;
    }
    function log_impression($type,$type_id){
        $error = new err();
        if (! in_array($type,array('book','collection'))){
            $error->add('type','Impressions can only be logged for books or collections.');
        }
        $validate_return = stats::_validate($type,$type_id);
        $error->merge($validate_return);
        if ($error->has()){
            return $error;
        }
        $input_data = array(
            'landing_id'            => $this->landing_id,
            'stat'                  => 'impression',
            'type'                  => $type,
            'type_id'               => $type_id,
            'timestamp'             => current_time('timestamp',true),
        );
        global $wpdb;
        $response = $wpdb->insert(
            self::$actions_table,
            $input_data
        );
        if ($response === false){
            $error->add('db_error',$wpdb->last_error);
            return $error;
        }
        return $wpdb->insert_id;
    }
    public static function query($args = array()){
        $fields = array(
            array(
                'field'     => 'ID',
                'type'      => 'option',
                'arg'       => 'ids',
                'data_type' => 'int',
            ),
            array(
                'field'     => 'landing_id',
                'type'      => 'option',
                'arg'       => 'landing_ids',
                'data_type' => 'int',
            ),
            array(
                'field'     => 'type',
                'type'      => 'option',
                'arg'       => 'types',
                'data_type' => 'string',
            ),
            array(
                'field'     => 'type_id',
                'type'      => 'option',
                'arg'       => 'type_ids',
                'data_type' => 'int',
            ),
            array(
                'field'     => 'stat',
                'type'      => 'option',
                'arg'       => 'stats',
                'data_type' => 'string',
            ),
            array(
                'field'         => 'timestamp',
                'type'          => 'scale',
                'arg'           => 'timestamp',
                'data_type'     => 'int',
                'default_from'  => 0,
                'default_to'    => current_time('timestamp',true)
            ),
        );
        $error = new err();
        $base_sql =
        "SELECT *
        FROM " . stats::$actions_table . "
        WHERE 1";
        $sql = query_mec($fields,$args,$base_sql);
        $error->merge($sql);
        if ($error->has()){
            return $error;
        }
        global $wpdb;
        $results = $wpdb->get_results( $sql ,ARRAY_A );
        return $results;
    }
}
function ctrk_encrypt($custom_data){
    $encrypt_method = "AES-256-CBC";
    $secret_key = 'ygj6410386b3a7369pcs22b8dq21388430025v118143thu6baj41';
    $iv  =        'flkdd5ge63w1bb51';
    $encrypted = bin2hex(openssl_encrypt(json_encode($custom_data),$encrypt_method,$secret_key,0,$iv));
    return $encrypted;
}
function ctrk_decrypt($hash){
    if (! is_string($hash) || strlen($hash) % 2 !== 0){
        return null;
    }
    $encrypt_method = "AES-256-CBC";
    $secret_key = 'ygj6410386b3a7369pcs22b8dq21388430025v118143thu6baj41';
    $iv  =        'flkdd5ge63w1bb51';
    $decrypted = json_decode(openssl_decrypt(hex2bin($hash),$encrypt_method,$secret_key,0,$iv));
    return $decrypted;
}


