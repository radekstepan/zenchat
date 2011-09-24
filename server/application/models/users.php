<?php if (!defined('FARI')) die();

class Users extends Fari_Model {
    
    public static function add($username, $password, $realname) {
        // escape input
        $username = Fari_Escape::html($username);
        $password = Fari_Escape::html($password);
        $realname = Fari_Escape::html(Fari_Decode::javascript($realname));

        // verify that credentials are provided in a valid form
        if (!empty($username) && ctype_alnum($username) && strlen($username) <= 10) {
            if (!empty($password) && ctype_alnum($password) && strlen($password) <= 10) {
                if (!empty($realname) && strlen($realname) <= 100) {
                    // all OK, db insert
                    Fari_Db::insert('users', array('username' => $username, 'password' => sha1($password),
                        'realname' => $realname));
                    Fari_Message::success("Welcome $realname!");
                    return TRUE;
                } else Fari_Message::fail("Please provide a valid real name.");
            } else Fari_Message::fail("Please provide a valid password.");
        } else Fari_Message::fail("Please provide a valid username.");

        return FALSE;
    }
}
