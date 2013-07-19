<?php

abstract class App_AppsManager_Admin_Abstract_Install
{

    public $config;
    
    public function __construct()
    {
        

    }
    public function install()
    {

        $db = Zend_Registry::get('db');
        
        $applicationConfigs = array();

        $applicationConfigs['app_prefix'] = $this->config->prefix;
        $applicationConfigs['app_name'] = $this->config->name;
        $applicationConfigs['internal'] = $this->config->internal;
        $applicationConfigs['configrable'] = $this->config->configrable;
        $applicationConfigs['plugins'] = $this->config->plugins;
        $applicationConfigs['version'] = $this->config->version;

        $db->insert('Applications', $applicationConfigs);

    }

    public function upgrade()
    {
        $db = Zend_Registry::get('db');
        
        $applicationConfigs = array();

        $applicationConfigs['app_prefix'] = $this->config->prefix;
        $applicationConfigs['app_name'] = $this->config->name;
        $applicationConfigs['internal'] = $this->config->internal;
        $applicationConfigs['configrable'] = $this->config->configrable;
        $applicationConfigs['plugins'] = $this->config->plugins;
        $applicationConfigs['version'] =  $this->config->version;

        
        $where = $db->quoteInto('app_prefix = ?', $this->config->prefix);
        
        $db->update('Applications', $applicationConfigs, $where);

    }

    public function uninstall()
    {
        $db = Zend_Registry::get('db');

        $where = $db->quoteInto('app_prefix = ?', $this->config->prefix);
        
        $db->delete('Applications', $where);
    }

    public function current_version()
    {

        $db = Zend_Registry::get('db');
        
        $applicationsQuery = $db->select()->from('Applications')->where('app_prefix = ?', $this->config->prefix);

        $application = $db->fetchRow($applicationsQuery);

        return $application['version'];

    }

}