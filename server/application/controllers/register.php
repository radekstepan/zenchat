<?php if (!defined('FARI')) die();

class Register_Controller extends Fari_Controller {

	public static function _desc() { return 'Register a new user form'; }

    public function _init() {
        // get messages for us
        $this->view->system = Fari_Message::get();
    }

	public function index($param) {
        // check if we've submitted a new user registration
		if (isset($_POST['username'])) {
            if (Users::add($_POST['username'], $_POST['password'], $_POST['realname'])) {
                // OK, take us to the admin (or most often to the login)
                $this->redirect('/secure/'); die();
            // failed to add a user, show the form again
            } else {
                $this->redirect('/register/'); die();
            }
        }
        
		$this->view->display('register');
	}
}
