<?php if (!defined('FARI')) die();

class Messages extends Fari_Model {
    
    public static function add($text, $author) {
        // escape input and decode from JavaScript
        $text =  Fari_Escape::text(Fari_Decode::javascript($text));
        $author = Fari_Escape::text(Fari_Decode::javascript($author));
        
        if (!empty($text) && !empty($author)) {
            // db insert
            Fari_Db::insert('messages', array('text' => $text, 'author' => $author, 'time' => time()));
            return TRUE;
        }
        return FALSE;
    }

    public static function get($lastMessageId=NULL) {
        // fetch the newest messages from the system, display all on startup
        $lastMessageId = (isset($lastMessageId)) ? "id > $lastMessageId" : NULL;
        // db select
        $result = Fari_Db::select("messages", "*", $lastMessageId, "time DESC");
        foreach ($result as &$message) {
            // nicely format date for all
            $message['time'] = date("F j, g:i a", $message['time']);
        }
        return $result;
    }
}
