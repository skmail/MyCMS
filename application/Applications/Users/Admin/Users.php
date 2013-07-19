<?php

class App_Users_Admin_Users extends Admin_Model_ApplicationAbstract
{

    public $application = array();

    protected $db;

    protected $_Zend;

    public function __construct($application = array())
    {
        parent::__construct($application);
       
        $this->_form = new App_Users_Admin_Forms($application);

        $this->_query = new App_Users_Admin_Queries($application);

        $this->_sharedQuery = new App_Users_Shared_Queries($application);
        
        $this->Functions = new App_Users_Admin_Functions($application);

        $this->_api = new App_Users_Shared_Api();

        $this->menu = $this->Functions->sideMenu();
        
        $this->application['nav']->append(Zend_Registry::get('Zend_Translate')->translate('Users'));

    }

    public function index()
    {
        $this->application['usergroupsList'] = $this->_query->usergroupList();


        $this->application['sidebar'] = 'indexSidebar';

        return $this->application;

    }

    public function usergroup()
    {
        $do = $this->_Zend->getRequest()->getParam('do');
        
        if ($do != "edit" && $do != "add")
        {
            return;
        }
        if ($do == "edit")
        {
            $ugid = $this->_Zend->getRequest()->getParam('ugid');
        
            $ugid = intval($ugid);
            
            $usergroupQuery = $this->_query->usergroupQuery($ugid, true, false);
            
            if (!$usergroupQuery)
            {
                return;
            }
          
            $this->application['nav']->append(Zend_Registry::get('Zend_Translate')->translate('Edit usergroup') . " : " . $usergroupQuery['usergroup_lang'][$this->application['lang_id']]['usergroup_name']);
        }
        else
        {
            $this->application['nav']->append(Zend_Registry::get('Zend_Translate')->translate('Add new users group'));
        }

        $usergroupQuery['usergroup_id'] = $usergroupQuery[0]['usergroup_id'];

        $usergroupQuery['do'] = $do;
      
        $this->application['usergroupForm'] = $this->_form->usergroupForm($usergroupQuery);
        
        return $this->application;

    }

    public function saveUsergroup()
    {
        
        $request = $this->_Zend->getRequest();
        
        $do = $request->getPost('do');

        if ($do != 'add' && $do != 'edit' && $do != 'delete')
        {
            return;
        }


        $this->application['window'] = 'usergroup.phtml';
        
        $this->application['usergroupForm'] = $this->_form->usergroupForm();

        if ($this->application['usergroupForm']->isValid($request->getPost()))
        {

            $usergroup_lang = $request->getPost('usergroup_lang');
          
            $usergroup_lang = $usergroup_lang['usergroup_lang'];
            
            if ($do == 'add')
            {

                $this->db->insert('usergroups', array('usergroup_id' => ''));
            
                $usergroupId = $this->db->lastInsertId();

                foreach ($usergroup_lang as $lang_id => $usergroupLang)
                {

                    if (empty($usergroupLang['usergroup_name']))
                    {
                        continue;
                    }
                    
                    $usergroupLang['lang_id'] = $lang_id;
                  
                    $usergroupLang['usergroup_id'] = $usergroupId;

                    $this->db->insert('usergroups_lang', $usergroupLang);
                }
                
                $this->application['message']['text'] = 'Usergroup Added Succesfully, You Need to set permissions for Usergroup';
               
                $this->application['message']['type'] = 'success';

                $this->application['replaceUrl'] = $this->Functions->permissionsUrl(array('ugid'                           => $usergroupId));
                
                $this->application['window'] = 'permissions.phtml';
                
                $this->application = array_merge($this->application, $this->permissions(array('usergroup_id' => $usergroupId)));
            }
            if ($do == 'edit')
            {
                
                $usergroupId = $request->getPost('usergroup_id');
               
                foreach ($usergroup_lang as $lang_id => $usergroupLang)
                {
                    if (empty($usergroupLang['usergroup_name']))
                    {
                        continue;
                    }


                    $checkExists = $this->_query->usergroupQuery($usergroupId, true, true, array('lang_id' => $lang_id));

                    $usergroupLang['lang_id'] = $lang_id;
                    
                    $usergroupLang['usergroup_id'] = $usergroupId;

                    if ($checkExists)
                    {

                        $where = $this->db->quoteInto('usergroups_lang.usergroup_id = ? ', $usergroupId);
                    
                        $where .= $this->db->quoteInto(' AND usergroups_lang.lang_id = ? ', $lang_id);

                        $this->db->update('usergroups_lang', $usergroupLang, $where);
                    }
                    else
                    {
                        $this->db->insert('usergroups_lang', $usergroupLang);
                    }
                }

                $usergroupQuery = $this->_query->usergroupQuery($usergroupId, true, false);

                $this->application['usergroupForm'] = $this->_form->usergroupForm($usergroupQuery);
                
                $this->application['message']['text'] = 'Usergroup Saved Successfuly';
                
                $this->application['message']['type'] = 'success';
            }
        }
        else
        {
            $this->application['message']['text'] = 'Some Fields empty';
         
            $this->application['message']['type'] = 'error';
        }
        
        return $this->application;

    }

    public function users()
    {
        $ugid = $this->_Zend->getRequest()->getParam('ugid');
       
        $ugid = intval($ugid);
        
        if ($ugid == 0)
        {
            return;
        }
        $this->setSidebar('usersSidebar');

        $this->application['users'] = $this->_query->userQuery(array('usergroup_id' => $ugid));
        $this->assign('usergroup_id',$ugid);

        $this->application['nav']->append($this->application['users'][0]['usergroup_name']);

        return $this->application;

    }

    public function user()
    {
        $request = $this->_Zend->getRequest();

        $do = $request->getParam('do');

        if ($do != 'add' && $do != "edit")
        {
            return;
        }

        if ($do == 'edit')
        {
            $userid = $request->getParam('uid');

            if (intval($userid) == 0)
            {
                return;
            }

            $user = $this->_query->userQuery(array('user_id' => $userid));

            $this->application['nav']->append($user['usergroup_name'], 'window/users/ugid/' . $user['usergroup_id']);
            
            $this->application['nav']->append($user['username']);

            $user['do'] = 'edit';
            
        }else
        {
            $usergroupId = intval($request->getParam('ugid'));
            if ($usergroupId == 0)
            {
                return;
            }
            
            $usergroupQuery = $this->_query->usergroupQuery($usergroupId, true, true);
            
            $user = array();
            
            $user['usergroup_id'] = $usergroupId;
            
            $user['do'] = 'add';

            $this->application['nav']->append($usergroupQuery['usergroup_name'], 'window/users/ugid/' . $usergroupQuery['usergroup_id']);
            
            $this->application['nav']->append('Add New User');
        }

        $this->application['userForm'] = $this->_form->userForm($user);
       
        return $this->application;

    }

    public function permissions($options = array())
    {

        $userid = (isset($options['userid'])) ? $options['userid'] : intval($this->_Zend->getRequest()->getParam('uid'));
        
        $usergroup_id = (isset($options['usergroup_id'])) ? $options['usergroup_id'] : intval($this->_Zend->getRequest()->getParam('ugid'));
        
        $permsQuery = $this->db->select()->from('permissions');
        
        $this->application['permsLabels'] = array(1=>'yes',0=>'no',2=>'inherit');

        if ($userid != 0)
        {
            $this->application['permissionFor'] = 'user';
        
            $this->application['item'] = $this->_query->userQuery(array('user_id' => $userid));
            
            $this->application['nav']->append($this->application['item'][0]['usergroup_name'], 'window/users/ugid/' . $this->application['item'][0]['usergroup_id']);
            
            $this->application['nav']->append("User Permission : " . $this->application['item'][0]['username']);
            
            $permsQuery->where('user_id = ? ', $userid);
        }
        else if ($usergroup_id != 0)
        {
            $this->application['permissionFor'] = 'usergroup';

            $this->application['item'] = $this->_query->usergroupQuery($usergroup_id);

            $this->application['nav']->append("Usergroup Permissions : " . $this->application['item']['usergroup_name'], 'window/users/ugid/' . $this->application['item']['usergroup_id']);

            $permsQuery->where('usergroup_id = ? ', $usergroup_id);
        }else
        {
            return;
        }

        if (!count($this->application['item']))
        {
            return;
        }

        $this->application['sidebar'] = 'permissionsSidebar';

        $permissions = new App_Users_Admin_Permissions();

        $permsRow = $this->db->fetchRow($permsQuery);
        
        
        $this->application['super_admin'] = $permsRow['super_admin'];
        $this->application['perms'] = Zend_Json::decode($permsRow['perms']);
       
        $this->application['applications'] = Admin_Model_System_Application::getApp(array('listAll' => true));

        $applications = array();

        foreach ($this->application['applications'] as $app)
        {
            
            $this->application['default_permissions'][$app['app_id']] = $permissions->defaultPerms();
            
            $applications[$app['app_id']] = array();
            
            $applications[$app['app_id']]['app_name'] = $app['app_name'];
            
            $applications[$app['app_id']]['app_prefix'] = $app['app_prefix'];
            
            $applications[$app['app_id']]['app_id'] = $app['app_id'];

            $appClass = 'App_' . ucfirst($app['app_prefix']) . '_Admin_' . ucfirst($app['app_prefix']);

            $reflectionClass = new ReflectionClass($appClass);

            $methods =  $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC); //get_class_methods($appClass);


            foreach ($methods as $method)
            {

                if($method->class == $appClass)
                {
                    $applications[$app['app_id']]['methods'][] = $method->name;
                }
            }

            $appPermClass = 'App_' . ucfirst($app['app_prefix']) . '_Admin_Permissions';

            if (class_exists($appPermClass))
            {
                $appPermClass = new $appPermClass();

                if ($appPermClass instanceof MC_Models_Permissions_Abstract)
                {
                    
                    if(method_exists($appPermClass, 'getTables'))
                    {
                        if($getTables = $appPermClass->getTables())
                        {
                            $applications[$app['app_id']]['tables'] = $getTables;
                        }
                        
                    }
                    if(method_exists($appPermClass, 'perms'))
                    {   
                        $this->application['default_permissions'][$app['app_id']] = array_merge($permissions->defaultPerms(),$appPermClass->perms());
                    }
                }
            }
        }
      
        $this->application['applications'] = $applications;
        
        return $this->application;

    }

    public function savePerm()
    {

        $request = Zend_Controller_Front::getInstance()->getRequest();

        $usergroup_id = intval($request->getPost('usergroup_id'));
       
        $user_id = intval($request->getPost('user_id'));
        
        $perms = $request->getPost('perm');

        if (!is_array($perms))
        {
            return;
        }
        if (count($perms) == 0)
        {
            return;
        }

        $params = array();

        $params['perms'] = Zend_Json_Encoder::encode($perms);

        if ($usergroup_id != 0)
        {
            $params['usergroup_id'] = $usergroup_id;
            
            $existsPerms = $this->db->select()->from('permissions');
            
            $where = $this->db->quoteInto('usergroup_id = ? ', $usergroup_id);
            
            $existsPerms->where($where);
            
            $existsPerms = $this->db->fetchRow($existsPerms);
            
            $permissionsFor = array('usergroup_id'                        => $usergroup_id);
            
            $this->application['message']['text'] = 'Usergroup Permissions Saved';
            $this->setRefresh('sidebar');
        }
        else if ($user_id != 0)
        {
            $params['user_id'] = $user_id;

            $existsPerms = $this->db->select()->from('permissions');
            
            $where = $this->db->quoteInto('user_id = ? ', $user_id);
            
            $existsPerms->where($where);
            
            $existsPerms = $this->db->fetchRow($existsPerms);
            
            $permissionsFor = array('userid'                              => $user_id);
            
            $this->application['message']['text'] = 'User Permissions Saved';
            $this->setRefresh('sidebar');
            
        }
        else
        {
            return;
        }
        
        $params['super_admin'] = $request->getPost('super_admin');
        
        if (count($existsPerms) > 0)
        {
            $this->db->update('permissions', $params, $where);
        }
        else
        {
            $this->db->insert('permissions', $params);
        }

        $this->application['message']['type'] = 'success';

        $this->application = array_merge($this->application, $this->permissions($permissionsFor));

        $this->application['window'] = 'permissions.phtml';

        return $this->application;

    }

    public function saveUser()
    {

        $request = Zend_Controller_Front::getInstance()->getRequest();

        $form = $this->_form->userForm();

        $data = $request->getPost();

        if($data['do'] != "add" && $data['do'] != "edit")
        {
            return ;
        }

        if ($form->isValid($data))
        {

            if($data['do'] == 'add')
            {

            }
            if($data['do'] == 'edit')
            {

            }

        }

        $this->application['userForm'] = $form;

        $this->application['window'] = 'user.phtml';

        return $this->application;

    }

    public function fields()
    {
        $usergroup_id = $this->_Zend->getRequest('ugid');
        return $this->application;
    }

    public function pages()
    {
        $pages = $this->_sharedQuery->page(array('lang_id'=>$this->application['lang_id']));

        foreach($pages as $k=>$page)
        {
            $pages[$k]['url'] = $this->Functions->userPageUrl(array('user_page_id'=>$page['user_page_id']));
        }

        $this->setNav($this->translate('pages'));
        $this->setSidebar('pagesSidebar');
        $this->assign('pagesList',$pages);
        return $this->application;
    }


    public function page(array $options = array())
    {

        $do = (empty($options['do']))?$this->_Zend->getRequest()->getParam('do'):$options['do'];
        $pageId = (empty($options['user_page_id']))?$this->_Zend->getRequest()->getParam('user_page_id'):$options['user_page_id'];

        $userPageQuery = array();
        $this->setNav($this->translate('pages'),'window/pages');

        if($do == 'edit')
        {
            $userPageQuery = $this->_sharedQuery->page(array('user_page_id'=>$pageId));

            if(!$userPageQuery)
            {
                return $this->setError();
            }
            $this->setNav($userPageQuery['users_pages_lang'][$this->application['lang_id']]['user_page_name']);
        }
        else
        {
            $this->setNav($this->translate('add_new_page'));
        }

        $userPageQuery['do'] = $do;
        $pageForm = (empty($options['pageForm']))?$this->_form->userPage($userPageQuery):$options['pageForm'];
        $this->assign('pageForm',$pageForm);
        return $this->application;
    }


    public function saveUserPage()
    {
        $data  = $this->_Zend->getRequest()->getPost();
        $userPageForm = $this->_form->userPage();

        $options = array();

        if($userPageForm->isValid($data))
        {
            $dataArray = array();
            $dataArray['page']['user_page_id'] = $data['user_page_id'];
            $dataArray['page']['user_page_url'] = $data['user_page_url'];
            foreach($data['user_page_lang'] as $langId=>$userPageLang)
            {
                if(empty($userPageLang['user_page_name']))
                {
                   continue;
                }
                $dataArray['user_page_lang'][$langId]['user_page_name'] = $userPageLang['user_page_name'];
            }

            if($this->_api->isValidSaveUserPage($this->_api->saveUserPage($dataArray)))
            {
                if($data['do'] == 'add')
                {
                    $this->setMessage('user_page_added','success');
                }
                else
                {
                    $this->setMessage('user_page_updated','success');
                }

                $userPageId = $this->_api->userPageId();
                $this->replaceUrl($this->Functions->userPageUrl(array('user_page_id'=>$userPageId)));
                $options['user_page_id'] = $userPageId;
                $options['do'] = 'edit';
            }
            else
            {
                $options['pageForm'] = $userPageForm;
                $options['do'] = $data['do'];
                $this->setMessage('errors_occured','error');
            }
        }
        else
        {
            $options['pageForm'] = $userPageForm;
            $this->setMessage('errors_occured','error');
        }
        $this->merge($this->page($options));
        $this->setView('page');
        return $this->application;
    }
}