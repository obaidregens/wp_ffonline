<?php
class import_stories {
    static function get ($author_id) {
        $html = file_get_contents("https://www.fanfiction.net/u/" . $author_id);
        $gzip = gzdecode($html);
        if ($gzip !== false){
            $html = $gzip;
            $gzip = null;
        }
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $r = $doc->loadHTML($html);
        $html = null;
        if (!$r){
            return;
        }
        $XPath = new DOMXPath ($doc);
        $nodes = ($XPath->query('//div[@class="z-list mystories"]/a[@class="stitle"]'));
        $return = [];
        foreach ($nodes as $node ) {
            $title = $node->textContent;
            $href = $node->attributes->getNamedItem("href")->value;
            $id = arr::non_empty(explode('/',$href))[1];
            $return[] = [
                'ID'        => intval($id),
                'title'     => $title
            ];
        }
        return $return;
    }
}