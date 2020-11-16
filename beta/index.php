<?php
function can_beta($user_id = null) {
    return false;
    if ($user_id === null) {
        $user_id = get_current_user_id();
    }
    $current = (int) $user_id;
    $betas = array_map('intval',get_option( 'reimport_allow', [] ));
    return in_array($current,$betas) || current_user_can( 'administrator' );
}
// if (!can_beta() && $app->is_beta() ) {
//     $app->redirect("https://fanfiction.online/" . trim($self->request,"/"));
// }
// if ($app->is_beta()) {
//     add_filter( 'home_url', function($url,$path) {
//         return "https://beta.fanfiction.online/" . trim($path,"/");
//     }, 10 , 2);
//     $app->beta_bundle = new bundle('beta-global');
//     $app->beta_bundle->js('beta/beta');
//     $app->beta_bundle->css('beta/beta');
// }
