<?php

class Admin_View_Helper_IsActivePage extends Zend_View_Helper_Abstract
{

    public function isActivePage($page_key, $param = 'window')
    {

        $request = Zend_Controller_Front::getInstance()->getRequest()->getParam($param);
        
        if(empty($request))
        {
            $request = 'index';
        }

        if ($request == $page_key)
        {
            return true;
        }
        else
        {
            return false;
        }

    }

}