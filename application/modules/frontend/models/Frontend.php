<?php

class Frontend_Model_Frontend
{

    private $appRow;

    private $appModel;

    private $_home = 'home';

    public function __construct()
    {
       
        $this->_Zend = Zend_Controller_Front::getInstance();
        
        $this->page = $this->_Zend->getRequest()->getParam('page');
        
        $this->db = Zend_Registry::get('db');

        $this->lang_id = App_Language_Shared_Lang::currentLang();

    }

    public function run()
    {

        $data['app'] = $this->_Zend->getRequest()->getParam('app');

        $this->loadApp($data);

        if(is_array($this->data)){
            $theme = new Frontend_Model_Templates_Theme();
            $theme->theme()->plugins = new Frontend_Model_Plugins_Plugin($this->data);
            $theme->loadLayout();
        }else{
            echo $this->data;
        }
    }

    protected function loadApp($appData)
    {

        $data = array();

            if ($this->getApp($appData['app']))
            {

                $appInit = $this->appModel->init($this->appRow);

                if ($appInit)
                {
                    $this->data = $this->appModel->data;
                    return $appInit;
                }
                else
                {
                    $error[] = 1;
                }
            } else
            {
                $error[] = 2;
            }

            if (sizeof($error > 0))
            {
                $dataLoad = array('app' => $this->_home);
                
                $this->loadApp($dataLoad);
            }


    }

    protected function appName($appName)
    {

        if (empty($appName))
        {
            return false;
        }

        $appName = ucfirst($appName);

        return $appName;

    }

    protected function getApp($appName)
    {

        $appName = $this->appName($appName);

        $appData = $this->appQuery($appName);

        if ($appData)
        {

            $appModel = 'Frontend_Model_Applications_' . $appName . '_' . $appName;

            $this->appModel = new $appModel();

            return true;
        }
        else
        {
            return false;
        }
    }

    protected function appQuery($appName)
    {

        $appQuery = $this->db->select()
                ->from('Applications')
                ->where('app_status = ? ', 0);

        if(!empty($appName))
        {
            $appQuery->where('app_prefix = ?', $appName);
        }
        else
        {
            $appQuery->where('app_default = ?', 1);
        }

        $appRows = $this->db->fetchRow($appQuery);

        if (sizeof($appRows) == 1)
        {
            $appRow = array_shift($appRows);
            
            $appRow['application_id'] = $appRow['app_id'];
         
            $this->appRow = $appRow;
            
            return true;
        }
        else
        {
            return false;
        }

    }

    private function loadModel()
    {
        echo $this->appModel;
    }

}