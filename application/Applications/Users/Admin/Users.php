<?php

class App_Users_Admin_Users extends Admin_Model_ApplicationAbstract
{

    public $application = array();

    protected $db;

    protected $_Zend;

    public function __construct($application = array())
    {
        parent::__construct($application);
        $this->MC->load->appLibrary('Queries');
        $this->MC->load->appLibrary('Functions');
        $this->MC->load->appLibrary('Forms');
        $this->MC->load->appLibrary('Api');
        $this->application['nav']->append($this->translate('users'),'window/index');
    }

    public function index()
    {
        $usergroupsList = $this->MC->Queries->usergroupList($this->application['lang_id']);
        if(!$usergroupsList){
            $usergroupsList = array();
        }
        $this->assign('usergroupsList',$usergroupsList);
        $this->setSidebar('indexSidebar');
        return $this->application;
    }
    

    public function usergroup($options = array())
    {
        $do =  (isset($options['do']))?$options['do']:$this->MC->Zend->getRequest()->getParam('do');
        if ($do != "edit" && $do != "add"){
            return $this->setError();
        }
        if ($do == "edit"){
            $ugid = (isset($options['usergroupId']))?$options['usergroupId']:intval($this->_Zend->getRequest()->getParam('ugid'));
            $usergroupQuery = $this->MC->Queries->usergroupQuery($ugid, true, false);
            if (!$usergroupQuery){
                return;
            }
            $this->application['nav']->append(Zend_Registry::get('Zend_Translate')->translate('Edit usergroup') . " : " . $usergroupQuery['usergroup_lang'][$this->application['lang_id']]['usergroup_name']);
        }else{
            $this->application['nav']->append(Zend_Registry::get('Zend_Translate')->translate('Add new users group'));
        }
        $usergroupQuery['usergroup_id'] = $usergroupQuery[0]['usergroup_id'];
        $usergroupQuery['do'] = $do;
        $this->application['usergroupForm'] = (isset($options['usergroupForm']))?$options['usergroupForm']:$this->MC->Forms->usergroupForm($usergroupQuery);
        
        return $this->application;

    }

    public function saveUsergroup()
    {
        $data = $this->MC->Zend->getRequest()->getPost();
        $do = $data['do'];
        if ($do != 'add' && $do != 'edit'){
            return $this->setError();
        }

        $usergroupForm = $this->MC->Forms->usergroupForm();

        if ($usergroupForm->isValid($data)){
            $usergroupLang = $data['usergroup_lang'];
            if ($do == 'add'){
                $this->db->insert('usergroups',array('usergroup_id'=>''));
                $usergroupId = $this->db->lastInsertId();
                $this->setMessage($this->translate('usergroup_added_succes_need_to_set_permissions_to_this_usergroup'),'success');
                $this->replaceUrl($this->MC->Functions->permissionsUrl(array('ugid' => $usergroupId)));
                $this->setView('permissions');
                $this->merge($this->permissions(array('usergroup_id' => $usergroupId)));
            }elseif ($do == 'edit'){
                $usergroupId = $data['usergroup_id'];
                $this->setMessage($this->translate('usergroup_saved_success'),'success');

            }
            foreach ($usergroupLang as $langId => $usergroup)
            {
                $usergroup['lang_id'] = $langId;
                $usergroup['usergroup_id'] = $usergroupId;
                if (empty($usergroup['usergroup_name'])){
                    continue;
                }
                if($do == 'add'){
                    $this->db->insert('usergroups_lang', $usergroup);
                }else{
                    $checkExists = $this->MC->Queries->getUsergroupByLang($usergroupId,$langId);
                    if ($checkExists){
                        $where = $this->db->quoteInto('usergroups_lang.usergroup_id = ? ', $usergroupId);
                        $where .= $this->db->quoteInto(' AND usergroups_lang.lang_id = ? ', $langId);
                        $this->db->update('usergroups_lang', $usergroup, $where);
                    }else{
                        $this->db->insert('usergroups_lang', $usergroup);
                    }
                }
            }

            if($do == 'edit'){
                $this->setView('usergroup');
                $this->merge($this->usergroup(array('do'=>'edit','usergroupId'=>$usergroupId)));
            }
        }
        else
        {
            $options = array();
            if ($do == 'edit'){
                $options['usergroupId'] = $data['usergroup_lang'];
                $options['do'] = 'edit';
            }else{
                $options['do'] = 'add';
            }
            $options['usergroupForm'] = $usergroupForm;
            $this->setMessage($this->translate('fields_empty'),'error');
            $this->merge($this->usergroup($options));
            $this->setView('usergroup');
        }
        return $this->application;
    }

    public function users()
    {
        $usergroupId = $this->_Zend->getRequest()->getParam('usergroupId');

        $usergroupId = intval($usergroupId);
        
        if ($usergroupId == 0)
        {
            return;
        }

        $usergroup = $this->MC->Queries->getUsergroupByLang($usergroupId, $this->application['lang_id']);
        if(!$usergroup){
            $usergroup = $this->MC->Queries->getUsergroupByLang($usergroupId);
        }
        if(!$usergroup){
            return $this->setError($this->translate('not_found_usergroup'));
        }


        $this->application['users'] = $this->MC->Queries->getUsersByUsergroupId($usergroupId);
        $this->assign('usergroup_id',$ugid);

        $this->application['nav']->append($usergroup['usergroup_name']);
        $this->setSidebar('usersSidebar');
        return $this->application;
    }

    public function user($options = array())
    {
        $request = $this->_Zend->getRequest();
        $do = (isset($options['do']))?$options['do']:$request->getParam('do');
        if ($do != 'add' && $do != "edit"){
            return $this->setError();
        }

        if ($do == 'edit'){
            $userid = (isset($options['userId']))?$options['userId']:$request->getParam('userId');
            if (intval($userid) == 0){
                return;
            }
            $user = $this->MC->Queries->userQuery(array('user_id' => $userid));

            $usergroup = $this->MC->Queries->getUsergroupByLang($user['usergroup_id'], $this->application['lang_id']);
            if(!$usergroup)
            {
                $usergroup = $this->MC->Queries->getUsergroupByLang($user['usergroup_id']);
            }
            if(!$usergroup)
            {
                return $this->setError($this->translate('not_found_usergroup'));
            }
            $this->setNav($usergroup['usergroup_name'],'window/users/usergroupId/' . $usergroup['usergroup_id']);
            $this->application['nav']->append($user['username']);
            $user['do'] = 'edit';
        }else
        {
            $usergroupId = (isset($options['usergroupId']))?$options['usergroupId']:intval($request->getParam('usergroupId'));
            if ($usergroupId == 0){
                return $this->setError();
            }
            $user = array();
            $user['usergroup_id'] = $usergroupId;
            $user['do'] = 'add';
            $usergroup = $this->MC->Queries->getUsergroupByLang($usergroupId, $this->application['lang_id']);
            if(!$usergroup)
            {
                $usergroup = $this->MC->Queries->getUsergroupByLang($usergroupId);
            }
            if(!$usergroup)
            {
                return $this->setError($this->translate('not_found_usergroup'));
            }
            $this->setNav($usergroup['usergroup_name'],'window/users/ugid/' . $usergroup['usergroup_id']);
            $this->setNav($this->translate('add_user'));
        }


        $this->assign('userForm',(isset($options['userForm']))?$options['userForm']:$this->MC->Forms->userForm($user));
        return $this->application;
    }

    public function permissions($options = array())
    {
        $userId = (isset($options['userid'])) ? $options['userid'] : intval($this->_Zend->getRequest()->getParam('uid'));
        $usergroupId = (isset($options['usergroup_id'])) ? $options['usergroup_id'] : intval($this->_Zend->getRequest()->getParam('ugid'));
        $permsQuery = $this->db->select()->from('permissions');

        $this->application['permsLabels'] = array(1=>'yes',0=>'no',2=>'inherit');

        if ($userId != 0){
            $this->assign('permissionFor','user');
            $this->assign('item',$this->MC->Queries->userQuery(array('user_id' => $userId)));
            $this->setNav($this->application['item'][0]['usergroup_name'],'window/users/ugid/' . $this->application['item'][0]['usergroup_id']);
            $this->setNav("User Permission : " . $this->application['item'][0]['username']);
            $permsQuery->where('user_id = ? ', $userId);
        }else if ($usergroupId != 0){
            $this->assign('permissionFor','usergroup');
            $usergroup = $this->MC->Queries->getUsergroupByLang($usergroupId,$this->application['lang_id']);
            if($usergroup == false){
                $usergroup = $this->MC->Queries->getUsergroupByLang($usergroupId);
            }

            $this->assign('item',$usergroup);
            $this->setNav("Usergroup Permissions : " . $usergroup['usergroup_name'],'window/users/ugid/' . $usergroup['usergroup_id']);
            $permsQuery->where('usergroup_id = ? ', $usergroupId);
        }else{
            return $this->setError();
        }

        if (!count($this->application['item'])){
            return $this->setError();
        }

        $permissions = new App_Users_Admin_Permissions();
        $permsRow = $this->db->fetchRow($permsQuery);

        $this->assign('super_admin',$permsRow['super_admin']);
        $this->assign('perms',MC_Json::decode($permsRow['perms']));
        $this->assign('applications',Admin_Model_System_Application::getApp(array('listAll' => true)));
        $this->setSidebar('permissionsSidebar');
        $applications = array();
        foreach ($this->application['applications'] as $app)
        {
            $this->application['default_permissions'][$app['app_prefix']] = $permissions->defaultPerms();
            $applications[$app['app_prefix']] = array();
            $applications[$app['app_prefix']]['app_name'] = $app['app_name'];
            $applications[$app['app_prefix']]['app_prefix'] = $app['app_prefix'];
            $applications[$app['app_prefix']]['app_id'] = $app['app_id'];
            $appClass = 'App_' . ucfirst($app['app_prefix']) . '_Admin_' . ucfirst($app['app_prefix']);
            $reflectionClass = new ReflectionClass($appClass);
            $methods =  $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC); //get_class_methods($appClass);
            foreach ($methods as $method)
            {
                if($method->class == $appClass){
                    $applications[$app['app_prefix']]['methods'][] = $method->name;
                }
            }
            $appPermClass = 'App_' . ucfirst($app['app_prefix']) . '_Admin_Permissions';
            if (class_exists($appPermClass)){
                $appPermClass = new $appPermClass();
                if ($appPermClass instanceof MC_Models_Permissions_Abstract){
                    if(method_exists($appPermClass, 'getTables')){
                        if($getTables = $appPermClass->getTables()){
                            $applications[$app['app_prefix']]['tables'] = $getTables;
                        }
                    }
                    if(method_exists($appPermClass, 'perms')){
                        $this->application['default_permissions'][$app['app_prefix']] = array_merge($permissions->defaultPerms(),$appPermClass->perms());
                    }
                }
            }
        }
        $this->assign('applications',$applications);
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
        $data = $this->MC->Zend->getRequest()->getPost();
        if($data['do'] != "add" && $data['do'] != "edit"){
            return $this->setError();
        }

        $form = $this->MC->Forms->userForm($data);
        $do = $data['do'];
        $options = array();
        if ($form->isValid($data)){
            $user = array();
            $crypyPassword = new MC_Crypt_Password();
            $user['username'] = $data['user']['username'];
            $user['email'] = $data['user']['email'];
            $user['usergroup_id'] = $data['usergroup_id'];
            if($data['do'] == 'add'){
                $date = new MC_date();
                $user['create_date'] = $date->getTimestamp();
                $user['password'] = $crypyPassword->create($data['user']['password']);
                $this->MC->db->insert('users',$user);
                $userId = $this->MC->db->lastInsertId();
                $this->setMessage('user_added_success','success');
                $this->replaceUrl($this->MC->Functions->userUrl($userId));
            }elseif($data['do'] == 'edit'){
                $userId = $data['user_id'];
                if($data['user']['password'] != ""){
                    $user['password'] = $crypyPassword->create($data['user']['password']);
                }
                $where = $this->MC->db->quoteInto('user_id = ? ',$userId);
                $this->MC->db->update('users',$user,$where);
                $this->setMessage('user_saved_success','success');
            }
            $options['do'] = 'edit';
            $options['userId'] = $userId;
        }else{
            $options['userForm'] = $form;
            if($do == 'add'){
                $options['usergroupId'] = $data['usergroup_id'];
            }else{
                $options['user_id'] = $data['user_id'];
            }
            $options['userForm'] = $form;
            $this->setMessage('empty_fields','error');
        }
        $this->merge($this->user($options));

        $this->setView('user');
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
            $pages[$k]['url'] = $this->MC->Functions->userPageUrl(array('user_page_id'=>$page['user_page_id']));
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
        $pageForm = (empty($options['pageForm']))?$this->MC->Forms->userPage($userPageQuery):$options['pageForm'];
        $this->assign('pageForm',$pageForm);
        return $this->application;
    }


    public function saveUserPage()
    {
        $data  = $this->_Zend->getRequest()->getPost();
        $userPageForm = $this->MC->Forms->userPage();

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

            if($this->Api->isValidSaveUserPage($this->Api->saveUserPage($dataArray)))
            {
                if($data['do'] == 'add')
                {
                    $this->setMessage('user_page_added','success');
                }
                else
                {
                    $this->setMessage('user_page_updated','success');
                }

                $userPageId = $this->Api->userPageId();
                $this->replaceUrl($this->MC->Functions->userPageUrl(array('user_page_id'=>$userPageId)));
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

    public function login($loginParams = array())
    {
        $this->assign('loginForm',(isset($loginParams['loginForm']))?$loginParams['loginForm']:$this->MC->Forms->login());
        $this->setLayout('login');
        return $this->application;
    }

    public function submitLogin()
    {
        $request = $this->MC->Zend->getRequest();

        $form = $this->MC->Forms->login();

        if (!$form->isValid($request->getPost()))
        {
            $this->merge($this->login(array('loginForm'=>$form)));
            $this->setView('login');
            return $this->application;
        }

        $params = $form->getValues();
        $adapter = $this->getAuthAdapter(array_shift($params));
        $auth = Zend_Auth::getInstance();
        $result = $auth->authenticate($adapter);
        if (!$result->isValid())
        {
            $form->setDescription("Invalid Creditnitial Provided");
            $this->merge($this->login(array('loginForm'=>$form)));
            $this->setView('login');
            return $this->application;
        }
        else
        {
            $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
            $redirector->gotoSimple('index','index');
        }
    }

   public function logout()
   {
       Zend_Auth::getInstance()->clearIdentity();
       $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
       $redirector->gotoSimple('index','index');
   }
    protected  function getAuthAdapter(array $params)
    {
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);
        $cryptPassword = new MC_Crypt_Password();
        $authAdapter->setTableName('users')
            ->setIdentityColumn('username')->
            setCredentialColumn('password')->setCredentialTreatment('MD5(?)');//setCredentialTreatment($cryptPassword->create($params['password']));
        return $authAdapter->setIdentity($params['username'])->setCredential($params['password']);
    }


}