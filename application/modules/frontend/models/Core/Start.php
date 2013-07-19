<?php

class Frontend_Model_Core_Start {

    public function run()
    {
        $this->CC =& MC_Core_Instance::getInstance();

        $templateCategory = $this->CC->Zend->getRequest()->getParam('folder');
        $template = $this->CC->Zend->getRequest()->getParam('template');

        $this->CC->hook->call('session_start');

        if($templateCategory == "")
        {
            $template = $this->CC->Template->fetchTemplate('','',false);
        }
        else
        {
            if(empty($template))
            {
                $template = $this->CC->Template->fetchTemplate($templateCategory,'',false,true);
            }
            else
            {
                $template = $this->CC->Template->fetchTemplate($templateCategory,$template);
            }
        }

        if($template)
        {
            $this->CC->hook->call('pre_parse_main_template',$template);
            $this->CC->Template->parse($template);
            $this->CC->hook->call('post_parse_main_template',$template);
        }else
        {
            throw new MC_Core_Exception('Not Found Page');
        }













        /* Old system */

        die();
        $this->db = Zend_Registry::get('db');

        $appPrefix = $this->_Zend->getRequest()->getParam('app');

        $this->loadApp($appPrefix);

        if(is_array($this->data)){
            $theme = new Frontend_Model_Templates_Theme();
            $theme->theme()->plugins = new Frontend_Model_Plugins_Plugin($this->data);
            $theme->loadLayout();
        }else{
            echo $this->data;
        }
    }

    protected function loadApp($appPrefix = '')
    {
        $appPrefix = ucfirst($appPrefix);

        $appQuery = $this->db->select()
            ->from('Applications')
            ->where('app_status = ? ', 0);

        if(!empty($appPrefix))
        {
            $appQuery->where('app_prefix = ?', $appPrefix);
        }
        else
        {
            $appQuery->where('app_default = ?', 1);
        }

        $appRow = $this->db->fetchRow($appQuery);

        if ($appRow)
        {

            $appPrefix = ucfirst($appRow['app_prefix']);

            $appRow['application_id'] = $appRow['app_id'];

            $appRow['settings'] = Zend_Json::decode($appRow['settings']);

            $this->appRow = $appRow;

            $appModel = 'App_' . $appPrefix . '_Frontend_' . $appPrefix;

            $this->appModel = new $appModel();

            $appInit = $this->appModel->init($this->appRow);

            if(!is_array($this->appModel->data))
            {
                $this->appModel->data = array();
            }
            $this->data = array_merge($appRow,$this->appModel->data);

            return true;
        }
        else
        {

            return false;
        }
    }
}