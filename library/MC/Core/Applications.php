<?php


class MC_Core_Applications
{
    public function getApp($options = array())
    {
        $db = Zend_Registry::get('db');
        $appQuery = $db->select()->from('Applications');

        if (isset($options['where']))
        {
            if (count($options['where']) > 0)
            {
                foreach ($options['where'] as $field => $data)
                {
                    $appQuery->where('app_prefix = ?', $data);
                }
            }
        }
        $appQuery->where('app_status = ?', 0);
        $appQuery->order(array('app_id  ASC'));

        if ($options['listAll'] === true)
        {
            $applicationsList = array();
            $applications = $db->fetchAll($appQuery);

            foreach ($applications as $app)
            {
                $permissions = MC_Core_Loader::appClass('Users', 'Permissions', NULL, 'shared'); //new Custom_App_Users_Permissions();

                if ($permissions->isAllow($this, 'index', 'view'))
                {
                    $url = Admin_Model_System_Application::appUrl($ap['app_prefix']);
                    $applicationsList[$app['app_id']] = $app;
                    $applicationsList[$app['app_id']]['url'] = $url;
                }
            }
            return $applicationsList;
        }
        $row = $db->fetchRow($appQuery);
        if ($row)
        {
            return $row;
        }
        else
        {
            return false;
        }
    }
}