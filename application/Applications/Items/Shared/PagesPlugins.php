<?php


class App_Items_Shared_PagesPlugins extends App_Plugins_Shared_PagesPluginsAbstract
{

    public function buildPages()
    {
        $itemsQueries = new App_Items_Admin_Queries();

        $langs = new App_Language_Shared_Lang();

        $categories = $itemsQueries->categoryQuery(array('lang_id'=>$langs->currentLang()));

        foreach($categories as $cat)
        {
            $this->setPage('category',$cat['cat_id'],$cat['cat_name']);
        }
    }


    public function categoryPlugin($cat_url = '')
    {
        $db = Zend_Registry::get('db');
        $catQuery = $db->select()->from('items_categories')
                       ->where('cat_url = ? ', $cat_url)
                       ->join('items_categories_lang', 'items_categories.cat_id = items_categories_lang.cat_id');

        $catRow = $db->fetchRow($catQuery);

        if($catRow)
        {
            return $catRow['cat_id'];
        }else
        {
            return false;
        }
    }
}