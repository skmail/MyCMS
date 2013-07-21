<?php

abstract class App_Widgets_Shared_PagesPluginsAbstract
{

    protected $pluginsPages = array();

    protected function setPage($page_key,$page_value,$page_label = '')
    {
        $this->pluginsPages[$page_key][$page_value] = (!empty($page_label))?$page_label:$page_value;
    }

    public function getPages()
    {
        $this->buildPages();
        return $this->pluginsPages;
    }
}