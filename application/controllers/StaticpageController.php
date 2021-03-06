<?php

class StaticpageController extends Zend_Controller_Action //namena kontrolera je da vrsi prikazivanje stranica tipa @static page@
{
    public function indexAction() {

        $request = $this->getRequest();

        $sitemapPageId = $request->getParam('sitemap_page_id');

        if ($sitemapPageId <= 0) {
            throw new Zend_Controller_Router_Exception('Invalid sitemap page id:' . $sitemapPageId, 404);
        }


        $cmsSiteMapPageDbTable = new Application_Model_DbTable_CmsSitemapPages();

        $sitemapPage = $cmsSiteMapPageDbTable->getSitemapPageById($sitemapPageId);

        if (!$sitemapPage) {
            throw new Zend_Controller_Router_Exception('No sitemap page is found for id:' . $sitemapPageId, 404);

            if (
                    $sitemapPage['status'] == Application_Model_DbTable_CmsSitemapPages::STATUS_DISABLED
                    //check if user is not logged in , 
                    //than preview is not avaliable
                    //for disabled pages
                    && Zend_Auth::getInstance()->hasIdentity()
            ) {
                throw new Zend_Controller_Router_Exception('Sitemap page is disabled:' . $sitemapPageId, 404);
            }
        }
        $this->view->sitemapPage = $sitemapPage;
    }

}
