<?php
class db {
    protected $table;
    protected $columns = [];
    protected $values = [];
    protected $prep = [];
    public $error;
    function __construct ($table,$columns) {
        $this->table = $table;
        $this->error = new err;
        if (!in_array($table,self::tables())) {
            return $this->error->add("table","table doesn't exist");
        }
        $existing_columns = self::columns($table);
        foreach ($columns as $column => $default) {
            if (is_int($column)) {
                $column = $default;
                $default = null;
            }
            if (! in_array($column,$existing_columns)) {
                return $this->error->add("column","column {$column} doesn't exist");
            }
            $this->columns[$column] = $default;
        }
    }
    function insert($data) {
        if ($this->error->has()) {
            return $this->error;
        }
        foreach ($this->columns as $column => $default) {
            $val = &$data[$column];
            if (!isset($val) && $default === null) {
                return $this->error->add('missing',"missing column {$column}");
            }
            $val = $val ?? $default;
            $this->prep[] = $val;
        }
        $this->values[] = "(" . sqlPlaceholder($this->columns) . ")";
    }
    function close() {
        if ($this->error->has()) {
            return $this->error;
        }
        $insert_sql =
        "INSERT INTO " . $this->table . "
        (`" . implode("`,`",array_keys($this->columns)) . "`)
        VALUES " . implode(',',$this->values) . ";" ;
        global $wpdb;
        $prepped = $wpdb->prepare($insert_sql,$this->prep);
        $rows = $wpdb->query($prepped);
    }
    protected static function tables () {
        global $wpdb;
        return array_keys($wpdb->get_results("show tables",OBJECT_K));
    }
    protected static function columns($table) {
        global $wpdb;
        return $wpdb->get_col("DESC " . $table, 0);
    }
}