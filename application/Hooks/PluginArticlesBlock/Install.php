<?php
class Hooks_PluginArticlesBlock_install extends App_Hooks_Admin_InstallerAbstract
{

    public $config;

    public function __construct()
    {
        $this->config = new Hooks_PluginArticlesBlock_Config();
    }

    public function install()
    {

        if($this->config->version < $this->currentVersion())
        {
            return false;
        }

        if($this->currentVersion() < '1.0.0')
        {
            $this->upgrade100();
        }



        if($this->currentVersion() < "1.0.1")
        {
            $this->upgrade101();
        }
        parent::install();
    }

    private function  upgrade100()
    {
        $this->installHook('build_PluginArticlesBlock_Form','setOptions');
    }

    private function  upgrade101()
    {
        $this->installHook('create_group_params','create_group_params');
    }
}