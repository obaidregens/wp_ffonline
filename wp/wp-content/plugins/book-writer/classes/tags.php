<?php
class pairing {
    public $characters;
    public $count;

    static function add($book_id,array $characters) {
        if (count($characters) < 2) {
            return false;
        }
        global $wpdb;
        $r = $wpdb->get_results("SELECT * FROM character_pairings");
        $characters = array_map('strval',$characters);
        sort($characters);
        $n = [];
        foreach ($r as $k => $row) {
            $n[$row->pairing_id] = $n[$row->pairing_id] ?? [];
            $n[$row->pairing_id][] = $row->character_id;
        }
        $pp = 0;
        foreach ($n as $pairing_id => $characters_of) {
            sort($characters_of);
            if ($characters === $characters_of) {
                $pp = $pairing_id;
                break;
            }
        }
        // Pairing ID Got
        if ($pp === 0) {
            $pp = intval($wpdb->get_results("SELECT MAX(pairing_id) AS m FROM character_pairings")[0]->m)+1;
            $sql = 
            "INSERT INTO character_pairings
                (pairing_id, character_id)
            VALUES " . implode(",",array_fill(0,count($characters),"('$pp', %s)")) . "
            ";
            $wpdb->query($wpdb->prepare($sql,$characters));
        }
        ob_start();
        $wpdb->insert(
            'pairing_relationships',
            [
                'pairing_id'    => $pp,
                'book_id'       => $book_id,
                'priority'      => 'major',
                'added_time'    => time()
            ]
        );
        ob_end_clean();
        return intval($pp);
    }
    static function set($book_id, array $pairings, bool $append = false) {
        $pp_s = [];
        foreach ($pairings as $pairing) {
            $pp_s[] = self::add($book_id, $pairing);
        }
        if (! $append) {
            global $wpdb;
            $not_in = empty($pp_s) ? "" : "AND pairing_id NOT IN (" . implode(",",$pp_s) . ")";
            $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM pairing_relationships
                    WHERE book_id = %s
                    $not_in
                    "
                ,[$book_id])
            );
        }
        return true;
    }
    static function for_books(array $book_ids) {
        global $wpdb;
        $rel = empty($book_ids) ? [] : $wpdb->get_results(
            $wpdb->prepare(
                "SELECT book_id,pairing_id,priority,added_time FROM pairing_relationships WHERE book_id IN(" . implode(",",array_fill(0,count($book_ids),"%s")) . ")",
                $book_ids
            )
        );
        $char_ids = empty($rel) ? [] : $wpdb->get_results(
            $wpdb->prepare(
                "SELECT pairing_id,character_id FROM character_pairings WHERE pairing_id IN (" . implode(",",array_fill(0,count($rel),"%s")) . ")",
                array_column($rel,'pairing_id')
            )
        );
        $char_objs_wp = empty($char_ids) ? [] : get_terms([
            'taxonomy'  => 'character',
            'include'   => array_column($char_ids,'character_id'),
            'hide_empty'=> false
        ]);
        $char_objs = array_combine(array_column($char_objs_wp,'term_id'),$char_objs_wp);
        
        $pairings = [];
        foreach ($char_ids as $k => $row) {
            $kk = &$pairings[$row->pairing_id];
            $kk = $kk ?? [];
            $kk[] = $char_objs[$row->character_id] ?? null;
        }
        $return = [];
        foreach ($rel as $k => $row) {
            $kk = &$return[$row->book_id];
            $kk = $kk ?? [];
            $row->characters = $pairings[$row->pairing_id] ?? [];
            $kk[] = $row;
        }
        $empty_book_ids = array_diff($book_ids,array_column($rel,'book_id'));
        foreach ($empty_book_ids as $book_id ) {
            $return[$book_id] = [];
        }
        return $return;
    }
    static function query(bool $hide_empty = true) {
        global $wpdb;
        $i = $wpdb->get_results("SELECT * FROM character_pairings");
        $b = empty($i) ? [] : $wpdb->get_results("SELECT pairing_id,book_id FROM pairing_relationships WHERE pairing_id IN(" . implode(",",array_column($i,'pairing_id')) . ")");
        $pairing_count = array_count_values(array_column($b,'pairing_id'));
        $pairing_ids = [];
        foreach ($b as $row) {
            $k = &$pairing_ids[$row->pairing_id];
            $k = $k ?? [];
            $k[] = $row->book_id;
        }
        $terms = (new WP_Term_Query([
            'taxonomy'		=> 'character',
            'include'       => array_column($i,'character_id'),
            'hide_empty'    => false
        ]))->terms ?: [];
        $characters = array_combine(array_column($terms,'term_id'),$terms);
        $pairings = [];
        foreach ($i as $row) {
            $count = $pairing_count[$row->pairing_id] ?? 0;
            if ($count < 1 && $hide_empty) {
                continue;
            }
            $k = &$pairings[$row->pairing_id];
            $k = $k ?? new pairing;
            $k->characters = $k->characters ?? [];
            $k->characters[] = $characters[$row->character_id] ?? null;
            $k->count = $count;
            $k->book_ids = $pairing_ids[$row->pairing_id];
            $k->pairing_id = $row->pairing_id;
        }
        return $pairings;
    }
}