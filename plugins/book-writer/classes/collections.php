<?php
class collection {
    public static function get_hidden($author = null){
        if ($author === null){
            $author = get_current_user_id();
        }
        $collection_ids = array_column(collection::query(array(
            'authors'      => array($author),
            'title'         => 'Hidden'
        )),'ID');
        return array_column(collection::book_query($collection_ids),'ID');
    }
    public static function get_favorites($author = null){
        if ($author === null){
            $author = get_current_user_id();
        }
        $collection_ids = array_column(collection::query(array(
            'authors'      => array($author),
            'types'         => array('Favorites')
        )),'ID');
        return array_column(collection::book_query($collection_ids),'ID');
    }
    public static function notifications($user_id = 0 ){
        if ($user_id === 0){
            $user_id = get_current_user_id();
        }
        $follower_query = array_column(collection::follower_query(array(
            'user_ids'      => array($user_id)
        )),'notifications','collection_id');
        return $follower_query;
    }
    public static function add_follow($collection_id,$user_id,$notification){
        $collection_ids = array_column(
            collection::follower_query('user_id',array(
                'user_ids'      => array($user_id)
            )),
            'notifications',
            'collection_id'
        );
        $collection_ids[$collection_id] = $notification;
        foreach($collection_ids as $key => $value){
            if ($value === false){
                unset($collection_ids[$key]);
            }
        }
        $return = collection::set_follow('user',$user_id,$collection_ids);
        if (err::is($return)){
            return $return;
        }
        return $collection_id;
    }
    public static function follower_query($args = array()){
        $error = new err();
        $base_sql = 'SELECT collection_id,user_id,notifications FROM collection_follow WHERE 1';
        $prepare = [];
        if (isset($args['notifications'])){
            $args['notifications'] = (array) $args['notifications'];
            $fill = implode(', ',array_fill(0,count($args['notifications']),'%s'));
            $base_sql .= ' AND notifications IN (' . $fill . ')';
            $prepare = array_merge($prepare,$args['notifications']);
        }
        if (isset($args['user_ids'])){
            $args['user_ids'] = (array) $args['user_ids'];
            $fill = implode(', ',array_fill(0,count($args['user_ids']),'%s'));
            $base_sql .= ' AND user_id IN (' . $fill . ')';
            $prepare = array_merge($prepare,$args['user_ids']);
        }
        if (isset($args['collection_ids'])){
            $args['collection_ids'] = (array) $args['collection_ids'];
            $fill = implode(', ',array_fill(0,count($args['collection_ids']),'%s'));
            $base_sql .= ' AND collection_id IN (' . $fill . ')';
            $prepare = array_merge($prepare,$args['collection_ids']);
        }
        global $wpdb;
        $sql = $wpdb->prepare( $base_sql, $prepare );
        $results = $wpdb->get_results( $sql ,ARRAY_A );
        return $results;
    }
    public static function set_follow($key_is,$key,$values,$strict_collection_author = null){
        $error = new err();
        $available = array('user','collection');
        if (! in_array($key_is,$available)){
            $error->add('key_is(0)','Must be "user" or "collection"');
        }
        if ($error->has()){
            return $error;
        }
        $notifications = array_values($values);
        if (! empty(array_diff($notifications,array('no-email','all')))){
            $error->add('notifications(2)','Must be "no-email" or "all"');
        }
        $values = array_keys($values);
        $pairs = array();
        $pairs[$key_is] = array_fill(0,count($values),$key);
        unset($available[array_search($key_is,$available)]);
        $pairs[reset($available)] = $values;
        
        $existing_ = collection::query(array(
            'ids'   => $pairs['collection'],
            'types' => array('Public', 'Unlisted', 'Private', 'Favorites', 'Trash')
        ));
        $existing_ids = array_column($existing_,'ID');
        $diff_ids = array_unique( array_diff($pairs['collection'],$existing_ids) );
        if (! empty($diff_ids)){
            $error->add('collection','Invalid collection ids: (' . implode(',',$diff_ids) . ')');
        }
        $existing_users = array_column($existing_,'user_id');
        $diff_users = $strict_collection_author !== null ? array_unique( array_diff( $existing_users,array($strict_collection_author) )) : array();
        if (! empty($diff_users)){
            $error->add('collection','Invalid authors for some collections: (' . implode(',',$diff_users) . ')');
        }

        $existing_ids = array_column((new WP_User_Query(array(
            'include'       => $pairs['user'],
        )))->results,'data');
        $diff_ids = array_unique(array_diff($pairs['user'],array_column($existing_ids,'ID')));
        if (! empty($diff_ids)){
            $error->add('user','Invalid user ids: (' . implode(',',$diff_ids) . ')');
        }
        if ($error->has()){
            return $error;
        }
        //ob_start();
        global $wpdb;
        $response = $wpdb->delete(
            'collection_follow',
            array(
                $key_is . '_id' => intval($key),
            )
        );
        if ($response === false){
            $error->add('db_error',$wpdb->last_error);
        }
        $timestamp = current_time('timestamp',true);
        foreach ($pairs['user'] as $key => $user_id) {
            $notification_status = $notifications[$key];
            $collection_id = $pairs['collection'][$key];
            $response = $wpdb->insert(
                'collection_follow',
                array(
                    'collection_id'     => $collection_id,
                    'user_id'           => $user_id,
                    'notifications'     => $notification_status,
                    'time_followed'     => $timestamp
                )
            );
            if ($response === false && substr($wpdb->last_error,0,15) != 'Duplicate entry'){
                $error->add('db_error',$wpdb->last_error);
            }
        }
        //ob_end_clean();
        if ($error->has()){
            return $error;
        }
        return $timestamp;
    }
    public static function link($collection_id){
        $collection = collection::query(array(
            'ids'       => array($collection_id)
        ));
        if (empty($collection)){
            return false;
        }
        if (in_array($collection[0]['type'],array('Unlisted','Public'))){
            $link = rtrim(home_url(),'/') . '/collections/' . $collection[0]['slug'];
        }
        else if (in_array($collection[0]['type'],array('Private','Favorites'))){
            $link = rtrim(get_author_posts_url( $collection[0]['author']),'/') . '/collections/' . $collection[0]['slug'];
        }
        return $link;
    }
    public static function _present_dashboard(){
        $collections = collection::query(array(
            'authors'  => array(get_current_user_id()),
            'types'     => array('Public','Unlisted','Private','Favorites')
        ));
        $collections_ = [];
        foreach ($collections as $key => $collection) {
            $books = collection::book_query(array($collection['ID']));
            $collection['books'] = [];
            foreach ($books as $book) {
                $collection['books'][$book->ID] = array(
                    'title'      => $book->post_title,
                    'link'       => get_permalink( $book->ID ),
                );
            }
            $collection['link'] = collection::link($collection['ID']);
            $id = $collection['ID'];
            unset($collection['slug']);
            unset($collection['created']);
            unset($collection['author']);
            unset($collection['ID']);
            $collection['type'] = $collection['type'] == 'Favorites' ? 'Public' : $collection['type'];
            $collections_[$id] = $collection;
        }
        return $collections_;
    }
    public static function create_default($user_id){
        $default_ = array(
            array(
                'title'         => 'Favorites',
                'type'          => 'Favorites',
                'author'        => $user_id,
                'notification'  => 'all'
            ),
            array(
                'title'         => 'Hidden',
                'type'          => 'Private',
                'author'        => $user_id,
                'notification'  => 'no-email'
            )
        );
        foreach ($default_ as $value) {
            $id = collection::create($value);
            if (! err::is($id)){
                collection::add_follow($id,$value['author'],$value['notification']);
            }
        }
    }
    public static function query_by_book($book_ids,$columns = 'all'){
        $book_collections = array();
        foreach ($book_ids as $book_id) {
            $collections = collection::query(array(
                'book_ids'	=> array($book_id),
            ));
            if ($columns == 'all'){
                $book_collections[$book_id] = $collection;
            }
            else{
                $book_collections[$book_id] = array_column($collections,$columns);
            }
        }
        return $book_collections;
    }
    public static function set($key_is,$key,$values,$strict_book_author = null,$strict_collection_author = null){
        $error = new err();
        $available = array('book','collection');
        if (! in_array($key_is,$available)){
            $error->add('key_is(1)','Must be "book" or "collection"');
        }
        if ($error->has()){
            return $error;
        }
        $values = array_unique($values);
        $pairs = array();
        $pairs[$key_is] = array_fill(0,count($values),$key);
        unset($available[array_search($key_is,$available)]);
        sort($available);
        $pairs[$available[0]] = $values;
        
        $existing_ = collection::query(array(
            'ids' => $pairs['collection']
        ));

        $existing_ids = array_column($existing_,'ID');
        $diff_ids = array_unique(array_diff($pairs['collection'],$existing_ids));
        if (! empty($diff_ids)){
            $error->add('collection','Invalid collection ids: (' . implode(',',$diff_ids) . ')');
        }
        $existing_users = array_column($existing_,'user_id');
        $diff_users = $strict_collection_author !== null ? array_unique( array_diff( $existing_users,array($strict_collection_author) )) : array();
        if (! empty($diff_users)){
            $error->add('collection','Invalid authors for some collections: (' . implode(',',$diff_users) . ')');
        }

        $existing_ids = (new book_query(array(
            'include_ids'      => $pairs['book'],
        )))->ids;
        $diff_ids = array_unique(array_diff($pairs['book'],array_column($existing_ids,'ID')));
        if (! empty($diff_ids)){
            $error->add('book','Invalid book ids: (' . implode(',',$diff_ids) . ')');
        }
        $diff_users = $strict_book_author !== null ? array_unique(array_diff(array_column( $existing_ids,'post_author'),array($strict_book_author) ) ) : array();
        if (! empty($diff_users)){
            $error->add('book','Invalid authors for some books: (' . implode(',',$diff_users) . ')');
        }
        if ($error->has()){
            return $error;
        }
        ob_start();
        global $wpdb;
        $diff_sql = "SELECT " . $available[0] . "_id FROM collection_books WHERE " . $key_is . "_id = " . $key;
        $existing_values_key = array_column($wpdb->get_results($diff_sql,ARRAY_A),$available[0] . '_id');
        $to_delete = array_diff($existing_values_key,$pairs[$available[0]]);
        foreach ($to_delete as $value ) {
            $response = $wpdb->delete(
                'collection_books',
                array(
                    $available[0] . '_id'   => $value,
                    $key_is . '_id'         => $key

                )
            );
            if ($response === false){
                $error->add('db_error',$wpdb->last_error);
            }
        }
        $timestamp = current_time('timestamp',true);
        $collection_ids_updated = array();
        foreach ($pairs['book'] as $key => $book_id) {
            $collection_id = $pairs['collection'][$key];
            if (! in_array($collection_id,$collection_ids_updated)){
                collection::update_modified($collection_id);
                $collection_ids_updated[] = $collection_id;
            }
            $response = $wpdb->insert(
                'collection_books',
                array(
                    'collection_id' => $collection_id,
                    'book_id'       => $book_id,
                    'time_added'    => $timestamp
                )
            );
            if ($response === false && substr($wpdb->last_error,0,15) != 'Duplicate entry'){
                $error->add('db_error',$wpdb->last_error);
            }
        }
        ob_end_clean();
        if ($error->has()){
            return $error;
        }
        return $timestamp;
    }
    public static function query($args = array()){
        if (! isset($args['text_search_protocol'])){
            $args['text_search_protocol'] = 'lenient';
        }
        if (! isset($args['types'])){
            $args['types'] = array('Public','Unlisted','Private','Favorites');
        }
        $error = new err();
        $select_what = (isset($args['select']) && $args['select'] === 'count') ? 'COUNT(*)' : '*';
        $base_sql =
        "SELECT " . $select_what . ",(
            SELECT COUNT(*)
            FROM collection_books
            WHERE collections.ID = collection_books.collection_id
        ) AS count
        FROM collections
        WHERE 1";
        $prepare = array();
        
        $args['exclude_ids'] = isset($args['exclude_ids']) ? (array) $args['exclude_ids'] : array();
        if (isset($args['ids'])){
            $args['ids'] = (array) $args['ids'];
            if (empty($args['ids'])){
                return array();
            }
            $fill = implode(', ',array_fill(0,count($args['ids']),'%d'));
            $base_sql .= ' AND ID IN (' . $fill . ')';
            $prepare = array_merge($prepare,$args['ids']);
        }

        if (! empty($args['exclude_ids'])){
            $fill = implode(', ',array_fill(0,count($args['exclude_ids']),'%d'));
            $base_sql .= ' AND ID NOT IN (' . $fill . ')';
            $prepare = array_merge($prepare,$args['exclude_ids']);
        }
        if (isset($args['authors'])){
            $args['authors'] = (array) $args['authors'];
            if (empty($args['authors'])){
                return array();
            }
            $fill = implode(', ',array_fill(0,count($args['authors']),'%d'));
            $base_sql .= ' AND author IN (' . $fill . ')';
            $prepare = array_merge($prepare,$args['authors']);
        }
        if (isset($args['types'])){
            $args['types'] = (array) $args['types'];
            if (empty($args['types'])){
                return array();
            }
            $fill = implode(', ',array_fill(0,count($args['types']),'%s'));
            $base_sql .= ' AND type IN (' . $fill . ')';
            $prepare = array_merge($prepare,$args['types']);
        }
        if (isset($args['created'])){
            $base_sql .= ' AND created >= %d AND created <= %d';
            $prepare[] = isset($args['created']['from']) ? intval($args['created']['from']) : 0;
            $prepare[] = isset($args['created']['to']) ? intval($args['created']['to']) : current_time('timestamp',true);
        }
        if (isset($args['modified'])){
            $base_sql .= ' AND modified >= %d AND modified <= %d';
            $prepare[] = isset($args['modified']['from']) ? intval($args['modified']['from']) : 0;
            $prepare[] = isset($args['modified']['to']) ? intval($args['modified']['to']) : current_time('timestamp',true);
        }
        if (isset($args['title'])){
            $base_sql .= ' AND title LIKE %s';
            $args['title'] = (string) $args['title'];
            $prepare[] = $args['text_search_protocol'] != 'lenient' ? $args['title'] : '%' . $args['title'] . '%';
        }
        if (isset($args['slug'])){
            $base_sql .= ' AND slug LIKE %s';
            $args['slug'] = (string) $args['slug'];
            $prepare[] = $args['text_search_protocol'] != 'lenient' ? $args['slug'] : '%' . $args['slug'] . '%';
        }
        if (isset($args['description'])){
            $base_sql .= ' AND description LIKE %s';
            $args['description'] = (string) $args['description'];
            $prepare[] = $args['text_search_protocol'] != 'lenient' ? $args['description'] : '%' . $args['description'] . '%';
        }
        if (isset($args['book_ids'])){
            $args['book_ids'] = (array) $args['book_ids'];
            if (empty($args['book_ids'])){
                return array();
            }
            $fill = implode(', ',array_fill(0,count($args['book_ids']),'%d'));
            $base_sql .= ' AND ID IN (
                SELECT collection_id FROM collection_books WHERE book_id IN (' . $fill . ')
            )';
            $prepare = array_merge($prepare,$args['book_ids']);
        }
        if (isset($args['follower_ids'])){
            $args['follower_ids'] = (array) $args['follower_ids'];
            if (empty($args['follower_ids'])){
                return array();
            }
            $fill = implode(', ',array_fill(0,count($args['follower_ids']),'%d'));
            $base_sql .= ' AND ID IN (
                SELECT collection_id FROM collection_follow WHERE user_id IN (' . $fill . ')
            )';
            $prepare = array_merge($prepare,$args['follower_ids']);
        }
        if (isset($args['count'])){
            $base_sql .= ' HAVING count >= %d AND count <= %d';
            $prepare[] = isset($args['count']['from']) ? intval($args['count']['from']) : 0;
            $prepare[] = isset($args['count']['to']) ? intval($args['count']['to']) : 84093289483209844093284883209;
        }
        if (! isset($args['orderby'])){
            $args['orderby'] = 'ID';
        }
        if (! isset($args['order'])){
            $args['order'] = 'ASC';
        }
        if (! in_array($args['orderby'],array('count','ID','title','description','type','slug','created','author','modified'))){
            $error->add('orderby','Field doesn\'t exist.');
        }
        if (! in_array(strtoupper($args['order']),array('ASC','DESC'))){
            $error->add('order','Pass ASC or DESC.');
        }
        $base_sql .= ' ORDER BY ' . $args['orderby'] . ' ' . strtoupper($args['order']);

        if (! isset($args['limit'])){
            $args['limit'] = 10;
        }
        $args['limit'] = intval($args['limit']);
        $base_sql .= ' LIMIT %d';
        $prepare[] = $args['limit'];

        if (! isset($args['page'])){
            $args['page'] = 1;
        }
        $base_sql .= ' OFFSET %d';
        $prepare[] = (intval($args['page']) - 1) * $args['limit'];

        if ($error->has()){
            return $error;
        }
        global $wpdb;
        $sql = $wpdb->prepare( $base_sql, $prepare );
        $results = $wpdb->get_results( $sql ,ARRAY_A );
        return $results;
    }
    public static function book_query($collection_ids,$wp_query_args_add = array()){
        $collection_ids = (array) $collection_ids;
        if (empty($collection_ids)){
            return array();
        }
        $fill = implode(', ',array_fill(0,count($collection_ids),'%d'));
        $base_sql = 'SELECT book_id FROM collection_books WHERE collection_id IN (' . $fill . ')';
        global $wpdb;
        $sql = $wpdb->prepare( $base_sql, $collection_ids );
        $book_ids = array_column($wpdb->get_results( $sql ,ARRAY_A ),'book_id');
        if (empty($book_ids)){
            return array();
        }
        $args = array(
            'include_ids'   => $book_ids,
        );
        //These value(s) cannot be passed through custom_args
        unset($wp_query_args_add['include_ids']);

        $args = array_merge($args,$wp_query_args_add);
        $books = (new book_query($args))->books;
        return $books;
    }
    public static function update($id,$args){
        $error = new err();
        //Remove time and ID from $args if exists
        unset($args['ID']);
        unset($args['created']);
        unset($args['modified']);
        
        $existing = collection::query(array(
            'ids'   => array($id)
        ));
        if (empty($existing)){
            $error->add('id','Doesn\'t exist');
            return $error;
        }
        $args = array_replace($existing[0],$args);
        $args = collection::validate($args);
        if (err::is($args)){
            return $args;
        }
        global $wpdb;
        $response = $wpdb->update(
            'collections',
            $args,
            array(
                'ID'    => $id
            )
        );
        if ($response === false){
            $error->add('db_error',$wpdb->last_error);
            return $error;
        }
        return $id;

    }
    public static function create($args){
        $args = collection::validate($args);
        if (err::is($args)){
            return $args;
        }
        unset($args['ID']);
        $args['created'] = current_time('timestamp',true);
        $args['modified'] = current_time('timestamp',true);
        global $wpdb;
        $response = $wpdb->insert(
            'collections',
            $args
        );
        if ($response === false){
            $error = new err();
            $error->add('db_error',$wpdb->last_error);
            return $error;
        }
        $id = $wpdb->insert_id;
        return $id;
    }
    private static function update_modified($id){
        $error = new err();
        
        global $wpdb;
        $response = $wpdb->update(
            'collections',
            array(
                'modified'  => current_time('timestamp',true)
            ),
            array(
                'ID'        => $id
            )
        );
        if ($response === false){
            $error->add('db_error',$wpdb->last_error);
            return $error;
        }
        return $id;

    }
    private static function validate($args){
        $error = new err();
        $unlisted_slug = bin2hex(random_bytes(7));
        if (! isset ($args['title'])){
            $error->add('title','Required');
        }
        else if ($args['title'] == ''){
            $error->add('title','Cannot be empty');
        }
        else if (! title_regex_valid($args['title'])){
            $error->add('title','Only underscores, dots, hyphens,question marks & alphanumeric characters allowed.');
        }
        else if (ucfirst($args['type']) == 'Public' && ! empty(collection::query(array(
            'types'                 => array('Public','Unlisted'),
            'slug'                  => collection::toSlug($args['title']),
            'exclude_ids'           => isset($args['ID']) ? array($args['ID']) : array(),
            'text_search_protocol'  => 'strict'
        )))){
            $error->add('title','Slug exists.');
        }
        else if (ucfirst($args['type']) == 'Unlisted' && ! empty(collection::query(array(
            'types'                 => array('Public','Unlisted'),
            'slug'                  => $unlisted_slug,
            'exclude_ids'           => isset($args['ID']) ? array($args['ID']) : array(),
            'text_search_protocol'  => 'strict'
        )))){
            $error->add('slug','Slug exists.');
        }
        else if (in_array(ucfirst($args['type']),array('Public','Unlisted','Private','Favorites')) && ! empty(collection::query(array(
            'slug'                  => collection::toSlug($args['title']),
            'authors'              => isset($args['author']) ? array($args['author']) : array(get_current_user_id()),
            'exclude_ids'           => isset($args['ID']) ? array($args['ID']) : array(),
            'text_search_protocol'  => 'strict'
        )))){
            $error->add('title','Author has collection with same slug.');
        }
        else if (in_array(ucfirst($args['type']),array('Trash')) && in_array($args['title'],array('Favorites','Hidden')) ){
            $error->add('type','Favorites & Hidden can\'t be deleted');
        }
        if (! isset ($args['type'])){
            $error->add('type','Required');
        }
        else if (! in_array(ucfirst(strval($args['type'])),array('Public','Private','Favorites','Unlisted','Trash'))){
            $error->add('type','Invalid Value');
        }
        if (! isset($args['author']) && get_current_user_id() == 0){
            $error->add('author','Author required. No user logged in.');
        }
        else if (isset($args['author']) && get_userdata($args['author']) == false){
            $error->add('author','User doesn\'t exist.');
        }
        if ($error->has()){
            return $error;
        }
        if (! isset($args['description'])){
            $args['description'] = '';
        }
        $args['description'] = strval($args['description']);
        if (! isset($args['author'])){
            $args['author'] = get_current_user_id();
        }
        $args['type'] = ucfirst(strval($args['type']));
        $args['title'] = strval($args['title']);
        if (in_array($args['type'],array('Unlisted'))){
            $args['slug'] = $unlisted_slug;
        }
        else{
            $args['slug'] = collection::toSlug($args['title']);
        }
        $allowed_keys = array('ID','created','modified','title','type','description','author','slug');
        foreach ($args as $key => $value) {
            if (! in_array($key,$allowed_keys)){
                unset($args[$key]);
            }
        }
        return $args;
    }
    private static function toSlug($title){
        return strtolower(str_replace(array('_',' ','.','-'),'-',$title));
    }
}