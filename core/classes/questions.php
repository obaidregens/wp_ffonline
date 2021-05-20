<?php
class questions {
    protected static $table = 'questions';
    static function get($q_id) {
        $table = self::$table;
        global $wpdb;
        $sql = $wpdb->prepare("SELECT * FROM $table WHERE ID = %d",[$q_id]);
        $r = $wpdb->get_results($sql);
        return empty($r) ? false : $r[0];
    }
    static function new ($args) {
        $args = array_replace([
            'email'         => '',
            'user_id'       => get_current_user_id(),
            'landing_id'    => "",
            'for_user'      => 0,
            'category'      => 'General',
            'is_anonymous'  => 1,
            'question'      => '',
            'answer'        => '',
            'status'        => 'pending',
        ],$args);
        $e = new err;
        if ( trim($args['email']) !== "" && !filter_var($args['email'], FILTER_VALIDATE_EMAIL)){
            $e->add('email','Invalid Format.');
        }
        $e->one_of('is_anonymous',$args['is_anonymous'],[0,1]);
        $e->one_of('status',$args['status'],['pending','public']);
        $e->numeric('landing_id',$args['landing_id']);
        $e->numeric('user_id',$args['user_id']);
        $e->numeric('for_user',$args['for_user']);
        if ( trim($args['question']) === ""){
            $e->add('question',"Question is empty.");
        }
        if ($e->has()) {
            return $e;
        }
        $args['asked_millitime'] = millitime();
        $args['replied_millitime'] = 0;
        $args['deleted_millitime'] = 0;
        
        global $wpdb;
        $wpdb->insert(
            self::$table,
            $args
        );
    }
    static function answer($question_id,$answer,$altered_question = "",$category = "General",$link = 0) {
        $q = questions::get($question_id);
        global $wpdb;
        if ($link!==0) {
            $link = questions::get($link);
            if (!$link) {
                return false;
            }
            if ($q->email !== ""){
                $mail = new SendGrid("faq-alert",[
                    "link"  => "https://fanfiction.online/faq/{$link->ID}"
                ]);
                try { $mail->send([$q->email]); }
                catch(Exception $e) {}
            }
            $rows = $wpdb->update(self::$table,[
                'status'    => 'linked',
            ],[
                'ID'        => $q->ID
            ]);
            return true;
        }
        $prev = [
            'answer'                => $answer,
            'replied_millitime'     => millitime(),
            'status'                => 'public',
            'category'              => $category
        ];
        if ($altered_question !== "") {
            $prev['question'] = $altered_question;
        }
        $wpdb->update(
            self::$table,
            $prev,
            [
                'ID'                    => $question_id
            ]
        );
        if ($q->email !== ""){
            $mail = new SendGrid("faq-alert",[
                "link"  => "https://fanfiction.online/faq/$question_id"
            ]);
            try { $mail->send([$q->email]); }
            catch(Exception $e) { }
        }
    }
    static function delete($question_id) {
        global $wpdb;
        $wpdb->update(
            self::$table,
            [
                'deleted_millitime'     => millitime(),
                'status'                => 'deleted'
            ],
            [
                'ID'                    => $question_id
            ]
        );
    }
    static function query(array $args = []) {
        $table = self::$table;
        $sql = "SELECT * FROM $table WHERE 1";
        $prep = [];
        $args['status'] = $args['status'] ?? ['public'];
        if (empty($args['status'])) {
            return [];
        }
        $sql .= " AND status IN(" . sqlPlaceholder($args['status']) . ")";
        $prep = $args['status'];

        if (isset($args['users'])){
            if (empty($args['users'])) {
                return [];
            }
            $args['users'] = (array) $args['users'];
            $sql .= " AND user_id IN(" . sqlPlaceholder($args['users'],'%d') . ")";
            $prep = array_merge($prep,$args['users']);
        }
        
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare($sql,$prep));
    }
    static function by_category(array $args = []) {
        $r = self::query($args);
        $n = [];
        foreach ($r as $k => $v) {
            $p = &$n[$v->category];
            $p = $p ?? [];
            $p[] = $v;
        }
        return $n;
    }
}