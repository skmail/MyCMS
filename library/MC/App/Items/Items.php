<?php

class MC_App_Items_Items
{

    public function itemUrl($resource)
    {
     
        return Zend_Controller_Front::getInstance()->getBaseUrl() . '/items/' . $resource['cat_url'] .
                '/' . $resource['item_id'] . $resource['item_url'];
        
    }

    public function catUrl($resource)
    {
        return Zend_Controller_Front::getInstance()->getBaseUrl() . '/items/' . $resource['cat_url'];

    }

    public function segmentSettings($resource)
    {

        if (Zend_Controller_Front::getInstance()->getRequest()->getParam('category') == $resource['cat_url'])
        {
        
            $resource['active'] = true;
        
            $resource['url'] =
                    Zend_Controller_Front::getInstance()->getBaseUrl() . '/items/' . $resource['cat_url'];
        }
        return $resource;

    }

}

