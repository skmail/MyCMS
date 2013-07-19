<?php

class App_Items_Shared_Libraries_Queries
{


    protected $_categoriesChildsTree = array();
    protected $_categoriesParentTree = array();

    public function __construct($application = array())
    {
        $this->MC =& MC_Core_Instance::getInstance();
    }

    //public function itemQuery($itemId = 0, $cat_id = 0, $onlyRow = false, $onlyLang = true, $where = array())
    public function itemQuery($item = array())
    {

        $itemQuery = $this->MC->db->select()->from('items');
        $itemQuery->join('items_lang', 'items_lang.item_id = items.item_id');
        $itemQuery->join('items_categories', 'items_categories.cat_id = items.cat_id');

        if (isset($item['item_id']))
        {
            $fields = $this->MC->db->select()->from('items_fields_data')->where('item_id = ?',$item['item_id']);
            $itemQuery->where('items_lang.item_id = ? ', $item['item_id']);
        }
        if (isset($item['cat_id']))
        {
            $itemQuery->where('items.cat_id = ? ', $item['cat_id']);
        }
        if (isset($item['lang_id']))
        {
            $itemQuery->where('items_lang.lang_id = ? ', $item['lang_id']);
        }
        if (isset($item['item_title']))
        {
            $itemQuery->where('items_lang.item_title LIKE  ? ', "%".$item['item_title']."%");
        }
        if (isset($item['item_status']))
        {
            $itemQuery->where('items.item_status = ? ', $item['item_status']);
        }

        if (isset($item['item_id']) && isset($item['lang_id']))
        {            
            $itemQuery = $this->MC->db->fetchRow($itemQuery);
        }
        else
        {
            $itemQuery = $this->MC->db->fetchALL($itemQuery);
        }

        if (isset($item['item_id']) && !isset($item['lang_id']))
        {
            foreach ($itemQuery as $item)
            {
                $item_lang[$item['lang_id']] = $item;

                $langFieldsResult= $this->MC->db->fetchRow($this->MC->db->select()->from('items_fields_data_lang')->where('item_id = ?',$item['item_id'])->where('lang_id = ? ', $item['lang_id']));

                if(!$langFieldsResult)
                {
                    $langFields = array();
                }
                else
                {
                    unset($langFieldsResult['item_id']);
                    unset($langFieldsResult['lang_id']);

                    foreach($langFieldsResult as $field_id=>$field_value)
                    {
                        $field_id = str_replace('field_','',$field_id);
                        $langFields[$field_id] = $field_value;
                    }
                }
                $item_lang[$item['lang_id']]['fields_lang'] = $langFields;
            }

            $itemQuery = $itemQuery[0];

            $itemQuery['item_lang'] = $item_lang;
            
            $itemFields = $this->MC->db->fetchRow($fields);
            unset($itemFields['item_id']);
            foreach($itemFields as $fieldId=>$fieldValue)
            {
                $fieldId = str_replace('field_','',$fieldId);
                $itemQuery['fields'][$fieldId] = $fieldValue;
            }
        }

        if ($itemQuery['images'] != "")
        {
            $itemQuery['images'] = explode(',', $itemQuery['images']);
        }

        return $itemQuery;

    }

    //public function categoryQuery($cat_id = 0, $onlyRow = false, $onlyLang = true)
    public function categoryQuery($cat = array())
    {

        $catQuery = $this->MC->db->select()->from('items_categories');

        $catQuery->join('items_categories_lang', 'items_categories.cat_id = items_categories_lang.cat_id');

        if (isset($cat['cat_id']))
        {
            $catQuery->where('items_categories_lang.cat_id = ?', $cat['cat_id']);
        }

        if (isset($cat['folder_id']))
        {
            $catQuery->where('items_categories.folder_id = ?', $cat['folder_id']);
        }
        if (isset($cat['parent_id']))
        {
            $catQuery->where('items_categories.parent_id = ?', $cat['parent_id']);
        }

        if (isset($cat['lang_id']))
        {
            $catQuery->where('lang_id = ?', $cat['lang_id']);
        }

        if (isset($cat['cats_id_in']))
        {
            $catQuery->where('items_categories_lang.cat_id in ( '.$cat['cats_id_in'].' )');

            $catQuery->order(array('FIELD(items_categories_lang.cat_id,'.$cat['cats_id_in'].' )'));


        }


        if (isset($cat['cat_id']) && isset($cat['lang_id']))
        {
            $catQuery = $this->MC->db->fetchRow($catQuery);
            $catQuery['params'] = Zend_Json::decode($catQuery['cat_params']);
            unset($catQuery['cat_params']);
        }else
        {
            $catQuery = $this->MC->db->fetchAll($catQuery);
            foreach($catQuery as $key=>$val)
            {
                if (count($catQuery[$key]['cat_params']) > 0)
                {
                    $catQuery[$key]['params'] = Zend_Json::decode($catQuery[$key]['cat_params']);
                }
                //unset($catQuery[$key]['cat_params']);
            }
        }

        if (!isset($cat['lang_id']))
        {
            foreach ($catQuery as $cat)
            {
                $catLang[$cat['lang_id']] = $cat;
            }
            $catQuery = $catQuery[0];
            if (count($catLang) > 0)
            {
                $catQuery['cat_lang'] = $catLang;
            }
        }
        return $catQuery;
    }

    public function deleteItems(array $items)
    {

        $errors = 0;
        $success = 0;

        foreach ($items as $itemId)
        {
            $itemRow = $this->itemQuery($itemId, 0, true);

            if (!$itemRow)
            {
                $errors++;
            }

            $where = $this->MC->db->quoteInto("item_id = ?", $itemId);

            $this->MC->db->delete('items', $where);

            $this->MC->db->delete('items_lang', $where);
        }

        $results = array();

        $results['success'] = $success;

        $results['errors'] = $errors;

        return $results;

    }

    public function buildCategoiesRelations()
    {

        $this->_buildCategoriesChildsTree();
        $this->_buildCategoriesParentTree();
        $relations = array();

        $relations['childs'] = $this->_categoriesChildsTree;

        $relations['parents'] = $this->_categoriesParentTree;

        return $relations;


    }

    public function _buildCategoriesChildsTree($options = array())
    {

        $query = $this->MC->db->select()->from('items_categories');

        if(isset($options['parent_id']) && isset($options['base_cat']))
        {
            $query->where('parent_id = ?',$options['parent_id']);
        }

        $rows = $this->MC->db->fetchAll($query);

        if(!$rows)
        {
            return;
        }

        foreach($rows as $row)
        {



            if(isset($options['base_cat']))
            {
                $base_cat = $options['base_cat'];
            }else{
                $base_cat = $row['cat_id'];
            }

            if(!isset($this->_categoriesChildsTree[$base_cat]))
            {
                $this->_categoriesChildsTree[$base_cat] = array();
            }

            $this->_categoriesChildsTree[$base_cat][] = $row['cat_id'];

            $this->_buildCategoriesChildsTree(array('parent_id'=>$row['cat_id'],'base_cat'=>$base_cat));

        }
    }

    public function _buildCategoriesParentTree($options = array())
    {
        $query = $this->MC->db->select()->from('items_categories');

        if(isset($options['cat_id']) && isset($options['base_cat']))
        {
            $query->where('cat_id = ?',$options['cat_id']);
        }

        $rows = $this->MC->db->fetchAll($query);

        if(!$rows)
        {
            return;
        }

        foreach($rows as $row)
        {
            if(isset($options['base_cat']))
            {
                $base_cat = $options['base_cat'];
            }else{
                $base_cat = $row['cat_id'];
            }
            if(!isset($this->_categoriesParentTree[$base_cat]))
            {
                $this->_categoriesParentTree[$base_cat] = array();
            }
            if($base_cat == $row['cat_id'] && $row['parent_id'] == 0)
            {
                $this->_categoriesParentTree[$base_cat][] = $row['parent_id'];
            }

            if($row['parent_id'] == 0)
            {
                continue;
            }
            $this->_categoriesParentTree[$base_cat][] = $row['parent_id'];

            $this->_buildCategoriesParentTree(array('cat_id'=>$row['parent_id'],'base_cat'=>$base_cat));

        }

    }

    public function listFields($folderId, $where = array(), $lang = true, $lang_key = 'MC_key_lang')
    {

        $query = $this->MC->db->select()->from('items_fields');

        $query->where('folder_id = ?', $folderId);

        $langs = MC_Core_Loader::appClass('Language', 'Lang', NULL, 'Shared');

        if ($lang)
        {
            $query->joinLeft('items_fields_lang', "items_fields.field_id = items_fields_lang.field_id
                            AND lang_id = " . $langs->currentLang(), array('field_label', 'lang_id'));
        }
        
        foreach ($where as $field => $value)
        {
            $query->where($field . " = ?", $value);
        }

        $rows = $this->MC->db->fetchAll($query);

        if ($lang == false)
        {
            $langRows = array();

            foreach ($rows as $row)
            {
                $langRows[$row['field_id']] = $this->getField($row['field_id'], $lang_key);
            }
            $rows = $langRows;
        }

        if (!$rows)
        {
            $rows = array();
        }

        return $rows;

    }

    public function getField($field_id, $lang_key = 'MC_key_lang')
    {

        $query = $this->MC->db->select()->from('items_fields');

        $query->where('field_id = ?', $field_id);

        $row = $this->MC->db->fetchRow($query);

        $langQuery = $this->MC->db->select()->from('items_fields_lang');

        $langQuery->where('field_id = ?', $field_id);

        $langRows = $this->MC->db->fetchAll($langQuery);

        foreach ($langRows as $lang_val)
        {
            $row[$lang_key][$lang_val['lang_id']] = $lang_val;
        }

        return $row;

    }

    public function create_field($field_id, $lang = true)
    {

        $field_name = 'field_' . $field_id;

        if ($lang)
        {
            $table_name = 'items_fields_lang';
        }
        else
        {
            $table_name = 'items_fields';
        }

        $config = Zend_Registry::get('config');

        $query = "Show columns from " . $table_name . " like '" . $field_name . "'";

        $fields = $this->MC->db->fetchAll($query);

        if (!is_array($fields) || count($fields) == 0)
        {
            $this->MC->db->query("ALTER TABLE " . $table_name . " ADD " . $field_name . " VARCHAR(255);");
        }

        return $field_name;

    }

    public function update_field($item_id, array $fields, $lang_id)
    {

        $fields['item_id'] = $item_id;

        if (is_numeric($lang_id) && $lang_id != 0)
        {
            $table_name = 'items_fields_lang';
            $where = $this->MC->db->quoteInto("lang_id = ? AND ", $lang_id);

            $fields['lang_id'] = $lang_id;
        }
        else
        {
            $table_name = 'items_fields';
        }

        $where.= $this->MC->db->quoteInto(" item_id = ?", $item_id);

        if ($this->MC->db->fetchRow($this->MC->db->select()->from($table_name)->where($where)))
        {
            $this->MC->db->update($table_name, $fields, $where);
        }
        else
        {
            $this->MC->db->insert($table_name, $fields);
        }
        return true;
    }



    public function folderQuery($folderParams = array())
    {
        $query = $this->MC->db->select()->from('items_folders');
        $query->join('items_folders_lang', 'items_folders.folder_id = items_folders_lang.folder_id');

        if(isset($folderParams['folder_id']) && isset($folderParams['lang_id']))
        {
            $query->where('items_folders_lang.folder_id = ?',$folderParams['folder_id']);
            $query->where('lang_id = ?',$folderParams['lang_id']);

            $result = $this->MC->db->fetchRow($query);

            return $result;

        }else if(!isset($folderParams['folder_id']) && isset($folderParams['lang_id']))
        {
            $query->where('lang_id = ?',$folderParams['lang_id']);

            $result = $this->MC->db->fetchAll($query);

            return $result;

        }
        else if(isset($folderParams['folder_id']) && !isset($folderParams['lang_id']))
        {
            $query->where('items_folders_lang.folder_id = ?',$folderParams['folder_id']);

            $initialResult = $this->MC->db->fetchAll($query);

            if(!$initialResult)
            {
                return false;
            }
            $results = array();

            $results['folder'] = reset($initialResult);

            foreach($initialResult as $result)
            {
                $results['folder']['folder_lang'][$result['lang_id']] = $result;
            }

            return $results;
        }
    }



    public function categoriesTree($parent_id)
    {
        $query = $this->categoryQuery(array('parent_id'=>$parent_id,'lang_id'=>$this->MC->model->lang->currentLang('lang_id')));

        if(!$query)
        {
            return false;
        }

        $results = array();

        foreach($query as $row){

            $childs = $this->categoriesTree($row['cat_id']);

            if($childs)
            {
                $row['childs']  = $childs;
            }

            $results[$row['cat_id']]  =  $row;
        }
        return $results;
    }
}

