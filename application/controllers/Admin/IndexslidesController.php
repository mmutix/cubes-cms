<?php

class Admin_IndexslidesController extends Zend_Controller_Action {

    public function indexAction() {

        $flashMessenger = $this->getHelper('FlashMessenger');

        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );

        //prikaz svih indexSlide-a
        $cmsindexSlidesDbTable = new Application_Model_DbTable_CmsIndexSlides();

 	$indexSlides = $cmsindexSlidesDbTable->search(array(
			//'filters' => array(
			//	'status' => Application_Model_DbTable_CmsIndexSlides::STATUS_ENABLED
			//),
			'orders' => array(
				'order_number' => 'ASC'
			),
			//'limit' => 4,
			//'page' => 2
		));

        $this->view->indexSlides = $indexSlides; 
        $this->view->systemMessages = $systemMessages;
    }

    public function addAction() {

        $request = $this->getRequest();
        $flashMessenger = $this->getHelper('FlashMessenger');

        $form = new Application_Form_Admin_IndexSlideAdd();

//default form data
        $form->populate(array(
        ));

        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors'),
        );

        if ($request->isPost() && $request->getPost('task') === 'save') {

            try {

                //check form is valid
                if (!$form->isValid($request->getPost())) {
                    throw new Application_Model_Exception_InvalidInput('Invalid data was sent for new indexSlide.');
                }

                //get form data
                $formData = $form->getValues();

                //remove key index_slide_photo  form data because there is no column index_slide_photo in cms_indexSlides 
                unset($formData['index_slide_photo']);

                //Insertujemo novi zapis u tabelu
                $cmsindexSlidesTable = new Application_Model_DbTable_CmsIndexSlides();

                // insert indexSlide returns ID of the new indexSlide
                $indexSlideId = $cmsindexSlidesTable->insertindexSlide($formData);


                if ($form->getElement('index_slide_photo')->isUploaded()) {

                    // photo is uploaded 
                    $fileInfos = $form->getElement('index_slide_photo')->getFileInfo('index_slide_photo');
                    $fileInfo = $fileInfos['index_slide_photo']; // ILI NA OVAJ NACIN $fileInfos =$_FILES['index_slide_photo']

                    try {
                        // open uploaded photo in temporary directory
                        $indexSlidePhoto = Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);

                        $indexSlidePhoto->fit(600, 400);

                        //snimanje slike , mesto gde ce se slika sacuvati
                        $indexSlidePhoto->save(PUBLIC_PATH . '/uploads/index-slides/' . $indexSlideId . '.jpg');
                    } catch (Exception $ex) {

                        //set system message
                        $flashMessenger->addMessage('indexSlide has been saved but error occured during image processing', 'errors');
                        // $flashMessenger->addMessage('Or maybe somethign is wrong', 'errors');
                        //redirect to same or another page
                        $redirector = $this->getHelper('Redirector');
                        $redirector->setExit(true)
                                ->gotoRoute(array(
                                    'controller' => 'admin_indexslides',
                                    'action' => 'edit',
                                    'id' => $indexSlideId,
                                        ), 'default', true);
                    }



                    //print_r($fileInfo);
                    //die();
                }


                //die(print_r($formData, true));
                //do actual task
                //save to database etc
                //
                //set system message
                $flashMessenger->addMessage('indexSlide has been saved', 'success');
                // $flashMessenger->addMessage('Or maybe somethign is wrong', 'errors');
                //redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_indexslides',
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

        $id = (int) $request->getParam('id');

        if ($id <= 0) {
            //prekida se izvrsavanje i prikazuje se "page not found"
            throw new Zend_Controller_Router_Exception('Invalid indexSlide id:' . $id, 404);
        }

        $cmsindexSlidesTable = new Application_Model_DbTable_CmsIndexSlides();

        $indexSlide = $cmsindexSlidesTable->getindexSlideById($id);

        if (empty($indexSlide)) {

            throw new Zend_Controller_Router_Exception('No indexSlide is found with id:' . $id, 404);
        }

        $flashMessenger = $this->getHelper('FlashMessenger');



        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors'),
        );

        $form = new Application_Form_Admin_IndexSlideAdd();
//
////default form data
        $form->populate($indexSlide);

        if ($request->isPost() && $request->getPost('task') === 'update') {

            try {

                //check form is valid
                if (!$form->isValid($request->getPost())) {
                    throw new Application_Model_Exception_InvalidInput('Invalid data was sent for indexSlide.');
                }

                //get form data
                $formData = $form->getValues();

                //remove key index_slide_photo  form data because there is no column index_slide_photo in cms_indexSlides 
                unset($formData['index_slide_photo']);
                //Radimo update postojeceg zapisa u tabeli

                if ($form->getElement('index_slide_photo')->isUploaded()) {

                    // photo is uploaded 
                    $fileInfos = $form->getElement('index_slide_photo')->getFileInfo('index_slide_photo');
                    $fileInfo = $fileInfos['index_slide_photo']; // ILI NA OVAJ NACIN $fileInfos =$_FILES['index_slide_photo']

                    try {
                        // open uploaded photo in temporary directory
                        $indexSlidePhoto = Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);

                        $indexSlidePhoto->fit(600, 400);

                        //snimanje slike , mesto gde ce se slika sacuvati
                        $indexSlidePhoto->save(PUBLIC_PATH . '/uploads/index-slides/' . $indexSlide['id'] . '.jpg');
                    } catch (Exception $ex) {

                        throw new Application_Model_Exception_InvalidInput('Error occured during image processing: ' . $ex->getMessage());
                    }



                    //print_r($fileInfo);
                    //die();
                }


                //$cmsindexSlidesTable->update($formData, 'id = ' . $indexSlide['id']);
                $cmsindexSlidesTable->updateindexSlide($indexSlide['id'], $formData);

                //set system message
                $flashMessenger->addMessage('indexSlide has been updated', 'success');
                // $flashMessenger->addMessage('Or maybe somethign is wrong', 'errors');
                //redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_indexslides',
                            'action' => 'index',
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }

        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;
        $this->view->indexSlide = $indexSlide;
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
                        'controller' => 'admin_indexslides',
                        'action' => 'index',
                            ), 'default', true);
        }

        $flashMessenger = $this->getHelper('FlashMessenger');

        try {
            //(int) sve sto nije integer pretvara se u nulu
            //read $_POST['id']
            $id = (int) $request->getPost('id');
            if ($id <= 0) {

                throw new Application_Model_Exception_InvalidInput('Invalid indexSlide id:' . $id , 'errors');
            }
            $cmsindexSlidesTable = new Application_Model_DbTable_CmsIndexSlides();

            $indexSlide = $cmsindexSlidesTable->getindexSlideById($id);

            if (empty($indexSlide)) {
                throw new Application_Model_Exception_InvalidInput('No indexSlide is found with id' . $id , 'errors');
            }


            $cmsindexSlidesTable->deleteindexSlide($id);

            $flashMessenger->addMessage('IndexSlide ' . $indexSlide['title']);
            //redirect to same or another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_indexslides',
                        'action' => 'index',
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {

            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            //redirect to same or another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_indexslides',
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
                        'controller' => 'admin_indexslides',
                        'action' => 'index',
                            ), 'default', true);
        }

        $flashMessenger = $this->getHelper('FlashMessenger');


        try {
            //read $_POST['id']
            $id = (int) $request->getPost('id');

            if ($id <= 0) {

                throw new Application_Model_Exception_InvalidInput('Invalid indexSlide id: ' . $id , 'errors');
            }

            $cmsindexSlidesTable = new Application_Model_DbTable_CmsIndexSlides();

            $indexSlide = $cmsindexSlidesTable->getindexSlideById($id);

            if (empty($indexSlide)) {
                throw new Application_Model_Exception_InvalidInput('No indexSlide is found with id: ' . $id , 'errors');
            }


            $cmsindexSlidesTable->enableindexSlide($id);

            $flashMessenger->addMessage('indexSlide ' . $indexSlide['title']);
            //redirect to same or another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_indexslides',
                        'action' => 'index',
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {

            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            //redirect to same or another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_indexslides',
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
                        'controller' => 'admin_indexslides',
                        'action' => 'index',
                            ), 'default', true);
        }

        $flashMessenger = $this->getHelper('FlashMessenger');


        try {
            //read $_POST['id']
            $id = (int) $request->getPost('id');

            if ($id <= 0) {

                throw new Application_Model_Exception_InvalidInput('Invalid indexSlide id: ' . $id , 'errors');
            }

            $cmsindexSlidesTable = new Application_Model_DbTable_CmsIndexSlides();

            $indexSlide = $cmsindexSlidesTable->getindexSlideById($id);

            if (empty($indexSlide)) {
                throw new Application_Model_Exception_InvalidInput('No indexSlide is found with id' . $id , 'errors');
            }

            $cmsindexSlidesTable->disableindexSlide($id);

            $flashMessenger->addMessage('indexSlide ' . $indexSlide['title']);
            //redirect to same or another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_indexslides',
                        'action' => 'index',
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {

            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            //redirect to same or another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_indexslides',
                        'action' => 'index',
                            ), 'default', true);
        }
    }

    public function updateorderAction() {

        $request = $this->getRequest();

        if (!$request->isPost() || $request->getPost('task') != 'saveOrder') {
            // request is not post
            // or task is not saveOrder
            //redirect to index page
            //redirect to same or another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_indexslides',
                        'action' => 'index'
                            ), 'default', true);
        }

        $flashMessenger = $this->getHelper('FlashMessenger');

        try {

            $sortedIds = $request->getPost('sorted_ids');

            if (empty($sortedIds)) {
                throw new Application_Model_Exception_InvalidInput('Sorted ids are not sent');
            }

            $sortedIds = trim($sortedIds, ' ,');

            if (!preg_match('/^[0-9]+(,[0-9]+)*$/', $sortedIds)) {
                throw new Application_Model_Exception_InvalidInput('Invalid sorted ids: ' . $sortedIds);
            }

            $sortedIds = explode(',', $sortedIds);

            $cmsindexSlidesTable = new Application_Model_DbTable_CmsIndexSlides();

            $cmsindexSlidesTable->updateOrderOfindexSlides($sortedIds);

            $flashMessenger->addMessage('Order is successfully saved', 'success');

            //redirect to same or another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_indexslides',
                        'action' => 'index'
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {

            $flashMessenger->addMessage($ex->getMessage(), 'errors');

            //redirect to same or another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_indexslides',
                        'action' => 'index'
                            ), 'default', true);
        }
    }
    
     public function dashboardAction() {
        
        $cmsindexSlidesDbTable = new Application_Model_DbTable_CmsIndexSlides();
		
		$totalNumberOfindexSlides = $cmsindexSlidesDbTable->count();
		$activeindexSlides = $cmsindexSlidesDbTable->count(array(
			'status' => Application_Model_DbTable_CmsIndexSlides::STATUS_ENABLED
		));
                
        $this->view->totalNumberOfindexSlides = $totalNumberOfindexSlides;        
        $this->view->activeindexSlides = $activeindexSlides;
        

	}

}
