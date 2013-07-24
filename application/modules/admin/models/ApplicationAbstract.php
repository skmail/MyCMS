<?php

class Admin_Model_ApplicationAbstract
{

    protected $view = NULL;

    protected $db;

    protected $_Zend;

    public $application = array();

    //controller , get request process
    // fetch view 
    //non fetch view


    public function __construct($application)
    {

        if ($application == NULL)
        {
            $application = array();
        }

        $this->MC =& MC_Core_Instance::getInstance();

        MC_Core_Instance::setAppPath($application['appPrefix']);

        $this->db = Zend_Registry::get('db');

        $this->_Zend = Zend_Controller_Front::getInstance();

        $this->app_id = $application['app_id'];
        $language = new MC_Translate_Language();

        $this->application['nav'] = new Admin_Model_System_Breadcrumb(array('url' => $application['url']));

        $window = $this->_Zend->getRequest()->getParam('window');

        $this->application['currentPage'] = (empty($window))?"index":$window;

        $this->application = array_merge($application,$this->application);
        $this->application['lang_id'] = $language->current('lang_id') ;


        $this->MC->application = $this->application;
    }

    protected function view()
    {
        $view = $this->viewModel();

        $view->addHelperPath(APPLICATION_PATH . '/modules/admin/views/helpers', 'Admin_View_Helper');

        $view->addScriptPath(APPLICATION_PATH . '/Applications/' . ucfirst($this->application['app_prefix']) . '/Admin/views');

        return $view;

    }

    private function viewModel()
    {
        if ($this->view == NULL)
        {
            $this->view = new Zend_View();
        }
        return $this->view;
    }

    public function _Dependency()
    {
        return array(
            //the main table that every item off application depend on
            'categoryTable'  => NULL,
            //The primary key of the table 'categoryTable'
            'categoryKey'    => NULL,
            //the label of main table that  will appear to user
            'categoryLabel'  => NULL,
            //if the main table depend on another tables (almoset the main table will used by join)
            'dependOn'       => NULL,
            //The primary key of the sendoray table that will constraint with the primary key of main table
            'dependOnPriKey' => NULL,
            //The second primary key  of secondary table that will use by where statment
            'dependOnSecKey' => NULL,
            //The label of secondary table  that will appear to user
            'dependOnSecVal' => NULL
        );

    }

    protected function setView($view)
    {
        //if false,  no views will returned
        $this->application['window'] = $view;

    }

    protected function assign($key, $value = '')
    {
        $this->application[$key] = $value;

    }

    protected function log($message = '', $level = 3)
    {
        $currentLogCounter = count($this->application['system_logs_level']);
        $this->application['system_logs_level'][$currentLogCounter] = $level;
        $this->application['system_logs_message'][$currentLogCounter] = $message;

    }

    protected function setSidebar($sidebarName)
    {
        $this->application['sidebar'] = $sidebarName;

    }

    protected function nav($title, $url = '', $options = array())
    {

        $this->application['nav']->append($title, $url, $options);

    }

    protected function translate($translation)
    {

        $translate = Zend_Registry::get('Zend_Translate')->translate($translation);

        return $translate;

    }

    public function merge($arrayToMerge)
    {
        $this->application = array_merge($this->application, $arrayToMerge);

    }

    public function setRefresh($refresh = TRUE)
    {
        $this->application['refresh'][$refresh] = $refresh;

    }

    public function setMessage($messageText, $messageType,$messagHeading = '')
    {
        $this->application['message']['text'] = $messageText;
        $this->application['message']['type'] = $messageType;
        $this->application['message']['heading'] = $messagHeading;

    }
    public function setError($messageText = '')
    {
        if($messageText == '')
        {
            $this->translate('an error ocurred');
        }

        $this->application['fatal_error'] = $messageText;
    }


    public function setNav($label,$url = '')
    {
        $this->application['nav']->append($label,$url);
    }

    public function replaceUrl($url)
    {
        $this->application['replaceUrl'] = $url;
    }
}