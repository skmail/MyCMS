<?php

class App_Pages_Admin_Functions
{

    public function __construct($application = array())
    {
        $this->application = $application;

    }

    public function updatePageUrl($page_id)
    {
        
    }

    public function _setDependecy()
    {

        $plugin['categoryTable'] = 'pages';
        $plugin['categoryKey'] = 'page_id';
        $plugin['categoryLabel'] = 'page_name';
        $plugin['dependOn'] = 'pages_lang';
        $plugin['dependOnPriKey'] = 'page_id';
        $plugin['dependOnSecKey'] = 'lang_id';
        $plugin['dependOnSecVal'] = $this->application['lang_id'];
        $plugin['app'] = 'Pages';

        return $plugin;

    }

}