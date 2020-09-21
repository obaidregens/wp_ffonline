<?php
//Takes in $args,$fields,$base_sql
//Gives out $error or $sql
function query_mec($fields,$args,$base_sql){
    $error = new err();

    $scale_fields = array();
    foreach ($fields as $key => $field ) {
        if ($field['type'] === 'scale'){
            $scale_fields[] = $field;
            unset($fields[$key]);
        }
    }
    sort($fields);
    $base_sql .= ' WHERE 1';
    $prepare = array();
    $text_protocol = (isset($args['text_search_protocol']) && $args['text_search_protocol'] === 'lenient') ? '%' : '';
    foreach ($fields as $field) {
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
    }

    $base_sql .= ' HAVING 1';
    foreach ($scale_fields as $field) {
        $arg = $field['arg'];
        if (isset($args[$arg])){
            $base_sql .= ' AND ' . $field['field'] . ' >= ' . $data_type . ' AND ' . $field['field'] . ' <= ' . $data_type;
            $prepare[] = isset($args[$arg]['from']) ? intval($args[$arg]['from']) : $field['default_from'];
            $prepare[] = isset($args[$arg]['to']) ? intval($args[$arg]['to']) : $field['default_to'];
        }
    }
    $args['orderby'] = isset($args['orderby']) ? $args['orderby'] : 'ID';
    $args['order'] = isset($args['order']) ? strtoupper(strval($args['order'])) : 'ASC';
    $base_sql .= ' ORDER BY ' . $args['orderby'] . ' ' . $args['order'];
    if (! in_array($args['orderby'],array_column($fields,'field')) &&
        ! in_array($args['orderby'],array_column($scale_fields,'field'))){
        $error->add('orderby','Field doesn\'t exist.');
    }
    if (! in_array($args['order'],array('ASC','DESC'))){
        $error->add('order','Pass ASC or DESC.');
    }

    $args['limit'] = isset($args['limit']) ? intval($args['limit']) : 10;
    $base_sql .= ' LIMIT %d';
    $prepare[] = $args['limit'];

    $args['page'] = isset($args['page']) ? intval($args['page']) : 1;
    $base_sql .= ' OFFSET %d';
    $prepare[] = ($args['page'] - 1) * $args['limit'];

    if ( isset($args['unique']) ) {
        $base_sql = 'SELECT * FROM ( ' . $base_sql . ') AS sub';
        $base_sql .= ' GROUP BY ' . $args['unique'];
        if ( ! in_array($args['unique'],array_column($fields,'field')) ){
            $error->add('unique','Unknown Field passed.');
        }
    }

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
    public static function get_type(){
        global $app;
        return array(
            'type'      => $app->type,
            'type_id'   => $app->type_id
        );
        $error = new err();
        global $template;
        if ($template === false || $template === null){
            $error->add('template','Template is false/null.');
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
            'page-create-cat.php',
            'manage/index.php',
            'page-login.php'
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
        return array(
            'type'      => $type,
            'type_id'   => $type_id
        );
    }
    private function type(){
        $error = new err();
        $get_type = _landing::get_type();
        $error->merge($get_type);
        if ($error->has()){
            return $error;
        }
        $this->type    = $get_type['type'];
        $this->type_id = $get_type['type_id'];
        return true;
    
    }
    private function log(){
        global $app;
        $error = new err();
        $input_data = array(
            'type'                  => $this->type,
            'type_id'               => $this->type_id,
            'timestamp'             => current_time('timestamp',true),
            'request'               => $app->request,
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
        if (! in_array($type,array('story','collection'))){
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
        FROM " . stats::$actions_table;
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
class reports extends stats {
    public static $dir = null;
    public static function init_dir(){
        $maindir = rtrim(explode('content',__DIR__,2)[0],'/\\') . '/';
        self::$dir = $maindir . 'reports/';
        if (! file_exists(self::$dir)){
            mkdir(self::$dir);
        }
    }
    public static function average_time_by_referrer(){
        $filename = self::$dir . 'average_time_by_referrer';
        if (file_exists($filename) && time() - filemtime($filename) <= 60*60*24*5){
            return file_get_contents($filename);
        }
        global $wpdb;
        $old_landings = $wpdb->get_results("SELECT * FROM stats_landings WHERE referrer_host IS NOT NULL ORDER BY ID ASC",ARRAY_A);
        $actions = $wpdb->get_results("SELECT * FROM stats_actions WHERE landing_id IN(" . implode(',',array_column($old_landings,'ID')) . ")",ARRAY_A);
        
        $gactions = [];
        foreach ($actions as $action ) {
            $landing_id = $action['landing_id'];
            if (! isset($gactions[$landing_id])){
                $gactions[$landing_id] = [];
            }
            $action['timestamp'] = (int) $action['timestamp'];
            $gactions[$landing_id][] = $action;
        }
        $vfs_full = [];
        foreach ($old_landings as $key => $landing ) {
            $landing_id = $landing['ID'];
            $vfs = $landing['vfs'];
            if (! isset($gactions[$landing_id])){
                continue;
            }
            $landing['actions'] = $gactions[$landing_id];
            if (! isset($vfs_full[$vfs])){
                $vfs_full[$vfs] = [];
            }
            $vfs_full[$vfs][] = $landing;
        }
        $stat = [];
        foreach($vfs_full as $vfs => $landings){
            $session_start = $landings[0]['timestamp'];
            foreach ($landings as $key => $landing){
                $start_timestamp = $landing['timestamp'];
                if ($key !== 0 && $start_timestamp - $end_timestamp >= 30*60){
                    $prev_landing = $landings[$key-1];
                    $stat[$prev_landing['referrer_host']][] = array(
                        'timespan'      => $end_timestamp - $session_start,
                        'vfs'           => $prev_landing['vfs'],
                        'user_id'       => $prev_landing['user_id']
                    );
                    $session_start = $start_timestamp;
                }
                $action_stamps = array_column($landing['actions'],'timestamp');
                rsort($action_stamps);
                $end_timestamp = $action_stamps[0];
            }
            $prev_landing = $landings[count($landings)-1];
            $stat[$prev_landing['referrer_host']][] = array(
                'timespan'      => $end_timestamp - $session_start,
                'vfs'           => $prev_landing['vfs'],
                'user_id'       => $prev_landing['user_id']
            );
        }
        $to_echo = [];
        $the_count = 0;
        foreach ($stat as $referrer => $visits) {
            $timestamps = array_column($visits,'timespan');
            $visit_count = count($timestamps);
            $the_count += $visit_count;
            $timespan = array_sum($timestamps)/$visit_count;
            $to_echo[$referrer] = [
                human_time_diff(0,$timespan),
                $visit_count
            ];
        }
        ob_start();
        ?>
        <h1>Average time by referrer.</h1>
        <index>
            <li>
                <cell>Referrer</cell>
                <cell>Average Time</cell>
                <cell>Visits</cell>
            </li>
            <?php foreach( $to_echo as $first => $array) { ?>
            <li>
                <cell><?= $first; ?></cell>
                <cell><?= $array[0]; ?></cell>
                <cell><?= $array[1]; ?></cell>
            </li>
            <?php } ?>
        </index>
        <?php
        $ml = ob_get_contents();
        ob_end_clean();
        file_put_contents($filename,$ml);
        return $ml;
    }
    public static function popular_link_ins(){
        $filename = self::$dir . 'popular_link_ins';
        if (file_exists($filename) && time() - filemtime($filename) <= 60*60*24*5){
            return file_get_contents($filename);
        }
        global $wpdb;
        $old_landings = $wpdb->get_results("SELECT * FROM stats_landings WHERE referrer_host IS NOT NULL AND referrer_host != 'local' ORDER BY ID ASC",ARRAY_A);
        $actions = $wpdb->get_results("SELECT * FROM stats_actions WHERE landing_id IN(" . implode(',',array_column($old_landings,'ID')) . ")",ARRAY_A);
        
        $gactions = [];
        foreach ($actions as $action ) {
            $landing_id = $action['landing_id'];
            if (! isset($gactions[$landing_id])){
                $gactions[$landing_id] = [];
            }
            $action['timestamp'] = (int) $action['timestamp'];
            $gactions[$landing_id][] = $action;
        }
        $referrers = [];
        foreach ($old_landings as $key => $landing ) {
            $landing_id = $landing['ID'];
            $ref = $landing['referrer_host'];
            if (! isset($gactions[$landing_id])){
                continue;
            }
            $collection = &$referrers[$landing['type']][$landing['type_id'] . '<->' . $ref];
            if (! isset($collection)){
                $collection = 0;
            }
            $collection += 1;
        }
        ob_start();
        ?>
        <style>
        h2,h2 + index {
            padding-left: 20px;
        }
        h1,h2 {
            padding-top: 20px;
        }
        </style>
        <h1>Popular Link-Ins</h1>
        <?php foreach ($referrers as $type => $from) { ?>
            <?php arsort($from); ?>
            <h2><?= ucfirst($type); ?></h2>
            <index>
                <li>
                    <cell>ID</cell>
                    <cell>From</cell>
                    <cell>Count</cell>
                </li>
                <?php foreach ($from as $ref => $each) { ?>
                    <?php
                    $ref = explode('<->',$ref);
                    if (in_array($type,array('chapter','book'))){
                        $ref[0] = '<a href="' . get_permalink( $ref[0] ) . '">' . get_the_title( $ref[0] ) . '</a>';
                    }
                    else if (in_array($type,array('author','author-updates','author-collections','author-books'))){
                        $ref[0] = '<a href="' . get_author_posts_url( $ref[0] ) . '">' . get_the_author_meta( 'display_name',$ref[0] ) . '</a>';
                    }
                    ?>
                    <li>
                        <cell><?= $ref[0]; ?></cell>
                        <cell><?= $ref[1]; ?></cell>
                        <cell><?= $each; ?></cell>
                    </li>
                <?php } ?>
            </index>
        <?php } ?>
        <?php
        $ml = ob_get_contents();
        ob_end_clean();
        file_put_contents($filename,$ml);
        return $ml;
    }

}
reports::init_dir();
function ctrk_encrypt($custom_data){
    $encrypt_method = CTRK_encrypt_method;
    $secret_key = CTRK_secret_key;
    $iv  =        CTRK_iv;
    $encrypted = bin2hex(openssl_encrypt(json_encode($custom_data),$encrypt_method,$secret_key,0,$iv));
    return $encrypted;
}
function ctrk_decrypt($hash,$assoc = false){
    if (! is_string($hash) || strlen($hash) % 2 !== 0){
        return null;
    }
    $assoc = (bool) $assoc;
    
    $encrypt_method = CTRK_encrypt_method;
    $secret_key = CTRK_secret_key;
    $iv  =        CTRK_iv;
    $decrypted = json_decode(openssl_decrypt(hex2bin($hash),$encrypt_method,$secret_key,0,$iv),$assoc);
    return $decrypted;
}


