<?php

class Admin_AjaxController extends MC_Admin_Controller_Action
{

    private $viewModel = NULL;

    public function init()
    {
        parent::init();

    }

    public function indexAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);




    }

    public function windowAction()
    {

        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();
        $initApplication = new Admin_Model_System_Application();
        $initApplication->start();
    }

    private function view($option = array())
    {

        $view = $this->viewModel();
        
        
        $helperPath = APPLICATION_PATH . '/modules/admin/views/helpers';

 
        if (isset($option['script']))
        {
            $scriptPath = $option['script'];
            
        }
        else
        {
            $scriptPath = APPLICATION_PATH . '/modules/admin/models/Applications/' . ucfirst($this->app['app_prefix']) . '/views';
        }
        
        $view->addScriptPath($scriptPath);
        
        return $view;

    }
    protected function viewModel()
    {
        if(!isset($this->viewModel))
        {
            $this->viewModel = new Zend_View();
        }
        
        return $this->viewModel;
    }
    public function sidebarAction($return = false)
    {

        $this->_helper->layout->disableLayout();
        
        $this->_helper->viewRenderer->setNoRender(true);

        if (!$this->_request->isXmlHttpRequest() && $return === false)
        {
            die();
        }
        
        $path = APPLICATION_PATH . '/layouts/scripts/default/';

                
        $view = $this->view(array('script'=>$path));



        $sidebarItems =  Admin_Model_System_Application::getSidebar();

        $view->assign('applications',$sidebarItems);
        
        echo $view->render('sidebar.phtml');

    }

}