<?php

abstract class App_Hooks_Admin_InstallerAbstract {



   public function installHook($hook_callable,$method,array $settings = array())
   {

       $db = Zend_Registry::get('db');

       $dataArray = array();

       $dataArray['event'] = $hook_callable;
       $dataArray['method'] = $method;
       $dataArray['hook_name'] = $this->config->hookName;
       $dataArray['version'] = $this->config->version;
       $dataArray['settings'] = json_encode($settings);

       $db->insert('hooks',$dataArray);

   }

    protected  function install()
    {
        $db = Zend_Registry::get('db');

        $where = $db->quoteInto('hook_name = ? ', $this->config->hookName);

        $db->update('hooks',array('version'=>$this->config->version),$where);
    }

    protected function currentVersion()
    {

        $db = Zend_Registry::get('db');

        $hookQuery = $db->select()->from('hooks')->where('hook_name = ?',$this->config->hookName)->group('hook_name');

        $hookRows = $db->fetchRow($hookQuery);
        if($hookRows)
        {
            return $hookRows['version'];
        }
        else
        {
            return false;
        }
    }


}