<?php
function api_import_stories() {
    required_login();
    $re = import_stories::import_request($_POST['data']['storyIds'] ?? []);
    if ($re === false) {
        return ['code' => 9];
    }
    return ['code'  => 1];
}