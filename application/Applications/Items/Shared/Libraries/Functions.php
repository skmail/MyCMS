<?php

class App_Items_Shared_Libraries_Functions
{

    public function __construct($application = array())
    {
        $this->application = $application;
        $this->MC =& MC_Core_Instance::getInstance();
    }

    public function _setDependecy()
    {
        $this->plugin['categoryTable'] = 'items_categories';
        
        $this->plugin['categoryKey'] = 'cat_id';
        
        $this->plugin['categoryLabel'] = 'cat_name';
        
        $this->plugin['dependOn'] = 'items_categories_lang';
        
        $this->plugin['dependOnPriKey'] = 'cat_id';
        
        $this->plugin['dependOnSecKey'] = 'lang_id';
        
        $this->plugin['dependOnSecVal'] = $this->application['lang_id'];
        
        $this->plugin['app'] = 'Items';

        return $this->plugin;

    }

    public function _sideMenu()
    {
        return array(
            array(
                'title' => 'Add new category',
                'url'   => 'window/category/do/add'
            )
        );
    }

    public static function catStatus()
    {

        $statusArray = array(
            1 => 'item_status_1', // Active
            2 => 'item_status_2', // Inactive
        );

        return $statusArray;
    }

    public static function itemStatus()
    {

        $statusArray = array(
            1 => 'item_status_1',//Published
            2 => 'item_status_2',   //Drafts
            3 => 'item_status_3',    //Trash
            4 => 'item_status_4', // Pending
        );

        return $statusArray;

    }

    public function catUrl($cat_id = 0, $do = 'edit')
    {

        $application_id = $this->MC->application['app_id'];

        $categoryEditUrl = $this->MC->application['url'] . 'window/category/do/%s';

        if ($do == 'edit')
        {
            $categoryEditUrl.='/cat_id/%s';
            $categoryEditUrl = sprintf($categoryEditUrl, $do, $cat_id);
        }
        else
        {
            $categoryEditUrl = sprintf($categoryEditUrl, $do);
        }
        
        return $categoryEditUrl;

    }

    public function itemUrl($itemId = 0, $do = 'edit')
    {

        $itemUrl = $this->MC->application['url'] . 'window/item/do/' . $do . '/itemid/' . $itemId;

        return $itemUrl;

    }

    public function folderUrl($folderId = 0, $do = 'edit')
    {

        $folderUrl = $this->MC->application['url'] . 'window/folder/do/' . $do . '/folderId/' . $folderId;

        return $folderUrl;

    }

    public function parentsCatTree($cat_id)
    {

        $isParent = true;

        $parents = array();

        $firstcat_id = $cat_id;

        while ($isParent)
        {

            $query = $this->_query->categoryQuery($cat_id, true);

            $row = $this->db->fetchRow($query);

            if (!$row || $row['parent_id'] == 0)
            {
                $isParent = false;
            }
            else
            {
                $parents[$row['parent_id']] = $row['parent_id'];
                $cat_id = $row['parent_id'];
            }
        }

        $parents = array_reverse($parents);

        if (count($parents) > 0)
        {
            $parents = implode(',', $parents);
        }
        else
        {
            $parents = 0;
        }
        
        $parentsToSave['parents'] = $parents;
        
        $where = $this->db->quoteInto("cat_id = ?", $firstcat_id);
        
        $this->db->update('items_categories', $parentsToSave, $where);

    }

    public function childsCatTree($cat_id)
    {

        $cats = $this->db->fetchAll($this->_query->categoryQuery());

        foreach ($cats as $c)
        {
            
        }

    }

    public function setParentsCats()
    {

        $cats = $this->db->fetchAll($this->_query->categoryQuery());

        foreach ($cats as $cat)
        {
            echo $this->parentsCatTree($cat['cat_id']);
        }

    }

    public function addFieldToform($fields,$data,&$fieldsForm)
    {

        foreach ($fields as $field)
            {

                $attr = array();

                if (empty($field['item_lang'][$langV['lang_id']]['field_label']))
                {
                    $attr['label'] = $field['field_name'];
                }
                else
                {
                    $attr['label'] = $field['item_lang'][$langV['lang_id']]['field_label'];
                }
                if($field['field_type'] == 'select'){
                    $attr['multiOptions'] = explode(',',$field['field_vars']);
                    foreach($attr['multiOptions'] as $key=>$val){
                        $attr['multiOptions'][$val] = $val;
                        unset($attr['multiOptions'][$key]);
                    }
                }

                $fieldTypeClass =  'App_Items_Shared_FieldsTypes_'.$field['field_type'].'_Field';

                $name = new $fieldTypeClass($field['field_id'],$attr);

                if($name->helper != "" || $name->helper != NULL)
                {
                    $name->getView()->addHelperPath(rtrim(APPLICATION_PATH,'/').'/Applications/Items/Shared/FieldsTypes/'.$field['field_type'].'/View', 'App_Items_Shared_FieldsTypes_'.$field['field_type'].'_View');
                }

                $name->setAttrib('data',$data);

                $name->setDecorators(MC_Admin_Form_Form::$elementDecorators);


                $fieldsForm->addElement($name);

                //$fieldsForm->addElement($field['field_type'], $field['field_id'], $attr);
            }
    }

   public function isChildCategory($parent,$child)
   {

   }
}