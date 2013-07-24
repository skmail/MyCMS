<?php

class App_Items_Shared_Libraries_Queries
{
    protected $_categoriesChildsTree = array();

    protected $_categoriesParentTree = array();

    public function __construct($application = array())
    {
        $this->MC =& MC_Core_Instance::getInstance();
    }

    /**
     * @param $catId
     * @param $langId
     */
    public function deleteCatByCatIdAndLangId($catId,$langId)
    {
        $where = $this->MC->db->quoteInto("cat_id = ?", $catId);
        $cat = $this->MC->db->fetchRow($this->MC->db->select()->from('items_categories')->where($where));
        $childs = explode(',',$cat['childs']);
        foreach($childs as $child)
        {
            $where = $this->MC->db->quoteInto("cat_id = ? ", $child);
            $whereLang = $where . $this->MC->db->quoteInto(" AND lang_id = ? ", $langId);
            $catData = $this->MC->Queries->getCatByCatId($child);
            $this->deleteItemsByCatIdAndLangId($child,$langId);
            if($catData && count($catData) == 1 && isset($catData[$langId])){
                $this->MC->db->delete('items_categories', $where);
            }
            $this->MC->db->delete('items_categories_lang', $whereLang);
        }
        $where = $this->MC->db->quoteInto("cat_id = ?", $catId);
        $whereLang = $where . $this->MC->db->quoteInto(" AND lang_id = ? ", $langId);
        $catData = $this->MC->Queries->getCatByCatId($catId);
        $this->deleteItemsByCatIdAndLangId($catId,$langId);
        if($catData && count($catData) == 1 && isset($catData[$langId])){
            $this->MC->db->delete('items_categories', $where);
        }
        $this->MC->db->delete('items_categories_lang', $whereLang);
    }

    /**
     * @param $catId
     * @param $langId
     */
    public function deleteItemsByCatIdAndLangId($catId,$langId)
    {
        $items = $this->getItemByCatIdAndLangId($catId,$langId);
        foreach($items as $item)
        {
            $itemByItemId = $this->getItemByItemId($item['item_id']);
            $this->_deleteItemByLangId($itemByItemId,$langId);
        }
    }

    /**
     * @param $item
     * @param $langId
     */
    protected function _deleteItemByLangId($item,$langId)
    {
        $where = $this->MC->db->quoteInto(' item_id = ? ',$item['item_id']);
        $whereLang = $where . $this->MC->db->quoteInto(' AND lang_id = ? ',$langId);
        if(isset($item['item_lang']) && $item['item_lang'][$langId] ){
            if(count($item['item_lang']) > 1){
                $this->MC->db->delete('items_lang', $whereLang);
                $this->deleteItemFieldsDataLang($item['item_id'],$langId);
            }else{
                $this->deleteItemFieldsData($item['item_id']);
                $this->MC->db->delete('items', $where);
                $this->MC->db->delete('items_lang', $where);
            }
        }
    }
    /**
     * @param $itemId
     * @param $langId
     */
    public function deleteItemByItemIdAndLangId($itemId,$langId)
    {
        $item = $this->getItemByItemId($itemId);
        $this->_deleteItemByLangId($item,$langId);
    }

    /**
     * @param $itemId
     * @param $langId
     */
    public function deleteItemFieldsDataLang($itemId,$langId)
    {
        $where  = $this->MC->db->quoteInto(' item_id = ? ',$itemId);
        $where .= $this->MC->db->quoteInto(' AND lang_id = ? ',$langId);
        $this->MC->db->delete('items_fields_data_lang',$where);
    }

    public function deleteItemFieldsData($itemId)
    {
        $where = $this->MC->db->quoteInto(' item_id = ? ',$itemId);
        $this->MC->db->delete('items_fields_data',$where);
    }

    /**
     * @param $folderId
     * @param $langId
     */
    public function deleteFolderByLangId($folderId,$langId)
    {
        $this->_deleteCatsByFolderIdRecursiveByLang($this->MC->Queries->categoriesTree(0,$langId,array('folder_id'=>$folderId)),$langId);

        $folder = $this->getFolderByFolderId($folderId);
        $where = $this->MC->db->quoteInto('folder_id = ?',$folderId);
        if(isset($folder['folder']['folder_lang']) && isset($folder['folder']['folder_lang'][$langId])){
            if(count($folder['folder']['folder_lang']) > 1){
                $this->_deleteFolderItemFieldLang($folderId,$langId);
                $where.= $this->MC->db->quoteInto(' AND lang_id = ?',$langId);
                $this->MC->db->delete('items_folders_lang',$where);
            }else{
                $this->_deleteFolderItemField($folderId);
                $this->MC->db->delete('items_folders_lang',$where);
                $this->MC->db->delete('items_folders',$where);
            }
        }
    }

    /**
     * @param $folderId
     * @param $langId
     */
    protected function _deleteFolderItemFieldLang($folderId,$langId)
    {
        $fields = $this->listFields($folderId);
        foreach($fields as $field)
        {
            if(1 == $field['multi_lang']){
                $this->_deleteFieldByFieldIdAndFieldLang($field['field_id'],$langId);
            }
        }
    }

    /**
     * @param $fieldId
     * @param $langId
     */
    protected  function _deleteFieldByFieldIdAndFieldLang($fieldId,$langId){
        $where  = $this->MC->db->quoteInto(' field_id = ? ',$fieldId);
        $where .= $this->MC->db->quoteInto(' AND lang_id = ? ',$langId);
        $this->MC->db->delete('items_fields_lang',$where);
    }

    /**
     * @param $folderId
     */
    protected function _deleteFolderItemField($folderId)
    {
        $this->_dropFolderItemField($folderId);
    }

    /**
     * @param $fieldId
     * @param $langId
     */
    public function deleteFieldByFieldIdAndLangId($fieldId,$langId)
    {
        $field = $this->getField($fieldId);

        if(isset($field['MC_key_lang']) && count($field['MC_key_lang']) > 0){
            if(count($field['MC_key_lang']) > 1){
                $this->_deleteFieldByFieldIdAndFieldLang($fieldId,$langId);
            }else{
                $this->_deleteField($field);
            }
        }else{
            $this->_deleteField($field);
        }
    }

    /**
     * @param $folderId
     */
    protected function _dropFolderItemField($folderId)
    {
        $fields = $this->listFields($folderId);
        foreach($fields as $field)
        {
            $this->_deleteField($field);
        }
    }

    /**
     * @param $field
     */
    protected function _deleteField($field)
    {
        $where  = $this->MC->db->quoteInto(' field_id = ? ',$field['field_id']);
        if(1 == $field['multi_lang']){
            $this->MC->db->getConnection()->exec("ALTER table items_fields_data_lang DROP COLUMN field_".$field['field_id']);
        }
        $this->MC->db->delete('items_fields_lang',$where);
        $this->MC->db->delete('items_fields',$where);
        $this->MC->db->getConnection()->exec("ALTER table items_fields_data DROP COLUMN field_".$field['field_id']);

    }
    /**
     * @param $cats
     * @param $langId
     */
    protected function _deleteCatsByFolderIdRecursiveByLang($cats,$langId)
    {
        if(!is_array($cats)){
            return;
        }
        foreach($cats as $cat)
        {
            if(isset($cat['childs']) && count($cat['childs']) > 0){
                $this->_deleteCatsByFolderIdRecursiveByLang($cat['childs'],$langId);
            }
            $this->deleteCatByCatIdAndLangId($cat['cat_id'],$langId);
        }
    }

    /**
     * @return array
     */
    public function buildCategoiesRelations()
    {
        $this->_buildCategoriesChildsTree();
        $this->_buildCategoriesParentTree();
        $relations = array();
        $relations['childs'] = $this->_categoriesChildsTree;
        $relations['parents'] = $this->_categoriesParentTree;
        return $relations;
    }

    /**
     * @param array $options
     */
    public function _buildCategoriesChildsTree($options = array())
    {
        $query = $this->MC->db->select()->from('items_categories');
        if(isset($options['parent_id']) && isset($options['base_cat'])){
            $query->where('parent_id = ?',$options['parent_id']);
        }
        $rows = $this->MC->db->fetchAll($query);
        if(!$rows){
            return;
        }
        foreach($rows as $row)
        {
            if(isset($options['base_cat'])){
                $base_cat = $options['base_cat'];
            }else{
                $base_cat = $row['cat_id'];
            }
            if(!isset($this->_categoriesChildsTree[$base_cat])){
                $this->_categoriesChildsTree[$base_cat] = array();
            }
            $this->_categoriesChildsTree[$base_cat][] = $row['cat_id'];
            $this->_buildCategoriesChildsTree(array('parent_id'=>$row['cat_id'],'base_cat'=>$base_cat));
        }
    }

    /**
     * @param array $options
     */
    public function _buildCategoriesParentTree($options = array())
    {
        $query = $this->MC->db->select()->from('items_categories');
        if(isset($options['cat_id']) && isset($options['base_cat'])){
            $query->where('cat_id = ?',$options['cat_id']);
        }
        $rows = $this->MC->db->fetchAll($query);
        if(!$rows){
            return;
        }
        foreach($rows as $row)
        {
            if(isset($options['base_cat'])){
                $base_cat = $options['base_cat'];
            }else{
                $base_cat = $row['cat_id'];
            }
            if(!isset($this->_categoriesParentTree[$base_cat])){
                $this->_categoriesParentTree[$base_cat] = array();
            }
            if($base_cat == $row['cat_id'] && $row['parent_id'] == 0){
                $this->_categoriesParentTree[$base_cat][] = $row['parent_id'];
            }
            if($row['parent_id'] == 0){
                continue;
            }
            $this->_categoriesParentTree[$base_cat][] = $row['parent_id'];
            $this->_buildCategoriesParentTree(array('cat_id'=>$row['parent_id'],'base_cat'=>$base_cat));
        }
    }

    /**
     * @param $folderId
     * @param array $where
     * @param bool $lang
     * @param string $lang_key
     * @return array
     */
    public function listFields($folderId, $where = array(), $lang = true, $lang_key = 'MC_key_lang')
    {
        $query = $this->MC->db->select()->from('items_fields');
        $query->where('folder_id = ?', $folderId);
        $langs = MC_Core_Loader::appClass('Language', 'Lang', NULL, 'Shared');
        if ($lang){
            $query->joinLeft('items_fields_lang', "items_fields.field_id = items_fields_lang.field_id
                            AND lang_id = " . $langs->currentLang(), array('field_label', 'lang_id'));
        }
        foreach ($where as $field => $value)
        {
            $query->where($field . " = ?", $value);
        }
        $rows = $this->MC->db->fetchAll($query);
        if ($lang == false){
            $langRows = array();
            foreach ($rows as $row)
            {
                $langRows[$row['field_id']] = $this->getField($row['field_id'], $lang_key);
            }
            $rows = $langRows;
        }
        if (!$rows){
            $rows = array();
        }
        return $rows;
    }

    /**
     * @param $field_id
     * @param string $lang_key
     * @return mixed
     */
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

    /**
     * @param $field_id
     * @param bool $lang
     * @return string
     */
    public function create_field($field_id, $lang = true)
    {
        $field_name = 'field_' . $field_id;
        if ($lang){
            $table_name = 'items_fields_data_lang';
        }else{
            $table_name = 'items_fields_data';
        }
        $query = "Show columns from " . $table_name . " like '" . $field_name . "'";

        $fields = $this->MC->db->fetchAll($query);

        if (!is_array($fields) || count($fields) == 0){
               $this->MC->db->query("ALTER TABLE " . $table_name . " ADD " . $field_name . " VARCHAR(255);");
        }
        return $field_name;
    }

    /**
     * @param $item_id
     * @param array $fields
     * @param $lang_id
     * @return bool
     */
    public function update_field($item_id, array $fields, $lang_id)
    {
        $fields['item_id'] = $item_id;

        if (is_numeric($lang_id) && $lang_id != 0){
            $table_name = 'items_fields_lang';
            $where = $this->MC->db->quoteInto("lang_id = ? AND ", $lang_id);
            $fields['lang_id'] = $lang_id;
        }else{
            $table_name = 'items_fields';
        }
        $where.= $this->MC->db->quoteInto(" item_id = ?", $item_id);
        if ($this->MC->db->fetchRow($this->MC->db->select()->from($table_name)->where($where))){
            $this->MC->db->update($table_name, $fields, $where);
        }else{
            $this->MC->db->insert($table_name, $fields);
        }
        return true;
    }


    /**
     * @param $parentId
     * @param $langId
     * @param array $where
     * @return array|bool
     */
    public function categoriesTree($parentId,$langId,$where = array())
    {
        $query = $this->getCategoryByParentIdAndlangId($parentId,$langId,$where);
        if(!$query){
            return false;
        }
        $results = array();
        foreach($query as $row)
        {
            $childs = $this->categoriesTree($row['cat_id'],$langId,$where);
            if($childs){
                $row['childs']  = $childs;
            }
            $results[$row['cat_id']]  =  $row;
        }
        return $results;
    }

    /**
     * @param $parentId
     * @param $langId
     * @param array $where
     * @return array|bool
     */
    public function categoriesTreeBySequence($parentId,$langId,$where = array())
    {
        $query = $this->getCategoryByParentIdAndlangId($parentId,$langId,$where);
        if(!$query){
            return false;
        }
        $results = array();
        foreach($query as $row)
        {
            $results[$row['cat_id']]  =  $row;
            $childs = $this->categoriesTreeBySequence($row['cat_id'],$langId,$where);
            if($childs){
                foreach($childs as $child )
                {
                    $results[$child['cat_id']]  =  $child;
                    $this->categoriesTreeBySequence($child['cat_id'],$langId,$where);
                }
            }
        }
        return $results;
    }

    /**
     * @param $itemId
     * @param array $where
     * @return mixed
     */
    public function getItemByItemId($itemId,$where = array())
    {
        $itemQuery = $this->_itemQuery();
        $itemQuery->where('items_lang.item_id = ? ', $itemId);
        $result = $this->MC->db->fetchAll($itemQuery);
        $result = $this->_organizeItem($result);
        return $result;
    }

    /**
     * @param array $itemRows
     * @return mixed
     */
    protected function _organizeItem(array $itemRows )
    {
        $items = reset($itemRows);
        $items['item_lang'] = array();
        foreach($itemRows as $item)
        {
            $items['item_lang'][$item['lang_id']] = $item;
            if($langFields = $this->getFieldByItemIdAndLangId($item['item_id'],$item['lang_id']) && count($langFields) > 0){
                $items['item_lang'][$item['lang_id']]['fields_lang'] = $langFields;
            }
        }
        if($fields = $this->getFieldByItemId($items['item_id']) && count($fields) > 0){
            $items['fields'] = $fields;
        }
        return $items;
    }

    /**
     * @param $itemId
     * @return array
     */
    public function getFieldByItemId($itemId)
    {
        $fieldsQuery = $this->MC->db->select()->from('items_fields_data')->where('item_id = ?',$itemId);
        $itemFields = $this->MC->db->fetchRow($fieldsQuery);
        unset($itemFields['item_id']);
        $fields = array();
        if($itemFields){
            foreach($itemFields as $fieldId=>$fieldValue)
            {
                $fieldId = str_replace('field_','',$fieldId);
                $fields[$fieldId] = $fieldValue;
            }
        }
        return $fields;
    }
    /**
     * @param $itemId
     * @param $langId
     * @return array
     */
    public function getFieldByItemIdAndLangId($itemId,$langId)
    {
        $fieldQuery = $this->MC->db->select()->from('items_fields_data_lang');
        $fieldQuery->where('item_id = ?',$itemId);
        $fieldQuery->where('lang_id = ? ',$langId);
        $langFieldsResult = $this->MC->db->fetchRow($fieldQuery);
        $langFields = array();
        if($langFieldsResult){
            unset($langFieldsResult['item_id']);
            unset($langFieldsResult['lang_id']);
            foreach($langFieldsResult as $field_id=>$field_value)
            {
                $field_id = str_replace('field_','',$field_id);
                $langFields[$field_id] = $field_value;
            }
        }
        return $langFields;
    }
    /**
     * @param $catId
     * @param $langId
     * @param array $where
     * @return array|bool false
     */
    public function getItemByCatIdAndLangId($catId,$langId,array $where = array())
    {
        $itemQuery = $this->_itemQuery();
        $itemQuery->where('items.cat_id = ?',$catId);
        $itemQuery->where('items_lang.lang_id = ?',$langId);
        $this->_where($itemQuery,$where);
        $results = $this->MC->db->FetchAll($itemQuery);
        return $results;
    }

    /**
     * @param $query
     * @param array $where
     */
    protected function _where(&$query,array $where)
    {
        foreach($where as $key=>$value){
            $query->where($key.' = ?',$value);
        }
    }

    public function getCatByCatId($catId)
    {
        $catQuery = $this->_catQuery();
        $catQuery->where('items_categories.cat_id = ?',$catId);
        $result = $this->MC->db->fetchAll($catQuery);
        $catRows = reset($result);
        foreach($result as $cat)
        {
            $catRows['cat_lang'][$cat['lang_id']] = $cat;
        }
        return $catRows;
    }

    /**
     * @param $catId
     * @param $langId
     * @param array $where
     * @return array|bool
     */
    public function getCatByCatIdAndLangId($catId,$langId,$where = array())
    {
        $catQuery = $this->_catQuery();
        $catQuery->where('items_categories_lang.cat_id IN (?)',$catId);
        $catQuery->where('items_categories_lang.lang_id = ?',$langId);
        $this->_where($catQuery,$where);
        $result = $this->MC->db->fetchRow($catQuery);
        return $result;
    }
    public function getCatByInCatIdAndLangId($catsIds,$langId,$where = array())
    {
        $catQuery = $this->_catQuery();
        $catQuery->where('items_categories_lang.cat_id IN (?)',$catsIds);
        $catQuery->where('items_categories_lang.lang_id = ?',$langId);
        $this->_where($catQuery,$where);
        $catQuery->order(array('FIELD(items_categories_lang.cat_id,'.$catsIds.' )'));
        $result = $this->MC->db->fetchAll($catQuery);
        return $result;
    }
    public function getCategoryByParentIdAndlangId($parentId,$langId,$where = array())
    {
        $catQuery = $this->_catQuery();
        $catQuery->where('items_categories.parent_id = ?',$parentId);
        $catQuery->where('items_categories_lang.lang_id = ?',$langId);
        $this->_where($catQuery,$where);
        $result = $this->MC->db->fetchAll($catQuery);
        return $result;
    }
    /**
     * @return Zend_Db
     */
    protected function _catQuery()
    {
        $catQuery = $this->MC->db->select()->from('items_categories');
        $catQuery->join('items_categories_lang', 'items_categories.cat_id = items_categories_lang.cat_id');
        return $catQuery;
    }

    /**
     * @param $folderId
     * @return array
     */
    public function getFolderByFolderId($folderId)
    {
        $folderQuery = $this->_folderQuery();
        $folderQuery->where('items_folders_lang.folder_id = ?',$folderId);
        $result = $this->MC->db->fetchAll($folderQuery);
        $folderRow = array();
        $folderRow['folder'] = reset($result);
        foreach($result as $folder)
        {
            $folderRow['folder']['folder_lang'][$folder['lang_id']] = $folder;
        }
        return $folderRow;
    }

    /**
     * @param $folderId
     * @param $langId
     * @param array $where
     * @return array|bool
     */
    public function getFolderByFolderIdAndLangId($folderId,$langId,$where = array())
    {
        $folderQuery = $this->_folderQuery();
        $folderQuery->where('items_folders_lang.folder_id = ?',$folderId);
        $folderQuery->where('lang_id = ?',$langId);
        $this->_where($folderQuery,$where);
        $result = $this->MC->db->fetchRow($folderQuery);
        return $result;
    }

    /**
     * @param $langId
     * @param array $where
     * @return mixed
     */
    public function getFolderByLangId($langId,$where = array())
    {
        $folderQuery = $this->_folderQuery();
        $folderQuery->where('lang_id = ?',$langId);
        $this->_where($folderQuery,$where);
        $result = $this->MC->db->fetchAll($folderQuery);
        return $result;
    }

    /**
     * @return mixed
     */
    protected function _folderQuery()
    {
        $folderQuery = $this->MC->db->select()->from('items_folders');
        $folderQuery->join('items_folders_lang', 'items_folders.folder_id = items_folders_lang.folder_id');
        return $folderQuery;
    }

    /**
     * @return Zend_Db
     */
    protected function _itemQuery()
    {
        $itemQuery = $this->MC->db->select()->from('items');
        $itemQuery->join('items_lang', 'items_lang.item_id = items.item_id');
        $itemQuery->join('items_categories', 'items_categories.cat_id = items.cat_id');
        return $itemQuery;
    }
}

