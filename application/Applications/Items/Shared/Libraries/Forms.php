<?php

class App_Items_Shared_Libraries_Forms
{

    public function __construct()
    {
        $this->MC =& MC_Core_Instance::getInstance();
    }

    public function itemForm($itemQuery = array())
    {
        if(intval($itemQuery['folder_id']) != 0)
        {
            $query = new $this->MC->Queries($this->application);
            $fields['fields'] = $query->listFields($itemQuery['folder_id'], array('multi_lang'          => 0));
            $fields['lang_fields'] = $query->listFields($itemQuery['folder_id'], array('multi_lang' => 1), false, 'item_lang');
        }

        $itemForm = new App_Items_Admin_Forms_Item(array('action' => $this->MC->application['url'] . 'window/saveItem', 'data'   => $itemQuery,'fields'=>$fields));

        $itemForm->populate($itemQuery);

        return $itemForm;
    }

    public function categoryForm($categoryQuery = NULL)
    {
        if(null == $categoryQuery)
        {
            $categoryQuery = array();
        }
        $categoryForm = new App_Items_Admin_Forms_Category(array(
                    'action' => $this->MC->application['url'] . 'window/saveCat',
                    'data'   => $categoryQuery
                ));

        $categoryForm->populate($categoryQuery);

        return $categoryForm;
    }



    public  function dataGraber($data = array(),$results = array())
    {
        $attributes['action'] = $this->application['url'] . 'window/doDataGraber';
        $attributes['results'] = $results;
        $form = new App_Items_Admin_Forms_DataGraber($attributes);
        $form->populate($data);
        return $form;
    }


    public function field($data = array())
    {

        $form = new App_Items_Admin_Forms_CustomField(array('action'=>$this->MC->application['url'] . 'window/saveField'));
        $form->populate($data);
        return $form;
    }

    public  function folder($data = array())
    {
        $attributes = array();
        $attributes['action'] = $this->MC->application['url'] . 'window/saveFolder';
        $form = new App_Items_Admin_Forms_Folder($attributes);
        $form->populate($data);
        return $form;
    }
}