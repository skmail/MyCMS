<?php

class App_Widgets_Admin_Widgets extends Admin_Model_ApplicationAbstract
{

    public $render = true;

    private $_do = array('add', 'edit', 'delete','duplicate');

    public function __construct($application = array())
    {


        parent::__construct($application);

        $this->MC->load->appLibrary('Queries');
        $this->MC->load->appLibrary('Functions');
        $this->MC->load->appLibrary('Forms');

        $this->setNav($this->translate('widgets'),'window/index');

    }

    public function index()
    {
        $gridList = $this->MC->Queries->gridQuery(array('lang_id'=>$this->application['lang_id']));
        $this->assign('grids',$gridList);
        $this->setSidebar('indexSidebar');
        $this->setNav($this->translate('grids'),'window/index');
        return $this->application;
    }

    public function grid($options = array())
    {

        $request = $this->_Zend->getRequest();

        $do = isset($options['do'])?$options['do']: $request->getParam('do');

        $this->setNav($this->translate('grids'),'window/index');

        if (!in_array($do, $this->_do))
        {
            return $this->setError();
        }

        if ($do == 'edit' || $do == 'duplicate')
        {
            $gridId = intval(isset($options['gridid'])?$options['gridid']:$request->getParam('gridid'));
            $grid = $this->MC->Queries->gridQuery(array('grid_id'=>$gridId));
            if (!$grid)
            {
                return $this->setError();
            }
            $this->setNav($this->translate('edit_grid'));
            $this->setNav($grid['grid_name']);
        }
        else if ($do == 'add')
        {
            $grid['theme_id'] = MC_App_Themes_Themes::currentTheme();
            $this->setNav($this->translate('add_grid'));
        }
        else
        {
            return $this->setError();
        }

        $grid['do'] = $do;
        $this->application['gridForm'] = isset($options['gridForm'])?$options['gridForm']:$this->MC->Forms->gridForm($grid);

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

        $gridForm = $this->MC->Forms->gridForm($data);

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

                $this->MC->db->insert('grid', $grid);

                $gridId = $this->MC->db->lastInsertId();

                $this->application['message']['text'] = 'Grid added succesfully';
                $this->application['message']['type'] = 'success';

                $gridData['do'] = 'edit';
                $gridData['gridid'] = $gridId;
            }

            if ($data['do'] == 'edit')
            {
                $gridId = intval($data['grid_id']);

                $where = $this->MC->db->quoteInto("grid_id = ? ", $gridId);

                $this->MC->db->update('grid', $grid, $where);

                $this->MC->db->delete('grid_lang', $where);

                $this->application['message']['text'] = 'Grid saved succesfully';
                $this->application['message']['type'] = 'success';
                $gridData['gridid'] = $gridId;

            }

            foreach ($gridLang as $lang_id => $gridLangData)
            {
                $gridLangData['lang_id'] = $lang_id;
                $gridLangData['grid_id'] = $gridId;

                $this->MC->db->insert("grid_lang", $gridLangData);
            }

            $gridQuery = $this->MC->Queries->gridQuery($gridId, true, false);
            $gridQuery['do'] = 'edit';
            $this->assign('replaceUrl',$this->MC->Functions->gridUrl($gridId));
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

    /**
     * @param array $options
     * @return array|void
     */
    public function groups($options = array())
    {
        $request = $this->_Zend->getRequest();
        $gridId = ($options['groupid'])?$options['groupid']:intval($request->getParam('gridid'));
        $gridQuery = $this->MC->Queries->gridQuery(array('grid_id'=>$gridId,'lang_id'=>$this->application['lang_id']));

        if (!$gridQuery)
        {
            return $this->setError();
        }

        $groups = $this->MC->Queries->groupQuery(array('grid_id'=>$gridId,'lang_id'=>$this->application['lang_id']));

        $this->assign('grid',$gridQuery);
        $this->setNav($gridQuery['grid_name']);
        $this->assign('groups',$groups);
        $this->setSidebar('groupsSidebar');

        return $this->application;
    }

    /**
     * @param array $options
     * @return array|void
     */
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
            $groupQuery = $this->MC->Queries->groupQuery(array('group_id'=>$groupId));
            if (!$groupQuery)
            {
                return $this->setError();
            }
            $groupQuery['do'] = 'edit';
            $gridQuery = $this->MC->Queries->gridQuery(array('grid_id'=>$groupQuery['grid_id'],'lang_id'=>$this->application['lang_id']));
            $this->setNav($gridQuery['grid_name'], 'window/groups/gridid/' . $gridQuery['grid_id']);
            $this->setNav($this->translate('edit_group'));
            $this->setNav($groupQuery['group_name']);
        }
        else
        {
            $gridId = isset($options['gridid'])?$options['gridid']:$this->_Zend->getRequest()->getParam('gridid');
            $groupId = isset($options['groupid'])?$options['groupid']:$this->_Zend->getRequest()->getParam('groupid');
            if(intval($gridId) != 0)
            {
                $gridQuery = $this->MC->Queries->gridQuery(array('grid_id'=>$gridId,'lang_id'=>$this->application['lang_id']));
                if (!$gridQuery)
                {
                    return $this->setError();
                }
                $groupQuery = $gridQuery;
                $groupQuery['group']['grid_id'] = $gridId;
            }else
            {
                $parentGroupRow = $this->MC->Queries->groupQuery(array('group_id'=>$groupId));
                if(!$parentGroupRow)
                {
                    return $this->setError();
                }
                $groupQuery['parent_group'] = $groupId;
            }
            $this->setNav($gridQuery['grid_name'], 'window/groups/gridid/' . $gridQuery['grid_id']);
            $this->setNav($this->translate('add_new_group'));
            $groupQuery['do'] = 'add';
        }
        $this->assign('groupForm',isset($options['groupForm'])?$options['groupForm']:$this->MC->Forms->groupForm($groupQuery));
        return $this->application;
    }

    /**
     * @param array $options
     * @return array
     */
    public function widgets($options = array())
    {
        $this->application['group_id'] = $groupId = isset($options['group_id'])
                                                            ?$options['group_id']
                                                            :$this->_Zend->getRequest()->getParam('groupid');
        $groupId = intval($groupId);
        if($groupId == 0)
        {
            return $this->setError();
        }
        $groupRow = $this->MC->Queries->groupQuery(array('group_id'=>$groupId,'lang_id'=>$this->application['lang_id']));
        $pluginQuery = $this->MC->Queries->widget(array('group_id'=>$groupId,'lang_id'=>$this->application['lang_id']));
        $gridQuery = $this->MC->Queries->gridQuery(array('grid_id'=>$groupRow['grid_id'],'lang_id'=>$this->application['lang_id']));

        $this->assign('widgets',$pluginQuery);
        $this->assign('widgetsSources',$this->MC->Queries->widgetSource());
        $this->assign('group',$groupRow);

        $this->setNav($gridQuery['grid_name'], 'window/groups/gridid/' . $gridQuery['grid_id']);
        $this->setNav($groupRow['group_name']);

        $this->setSidebar('widgetsSidebar');

        return $this->application;
    }

    /**
     * @param array $options
     * @return array
     */

    public function widget($options = array())
    {
        $request = $this->_Zend->getRequest();

        $do = (isset($options['do']))?$options['do']:$request->getParam('do');

        if ($do == 'add')
        {
            $widgetSourceId = intval((isset($options['widgetSourceId']))?$options['widgetSourceId']:$request->getParam('widgetSourceId'));
            $groupId = intval((isset($options['groupId']))?$options['groupId']:$request->getParam('groupId'));

            if ($groupId == 0)
            {
                return $this->setError();
            }

            if ($widgetSourceId == 0)
            {
                return $this->setError();
            }

            $widgetSource = $this->MC->Queries->widgetSource(array('plugin_resource_id'=>$widgetSourceId));

            if (!$widgetSource)
            {
                return $this->setError();
            }

            $groupQuery = $this->MC->Queries->groupQuery(array('group_id'=>$groupId,'lang_id'=>$this->application['lang_id']));

            if (!$groupQuery)
            {
                return $this->setError();
            }

            $gridQuery = $this->MC->Queries->gridQuery(array('grid_id'=>$groupQuery['grid_id'],'lang_id'=>$this->application['lang_id']));

            if (!$gridQuery)
            {
                return $this->setError();
            }

            $widgetSource['group_id'] = $groupId;

            $this->setNav($gridQuery['grid_name'],'window/groups/gridid/' . $gridQuery['grid_id']);
            $this->setNav($groupQuery['group_name'],'window/widgets/groupid/' . $groupQuery['group_id']);
            $this->setNav($this->translate('add_plugin'));
            $this->setNav($widgetSource['plugin_resource_name']);

            $groupQuery = array_merge($groupQuery,$gridQuery);
            $widgetSource = array_merge($widgetSource,$groupQuery);

            unset($widgetSource['plugin_name']);
        }

        if ($do == 'edit')
        {
            $widgetId = intval((isset($options['widgetId']))?$options['widgetId']:$request->getParam('widgetId'));

            $widgetRow = $this->MC->Queries->widget(array('plugin_id'=>$widgetId));

            $params = $widgetRow['plugin_params'];

            foreach($widgetRow['plugin_lang'] as $lang_id=>$widgetRowParams){
                $params['lang_params'][$lang_id] = json_decode($widgetRowParams['lang_params'],true);
            }

            $widgetRow['application'] = $this->getApplicationShowIn($widgetId);

            $widgetSource = $this->MC->Queries->widgetSource($widgetRow['plugin_resource_id']);


            $widgetSource = array_merge($widgetSource, $widgetRow);

            $widgetSource['params'] = $params;

            $groupQuery = $this->MC->Queries->groupQuery(array('group_id'=>$widgetSource['group_id'],'lang_id'=>$this->application['lang_id']));

            $gridQuery = $this->MC->Queries->gridQuery(array('grid_id'=>$groupQuery['grid_id'],'lang_id'=>$this->application['lang_id']));

            $this->setNav($gridQuery['grid_name'],'window/groups/gridid/' . $gridQuery['grid_id']);

            $this->setNav($groupQuery['group_name'],'window/widgets/groupid/' . $groupQuery['group_id']);

            $this->setNav($this->translate('edit_widget'));
            $this->setNav($widgetSource['plugin_name']);

        }

        $widgetSource['do'] = $do;
        $widgetForm = $this->MC->Forms->widgetForm($widgetSource);

        $this->assign('widgetForm',(!empty($options['widgetForm']))?$options['widgetForm']:$widgetForm);
        return $this->application;
    }

    /**
     * @return array
     */
    public function saveWidget()
    {


        $data = $this->_Zend->getRequest()->getPost();

        $do = $data['do'];

        // get plugin resource settings
        $widgetSource = $this->MC->Queries->widgetSource($data['plugin']['plugin_resource_id']);

        $groupQuery = $this->MC->Queries->groupQuery(array('group_id'=>$data['plugin']['group_id'],'lang_id'=>$this->application['lang_id']));

        $widgetSource = array_merge($widgetSource,$groupQuery);

        $widgetForm = $this->MC->Forms->widgetForm($widgetSource);
        
        //Validation form process
        if ($widgetForm->isValid($data))
        {
            $widgetFormObj = new $widgetSource['widgetForm']();

            //Plugin params my will shape to another format
            if (method_exists($widgetFormObj, 'process'))
            {
                $data['params'] = $widgetFormObj->process($data['params']);
            }

            if (isset($data['params']['lang_params']))
            {
                $data['lang_params'] = $data['params']['lang_params'];
                unset($data['params']['lang_params']);
            }


            $widget = array();
            $widget['plugin_resource_id'] = $data['plugin']['plugin_resource_id'];
            $widget['group_id'] = $data['plugin']['group_id'];
            $widget['plugin_status'] = $data['plugin']['plugin_status'];
            $widget['plugin_params'] = MC_Json::encode($data['params']);

            $widgetLang = array();
            foreach($data['plugin_lang'] as $lang_id => $widgetLangValue)
            {
                if(!empty($widgetLangValue['plugin_name']))
                {
                    $widgetLang[$lang_id] = array();
                    if(isset($data['lang_params'][$lang_id]))
                    {
                        $widgetLang[$lang_id]['lang_params'] = MC_Json::encode($data['lang_params'][$lang_id]);
                    }
                    $widgetLang[$lang_id]['plugin_name'] = $widgetLangValue['plugin_name'];
                }
            }

            //Add plugin Process
            if ($do == 'add')
            {

                //Add plugin Settings
                $this->MC->db->insert('plugins', $widget);

                $widgetId = $this->MC->db->lastInsertId();

                //Passing Success Message
                $this->application['message']['text'] = $this->translate('widget_added_success') ;

                $this->application['message']['type'] = 'success';

                //set the url to replace in address bar
                $this->application['replaceUrl'] = $this->MC->Functions->widgetUrl($widgetId);
                $do = 'edit';
            }else if ($do == 'edit')
            {
                $widgetId = intval($data['plugin']['plugin_id']);

                $whereWidget = $this->MC->db->quoteInto('plugin_id = ? ', $widgetId);

                //Update plugin settings
                $this->MC->db->update('plugins', $widget, $whereWidget);

                //Delete plugin language
                $this->MC->db->delete('plugins_lang', $whereWidget);

                //Delete Application From Languages
                $this->MC->db->delete('plugins_applications', $whereWidget);

                //Passing Success Message
                $this->setMessage($this->translate('widget_saved_success'),'success');
            }

            //Set the application segments  that will contain the plugin

            //implode the application pages will Contain plugin and save it

            if (isset($data['application']))
            {
                if (is_array($data['application']))
                {
                    $this->MC->db->delete('plugins_applications',$this->MC->db->quoteInto('plugin_id = ? ', $widgetId));

                    foreach ($data['application'] as $appId => $pages)
                    {
                        foreach($pages as $pageKey=>$pagesList)
                        {
                            $applicationData['plugin_id'] = $widgetId;

                            $applicationData['application_id'] = $appId;

                            $applicationData['page_key'] = $pageKey;

                            $applicationData['page_value'] = implode(',',$pagesList);

                            $this->MC->db->insert('plugins_applications', $applicationData);
                        }
                    }
                }
            }
            //Save plugin language settings
            foreach ($widgetLang as $langId => $widgetLangVal)
            {
                $widgetLangVal['plugin_id'] = $widgetId;
                $widgetLangVal['lang_id'] = $langId;
                $this->MC->db->insert('plugins_lang', $widgetLangVal);
            }
            $options = array('widgetId'=>$widgetId,'do'=>'edit');


        }
        else
        {

            if($do == 'edit')
            {
                $options['widgetId'] = $data['plugin_id'];
            }else{
                $options['widgetSourceId'] = $data['plugin']['plugin_resource_id'];
                $options['groupId'] = $data['plugin']['group_id'];
            }
            $options['widgetForm'] = $widgetForm;
            //Validation Errors
            $this->setMessage($this->translate('error_in_widget_sve'),'error');
            $options['do'] = $do;
        }


        $this->application = array_merge($this->application,$this->widget($options));

        $this->application['window'] = 'widget.phtml';

        return $this->application;
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

            $groupQuery = $this->MC->Queries->groupQuery(array('group_id'=>$groupId));

            if (!$groupQuery)
            {
                return;
            }

            $groupQuery['do'] = 'edit';
        }
        else
        {

            $groupQuery = $this->MC->Queries->gridQuery(array('grid_id'=>$data['group']['grid_id'],'lang_id'=>$this->application['lang_id']));

            $groupQuery['do'] = 'add';
        }

        $groupForm = $this->MC->Forms->groupForm($groupQuery);

        if ($groupForm->isValid($data))
        {

            $group = $data['group'];

            $group['group_params'] = Zend_Json::encode($group['group_params']);

            $groupLangs = $data['group_lang'];

            if ($do == 'add')
            {
                $this->MC->db->insert('plugins_groups', $group);

                $groupId = $this->MC->db->lastInsertId();

                $this->application['message']['text'] = 'Group data Added Succefull';

                $this->application['message']['type'] = 'success';
                
                $groupData['do'] = 'edit';
                
                $groupData['groupid'] = $groupId;
                
            }
            else
            {
                $groupId = $data['group_id'];

                $groupWhere = $this->MC->db->quoteInto('group_id = ? ', $groupId);

                $this->MC->db->update('plugins_groups', $group, $groupWhere);

                $this->MC->db->delete('plugins_groups_lang', $groupWhere);

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

                $this->MC->db->insert('plugins_groups_lang', $groupLang);
            }

            $groupQuery = $this->MC->Queries->groupQuery(array('group_id'=>$groupId));

            $groupForm = $this->MC->Forms->groupForm($groupQuery);
            
            $this->application['replaceUrl'] = $this->MC->Functions->groupUrl($groupId);
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



    private function getApplicationShowIn($pluginId)
    {
        $pluginId = intval($pluginId);
        $pluginId = intval($pluginId);

        if ($pluginId == 0)
        {
            return;
        }

        $query = $this->MC->db->select()->from('plugins_applications')->where('plugin_id = ?', $pluginId);

        $rows = $this->MC->db->fetchAll($query);

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
            $this->MC->Queries->deleteGrid(intval($gridIdGet));
        }else
        if(is_array($gridIdPost))
        {
            if(count($gridIdPost) > 0)
            {
                foreach($gridIdPost as $gridId)
                {
                    $this->MC->Queries->deleteGrid(intval($gridId));
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
            $this->MC->Queries->deleteGroup(intval($groupIdGet));
        }
        elseif(is_array($groupIdPost))
        {
            if(count($groupIdPost) > 0)
            {
                foreach($gridIdPost as $gridId)
                {
                    $this->MC->Queries->deleteGrid(intval($gridId));
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
                    $this->MC->Queries->deletePlugin(intval($id));
                }
            }
            //Passing Success Message
            $this->application['message']['text'] = Zend_Registry::get('Zend_Translate')->translate('plugins_delete_success');
            $this->application['message']['type'] = 'success';
        
        }else if(is_numeric($plugin_id))
        {
            $this->MC->Queries->deletePlugin($id);
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
            $this->MC->Queries->duplicateGrid(intval($gridIdGet));
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
                    $this->MC->Queries->deleteGrid(intval($gridId));
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
            $this->MC->Queries->duplicateGroup(intval($groupIdGet));
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

        $categoriesList = $this->MC->db->fetchAll($this->MC->db->select()->from('templates_categories')->where('theme_id = ?',$theme_id));

        $this->assign('categories',$categoriesList);

        $this->setView(false);

        return $this->application;
    }
}
#--------------------------------------------
#   | End of App_Widgets_Admin_Widgets
#--------------------------------------------
