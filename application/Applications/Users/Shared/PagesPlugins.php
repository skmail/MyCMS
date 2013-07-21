<?php


class App_Users_Shared_PagesPlugins extends App_Widgets_Shared_PagesPluginsAbstract
{

    public function buildPages()
    {
        $pagesQueries = new App_Users_Shared_Queries();

        $langs = new App_Language_Shared_Lang();

        $pages = $pagesQueries->page(array('lang_id'=>$langs->currentLang()));

        foreach($pages as $page)
        {
            $this->setPage('page',$page['user_page_id'],$page['user_page_name']);
        }
    }


    public function pagePlugin($pageUrl = '')
    {
        $db = Zend_Registry::get('db');

        $pageQuery = $db->select()->from('users_pages')
                       ->where('cat_url = ? ', $pageUrl)
                       ->join('users_pages_lang', 'users_pages.user_page_id = users_pages_lang.user_page_id');

        $pageRow = $db->fetchRow($pageQuery);

        if($pageRow)
        {
            return $pageRow['user_page_id'];
        }else
        {
            return false;
        }
    }
}