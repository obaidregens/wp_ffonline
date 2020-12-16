<?php
function is_current_user($user_id) {
	return intval($user_id) === intval(get_current_user_id());
}
function setup_book_query() {
	$query = new book_query;
	$query->args_from_url();
	$query->args['is_search'] = true;
    $placeholder = _landing::get_type();
    $query->args = type_args($query->args,$placeholder);
    if (err::is($query->args)){
        $app->_404();
    }
	$query->query();
	global $app;
    $app->book_query = $query;
}

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
	<main-tags>
	<?php
	foreach (['fandom','rating','language','status'] as $taxonomy ){
		$terms = &$ref[$taxonomy];
		?>
		<tag-group name="<?= ucfirst($taxonomy) ?>">
			<?= implode('',array_column($terms,'link')); ?>
		</tag-group>
		<?php
	}	
	?>
	</main-tags>
	<tags>
	<?php
	foreach (['genre','character','pairing','tag'] as $taxonomy ){
		$terms = &$ref[$taxonomy];
		if (!isset($terms)) {
			continue;
		}
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
function print_book_meta($book_id,$book_query) {
	$story = story::get($book_id,false);
	$ref = $book_query->book_metas[$book_id];
	$words = $ref['words'];
	if (current_user_can( 'administrator' )) {
		global $wpdb;
		$r = $wpdb->get_results($wpdb->prepare("SELECT * FROM import_stories WHERE story_id = %d",[$book_id]));
		if (!empty($r)) {
			?>
			<book-meta>
				<span tooltip-top="FFN Follows"><?= $r[0]->import_follows; ?></span>
				<span tooltip-top="FFN Favs"><?= $r[0]->import_favs; ?></span>
			</book-meta>
			<?php	
		}
	}
	?>
	<book-meta>
		<span tooltip-top="Updated"><?= human_time_diff(strtotime($story->post_modified)); ?></span>
		<span tooltip-top="<?= number_format($words) ?> Words"><?= number_abbr($words); ?></span>
		<span tooltip-top="Collections"><?= $ref['collections']; ?></span>
		<span tooltip-top="Votes"><?= $ref['votes']; ?></span>
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
	$book = story::get($_post_id,false,false);
	$_author = intval($book->post_author);
	$user = user::get($_author);

	$a_href = "";
	if ($_author === 37){
		// Author Name
		$old_name = get_post_meta( $book->ID,'source_author_name',true );
		$new_name = get_post_meta( $book->ID,'author_name',true );
		$tname = $new_name !== '' ? $new_name : $old_name;

		$new_link = get_post_meta( $book->ID,'ffn_author_id',true );
		if ($new_link !== ''){
			return "<a href='/ffn@$new_link'>$tname</a>";
		}
		$old_link = get_post_meta( $book->ID,'source_author_link',true );
		return "<a rel='nofollow' href='$old_link'>$tname</a>";
	}
	$a_href .= '<a href="/@' . $user->user_login . '">' . $user->display_name . '</a>';
	return $a_href;
}
function author_name_single($_post_id){
	$book = story::get($_post_id,false);
    $_author = intval($book->post_author);

	$ffonline_name = get_the_author_meta( 'display_name',$_author );
	if ($_author === 37){
		$old_name = get_post_meta( $book->ID,'source_author_name',true );
		$new_name = get_post_meta( $book->ID,'author_name',true );
		return $new_name !== '' ? $new_name : ($old_name !== '' ? $old_name : $ffonline_name);
	}
	return $ffonline_name;
}

function verify_reCAPTCHA($response){
    $postdata = http_build_query(
        array(
            'secret' => RECAPTCHA_SECRET,
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
function logging(...$datas) {
    foreach ($datas as $data ) {
        file_put_contents(MAIN_DIR . '/logging.txt', json_encode($data) . "\r\n",FILE_APPEND);
    }
    file_put_contents(MAIN_DIR . '/logging.txt', "\r\n",FILE_APPEND);
}
function timer($logtext,$echo = false){
    global $lastlogtime;
    $now = microtime(true);
    $diff = $now - ($lastlogtime ?? $now);
	file_put_contents(MAIN_DIR . '/content/time_logger.txt', $logtext . ": " . $diff . "\r\n", FILE_APPEND );
	if ($echo === true){
		echo $logtext . ": " . $diff . "<br>";
	}
    $lastlogtime = microtime(true);
}
class arr {
    public static function non_empty ($arr,$reindex = true){
        foreach($arr as $k => $v){
            if ($v === ''){
                unset($arr[$k]);
            }
        }
        return $reindex ? array_values($arr) : $arr;
    }
    static function remove(&$arr,$val,bool $strict = false,bool $reindex = true) {
        $i = array_search($val,$arr,$strict);
        if ($i === false){
            return;
        }
        unset($arr[$i]);
        return array_values($arr);
    }
}
class str {
    static function is_lower($txt) {
        return strtolower($txt) === $txt;
    }
}
function roundToNextHour($stamp) {
    $date = new DateTime( );
    $date->setTimestamp( intval($stamp) );

    $minutes = $date->format('i');
    if ($minutes > 0) {
        $date->modify("+1 hour");
        $date->modify('-'.$minutes.' minutes');
    }
    return intval($date->getTimestamp());
}
function DEV() {
    return defined('GLOBAL_ENV') && GLOBAL_ENV === 'DEV';
}
function STATIC_URL() {
    return defined("STATIC_URL") ? rtrim(STATIC_URL,'/') . '/' : "https://static.fanfiction.online/";
}
function is_test_story($id) {
    $a = get_post($id);
    if ($a->post_type === "chapter") {
        $a = get_post($a->post_parent);
    }
    $admin = intval(user::get_by('login','admin')->ID);
    return
        intval($a->post_author) === $admin &&
        $a->post_type === "book" &&
        $a->post_status === "draft" &&
        $a->post_title === "Test Story";
}
function query_log($display_all = false) {
    if (!current_user_can('administrator')) {
        return false;
    }
    global $wpdb;
    if ($display_all) {
        ?>
        <style>#query-log{white-space:break-spaces !important;}</style>
        <pre id="query-log"><?php print_r($wpdb->queries); ?></pre><?php
	}
	$c = 0;
	foreach ($wpdb->queries as $q ) {
		if (strpos($q[2],'book_metas') !== false) {
			$c += $q[1];
		}
	}
	echo "Meta Time: $c<br>";
	echo "DB Calls on this page: " . $wpdb->num_queries . "<br>";
	$queries_time = array_sum(array_column($wpdb->queries,1)); 
	echo "Total DB Query Time: $queries_time<br>";
	$page_load = (microtime(true) - BEGIN_PAGE_RENDER);
	echo "Total Page Load: $page_load<br>";
	echo "Without Page Query: " . ($page_load - $queries_time) . "<br>";
	global $app;
	echo "Book Query Time: " . ($app->book_query->query_time ?? null) . "<br>";
}
function out($var,$dump = false) {
	?>
	<style>#id-output{white-space: break-spaces !important;}</style>
	<pre id="id-output"><?php
	if ($dump) {
		var_dump($var);
	} else {
		print_r($var);
	}
	?></pre>
	<?php
}
function column_sort($a,$column) {
	$counts = array_combine(array_keys($a),array_column($a,$column));
	asort($counts);
	$correctly_indexed_keys = array_keys($counts);
	$new_array = [];
	foreach($correctly_indexed_keys as $k) {
		$new_array[$k] = $a[$k];
	}
	return $new_array;
}
