<?php


class App_HomePage_Shared_PagesPlugins extends App_Plugins_Shared_PagesPluginsAbstract
{

    public function buildPages()
    {
        $this->setPage('homepage','home','Homepage');
    }
}