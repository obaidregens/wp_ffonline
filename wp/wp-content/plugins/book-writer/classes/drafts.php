<?php
class drafts {
    private static $table = 'drafts';
    private static function hash($content) {
        return sha1($content);
    }
    public static function update ($args) {
        if (isset($args['title'])) {
            $args['title'] = substr($args['title'],0,70);
        }
        unset($args['updated']);
        $e = new err();
        global $wpdb;
        if (isset($args['ID'])) {
            $r = self::get_by('ID',$args['ID']);
            if ($r === false) {
                $e->add('ID','Doesn\'t exist.');
                return $e;
            }
            $update = $args;
            unset($update['ID']);
            if (isset($update['title']) || isset($update['content'])){
                $overwrite = array_replace($r,$args);
                $update['hash'] = self::hash($overwrite['content']);
                $update['updated'] = time();
            }
            $wpdb->update(
                self::$table,
                $update,
                [
                    'ID'    => $args['ID']
                ]
            );
            return intval($args['ID']);
        }
        $args = array_replace([
            'content'   => '',
            'title'     => '',
            'user_id'   => get_current_user_id(),
            'share'     => null,
            'chapter_id'=> 0,
            'updated'   => time()
        ],$args);
        if ( trim($args['content']) === '' || trim($args['title']) === ''){
            $e->add('content/title','Content and title are required.');
            return $e;
        }
        $args['hash'] = self::hash($args['content']);
        $wpdb->insert(
            self::$table,
            $args
        );
        return intval($wpdb->insert_id);
        
    }
    public static function get_by ($field, $value) {
        $table = self::$table;
        $e = new err();
        if (! in_array($field,['share','ID','chapter_id'])){
            $e->add('$field','Should be either "share" or "ID"');
        }
        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE $field = %s",[$value]));
        if (empty($results)) {
            return false;
        }
        return $results[0];
    }
    public static function exists($content, $user_id = null) {
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }
        $table = self::$table;
        $r = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %s AND hash = %s",
            [$user_id,self::hash($content)]
        ));
        return empty($r) ? false : $r[0]->ID;
    }
    public static function by_users($user = null){
        if ($user === null ) {
            $user = get_current_user_id();
        }
        $table = self::$table;
        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE user_id = %s",[$user]));
        return $results;
    }
}
class drafts_json extends drafts {
    public static function read($json) {
        function create_span_draft($leaf) {
            $leaf_attr = [
                'bold'      => 'font-weight: bold;',
                'italic'    => 'font-style: italic'
            ];
            $span_style = "";
            foreach ($leaf_attr as $attr => $attr_style) {
                if (! isset($leaf[$attr])){
                    continue;
                }
                $span_style .= $leaf_attr[$attr];
            }
            if ($span_style !== '') {
                $span_style = 'style="' . $span_style . '"';
            }
            $span = "<span $span_style>" . $leaf['text'];
            $span .= "</span>";
            return $span;
        }
        $html = "";
        $array = json_decode($json,true);
        foreach ($array as $k => $para) {
            $para_style = '';
            if (isset($para['type']) && $para['type'] === 'center'){
                $para_style = 'style="text-align:center;"';
            }
            $html .= "<p $para_style>";
            foreach ($para['children'] as $kk => $leaf) {
                $html .= create_span_draft($leaf);
            }
            $html .= '</p>'; 
        }
        return $html;
    }
}