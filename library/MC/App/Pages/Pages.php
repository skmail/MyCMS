<?php

class MC_App_Pages_Pages {

    public function pageUrl($resource) {

        return Zend_Controller_Front::getInstance()->getBaseUrl() . '/index/index/app/items/' . $resource['cat_url'] .
                '/' . $resource['item_id'] . $resource['item_url'];
    }

    
    
    
    public function segmentSettings($resource){
        
        if(Zend_Controller_Front::getInstance()->getRequest()->getParam('page') == $resource['page_url'])
            $resource['active'] = true;
        
        $resource['url'] = 
                Zend_Controller_Front::getInstance()->getBaseUrl() . '/index/index/app/pages/' . $resource['page_url'];
        
        return $resource;
    }
    
    
    
}

