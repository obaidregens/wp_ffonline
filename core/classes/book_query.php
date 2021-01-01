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
    public static $taxonomies = [
        'genre',
        'fandom',
        'language',
        'status',
        'character',
        'pairing',
        'rating',
        'tag'
    ];
    protected static $words = [
        0,500,1000,2000,
        5000,10000,15000,20000,30000,40000,50000,75000,
        100000,150000,200000,300000,500000,
        1000000,2000000,3000000
    ];
    protected static $default_args = array(
        'included'      => [],
        'excluded'      => [],
        'search'        => '',
        'author'        => '',
        'order'         => 'DESC',
        'orderby'       => 'updated',
        'words'         => [
            'from'  => 0,
            'to'    => 3000000
        ],
        'per_page'      => 10,
        'page'          => 1,
        'include_ids'   => null,
        'exclude_ids'   => null,
        'is_search'     => false
    );
    protected static $wp_base_args = array(
        'post_type'              => array( 'book' ),
        'post_status'            => array( 'publish' ),
        'posts_per_page'		 => 10,
    );
    protected $init_time = 0;
    protected function book_tags(){
        $terms_ = (new WP_Term_Query(array(
            'object_ids'	=> array_column($this->books,'ID'),
            'taxonomy'      => array('genre','category','language','status','character','rating','tag'),
            'orderby'		=> 'term_group',
            'fields'        => 'all_with_object_id'
        )))->terms ?: [];
        $tax = [];
        foreach ($terms_ as $term ) {
            $term->taxonomy = $term->taxonomy === 'category' ? 'fandom' : $term->taxonomy;
            $esc_name = htmlspecialchars($term->name);
            $tax[$term->object_id][$term->taxonomy][] = [
                'ID'        => $term->term_id,
                'name'      => $esc_name,
                'link'      => '<a href="/read?' . $term->taxonomy . '_included=' . $term->term_id . '">' . $esc_name . '</a>',
            ];
        }
        $book_pairings = pairing::for_books(array_column($this->books,'ID'));
        foreach ($book_pairings as $book_pairing) {
            foreach ($book_pairing as $pairing ) {
                $name = implode('/',array_column($pairing->characters,"name"));
                $tax[$pairing->book_id]['pairing'][] = array(
                    'ID'        => $pairing->pairing_id,
                    'name'      => $name,
                    'link'      => '<a href="/read?pairing_included=' . $pairing->pairing_id . '">' . $name . '</a>',
                );
            }
        }
        return $tax;
    }
    protected function book_metas() {
        if (empty($this->books)) {
            return [];
        }
        $ids = array_column($this->books,'ID');
        $placeholder = sqlPlaceholder($ids,'%d');
        global $wpdb;
        $votes = array_column($wpdb->get_results($wpdb->prepare(
            "SELECT wp_posts.post_parent as book_id,COUNT(*) as votes
            FROM votes
            INNER JOIN wp_posts ON votes.type_id = wp_posts.ID
            WHERE votes.type = 'chapter'
            AND wp_posts.post_type = 'chapter'
            AND wp_posts.post_status = 'publish'
            AND wp_posts.post_parent IN(" . $placeholder . ")
            GROUP BY wp_posts.post_parent",
            $ids
        )),'votes','book_id');
        $collections = array_column($wpdb->get_results($wpdb->prepare(
            "SELECT
                wp_posts.ID as book_id,
                COUNT(*) as collections
            FROM wp_posts
            INNER JOIN collection_books ON wp_posts.ID = collection_books.book_id
            INNER JOIN collections ON collection_books.collection_id = collections.ID
            WHERE collections.type IN ('Favorites','Public')
            AND wp_posts.ID IN (" . $placeholder . ")
            GROUP BY wp_posts.ID",
            $ids
        )),'collections','book_id');
        $words = array_column($wpdb->get_results($wpdb->prepare(
            "SELECT
                wp_posts.ID as book_id,
                wp_postmeta.meta_value as words
            FROM wp_posts
            INNER JOIN wp_postmeta ON wp_posts.ID = wp_postmeta.post_id
            WHERE wp_postmeta.meta_key = 'word-count'
            AND wp_posts.ID IN (" . $placeholder . ")",
            $ids
        )),'words','book_id');
        $a = [];
        foreach ($words as $book_id => $word_count ) {
            $a[$book_id] = [
                'votes'         => $votes[$book_id] ?? 0,
                'collections'   => $collections[$book_id] ?? 0,
                'words'         => $word_count
            ];
        }
        return $a;
    }
    function __construct($args = null){
        if (! is_array($args)){
            return;
        }
        $this->args = $args;
        $this->query();
    }
    function query(){
        $this->init_time = microtime(true);
        $args = $this->args;
        $args = array_replace_recursive(book_query::$default_args,$args);
        $this->args = $args;
        $this->core_args = $this->core_args();
        
        if ($args['is_search']) {
            new search_log($this->core_args);
        }
        

        if ($args['is_search']) {
            $included = $this->from_cache();
        }
        global $wpdb;
        if (empty($included)) {
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
            $full_query = $wpdb->prepare($query,$prepared);
            $results = $wpdb->get_results($full_query);
            $results_ = array();
            foreach ($results as $value) {
                $results_[$value->_key . '=' . $value->_value] = json_or_serialize_decode($value->ids);
            }
            $results = null;
            // Words
            $included = array_diff(
                $results_['words=' . $args['words']['from']],
                $results_['words=' . $args['words']['to']]
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
            $excluded = null;
            // Search
            if ($args['search'] !== ''){
                $search = $args['search'];
                $search_sql = '%' . $search . '%';
                global $wpdb;
                $search_result = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT ID FROM wp_posts
                        WHERE post_type = 'book'
                        AND (
                            post_title LIKE %s
                            OR post_excerpt LIKE %s
                        )",
                        array($search_sql,$search_sql)
                    )
                );
                $included = a_intersect($included,array_column($search_result,'ID'));
            }
            // Author Search
            if ($args['author'] !== ''){
                $search = $args['author'];
                $search_sql = '%' . $search . '%';
                global $wpdb;
                $search_result = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT wp_posts.ID FROM wp_posts
                        INNER JOIN wp_users ON wp_posts.post_author = wp_users.ID
                        INNER JOIN wp_postmeta ON wp_postmeta.post_id = wp_posts.ID
                        WHERE post_type = 'book'
                        AND (
                            wp_users.display_name LIKE %s
                            OR (
                                wp_postmeta.meta_key = 'author_name'
                                AND wp_postmeta.meta_value LIKE %s 
                            )
                            OR (
                                wp_postmeta.meta_key = 'ffn_author_id'
                                AND wp_postmeta.meta_value = %s 
                            )
                        )",
                        array($search_sql,$search_sql,$search)
                    )
                );
                $included = a_intersect($included,array_column($search_result,'ID'));
            }
            $search_result = null;
            
            // Sort
            $included = a_intersect($results_['sort=' . $args['orderby'] . '/' . $args['order'] ],$included);

            $this->to_cache($included);
        }

        // Custom Ids
        if ($args['include_ids'] !== null){
            $included = a_intersect($included,$args['include_ids']);
        }
        if ($args['exclude_ids'] !== null){
            $included = array_diff($included,$args['exclude_ids']);
        }

        // Page
        $paged_ids = $included;
        if ($args['page'] < 1) {
            $paged_ids = [];
        }
        else if ($args['per_page'] !== 'all'){
            $paged_ids = array_slice(
                $included,
                ($args['page']-1)*$args['per_page'],
                $args['per_page']
            );    
        }
        $this->is_default = empty($this->core_args);
        $this->ids = $included;
        $included = null;
        $this->count = count($this->ids);
        $this->page = $args['page'];
        if ($args['per_page'] !== "all") {
            $this->pages = ($this->count % $args['per_page'] > 0) ? (intval($this->count / $args['per_page'])+1) : (intval($this->count / $args['per_page']));
        }
        $this->books = array();
        if (! empty($paged_ids)){
            $fill = implode(',',array_fill(0,count($paged_ids),'%d'));
            $this->books = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM wp_posts
                    WHERE ID IN(" . $fill . ")
                    AND post_status = 'publish'
                    ORDER BY FIELD(ID, " . $fill . ")",
                array_merge($paged_ids,$paged_ids)
            ));
            foreach ($this->books as $k => $v ) {
                $this->books[$k] = story::get($v,false,true);
            }
        }
        $this->book_tags = $this->book_tags();
        $this->book_metas = $this->book_metas();
        $this->query_time = microtime(true) - $this->init_time;
    }
    protected function core_args() {
        $core_args = [];
        $ignore = ['include_ids','exclude_ids','is_search','page','per_page'];
        foreach (self::$default_args as $k => $v) {
            $arg = $this->args[$k];
            if (in_array($k,$ignore)) {
                continue;
            }
            if ($v == $arg) {
                continue;
            }
            if ($k === "words") {
                if ($v['from'] != $arg['from']) {
                    $core_args[$k]['from'] = $arg['from'];
                }
                if ($v['to'] != $arg['to']) {
                    $core_args[$k]['to'] = $arg['to'];
                }
                continue;
            }
            if (in_array($k,['included','excluded'])) {
                foreach (book_query::$taxonomies as $tax ) {
                    if (!isset($arg[$tax])) {
                        continue;
                    }
                    if ( $k === "excluded" && empty($arg[$tax]) ) {
                        continue;
                    }
                    $core_args[$k][$tax] = $arg[$tax];
                }
                continue;
            }
            $core_args[$k] = $arg;
        }
        return $core_args;
    }
    protected function from_cache() {
        return false;
        $cache_time_min = 30;
        $cache_time = $cache_time_min*60;
        global $wpdb;
        $r = $wpdb->get_results($wpdb->prepare(
            "SELECT ids,updated FROM search_cache
            WHERE `_key` = 'query_ids'
            AND `_value` = %s",
            [serialize($this->core_args)])
        );
        if (empty($r) || (time() - intval($r[0]->updated)) > $cache_time){
            return false;
        }
        return unserialize($r[0]->ids);
    }
    protected function to_cache($included) {
        return;
        book_query_cache::put([[
            '_key'      => 'query_ids',
            '_value'    => serialize($this->core_args),
            'ids'       => $included,
        ]]);
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
                    $args['orderby'] = in_array($array[0],array('updated','words','votes','date','top')) ? $array[0] : 'updated';
                    $args['order'] = in_array($array[1],array('DESC','ASC')) ? $array[1] : 'DESC';
                }
                else if ($arr[0] === 'page' ){
                    $args['page'] = is_numeric($arr[1]) ? intval($arr[1]) : 0;
                }
                else if ($arr[0] === 'search'){
                    $args['search'] = $arr[1];
                }
                else if ($arr[0] === 'author'){
                    $args['author'] = $arr[1];
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
        $string .= isset($args['author']) ? '&author=' . $args['author'] : '';
        return $string;
    }
    function has(){
        return (isset($this->books) && ! empty($this->books));
    }
    function is_only_fandom() {
        if ($this->args == self::$default_args) {
            return false;
        }
        $clone = $this->args;
        unset($clone['included']['fandom']);
        if (empty($clone['exclude_ids'])) {
            unset($clone['exclude_ids']);
        }
        ?><style>pre{white-space:break-spaces !important;}</style><?php
        if ($clone != self::$default_args){
            return false;
        }
        return array_values($this->args['included']['fandom']);
    }
}
class book_query_cache extends book_query {
    protected static $midfix = '</--/>';
    function __construct(){
        $min_gap_min = 0.0000001;
        $min_gap = $min_gap_min * 60;

        global $wpdb;
        $results = $wpdb->get_results("SELECT * FROM " . self::$table . " WHERE _key != 'tags_of' AND _key != 'query_ids'");
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
            // Publish
            $term_key = 'sort' . self::$midfix . 'date/' . $order;
            if ( ! isset($this->existing[$term_key]) ){
                $sort_ids = (new WP_Query(array_replace(book_query::$wp_base_args,array(
                    'posts_per_page'    => -1,
                    'fields'            => 'ids',
                    'meta_key'          => 'first_publish',
                    'orderby'           => 'meta_value_num',
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
        $term_key = 'sort' . self::$midfix . 'votes/DESC';
        if (! isset($this->existing[$term_key])){
            global $wpdb;
            $r = array_column($wpdb->get_results("
                SELECT wp_posts.post_parent as story, COUNT(wp_posts.post_parent) as c
                FROM votes
                INNER JOIN wp_posts ON votes.type_id = wp_posts.ID
                WHERE votes.type = 'chapter'
                AND wp_posts.post_type = 'chapter'
                AND wp_posts.post_status = 'publish'
                GROUP BY wp_posts.post_parent
                ORDER BY c DESC
            "),'story');
            $word_ids = json_or_serialize_decode($wpdb->get_results("SELECT ids FROM search_cache WHERE _key = 'sort' AND _value = 'words/DESC'")[0]->ids);
            $diff = (array_diff($word_ids,$r));
            shuffle($diff);
            $r = array_merge($r,$diff);
            self::put([
                [
                    '_key'      => 'sort',
                    '_value'    => 'votes/DESC',
                    'ids'       => $r
                ],
                [
                    '_key'      => 'sort',
                    '_value'    => 'votes/ASC',
                    'ids'       => array_reverse($r)
                ]
            ]);    
        }
        // 
        $term_key = 'sort' . self::$midfix . 'top/DESC';
        if (! isset($this->existing[$term_key])){
            global $wpdb;
            $imported_ids = array_column($wpdb->get_results("
                SELECT
                    import_stories.story_id as s,
                    (import_stories.`import_favs` + import_stories.`import_follows`) as c
                FROM import_stories
                INNER JOIN wp_posts ON wp_posts.ID = import_stories.story_id
                WHERE wp_posts.post_type = 'book'
                AND wp_posts.post_status = 'publish'
                ORDER BY c DESC
            "),'s');
            $all_voted_ids = array_column($wpdb->get_results("
                SELECT
                    wp_posts.post_parent as story,
                    COUNT(wp_posts.post_parent) as c
                FROM votes
                INNER JOIN wp_posts ON votes.type_id = wp_posts.ID
                WHERE votes.type = 'chapter'
                AND wp_posts.post_type = 'chapter'
                AND wp_posts.post_status = 'publish'
                GROUP BY wp_posts.post_parent
                ORDER BY c DESC
            "),'story');
            $voted_or_imported = array_unique(array_merge($imported_ids,$all_voted_ids));
            $non_imported_no_votes = empty($voted_or_imported) ? [] : array_column($wpdb->get_results(
                $wpdb->prepare("
                    SELECT ID as story
                    FROM wp_posts
                    WHERE post_type = 'book'
                    AND post_status = 'publish'
                    AND ID NOT IN (" . sqlPlaceholder($voted_or_imported) . ")
                    ",
                    $voted_or_imported
                )
            ),'story');
            shuffle($non_imported_no_votes);
            $non_imported = array_merge($all_voted_ids,$non_imported_no_votes);
            $inserts = [];
            foreach ($non_imported as $id ) {
                $inserts[] = mt_rand(0, ceil(count($imported_ids)/4));
            }
            sort($inserts);
            foreach($non_imported as $k => $id){
                array_splice($imported_ids, $inserts[$k], 0, $id);
            }
            $imported_ids = array_unique($imported_ids);
            self::put([
                [
                    '_key'      => 'sort',
                    '_value'    => 'top/DESC',
                    'ids'       => $imported_ids
                ],
                [
                    '_key'      => 'sort',
                    '_value'    => 'top/ASC',
                    'ids'       => array_reverse($imported_ids)
                ]
            ]);    
        }
    }
    function tax(){
        global $wpdb;
        $ids = json_or_serialize_decode($wpdb->get_results("SELECT ids FROM " . self::$table . " WHERE _key = 'words' AND _value = '0'")[0]->ids);
        $terms = (new WP_Term_Query(array(
            'taxonomy'		=> array('category','rating','language','status','genre','character','tag'),
			'object_ids'    => $ids,
			'fields'        => 'all_with_object_id'
        )))->terms ?: [];
        $key_value_ids = [];
        foreach ( $terms as $term ) {
            $term->taxonomy = $term->taxonomy === 'category' ? 'fandom' : $term->taxonomy;
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
        global $wpdb;
        $a = array_column($wpdb->get_results("SELECT ID FROM wp_users WHERE user_status = 0"),"ID");
        //Words
        $word_limits = self::$words;
        foreach ($word_limits as $from) {
            $term_key = 'words' . self::$midfix . $from;
            if (isset($this->existing[$term_key])){
                continue;
            }
            $word_ids = (new WP_Query(array_replace(book_query::$wp_base_args,array(
                'author__in'        => $a,
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
        $tax_tags = [];
        foreach (self::$taxonomies as $tax ) {
            $term_key = 'tag_names' . self::$midfix . $tax;
            if ($tax === 'pairing' || isset($this->existing[$term_key])){
                continue;
            }
            $tax_tags[] = $tax === "fandom" ? "category" : $tax;
        }
        if (empty($tax_tags)) {
            return;
        }
        $terms_query = new WP_Term_Query([
            'taxonomy'		=> $tax_tags
        ]);
        $terms =  ($terms_query->terms ?? []) ?: [];
        $tn = [];
        foreach ($terms as $term ) {
            if ($term->taxonomy === 'category') {
                if (intval($term->parent) === 0) {
                    continue;
                }
                $term->taxonomy = 'fandom';
            }
            $tn[$term->taxonomy] = $tn[$term->taxonomy] ?? [
                '_key'  => 'tag_names',
                '_value'=> $term->taxonomy,
                'ids'   => []
            ];
            $tn[$term->taxonomy]['ids'][$term->term_id] = $term->name;
        }
        self::put(array_values($tn));
    }
    function pairing_tag_names(){
        $term_key = 'tag_names' . self::$midfix . 'pairing';
        if (isset($this->existing[$term_key])){
            return;
        }
        $pairings = pairing::query();
        $terms = [];
        foreach ($pairings as $pairing ) {
            $terms[$pairing->pairing_id] = implode('/',array_column($pairing->characters,'name'));
        }
        self::put(array(array(
            '_key'      => 'tag_names',
            '_value'    => 'pairing',
            'ids'       => $terms
        )));
    }
    function pairings() {
        global $wpdb;        
        $terms = pairing::query();
        foreach ( $terms as $term ) {
            $term_key = 'pairing' . self::$midfix . $term->pairing_id;
            if (isset($this->existing[$term_key])){
                continue;
            }
            self::put([[
                '_key'      => 'pairing',
                '_value'    => $term->pairing_id,
                'ids'       => $term->book_ids
            ]]);
        }
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
    protected $terms;
    protected $filtered;
    function __construct($tax,$book_ids){
        if (!in_array($tax,self::$taxonomies)) {
            $this->terms = [];
            return;
        }
        sort($book_ids);
        $no_cache_hours = 0.5;
        $no_cache_seconds = $no_cache_hours*60*60;
        $book_ids_hash = md5(serialize($book_ids));

        global $wpdb;
        $table = self::$table;
        $prepared = $wpdb->prepare(
            "SELECT * FROM $table
            WHERE `_key` = %s
            AND `_value` = %s "
        ,array("tags_of_$tax",$book_ids_hash));
        $existing = $wpdb->get_results($prepared);

        if (!empty($existing) && (time() - intval($existing[0]->updated)) <= $no_cache_seconds ){
            $this->terms = json_or_serialize_decode($existing[0]->ids);
            return;
        }
        $existing = null;

        $tag_names_key = 'tag_names';
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table
            WHERE `_key` = %s || (`_key` = %s AND `_value` = %s)",
            [$tax,$tag_names_key,$tax]
        ));
        // Remove Tag Names
        $index_of_names = array_search($tag_names_key,array_column($results,'_key'));
        $names = json_or_serialize_decode($results[$index_of_names]->ids);
        unset($results[$index_of_names]);
        $results = array_values($results);

        $terms_with_count = [];
        foreach ($results as $term) {
            $id = intval($term->_value);
            if (! isset($names[$id])){
                continue;
            }
            $terms_with_count[$id] = [
                'ID'            => $id,
                'name'          => $names[$id],
                'count'         => count(a_intersect($book_ids,json_or_serialize_decode($term->ids)))
            ];
        }

        $this->terms = $terms_with_count;
        book_query_cache::put([[
            '_key'      => "tags_of_$tax",
            '_value'    => $book_ids_hash,
            'ids'       => $terms_with_count
        ]]);
    }
    function search ($s) {
        $s = trim(strtolower($s));
        if ($s === "") {
            return $this;
        }
        $filtered = [];
        foreach($this->terms as $term) {
            $n = strtolower($term['name']);
            if (stripos($n,$s) === false ) {
                continue;
            }
            $filtered[$term['ID']] = $term;
        }
        $this->original = $this->terms;
        $this->terms = $filtered;
        $filtered = null;
        return $this;
    }
    function sort() {
        $counted = array_column($this->terms,'count','ID');
        arsort($counted);
        $counted = array_keys($counted);
        $new = [];
        foreach ($counted as $id) {
            $new[$id] = $this->terms[$id];
        }
        $this->terms = $new;
        $new = null;
        return $this;
    }
    function select($selected) {
        if (!isset($this->original)) {
            $this->original = $this->terms;
        }
        $selects = array_merge($selected['included'],$selected['excluded']);
        $at_start = [];
        foreach ($selects as $id ) {
            if (!isset($this->terms[$id])) {
                if (!$this->original[$id]) {
                    continue;
                }
                $this->terms[$id] = $this->original[$id];
            }
            $this->terms[$id]['selected'] = in_array($id,$selected['included']);
            $at_start[$id] = $this->terms[$id];
            unset($this->terms[$id]);
        }
        $this->terms = array_replace($at_start,$this->terms);
        return $this;
    }
    function get() {
        return array_values($this->terms);
    }
}