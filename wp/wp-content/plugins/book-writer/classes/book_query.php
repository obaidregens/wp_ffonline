<?php
// The idea is that we use packed data, with some alterations,
// (like making included/excluded parents instead of a suffix)
// the standard, and only use WP_Query for books within the class.
// After caching, of course.
function json_or_serialize_decode($packed) {
    $unserialize = unserialize($packed);
    if ($unserialize !== false ) {
        return $unserialize;
    }
    return json_decode($packed,true);
}
class book_query{
    protected static $table = 'search_cache';
    protected static $taxonomies = array('genre','fandom','language','status','character','pairing','rating','tag');
    protected static $words = [
        0,500,1000,2000,
        5000,10000,15000,20000,30000,40000,50000,75000,
        100000,150000,200000,300000,500000,
        1000000,2000000,3000000
    ];
    protected static $default_args = array(
        'included'      => array(

        ),
        'excluded'      => array(

        ),
        'search'        => '',
        'order'         => 'DESC',
        'orderby'       => 'updated',
        'words'         => array(
            'from'  => 0,
            'to'    => 3000000
        ),
        'per_page'      => 10,
        'page'          => 1
    );
    protected static $wp_base_args = array(
        'post_type'              => array( 'book' ),
        'post_status'            => array( 'publish' ),
        'posts_per_page'		 => 10,
    );
    protected function book_tags(){
        $terms_ = (new WP_Term_Query(array(
            'object_ids'	=> array_column($this->books,'ID'),
            'orderby'		=> 'term_group',
            'fields'        => 'all_with_object_id'
        )))->terms;
        $tax = [];
        foreach ($terms_ as $term ) {
            $term->taxonomy = $term->taxonomy === 'category' ? 'fandom' : $term->taxonomy;
            $tax[$term->object_id][$term->taxonomy][] = array(
                'ID'        => $term->term_id,
                'name'      => $term->name,
                'link'      => '<a href="/?' . $term->taxonomy . '_included=' . $term->term_id . '">' . $term->name . '</a>',
            );
        }
        return $tax;
    }
    function __construct($args = null){
        if (! is_array($args)){
            return;
        }
        $this->args = $args;
        $this->query();
    }
    function query(){
        $args = $this->args;
        $args = array_replace_recursive(book_query::$default_args,$args);
        $all_tags = array_merge_recursive($args['included'],$args['excluded']);
        $prepared = array(
            'sort',
            $args['orderby'] . '/' . $args['order'],
            'words',
            $args['words']['from'],
            'words',
            $args['words']['to']
        );
        // Meant when cache for searching is enabled
        // if ($args['search'] !== ''){
        //     $prepared[] = 'search';
        //     $prepared[] = $args['search'];
        // }
        foreach ( $all_tags as $key => $tag_ids ){
            foreach ( $tag_ids as $value ) {
                $prepared[] = $key;
                $prepared[] = $value;
            }
        }
        $fill = implode(',',array_fill(0,count($prepared)/2,'(%s,%s)'));
        $query =
        "SELECT * FROM " . book_query::$table . " WHERE (`_key`, `_value`) IN (
            " . $fill . " 
        );";
        global $wpdb;
        $full_query = $wpdb->prepare($query,$prepared);
        $results = $wpdb->get_results($full_query);
        $results_ = array();
        foreach ($results as $value) {
            $results_[$value->_key . '=' . $value->_value] = json_or_serialize_decode($value->ids);
        }
        // Words
        $included = array_merge(
            array_diff(
                $results_['words=' . $args['words']['from']],
                $results_['words=' . $args['words']['to']]
            ),
            a_intersect(
                $results_['words=' . $args['words']['from']],
                $results_['words=' . $args['words']['to']]
            )
        );
        //Included
        foreach ($args['included'] as $key => $ids) {
            foreach ($ids as $id ) {
                $akey = $key . '=' . $id;
                $included = a_intersect($included, $results_[$akey] ?? array() );
            }
        }
        // Excluded
        $excluded = array();
        foreach ($args['excluded'] as $key => $ids) {
            foreach ($ids as $id ) {
                $akey = $key . '=' . $id;
                $excluded = array_merge($excluded,$results_[$akey] ?? array() );
            }
        }
        $included = array_diff($included,$excluded);
        // Search
        if ($args['search'] !== ''){
            $search = $args['search'];
            $search_sql = '%' . $search . '%';
            global $wpdb;
            $search_result = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT ID FROM " . $wpdb->prefix . "posts
                    WHERE post_title LIKE %s
                    OR post_excerpt LIKE %s",
                    array($search_sql,$search_sql)
                )
            );
            $included = a_intersect($included,array_column($search_result,'ID'));
        }
        
        // Sort
        $included = a_intersect($results_['sort=' . $args['orderby'] . '/' . $args['order'] ],$included);
        // Custom Ids
        if (isset($args['include_ids'])){
            $included = a_intersect($included,$args['include_ids']);
        }
        if (isset($args['exclude_ids'])){
            $included = array_diff($included,$args['exclude_ids']);
        }

        // Page
        $paged_ids = $included;
        if ($args['per_page'] !== 'all'){
            $paged_ids = array_slice(
                $included,
                ($args['page']-1)*$args['per_page'],
                $args['per_page']
            );    
        }
        $this->args = $args;
        $this->ids = $included;
        $this->count = count($this->ids);
        $this->pages = $this->count % 10 > 0 ? intval($this->count / 10)+1 : intval($this->count / 10);
        $this->books = array();
        if (! empty($paged_ids)){
            $fill = implode(',',array_fill(0,count($paged_ids),'%d'));
            $this->books = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM " . $wpdb->prefix . "posts
                    WHERE ID IN(" . $fill . ")
                    ORDER BY FIELD(ID, " . $fill . ")",
                array_merge($paged_ids,$paged_ids)
            ));    
        }
        $this->book_tags = $this->book_tags();
    }
    function args_from_url($url = true){
        $args = array(
            'included'  => array(),
            'excluded'  => array()
        );
        $url = $url === true ? $_SERVER['QUERY_STRING'] : $url;
        $url = urldecode($url);
        $groups = explode('&',$url);
        $types = array('included','excluded');
        foreach ( $groups as $group ) {
            $arr = explode('=',$group);
            if (count($arr) < 2){
                continue;
            }
            foreach ($types as $type ) {
                $key = substr($arr[0],0,-9);
                $suffix = substr($arr[0],-9,9);
                if ($suffix === '_' . $type){
                    if (! in_array($key,book_query::$taxonomies)){
                        continue;
                    }
                    $args[$type][$key] = explode( ',', $arr[1] );
                }
                else if ($arr[0] === 'words'){
                    $array = explode(',',$arr[1]);
                    if (count($array) <= 1 ||
                        ! is_numeric( $array[0] ) ||
                        ! is_numeric( $array[1] )
                    ){
                        continue;
                    }
                    $words = self::$words;
                    sort($words);
                    foreach ($words as $key => $num) {
                        $this_diff_0 = abs(intval($array[0]) - $num);
                        $this_diff_1 = abs(intval($array[1]) - $num);
                        $next_diff_0 = abs(intval($array[0]) - ($words[$key+1] ?? 0) );
                        $next_diff_1 = abs(intval($array[1]) - ($words[$key+1] ?? 0) );
                        if ($this_diff_0 <= $next_diff_0 && ! isset($word_0)){
                            $word_0 = $num;
                        }
                        if ($this_diff_1 <= $next_diff_1 && ! isset($word_1)){
                            $word_1 = $num;
                        }
                    }
                    $args['words'] = array(
                        'from'  => $word_0,
                        'to'    => $word_1
                    );
                }
                else if ($arr[0] === 'sort'){
                    $array = explode('/',$arr[1]);
                    if (count($array) <= 1){
                        continue;
                    }
                    $args['orderby'] = in_array($array[0],array('updated','words','favorites')) ? $array[0] : 'updated';
                    $args['order'] = in_array($array[1],array('DESC','ASC')) ? $array[1] : 'DESC';
                }
                else if ($arr[0] === 'page' && is_numeric($arr[1]) ){
                    $args['page'] = intval($arr[1]);
                }
                else if ($arr[0] === 'search'){
                    $args['search'] = $arr[1];
                }
            }
        }
        $this->args = $args;
    }
    function get_url($page = null){
        $args = $this->args;
        if ($page !== null){
            $args['page'] = $page;
        }
        $get = array();
        $types = array('included','excluded');
        foreach ( $types as $type ) {
            foreach ($args[$type] as $key => $ids ) {
                $get[] = $key . '_' . $type . '=' . implode(',',$ids);
            }
        }
        $string = implode('&',$get);
        $string .= isset($args['page']) ? '&page=' . $args['page'] : '';
        $string .= isset($args['order']) || isset($args['orderby']) ? '&sort=' . ($args['orderby'] ?? 'updated') . '/' . ($args['order'] ?? 'DESC') : '';
        $string .= isset($args['words']) ? '&words=' . $args['words']['from'] . ',' . $args['words']['to'] : '';
        $string .= isset($args['search']) ? '&search=' . $args['search'] : '';
        return $string;
    }
    function has(){
        return (isset($this->books) && ! empty($this->books));
    }
}
class book_query_cache extends book_query {
    protected static $midfix = '</--/>';
    function __construct(){
        $min_gap_min = 180;
        $min_gap = $min_gap_min * 60;

        global $wpdb;
        $results = $wpdb->get_results("SELECT * FROM " . self::$table);
        $t = time();
        $this->existing = array();
        foreach ($results as $value) {
            if ($t - intval($value->updated) <= $min_gap){
                $this->existing[$value->_key . self::$midfix . $value->_value] = 0;
            }
        }
    }
    function sort(){
        $orders = array('DESC','ASC');
        foreach ($orders as $order) {
            // Updated
            $term_key = 'sort' . self::$midfix . 'updated/' . $order;
            if (! isset($this->existing[$term_key]) ){
                $sort_ids = (new WP_Query(array_replace(book_query::$wp_base_args,array(
                    'posts_per_page'    => -1,
                    'fields'            => 'ids',
                    'orderby'           => 'modified',
                    'order'             => $order
                ))))->posts;
                self::put(array(
                    array(
                        '_key'      => 'sort',
                        '_value'    => 'updated/' . $order,
                        'ids'       => $sort_ids
                    )
                ));    
            }
            // Created
            $term_key = 'sort' . self::$midfix . 'date/' . $order;
            if ( ! isset($this->existing[$term_key]) ){
                $sort_ids = (new WP_Query(array_replace(book_query::$wp_base_args,array(
                    'posts_per_page'    => -1,
                    'fields'            => 'ids',
                    'orderby'           => 'date',
                    'order'             => $order
                ))))->posts;
                self::put(array(
                    array(
                        '_key'      => 'sort',
                        '_value'    => 'date/' . $order,
                        'ids'       => $sort_ids
                    )
                ));    
            }
            // Words
            $term_key = 'sort' . self::$midfix . 'words/' . $order;
            if ( ! isset($this->existing[$term_key]) ){
                $sort_ids = (new WP_Query(array_replace(book_query::$wp_base_args,array(
                    'posts_per_page'    => -1,
                    'fields'            => 'ids',
                    'meta_key'          => 'word-count',
                    'orderby'           => 'meta_value_num',
                    'order'             => $order
                ))))->posts;
                self::put(array(
                    array(
                        '_key'      => 'sort',
                        '_value'    => 'words/' . $order,
                        'ids'       => $sort_ids
                    )
                ));    
            }
        }
    }
    function tax(){
        global $wpdb;
        $ids = json_or_serialize_decode($wpdb->get_results("SELECT ids FROM " . self::$table . " WHERE _key = 'words' AND _value = '0'")[0]->ids);
        $terms = (new WP_Term_Query(array(
            'taxonomy'		=> array('category','rating','language','status','genre','character','pairing','tag'),
			'object_ids'    => $ids,
			'fields'        => 'all_with_object_id'
        )))->terms;
        $fandoms_of_chars = [];
        $key_value_ids = [];
        foreach ( $terms as $term ) {
            $term->taxonomy = $term->taxonomy === 'category' ? 'fandom' : $term->taxonomy;
            if ($term->taxonomy === 'character'){
                $fandoms_of_chars[intval($term->term_id)] = intval($term->parent);
            }
            $term_key = $term->taxonomy . self::$midfix . $term->term_id;
            if (isset($this->existing[$term_key])){
                continue;
            }
            if (! isset($key_value_ids[$term_key])){
                $key_value_ids[$term_key] = array(
                    '_key'      => $term->taxonomy,
                    '_value'    => $term->term_id,
                    'ids'       => array()
                );
            }
            $key_value_ids[$term_key]['ids'][] = $term->object_id;
        }
        $term_key = 'character_fandoms' . self::$midfix . 'all';
        if (! isset($this->existing[$term_key])){
            $key_value_ids[$term_key] = array(
                '_key'      => 'character_fandoms',
                '_value'    => 'all',
                'ids'       => $fandoms_of_chars
            );    
        }
        self::put($key_value_ids);
    }
    function author(){
        global $wpdb;
        $books_all = $wpdb->get_results(
            "SELECT ID, post_author FROM " . $wpdb->prefix . "posts
            WHERE post_type = 'book'
            AND post_status = 'publish'"
        );
        $key_value_ids = array();
        foreach ( $books_all as $book ) {
            $term_key = 'author' . self::$midfix . $book->post_author;
            if (isset($this->existing[$term_key])){
                continue;
            }
            if (! isset($key_value_ids[$term_key])){
                $key_value_ids[$term_key] = array(
                    '_key'       => 'author',
                    '_value'     => $book->post_author,
                    'ids'       => array()
                );
            }
            $key_value_ids[$term_key]['ids'][] = $book->ID;
        }
        self::put($key_value_ids);
    }
    function words(){
        //Words
        $word_limits = self::$words;
        foreach ($word_limits as $from) {
            $term_key = 'words' . self::$midfix . $from;
            if (isset($this->existing[$term_key])){
                continue;
            }
            $word_ids = (new WP_Query(array_replace(book_query::$wp_base_args,array(
                'posts_per_page'    => -1,
                'fields'            => 'ids',
                'meta_query'        => array(
                    'relation' => 'AND',
                    array(
                        'key'     => 'word-count',
                        'value'   => $from,
                        'compare' => '>=',
                        'type'    => 'NUMERIC',
                    ),
                )
            ))))->posts;
            self::put(array(
                array(
                    '_key'        => 'words',
                    '_value'      => $from,
                    'ids'         => $word_ids
                )
            ));
        }
    }
    function tag_names(){
        $term_key = 'tag_names' . self::$midfix . 'all';
        if (isset($this->existing[$term_key])){
            return;
        }
        $terms =  (new WP_Term_Query(array(
            'taxonomy'		=> array('category','rating','language','status','genre','character','pairing','tag'),
            'fields'        => 'id=>name'
        )))->terms;
        self::put(array(array(
            '_key'      => 'tag_names',
            '_value'    => 'all',
            'ids'       => $terms
        )));
    }
    function ffn_author() {
        global $wpdb;
        $all_meta = $wpdb->get_results("SELECT * FROM wp_postmeta WHERE meta_key = 'ffn_author_id'");
        $key_value_ids = array();
        foreach ( $all_meta as $meta ) {
            $term_key = 'ffn_author' . self::$midfix . $meta->meta_value;
            if (isset($this->existing[$term_key])){
                continue;
            }
            if (! isset($key_value_ids[$term_key])){
                $key_value_ids[$term_key] = array(
                    '_key'       => 'ffn_author',
                    '_value'     => $meta->meta_value,
                    'ids'       => array()
                );
            }
            $key_value_ids[$term_key]['ids'][] = $meta->post_id;
        }
        self::put($key_value_ids);
    }
    public static function put($key_value_ids){
        global $wpdb;
        $t = time();
        foreach ($key_value_ids as $term) {
            $term['updated'] = $t;
            $term['ids']     = serialize($term['ids']);
            $wpdb->replace(
                book_query::$table,
                $term
            );
        }
    }
}
class tag_query extends book_query{
    function __construct($book_ids){
        $no_cache_hours = 48;

        $book_ids_hash = md5(serialize($book_ids));
        global $wpdb;
        $prepared = $wpdb->prepare(
            "SELECT * FROM " . self::$table . "
            WHERE `_key` = %s
            AND `_value` = %s "
        ,array('tags_of',$book_ids_hash));
        $existing = $wpdb->get_results($prepared);
        if (! empty($existing) && (time() - intval($existing[0]->updated)) <= ($no_cache_hours*60*60) ){
            $this->terms_with_count = json_or_serialize_decode($existing[0]->ids);
            return;
        }
        $results = $wpdb->get_results(
            "SELECT * FROM " . self::$table . "
            WHERE `_key` IN ('" . implode('\', \'',self::$taxonomies) . "','tag_names','character_fandoms')"
        );
        // Tag Names
        $index_of_names = array_search('tag_names',array_column($results,'_key'));
        $tag_names = json_or_serialize_decode($results[$index_of_names]->ids);
        unset($results[$index_of_names]);
        sort($results);

        // Character Fandom
        $index_of_char_fandoms = array_search('character_fandoms',array_column($results,'_key'));
        $character_fandoms = json_or_serialize_decode($results[$index_of_char_fandoms]->ids);
        unset($results[$index_of_char_fandoms]);
        sort($results);

        $terms_with_count = [];
        foreach ($results as $term) {
            $term->_value = intval($term->_value);
            if (! isset($tag_names[$term->_value])){
                continue;
            }
            if ($term->_key === 'character'){
                $ref = &$terms_with_count[$term->_key][$character_fandoms[$term->_value]][$term->_value];
            }
            else {
                $ref = &$terms_with_count[$term->_key][$term->_value];
            }
            $ref = array(
                'count'         => count(a_intersect($book_ids,json_or_serialize_decode($term->ids))),
                'name'          => $tag_names[$term->_value]
            );
        }
        $this->terms_with_count = $terms_with_count;
        book_query_cache::put(array(array(
            '_key'      => 'tags_of',
            '_value'    => $book_ids_hash,
            'ids'       => $terms_with_count,
        )));
    }
}