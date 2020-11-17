<?php
if (!is_user_logged_in() && beta::is()) {
    $app->type = 'beta-login';
    $app->type_id = 0;
    $app->login();
}
if (!beta::can() && beta::is() ) {
    $app->redirect("https://fanfiction.online/" . trim($self->request,"/"));
}
if (beta::is()) {
    add_filter( 'home_url', function($url,$path) {
        return "https://beta.fanfiction.online/" . trim($path,"/");
    }, 10 , 2);
    $app->beta_bundle = new bundle('beta-global');
    $app->beta_bundle->js('beta/beta');
    $app->beta_bundle->css('beta/beta');
}