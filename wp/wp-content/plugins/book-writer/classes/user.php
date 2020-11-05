<?php
// Exclude user_status 1 & 2 from WP_User_Query
add_action( 'pre_user_query', function( $uqi ) {
    $uqi->query_where .= ' AND user_status = 0';
});
class user {
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
        wp_set_current_user ( $user_id );
        wp_set_auth_cookie  ( $user_id, true );
    }
    // Get
    // Duplicates WP get_user_by
    static function get_by(string $field,$value,bool $show_unverified = false) {
        $f = in_array($field,["id","ID"]) ? "ID" : "";
        $f = in_array($field,["email"]) ? "user_email" : $f;
        $f = in_array($field,["login"]) ? "user_login" : $f;
        if ($f === "") {
            return false;
        }
        $sql = "SELECT * FROM wp_users WHERE $f = " . ($f === "ID" ? "%d" : "%s");
        if (!$show_unverified) {
            $sql .= " AND user_status = 0";
        }
        else {
            $sql .= " AND user_status IN (0,1)";
        }
        global $wpdb;
        $r = $wpdb->get_results($wpdb->prepare($sql,[$value]));
        return empty($r) ? false : $r[0];
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
                '###CODE###'    => $code,
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
                '###CODE###'    => $code,
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