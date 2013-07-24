<?php
class App_Items_Admin_Items extends Admin_Model_ApplicationAbstract
{


    public $plugin = array();

    /**
     * @param array $application
     */
    public function __construct($application = array())
    {
        parent::__construct($application);
        $this->MC->load->appLibrary('Queries');
        $this->MC->load->appLibrary('Functions');
        $this->MC->load->appLibrary('Forms');
        $this->menu   = $this->MC->Functions->_sideMenu();
        $this->plugin = $this->MC->Functions->_setDependecy();
        $this->setNav($this->translate('Items'),'window/index');
    }

    /**
     * @desc Index page - list folder
     * @return array
     */
    public function index()
    {
        $folders = $this->MC->Queries->getFolderByLangId($this->application['lang_id']);
        $this->assign('folders',$folders);
        $this->setSidebar('indexSidebar');
        return $this->application;
    }

    /**
     * @desc list categories
     * @param array $options
     * @return array
     * @throws MC_Core_Exception
     */
    public function categories($options = array())
    {
        $folderId = (isset($options['folderId']))?$options['folderId']:intval($this->MC->Zend->getRequest()->getParam('folderId'));
        $folderData = $this->MC->Queries->getFolderByFolderIdAndLangId($folderId,$this->application['lang_id']);
        if(!$folderData){
            throw new  MC_Core_Exception('folder_not_found');
        }
        $this->setNav($folderData['folder_name']);
        $categoriesList = $this->MC->Queries->categoriesTree(0,$this->application['lang_id'],array('folder_id'=>$folderId));
        $this->setSidebar('categoriesSidebar');
        $this->assign('folder',$folderData);
        $this->assign('categories',$categoriesList);
        return $this->application;
    }

    /**
     * @desc List category items
     * @param int $catId
     * @return array
     * @throws MC_Core_Exception
     */
    public function items($catId = 0)
    {
        $catId = intval(($catId == 0) ? $this->MC->Zend->getRequest()->getParam('cat_id') : $catId);
        $itemStatus = intval($this->_Zend->getRequest()->getParam('status'));
        $catInfoArray = $this->MC->Queries->getCatByCatIdAndLangId($catId,$this->application['lang_id']);
        if (!$catInfoArray){
            throw new MC_Core_Exception('not_found_category');
        }
        $folderData = $this->MC->Queries->getFolderByFolderIdAndLangId($catInfoArray['folder_id'],$this->application['lang_id']);
        if(!$folderData){
            throw new  MC_Core_Exception('folder_not_found');
        }
        $this->setNav($folderData['folder_name'],'window/categories/folderId/'.$catInfoArray['folder_id']);
        $itemsStatuesCatsList = $this->MC->Functions->itemStatus();
        if (!array_key_exists($itemStatus, $itemsStatuesCatsList)){
            $itemStatus = 1;
        }
        $itemsList = $this->MC->Queries->getItemByCatIdAndLangId($catId,$this->application['lang_id'],array('item_status' => $itemStatus));
        $parentsInfo = $this->MC->Queries->getCatByInCatIdAndLangId($catInfoArray['parents'],$this->application['lang_id']);
        if($parentsInfo){
            $parentsInfo = array_reverse($parentsInfo);
            foreach($parentsInfo as $parent)
            {
                $this->setNav($parent['cat_name'],'window/items/cat_id/' . $parent['cat_id']);
            }
        }
        $this->setNav( $catInfoArray['cat_name']);
        $this->assign('itemsStatuesCats',$itemsStatuesCatsList);
        $this->assign('itemStatus',$itemStatus);
        $this->assign('itemsList',$itemsList);
        $this->assign('catInfo',$catInfoArray);
        $this->setSidebar('itemsSidebar');
        return $this->application;
    }

    /**
     * @desc Item page add/edit
     * @param array $options
     * @return array|void
     * @throws MC_Core_Exception
     */
    public function item($options = array())
    {
        $do = (isset($options['do'])) ? $options['do'] : $this->_Zend->getRequest()->getParam('do');
        if ($do != 'add' && $do != 'edit'){
            throw new MC_Core_Exception('invalid_request');
        }

        if ($do == 'add'){
            $cat_id = (isset($options['cat_id'])) ? $options['cat_id'] : intval($this->_Zend->getRequest()->getParam('cat_id'));
            $itemQuery = $this->MC->Queries->getCatByCatIdAndLangId($cat_id,$this->application['lang_id']);
            $nav = $this->translate('add_new_item');
            if (!$itemQuery){
                return $this->setError();
            }
        }

        if ($do == 'edit'){
            $itemId = (isset($options['itemid'])) ? $options['itemid'] : intval($this->_Zend->getRequest()->getParam('itemid'));
            $itemQuery = $this->MC->Queries->getItemByItemId($itemId);
            if (!$itemQuery){
                return $this->setError();
            }
            $nav = $this->translate('edit_item') . " " . $itemQuery['item_lang'][$this->application['lang_id']]['item_title'];
            $itemQuery = array_merge($itemQuery,$this->MC->Queries->getCatByCatIdAndLangId($itemQuery['cat_id'],$this->application['lang_id']));
        }
        $folderData = $this->MC->Queries->getFolderByFolderIdAndLangID($itemQuery['folder_id'],$this->application['lang_id']);
        if(!$folderData){
            throw new  MC_Core_Exception('folder_not_found');
        }
        $this->setNav($folderData['folder_name'],'window/categories/folderId/'.$itemQuery['folder_id']);
        $parentsInfo = $this->MC->Queries->getCatByCatIdAndLangId($itemQuery['parents'],$this->application['lang_id']);
        if($parentsInfo){
            $parentsInfo = array_reverse($parentsInfo);
            foreach($parentsInfo as $parent)
            {
                $this->setNav($parent['cat_name'],'window/items/cat_id/' . $parent['cat_id']);
            }
        }

        $this->setNav($itemQuery['cat_name'],'window/items/cat_id/' . $itemQuery['cat_id']);
        $this->setNav($nav);
        $itemQuery['do'] = $do;
        $this->assign('itemForm',(isset($options['itemForm'])) ? $options['itemForm'] : $this->MC->Forms->itemForm($itemQuery));
        return $this->application;
    }


    #-------------------------------
    #  Category page edit/add
    #-------------------------------
    public function category($options = array())
    {

        $do = (isset($options['do'])) ? $options['do'] : $this->_Zend->getRequest()->getParam('do');
        if ($do != "edit" && $do != "add"){
            return $this->setError();
        }

        if ($do == 'edit'){
            $cat_id = (isset($options['cat_id'])) ? $options['cat_id'] : intval($this->_Zend->getRequest()->getParam('cat_id'));
            if ($cat_id == 0){
                return $this->setError();
            }

            $catQuery = $this->MC->Queries->getCatByCatId(array('cat_id'=>$cat_id));

            if (!$catQuery){
                return $this->setError();
            }

            $folderData = $this->MC->Queries->getFolderByFolderIdAndLangId($catQuery['folder_id'],$this->application['lang_id']);
            if(!$folderData){
                throw new  MC_Core_Exception('folder_not_found');
            }

            $this->setNav($folderData['folder_name'],'window/categories/folderId/'.$catQuery['folder_id']);
            $parentsInfo = $this->MC->Queries->getCatByInCatIdAndLangId($catQuery['parents'],$this->application['lang_id']);

            if($parentsInfo){
                $parentsInfo = array_reverse($parentsInfo);
                foreach($parentsInfo as $parent)
                {
                    $this->setNav($parent['cat_name'],'window/items/cat_id/' . $parent['cat_id']);
                }
            }

            $this->setNav($this->translate('edit_category'));
            $this->setNav($catQuery['cat_lang'][$this->MC->model->lang->currentLang('lang_id')]['cat_name']);
            $catQuery['do'] = 'edit';
        }else{
            $folderId = intval((isset($options['folderId'])) ? $options['folderId'] : $this->_Zend->getRequest()->getParam('folderId'));
            $cat_id = (isset($options['cat_id'])) ? $options['cat_id'] : intval($this->_Zend->getRequest()->getParam('cat_id'));

            if ($cat_id != 0){
                $catQuery = $this->MC->Queries->getCatByCatIdAndLangId($cat_id,$this->application['lang_id']);
                if (!$catQuery){
                    return $this->setError();
                }
                $catQuery['parent_id'] = $cat_id;
            }else{
                if($folderId != 0){
                    $catQuery['folder_id'] = $folderId;
                }

                $folderData = $this->MC->Queries->getFolderByFolderIdAndLangId($folderId,$this->application['lang_id']);
                if(!$folderData){
                    throw new  MC_Core_Exception('folder_not_found');
                }
                $this->setNav($folderData['folder_name'],'window/categories/folderId/'.$catQuery['folder_id']);
                $this->setNav($this->translate('add_new_category'));
            }
            $catQuery['do'] = 'add';
        }
        $this->assign('categoryForm',(isset($options['categoryForm'])) ? $options['categoryForm'] : $this->MC->Forms->categoryForm($catQuery));
        return $this->application;
    }

    #-------------------------------
    #  Save/Add item proccess
    #-------------------------------
    public function saveItem()
    {
        $request = $this->_Zend->getRequest();
        $data = $request->getPost();

        if ($data['do'] != "edit" && $data['do'] != "add"){
            return $this->setError();
        }

        $itemForm = $this->MC->Forms->itemForm($request->getPost());

        if ($itemForm->isValid($request->getPost())){
            $itemApi = new App_Items_Shared_Api();
            //-------------------------------
            //  Prepare item data
            //-------------------------------
            $item = array();
            $item['item_id'] = $data['item_id'];
            $item['item_url'] = $data['item']['item_url'];
            $item['item_status'] = $data['item']['item_status'];
            $item['cat_id'] = $data['cat_id'];

            //-------------------------------------------
            //  Prepare item lang data & Lang fields
            //-------------------------------------------
            $itemLang = array();
            $langFields = array();

            foreach($data['item_lang'] as $langId=>$item_lang)
            {
                if(empty($item_lang['item_title'])){
                    continue;
                }

                $itemLang[$langId]['lang_id'] = $langId;
                $itemLang[$langId]['item_title'] = $item_lang['item_title'];

                if(isset($item_lang['fields_lang'])){
                    $langFields[$langId] = $item_lang['fields_lang'];
                }
            }

            //-------------------------------------------
            //  Prepare item fields
            //-------------------------------------------
            $itemFields = array();
            if(isset($data['fields'])){
                $itemFields = $data['fields'];
            }

            $data = array();
            $data['item'] = $item;
            $data['item_langs'] = $itemLang;
            $data['fields'] = $itemFields;
            $data['fields_lang'] = $langFields;

            if($result = $itemApi->validSave($itemApi->saveItem($data))){

                $itemOptions['do'] = 'edit';
                $itemOptions['itemid'] = $itemApi->getItemId();
                $this->setMessage('item_saved_succesfully','success');
                $this->replaceUrl($this->MC->Functions->itemUrl($itemOptions['itemid']));

                if(count($itemApi->getSaveErrors()) > 0){
                    $this->setMessage($itemApi->getSaveErrors() ,'error','item_saved_succesfully_but_errors_occured');
                }
            }
            else
            {
                $itemOptions['do'] = $data['do'];
                $itemOptions['itemForm'] = $itemForm;
                $this->setMessage($itemApi->getSaveErrors(),'error');
            }
        }
        else
        {
            $itemOptions['itemForm'] = $itemForm;
            $itemOptions['do'] = $data['do'];
        }

        $this->merge($this->item($itemOptions));
        $this->setView('item');

        return $this->application;
    }

    #-------------------------------
    #  Save/Add Category process
    #-------------------------------
    public function saveCat()
    {
        $request = $this->_Zend->getRequest();
        $data = $request->getPost();
        $do = $data['do'];
        $dataArray = array('folder_id'=>$data['folder_id']);

        if ($do == 'edit'){
            $cat_id = intval($request->getPost('cat_id'));
            $cat = $this->MC->Queries->getCatByCatId($cat_id);
            if (!$cat){
                return $this->setError();
            }
        }
        $catForm = $this->MC->Forms->categoryForm($dataArray);
        if ($catForm->isValid($data))
        {

            $category['cat_url']        = MC_Models_Url::friendly($data['category']['cat_url']);
            $category['cat_status']     = $data['category']['cat_status'];
            $category['parent_id']      = $data['category']['parent_id'];
            $category['folder_id']      = $data['folder_id'];
            $category_lang['lang_id']   = $this->application['lang_id'];
            $category['cat_params']     = Zend_Json::encode($data['params']);
            $cat_lang = $data['category_lang']['cat_lang'];
            foreach ($cat_lang as $lang_id => $catLang)
            {
                if (empty($catLang['cat_name']) && trim($catLang['cat_name']) == ""){
                    unset($cat_lang[$lang_id]);
                }
            }
            if ($do == 'edit'){
                $cat_id = $data['cat_id'];
                $this->MC->db->update('items_categories', $category, ' cat_id = ' . $cat_id);

                $where = $this->MC->db->quoteInto("cat_id = ?", $cat_id);
                $this->MC->db->delete('items_categories_lang', $where);

                $this->setMessage($this->translate('category_saved_success'),'success');
            }else{
                $this->MC->db->insert('items_categories', $category);
                $cat_id = $category_lang['cat_id'] = $this->MC->db->lastInsertId();
                $this->setMessage($this->translate('category_added_success'),'success');
                $do = 'edit';
            }
            if(empty($category['cat_url'])){
                $this->MC->db->update('items_categories', array('cat_url'=>'cat-'.$cat_id), ' cat_id = ' . $cat_id);
            }
            foreach ($cat_lang as $lang_id => $cat)
            {
                $cat['lang_id'] = $lang_id;
                $cat['cat_id'] = $cat_id;
                $this->MC->db->insert('items_categories_lang', $cat);
            }
            $catsChildsParents = $this->MC->Queries->buildCategoiesRelations();
            foreach($catsChildsParents['childs'] as $catid => $childs)
            {
                $where = $this->MC->db->quoteInto("cat_id = ?", $catid);
                $this->MC->db->update('items_categories',array('childs'=>implode(',',$childs)),$where);
            }
            foreach($catsChildsParents['parents'] as $catid => $parents)
            {
                $where = $this->MC->db->quoteInto("cat_id = ?", $catid);
                $this->MC->db->update('items_categories',array('parents'=>implode(',',$parents)),$where);
            }
            $this->replaceUrl($this->MC->Functions->catUrl($cat_id));
            $catOptions['do'] = $do;
            $catOptions['cat_id'] = $cat_id;
        }else{
            $this->setMessage($this->translate('some_fields_empty'),'error');
            $catOptions['do'] = $do;
            $catOptions['folderId'] = $data['folder_id'];
            $catOptions['categoryForm'] = $catForm;
        }
        $this->merge($this->category($catOptions));
        $this->setView('category');
        return $this->application;
    }

    public function deleteCat()
    {
        $catId = intval($this->_Zend->getRequest()->getParam('catId'));
        $cats = array();
        $folderId = (intval($this->_Zend->getRequest()->getPost('folder_id'))!=0)?intval($this->_Zend->getRequest()->getPost('folder_id')):intval($this->_Zend->getRequest()->getParam('folderId'));
        $folderData = $this->MC->Queries->getFolderByFolderIdAndLangId($folderId,$this->MC->model->lang->currentLang('lang_id'));
        if (!$folderData){
            throw new MC_Core_Exception('not_found_folder');
        }
        if ($catId == 0){
            $catId = $this->_Zend->getRequest()->getPost('cat_id');

            if (is_array($catId) && count($catId) > 0){
                $cats = $catId;
            }
        }else{
            $cats[] = $catId;
        }
        if(count($cats) > 0){
            foreach ($cats as $catId)
            {
                $catRow = $this->MC->Queries->getCatByCatIdAndLangId($catId,$this->application['lang_id']);
                if (!$catRow){
                    continue;
                }
                $this->MC->Queries->deleteCatByCatIdAndLangId($catId,$this->application['lang_id']);
            }
            $this->setMessage($this->translate('categories_was_deleted'),'success');
        }else{
            $this->setMessage($this->translate('no_categorie_seleted_to_delete'),'error');
        }
        $this->setView('categories');
        $this->merge($this->categories(array('folderId'=>$folderId)));
        return $this->application;
    }

    public function deleteFolder()
    {
        $folderId = intval($this->_Zend->getRequest()->getParam('folderId'));
        $folders = array();
        if ($folderId == 0){
            $folderId = $this->_Zend->getRequest()->getPost('folder_id');
            if (is_array($folderId) && count($folderId) > 0){
                $folders = $folderId;
            }
        }else{
            $folders[] = $folderId;
        }
        if(count($folders) > 0){
            foreach ($folders as $folderId)
            {
                $folderRow = $this->MC->Queries->getFolderByFolderIdAndLangId(intval($folderId),$this->application['lang_id']);
                if (!$folderRow){
                    continue;
                }
                $this->MC->Queries->deleteFolderByLangId($folderId,$this->application['lang_id']);
            }
            $this->setMessage($this->translate('folders_was_deleted'),'success');
        }else{
            $this->setMessage($this->translate('no_folders_seleted_to_delete'),'error');
        }
        $this->setView('index');
        $this->merge($this->index());
        return $this->application;
    }

    public function deleteField()
    {
        $fieldId = intval($this->_Zend->getRequest()->getParam('fieldId'));
        $fields = array();
        $folderId = intval($this->_Zend->getRequest()->getPost('folder_id'));

        if(!$this->MC->Queries->getFolderByFolderIdAndLangId($folderId,$this->application['lang_id'])){
            return $this->setError();
        }
        if ($fieldId == 0){
            $fieldId = $this->_Zend->getRequest()->getPost('field_id');
            if (is_array($fieldId) && count($fieldId) > 0){
                $fields = $fieldId;
            }
        }else{
            $fields[] = $fieldId;
        }
        if(count($fields) > 0){
            foreach ($fields as $fieldId)
            {
                $this->MC->Queries->deleteFieldByFieldIdAndLangId($fieldId,$this->application['lang_id']);
            }
            $this->setMessage($this->translate('folders_was_deleted'),'success');
        }else{
            $this->setMessage($this->translate('no_folders_seleted_to_delete'),'error');
        }
        $this->setView('fields');
        $this->merge($this->fields($folderId));
        return $this->application;
    }

    public function deleteItem()
    {
        $itemId = intval($this->_Zend->getRequest()->getParam('itemid'));
        $catId = (intval($this->_Zend->getRequest()->getParam('cat_id'))!=0)?intval($this->_Zend->getRequest()->getParam('cat_id')):intval($this->_Zend->getRequest()->getParam('catId'));
        $items = array();
        if($catId != 0){
            if(!$this->MC->Queries->getCatByCatIdAndLangId($catId,$this->application['lang_id'])){
                return $this->setError($this->translate('not_found_category'));
            }
        }else{
            return $this->setError($this->translate('not_found_category'));
        }
        if ($itemId == 0){
            $itemId = $this->_Zend->getRequest()->getPost('item');
            if (is_array($itemId) && count($itemId) > 0){
                $items = $itemId;
            }
        }else{
            $items[] = $itemId;
        }
        if(count($items)){
            foreach($items as $item )
            {
                $delete = $this->MC->Queries->deleteItemByItemIdAndLangId($item,$this->application['lang_id']);
            }
            $this->setMessage(sprintf($this->translate('no_of_items_deleted'), $delete['success']),'success');
        }else{
            $this->setMessage($this->translate('no_items_selected'),'error');
        }
        $this->setView('items');
        $this->merge($this->items($catId));
        return $this->application;
    }

    public function fields($folderId = 0)
    {
        $folderId = (intval($folderId) != 0)?$folderId:intval($this->_Zend->getRequest()->getParam('folderId'));
        $folderData = $this->MC->Queries->getFolderByFolderIdAndLangId($folderId,$this->application['lang_id']);
        if (!$folderData){
            throw new MC_Core_Exception('not_found_folder');
        }
        $this->setNav($this->translate('fields'));
        $this->setNav($folderData['folder_name']);
        $this->application['folder'] = $folderData;
        $this->application['fields'] = $this->MC->Queries->listFields($folderId);
        //print_r($this->application['fields']);
        //die();
        $this->application['sidebar'] = 'fieldsSidebar';
        return $this->application;
    }

    public function field($options = array())
    {
        $do = ($options['do']) ? $options['do'] : $this->_Zend->getRequest()->getParam('do');
        if ($do != "add" && $do != "edit"){
            return $this->setError();
        }
        if ($do == 'add'){
            $folderId = intval($this->_Zend->getRequest()->getParam('folderId'));
            $folderData = $this->MC->Queries->getFolderByFolderIdAndLangId($folderId,$this->application['lang_id']);
            if (!$folderData){
                throw new MC_Core_Exception('not_found_folder');
            }
            $this->setNav($this->translate('fields'));
            $this->setNav($folderData['folder_name']);
            $this->setNav($this->translate('add_new_field'));
            $data['folder_id'] = $folderData['folder_id'];
            $data['do'] = 'add';
        }else if ($do == 'edit'){
            $field_id = (isset($options['field_id'])) ? $options['field_id'] : intval($this->_Zend->getRequest()->getParam('field_id'));
            $field = $this->MC->Queries->getField($field_id);
            if (!$field){
                throw new MC_Core_Exception('not_found_field');
            }
            $folderData = $this->MC->Queries->getFolderByFolderIdAndLangId($field['folder_id'],$this->application['lang_id']);
            if (!$folderData){
                throw new MC_Core_Exception('not_found_folder');
            }
            $this->setNav($this->translate('fields'),'window/fields/folderId/'.$folderData['folder_id']);
            $this->setNav($folderData['folder_name'],'window/fields/folderId/'.$folderData['folder_id']);
            $this->setNav($this->translate($field['MC_key_lang'][$this->application['lang_id']]['field_label']));
            $data = $field;
            $data['do'] = 'edit';
        }
        $this->application['fieldForm'] = (isset($options['fieldForm'])) ? $options['fieldForm'] : $this->MC->Forms->field($data);
        return $this->application;
    }

    public function saveField()
    {
        $request = $this->_Zend->getRequest()->getPost('MC_field');
        if ($request['do'] != "add" && $request['do'] != "edit"){
            return;
        }

        $fieldForm = $this->MC->Forms->field();
        if ($fieldForm->isValid($request))
        {
            $data['field_name'] = $request['field_name'];
            $data['field_type'] = $request['field_type'];
            $data['multi_lang'] = $request['multi_lang'];
            $data['folder_id'] = $request['folder_id'];

            if ($request['do'] == 'add'){
                $this->MC->db->insert('items_fields', $data);
                $data['field_id'] = $this->MC->db->lastInsertId();
                foreach ($request['MC_key_lang'] as $lang_id => $lang_val)
                {
                    if (empty($lang_val['field_label'])){
                        continue;
                    }
                    $langData = array();
                    $langData['field_id'] = $data['field_id'];
                    $langData['field_label'] = $lang_val['field_label'];
                    $langData['lang_id'] = $lang_id;
                    $this->MC->db->insert('items_fields_lang', $langData);
                }
                $data['do'] = 'edit';

                if ($data['multi_lang'] == 1){
                    $createFieldLang = true;
                }else{
                    $createFieldLang = false;
                }

                $this->MC->Queries->create_field($data['field_id'], $createFieldLang);


                $this->application['message']['text'] = $this->translate('field_added_success');
                $this->application['message']['type'] = 'success';
            }
            if ($request['do'] == 'edit')
            {

                $fieldId = $request['field_id'];
                $where = $this->MC->db->quoteInto('field_id = ? ', $fieldId);
                $this->MC->db->update('items_fields', $data, $where);
                $data['field_id'] = $fieldId;
                $data['do'] = 'edit';

                foreach ($request['MC_key_lang'] as $lang_id => $lang_val)
                {
                    if (empty($lang_val['field_label'])){
                        continue;
                    }
                    $fieldLangQuery = $this->MC->db->select()->from('items_fields_lang');
                    $fieldLangQuery->where('lang_id = ?', $lang_id);
                    $fieldLangQuery->where('field_id = ?', $fieldId);

                    $langData = array();
                    $langData['field_id'] = $data['field_id'];
                    $langData['field_label'] = $lang_val['field_label'];
                    $langData['lang_id'] = $lang_id;

                    if ($this->MC->db->fetchRow($fieldLangQuery) == 0){
                        $this->MC->db->insert('items_fields_lang', $langData);
                    }else{
                        $where = $this->MC->db->quoteInto('field_id = ? AND ', $fieldId);
                        $where .= $this->MC->db->quoteInto('lang_id = ? ', $lang_id);
                        $this->MC->db->update('items_fields_lang', $langData, $where);
                    }
                }

                if ($data['multi_lang'] == 1){
                    $createFieldLang = true;
                }else{
                    $createFieldLang = false;
                }

                $this->MC->Queries->create_field($data['field_id'], $createFieldLang);

                $this->application['message']['text'] = $this->translate('field_saved_success');
                $this->application['message']['type'] = 'success';
            }
        }
        else
        {
            $data['fieldForm'] = $fieldForm;
            $data['do'] = $request['do'];
            if ($data['do'] == 'edit'){
                $data['field_id'] = $request['field_id'];
            }
            $this->application['message']['text'] = $this->translate('errors_occured');
            $this->application['message']['type'] = 'error';
        }

        $this->application = array_merge($this->application, $this->field($data));
        $this->application['window'] = 'field';
        return $this->application;
    }


    /**
     * @desc Folder Page
     * @method folder
     */
    public function folder($folderParams = array())
    {
        $do = (isset($folderParams['do']))?$folderParams['do']:$this->MC->Zend->getRequest()->getParam('do');

        $folderData = array();

        if($do == 'add'){
            $this->setNav($this->translate('add_new_folder'));
        }else if($do == 'edit'){
            $this->MC->load->model('language','lang');
            $folderId = (isset($folderParams['folderId']))?$folderParams['folderId']:$this->MC->Zend->getRequest()->getParam('folderId');
            $folderData = $this->MC->Queries->getFolderByFolderId($folderId);
            if(!$folderData){
                throw new  MC_Core_Exception('folder_not_found');
            }
            $this->setNav($this->translate('edit_folder'));
            $this->setNav($folderData['folder']['folder_lang'][$this->application['lang_id']]['folder_name']);
        }else{
           return $this->setError();
        }
        $folderData['folder']['do'] = $do;
        $this->assign('folderForm',((isset($folderParams['folderForm']))?$folderParams['folderForm']:$this->MC->Forms->folder($folderData)));
        return $this->application;
    }

    public function saveFolder()
    {
        $folderData = $this->MC->Zend->getRequest()->getPost('folder');
        $folderForm = $this->MC->Forms->folder($folderData);
        $folderParams = array();
        $folderParams['do'] = $folderData['do'];

        if($folderForm->isValid($folderData)){
            $itemApi = new App_Items_Shared_Api();
            $folder['folder_url'] = $folderData['folder_url'];
            $folder['folder_status'] = $folderData['folder_status'];
            if($folderData['do'] == 'edit'){
                $folder['folder_id'] = $folderData['folder_id'];
            }
            $folderArray['folder'] = $folder;
            $folderArray['folder_lang'] = array();

            foreach($folderData['folder_lang'] as $langId=>$folderLang)
            {
                if(empty($folderLang['folder_name'])){
                    continue;
                }
                $folderArray['folder_lang'][$langId] = $folderLang;
            }

            if(!$itemApi->saveFolder($folderArray)){
                $this->setMessage($itemApi->getSaveErrors(),'error');
                if($folderId = $itemApi->getFolderId() && $folderId !== false){
                    $folderParams['folderId'] = $itemApi->getFolderId();
                }
            }else{
                $folderParams['folderId'] = $itemApi->getFolderId();
                $this->replaceUrl($this->MC->Functions->folderUrl($folderParams['folderId']));
                $this->setMessage($this->translate('folder_saved'),'success');
                $folderParams['do'] = 'edit';
            }
        }else{
             $folderParams['folderForm'] = $folderForm;
        }
        $this->merge($this->folder($folderParams));
        $this->setView('folder');
        return $this->application;
    }
}