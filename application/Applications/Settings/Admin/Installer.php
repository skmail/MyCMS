<?php

class App_Settings_Admin_Install extends App_AppsManager_Admin_Abstract_Install
{

    public function __construct()
    {
        $this->config = MC_Core_Loader::appClass('Settings', 'Config', NULL, 'shared');

        parent::__construct();

    }

    public function install()
    {
        parent::install();

    }

    public function upgrade()
    {
        if ($this->version >= $this->current_version())
        {
            return false;
        }

        parent::upgrade();
    }

    public function uninstall()
    {
        parent::uninstall();

    }

}