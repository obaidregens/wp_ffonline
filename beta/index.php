<?php
if (!is_user_logged_in() && beta::is()) {
    $app->type = 'beta-login';
    $app->type_id = 0;
    $app->login();
}
if (!beta::can() && beta::is() ) {
    $expired = beta::expired();
    if ($expired) {
        $app->type = "beta-expired";
        $app->type_id = 0;
        $app->last_beta = $expired;
        $app->header([
            'title'     => construct_page_title("Invite Expired")
        ]);
        $app->template("/views/beta/expired");
        $app->footer();
        exit();
    }
    else {
        $app->redirect("https://fanfiction.online/" . trim($self->request,"/"));
    }
}
if (beta::is()) {
    add_filter( 'home_url', function($url,$path) {
        return "https://beta.fanfiction.online/" . trim($path,"/");
    }, 10 , 2);
    $app->beta_bundle = new bundle('beta-global');
    $app->beta_bundle->mix('idb');
    $app->beta_bundle->js('js/DOM');
    $app->beta_bundle->js('js/components/helpers');
    $app->beta_bundle->js('js/components/text-input');
    $app->beta_bundle->js('js/components/popup');
    $app->beta_bundle->js('js/components/toast');
    $app->beta_bundle->js('beta/beta');
    $app->beta_bundle->css('beta/beta');
}