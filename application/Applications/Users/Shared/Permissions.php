<?php

class App_Users_Shared_Permissions
{

    protected $userid;

    protected $usergroup;

    public function __construct()
    {

        $this->usergroup = 1;

        $this->userid = 1;

        $this->db = Zend_Registry::get('db');

    }

    public function isAllow($app, $method, $do = '')
    {
        
        
        
        if(is_object($app))
        {
        
            $appObj = $app;
            
            $app = array();
            
            foreach($appObj as $appKey=>$appVal)
            {
                $app[$appKey] = $appVal;
            }
            
        }

        
        $appPermClass = 'Admin_Model_Applications_' . ucfirst($app['app_prefix']) . '_Permissions';

        
        $usergroupOptions['usergroup'] = $this->usergroup;
        
        $usergroupRow = $this->getPerms($usergroupOptions);
        
        if (!empty($usergroupRow['perms']))
        {
            if($usergroupRow['super_admin'] == 1)
            {
                return true;
            }
            $usergroup = Zend_Json_Decoder::decode($usergroupRow['perms']);
        }

        $method = strtolower($method);

        $userOptions['user_id'] = $this->userid;
        
        $userRow = $this->getPerms($userOptions);
        
        if (!empty($userRow['perms']))
        {
            if($userRow['super_admin'] == 1)
            {
                return true;
            }
            $user = Zend_Json_Decoder::decode($userRow['perms']);
        }


        $usergroupPerm = $usergroup[$app['app_id']]['methods'][$method];
       
        
        $userPerm = $user[$app['app_id']]['methods'][$method];

        if (!$this->_getPermAuth($usergroup[$app['app_id']]['methods']['__construct'], $user[$app['app_id']]['methods']['index'], 'view') || !$this->_getPermAuth($usergroup[$app['app_id']]['methods'][$method], $user[$app['app_id']]['methods'][$method], $do))
        {
            return false;
        }
        else
        {
            $allowed = $this->_getPermAuth($usergroupPerm, $userPerm);
        }

        if (class_exists($appPermClass))
        {
            $appPermClass = new $appPermClass();
           
            if ($appPermClass instanceof MC_Models_Permissions_Abstract)
            {
                $tablesPerms = $appPermClass->checkPerms($method);

                if ($tablesPerms != false && is_array($tablesPerms) && count($tablesPerms) > 0)
                {
                    foreach ($tablesPerms as $tableName => $tableVals)
                    {

                        if (empty($tableVals['key']))
                        {
                            continue;
                        }

                        $usergroupPerm = $usergroup[$app['app_id']]['tables'][$tableName][$tableVals['key']];
                        
                        $userPerm = $user[$app['app_id']]['tables'][$tableName][$tableVals['key']];
                        
                        if ($this->_getPermAuth($usergroupPerm, $userPerm))
                        {
                            $allowed = true;
                        }
                        else
                        {
                            return false;
                        }
                    }
                }
            }
        }

        return $allowed;

    }

    protected function _getPermAuth($usergroupPerm, $userPerm, $do = '')
    {


        $doByGet = Zend_Controller_Front::getInstance()->getRequest()->getParam('do');
        $doByPost = Zend_Controller_Front::getInstance()->getRequest()->getPost('do');

        if (empty($doByGet) && empty($do) && empty($doByPost))
        {
            $do = 'view';
        }
        else if (!empty($do))
        {
            $do = $do;
        }
        else if (!empty($doByGet))
        {
            $do = $doByGet;
        }
        else if (!empty($doByPost))
        {
            $do = $doByPost;
        }


        if ($userPerm[$do] != 2 && !empty($userPerm[$do]))
        {
            if ($userPerm[$do] == 1)
            {
                return true;
            }
        }else
        {
            if ($usergroupPerm[$do] == 1)
            {
                return true;
            }
        }

        return false;

    }

    protected function getPerms($options = array())
    {

        $query = $this->db->select()->from('permissions');

        if (isset($options['usergroup_id']))
        {
            $query->where('usergroup_id = ? ', $options['usergroup_id']);
        }

        if (isset($options['user_id']))
        {
            $query->where('user_id = ? ', $options['user_id']);
        }

        $row = $this->db->fetchRow($query);

        return $row;

    }

}

