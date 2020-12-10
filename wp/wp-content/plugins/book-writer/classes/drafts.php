<?php
class drafts {
    protected static $table = 'drafts';
    protected static $blank_title = 'Untitled';
    static function new($args) {
        $e = new err;
        $default_draft = [
            'user_id'       => get_current_user_id(),
            'share'         => null,
            'title'         => '',
            'chapter_id'    => null,
            'created'       => time(),
            'branch_type'   => null,
            'path'          => '',
        ];
        $args = array_replace($default_draft,$args);
        if (intval($args['user_id']) === 0) {
            return $e->add('user_id','No user is logged in');
        }
        $exists_dir = drafts_dir::exists(
            $args['path'],
            $args['user_id']
        );
        if (! $exists_dir) {
            return $e->add('path','Folder doesn\'t exist');
        }
        if ($args['title'] === '') {
            $args['title'] = self::$blank_title;
        }
        $args['title'] = substr($args['title'],0,80);
        global $wpdb;
        $wpdb->insert(
            self::$table,
            $args
        );
        return intval($wpdb->insert_id);
    }
    static function update($id,$args) {
        $e = new err;
        $existing = self::get_by('ID',$id);
        if ($existing === false || !is_current_user($existing->user_id) ) {
            return $e->add('ID','Draft doesn\'t exist');
        }
        if ( isset($args['path']) && !drafts_dir::exists($args['path']) ) {
            return $e->add('path','Folder doesn\'t exist.');
        }
        if ( isset($args['title']) && $args['title'] === '') {
            $args['title'] = self::$blank_title;
        }
        if (isset($args['title'])) {
            $args['title'] = substr($args['title'],0,80);
        }
        global $wpdb;
        $r = $wpdb->update(
            self::$table,
            $args,
            ['ID' => $id]
        );
        return intval($id);
    }
    static function get_by ($field, $value) {
        $table = self::$table;
        $e = new err();
        if (! in_array($field,['share','ID','chapter_id'])){
            return $e->add('$field','Should be either "share" or "ID"');
        }
        $placeholder = $field === "share" ? "%s" : "%d";
        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE $field = $placeholder AND branch_type IS NULL",[$value]));
        if (empty($results)) {
            return false;
        }
        $revision = draft_revision::getOne($results[0]->ID);
        $results[0]->content = $revision->content;
        $results[0]->edited = $revision->edited;
        return $results[0];
    }
    static function delete($draft_id) {
        global $wpdb;
        return $wpdb->delete(self::$table,[
            'ID'        => $draft_id,
            'user_id'   => get_current_user_id()
        ]);
    }
    static function move($draft_id,$path) {
        $e = new err;
        $path = implode('/',arr::non_empty(explode('/',$path)));
        global $wpdb;
        $draft = drafts::get_by('ID',$draft_id);
        if ($draft === false || !is_current_user($draft->user_id)) {
            return $e->add('draft','Draft doesn\'t exist');
        }
        $current_user_id = get_current_user_id();
        $exists_draft = drafts_dir::exists($path,$current_user_id);
        if (! $exists_dir) {
            $e->add('folder','Folder doesn\'t exist');
        }
        $wpdb->update(
            self::$table,
            [
                'path'  => $path
            ],
            [
                'ID'        => $draft->ID,
                'user_id'   => $current_user_id,
            ]
        );
        return true;
    }
}
class draft_revision extends drafts{
    protected static $table = 'draft_revisions';
    protected static function hash ($content) {
        return sha1($content);
    }
    // This Static Method will noe accept change only
    static function push($draft_id,$changes, $length = null, $FLAG = 'update',$maps = []) {
        if ($length === null) {
            $length = count($changes);
        }
        if (! in_array($FLAG,['push','update'])) {
            $FLAG = 'update';
        }
        $session_last_revision = &$_SESSION['drafts'][$draft_id]['last_revision'];
        $prev_draft = $session_last_revision ?? (array) self::getOne($draft_id);

        if ($prev_draft === false) {
            return (new err)->add('draft_id',"Draft doesn't exists");
        }

        if (isset($changes['title'])) {
            unset($changes['title']);
        }

        // Edit Changes
        $content = json_decode($prev_draft['content'],true);

        foreach ($maps as $map) {
            if ($map['type'] === "delete") {
                unset($content[$map['pa']]);
            }
            else if ($map['type'] === "insert") {
                array_splice($content,$map['pa'],0, [[]] );
            }
            $content = array_values($content);
        }

        foreach ($changes as $p => $change) {
            $content[$p] = $change;
        }
        $content = array_values($content);
        array_splice($content,$length);

        // Convert for pushing
        $words = str_word_count(drafts_json::simpleText($content,true));
        $content = json_encode($content);

        $t = time();
        $added_last = $prev_draft === false ? 0 : intval($prev_draft['edited']);
        $ago = $t - $added_last;

        $hash = self::hash($content);
        if ( $ago > 180 ) {
            // If last update was more than 3 min ago,
            // push regardless of whatever flag says
            $FLAG = 'push';
        }
        if ($prev_draft['hash'] === $hash ) {
            return [
                'time'  => intval($prev_draft['edited']),
                'words' => $words,
                'length'=> $length
            ];
        }
        global $wpdb;
        $table = self::$table;
        // Now that validation is done,
        // remove previous depending on flag
        if ($FLAG === 'update') {
            $sql1 = $wpdb->prepare("DELETE FROM $table WHERE ID=%d",[$prev_draft['ID']]);
        }
        $sql2 = $wpdb->prepare(
            "INSERT INTO $table (`draft_id`,`user_id`,`hash`,`edited`,`content`)
            VALUES (%s,%s,%s,%s,%s)",
            [$draft_id,get_current_user_id(),$hash,$t,$content]
        );

        $rows = $wpdb->query($sql2);
        if (isset($sql1)) {
            $rows = $wpdb->query($sql1);
        }

        $session_last_revision = [
            'ID'        => $wpdb->insert_id,
            'edited'    => $t,
            'hash'      => $hash,
            'content'   => $content
        ];
        return [
            'time'      => intval($t),
            'words'     => $words,
            'length'    => $length
        ];
    }
    static function getOne($draft_id) {
        $table = self::$table;
        global $wpdb;
        $sql = $wpdb->prepare("SELECT * FROM $table WHERE draft_id = %s ORDER BY ID DESC LIMIT 1",[$draft_id]);
        $r = $wpdb->get_results($sql);
        return empty($r) ? false : $r[0];
    }
    static function getAll($draft_id,$withContent = false) {
        $table = self::$table;
        global $wpdb;
        $withContentQuery = $withContent ? ",content" : "";
        $sql = $wpdb->prepare("SELECT ID,edited$withContentQuery FROM $table WHERE draft_id = %s ORDER BY ID DESC",[$draft_id]);
        $r = $wpdb->get_results($sql);
        return $r;
    }
    static function getById($revision_id) {
        $table = self::$table;
        global $wpdb;
        $sql = $wpdb->prepare("SELECT * FROM $table WHERE ID = %s ORDER BY ID DESC",[$revision_id]);
        $r = $wpdb->get_results($sql);
        return empty($r) ? false : $r[0];
    }
}
class drafts_dir extends drafts {
    static function exists($path,$user_id) {
        if ($path === '') {
            return true;
        }
        global $wpdb;
        $table = self::$table;
        $path = implode('/',arr::non_empty(explode('/',$path)));
        $sql = $wpdb->prepare(
            "SELECT * FROM $table
                WHERE path = %s
                AND user_id = %s
                AND branch_type = 'dir'",
            [$path,$user_id]
        );
        $r = $wpdb->get_results($sql);
        $exists_dir = $path === '' ? true : false;
        return empty($r) ? false : true;
    }
    static function get_path (bool $words = false) {
        $user = get_current_user_id();
        if ($user === 0) {
            return [];
        }
        $table = self::$table;
        global $wpdb;
        if ($words) {
            $rsql = $wpdb->prepare(
                "SELECT draft_id,content FROM `draft_revisions`
                WHERE user_id = %d
                GROUP BY draft_id
                ORDER BY edited DESC",
                [$user]
            );
            $revisions = array_column($wpdb->get_results($rsql),'content','draft_id');    
        }
        $sql = $wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d AND (branch_type IS NULL || branch_type = 'dir') "
        ,[$user]);
        $results = $wpdb->get_results($sql);
        $f = [];
        foreach ($results as $s ) {
            $f[$s->path] = $f[$s->path] ?? [];
            if ( $s->branch_type === null ) {
                $put_draft = [
                    'ID'        => intval($s->ID),
                    'title'     => $s->title,
                    'time'      => intval($s->updated)
                ];
                if ($words) {
                    $put_draft['words'] = str_word_count( drafts_json::simpleText( $revisions[$s->ID] ) );
                }
                $f[$s->path][] = $put_draft;
            }
        }
        return $f;
    }
    static function create_dir($name,$path = '') {
        $e = new err;
        $table = self::$table;
        $name = substr($name,0,40);
        $arrs = arr::non_empty(explode('/',$path));
        if (count($arrs) > 2) {
            return $e->add('name','Only two subfolders are allowed.');
        }
        if (trim($name) === '') {
            return $e->add('name','Folder name?');
        }
        if (strlen (preg_replace ('/(\d)|(-)|( )|(_)|([A-Z])+/i','',$name) ) > 0) {
            return $e->add('name','Folder names can only contain spaces, hyphens(-), underscores(_), english alphabets, or numbers.');
        }
        $path = ltrim(implode('/',$arrs) . '/' . $name,'/');
        $current_user_id = get_current_user_id();
        global $wpdb;

        if (! empty(self::exists($path,$current_user_id))) {
            return $e->add('name','Folder with same name exists.');
        }
        $wpdb->insert(
            $table,
            [
                'user_id'       => $current_user_id,
                'content'       => '',
                'title'         => '',
                'updated'       => time(),
                'branch_type'   => 'dir',
                'path'          => $path
            ]
        );
    }
    static function delete($folder) {
        $e = new err;
        $arrs = arr::non_empty(explode('/',$folder));
        $path = implode('/',$arrs);
        array_pop($arrs);
        $move_to = implode('/',$arrs);
        global $wpdb;
        $wpdb->update(
            self::$table,
            [
                'path'          => $move_to
            ],
            [
                'user_id'       => get_current_user_id(),
                'branch_type'   => null,
                'path'          => $path
            ]
        );
        $wpdb->delete(
            self::$table,
            [
                'user_id'       => get_current_user_id(),
                'branch_type'   => 'dir',
                'path'          => $path
            ]
        );
    }
}
class drafts_json extends drafts {
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
    protected static function parseHTMLStyle($style) {
        $style_arrs = explode(';',$style);
        $TheNew = [];
        foreach ($style_arrs as $s ) {
            if ($s === '') {
                continue;
            }
            $split = explode(':',$s);
            $TheNew[trim($split[0])] = trim($split[1]);
        }
        return $TheNew;
    }
    protected static function BuildLeafsRecursive($lnode,$leaf,&$block) {
        if ($lnode instanceof DOMText) {
            $leaf['text'] = $lnode->wholeText;
            $block['children'][] = $leaf;
            return;
        }
        $style = self::parseHTMLStyle($lnode->getAttribute('style'));
        if (in_array($lnode->tagName,['b','strong']) || ($style['font-weight'] ?? '') === 'bold' ) {
            $leaf['bold'] = true;
        }
        if (in_array($lnode->tagName,['i','em']) || ($style['font-style'] ?? '') === 'italic' ) {
            $leaf['italic'] = true;
        }
        foreach ($lnode->childNodes as $tnode ) {
            self::BuildLeafsRecursive($tnode,$leaf,$block);
        }
    }
    public static function toJSON($xml) {
        $d = new DOMDocument();
        $r = $d->loadXML('<content>' . $xml . '</content>');
        if (! $r) {
            return;
        }
        $json = [];
        foreach ($d->firstChild->childNodes as $node) {
            if (!$node instanceof DOMElement || !in_array($node->tagName,['p','hr']) ) {continue;}
            $block = [
                'children' => []
            ];
            $style = self::parseHTMLStyle($node->getAttribute('style'));
            if ( ($style['text-align'] ?? '') === 'center' && $node->tagName === "p" ) {
                $block['type'] = 'center';
            }
            else if ($node->tagName === "hr") {
                $block['type'] = 'seperator';
            }
            // Build Styles
            foreach ($node->childNodes as $leaf_el) {
                self::BuildLeafsRecursive($leaf_el,[],$block);
            }
            if (empty($block['children'])) {
                $block['children'][] = ['text'=>""];
            }
            $json[] = $block;
        }
        return json_encode($json);
    }
    public static function read($json) {
        $create_span_draft = function($leaf) {
            $leaf_attr = [
                'bold'      => 'font-weight: bold;',
                'italic'    => 'font-style: italic;'
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
            $span = "<span $span_style>" . htmlspecialchars($leaf['text']);
            $span .= "</span>";
            return $span;
        };
        $html = "";
        $array = json_decode($json,true);
        foreach ($array as $k => $para) {
            $para_style = '';
            $tagName = "p";
            if (isset($para['type']) && $para['type'] === 'center'){
                $para_style = 'style="text-align:center;"';
            }
            if (isset($para['type']) && $para['type'] === 'seperator'){
                $tagName = "hr";
            }
            $html .= "<$tagName $para_style>";
            foreach ($para['children'] as $kk => $leaf) {
                $html .= $create_span_draft($leaf);
            }
            $html .= "</$tagName>"; 
        }
        return $html;
    }
    public static function simpleText($json,$direct = false) {
        if (!$direct) {
            $json = json_decode($json,true);
        }
        $text = '';
        foreach ($json as $k => $para) {
            foreach ($para['children'] as $kk => $leaf) {
                $text .= $leaf['text'];
            }
            $text .= "\n";
        }
        return $text;
    }
    public static function compare($old,$new) {
        require(MAIN_DIR . 'content/php_includes/finediff.php');
        $old_text = self::simpleText($old);
        $opcodes = FineDiff::getDiffOpcodes($old_text, self::simpleText($new) );
        $style =
        "
        <style>
        ins {
            color: rgb(104 140 136);
            background: rgb(0 121 107 / 15%);
            text-decoration: none
        }
        
        del {
            color: var(--red);
            text-decoration: line-through
        }
        </style>
        ";
        return $style . nl2br(FineDiff::renderDiffToHTMLFromOpcodes($old_text, $opcodes));
    }
    public static function output_odt($json,$dump) {
        // Redundant
        return "";
        $create_span_draft = function($leaf) {
            $leaf_attr = [
                'bold'      => 'B',
                'italic'    => 'I'
            ];
            $span_style = "";
            foreach ($leaf_attr as $attr => $attr_style) {
                if (! isset($leaf[$attr])){
                    continue;
                }
                $span_style .= $leaf_attr[$attr];
            }
            $span = "<text:span text:style-name=\"T$span_style\">" . htmlspecialchars($leaf['text']);
            $span .= "</text:span>";
            return $span;
        };
        $html = '';
        $array = json_decode($json,true);
        foreach ($array as $k => $para) {
            $para_style = '';
            if (isset($para['type']) && $para['type'] === 'center'){
                $para_style = 'C';
            }
            $html .= "<text:p text:style-name=\"P$para_style\">";
            foreach ($para['children'] as $kk => $leaf) {
                $html .= $create_span_draft($leaf);
            }
            $html .= '</text:p>'; 
        }
        $dir = dirname ( $dump );
        if (! is_dir($dir)) {
            mkdir($dir,0777,true);
        }
        $zip = new ZipArchive;
        $zip->open($dump,ZipArchive::CREATE);
        $pre_xml = '<?xml version="1.0" encoding="UTF-8"?><office:document-content xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:config="urn:oasis:names:tc:opendocument:xmlns:config:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:presentation="urn:oasis:names:tc:opendocument:xmlns:presentation:1.0" xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:anim="urn:oasis:names:tc:opendocument:xmlns:animation:1.0" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:xforms="http://www.w3.org/2002/xforms" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:smil="urn:oasis:names:tc:opendocument:xmlns:smil-compatible:1.0"><office:automatic-styles><style:style style:name="T" style:family="text"><style:text-properties fo:font-weight="normal" /></style:style><style:style style:name="TB" style:family="text"><style:text-properties fo:font-weight="bold" /></style:style><style:style style:name="TI" style:family="text">    <style:text-properties fo:font-weight="normal" fo:font-style="italic" /></style:style><style:style style:name="TBI" style:family="text"><style:text-properties fo:font-weight="bold" fo:font-style="italic" /></style:style><style:style style:name="P" style:family="paragraph"><style:paragraph-properties fo:text-align="left" /></style:style><style:style style:name="PC" style:family="paragraph"><style:paragraph-properties fo:text-align="center" /></style:style></office:automatic-styles><office:body><office:text>';
        $post_xml = '</office:text></office:body></office:document-content>';
        $zip->addFromString('content.xml',$pre_xml . $html . $post_xml);
        $zip->addFromString(
            'META-INF/manifest.xml',
            '<?xml version="1.0" encoding="UTF-8"?><manifest:manifest xmlns:manifest="urn:oasis:names:tc:opendocument:xmlns:manifest:1.0"><manifest:file-entry manifest:full-path="/" manifest:media-type="application/vnd.oasis.opendocument.text" /><manifest:file-entry manifest:full-path="META-INF/manifest.xml" manifest:media-type="text/xml" /><manifest:file-entry manifest:full-path="content.xml" manifest:media-type="text/xml" /></manifest:manifest>'
        );
        $zip->close();
        return $html;
    }
    public static function output_html($json) {
        $create_span_draft = function($leaf) {
            $leaf_tagNames = [
                'bold'      => 'strong',
                'italic'    => 'em'
            ];
            $span_text = htmlspecialchars($leaf['text']);
            foreach ($leaf_tagNames as $attr => $attr_tag) {
                if (! isset($leaf[$attr])){
                    continue;
                }
                $span_text = "<$attr_tag>" . $span_text . "</$attr_tag>";
            }
            return $span_text;
        };
        $html = '';
        $array = json_decode($json,true);
        foreach ($array as $k => $para) {
            $para_style = '';
            $tagName = "p";
            if (isset($para['type']) && $para['type'] === 'center'){
                $para_style = ' align="center"';
            }
            if (isset($para['type']) && $para['type'] === 'seperator'){
                $tagName = 'hr';
            }
            $html .= "<$tagName$para_style>";
            foreach ($para['children'] as $kk => $leaf) {
                $html .= $create_span_draft($leaf);
            }
            $html .= "</$tagName>";
        }
        return $html;
    }
}
class draft_chapters extends drafts {
    static function save($draft_id_or_draft,$book_id,$title,$notes = [],$chapter_id = 'new') {
        if ($draft_id_or_draft !== null) {
            // Draft Check
            $draft = $draft_id_or_draft;
            if (is_numeric($draft_id_or_draft)) {
                $draft = self::get_by('ID', $draft_id_or_draft);
            }
            if (!$draft || !is_current_user($draft->user_id)){
                return false;
            }
        }
        $args = [
            'post_title'    => $title,
            'post_type'     => 'chapter',
            'post_status'   => 'publish',
            'post_parent'   => $book_id
        ];
        if ($chapter_id === "new") {
            if($draft_id_or_draft === null) {
                return false;
            }
            $args['post_content'] = drafts_json::read($draft->content);
            $chapter_id = wp_insert_post($args);
            if (is_wp_error( $chapter_id ) || $chapter_id === 0) {
                return false;
            }
            $words = str_word_count(drafts_json::simpleText($draft->content));
            update_post_meta( $chapter_id, 'word-count', $words );
            // Update Book Time
            wp_update_post(['ID' => $book_id]);
            $inst = new notifications_insert;
            $inst->updateStory($chapter_id);    
        }
        else {
            if ($draft_id_or_draft !== null) {
                $args['post_content'] = drafts_json::read($draft->content);
            }
            $args['ID'] = $chapter_id;
            $chapter_id = wp_update_post( $args );
            if (is_wp_error( $chapter_id ) || $chapter_id === 0) {
                return false;
            }
            if ($draft_id_or_draft !== null) {
                $words = str_word_count(drafts_json::simpleText($draft->content));
                update_post_meta( $chapter_id, 'word-count', $words );    
            }
        }
        foreach (['pre','post'] as $v ) {
            if (isset($notes[$v])){
                update_post_meta( $chapter_id, $v.'_author_note', $notes[$v] );
            }
        }
        return $chapter_id;
    }
    static function edit($chapter_id) {
        $chapter = get_post($chapter_id);
        if (!$chapter || !is_current_user($chapter->post_author) ) {
            return false;
        }
        $story = story::get($chapter->post_parent,true,false);
        if (!$story) {
            return false;
        }
        $draft_id = drafts::new([
            'title'     => $chapter->post_title,
        ]);
        if (err::is($draft_id)) {
            return false;
        }
        $json = drafts_json::toJSON($chapter->post_content);
        draft_revision::push($draft_id,json_decode($json,true));
        return $draft_id;
    }
}