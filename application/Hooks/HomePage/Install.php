<?php
class Hooks_HomePage_install extends App_Hooks_Admin_InstallerAbstract
{

    public $config;

    public function __construct()
    {

        $this->config = new Hooks_HomePage_Config();

    }
    public function install()
    {

        if($this->config->version <= $this->currentVersion())
        {
            return false;
        }

        if($this->currentVersion() < '1.0.0')
        {
            $this->upgrade100();
        }



        parent::install();
    }

    private function  upgrade100()
    {
        $this->installHook('build_admin_homepage_blocks','admin_homepage_blocks');
    }


}