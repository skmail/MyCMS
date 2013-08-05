<?php

class App_Items_Admin_Permissions extends MC_Models_Permissions_Abstract
{



    public function setEntities()
    {
        //$queries = new App_Items_Shared_Libraries_Queries();
        //$this->addEntity('items_category', 'cat_id', 'cat_name', $queries->categoryQuery());
    }

    public function checkPerms($method)
    {

        $perms = array();

        $request = Zend_Controller_Front::getInstance()->getRequest();

        $perms['items']['items_category'] = array('key' => $request->getParam('catid'));

        $perms['item']['items_category'] = array('key' => $request->getParam('catid'));

        $itemQuery = new App_Items_Admin_Queries();

        $itemQuery = $itemQuery->itemQuery(intval($request->getParam('catid')), 0, true, true);

        $perms['item']['items_category'] = array('key' => $itemQuery['cat_id']);

        $perms['category']['items_category'] = array('key' => $request->getParam('catid'));


        if (isset($perms[$method]))
        {
            return $perms[$method];
        }
        else
        {
            return false;
        }

    }

    public function perm()
    {
        
    }

}