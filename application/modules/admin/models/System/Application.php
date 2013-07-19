<?php

class Admin_Model_System_Application
{

    protected $defaultView = 'index';
    public function __construct()
    {
        $applicationVars = array();

        $this->MC =& MC_Core_Instance::getInstance();


        $applicationVars['appPrefix'] = $this->MC->Zend->getRequest()->getParam('appPrefix');
        $applicationVars['window'] = $this->MC->Zend->getRequest()->getParam('window');

        $this->application = $applicationVars;
    }

    public function start()
    {

        if (!empty($this->application['appPrefix']))
        {
            $applicationQuery = $this->getApp(array("where" => array('app_prefix' => $this->application['appPrefix'])));
        }
        else
        {
            $applicationQuery = $this->getApp(array("where" => array('app_default' => 1)));
        }

        if ($applicationQuery)
        {
            $options = array_merge($this->application, $applicationQuery);
            $options['window'] = (empty($options['window'])) ? $this->defaultView : $options['window'];
            $options['windowUri'] = $options['windowUri'] . '/window/' . $options['window'];
            $this->MC->load->model('language','lang');
            $options['lang_id'] = $this->MC->model->lang->currentLang('lang_id');
            $options['url'] = $this->appUrl($options['app_prefix']) . '/';
            $options['viewPath'] = APPLICATION_PATH . '/Applications' . $options['app_prefix'] . '/Admin/views/';
            $this->_callApp($options);
            $this->render();
        }else
        {

            throw new MC_Core_Exception(sprintf("Can't find %s application",$this->application['appPrefix']));
        }

    }

    protected function _callApp($options)
    {


        $application = MC_Core_Loader::appClass($options['app_prefix'], $options['app_prefix'], $options, 'Admin');
        if ($application)
        {
            if (method_exists($application, $options['window']))
            {

                $permissions = MC_Core_Loader::appClass('Users', 'Permissions', $options, 'Shared');
                if ($permissions->isAllow($options, $options['window']))
                {
                    $window = $application->$options['window']();

                    $this->options = $window;
                    return true;
                }
                else
                {
                    $this->errors[] = 'No permission';
                    return false;
                }
            }
        }

        $this->errors[] = 'Invalid Request';
        return false;
    }

    public function getApp($options = array())
    {
        
        $appQuery = $this->MC->db->select()->from('Applications');
        if (isset($options['where']))
        {
            if (count($options['where']) > 0)
            {
                foreach ($options['where'] as $field => $data)
                {
                    $appQuery->where($field . ' = ?', $data);
                }
            }
        }
        $appQuery->where('app_status = ?', 0);
        $appQuery->order(array('app_id  ASC'));

        if ($options['listAll'] === true)
        {
            $applicationsList = array();
            $applications = $this->MC->db->fetchAll($appQuery);

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
        $row = $this->MC->db->fetchRow($appQuery);
        if ($row)
        {
            return $row;
        }
        else
        {
            return false;
        }
    }

    public function appUrl($appPrefix, $window = '', $vars = array())
    {
        $varsArray = array();

        if ($window != "")
        {
            $window = '/window/' . $window;
        }

        if (count($vars) > 0)
        {
            foreach ($vars as $var_key => $var_val)
            {
                $varsArray[] = $var_key . '/' . $var_val;
            }

            $vars = '';
            $vars = implode('/', $varsArray);
            $window = $window . '/' . $vars;
        }

        return Zend_Controller_Front::getInstance()->getBaseUrl() . '/admin/' . $appPrefix . $window;
    }

    protected function render()
    {
        if ($this->MC->Zend->getRequest()->isXmlHttpRequest())
        {
            $this->ajaxRequest();
        }
        else
        {
            $this->httpRequest();
        }
    }

    protected function ajaxRequest()
    {
        if ($this->options['nav'])
        {
            $this->options['nav'] = $this->options['nav']->render();
        }

        $Viewer = $this->view();
        $Viewer->assign('app', $this->options);

        if (!empty($this->options['sidebar']))
        {
            $dataRender['sidebar'] = $Viewer->render($this->options['sidebar'] . '.phtml');
        }
        else
        {
            $dataRender['sidebar'] = false;
        }

        if (!$this->options['errors'] && $this->options['window'] != false)
        {
            $this->options['window'] = preg_replace('(.phtml$)', '', $this->options['window']);
            $dataRender['window'] = $Viewer->render($this->options['window'] . '.phtml');
        }
        else
        {
            $dataRender['window'] = false;
        }

        if ($this->options['errors'])
        {
            $dataRender['window'] = $this->error($this->options);
        }

        $this->setMessage();
        $dataRender['app'] = $this->options;
        echo json_encode($dataRender);
    }

    protected function httpRequest()
    {

        $Viewer = $this->view();
        $Viewer->assign('app', $this->options);

        if (!$this->options['errors'] && $this->options['window'] != "")
        {

            $this->options['body'] = $Viewer->render($this->options['window'] . '.phtml');

            if (isset($this->options['sidebar']))
            {
                $this->options['sidebar'] = $Viewer->render($this->options['sidebar'] . '.phtml');
            }
        }
        else
        {
            $this->options['body'] = $this->error($this->options);
        }

        $this->setMessage();
        $this->layout();

        echo $this->layoutModel->render();
    }

    protected function view($option = array())
    {
        if ($this->viewModel == NULL)
        {
            $this->viewModel = new Zend_View();
        }

        $view = $this->viewModel;
        $helperPath = APPLICATION_PATH . '/modules/admin/views/helpers';
        $view->addHelperPath($helperPath, 'Admin_View_Helper');
        $view->addHelperPath(APPLICATION_PATH . '/Applications/'. ucfirst($this->options['appPrefix']).'/Admin/views/helpers', 'App_'. ucfirst($this->options['appPrefix']).'_Admin_View_Helper');

        if (isset($option['script']))
        {
            $scriptPath = $option['script'];
        }
        else
        {
            $scriptPath = APPLICATION_PATH . '/Applications/' . ucfirst($this->options['app_prefix']) . '/Admin/views';
        }

        $view->addScriptPath($scriptPath);
        return $view;

    }

    protected function layout()
    {

        if ($this->layoutModel == NULL)
        {
            $this->layoutModel = $x = new Zend_Layout();
        }


        $path = APPLICATION_PATH . '/layouts/scripts/default';

        $this->viewModel = NULL;

        $view = $this->view(array('script' => $path));

        $view->assign('app', $this->options);

        $content = $view->render('window.phtml');

        $this->layoutModel->assign('app', $this->options);

        $this->layoutModel->assign('applications', $this->getSidebar());

        $this->layoutModel->setLayoutPath($path);

        $this->layoutModel->setLayout('admin');

        $this->layoutModel->content = $content;

    }

    protected function error()
    {

        $view = $this->view(array('script' => APPLICATION_PATH . '/modules/admin/views/scripts/ajax/default'));

        return $view->render('404.phtml');

    }

    protected function setMessage()
    {

        if (!is_array($this->options['message']['text']))
        {
            return;
        }

        if (count($this->options['message']['text']) == 0)
        {
            return;
        }

        $messageString = '<ul>';

        foreach ($this->options['message']['text'] as $message)
        {
            $messageString.="<li>" . $message . "</li>";
        }

        $messageString.='</ul>';

        $this->options['message']['text'] = $messageString;

    }
    public function getSidebar()
    {
        $sidebarItems = Admin_Model_System_Application::getApp(array('listAll'=>true));

        MC_Models_Hooks::call('build_sidebar_menu',$sidebarItems);

        return $sidebarItems;

    }

}