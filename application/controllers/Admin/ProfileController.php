<?php

class Admin_ProfileController extends Zend_Controller_Action
{
    public function indexAction() {
                  //redirect na login stranu      
         $redirector = $this->getHelper('Redirector');
        
        $redirector instanceof Zend_Controller_Action_Helper_Redirector;
        
        $redirector->setExit(true)
                ->gotoRoute(array(
                    
                    'controller'=>'admin_profile',
                    'action'=> 'edit',
             
                ),'default', true);
                
        //redirect ako nemamo dodatne parametre
        $redirector->setExit(true)
                ->gotoSimple('login', 'admin_session');
    }
    
    public Function editAction() {
        
        $user = Zend_Auth::getInstance()->getIdentity();

$request = $this->getRequest();
$flashMessenger = $this->getHelper('FlashMessenger');

$form = new Application_Form_Admin_ProfileEdit();

//default form data
$form->populate($user);

$systemMessages = array(
	'success' => $flashMessenger->getMessages('success'),
	'errors' => $flashMessenger->getMessages('errors')
);

if ($request->isPost() && $request->getPost('task') === 'save') {
	
	try {
		
		//check form is valid
		if (!$form->isValid($request->getPost())) {
			throw new Application_Model_Exception_InvalidInput('Invalid data has been sent for user profile');
		}
		
		//get form data
		$formData = $form->getValues();
		
		// do actual task
		//save to database etc
		
		//set system message
		$flashMessenger->addMessage('Profile has been saved', 'success');
		
		//redirect to same or another page
		$redirector = $this->getHelper('Redirector');
		$redirector->setExit(true)
			->gotoRoute(array(
				'controller' => 'admin_dashboard',
				'action' => 'index'
			), 'default', true);
		
	} catch (Application_Model_Exception_InvalidInput $ex) {
		$systemMessages['errors'][] = $ex->getMessage();
	}
}

$this->view->systemMessages = $systemMessages;
$this->view->form = $form;
    }
    
    public function changepasswordAction() {
        
    }
}
