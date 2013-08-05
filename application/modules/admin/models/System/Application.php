<?php
class Admin_Model_System_Application
{
    protected $defaultView = 'index';

    public function __construct()
    {
        $applicationVars = array();
        $this->MC =& MC_Core_Instance::getInstance();
        $this->MC->hook->call('session_start',$this);
        $applicationVars['appPrefix'] = $this->MC->Zend->getRequest()->getParam('appPrefix');
        $applicationVars['window'] = $this->MC->Zend->getRequest()->getParam('window');
        $this->application = $applicationVars;
    }

    public function start()
    {
        if (!empty($this->application['appPrefix'])){
            $applicationQuery = $this->getApp(array("where" => array('app_prefix' => $this->application['appPrefix'])));
        }else{
            $applicationQuery = $this->getApp(array("where" => array('app_default' => 1)));
        }
        if ($applicationQuery){
            $applicationData = array_merge($this->application, $applicationQuery);
            $applicationData['window'] = (empty($applicationData['window'])) ? $this->defaultView : $applicationData['window'];
            $applicationData['windowUri'] = $applicationData['windowUri'] . '/window/' . $applicationData['window'];
            $this->MC->load->model('language','lang');
            $applicationData['lang_id'] = $this->MC->model->lang->currentLang('lang_id');
            $applicationData['url'] = $this->appUrl($applicationData['app_prefix']) . '/';
            $applicationData['viewPath'] = APPLICATION_PATH . '/Applications' . $applicationData['app_prefix'] . '/Admin/views/';
            $applicationData = $this->_callApp($applicationData);
            $this->render($applicationData);
        }else{
            throw new MC_Core_Exception(sprintf("Can't find %s application",$this->application['appPrefix']));
        }
    }

    protected function _callApp($applicationData)
    {
        $application = MC_Core_Loader::appClass($applicationData['app_prefix'], $applicationData['app_prefix'], $applicationData, 'Admin');
        if ($application){
            if (method_exists($application, $applicationData['window'])){
                $permissions = MC_Core_Loader::appClass('Users', 'Permissions', $applicationData, 'Shared');
                if ($permissions->isAllow($applicationData['app_prefix'], $applicationData['window'])){
                    $window = $application->$applicationData['window']();
                    if(is_object($window['nav'])){
                        $window['nav'] = $window['nav']->render();
                    }
                    return $window;
                }else{
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
        if (isset($options['where'])){
            if (count($options['where']) > 0){
                foreach ($options['where'] as $field => $data)
                {
                    $appQuery->where($field . ' = ?', $data);
                }
            }
        }
        $appQuery->where('app_status = ?', 0);
        $appQuery->order(array('app_id  ASC'));

        if ($options['listAll'] === true){
            $applicationsList = array();
            $applications = $this->MC->db->fetchAll($appQuery);

            foreach ($applications as $app)
            {
                $permissions = MC_Core_Loader::appClass('Users', 'Permissions', NULL, 'shared'); //new Custom_App_Users_Permissions();

                if ($permissions->isAllow($app['app_prefix'], 'index', 'view'))
                {
                    $url = Admin_Model_System_Application::appUrl($app['app_prefix']);
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

    protected function render($applicationData)
    {

        $this->applicationData =& $applicationData;
        if(false != $applicationData && !isset($applicationData['error']) && !isset($this->errors)){
            $Viewer = $this->view(array('script'=>APPLICATION_PATH . '/Applications/' . ucfirst($applicationData['app_prefix']) . '/Admin/views'));
            $Viewer->assign('app',$applicationData);
            if (!empty($applicationData['sidebar'])){
                $applicationData['sidebar'] = $Viewer->render($applicationData['sidebar'] . '.phtml');
            }else{
                $applicationData['sidebar'] = false;
            }
            if (count($applicationData['errors']) == 0  && $this->applicationData['window'] !== false){
                $applicationData['window'] = preg_replace('(.phtml$)', '', $applicationData['window']);
                $applicationData['body'] = $Viewer->render($applicationData['window'] . '.phtml');
            }else{
                $applicationData['window'] = false;
            }
            $Viewer->assign('app',$applicationData);
        }else{
            $applicationData['body'] = $this->error();
        }
        if ($this->MC->Zend->getRequest()->isXmlHttpRequest()){
            $this->ajaxRequest($applicationData);
        }else{
            $this->httpRequest($applicationData);
        }
    }

    protected function ajaxRequest($applicationData)
    {
        echo json_encode($applicationData);
    }

    protected function httpRequest($applicationData)
    {
        $Viewer = $this->view();
        $Viewer->assign('app',$applicationData);
        $this->layout();
        echo $this->layoutModel->render();
    }

    protected function view($option = array())
    {
        if ($this->viewModel == NULL){
            $this->viewModel = new Zend_View();
        }
        $view = $this->viewModel;
        $helperPath = APPLICATION_PATH . '/modules/admin/views/helpers';
        $view->addHelperPath($helperPath, 'Admin_View_Helper');
        $view->addHelperPath(APPLICATION_PATH . '/Applications/'. ucfirst($this->applicationData['appPrefix']).'/Admin/views/helpers', 'App_'. ucfirst($this->applicationData['appPrefix']).'_Admin_View_Helper');
        if(isset($option['script'])){
            $view->addScriptPath($option['script']);
        }
        return $view;
    }
    protected function layout()
    {
        if ($this->layoutModel == NULL)
        {
            $this->layoutModel = $x = new Zend_Layout();
        }
        if(isset($this->applicationData['layout'])){
            $path = APPLICATION_PATH . '/Applications/'. ucfirst($this->applicationData['appPrefix']).'/Admin/layouts';
            $this->layoutModel->setLayout($this->applicationData['layout']);
        }else{
            $path = APPLICATION_PATH . '/layouts/scripts/default';
            $this->viewModel = NULL;
            $view = $this->view(array('script' => $path));
            $view->assign('app', $this->applicationData);
            $content = $view->render('window.phtml');
            $this->layoutModel->assign('userMenu', $this->getUserMenu());
            $this->layoutModel->assign('applications', $this->getSidebar());
            $this->layoutModel->setLayout('admin');
        }
        $this->layoutModel->assign('app', $this->applicationData);

        $this->layoutModel->setLayoutPath($path);

        $this->layoutModel->content = $content;
    }

    protected function error()
    {
        $view = $this->view(array('script' => APPLICATION_PATH . '/modules/admin/views/scripts/ajax/default'));
        return $view->render('404.phtml');
    }

    protected function setMessage()
    {

        if (!is_array($this->applicationData['message']['text']))
        {
            return;
        }

        if (count($this->applicationData['message']['text']) == 0)
        {
            return;
        }

        $messageString = '<ul>';

        foreach ($this->applicationData['message']['text'] as $message)
        {
            $messageString.="<li>" . $message . "</li>";
        }

        $messageString.='</ul>';

        $this->applicationData['message']['text'] = $messageString;

    }
    public function getSidebar()
    {
        $sidebarItems = Admin_Model_System_Application::getApp(array('listAll'=>true));
        MC_Models_Hooks::call('build_sidebar_menu',$sidebarItems);
        return $sidebarItems;
    }

    public function getUserMenu()
    {
        $array = array();
        $array[] = array('label'=>'visit_site');
        $this->MC->hook->call('admin_user_menu',$array);
        foreach($array as $k=>$v)
        {
            if(isset($v['attribs']))
            {
                $attribs = array();
                foreach($v['attribs'] as $attribName=>$attribVal)
                {
                    $attribs[] = $attribName."='".$attribVal."'";
                }

                $array[$k]['attribs'] = implode(' ',$attribs);
            }
        }
        return $array;
    }
}