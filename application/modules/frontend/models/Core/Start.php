<?php

class Frontend_Model_Core_Start {

    public function run()
    {
        $this->MC =& MC_Core_Instance::getInstance();
        $appPrefix = $this->MC->Zend->getRequest()->getParam('app');
        $this->loadApp($appPrefix);
        if(is_array($this->data)){
            $this->MC->hook->call('frontend_start',$this->data);
        }else{
            echo $this->data;
        }
    }

    protected function loadApp($appPrefix = '')
    {
        $appPrefix = ucfirst($appPrefix);

        $appQuery = $this->MC->db->select()
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

        $appRow = $this->MC->db->fetchRow($appQuery);

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