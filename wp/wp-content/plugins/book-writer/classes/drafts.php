<?php
class drafts {
    private static $table = 'drafts';
    protected static function hash ($content) {
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
            draft_versions::delete_all($args['ID']);
            return intval($args['ID']);
        }
        $args = array_replace([
            'content'       => '',
            'title'         => '',
            'user_id'       => get_current_user_id(),
            'share'         => null,
            'chapter_id'    => null,
            'updated'       => time()
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
        draft_versions::delete_all( 0 );
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
    public static function by_users ($user = null){
        if ($user === null ) {
            $user = get_current_user_id();
        }
        $table = self::$table;
        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE user_id = %s",[$user]));
        return $results;
    }
    public static function delete ($draft_id) {
        global $wpdb;
        $wpdb->delete(self::$table,[
            'ID'    => $draft_id
        ]);
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
    public static function get($xml) {
        $d = new DOMDocument();
        $r = $d->loadXML('<content>' . $xml . '</content>');
        if (! $r) {
            return;
        }
        $json = [];
        function parseHTMLStyle($style) {
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
        function BuildLeafsRecursive($lnode,$leaf,&$block) {
            if ($lnode instanceof DOMText) {
                $leaf['text'] = $lnode->wholeText;
                $block['children'][] = $leaf;
                return;
            }
            $style = parseHTMLStyle($lnode->getAttribute('style'));
            if (in_array($lnode->tagName,['b','strong']) || ($style['font-weight'] ?? '') === 'bold' ) {
                $leaf['bold'] = true;
            }
            if (in_array($lnode->tagName,['i','em']) || ($style['font-style'] ?? '') === 'italic' ) {
                $leaf['italic'] = true;
            }
            foreach ($lnode->childNodes as $tnode ) {
                BuildLeafsRecursive($tnode,$leaf,$block);
            }
        }
        foreach ($d->firstChild->childNodes as $node) {
            if (!$node instanceof DOMElement || $node->tagName !== 'p') {continue;}
            $block = [
                'children' => []
            ];
            $style = parseHTMLStyle($node->getAttribute('style'));
            if ( ($style['text-align'] ?? '') === 'center' ) {
                $block['type'] = 'center';
            }
            // Build Styles
            foreach ($node->childNodes as $leaf_el) {
                BuildLeafsRecursive($leaf_el,[],$block);
            }
            $json[] = $block;
        }
        echo (self::read(json_encode($json)));
    }
    public static function read($json) {
        function create_span_draft($leaf) {
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
    public static function simpleText($json) {
        $array = json_decode($json,true);
        $text = '';
        foreach ($array as $k => $para) {
            foreach ($para['children'] as $kk => $leaf) {
                $text .= $leaf['text'];
            }
            $text .= "\n";
        }
        return $text;
    }
    public static function compare($old,$new) {
        require(MAIN_DIR . 'content/finediff.php');
        $old_text = self::simpleText($old);
        $opcodes = FineDiff::getDiffOpcodes($old_text, self::simpleText($new) );
        $style =
        "
        <style>
        ins {
            color: green;
            background: #dfd;
            text-decoration: none;
        }
        del {
            color: red;
            background: #fdd;
            text-decoration: line-through;
        }
        </style>
        ";
        return $style . nl2br(FineDiff::renderDiffToHTMLFromOpcodes($old_text, $opcodes));
    }
    public static function output_odt($json,$dump) {
        function create_span_draft($leaf) {
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
            $span = "<text:span text:style-name=\"T$span_style\">" . $leaf['text'];
            $span .= "</text:span>";
            return $span;
        }
        $html = '';
        $array = json_decode($json,true);
        foreach ($array as $k => $para) {
            $para_style = '';
            if (isset($para['type']) && $para['type'] === 'center'){
                $para_style = 'C';
            }
            $html .= "<text:p text:style-name=\"P$para_style\">";
            foreach ($para['children'] as $kk => $leaf) {
                $html .= create_span_draft($leaf);
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
        function create_span_draft($leaf) {
            $leaf_tagNames = [
                'bold'      => 'strong',
                'italic'    => 'em'
            ];
            $span_text = $leaf['text'];
            foreach ($leaf_tagNames as $attr => $attr_tag) {
                if (! isset($leaf[$attr])){
                    continue;
                }
                $span_text = "<$attr_tag>" . $span_text . "</$attr_tag>";
            }
            return $span_text;
        }
        $html = '';
        $array = json_decode($json,true);
        foreach ($array as $k => $para) {
            $para_style = '';
            if (isset($para['type']) && $para['type'] === 'center'){
                $para_style = 'align="center"';
            }
            $html .= "<p$para_style>";
            foreach ($para['children'] as $kk => $leaf) {
                $html .= create_span_draft($leaf);
            }
            $html .= '</p>'; 
        }
        return $html;
    }
}
class drafts_chapter extends drafts {
    public static function save($draft_id_or_draft,$book_id,$title) {
        $draft = $draft_id_or_draft;
        if (is_numeric($draft_id_or_draft)) {
            $draft = self::get_by('ID', $draft_id_or_draft);
        }
        if ($draft === false){
            return false;
        }
        $chapter_id = wp_insert_post([
            'post_title'    => $title,
            'post_content'  => drafts_json::read($draft->content),
            'post_type'     => 'chapter',
            'post_status'   => 'publish',
            'post_parent'   => $book_id
        ]);
        return $chapter_id;
    }
}
class draft_versions extends drafts {
    protected static $table = 'draft_versions';
    public static function autosave($draft_id,$title,$content) {
        $draft = drafts::get_by('ID',$draft_id);
        if ( ($draft_id !== 'new' && $draft === false) || ($draft->content === $content && $draft->title === $title) ) {
            return false;
        }
        $draft_id = $draft_id === 'new' ? 0 : $draft_id;
        $table = self::$table;
        $hash = self::hash($content);
        $t = time();

        global $wpdb;
        $sql = $wpdb->prepare("SELECT * FROM $table WHERE draft_id = %s AND type ='autosave' ORDER BY created DESC LIMIT 1",[$draft_id]);
        $r = $wpdb->get_results($sql);
        $last_time = !empty($r) ? intval($r[0]->created) : 0;
        if ( $t - $last_time < 60) {
            return intval($r[0]->created);
        }
        $wpdb->insert(
            $table,
            [
                'draft_id'      => $draft_id,
                'title'         => $title,
                'content'       => $content,
                'created'       => $t,
                'hash'          => $hash,
                'type'          => 'autosave'
            ]
        );
        return $t;
    }
    public static function load_autosave($draft_id) {
        $draft_id = $draft_id === 'new' ? 0 : $draft_id;
        $table = self::$table;
        global $wpdb;
        $r = $wpdb->get_results($wpdb->prepare("SELECT title,content,created FROM $table WHERE draft_id = %s ORDER BY ID DESC",[$draft_id]));
        if (empty($r)) {
            return false;
        }
        $r[0]->timestamp = $r[0]->created;
        unset($r[0]->created);
        return $r[0];
    }
    public static function delete_all($draft_id) {
        global $wpdb;
        $wpdb->delete(
            self::$table,
            [
                'draft_id'  => $draft_id
            ]
        );
    }
}