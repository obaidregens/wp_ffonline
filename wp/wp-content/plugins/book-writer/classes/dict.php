<?php
class dict {
    protected static $table = 'dictionary_data';
    function __construct($word,$landing_id) {
        $table = self::$table;
        global $wpdb;
        $r = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE word = %s",$word));
        if (!empty($r)) {
            $this->data->google = json_decode($r[0]->google,true);
            $this->data->thesaurus_com = json_decode($r[0]->thesaurus_com,true);
            return;
        }
        $this->data->google = json_decode(file_get_contents("https://api.dictionaryapi.dev/api/v2/entries/en/$word"),true);
        $this->data->thesaurus_com = json_decode(file_get_contents("https://tuna.thesaurus.com/pageData/$word"),true)['data'];
        if (($this->data->google['title'] ?? "") === "No Definitions Found" && $this->data->thesaurus_com === null) {
            return;
        }
        $wpdb->insert(
            $table,[
                'word'              => $word,
                'google'            => json_encode($this->data->google),
                'thesaurus_com'     => json_encode($this->data->thesaurus_com),
                'milli_timestamp'   => millitime(),
                'landing_id'        => $landing_id
            ]
        );
    }
    function google_synonym() {
        $data = $this->data->google;
        if ($data['title'] === "No Definitions Found") {
            return [];
        }
        $all = [];
        $synonyms = [];
        foreach ($data as $word ) {
            foreach (($word['meanings'] ?? []) as $meaning) {
                foreach (($meaning['definitions'] ?? []) as $definition ) {
                    $syn = $definition['synonyms'] ?? [];
                    $synonyms[] = $syn;
                    $all = array_merge($all,$syn);
                }
            }
        }
        return $all;
    }
    function thesaurus_com_synonym() {
        $data = $this->data->thesaurus_com;
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
    function synonym() {
        $google = $this->google_synonym();
        if (count($google) >= 4) {
            return array_slice($google,0,4);
        }
        $thesaurus_com = $this->thesaurus_com_synonym();
        return array_slice(array_merge($google,$thesaurus_com),0,4);
    }
}