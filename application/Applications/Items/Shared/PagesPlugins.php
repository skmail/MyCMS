<?php


class App_Items_Shared_PagesPlugins extends App_Widgets_Shared_PagesPluginsAbstract
{

    public function buildPages()
    {

        $MC =& MC_Core_Instance::getInstance();

        $itemsQueries = new App_Items_Shared_Libraries_Queries();

        $categories = array();

        $folders = $itemsQueries->getFolderByLangId($MC->model->lang->currentLang('lang_id'));

        foreach ($folders as $folder)
        {
            $categories = $itemsQueries->categoriesTreeBySequence(0,2,array('folder_id'=>$folder['folder_id']));
            foreach($categories as $cat)
            {
                //$categories[$cat['cat_id']] = $cat['cat_name'];
                $this->setPage('category',$cat['cat_id'],$cat['cat_name']);
            }
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