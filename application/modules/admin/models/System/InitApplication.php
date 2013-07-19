<?php

class Admin_Model_System_InitApplication
{

    protected $defaultView = 'index';

    public function __construct($options = array())
    {

        $this->db = Zend_Registry::get('db');

        $application = new Admin_Model_System_Application();

        $initApp = $application->getApp(array("where" => array('app_prefix' => $options['appPrefix'])));

        if ($initApp)
        {

            $options = array_merge($options, $initApp);
          
            $options['renderWindowBody'] = (empty($options['window'])) ? true : false;
            
            $options['window'] = (empty($options['window'])) ? $this->defaultView : $options['window'];
            
            $options['windowUri'] = $options['windowUri'] . '/window/' . $options['window'];
            
            
            $options['namespace'] = $options['app_id'];
            
            $options['url'] = Admin_Model_System_Application::appUrl($options['app_prefix']) . '/';
            
            $options['windowView'] = $options['window'] . '.phtml';
            
            $options['viewPath'] = 'application/modules/admin/models/applications/' . $options['app_prefix'] . '/views/';

            $this->options = $options;
            
            $this->app =MC_Core_Loader::appClass($initApp['app_prefix'],$initApp['app_prefix'],NULL,'Admin');
            
        }
        else
        {
            $this->options['errors'] = true;
        }

    }

    protected function initApplication()
    {
        if (class_exists($this->app))
        {
            return new $this->app($this->options);
        }
        else
        {
            $this->options['errors'] = true;
        }
        return;
    }

    public function start()
    {

        $initWindow = $this->options['window'];

        $applicationStart = $this->initApplication();

        if ($applicationStart)
        {
            if (method_exists($applicationStart, $initWindow))
            {
                
                $applicationWindow = $applicationStart->$initWindow();
         
                if ($applicationWindow)
                {

                    $permissions = new MC_App_Users_Permissions();

                    $perm = $permissions->isAllow($this->options, $initWindow);
                
                    if ($perm == true)
                    {
                        $this->options = $applicationWindow;
                    }
                    else
                    {
                        $this->options['errors'] = true;
                        $this->options['error_messages'] = array('Access Denied, No Permissions');
                    }
                    
                    return;
                }
            }
        }
        $this->options['errors'] = true;
    }
}