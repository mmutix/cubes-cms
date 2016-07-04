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
    
}