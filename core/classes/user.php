<?php
// Exclude user_status 1 & 2 from WP_User_Query
add_action( 'pre_user_query', function( $uqi ) {
    $uqi->query_where .= ' AND user_status = 0';
});
class user {
    public $ID;
    public $user_login;
    public $user_pass;
    public $user_nicename;
    public $user_email;
    public $user_url;
    public $user_registered;
    public $user_activation_key;
    public $user_status;
    public $display_name;

    function __construct ($obj) {
        $aa = get_object_vars ( $obj );
        foreach ($aa as $k => $v) {
            $this->{$k} = $v;
        }    
    }
    // Signup
    public static function signup ($email,$username) {
        $validation = (new v_user([
            'username'  => $username,
            'email'     => $email
        ],['username','email']))->return;
        if (err::is($validation)){
            return $validation;
        }
        $code = new v_code;
        global $wpdb;

        $wpdb->insert(
            'wp_users',
            array(
                'user_login'            => $username,
                'user_pass'             => bin2hex(random_bytes(103)),
                'user_nicename'         => $username,
                'user_email'            => $email,
                'user_registered'       => current_time('mysql'),
                'user_activation_key'   => $code->ID,
                'user_status'           => 1,
                'display_name'          => '@' . $username
            )
        );
        $user_id = $wpdb->insert_id;

        $c = cache::now();
        $c->delete(self::key('ID',$user_id));
        $c->delete(self::key('user_email',$email));
        $c->delete(self::key('user_login',$username));

        mail_user::signup_mail($user_id,$code->code);
        return $code->ID;
    }
    public static function verify($code, $token, $email, $username){
        global $wpdb;

        $results = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM wp_users
            WHERE user_status = 0
            AND (
                user_email = %s OR
                user_login = %s
            )
        ",[$email,$username]));
        if (! empty($results)){
            return false;
        }

        $results = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM wp_users
            WHERE user_status = 1
            AND user_email = %s
            AND user_login = %s
            AND user_activation_key = %s
        ",[$email,$username,$token]));
        if (empty($results)){
            return false;
        }
        $return = v_code::is($results[0]->user_activation_key,$code);
        if ($return === false){
            return false;
        }
        $wpdb->update(
            'wp_users',
            array(
                'user_status'         => 0,
                'user_activation_key' => 0,
            ),
            array(
                'ID'    => $results[0]->ID
            )
        );

        $c = cache::now();
        $c->delete(self::key('ID',$results[0]->ID));
        $c->delete(self::key('user_email',$results[0]->user_email));
        $c->delete(self::key('user_login',$results[0]->user_login));

        collection_helpers::create_default(intval($results[0]->ID));
        self::internal_login($results[0]->ID);
        return true;
    }
    // Login
    public static function login($username, $password){
        $return = wp_authenticate( $username, $password );
        if (is_wp_error( $return )){
            return false;
        }
        $user = $return->data;
        if (intval($user->user_status) !== 0){
            return false;
        }
        self::internal_login($user->ID);
        return $return->data;
    }
    // OTP
    public static function send_code($username_or_email){
        $user = v_user::get_by_username_or_email($username_or_email);
        if ($user === false){
            return false;
        }
        $code = new v_code;
        global $wpdb;
        $wpdb->update(
            'wp_users',
            [ 'user_activation_key'   => $code->ID ],
            [ 'ID'        => $user->ID ]
        );
        mail_user::login_code_mail($user,$code->code);
        return $code->ID;
    }
    public static function login_code($username_or_email,$token,$code){
        $user = v_user::get_by_username_or_email($username_or_email);
        if ($user === false){
            return false;
        }
        if (v_code::is($token,$code) === false){
            return false;
        }
        global $wpdb;
        $return = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM wp_users
            WHERE ID = %s
            AND user_status = 0
            AND user_activation_key = %s
        ",[$user->ID,$token]));
        if (empty($return)){
            return false;
        }
        self::internal_login($return[0]->ID);
        return true;
    }
    // Internal
    public static function internal_login($user_id){
        wp_cache_delete($user_id, 'users');
        wp_clear_auth_cookie();
        wp_set_auth_cookie  ( $user_id, true );
        wp_set_current_user ( $user_id );
    }
    // Get
    // Duplicates WP get_user_by
    static function get_by(string $field,$value,bool $show_unverified = false) {
        $f = in_array($field,["id","ID"]) ? "ID" : $field;
        $f = in_array($f,["email"]) ? "user_email" : $f;
        $f = in_array($f,["login"]) ? "user_login" : $f;
        if (!in_array($f,['ID','user_email','user_login'])) {
            return false;
        }
        if (!$show_unverified) {
            $cached = cache::now()->get(user_cache::key($f,$value));
            if ($cached !== null) {
                return $cached;
            }    
        }

        $sql = "SELECT * FROM wp_users WHERE $f = " . ($f === "ID" ? "%d" : "%s");
        if (!$show_unverified) {
            $sql .= " AND user_status = 0";
        }
        else {
            $sql .= " AND user_status IN (0,1)";
        }
        $sql .= " LIMIT 1";
        global $wpdb;
        $r = $wpdb->get_results($wpdb->prepare($sql,[$value]));
        $user = empty($r) ? false : new user($r[0]);

        if (!$show_unverified) {
            user_cache::add($user);
        }

        return $user;
    }
    static function get($ID) {
        return user::get_by('ID',$ID);
    }
}
class user_settings extends user {
    protected static $default_usettings = [
        "features"  => true
    ];
    public static function change_username($new_username) {
        $validation = (new v_user([
            'username'  => $new_username,
        ],['username']))->return;
        if (err::is($validation)) {
            return $validation;
        }

        $meta_name = 'last_change_username';
        $change_username_meta = get_user_meta( get_current_user_id(), $meta_name, true );
        $change_username = $change_username_meta === "" ? 1 : (intval($change_username_meta) > time() - 60*60*24*30 ? 0 : 1);
        if ($change_username < 1) {
            return (new err())->add('username','No username change is available.');
        }
        update_user_meta( get_current_user_id(), $meta_name, time() );
        global $wpdb;
        $wpdb->update(
            $wpdb->users,
            [
                'user_login'        => $new_username,
                'user_nicename'     => "@" . $new_username,
                'display_name'      => "@" . $new_username
            ],
            [
                'ID'                => get_current_user_id()
            ]
        );
        $c = cache::now();
        $id_key = self::key('ID',get_current_user_id());
        $cached = $c->get($id_key);
        if ($cached !== null) {
            $c->delete($id_key);
            $c->delete(self::key('user_email',$cached->user_email));
            $c->delete(self::key('user_login',$cached->user_login));    
        }

        return true;
    }
    public static function get ($setting, $user = null) {
        if ($user === null){
            $user = get_current_user_id();
        }
        $meta = get_user_meta( $user, 'usetting_' . $setting, true );
        return $meta === '' ? (self::$default_usettings[$setting] ?? null) : unserialize($meta);
    }
    public static function set ($setting, $value, $user = null) {
        if ($user === null){
            $user = get_current_user_id();
        }
        update_user_meta( $user, 'usetting_' . $setting, serialize($value) );
    }
}
class mail_user extends user {
    protected static function signup_mail($user_id,$code){
        $user = user::get_by( 'ID', $user_id , true );
        email( [
            'to'        => $user->user_email,
            'template'  => 'signup',
            'subject'   => 'Welcome to Fanfiction Online!',
            'params'    => [
                '###SUBJECT###' => 'Welcome to Fanfiction Online!',
                '###SETTINGS_URL###' => "https://fanfiction.online/" . '@' . $user->user_login . '/settings',
                '###USERNAME###'=> '@' . $user->user_login,
                '###CODE###'    => strtoupper($code),
            ],
        ] );
    }
    protected static function login_code_mail($user,$code){
        email( [
            'to'        => $user->user_email,
            'template'  =>'OTP',
            'subject'   => 'Here\'s your One-Time PIN',
            'params'    => [
                '###SUBJECT###' => 'Here\'s your One-Time PIN',
                '###USERNAME###'=> "@" . $user->user_login,
                '###CODE###'    => strtoupper($code),
            ],
        ] );
    }
}
class v_user extends user {
    function __construct($args = array(),$required = array()){
        $error = new err();
        $fields = array('password','username','email');
        foreach ($fields as $field ) {
            if ( isset($args[$field]) && $args[$field] !== '' ){
                $error->merge( $this->{$field}($args[$field]) );
            }
            else if ( in_array($field,$required) ){
                $error->add($field,'Required');
            }
        }
        if ($error->has()){
            $this->return = $error;
            return;
        }
        $this->return = true;
    }
    function email($value){
        $error = new err();
        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false){
            $error->add('email','Invalid Format.');
        }
        $user = user::get_by( 'email', $value );
        if ( self::exists($user) ){
            $error->add('email','User with email exists.');
        }
        return $error;
    }
    function username($value){
        $error = new err();
        if ( !str::is_lower($value) ) {
            $error->add('username','Username must be in lowercase.');
        }
        if ( strlen ($value) < 5 ) {
            $error->add('username','Username must be of at least 5 characters.');
        }
        if ( strlen ($value) > 20 ) {
            $error->add('username','Username cannot be of more than 20 characters.');
        }
        if (strlen (preg_replace ('/(\d)|(\.)|(_)|([A-Z])+/i','',$value) ) > 0){
            $error->add('username','Must only contain dots(.), underscores(_), english alphabets, or numbers.');
        }
        $user = user::get_by( 'login', $value );
        if ( self::exists($user) ){
            $error->add('username','User with username exists.');
        }
        return $error;
    }
    function password($value){
        $error = new err();
        if ($value === ''){
            return $error;
        }
        if (strlen($value) < 6){
            $error->add('password','Lesser than 6 characters.');
        }
        if (strlen($value) > 30){
            $error->add('password','Greater than 30 characters.');
        }
        return $error;
    }
    protected static function exists($user_obj){
        if ($user_obj->data) {
            $user_obj = $user_obj->data;
        }
        return 
            $user_obj !== false &&
            !is_current_user($user_obj->ID) &&
            intval($user_obj->user_status) === 0;
    }
    protected static function get_by_username_or_email($username_or_email){
        $byusername = user::get_by( 'login' , $username_or_email );
        $byemail = user::get_by( 'email' , $username_or_email );
        if ($byusername === false && $byemail === false ){
            return false;
        }
        $user = $byusername !== false ? $byusername : $byemail;
        return $user;
    }
}
function get_user_by(string $field,$value,bool $unverified = false) {
    $user = user::get_by($field,$value,$unverified);
    if ($user === false) {
        return false;
    }
    return new WP_User($user);
}

class GoogleAuth {
    protected static $table = "google_auth";
    static function login($token,$landing_id){
        $opts = [
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query(
                    [ 'id_token' => $token ]
                )
            ]
        ];
        $context = stream_context_create($opts);
        $result = json_decode(file_get_contents('https://oauth2.googleapis.com/tokeninfo', false, $context),true);
        if (($result['aud'] ?? false) !== GOOGLE_CLIENT_ID . ".apps.googleusercontent.com") {
            return false;
        }
        if (($result['email_verified'] ?? false) === false) {
            return false;
        }
        $table = self::$table;
        global $wpdb;
        
        $exists = $wpdb->get_results($wpdb->prepare(
            "SELECT `ID`,`user_id` FROM $table
            WHERE `user_id` != 0
            AND google_user_id = %s",
        [$result['sub']]));
        $exists = empty($exists) ? false : $exists[0];

        if ($exists) {
            user::internal_login($exists->user_id);
            return ['action'=>'login'];
        }

        $exists_email = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM wp_users
            WHERE `user_email` = %s
            AND user_status = 0",
        [$result['email']]));
        $exists_email = empty($exists_email) ? false : $exists_email[0];

        $wpdb->insert(
            self::$table,
            [
                'user_id'       => $exists_email ? $exists_email->ID : 0,
                'email'         => $result['email'],
                'google_user_id'=> $result['sub'],
                'landing_id'    => $landing_id,
                'registered'    => millitime(),
            ]
        );
        if ($exists_email) {
            self::internal_login($exists_email->ID);
            return ['action'=>'login'];
        }
        return ['action'=>'signup','ID'=>$wpdb->insert_id];
    }
    static function verify($ID,$username) {
        global $wpdb;
        $table = self::$table;

        $exists = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table
            WHERE `ID` = %d AND `user_id` = 0",
        [$ID]));
        $exists = empty($exists) ? false : $exists[0];
        if (!$exists) {
            return false;
        }

        $exists_email = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM wp_users
            WHERE (`user_login` = %s OR `user_email` = %s)
            AND user_status = 0",
        [$username,$exists->email]));
        $exists_email = empty($exists_email) ? false : $exists_email[0];
        if ($exists_email) {
            return false;
        }
        $wpdb->insert(
            'wp_users',
            [
                'user_login'            => $username,
                'user_pass'             => bin2hex(random_bytes(103)),
                'user_nicename'         => $username,
                'user_email'            => $exists->email,
                'user_registered'       => current_time('mysql'),
                'user_activation_key'   => "",
                'user_status'           => 0,
                'display_name'          => "@$username"
            ]
        );
        $user_id = intval($wpdb->insert_id);

        $c = cache::now();
        $c->delete(self::key('ID',$user_id));
        $c->delete(self::key('user_email',$exists->email));
        $c->delete(self::key('user_login',$username));

        $wpdb->update(
            self::$table,[
                'user_id'   => $user_id,
            ],[
                'ID'        => $ID,
                'user_id'   => 0
            ]
        );
        collection_helpers::create_default($user_id);
        return $user_id;
    }
}
class user_cache {
    static function key ($field, $value) {
        if (in_array($field,['ID','user_email','user_login'])) {
            return 'User_' . $field . '_' . $value;
        }
        $f = in_array($field,["id","ID"]) ? "ID" : $field;
        $f = in_array($f,["email"]) ? "user_email" : $f;
        $f = in_array($f,["login"]) ? "user_login" : $f;    
        return 'User_' . $field . '_' . $value;
    }
    static function add ($obj) {
        $c = cache::now();
        $id_key = self::key('ID',$obj->ID);
        $c->set($id_key,$obj);
        $c->ref(self::key('user_login',$obj->user_login),$id_key);
        $c->ref(self::key('user_email',$obj->user_email),$id_key);
    }
    static function delete($id) {
        $c = cache::now();
        $c->delete(self::key('ID',$id));
    }
}