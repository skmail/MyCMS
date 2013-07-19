<?php

class App_HomePage_Admin_HomePage extends Admin_Model_ApplicationAbstract
{

    public $renderWindow = true;

    public $plugin = array();

    protected $query = null;

    public function __construct($application = array())
    {

        parent::__construct($application);

    }

    public function index()
    {

        $listBlocks = array();

        MC_Models_Hooks::call('build_admin_homepage_blocks',$listBlocks);

        $this->assign('list_blocks',$listBlocks);

        $this->setNav('Home page');

        return $this->application;

    }

}