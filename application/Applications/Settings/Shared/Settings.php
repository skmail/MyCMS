<?php

class App_Settings_Shared_Settings
{

    public function __construct($application = array())
    {

        $this->application = $application;

    }

    public function get($app_prefix = '', $config)
    {

        if ($app_prefix == '')
        {
            return false;
        }

        $db = Zend_Registry::get('db');

        $applicationsQuery = $db->select()->from('Applications');

        $applicationsQuery->where('configrable = ?', 0);

        $applicationsQuery->where('app_prefix = ?', $app_prefix);


        $row = $db->fetchRow($applicationsQuery);

        $row['settings'] = json_decode($row['settings'], true);

        if (isset($row['settings'][$config]))
        {
            return $row['settings'][$config];
        }
        else
        {
            return false;
        }

    }

}