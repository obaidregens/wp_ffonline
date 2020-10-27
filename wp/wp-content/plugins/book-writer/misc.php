<?php
function fuzz($num) {
	$num = intval($num);
	if ($num === 0) {
		return 0;
	}
	if ($num <= 4) {
		$fuzz = 2;
	} else {
		$fuzz = ceil(sqrt($num));
	}
	$rand = mt_rand($fuzz*-1,$fuzz);
	return $num + $rand;
}
function get_anon_token($tok) {
	return explode('-',$tok,2)[1];
}
function anon_token($token,$len = 30) {
	if (!$token){
		// b so token can never be int and actually confused
        $token = "b" . bin2hex(random_bytes(4));
    }
    $addLen = ($len-1) - strlen(strval($token));
    $prepend = substr(bin2hex(random_bytes($addLen)),0,$addLen);
	$tok = $prepend . "-" . $token;
	return $tok;
}
function number_abbr($num) {
	if ($num < 1000) {
		return strval($num);
	}
	$defs = [
		[
			"abbr"		=> "K",
			"num"		=> 1000,
			"till"		=> 100,
			"decimals"	=> 1,
		],
		[
			"abbr"		=> "K",
			"num"		=> 1000,
			"till"		=> 1000,
			"decimals"	=> 0,
		],
		[
			"abbr"		=> "M",
			"num"		=> 1000000,
			"till"		=> 100,
			"decimals"	=> 1,
		],
		[
			"abbr"		=> "M",
			"num"		=> 1000000,
			"till"		=> INF,
			"decimals"	=> 0,
		],
	];
	foreach ($defs as $def) {
		if ( $num < ($def['till']*$def['num']) ) {
			$vals = explode(".",strval(round($num/$def['num'],$def['decimals'])));
			$dec = $def['decimals'] > 0 ? "." : "";
			$vals[1] = $vals[1] ?? "0";
			return $vals[0].$dec.substr($vals[1],0,$def['decimals']).$def['abbr'];
		}
	}
	return false;
}

remove_filter( 'pre_term_name', 'sanitize_text_field' );
remove_filter( 'pre_term_name', 'wp_filter_kses' );
remove_filter( 'pre_term_name', '_wp_specialchars', 30 );

function script_string($str) {
	return "'" . str_replace('/','\/',addslashes($str)) . "'";
}
function script_json($json) {
	$json = json_encode(json_decode($json));
	return $json;
}
function sqlPlaceholder($a,$type = '%s') {
	return implode(',',array_fill(0,count($a),$type));
}
function millitime(){
	return round(microtime(true) * 1000);
}
// Author Link
add_filter( 'author_link', function($link,$author_id,$author_nicename){
	return home_url( '@' ) . get_the_author_meta( 'user_login', $author_id );
}, 10, 3 );

add_filter('flush_rewrite_rules_hard','__return_false');

function mark_search($in, $search, $trim = null) {
	$should_trim = ! ($trim === null || strlen($in) <= $trim);
	$unmarked_trim = $should_trim ? substr($in, 0, $trim) . '...' : $in;
	if ($search === ''){
		return $unmarked_trim;
	}
	$pos = stripos($in, $search);
	if ($pos === false) {
		return $unmarked_trim;
	}
	if ($should_trim) {
		$in = substr($in, max(array($pos - (($trim/2) - strlen($search)) ,0)));
		$in = substr($in, 0, $trim);
		$pos = stripos($in, $search);
	}
	$occurrences = substr_count(strtolower($in),strtolower($search));
	$full = '';
	$trimmed = $in;
	for ($i=0; $i < $occurrences; $i++) {
		$pos = stripos($trimmed, $search);
		$trimmed = substr_replace($trimmed, '<mark>', $pos, 0);
		$trimmed = substr_replace($trimmed, '</mark>', $pos + 6 + strlen($search), 0);
		$full .= substr($trimmed,0,$pos + 6 + strlen($search) + 7);
		$trimmed = substr($trimmed,$pos + 6 + strlen($search) + 7);
	}
	$full .= $trimmed;
	return $should_trim ? $full . '...' : $full;
}
function print_book_tags($book_id,$book_query) {
	$ref = &$book_query->book_tags[$book_id];
	$fandom = $ref['fandom'];
	unset($ref['fandom']);
	$ref = array_merge(['fandom'=>$fandom],$ref);
	?>
	<tags>
	<?php
	foreach ($ref as $taxonomy => $terms){
		?>
		<tag-group name="<?= ucfirst($taxonomy) ?>">
			<?= implode('',array_column($terms,'link')); ?>
		</tag-group>
		<?php
	}
	?>
	</tags>
	<?php
}
function print_book_meta($book_id) {
	$collections = collection_books::query_by('book_id',$book_id);
	$collections = collection::query([
		'id_included'	=> array_column($collections,'collection_id'),
		'types'			=> ['Favorites','Public'],
	]);
	$words = get_post_meta($book_id,'word-count',true);
	?>
	<book-meta>
		<span tooltip-top="Updated"><?= get_the_time('',$book_id); ?></span>
		<span tooltip-top="<?= number_format($words) ?> Words"><?= number_abbr($words); ?></span>
		<span tooltip-top="Collections"><?= count($collections); ?></span>
		<span tooltip-top="Votes"><?= count(vote::query_by('story','type_id',$book_id)); ?></span>
	</book-meta>
	<?php
}
function a_intersect($arrayOne, $arrayTwo){
    $index = array_flip($arrayOne);
    $second = array_flip($arrayTwo);

    $x = array_intersect_key($index, $second);

    return array_flip($x);
}
function author_href($_post_id){
	$book = get_post($_post_id);
	$_author = intval($book->post_author);
	$a_href = "";
	if ($_author === 37){
		// Author Name
		$old_name = get_post_meta( $book->ID,'source_author_name',true );
		$new_name = get_post_meta( $book->ID,'author_name',true );
		$tname = $new_name !== '' ? $new_name : $old_name;

		$new_link = get_post_meta( $book->ID,'ffn_author_id',true );
		if ($new_link !== ''){
			return "<a href=\"/ffn@$new_link\">$tname</a>";
		}
		$old_link = get_post_meta( $book->ID,'source_author_link',true );
		return "<a rel=\"nofollow\" href=\"$old_link\">$tname</a>";
	}
	$a_href .= '<a href="' . get_author_posts_url($_author) . '">' . get_the_author_meta( 'display_name', $_author ) . '</a>';
	return $a_href;
}
function author_name_single($_post_id){
	$book = get_post($_post_id);
    $_author = intval($book->post_author);

	$ffonline_name = get_the_author_meta( 'display_name',$_author );
	if ($_author === 37){
		$old_name = get_post_meta( $book->ID,'source_author_name',true );
		$new_name = get_post_meta( $book->ID,'author_name',true );
		return $new_name !== '' ? $new_name : ($old_name !== '' ? $old_name : $ffonline_name);
	}
	return $ffonline_name;
}

function username_regex_valid($string){
	if (strlen(preg_replace ('/(\d)|(\.)|(_)|([A-Z])+/i','',$string)) > 0){
		return false;
	}
	return true;
}
function title_regex_valid($string){
	if (strlen(preg_replace ('/(\d)|(\.)|( )|(\-)|(\?)|(\_)|([A-Z])+/i','',$string)) > 0){
		return false;
	}
	return true;
}

function username_possible($username){
	if (strlen($username) < 5 || strlen($username) > 20){
		return false;
	}
	if (! username_regex_valid($username)){
		return false;
	}
	if (username_exists($username)){
		return false;
	}
	return true;
}
function verify_reCAPTCHA($response){
    $postdata = http_build_query(
        array(
            'secret' => '6Lc_ROEUAAAAADrnccSNHPP3whrLFAM5qrPAK4-S',
            'response' => $response
        )
    );
    $opts = array('http' =>
        array(
            'method'  => 'POST',
            'header'  => 'Content-Type: application/x-www-form-urlencoded',
            'content' => $postdata
        )
    );
    $context  = stream_context_create($opts);
    $result = json_decode(file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context),true);
	return $result;
}



function time_format_change($time,$format,$post)
{
	return human_time_diff(get_post_modified_time('U',false,$post));
}
add_filter( 'get_date', "time_format_change", 100, 3);
add_filter( 'get_the_date', "time_format_change", 100, 3);
add_filter( 'get_the_time', "time_format_change", 100, 3);
add_filter( 'post_date_column_time' , 'time_format_change', 100, 3);
function time_form($status)
{
	return "Updated";
}
add_filter( 'post_date_column_status', 'time_form', 99);
