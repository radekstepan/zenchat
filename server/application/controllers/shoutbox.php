<?php if (!defined('FARI')) die();

class Shoutbox_Controller extends Fari_Controller {

	public static function _desc() { return 'Display & add messages'; }
	
	public function _init() {
		// is user authenticated?
		if (!Fari_User::isAuthenticated('realname')) {
            $this->redirect('/secure/'); die();
        }
		
		// get user's credentials
		$this->view->user = Fari_User::getCredentials();
		
		// get messages for us
		$this->view->system = Fari_Message::get();
	}
	
	public function index($param) {
		// use paginator to display items on a page passed as the first parameter
		$this->view->messages = Messages::get();

		$this->view->display('shoutbox');
	}

    // AJAX
    public function get($lastMessageId) {
        if (Fari_Filter::isInt($lastMessageId)) {
            // get is the freshest messages
            $newMessages = Messages::get($lastMessageId);
            // handle with JSON
            echo json_encode($newMessages);
        }
    }

    // AJAX
    public function add() {
        if (isset($_POST['text'])) {
			// add a new message under current credentials
			if (Messages::add($_POST['text'], Fari_User::getCredentials())) {
                // handle with JSON
                echo json_encode(array('status'=>'success'));
            } else echo json_encode(array('status'=>'fail'));
		}
	}
}
