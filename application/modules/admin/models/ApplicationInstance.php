<?php

class Admin_Model_ApplicationInstance
{

    protected $view = NULL;

    public $dbTable = array(
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

    public function __construct($application)
    {

        if ($application == NULL)
        {
            $application = array();
        }

        $this->db = Zend_Registry::get('db');

        $this->_Zend = Zend_Controller_Front::getInstance();

        $this->app_id = $application['app_id'];

        $this->application['renderWindow'] = $this->renderWindow;

        $this->application['lang_id'] = 1;

        $this->application['nav'] = new Admin_Model_System_Breadcrumb(array('url' => $application['url']));

        $this->application = array_merge($this->application, $application);

    }

    protected function view()
    {

        $view = $this->viewModel();
        
        $view->addHelperPath(APPLICATION_PATH . '/modules/admin/views/helpers', 'Admin_View_Helper');
        
        $view->addScriptPath(APPLICATION_PATH . '/Applications/' . ucfirst($this->application['app_prefix']) . '/admin/views');
        
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

    
    protected function setView($view)
    {
        $this->application['window'] = $window;
    }
}