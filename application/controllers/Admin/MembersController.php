<?php

class Admin_MembersController extends Zend_Controller_Action
{
    public function indexAction() {
        
$flashMessenger = $this->getHelper('FlashMessenger');
        
         $systemMessages = array(
             'success'=>$flashMessenger->getMessages('success'),
             'errors'=>$flashMessenger->getMessages('errors')
             
         );
         


//prikaz svih member-a
        
         $cmsMembersDbTable = new Application_Model_DbTable_CmsMembers();
        
        // $select je objekat klase Zend_Db_Select
        $select = $cmsMembersDbTable->select();
        
        $select->order('order_number');
        
        
        //debug za db select - vraca se sql upit
       //die($select->assemble());
        
        $members = $cmsMembersDbTable->fetchAll($select);
        
        $this->view->members = $members;//prosledjivanje rezultata
        $this->view->systemMessages = $systemMessages;
        
}

public function addAction() {
    
        $request = $this->getRequest();
        $flashMessenger = $this->getHelper('FlashMessenger');

        $form = new Application_Form_Admin_MemberAdd();

//default form data
        $form->populate(array(
        ));

        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors'),
            'info' => $flashMessenger->getMessages('errors'),
        );

        if ($request->isPost() && $request->getPost('task') === 'save') {

            try {

                //check form is valid
                if (!$form->isValid($request->getPost())) {
                    throw new Application_Model_Exception_InvalidInput('Invalid data was sent for new member.');
                }

                //get form data
                $formData = $form->getValues();
                
                //Insertujemo novi zapis u tabelu
                $cmsMembersTable = new Application_Model_DbTable_CmsMembers();
                $cmsMembersTable->insert($formData);
                
                //die(print_r($formData, true));

                //do actual task
                //save to database etc
                //set system message
                $flashMessenger->addMessage('Member has been saved', 'success');
                // $flashMessenger->addMessage('Or maybe somethign is wrong', 'errors');

                //redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_members',
                            'action' => 'index',
                            ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }

        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;
    }
    
    
    public function editAction() {
        
        $request = $this->getRequest();
        
        $id - (int) $request->getParam('id');
        
        if ($id <= 0) {
            
            throw new Zend_Controller_Router_Exception('Invalid member id:' . $id , 404);//prekida se izvrsavanje i prikazuje se "page not found"
        }
        
        $cmsMembersTable = new Application_Model_DbTable_CmsMembers();
        
        $member = $cmsMembersTable->getMemberById($id);
        
        if (empty($member)) {
            
            throw new Zend_Controller_Router_Exception('No member is found with id:' . $id , 404);
        }
        
         $flashMessenger = $this->getHelper('FlashMessenger');



        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors'),
        );
        
        $form = new Application_Form_Admin_MemberAdd();
//
////default form data
      $form->populate($member);

        if ($request->isPost() && $request->getPost('task') === 'update') {

            try {

                //check form is valid
                if (!$form->isValid($request->getPost())) {
                    throw new Application_Model_Exception_InvalidInput('Invalid data was sent for member.');
                }

                //get form data
                $formData = $form->getValues();
                
                //Radimo update postojeceg zapisa u tabeli

                $cmsMembersTable->update($formData, 'id = ' . $member['id']);
                

                //set system message
                $flashMessenger->addMessage('Member has been updated', 'success');
                // $flashMessenger->addMessage('Or maybe somethign is wrong', 'errors');

                //redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_members',
                            'action' => 'index',
                            ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }

        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;

        $this->view->member = $member;
    }
    
    
    public function deleteAction() {
        
        $request = $this->getRequest();
        
        if (!$request->isPost() || $request->getPost('task') != 'delete') {
            //request is not post redirect to index page
            //or task is not delete
            //redirect to index page
            
            
             //redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_members',
                            'action' => 'index',
                            ), 'default', true);
            
        }
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        
        try {
            //read $_POST['id']
            $id - (int) $request->getPost('id');

            if ($id <= 0) {

                throw new Application_Model_Exception_InvalidInput('Invalid member id:' . $id );


            $cmsMembersTable = new Application_Model_DbTable_CmsMembers();

            $member = $cmsMembersTable->getMemberById($id);

            if (empty($member)) {
                 throw new Application_Model_Exception_InvalidInput('No member is found with id' . $id );
            }

                  $flashMessenger->addMessage('Invalid member id:' . $id , 'errors');
                 //redirect to same or another page
                    $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_members',
                                'action' => 'index',
                                ), 'default', true);

            }

             $cmsMembersTable->deleteMember($id);

               $flashMessenger->addMessage('Member ' . $member['first_name'] . ' ' . $member['last_name'] . 'has been deleted', 'success');
                 //redirect to same or another page
                    $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_members',
                                'action' => 'index',
                                ), 'default', true);


        } catch (Application_Model_Exception_InvalidInput $ex) {

            $flashMessenger->addMessage($ex->getMessage() , 'errors');
             //redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_members',
                            'action' => 'index',
                            ), 'default', true);
        }
        
    }
       
    
      public function enableAction() {
        
        $request = $this->getRequest();
        
        if (!$request->isPost() || $request->getPost('task') != 'enable') {
            //request is not post redirect to index page
            //or task is not delete
            //redirect to index page
            
            
             //redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_members',
                            'action' => 'index',
                            ), 'default', true);
            
        }
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        
        try {
            //read $_POST['id']
            $id - (int) $request->getPost('id');

            if ($id <= 0) {

                throw new Application_Model_Exception_InvalidInput('Invalid member id:' . $id );


            $cmsMembersTable = new Application_Model_DbTable_CmsMembers();

            $member = $cmsMembersTable->getMemberById($id);

            if (empty($member)) {
                 throw new Application_Model_Exception_InvalidInput('No member is found with id' . $id );
            }

                  $flashMessenger->addMessage('Invalid member id:' . $id , 'errors');
                 //redirect to same or another page
                    $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_members',
                                'action' => 'index',
                                ), 'default', true);

            }

             $cmsMembersTable->enableMember($id);

               $flashMessenger->addMessage('Member ' . $member['first_name'] . ' ' . $member['last_name'] . 'has been enabled', 'success');
                 //redirect to same or another page
                    $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_members',
                                'action' => 'index',
                                ), 'default', true);


        } catch (Application_Model_Exception_InvalidInput $ex) {

            $flashMessenger->addMessage($ex->getMessage() , 'errors');
             //redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_members',
                            'action' => 'index',
                            ), 'default', true);
        }
        
    }
    
    
        public function disableAction() {
        
        $request = $this->getRequest();
        
        if (!$request->isPost() || $request->getPost('task') != 'disable') {
            //request is not post redirect to index page
            //or task is not delete
            //redirect to index page
            
            
             //redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_members',
                            'action' => 'index',
                            ), 'default', true);
            
        }
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        
        try {
            //read $_POST['id']
            $id - (int) $request->getPost('id');

            if ($id <= 0) {

                throw new Application_Model_Exception_InvalidInput('Invalid member id:' . $id );


            $cmsMembersTable = new Application_Model_DbTable_CmsMembers();

            $member = $cmsMembersTable->getMemberById($id);

            if (empty($member)) {
                 throw new Application_Model_Exception_InvalidInput('No member is found with id' . $id );
            }

                  $flashMessenger->addMessage('Invalid member id:' . $id , 'errors');
                 //redirect to same or another page
                    $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_members',
                                'action' => 'index',
                                ), 'default', true);

            }

             $cmsMembersTable->disableMember($id);

               $flashMessenger->addMessage('Member ' . $member['first_name'] . ' ' . $member['last_name'] . 'has been disabled', 'success');
                 //redirect to same or another page
                    $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_members',
                                'action' => 'index',
                                ), 'default', true);


        } catch (Application_Model_Exception_InvalidInput $ex) {

            $flashMessenger->addMessage($ex->getMessage() , 'errors');
             //redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_members',
                            'action' => 'index',
                            ), 'default', true);
        }
        
    }
    
}
