<?php
class App_Search_Admin_Search extends Admin_Model_ApplicationAbstract
{

    public $renderWindow = true;
    public $plugin = array();
    protected $query = null;

    public function __construct($application = array())
    {
        parent::__construct($application);
    }

    #-------------------------------
    #  Index page - list categories
    #-------------------------------
    public function index()
    {
        return $this->application;
    }
}