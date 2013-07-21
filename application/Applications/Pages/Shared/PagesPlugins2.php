<?php


class App_Pages_Shared_PagesPlugins extends App_Widgets_Shared_PagesPluginsAbstract
{

    public function buildPages()
    {
        $page = new App_Pages_Admin_Pages();
        $langs = new App_Language_Shared_Lang();

        $pages = $page->pageQuery(array('lang_id'=>$langs->currentLang()));

        foreach($pages as $page)
        {
            $this->setPage('page',$page['page_id'],$page['page_name']);
        }
    }


    public function pagePlugin($page_url = '')
    {

        $page = new App_Pages_Shared_Queries();
        $langs = new App_Language_Shared_Lang();


         $pageRow = $page->pageQuery(array('page_url'=>$page_url,'lang_id'=>$langs->currentLang()));

        if($pageRow)
        {
            return $pageRow['page_id'];
        }else
        {
            return false;
        }
    }
}