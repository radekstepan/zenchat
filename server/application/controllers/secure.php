<?php if (!defined('FARI')) die();

class Secure_Controller extends Fari_Controller {

    private $clientIdentifier = 'ZenChat/0.1';

	public static function _desc() { return 'Login & logout, user authentication'; }

    public function _init() {
        // get messages for us
        $this->view->system = Fari_Message::get();
    }

	public function index($parameter) {
        // authenticate user if form data POSTed
		if (isset($_POST['username'])) {
		    if (Fari_User::authenticate($_POST['username'], $_POST['password'], $_POST['token'], 'realname')) {
                // JSON response for our client
                if ($_SERVER['HTTP_USER_AGENT'] == $this->clientIdentifier)
                    echo json_encode(array('status'=>'success'));
                // standard messaging using Fari_Message and $_SESSION
                else {
                    $user = Fari_User::getCredentials();
                    Fari_Message::notify("Welcome back $user!");
                    $this->redirect('/shoutbox/'); die();
                }
            } else {
                // JSON response for our client
                if ($_SERVER['HTTP_USER_AGENT'] == $this->clientIdentifier)
                    echo json_encode(array('status'=>'fail'));
                // standard messaging using Fari_Message and $_SESSION
                else {
                    Fari_Message::fail("Failed to authenticate!");
                    $this->view->system = Fari_Message::get();
                }
            }
        }
		// create token & display login form
		$this->view->token = Fari_Token::create();
		if ($_SERVER['HTTP_USER_AGENT'] != $this->clientIdentifier) $this->view->display('login');
	}
	
	public function logout() {
        $user = Fari_User::getCredentials();
        Fari_Message::notify("Thanks for the visit $user.");
		Fari_User::signOut();
		$this->redirect('/secure/');
	}
}
