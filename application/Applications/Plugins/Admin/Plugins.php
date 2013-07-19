<?php

class App_Plugins_Admin_Plugins extends Admin_Model_ApplicationAbstract
{

    public $render = true;

    private $_do = array('add', 'edit', 'delete','duplicate');

    public function __construct($application = array())
    {

        parent::__construct($application);

        $this->menu = array(
            array(
                'title' => Zend_Registry::get('Zend_Translate')->translate('Add new row'),
                'url'   => 'window/grid/do/add',
            ),
            array(
                'title' => Zend_Registry::get('Zend_Translate')->translate('Add new Group'),
                'url'   => $this->application['url'] . 'window/group/do/add'
            ),
            array(
                'title' => Zend_Registry::get('Zend_Translate')->translate('Upload New Plugin'),
                'url'   => 'window/install'
            )
        );

        $this->application['renderWindow'] = $this->renderWindow;

        $this->Functions = MC_Core_Loader::appClass('plugins','Functions',$application,'Admin');
        
        $this->_form = MC_Core_Loader::appClass('plugins','Forms',$application,'Admin');
        
        $this->_query = MC_Core_Loader::appClass('plugins','Queries',$application,'Admin') ;

        $this->assign('menubar',$menubar);

        $this->setNav($this->translate('plugins'),'window/index');
    }

    public function index()
    {

        $gridList = $this->_query->gridQuery(array('lang_id'=>$this->application['lang_id']));

        $this->assign('grids',$gridList);

        $this->setSidebar('indexSidebar');

        return $this->application;
    }

    public function groups($options = array())
    {

        $request = $this->_Zend->getRequest();

        $gridId = ($options['groupid'])?$options['groupid']:intval($request->getParam('gridid'));
        
        $gridQuery = $this->_query->gridQuery(array('grid_id'=>$gridId,'lang_id'=>$this->application['lang_id']));

        if (!$gridQuery)
        {
            return $this->setError();
        }

        $this->assign('grid',$gridQuery);

        $this->setNav($gridQuery['grid_name']);

        $this->assign('groups',$this->_query->groupQuery(array('grid_id'=>$gridId,'lang_id'=>$this->application['lang_id'])));

        $this->setSidebar('groupsSidebar');

        return $this->application;

    }

    public function grid($options = array())
    {

        $request = $this->_Zend->getRequest();

        $do = isset($options['do'])?$options['do']: $request->getParam('do');

        if (!in_array($do, $this->_do))
        {
            return $this->setError();
        }
        
        if ($do == 'edit' || $do == 'duplicate')
        {
            $gridId = intval(isset($options['gridid'])?$options['gridid']:$request->getParam('gridid'));

            $grid = $this->_query->gridQuery(array('grid_id'=>$gridId));

            if (!$grid)
            {
                return $this->setError();
            }

            $this->setNav($this->translate('edit_grid'). ": " . $grid['grid_name']);
        }

        if ($do == 'add')
        {
            $grid['theme_id'] = MC_App_Themes_Themes::currentTheme();
            $this->setNav($this->translate('add_grid'));

        }

        $grid['do'] = $do;

        $this->application['gridForm'] = isset($options['gridForm'])?$options['gridForm']:$this->_form->gridForm($grid);

        return $this->application;

    }

    public function saveGrid()
    {

        $request = $this->_Zend->getRequest();

        $data = $request->getPost();

        if (!in_array($data['do'], $this->_do))
        {
            return $this->setError();
        }


        $gridForm = $this->_form->gridForm($data);

        if ($gridForm->isValid($data))
        {
            $gridLang = $data['grid_lang'];

            foreach ($gridLang as $lang_id => $grid_lang)
            {
                if (empty($grid_lang['grid_name']))
                {
                    unset($gridLang[$lang_id]);
                }
            }

            $grid['grid_params'] = Zend_Json::encode($data['params']);

            $grid['theme_id'] = $data['theme_id'];

            $grid['grid_status'] = $data['grid_status'];
            
            if ($data['do'] == 'add' || $data['do'] == 'duplicate')
            {

                $this->db->insert('grid', $grid);

                $gridId = $this->db->lastInsertId();

                $this->application['message']['text'] = 'Grid added succesfully';

                $this->application['message']['type'] = 'success';
                
                $gridData['do'] = 'edit';
                
                $gridData['gridid'] = $gridId;
            }

            if ($data['do'] == 'edit')
            {
                $gridId = intval($data['grid_id']);

                $where = $this->db->quoteInto("grid_id = ? ", $gridId);

                $this->db->update('grid', $grid, $where);

                $this->db->delete('grid_lang', $where);

                $this->application['message']['text'] = 'Grid saved succesfully';

                $this->application['message']['type'] = 'success';
                
                $gridData['gridid'] = $gridId;

            }

            foreach ($gridLang as $lang_id => $gridLangData)
            {

                $gridLangData['lang_id'] = $lang_id;

                $gridLangData['grid_id'] = $gridId;

                $this->db->insert("grid_lang", $gridLangData);
            }

            $gridQuery = $this->_query->gridQuery($gridId, true, false);

            $gridQuery['do'] = 'edit';

            $this->assign('replaceUrl',$this->Functions->gridUrl($gridId));
        }
        else
        {
            $this->setMessage('Some Fields empty','error');

            $gridData['do'] = $data['do'];
                
            if($data['do'] == 'edit')
            {
                $gridData['gridid'] = intval($data['grid_id']);    
            }
            
            $gridData['gridForm'] = $gridForm;
                
        }

        $this->application = array_merge($this->application,$this->grid($gridData));

        $this->setView('grid') ;
        
        return $this->application;

    }

    public function plugin($options = array())
    {
        $request = $this->_Zend->getRequest();

        $do = (isset($options['do']))?$options['do']:$request->getParam('do');

        $pluginId = intval((isset($options['pluginid']))?$options['pluginid']:$request->getParam('pluginid'));

        if ($do == 'add')
        {
            $pluginResource = $this->pluginResource($pluginId);

            $groupId = intval((isset($options['groupid']))?$options['groupid']:$request->getParam('groupid'));

            if ($groupId != 0)
            {
                $pluginResource['group_id'] = $groupId;
            }

            $pluginName = $pluginResource['plugin_name'];

            $groupQuery = $this->_query->groupQuery(array('group_id'=>$pluginResource['group_id'],'lang_id'=>$this->application['lang_id']));

            $gridQuery = $this->_query->gridQuery(array('grid_id'=>$groupQuery['grid_id'],'lang_id'=>$this->application['lang_id']));


            $this->setNav($gridQuery['grid_name'],'window/groups/gridid/' . $gridQuery['grid_id']);

            $this->setNav($groupQuery['group_name'],'window/plugins/groupid/' . $groupQuery['group_id']);

            $this->setNav($this->translate('add_plugin') . ": ".$pluginResource['plugin_resource_name']);

            $groupQuery = array_merge($groupQuery,$gridQuery);
            $pluginResource = array_merge($pluginResource,$groupQuery);

            unset($pluginResource['plugin_name']);

        }

        if ($do == 'edit')
        {
            $pluginRow = $this->_query->pluginQuery($pluginId, 0, true, false);

            $params = $pluginRow['plugin_params'];
            
            foreach($pluginRow['plugin_lang'] as $lang_id=>$pluginLangParams){
                $params['lang_params'][$lang_id] = json_decode($pluginLangParams['lang_params'],true);
            }
            
            $pluginRow['application'] = $this->getApplicationShowIn($pluginId);

            $pluginResource = $this->pluginResource($pluginRow['plugin_resource_id']);


            $pluginResource = array_merge($pluginResource, $pluginRow);
            
            $pluginResource['params'] = $params;

            $groupQuery = $this->_query->groupQuery(array('group_id'=>$pluginResource['group_id'],'lang_id'=>$this->application['lang_id']));

            $gridQuery = $this->_query->gridQuery(array('grid_id'=>$groupQuery['grid_id'],'lang_id'=>$this->application['lang_id']));

            $this->setNav($gridQuery['grid_name'],'window/groups/gridid/' . $gridQuery['grid_id']);

            $this->setNav($groupQuery['group_name'],'window/plugins/groupid/' . $groupQuery['group_id']);

            $this->setNav($this->translate('edit_plugin') . ": ".$pluginResource['plugin_name']);

        }

        $pluginName = (isset($pluginResource['plugin_name'])) ? $pluginResource['plugin_resource_name'] : $pluginName;

        $pluginResource['do'] = $do;

        $pluginForm = $this->_form->pluginForm($pluginResource);

        $this->assign('pluginForm',(!empty($options['pluginForm']))?$options['pluginForm']:$pluginForm);

        return $this->application;

    }

    public function Plugins($options = array())
    {

        $this->application['group_id'] = $groupId = isset($options['group_id'])?$options['group_id']: $this->_Zend->getRequest()->getParam('groupid');

        $groupId = intval($groupId);

        $groupRow = $this->_query->groupQuery(array('group_id'=>$groupId,'lang_id'=>$this->application['lang_id']));


        $pluginQuery = $this->_query->pluginQuery(0, $groupId);

        $gridQuery = $this->_query->gridQuery(array('grid_id'=>$groupRow['grid_id'],'lang_id'=>$this->application['lang_id']));

        $this->assign('plugins',$pluginQuery);

        $this->assign('listPluginsResource',$this->listPluginsResource());

        $this->assign('group',$groupRow);

        $this->setNav($gridQuery['grid_name'], 'window/groups/gridid/' . $gridQuery['grid_id']);

        $this->setNav($groupRow['group_name']);

        $this->setSidebar('pluginsSidebar');

        return $this->application;
    }

    public function group($options = array())
    {

        $do = (isset($options['do']))?$options['do']:$this->_Zend->getRequest()->getParam('do');

        if ($do != "add" && $do != 'edit')
        {
            return $this->setError();
        }


        if ($do == 'edit')
        {
            $groupId = isset($options['groupid'])?$options['groupid']:$this->_Zend->getRequest()->getParam('groupid');

            $groupQuery = $this->_query->groupQuery(array('group_id'=>$groupId));

            if (!$groupQuery)
            {
                return $this->setError();
            }


            $groupQuery['do'] = 'edit';

            $gridQuery = $this->_query->gridQuery(array('grid_id'=>$groupQuery['grid_id'],'lang_id'=>$this->application['lang_id']));

        }
        else
        {
            $gridId = isset($options['gridid'])?$options['gridid']:$this->_Zend->getRequest()->getParam('gridid');

            $groupId = isset($options['groupid'])?$options['groupid']:$this->_Zend->getRequest()->getParam('groupid');

            if(intval($gridId) != 0)
            {
                $gridQuery = $this->_query->gridQuery(array('grid_id'=>$gridId,'lang_id'=>$this->application['lang_id']));

                if (!$gridQuery)
                {
                    return $this->setError();
                }
                $groupQuery = $gridQuery;
                $groupQuery['group']['grid_id'] = $gridId;
            }else
            {

                $parentGroupRow = $this->_query->groupQuery(array('group_id'=>$groupId));

                if(!$parentGroupRow)
                {
                    return $this->setError();
                }
                $groupQuery['parent_group'] = $groupId;
            }
            $groupQuery['do'] = 'add';
        }

        $windowName = ($do == 'add') ? 'Add Group' : "Edit Group : " . $groupQuery['group_name'];

        $this->application['windowName'] = $windowName;

        $this->setNav($gridQuery['grid_name'], 'window/groups/gridid/' . $gridQuery['grid_id']);

        $this->setNav($this->application['windowName']);

        $this->assign('windowName','Plugins Group');

        $this->assign('groupForm',isset($options['groupForm'])?$options['groupForm']:$this->_form->groupForm($groupQuery));

        return $this->application;

    }


   
    private function pluginResource($pluginId = 0)
    {

        $resourcesQuery = $this->db->select()->from('plugins_resources');

        if ($pluginId != 0)
        {
            $resourcesQuery->where('plugin_resource_id = ? ', $pluginId);

            $row = $this->db->fetchRow($resourcesQuery);


        }else
        {
            $row = $this->db->fetchAll($resourcesQuery);
        }

        return $row;

    }

    public function listPluginsResource()
    {

        $this->view()->pluginsResource = $this->pluginResource();

        $groupId = intval($this->_Zend->getRequest()->getParam('groupid'));

        if ($groupId != 0)
        {
            $this->view()->groupid = "/groupid/" . $groupId . '/';
        }

        $this->view()->app = $this->application;

        return $this->view()->render('pluginsResourcesList.phtml');
    }

    public function saveGroup()
    {
        $request = $this->_Zend->getRequest();
        $data = $request->getPost();
        $error = false;
        if ($data['do'] != 'add' && $data['do'] != 'edit')
        {
            return;
        }

        $do = $data['do'];

        if ($do != "add" && $do != 'edit')
        {
            return;
        }


        if ($do == 'edit')
        {

            $groupId = $data['group_id'];

            $groupQuery = $this->_query->groupQuery(array('group_id'=>$groupId));

            if (!$groupQuery)
            {
                return;
            }

            $groupQuery['do'] = 'edit';
        }
        else
        {

            $groupQuery = $this->_query->gridQuery(array('grid_id'=>$data['group']['grid_id'],'lang_id'=>$this->application['lang_id']));

            $groupQuery['do'] = 'add';
        }

        $groupForm = $this->_form->groupForm($groupQuery);

        if ($groupForm->isValid($data))
        {

            $group = $data['group'];

            $group['group_params'] = Zend_Json::encode($group['group_params']);

            $groupLangs = $data['group_lang'];

            if ($do == 'add')
            {
                $this->db->insert('plugins_groups', $group);

                $groupId = $this->db->lastInsertId();

                $this->application['message']['text'] = 'Group data Added Succefull';

                $this->application['message']['type'] = 'success';
                
                $groupData['do'] = 'edit';
                
                $groupData['groupid'] = $groupId;
                
            }
            else
            {
                $groupId = $data['group_id'];

                $groupWhere = $this->db->quoteInto('group_id = ? ', $groupId);

                $this->db->update('plugins_groups', $group, $groupWhere);

                $this->db->delete('plugins_groups_lang', $groupWhere);

                $this->application['message']['text'] = 'Group data saved Succefull';

                $this->application['message']['type'] = 'success';
                
                $groupData['groupid'] = $groupId;
            }


            foreach ($groupLangs as $lang_id => $groupLang)
            {
                if (empty($groupLang['group_name']) && trim($groupLang['cat_name']) == "")
                {
                    continue;
                }

                $groupLang['lang_id'] = $lang_id;

                $groupLang['group_id'] = $groupId;

                $this->db->insert('plugins_groups_lang', $groupLang);
            }

            $groupQuery = $this->_query->groupQuery(array('group_id'=>$groupId));

            $groupForm = $this->_form->groupForm($groupQuery);
            
            $this->application['replaceUrl'] = $this->Functions->groupUrl($groupId);
        }
        else
        {
            $this->application['message']['text'] = 'Some Fields empty';

            $this->application['message']['type'] = 'error';
           
            $groupData['groupForm'] = $groupForm;

            $groupData['do'] = $data['do'];
            
            if($data['do'] == 'edit')
            {
                $groupData['groupid'] = $data['group_id'];
            }else
            {
                $groupData['gridid'] = $data['group']['grid_id'];
            }            
        }

        $this->application = array_merge($this->application,$this->group($groupData));
        
        $this->application['window'] = 'group.phtml';

        return $this->application;

    }

    public function savePlugin()
    {

        $request = $this->_Zend->getRequest();
        
        $data = $request->getPost();
        
        $do = $data['do'];

        // get plugin resource settings
        $pluginResource = $this->pluginResource($data['plugin']['plugin_resource_id']);

        $groupQuery = $this->_query->groupQuery(array('group_id'=>$data['plugin']['group_id'],'lang_id'=>$this->application['lang_id']));

        $pluginResource = array_merge($pluginResource,$groupQuery);

        //get form to make validation
        $pluginForm = $this->_form->pluginForm($pluginResource);

        //Validation form process
        if ($pluginForm->isValid($request->getPost()))
        {

            //Plugin params my will shape to another format
            if (method_exists($this->subForm, 'process'))
            {
                $data['params'] = $this->subForm->process($data['params']);
            }

            //Extracting $data indexs to variables
            extract($data);
            $params = $data['params'];
            $plugin = $data['plugin'];
            $plugin_lang = $data['plugin_lang'];
            $application = $data['application'];


            //Move lang params to new varialbe
            if (isset($params['lang_params']))
            {
                $langParams = $params['lang_params'];
                unset($params['lang_params']);
            }

            $plugin['plugin_params'] = Zend_Json::encode($params);

            //Add plugin Process
            if ($do == 'add')
            {

                //Add plugin Settings
                $this->db->insert('plugins', $plugin);

                $pluginId = $this->db->lastInsertId();

                //Passing Success Message
                $this->application['message']['text'] = 'Plugin Added';

                $this->application['message']['type'] = 'success';

                //set the url to replace in address bar
                $this->application['replaceUrl'] = $this->Functions->PluginUrl($pluginId);
            }

            if ($do == 'edit')
            {
                $pluginId = $plugin_id;

                $wherePlugin = $this->db->quoteInto('plugin_id = ? ', $pluginId);

                //Update plugin settings
                $this->db->update('plugins', $plugin, $wherePlugin);

                //Delete plugin language
                $this->db->delete('plugins_lang', $wherePlugin);

                //Delete Application From Languages
                $this->db->delete('plugins_applications', $wherePlugin);

                //Passing Success Message
                $this->application['message']['text'] = 'Plugin Saved';

                $this->application['message']['type'] = 'success';
            }

            //Set the application segments  that will contain the plugin

            //implode the application pages will Contain plugin and save it

            if (isset($application))
            {
                if (is_array($application))
                {
                    $this->db->delete('plugins_applications',$this->db->quoteInto('plugin_id = ? ', $pluginId));

                    foreach ($application as $appId => $pages)
                    {
                        foreach($pages as $pageKey=>$pagesList)
                        {
                            $applicationData['plugin_id'] = $pluginId;

                            $applicationData['application_id'] = $appId;

                            $applicationData['page_key'] = $pageKey;

                            $applicationData['page_value'] = implode(',',$pagesList);

                            $this->db->insert('plugins_applications', $applicationData);
                        }
                    }
                }
            }

            //Save plugin language settings

            foreach ($plugin_lang as $lang_id => $pluginLang)
            {
                if (empty($pluginLang['plugin_name']))
                {
                    continue;
                }

                $pluginLang['lang_id'] = $lang_id;

                $pluginLang['plugin_id'] = $pluginId;

                $pluginLang['lang_params'] = Zend_Json::encode($langParams[$lang_id]);

                $this->db->insert('plugins_lang', $pluginLang);
            }

            $options = array('pluginid'=>$pluginId,'do'=>'edit');
            
        }
        else
        {
            if($do == 'edit')
            {
                $options['pluginid'] = $data['plugin_id'];
            }else{
                $options['pluginid'] = $data['plugin']['plugin_resource_id'];
            }
            $options['pluginForm'] = $pluginForm;
            //Validation Errors
            $this->application['message']['text'] = 'Error in submit form';
            $this->application['message']['type'] = 'error';
            $options['do'] = $do;
        }


        
        $this->application['windowName'] = $windowName;

        //$this->application['pluginForm'] = $pluginForm;

        $this->application = array_merge($this->application,$this->plugin($options));
        
        $this->application['window'] = 'plugin.phtml';

        return $this->application;

    }



    private function getApplicationShowIn($pluginId)
    {
        $pluginId = intval($pluginId);
        $pluginId = intval($pluginId);

        if ($pluginId == 0)
        {
            return;
        }

        $query = $this->db->select()->from('plugins_applications')->where('plugin_id = ?', $pluginId);

        $rows = $this->db->fetchAll($query);

        $outputs = array();

        foreach ($rows as $row)
        {
            $outputs[$row['application_id']][$row['page_key']] = explode(',', $row['page_value']);
        }

        return $outputs;

    }


    public function saveGridOrder()
    {

        $order = new MC_Admin_Model_Order();

        $options['table'] = 'grid';

        $options['primary'] = 'grid_id';

        $options['field'] = 'grid_order';


        if ($order->save($options) == false)
        {
            return;
        }

        $this->application['renderWindow'] = false;

        $this->application['windowRender'] = false;

        $this->application['window'] = false;

        return $this->application;

    }

    public function saveGroupOrder()
    {

        $order = new MC_Admin_Model_Order();

        $options['table'] = 'plugins_groups';

        $options['primary'] = 'group_id';

        $options['field'] = 'group_order';

        if ($order->save($options) == false)
        {
            return;
        }

        $this->application['renderWindow'] = false;

        $this->application['windowRender'] = false;

        $this->application['window'] = false;

        return $this->application;

    }

    public function savePluginOrder()
    {

        $order = new MC_Admin_Model_Order();

        $options['table'] = 'plugins';

        $options['primary'] = 'plugin_id';

        $options['field'] = 'plugin_order';

        if ($order->save($options) == false)
        {
            return;
        }

        $this->application['renderWindow'] = false;

        $this->application['windowRender'] = false;

        $this->application['window'] = false;

        return $this->application;

    }




    public function deleteGrid()
    {


        $request = $this->_Zend->getRequest();

        $gridIdGet = $request->getParam('gridid');

        $gridIdPost = $request->getPost('grids');
        
           //Passing Success Message
        $this->application['message']['text'] = Zend_Registry::get('Zend_Translate')->translate('Grid Deleted successfully');

        $this->application['message']['type'] = 'success';

        
        if(!empty($gridIdGet))
        {
            $this->_query->deleteGrid(intval($gridIdGet));
        }else
        if(is_array($gridIdPost))
        {
            if(count($gridIdPost) > 0)
            {
                foreach($gridIdPost as $gridId)
                {
                    $this->_query->deleteGrid(intval($gridId));
                }
            }
        }
                else
        {
            $this->application['message']['text'] = Zend_Registry::get('Zend_Translate')->translate('Please select grid to delete');

            $this->application['message']['type'] = 'success';
        }
        
        
        $this->application['window'] = 'index.phtml';

        $this->application = array_merge($this->index(), $this->application);

        return $this->application;

    }

    public function deleteGroup($options = array())
    {
      $request = $this->_Zend->getRequest();

        $gridIdGet = $request->getParam('gridid');

        $groupIdGet = $request->getParam('groupid');

        $groupIdPost = $request->getPost('grids');
        
           //Passing Success Message
        $this->application['message']['text'] = Zend_Registry::get('Zend_Translate')->translate('Group Deleted successfully');

        $this->application['message']['type'] = 'success';

        
        if(!empty($groupIdGet))
        {
            $this->_query->deleteGroup(intval($groupIdGet));
        }
        elseif(is_array($groupIdPost))
        {
            if(count($groupIdPost) > 0)
            {
                foreach($gridIdPost as $gridId)
                {
                    $this->_query->deleteGrid(intval($gridId));
                }
            }
        }
        else
        {
            $this->application['message']['text'] = $this->translate('please_select_at_leaset_one_group_to_delete');
            $this->application['message']['type'] = 'success';
        }

        $this->application['window'] = 'groups.phtml';

        $this->application = array_merge($this->groups(array('gridid'=>$gridIdGet)), $this->application);

        return $this->application;
  
    }

    public function deletePlugin($options = array())
    {
        
        
        $plugin_id = $this->_Zend->getRequest()->getPost('plugin_id');
        
        $group_id = $this->_Zend->getRequest()->getParam('group_id');
        if(empty($group_id) || !is_numeric($group_id))
        {
            return;
        }
        
        if(is_array($plugin_id) && count($plugin_id) > 0)
        {
            
            foreach($plugin_id as $id)
            {
                if(is_numeric($id))
                {
                    $this->_query->deletePlugin(intval($id));
                }
            }
            //Passing Success Message
            $this->application['message']['text'] = Zend_Registry::get('Zend_Translate')->translate('plugins_delete_success');
            $this->application['message']['type'] = 'success';
        
        }else if(is_numeric($plugin_id))
        {
            $this->_query->deletePlugin($id);
            //Passing Success Message
            $this->application['message']['text'] = Zend_Registry::get('Zend_Translate')->translate('plugin_delete_success');
            $this->application['message']['type'] = 'success';
        
        }else
        {
            //Passing Success Message
            $this->application['message']['text'] = Zend_Registry::get('Zend_Translate')->translate('please_select_plugin');
            $this->application['message']['type'] = 'error';
        }
        
        $this->application = array_merge($this->application,$this->Plugins(array('group_id'=>$group_id)));
        $this->application['window'] = 'plugins';
        return $this->application;
    }
 
    public function duplicateGrid()
            
    {
        $request = $this->_Zend->getRequest();

        $gridIdGet = $request->getParam('gridid');

        $gridIdPost = $request->getPost('grids');
        
        
        $this->application['message']['text'] = Zend_Registry::get('Zend_Translate')->translate('Grid duplicated successfully');

        $this->application['message']['type'] = 'success';

        
        
        if(!empty($gridIdGet))
        {
            $this->_query->duplicateGrid(intval($gridIdGet));
        }
                else
        {
                    return;
        }
        
        
        /*
        if(is_array($gridIdPost))
        {
            if(count($gridIdPost) > 0)
            {
                foreach($gridIdPost as $gridId)
                {
                    $this->_query->deleteGrid(intval($gridId));
                }
            }
        } 
        */
        $this->application['window'] = 'index.phtml';

        $this->application = array_merge($this->index(), $this->application);

        return $this->application;
    }
    
    
        public function duplicateGroup()
            
    {
        $request = $this->_Zend->getRequest();

        $groupIdGet = $request->getParam('groupid');
        
        $gridIdGet = intval($request->getParam('gridid'));

        $gridIdPost = $request->getPost('grids');

        $this->application['message']['text'] = Zend_Registry::get('Zend_Translate')->translate('Group duplicated successfully');

        $this->application['message']['type'] = 'success';

        if(!empty($groupIdGet))
        {
            $this->_query->duplicateGroup(intval($groupIdGet));
        }
        else
        {
                    return $this->setError();
        }
        
        if($gridIdGet != 0)
        {
            $this->application['window'] = 'groups.phtml';

            $this->application = array_merge($this->groups(array('gridid'=>$gridIdGet)), $this->application);
        }
        return $this->application;
    }

    public function _templateGroupCategoriesList()
    {

        $theme_id = intval($this->_Zend->getRequest()->getPost('themeid'));
        if($theme_id == 0)
        {
            return $this->setError();
        }

        $categoriesList = $this->db->fetchAll($this->db->select()->from('templates_categories')->where('theme_id = ?',$theme_id));

        $this->assign('categories',$categoriesList);

        $this->setView(false);

        return $this->application;
    }
}
#--------------------------------------------
#   | End of App_Plugins_Admin_Plugins
#--------------------------------------------
