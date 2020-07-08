<?php
class user {

}
class mail_user extends user {
    public static function change_email($new_email){
        $current_user = get_user_by('ID', get_current_user_id() );

        $hash           = md5( $new_email . time() . wp_rand() );
        $new_user_email = array(
            'hash'     => $hash,
            'newemail' => $new_email,
        );
        update_user_meta( $current_user->ID, '_new_email', $new_user_email );
        
        $email_text = __(
            'Hi ###USERNAME###,
            
            You recently requested a change in the email address associated with your account.

            To confirm the change, click on the link below:
            ###ADMIN_URL###

            Fanfiction Online'
        );

        $email_text = str_replace  ( '###USERNAME###', $current_user->user_login, $email_text );
        $email_text = str_replace  ( '###ADMIN_URL###', esc_url( admin_url( 'profile.php?newuseremail=' . $hash ) ), $email_text );
    
        wp_mail( $new_email, 'Email Change Request', $email_text );    
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
        $user = get_user_by( 'email', $value );
        if ($user !== false && intval($user->ID) !== intval(get_current_user_id()) ){
            $error->add('email','User with email exists.');
        }
        return $error;
    }
    function username($value){
        $error = new err();
        if ( strlen ($value) < 5 ) {
            $error->add('username','Username must be of at least 5 characters.');
        }
        if (strlen (preg_replace ('/(\d)|(\.)|(_)|([A-Z])+/i','',$value) ) > 0){
            $error->add('username','Must only contain dots(.), underscores(_), english alphabets, or numbers.');
        }
        $user = get_user_by( 'login', $value );
        if ($user !== false && intval($user->ID) !== intval(get_current_user_id()) ){
            $error->add('username','User with username exists.');
        }
        return $error;
    }
    function password($value){
        $error = new err();
        if ($value === ''){
            return $error;
        }
        if (strlen($value) < 9){
            $error->add('password','Lesser than 9 characters.');
        }
        if (strlen($value) > 30){
            $error->add('password','Greater than 30 characters.');
        }
        return $error;
    }
}