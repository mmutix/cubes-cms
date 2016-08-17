<?php

class Zend_View_Helper_PhotoGalleryLeadingPhotoUrl extends Zend_View_Helper_Abstract {

    public function photoGalleryLeadingPhotoUrl($photoGallery) {

        $photoGalleryLeadingPhotoFileName = $photoGallery['id'] . '.jpg';

        $photoGalleryLeadingPhotoFilePath = PUBLIC_PATH . '/uploads/photo-galleries/' . $photoGalleryLeadingPhotoFileName;
           
        if (is_file($photoGalleryLeadingPhotoFilePath)) {
            
            return $this->view->baseUrl('/uploads/photo-galleries/' . $photoGalleryLeadingPhotoFileName);
        
            
        } else {
            
            return '';
        
            
        }
    }

}
