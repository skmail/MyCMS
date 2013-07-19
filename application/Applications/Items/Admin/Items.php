<?php
class App_Items_Admin_Items extends Admin_Model_ApplicationAbstract
{

    public $renderWindow = true;
    public $plugin = array();
    protected $query = null;

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

    #-------------------------------
    #  Index page - list folder
    #-------------------------------
    public function index()
    {
        $folders = $this->MC->Queries->folderQuery(array('lang_id'=>$this->application['lang_id']));

        $this->assign('folders',$folders);
        $this->setSidebar('indexSidebar');

        return $this->application;
    }

    #-------------------------------
    #  list categories
    #-------------------------------
    public function categories()
    {

        $folderId = intval($this->MC->Zend->getRequest()->getParam('folderId'));

        $folderData = $this->MC->Queries->folderQuery(array('folder_id'=>$folderId,'lang_id'=>$this->application['lang_id']));

        if(!$folderData)
        {
            throw new  MC_Core_Exception('folder_not_found');
        }

        $this->setNav($folderData['folder_name']);

        $categories = array();
        $categoriesList = $this->MC->Queries->categoriesTree(0);// $this->MC->Queries->categoryQuery(array('folder_id'=>$folderId,'lang_id'=>$this->application['lang_id'],'parent_id'=>0));

        $this->setSidebar('categoriesSidebar');
        $this->assign('folder',$folderData);
        $this->assign('categories',$categoriesList);
        return $this->application;
    }

    #-------------------------------
    #  List category items
    #-------------------------------
    public function items($cat_id = 0)
    {

        $cat_id = intval(($cat_id == 0) ? $this->MC->Zend->getRequest()->getParam('cat_id') : $cat_id);
        $itemStatus = intval($this->_Zend->getRequest()->getParam('status'));
        $catInfoArray = $this->MC->Queries->categoryQuery(array('cat_id'=>$cat_id));
        if (!$catInfoArray)
        {
            throw new MC_Core_Exception('not_found_category');
        }


        $folderData = $this->MC->Queries->folderQuery(array('folder_id'=>$catInfoArray['folder_id'],'lang_id'=>$this->application['lang_id']));

        if(!$folderData)
        {
            throw new  MC_Core_Exception('folder_not_found');
        }

        $this->setNav($folderData['folder_name'],'window/categories/folderId/'.$catInfoArray['folder_id']);


        $itemsStatuesCatsList = $this->MC->Functions->itemStatus();
        if (!array_key_exists($itemStatus, $itemsStatuesCatsList))
        {
            $itemStatus = 1;
        }
        $itemsList = $this->MC->Queries->itemQuery(array('cat_id'=>$cat_id,
                'lang_id'=>$this->application['lang_id'],
                'item_status' => $itemStatus
            )
        );

        $parentsInfo = $this->MC->Queries->categoryQuery(array('cats_id_in'=>$catInfoArray['parents'],'lang_id'=>$this->application['lang_id']));

        $parentsInfo = array_reverse($parentsInfo);
        foreach($parentsInfo as $parent)
        {
            $this->setNav($parent['cat_name'],'window/items/cat_id/' . $parent['cat_id']);
        }

        $this->setNav( $catInfoArray['cat_name']);



        $this->assign('itemsStatuesCats',$itemsStatuesCatsList);
        $this->assign('itemStatus',$itemStatus);
        $this->assign('itemsList',$itemsList);
        $this->assign('catInfo',$catInfoArray);
        $this->setSidebar('itemsSidebar');
        return $this->application;
    }

    #-------------------------------
    #  Item page add/edit
    #-------------------------------
    public function item($options = array())
    {
        $do = (isset($options['do'])) ? $options['do'] : $this->_Zend->getRequest()->getParam('do');
        if ($do != 'add' && $do != 'edit')
        {
            throw new MC_Core_Exception('invalid_request');
        }


        if ($do == 'add')
        {
            $cat_id = (isset($options['cat_id'])) ? $options['cat_id'] : intval($this->_Zend->getRequest()->getParam('cat_id'));
            $itemQuery = $this->MC->Queries->categoryQuery(array('cat_id'=>$cat_id));
            $nav = $this->translate('add_new_item');
            if (!$itemQuery)
            {
                return $this->setError();
            }
        }

        if ($do == 'edit')
        {
            $itemId = (isset($options['itemid'])) ? $options['itemid'] : intval($this->_Zend->getRequest()->getParam('itemid'));
            $itemQuery = $this->MC->Queries->itemQuery(array('item_id'=>$itemId));
            if (!$itemQuery)
            {
                return $this->setError();
            }
            $nav = $this->translate('edit_item') . " " . $itemQuery['item_lang'][$this->application['lang_id']]['item_title'];
            $this->assign($categories,true);
            $itemQuery = array_merge($itemQuery,$this->MC->Queries->categoryQuery(array('cat_id'=>$itemQuery['cat_id'])));
        }



        $folderData = $this->MC->Queries->folderQuery(array('folder_id'=>$itemQuery['folder_id'],'lang_id'=>$this->application['lang_id']));

        if(!$folderData)
        {
            throw new  MC_Core_Exception('folder_not_found');
        }

        $this->setNav($folderData['folder_name'],'window/categories/folderId/'.$catInfoArray['folder_id']);



        $parentsInfo = $this->MC->Queries->categoryQuery(array('cats_id_in'=>$itemQuery['parents'],'lang_id'=>$this->application['lang_id']));
        $parentsInfo = array_reverse($parentsInfo);
        foreach($parentsInfo as $parent)
        {
            $this->setNav($parent['cat_name'],'window/items/cat_id/' . $parent['cat_id']);
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
        if ($do != "edit" && $do != "add")
        {
            return $this->setError();
        }
        $cat_id = (isset($options['cat_id'])) ? $options['cat_id'] : intval($this->_Zend->getRequest()->getParam('cat_id'));
        if ($do == 'edit')
        {
            if ($cat_id == 0)
            {
                return $this->setError();
            }
            $catQuery = $this->MC->Queries->categoryQuery(array('cat_id'=>$cat_id));

            if (!$catQuery)
            {
                return $this->setError();
            }

            $folderData = $this->MC->Queries->folderQuery(array('folder_id'=>$catQuery['folder_id'],'lang_id'=>$this->application['lang_id']));

            if(!$folderData)
            {
                throw new  MC_Core_Exception('folder_not_found');
            }

            $this->setNav($folderData['folder_name'],'window/categories/folderId/'.$catQuery['folder_id']);

            $parentsInfo = $this->MC->Queries->categoryQuery(array('cats_id_in'=>$catQuery['parents'],'lang_id'=>$this->application['lang_id']));

            $parentsInfo = array_reverse($parentsInfo);
            foreach($parentsInfo as $parent)
            {
                $this->setNav($parent['cat_name'],'window/items/cat_id/' . $parent['cat_id']);
            }
        }
        else
        {
            if ($cat_id != 0)
            {
                $catQuery = $this->MC->Queries->categoryQuery(array('cat_id'=>$cat_id));
                if (!$catQuery)
                {
                    return $this->setError();
                }
                $catQuery['parent_id'] = $cat_id;
            }
        }

        $this->setNav($catQuery['cat_name']);
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

        if ($data['do'] != "edit" && $data['do'] != "add")
        {
            return $this->setError();
        }

        $itemForm = $this->MC->Forms->itemForm($request->getPost());


        if ($itemForm->isValid($request->getPost()))
        {

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
                if(empty($item_lang['item_title']))
                {
                    continue;
                }

                $itemLang[$langId]['lang_id'] = $langId;
                $itemLang[$langId]['item_title'] = $item_lang['item_title'];
                $itemLang[$langId]['item_content'] = $item_lang['item_content'];

                if(isset($item_lang['fields_lang']))
                {
                    $langFields[$langId] = $item_lang['fields_lang'];
                }
            }

            //-------------------------------------------
            //  Prepare item fields
            //-------------------------------------------

            $itemFields = array();

            if(isset($data['fields']))
            {
                $itemFields = $data['fields'];
            }

            $data = array();
            $data['item'] = $item;
            $data['item_langs'] = $itemLang;
            $data['fields'] = $itemFields;
            $data['fields_lang'] = $langFields;

            if($result = $itemApi->validSave($itemApi->saveItem($data)))
            {

                $itemOptions['do'] = 'edit';
                $itemOptions['itemid'] = $itemApi->getItemId();
                $this->setMessage('item_saved_succesfully','success');
                $this->replaceUrl($this->MC->Functions->itemUrl($itemOptions['itemid']));

                if(count($itemApi->getSaveErrors()) > 0)
                {
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

        if ($do == 'edit')
        {
            $cat_id = intval($request->getPost('cat_id'));

            if (!$this->MC->Queries->categoryQuery($cat_id))
            {
                return $this->setError();
            }
        }

        $catForm = $this->MC->Forms->categoryForm();
        if ($catForm->isValid($request->getPost()))
        {
            $category['cat_url']        = MC_Models_Url::friendly($data['category']['cat_url']);
            $category['cat_status']     = $data['category']['cat_status'];
            $category['parent_id']      = $data['category']['parent_id'];
            $category_lang['lang_id']   = $this->application['lang_id'];
            $category['cat_params']     = Zend_Json::encode($data['params']);

            $cat_lang = $data['category_lang']['cat_lang'];

            foreach ($cat_lang as $lang_id => $catLang)
            {
                if (empty($catLang['cat_name']) && trim($catLang['cat_name']) == "")
                {
                    unset($cat_lang[$lang_id]);
                }
            }

            if ($do == 'edit')
            {
                $cat_id = $data['cat_id'];
                $this->db->update('items_categories', $category, ' cat_id = ' . $cat_id);

                $where = $this->db->quoteInto("cat_id = ?", $cat_id);
                $this->db->delete('items_categories_lang', $where);

                $this->setMessage($this->translate('category_saved_success'),'success');
            }
            else
            {
                $this->db->insert('items_categories', $category);
                $cat_id = $category_lang['cat_id'] = $this->db->lastInsertId();

                $this->setMessage($this->translate('category_added_success'),'success');

                $do = 'edit';
            }

            if(empty($category['cat_url']))
            {
                $this->db->update('items_categories', array('cat_url'=>'cat-'.$cat_id), ' cat_id = ' . $cat_id);
            }

            foreach ($cat_lang as $lang_id => $cat)
            {
                $cat['lang_id'] = $lang_id;
                $cat['cat_id'] = $cat_id;
                $this->db->insert('items_categories_lang', $cat);
            }


            $catsChildsParents = $this->MC->Queries->buildCategoiesRelations();

            foreach($catsChildsParents['childs'] as $catid => $childs)
            {
                $where = $this->db->quoteInto("cat_id = ?", $catid);
                $this->db->update('items_categories',array('childs'=>implode(',',$childs)),$where);
            }

            foreach($catsChildsParents['parents'] as $catid => $parents)
            {
                $where = $this->db->quoteInto("cat_id = ?", $catid);
                $this->db->update('items_categories',array('parents'=>implode(',',$parents)),$where);
            }

            $this->replaceUrl($this->MC->Functions->catUrl($cat_id));
            $catOptions['do'] = $do;

            $catOptions['cat_id'] = $cat_id;
        }
        else
        {
            $this->setMessage($this->translate('some_fields_empty'),'error');
            $catOptions['do'] = $do;
            $catOptions['categoryForm'] = $catForm;
        }

        $category = $this->category($catOptions);


        $this->merge($category);
        $this->setView('category');

        return $this->application;

    }

    public function deleteCat()
    {
        $multiDelete = false;

        $cat_id = intval($this->_Zend->getRequest()->getParam('cat_id'));

        if ($cat_id == 0)
        {
            $cat_id = $this->_Zend->getRequest()->getPost('cat');

            if (!is_array($cat_id))
            {
                $cats = array();
            }
            elseif (count($cat_id) == 0)
            {
                $cats = array();
            }
            else
            {
                $cats = $cat_id;
            }
        }
        else
        {
            $cats = array($cat_id);
        }
        foreach ($cats as $cat_id)
        {
            $getCat = $this->MC->Queries->categoryQuery(array('cat_id'=>$cat_id));

            if (!$getCat)
            {
                continue;
            }

            $where = $this->db->quoteInto('cat_id = ? ', $cat_id);

            $this->db->delete('items_categories', $where);

            $this->db->delete('items_categories_lang', $where);

            //Delete Category Items
            $this->db->query('delete items,items_lang from items,items_lang where items.cat_id = ' . $cat_id . ' AND items_lang.item_id = items.item_id');
        }
        if (count($cats) == 1)
        {
            $this->setMessage($this->translate('category_was_deleted'),'success');
        }
        elseif (count($cats) > 1)
        {
            $this->setMessage($this->translate('categories_was_deleted'),'success');
        }
        else
        {
            $this->setMessage($this->translate('no_categorie_seleted_to_delete'),'error');
        }

        $this->assign('breadcrumb',false);
        $this->setView('index');
        $this->merge($this->index());
        return $this->application;
    }

    public function deleteItem()
    {
        // -------------------------------
        // Under construction
        // -------------------------------

        return $this->setError();

        $itemId = $this->_Zend->getRequest()->getParam('itemid');
        if ($itemId == 0)
        {
            $itemId = $this->_Zend->getRequest()->getPost('item');
            if (!is_array($itemId))
            {
                $cats = array();
            }
            elseif (count($itemId) == 0)
            {
                $cats = array();
            }
            else
            {
                $cats = $itemId;
            }
        }
        else
        {
            $cats = $itemId;
        }

        $delete = $this->MC->Queries->deleteItems($cats);

        if ($delete['errors'] > 0)
        {
            $this->setMessage($this->translate('some_errors_occured_when_deleting_items'),'error');
        }
        if ($delete['success'] == 1)
        {
            $this->application['message']['text'] = '';
            $this->application['message']['type'] = 'success';
        }
        elseif ($delete['success'] > 1)
        {
            $this->application['message']['text'] = $delete['success'] . ' Items Deleted';
            $this->application['message']['type'] = 'success';
        }
        elseif ($delete['success'] == 0)
        {
            $this->application['message']['text'] = 'No items selected';
            $this->application['message']['type'] = 'error';
        }

        $this->application['windowRender'] = true;

        $this->application['breadcrumb'] = false;

        $this->application['windowView'] = 'items.phtml';

        $this->application = array_merge($this->items($itemRow['cat_id']), $this->application);

        return $this->application;

    }

    public function dataGraber($options = array())
    {
        $this->application['nav']->append(Zend_Registry::get('Zend_Translate')->translate('Data graber'), 'window/dataGraber');


        $this->application['dataGraberForm'] = (isset($options['dataGraberForm'])) ? $options['dataGraberForm'] : $this->MC->Forms->dataGraber();

        return $this->application;

    }

    public function doDataGraber()
    {

        $dataGraber = new App_Items_Admin_DataGraber();

        $results = array();

        $dataGraberVars = array();
        $dataGraberVars['dataGraberForm'] = $this->MC->Forms->dataGraber();
        $request = $this->_Zend->getRequest()->getPost();

        if ($dataGraberVars['dataGraberForm']->isValid($request))
        {

            $search = $request['search'];
            $error = true;

            if (!is_array($search) && $request['preset'] == '')
            {
                $this->application['message']['text'] = 'Please set at least one tag to search';
                $this->application['message']['type'] = 'error';
            }
            else
            {
                if (!count($search) && $request['preset'] == '')
                {

                    $this->application['message']['text'] = 'Please set at least one tag to search';
                    $this->application['message']['type'] = 'error';
                }
                else
                {
                    $error = false;
                }
            }


            if ($error == false)
            {
                if (count($search) && $request['preset'] == '')
                {
                    foreach ($search as $name => $pattern)
                    {

                        $dataGraber->search($name, $pattern);
                    }
                }
                else if ($request['preset'] != '')
                {
                    $dataGraber->setPreset($request['preset']);
                }
                else
                {
                    $this->application['message']['text'] = 'Please set at least one tag to search';
                    $this->application['message']['type'] = 'error';
                }


                $results = $dataGraber->getData($request['site_item_url']);


                if ($errors = $dataGraber->errors())
                {
                    $resultErrors = implode('<br/>', $errors);
                    $this->application['message']['text'] = $resultErrors;
                    $this->application['message']['type'] = 'error';
                }
            }


            $dataGraberVars['dataGraberForm'] = $this->MC->Forms->dataGraber($request, $results);
        }
        else
        {

        }

        $this->application = array_merge($this->application, $this->dataGraber($dataGraberVars));

        $this->application['window'] = 'dataGraber.phtml';

        return $this->application;

    }


    public function fields()
    {

        $folderId = intval($this->_Zend->getRequest()->getParam('folderId'));

        $folderData = $this->MC->Queries->folderQuery(array('folder_id'=>$folderId,'lang_id'=>$this->application['lang_id']));

        if (!$folderData)
        {
            throw new MC_Core_Exception('not_found_folder');
        }


        $this->setNav($this->translate('fields'));
        $this->setNav($folderData['folder_name']);

        $this->application['folder'] = $folderData;

        $this->application['fields'] = $this->MC->Queries->listFields($folderId);

        $this->application['sidebar'] = 'fieldsSidebar';
        return $this->application;
    }

    public function field($options = array())
    {

        $do = ($options['do']) ? $options['do'] : $this->_Zend->getRequest()->getParam('do');

        if ($do != "add" && $do != "edit")
        {
            return;
        }

        if ($do == 'add')
        {
            $folderId = intval($this->_Zend->getRequest()->getParam('folderId'));

            $folderData = $this->MC->Queries->folderQuery(array('folder_id'=>$folderId,'lang_id'=>$this->application['lang_id']));

            if (!$folderData)
            {
                throw new MC_Core_Exception('not_found_folder');
            }
            $this->setNav($this->translate('fields'));
            $this->setNav($folderData['folder_name']);
            $this->setNav($this->translate('add_new_field'));

            $data['folder_id'] = $folderData['folder_id'];
            $data['do'] = 'add';
        }
        if ($do == 'edit')
        {
            $field_id = (isset($options['field_id'])) ? $options['field_id'] : intval($this->_Zend->getRequest()->getParam('field_id'));
            $field = $this->MC->Queries->getField($field_id);

            if (!$field)
            {
                throw new MC_Core_Exception('not_found_field');
            }


            $folderData = $this->MC->Queries->folderQuery(array('folder_id'=>$field['folder_id'],'lang_id'=>$this->application['lang_id']));

            if (!$folderData)
            {
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
        if ($request['do'] != "add" && $request['do'] != "edit")
        {
            return;
        }
        $fieldForm = $this->MC->Forms->field();
        if ($fieldForm->isValid($request))
        {

            $data['field_name'] = $request['field_name'];
            $data['field_type'] = $request['field_type'];
            $data['multi_lang'] = $request['multi_lang'];
            $data['folder_id'] = $request['folder_id'];


            if ($request['do'] == 'add')
            {
                $this->db->insert('items_fields', $data);
                $data['field_id'] = $this->db->lastInsertId();
                foreach ($request['MC_key_lang'] as $lang_id => $lang_val)
                {
                    if (empty($lang_val['field_label']))
                    {
                        continue;
                    }
                    $langData = array();
                    $langData['field_id'] = $data['field_id'];
                    $langData['field_label'] = $lang_val['field_label'];
                    $langData['lang_id'] = $lang_id;
                    $this->db->insert('items_fields_lang', $langData);
                }
                $data['do'] = 'edit';

                $this->application['message']['text'] = $this->translate('field_added_success');
                $this->application['message']['type'] = 'success';
            }
            if ($request['do'] == 'edit')
            {

                $fieldId = $request['field_id'];
                $where = $this->db->quoteInto('field_id = ? ', $fieldId);
                $this->db->update('items_fields', $data, $where);
                $data['field_id'] = $fieldId;
                $data['do'] = 'edit';
                foreach ($request['MC_key_lang'] as $lang_id => $lang_val)
                {
                    if (empty($lang_val['field_label']))
                    {
                        continue;
                    }
                    $fieldLangQuery = $this->db->select()->from('items_fields_lang');
                    $fieldLangQuery->where('lang_id = ?', $lang_id);
                    $fieldLangQuery->where('field_id = ?', $fieldId);

                    $langData = array();
                    $langData['field_id'] = $data['field_id'];
                    $langData['field_label'] = $lang_val['field_label'];
                    $langData['lang_id'] = $lang_id;

                    if ($this->db->fetchRow($fieldLangQuery) == 0)
                    {
                        $this->db->insert('items_fields_lang', $langData);
                    }
                    else
                    {
                        $where = $this->db->quoteInto('field_id = ? AND ', $fieldId);
                        $where .= $this->db->quoteInto('lang_id = ? ', $lang_id);
                        $this->db->update('items_fields_lang', $langData, $where);
                    }
                }

                if ($data['multi_lang'] == 1)
                {
                    $createFieldLang = true;
                }
                else
                {
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
            if ($data['do'] == 'edit')
            {
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

        if($do == 'add')
        {
            $this->setNav($this->translate('add_new_folder'));
        }
        else if($do == 'edit')
        {
            $this->MC->load->model('language','lang');
            $folderId = (isset($folderParams['folderId']))?$folderParams['folderId']:$this->MC->Zend->getRequest()->getParam('folderId');
            $folderData = $this->MC->Queries->folderQuery(array('folder_id'=>$folderId));

            if(!$folderData)
            {
                throw new  MC_Core_Exception('folder_not_found');
            }

            $this->setNav($this->translate('edit_folder'));
            $this->setNav($folderData['folder']['folder_lang'][$this->application['lang_id']]['folder_name']);

        }else
        {
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

        $folderParams['do'] = $folderData['folder']['do'];

        if($folderForm->isValid($folderData))
        {
            $itemApi = new App_Items_Shared_Api();

            $folder['folder_url'] = $folderData['folder_url'];
            $folder['folder_status'] = $folderData['folder_status'];
            if($folderData['do'] == 'edit')
            {
                $folder['folder_id'] = $folderData['folder_id'];
            }
            $folderArray['folder'] = $folder;
            $folderArray['folder_lang'] = array();

            foreach($folderData['folder_lang'] as $langId=>$folderLang)
            {
                if(empty($folderLang['folder_name']))
                {
                    continue;
                }
                $folderArray['folder_lang'][$langId] = $folderLang;
            }

            if(!$itemApi->saveFolder($folderArray))
            {
                $this->setMessage($itemApi->getSaveErrors(),'error');
                if($folderId = $itemApi->getFolderId() && $folderId !== false)
                {
                    $folderParams['folderId'] = $itemApi->getFolderId();
                }
            }
            else
            {
                $folderParams['folderId'] = $itemApi->getFolderId();
                $this->replaceUrl($this->MC->Functions->folderUrl($folderParams['folderId']));
                $this->setMessage($this->translate('folder_saved'),'success');
                $folderParams['do'] = 'edit';
            }
        }
        else
        {
             $folderParams['folderForm'] = $folderForm;
        }

        $this->merge($this->folder($folderParams));
        $this->setView('folder');
        return $this->application;
    }
}