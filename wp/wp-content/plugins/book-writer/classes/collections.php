<?php
class collection {
    public static $table = 'collections';
    static function update($args) {
        $e = new err;
        if (! isset($args['ID'])) {
            $args = array_replace([
                'title'         => '',
                'description'   => '',
                'type'          => 'Public',
                'slug'          => null,
                'author'        => get_current_user_id(),
                'created'       => time(),
                'modified'      => 0 //Null because it won't be used
            ],$args);
        }
        if (isset($args['title']) && trim($args['title']) === '') {
            return $e->add('title','Title is required');
        }
        if (isset($args['type']) && $args['type'] === 'Unlisted'){
            $args['slug'] = bin2hex(random_bytes(49));
        }
        if ( isset($args['ID']) ) {
            $prev = collection::get_by('ID',$args['ID']);
            if (!$prev) {
                return $e->add('ID', 'Collection doesn\'t exist');
            }
        }
        $currentTitle = strtolower($args['title'] ?? $prev->title);
        $user_collections = collection::query_by_user($args['author'] ?? $prev->author);
        foreach ($user_collections as $collection ) {
            if (intval($collection->ID) === intval($args['ID'] ?? 0) ) {continue;}
            if (strtolower($collection->title) === $currentTitle) {
                var_dump($collection->ID);
                return $e->add('title','Author has collection with similar name.');
            }
        }
        global $wpdb;
        if (isset($args['ID'])) {
            $id = $args['ID'];
            unset($args['ID']);
            $wpdb->update(
                self::$table,
                $args,
                ['ID' => $id]
            );
            
            return intval($args['ID']);
        }
        $wpdb->insert(
            self::$table,
            $args
        );
        return intval($wpdb->insert_id);
    }
    static function delete($id) {
        global $wpdb;
        $wpdb->delete(
            self::$table,
            [
                'ID'        => $id,
                'author'    => get_current_user_id()
            ]
        );
    }
    static function get_by($field, $value) {
        $table = self::$table;
        $e = new err();
        if (! in_array($field,['ID','slug'])){
            return $e->add('$field','Should be either "ID" or "slug"');
        }
        $args = ['slug'   => $value];
        if ($field === 'ID') {
            $args = ['id_included'   => [$value]];
        }
        $q = self::query($args);
        return empty($q) ? false : $q[0];
    }
    static function query_by_user ($author) {
        return self::query([
            'author_included'   => $author
        ]);
    }
    static function query ($a) {
        // Fields
        // author_included, author_excluded
        // types
        // slug
        // order, orderby
        $a = array_replace([
            'types'     => ['Public','Private','Unlisted','Favorites']
        ],$a);
        $a['order'] = in_array($a['order'] ?? 'X',['DESC','ASC']) ? $a['order'] : 'DESC';
        $a['orderby'] = in_array($a['orderby'] ?? 'X',['created','count']) ? $a['orderby'] : 'created';
        $a['types'] = (array) $a['types'];

        if ( empty($a['author_included'] ?? [1]) || empty($a['id_included'] ?? [1]) || empty($a['types'] || [1]) ) {
            return [];
        }
        $prep = $a['types'];
        $sql = "SELECT * FROM " . self::$table . " WHERE type IN(" . implode(',',array_fill(0,count($a['types']),'%s')) . ")";
        if ( isset($a['id_included']) ) {
            $c = (array) $a['id_included'];
            $sql .= ' AND ID IN (' . implode(',',array_fill(0,count($c),'%s')) .') ';
            $prep = array_merge($prep,$c);
        }
        if ( isset($a['id_excluded']) && ! empty($a['id_excluded'])) {
            $c = (array) $a['id_excluded'];
            $sql .= ' AND ID NOT IN (' . implode(',',array_fill(0,count($c),'%s')) .') ';
            $prep = array_merge($prep,$c);
        }
        if ( isset($a['author_included']) ) {
            $c = (array) $a['author_included'];
            $sql .= ' AND author IN (' . implode(',',array_fill(0,count($c),'%s')) .') ';
            $prep = array_merge($prep,$c);
        }
        if ( isset($a['author_excluded']) && ! empty($a['author_excluded'])) {
            $c = (array) $a['author_excluded'];
            $sql .= ' AND author NOT IN (' . implode(',',array_fill(0,count($c),'%s')) .') ';
            $prep = array_merge($prep,$c);
        }
        if ( isset($a['slug']) ) {
            $sql .= " AND slug = %s";
            $prep[] = $a['slug'];
        }
        if ($a['orderby'] === 'created') {
            $sql .= " ORDER BY " . $a['orderby'] . " " . $a['order'];
        }
        $table = self::$table;

        global $wpdb;
        $r = $wpdb->get_results($wpdb->prepare($sql,$prep),'OBJECT_K');
        
        // Put Book Ids
        $ids = array_column($r,'ID');
        $sql_books = "SELECT * FROM " . collection_books::$table . " WHERE collection_id IN(" . implode(',',$ids) . ")";
        $rows = empty($ids) ? [] : $wpdb->get_results($sql_books);
        foreach ($rows as $row) {
            $loc = &$r[$row->collection_id]->book_ids;
            $loc = isset($loc) ? $loc : [];
            $loc[] = $row->book_id;
        }

        // Filter By Count & Add Count
        $n = [];
        foreach ($r as $k => $c) {
            $collection = &$r[$k];
            $collection->count = count($collection->book_ids ?? []);
            $c = $collection->count;
            if (
                $c >= ($a['count']['from'] ?? 0) &&
                $c <= ($a['count']['to'] ?? INF)
            ) {
                $n[] = $collection;
            }
        }
        if ( (empty($n) || $a['orderby'] === 'created') ) {
            return array_values($n);
        }
        function sortByCountOfBooks($a, $b) {
            if ($a->count === $b->count) {return 0;}
            return ($a->count < $b->count) ? -1 : 1;
        }
        usort($n,"sortByCountOfBooks");
        return array_values($n);
    }
}
class collection_books extends collection{
    public static $table = 'collection_books';
    static function add($collection, $book) {
        global $wpdb;
        $wpdb->insert(
            self::$table,
            [
                'collection_id'   => $collection,
                'book_id'         => $book,
                'time_added'      => time()
            ]
        );
    }
    static function remove($collection, $book) {
        global $wpdb;
        $wpdb->delete(
            self::$table,
            [
                'collection_id'   => $collection,
                'book_id'         => $book
            ]
        );
    }
    static function exists($collection, $book) {
        $table = self::$table;
        global $wpdb;
        $sql = $wpdb->prepare("SELECT * FROM $table WHERE collection_id = %s AND book_id = %s",[$collection,$book]);
        $r = $wpdb->get_results($sql);
        return empty($r) ? false : intval($r[0]->time_added);
    }
    static function query_by($field, $value) {
        $table = self::$table;
        $e = new err();
        if (! in_array($field,['collection_id','book_id'])){
            return $e->add('$field','Should be either "collection_id" or "book_id"');
        }
        global $wpdb;
        $sql = $wpdb->prepare("SELECT * FROM $table WHERE $field = %s",[$value]);
        $results = $wpdb->get_results($sql);
        return $results;
    }
}
class collection_follow extends collection {
    public static $table = 'collection_follow';
    static function follow ($collection, $user) {
        global $wpdb;
        $wpdb->insert(
            self::$table,
            [
                'collection_id'   => $collection,
                'user_id'         => $user,
                'notifications'   => 'all',
                'time_followed'   => time()
            ]
        );
    }
    static function unfollow ($collection, $user) {
        global $wpdb;
        $wpdb->delete(
            self::$table,
            [
                'collection_id'   => $collection,
                'user_id'         => $user
            ]
        );
    }
    static function exists($collection, $user) {
        $table = self::$table;
        global $wpdb;
        $sql = $wpdb->prepare("SELECT * FROM $table WHERE collection_id = %s AND user_id = %s",[$collection,$user]);
        $r = $wpdb->get_results($sql);
        return empty($r) ? false : intval($r[0]->time_followed);
    }
    static function query_by($field, $value) {
        $table = self::$table;
        $e = new err();
        if (! in_array($field,['collection_id','user_id'])){
            return $e->add('$field','Should be either "collection_id" or "user_id"');
        }
        global $wpdb;
        $sql = $wpdb->prepare("SELECT * FROM $table WHERE $field = %s",[$value]);
        $results = $wpdb->get_results($sql);
        return $results;
    }
}
class collection_helpers extends collection {
    static function link($collection) {
        $collection = collection::get_by('ID',$collection);
        if (!$collection){
            return false;
        }
        if ( $collection->type === 'Unlisted' ){
            return rtrim(home_url(),'/') . '/collections/' . $collection->slug;
        }
        else if ( $collection->type === 'Public') {
            return rtrim(home_url(),'/') . '/collections/' . $collection->ID;
        }
        else if ( $collection->type === 'Favorites' ) {
            return rtrim(get_author_posts_url( $collection->author ),'/') . '/collections/favorites';
        }
        else if ( $collection->type === 'Private' ){
            return rtrim(get_author_posts_url( $collection->author ),'/') . '/collections/' . $collection->ID;
        }
        return false;
    }
    static function query_by_book($book_ids) {
        if (empty($book_ids)) {
            return [];
        }
        global $wpdb;
        $table = collection_books::$table;
        $sql = $wpdb->prepare("SELECT * FROM $table WHERE book_id IN(" . implode(',',array_fill(0,count($book_ids),'%s')) . ")",$book_ids);
        $r = $wpdb->get_results($sql);
        $book_collections = [];
        foreach ($r as $row ) {
            $i = &$book_collections[intval($row->book_id)];
            if (! isset($i)) {$i = [];}
            $i[] = $row->collection_id;
        }
        foreach ($book_ids as $book_id) {
            $l = &$book_collections[intval($book_id)];
            $l = isset($l) ? $l : [];
        }
        return $book_collections;
    }
    static function js_data() {
        $collections = collection::query_by_user(get_current_user_id());
        $return = [];
        foreach($collections as $key => $collection){
            unset($collection->slug);
            unset($collection->created);
            unset($collection->modified);
            unset($collection->author);
            unset($collection->count);
            if ($collection->type === 'Favorites'){
                $collection->type = 'Public';
            }
            $collection->link = collection_helpers::link($collection->ID);
            $return[] = $collection;
        }
        return $return;
    }
}