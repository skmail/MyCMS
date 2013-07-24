<?php

class App_Items_Shared_Api {


    protected $db;

    protected $_errors = array();
    protected $_itemId;

    protected $_folderId = null;

    protected $folderPrefix = 'folder-';

    public function __construct()
    {
        $this->db = Zend_Registry::get('db');
        $this->MC =& MC_Core_Instance::getInstance();
    }


    //$itemData['item']
    //$itemData['item_langs']
    //$itemData['fields']
    //$itemData['fields_lang']
    /**
     * @param array $itemData
     * @return array
     */
    public function saveItem(array $itemData)
    {
        $errors = array();
        if(!isset($itemData['item']['cat_id'])){
            $errors[] = 'undefined_category';
        }else{
            if(intval($itemData['item']['cat_id']) == 0){
                $errors[] = 'undefined_category';
            }
        }
        if(!isset($itemData['item']['item_status'])){
            $itemData['item']['item_status'] == 3;
        }else{
            if(!array_key_exists($itemData['item_status'],App_Items_Shared_Core::itemStatus())){
                $itemData['item']['item_status'] == 3;
            }
        }
        if(!isset($itemData['item_langs'])){
            $errors[] = 'item_lang_array_empty';
        }

        //All fine save item
        if(count($errors) == 0){
            if(intval($itemData['item']['item_id']) == 0){
                #-------------------------------
                #  Add new item
                #-------------------------------
                $this->db->insert('items',$itemData['item']);
                $itemId = $this->db->lastInsertId();
                //-------------------------------
                //  Save item Lang
                //-------------------------------
                foreach($itemData['item_langs'] as $itemLang )
                {
                    $itemLang['item_id'] = $itemId;
                    $this->db->insert('items_lang',$itemLang);
                }
                //-------------------------------
                //  Save item fields
                //-------------------------------
                $this->db->insert('items_fields_data',array('item_id'=>$itemId));
                foreach($itemData['fields'] as $field_id=>$field_value)
                {
                    $where = $this->db->quoteInto('item_id = ? ', $itemId);
                    $this->db->update('items_fields_data',array('field_'.$field_id=>$field_value),$where);
                }
                //-------------------------------
                //  Save item fields lang
                //-------------------------------
                foreach($itemData['fields_lang'] as $lang_id=>$fields)
                {
                    $this->db->insert('items_fields_data_lang',array('item_id'=>$itemId,'lang_id'=>$lang_id));
                    if(is_array($fields)){
                        foreach($fields as $field_id=>$field_value)
                        {
                            $where = $this->db->quoteInto('item_id = ? AND ', $itemId);
                            $where.= $this->db->quoteInto('lang_id = ? ', $lang_id);
                            $this->db->update('items_fields_data_lang',array('field_'.$field_id=>$field_value),$where);
                        }
                    }
                }
            }else{
                #-------------------------------
                #  Update exists item
                #-------------------------------
                $itemId = $itemData['item']['item_id'];
                unset($itemData['item']['item_id']);
                $where = $this->db->quoteInto('item_id = ? ', $itemId);
                $this->db->update('items',$itemData['item'],$where);
                #-------------------------------
                #  Update/Insert item lang
                #-------------------------------
                foreach($itemData['item_langs'] as $itemLang )
                {
                    $itemLang['item_id'] = $itemId;
                    $query = $this->db->select()->from('items_lang');
                    $query->where('item_id = ?',$itemLang['item_id']);
                    $query->where('lang_id = ?',$itemLang['lang_id']);
                    if(!$this->db->fetchRow($query)){
                        //-------------------------------
                        //  Insert item lang
                        //-------------------------------
                        $this->db->insert('items_lang',$itemLang);
                    }else{
                        //-------------------------------
                        //  Update item lang
                        //-------------------------------
                        $where = $this->db->quoteInto('item_id = ? AND ', $itemId);
                        $where.= $this->db->quoteInto('lang_id = ? ', $itemLang['lang_id']);
                        $this->db->update('items_lang',$itemLang,$where);
                    }
                }

                #-------------------------------
                #  Update/Insert item field
                #-------------------------------

                foreach($itemData['fields'] as $field_id=>$field_value)
                {
                    $fieldData = $this->MC->Queries->getField($field_id);
                    $fieldClass = 'App_Items_Shared_FieldsTypes_'.$fieldData['field_type'].'_Field';
                    $field_value = $fieldClass::setFieldValue($field_value);
                    if(count($fieldClass::$_fieldErrors) == 0){
                        $this->updateField($itemId,$field_id,$field_value);
                    }else{
                        $this->_errors = array_merge($this->_errors,$fieldClass::$_fieldErrors);
                    }
                }

                //-------------------------------
                //  Save item fields lang
                //-------------------------------

                foreach($itemData['fields_lang'] as $lang_id=>$fields)
                {
                    foreach($fields as $field_id=>$field_value)
                    {
                        $fieldData = $this->MC->Queries->getField($field_id);
                        $fieldClass = 'App_Items_Shared_FieldsTypes_'.$fieldData['field_type'].'_Field';
                        $field_value = $fieldClass::setFieldValue($field_value);
                        if(count($fieldClass::$_fieldErrors) == 0){
                            $this->updateField($itemId,$field_id,$field_value,$lang_id);
                        }else{
                            $this->_errors = array_merge($this->_errors,$fieldClass::$_fieldErrors);
                        }
                    }
                }
            }
            if(empty($itemData['item']['item_url'])){
                $where = $this->db->quoteInto('item_id = ? ', $itemId);
                $this->db->update('items',array('item_url'=>'item-'.$itemId),$where);
            }
            $return  = array('success'=>true,'item_id'=>$itemId);
        }else{
            //An error occurred
            $return = array('errors'=>$errors);
        }
        return $return;
    }

    public function validSave($saveItemMethod)
    {
        if(isset($saveItemMethod['errors'])){
            $this->_errors = $saveItemMethod['errors'];
            return false;
        }else{
            $this->_itemId = $saveItemMethod['item_id'];
            return true;
        }
    }

    public function updateField($item_id,$fieldId,$fieldValue, $lang_id = false)
    {
        $dataArray = array();
        $dataArray['item_id'] = $item_id;
        if ($lang_id != false && intval($lang_id) != 0){
            $table_name = 'items_fields_data_lang';
            $where = $this->db->quoteInto("lang_id = ? AND ", $lang_id);
            $dataArray['lang_id'] = $lang_id;
        }else{
            $table_name = 'items_fields_data';
        }
        $dataArray['field_'.$fieldId] = $fieldValue;
        $where.= $this->db->quoteInto(" item_id = ?", $item_id);
        if ($this->db->fetchRow($this->db->select()->from($table_name)->where($where))){
                $this->db->update($table_name, $dataArray, $where);
        }else{
            $this->db->insert($table_name, $dataArray);
        }
        return true;
    }

    public function getItemId()
    {
        return $this->_itemId;
    }
    public function getFolderId()
    {
        if($this->_folderId == null){
            return false;
        }
        return $this->_folderId;
    }

    public function getSaveErrors()
    {
        return $this->_errors;
    }

    /**
     * @param $folderData
     * @return bool
     * @throws MC_Core_Exception
     */
    public function saveFolder($folderData)
    {
        /**
         * @var $folderData
         * @example $folderData = array(
         *                              'folder'=>array(
         *                                              'folder_status'=>'1',
         *                                              'folder_id'=>1
         *                                  ),
         *                              'folder_lang'=>array(
         *                                              1=>array('folder_name'=>'News')
         *                              ));
         */

        if(!isset($folderData['folder']) || !isset($folderData['folder_lang']) || !is_array($folderData['folder_lang'])){
            throw new MC_Core_Exception('unexpected_error');
        }

        // Init folder fields
        $folder = array();
        $folder['folder_status'] = $folderData['folder_status'];
        if($folderData['folder']['folder_url'] != ""){
            $folder['folder_url'] = MC_Models_Url::friendly($folderData['folder']['folder_url']);
        }

        if(!isset($folderData['folder']['folder_status'])){
            $folder['folder_status'] = 0;
        }else{
            $folder['folder_status'] = $folderData['folder']['folder_status'];
        }
        if(!isset($folderData['folder']['folder_id'])){
            // Add new folder
            $this->MC->db->insert('items_folders',$folder);
            $folderId =  $this->MC->db->lastInsertId();
            if($folderData['folder']['folder_url'] != ""){
                $folder['folder_url'] = $this->folderPrefix . $folderId;
                $this->MC->db->update('items_folders',$folder,$this->db->quoteInto('folder_id = ? ', $folderId));
            }
        }else{
            $folderId = intval($folderData['folder']['folder_id']);
            if(empty($folderData['folder']['folder_url'])){
                $folder['folder_url'] = $this->folderPrefix . $folderId;
                $this->MC->db->update('items_folders',$folder,$this->db->quoteInto('folder_id = ? ', $folderId));
            }
            $this->MC->db->update('items_folders',$folder,$this->db->quoteInto('folder_id = ? ', $folderId));
        }
        foreach($folderData['folder_lang'] as $langId=>$folderLang)
        {
            if($this->checkFolder($folderId,$langId)){
                $where = $this->db->quoteInto('folder_id = ? AND ', $folderId);
                $where .= $this->db->quoteInto('lang_id = ?  ', $langId);
                $folderLang['lang_id'] = $langId;
                $folderLang['folder_id'] = $folderId;
                $this->MC->db->update('items_folders_lang',$folderLang,$where);
            }else{
                $folderLang['lang_id'] = $langId;
                $folderLang['folder_id'] = $folderId;
                $this->MC->db->insert('items_folders_lang',$folderLang);
            }
        }
        if(count($this->getSaveErrors()) > 0){
            return false;
        }
        $this->_folderId = $folderId;
        return true;
    }

    protected function checkFolder($folderId,$langId)
    {
        $query = $this->MC->db->select()->from('items_folders_lang')->where('folder_id = ?',$folderId)->where('lang_id = ?',$langId);
        return $this->MC->db->fetchRow($query);
    }
}