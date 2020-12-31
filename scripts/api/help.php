<?php
function api_get_tour() {
    required_params("tour","url","isMobile");
    $d = &$_POST['data'];
    $f = MAIN_DIR . '/content/tours/' . $d['tour'] . '.json';
    if (!file_exists($f) || $d['tour'] === "index") {
        return ['code' => 10];
    }
    $tour = json_decode(file_get_contents($f),true);
    if ($tour['login'] && !is_user_logged_in()) {
        return ['code' => 6];
    }
    $steps = $tour['steps'];
    $r = new Router($d['url']);
    foreach ($steps as $dyno => $parts) {
        if(!$r->listen($dyno)) {
            continue;
        }
        foreach ($parts['steps'] as $k => $v) {
            $c = &$parts['steps'][$k];
            if (!$d['isMobile']){
                unset($c['mobile']);
                continue;
            }
            $c = $v['mobile'] ?? $v;
        }
        return [
            'code'  => 1,
            'steps' => $parts['steps']
        ];
    }
    return ['code'=>2,'steps'=>[]];
}
function api_get_help_results() {
    required_params('search');
    $s = $_POST['data']['search'];
    $list = [];
    global $wpdb;
    $faqs = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM questions
        WHERE question LIKE %s
        OR answer LIKE %s
        WHERE status = 'public'",
        ["%" . $s . "%","%" . $s . "%"]
    ));
    foreach ($faqs as $faq) {
        $list[] = [
            'type'  => 'faq',
            'value' => $faq->ID,
            'name'  => $faq->question
        ];
    }
    $index = json_decode(file_get_contents(MAIN_DIR . "/content/tours/index.json"),true);
    $lev = 3;
    $lev_tags = 2;
    foreach ($index as $tour) {
        $tour_obj = [
            'type'  => 'guide',
            'value' => $tour['name'],
            'name'  => $tour['Title']
        ];
        // logging($s,$tour['Title'],similar_text($s,$tour['Title']));
        // logging($s,$tour['Description'],similar_text($s,$tour['Description']));
        if (similar_text($s,$tour['Title']) >= $lev || similar_text($s,$tour['Description']) >= $lev) {
            $list[] = $tour_obj;
            continue;
        }
 
        foreach ($tour['tags'] as $tag) {
            if (similar_text($s,$tag) >= ($lev_tags ?? $lev)) {
                $list[] = $tour_obj;
                continue 2;
            }
        }
    }

    return [
        'code'      => 1,
        'result'    => [
            'list'      => $list
        ]
    ];
}