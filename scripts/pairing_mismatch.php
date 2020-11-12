<style>
    html {
        background-color: #292929;
        color: #b7bfc4;
    }
</style>
<?php
define("NO_ROUTES",true);
require (rtrim(explode('content',__DIR__,2)[0],'/\\') . '/content/index.php');

$sql =
"SELECT
	a.book_id as story,
    b.character_id as pairing_char,
    c.term_taxonomy_id as story_char,
    a.pairing_id as pairing
FROM pairing_relationships as a
INNER JOIN character_pairings as b ON a.pairing_id = b.pairing_id
INNER JOIN wp_term_relationships as c ON a.book_id = c.object_id
INNER JOIN wp_term_taxonomy as d ON c.term_taxonomy_id = d.term_id
WHERE d.taxonomy = 'character'
";
global $wpdb;
$r = $wpdb->get_results($sql);
$stories = [];
foreach ($r as $row ) {
    // Story
    $story = &$stories[$row->story];
    $story = $story ?? [
        "pairings"      => [],
        "story_char"    => [],
    ];
    $pair = &$story['pairings'][$row->pairing];
    $pair = $pair ?? [];
    $pair[] = $row->pairing_char;
    $story['story_char'][] = $row->story_char;
}
function n ($t = "") {
    echo $t . "\n";
}
$merge_raw = json_decode(file_get_contents(MAIN_DIR . '/pairings.txt'));
$remove_pairings = array_column($merge_raw,'remove');
$sql = $wpdb->prepare("SELECT * FROM character_pairings WHERE pairing_id IN (" . sqlPlaceholder($remove_pairings) .")",$remove_pairings);
$tempr = $wpdb->get_results($sql);
$lookup_chars = [];
foreach ($tempr as $row ) {
    $k = &$lookup_chars[$row->pairing_id];
    $k = $k ?? [];
    $k[] = $row->character_id;
}
n("<pre>");

$merge = [];
foreach ($merge_raw as $v) {
    if (! isset($lookup_chars[$v->remove])) {
        continue;
    }
    $k = &$merge[$v->merge];
    $k = $k ?? [];
    $k[$v->remove] = $lookup_chars[$v->remove];
}
n(count($lookup_chars) . "-" . count($merge));
n();n();n();n();n();n();n();n();n();n();
$misplaced = [];
// Action
foreach ($stories as $story_id => $story) {
    $misplaced[] = $story_id;
    $story['story_char'] = array_unique($story['story_char']);
    foreach($story['pairings'] as $pairing_id => $chars) {
        $diff = array_unique(array_diff($chars,$story['story_char']));
        if (count($diff) > 0) {
            if (!isset($merge[$pairing_id])) {
                // n("Cannot be resolved");
                continue;
            }
            foreach ($merge[$pairing_id] as $merge_pairing_option => $merge_chars) {
                $diff = array_unique(array_diff(
                    $merge_chars,
                    $story['story_char']
                ));
                if (count($diff) > 0) {
                    continue;
                }
                // n("Found Replacement for Pairing " . $pairing_id . " of Story " . $story_id );
            break;
            }
        }
    }
}
$s = array_unique($misplaced);
$sql = $wpdb->prepare(
    "SELECT * FROM import_stories
    WHERE story_id IN (" . sqlPlaceholder($s) .")
    AND import_status = 'live'",
    $s
);
$r = $wpdb->get_results($sql);
if (current_user_can( 'administrator' )) {
    n(json_encode(array_column($r,'story_id')));
}
n(count($r) . " - " . count($s));
n("</pre>");