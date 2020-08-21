<?php
class dict {
    function __construct($word) {
        $this->data = json_decode(file_get_contents("https://tuna.thesaurus.com/pageData/$word"),true)['data'];
    }
    function synonym() {
        $data = $this->data;
        if ($data === null) {
            return [];
        }
        $all = [];
        $defs = $data['definitionData']['definitions'];
        foreach ($defs as $k => $def ) {
            $u = array_column($def['synonyms'],'term');
            if ( $def['synonyms'][0]['similarity'] !== '100' ) {
                continue;
            }
            $all[] = $u[0];
            $c_all = count($all);
            if ($c_all >= 4) {
                return $all;
            }
            if ( $k+1 === count($defs) ) {
                $b = 1;
                for ( $i=$c_all; $i<4; $i++ ) { 
                    if (! isset($u[$b])){
                        return $all;
                    }
                    $all[] = $u[$b];
                    $b++;
                }
            }
        }
        return $all;
    }
}