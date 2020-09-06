<?php
function api_new_tax() {
    required_admin();
    required_params('tax','name');
    $d = &$_POST['data'];
    wp_insert_term( $d['name'], $d['tax'] );
    return ['code'  => 1];
}