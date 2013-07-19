<?php

class App_Hooks_Admin_Install extends App_AppsManager_Admin_Abstract_Install
{

    public function __construct()
    {
        $this->config = MC_Core_Loader::appClass('Hooks', 'Config', NULL, 'shared');

        parent::__construct();

    }

    public function install()
    {
        parent::install();

        $this->upgrade100();

    }

    public function upgrade()
    {
        if ($this->version >= $this->current_version())
        {
            return false;
        }

        parent::upgrade();

        if ($this->version > 0)
        {
            $this->upgrade100();
        }

    }

    public function upgrade100()
    {


    }

    public function uninstall()
    {

        parent::uninstall();

    }

}