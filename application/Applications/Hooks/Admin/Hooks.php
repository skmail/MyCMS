<?php
class App_Hooks_Admin_Hooks extends Admin_Model_ApplicationAbstract
{

    public $renderWindow = true;

    public $plugin = array();

    protected $query = null;

    public function __construct($application = array())
    {
        parent::__construct($application);
        $this->Functions = MC_Core_Loader::appClass('Hooks','Functions',$application,'Admin');
        $this->setNav($this->translate('Hooks'), 'window/index');
    }

    public function index()
    {

        $hooksDirs = scandir(rtrim(APPLICATION_PATH,'/').'/Hooks');
        $hooksArray = array();
        foreach($hooksDirs as $hook)
        {
            if($hook == '.' || $hook == '..')
            {
                continue;
            }

            $hookClass = MC_Core_Loader::hook($hook);

            if(!$hookClass)
            {
                continue;
            }

            $hookQuery = $this->db->select()->from('hooks')->where('hook_name = ?',$hook)->group('hook_name');
            $hookRow = $this->db->fetchRow($hookQuery);


            $hooksArray[$hook] = $hookRow;

            $hooksArray[$hook]['hook_name'] = $hook;


            $hooksArray[$hook]['current_version'] = $hookRow['version'];

            $hookConfig = @MC_Core_Loader::hook($hook,'Config');

            if(!$hookConfig)
            {
               $hooksArray[$hook]['status'] = $this->translate('config_file_not_found');
                $hooksArray[$hook]['status_color'] = 'color_red';
            }elseif($hookConfig->version > $hookRow['version'])
            {

                if(intval($hookRow['version']) == 0)
                {
                    $hooksArray[$hook]['status'] = $this->translate('install');
                }
                else
                {
                    $hooksArray[$hook]['status'] = $this->translate('upgrade_now');
                }
                $hooksArray[$hook]['status_color'] = 'color_orange';
                $hooksArray[$hook]['available_version'] = $hookConfig->version;

                $hooksArray[$hook]['url'] = $this->application['url'] . 'window/install/hookName/'.$hook ;

            }
            else
            {
                $hooksArray[$hook]['status_color'] = 'color_green';
                $hooksArray[$hook]['status'] = $this->translate('installed');
            }
        }

        $this->assign('listHooks',$hooksArray);

        return $this->application;
    }


    public function install()
    {
        $hookName = $this->_Zend->getRequest()->getParam('hookName');

        if(empty($hookName))
        {
            return $this->setError();
        }

        $hookClass = MC_Core_Loader::hook($hookName);

        if(!$hookClass)
        {
            return $this->setError('hook_not_found');
        }

        $hookConfig = MC_Core_Loader::hook($hookName,'Config');

        $hookQuery = $this->db->select()->from('hooks')->where('hook_name = ?',$hookName)->group('hook_name');
        $hookRow = $this->db->fetchRow($hookQuery);

        if(!$hookConfig)
        {
            return $this->setError('config_file_not_found');
        }elseif($hookConfig->version > $hookRow['version'])
        {

            $installClass = MC_Core_Loader::hook($hookName,'Install');

            if($installClass)
            {
                $installClass->install();
                $this->setMessage($this->translate('hook_upgraded'),'success');
            }
            else
            {
                $this->setMessage($this->translate('cannot_upgrade_check_installer'),'error');
            }
        }
        else{
            $this->setMessage($this->translate('hook_already_uptodate'),'error');
        }
        $this->application = array_merge($this->application,$this->index());

        $this->setView('index');

        return $this->application;

    }

    public function hook($options = array())
    {
        $hookName = (isset($options['hookName']))?$options['hookName']:$this->_Zend->getRequest()->getParam('hookName');

        if(empty($hookName))
        {
            return $this->setError();
        }

        $hookClass = MC_Core_Loader::hook($hookName);

        if(!$hookClass)
        {
            $this->setError('hook_not_found');
        }

        $hookQuery = $this->db->select()->from('hooks')->where('hook_name = ?',$hookName);

        $hookRows = $this->db->fetchAll($hookQuery);

        $hookMethods = array();

        $hooksSettingsClass = @MC_Core_Loader::hook($hookName,'Settings');

        if($hooksSettingsClass)
        {
            foreach($hookRows as $hook)
            {
                if(method_exists($hooksSettingsClass,$hook['method']))
                {
                    $settingMethodForm = $hooksSettingsClass->$hook['method']();

                    if($settingMethodForm)
                    {
                        if($settingMethodForm instanceof MC_Admin_Form_SubForm)
                        {
                            if(!$settingsData = @json_decode($hook['settings'],true))
                            {
                                $settingsData = array();
                            }
                            $settingMethodForm->populate($settingsData);

                            $hookMethods[$hook['method']][$hook['event']]['form'] =  $settingMethodForm;
                        }
                    }
                }
                $hookMethods[$hook['method']][$hook['event']]['status'] = $hook['status'];
            }
        }
        else
        {
            $this->setMessage('no_settings_for_this_hook','error');
        }
        if(count($hookMethods) > 0)
        {
            $formOptions = array();
            $formOptions['hooksMethods'] = $hookMethods;
            $formOptions['action'] = $this->application['url'] . 'window/saveHook/do/edit';
            $settingsForm = MC_Core_Loader::appClass('Hooks','Forms_HooksSettings',$formOptions,'Admin');
            $settingsForm->populate(array('hook_name'=>$hookName));
            $this->assign('settingsForm',$settingsForm);
        }
        else
        {
            $this->setMessage('no_settings_for_this_hook','error');
        }
        $this->setNav($hookName);
        return $this->application;
    }

    public function saveHook()
    {
        $request =  $this->_Zend->getRequest()->getPost();

        if(empty($request['hook_name']))
        {
            return $this->setError();
        }

        $hookName = $request['hook_name'];

        $hookMethods = $request['method'];

        foreach($hookMethods as $methodName=>$data)
        {
            foreach($data as $event=>$value)
            {
                $where = $this->db->quoteInto('hook_name = ? AND ',$hookName );
                $where.= $this->db->quoteInto('method = ? AND ',$methodName );
                $where.= $this->db->quoteInto('event = ? ',$event );

                if(isset($value['settings']))
                {
                    foreach($value['settings'] as $key=>$val)
                    {
                        if(empty($val))
                        {
                            unset($value['settings'][$key]);
                        }
                    }
                    $dataSaved['settings'] = Zend_Json::encode($value['settings']);
                }

                $dataSaved = array();

                $dataSaved['status'] = $value['status'];
                $this->db->update('hooks', $dataSaved, $where);
            }
        }

        $this->setMessage($this->translate('hook_settings_saved'),'success');

        $this->application = array_merge($this->application,$this->hook(array('hookName'=>$hookName)));

        $this->setView('hook');

        return $this->application;

    }
}