    <?php

    class App_AppsManager_Admin_AppsManager extends Admin_Model_ApplicationAbstract
    {

        public $application = array();

        protected $db;

        protected $_Zend;

        public function __construct($application)
        {

            $this->setSidebar('indexSidebar');

            $this->fn = MC_Core_Loader::appClass('AppsManager','Functions',Null,'Shared');

            parent::__construct($application);

        }

        public function index()
        {


            $applicationsQuery = $this->db->select()->from('Applications');

            $applicationsList = $this->db->fetchAll($applicationsQuery);

            $permissions = MC_Core_Loader::appClass('Users', 'Permissions', NULL, 'shared'); //new Custom_App_Users_Permissions();


            foreach ($applicationsList as $k => $app)
            {
                if ($permissions->isAllow($app, 'index', 'view'))
                {
                    $applicationsList[$k]['url'] = Admin_Model_System_Application::appUrl($app['app_prefix']);

                    $config = MC_Core_Loader::appClass($app['app_prefix'], 'Config', NULL, 'shared'); //new Custom_App_Users_Permissions();

                    if (!$config)
                    {
                        $applicationsList[$k]['app_status'] = 'config_file_not_found';
                        $applicationsList[$k]['app_status_color'] = "red";
                        $applicationsList[$k]['action'] = "error";
                        
                    }
                    else
                    {
                        if ($app['app_status'] == 1)
                        {
                            $applicationsList[$k]['app_status'] = "disabled";
                            $applicationsList[$k]['app_status_color'] = "gray";
                            $applicationsList[$k]['action'] = "enable";
                            
                        }
                        else
                        if ($config->version > $app['version'])
                        {
                            $applicationsList[$k]['app_status'] = "upgrade_now";
                            $applicationsList[$k]['app_status_color'] = "orange";
                            $applicationsList[$k]['install_url'] = $this->application['url'] . 'window/installer/prefix/' . $app['app_prefix'];
                            $applicationsList[$k]['action'] = "install";
                            
                        }
                        else
                        {
                            $applicationsList[$k]['app_status'] = "installed";
                            $applicationsList[$k]['app_status_color'] = "green";
                            $applicationsList[$k]['action_url'] = $this->application['url'] . 'window/uninstall/prefix/' . $app['app_prefix'];
                            $applicationsList[$k]['action'] = "uninstall";
                            
                        }
                    }
                }
                else
                {
                    unset($applicationsList[$k]);
                }
            }

            $this->assign('applicationsList', $applicationsList);
            return $this->application;

        }

        public function uploadApp()
        {

            $uploadForm = new App_AppsManager_Admin_Forms_Upload(array('action' => $this->application['url'] . 'window/uploadingApp'));

            $this->assign('uploadForm', $uploadForm);
             return $this->application;

        }

        public function uploadingApp()
        {
            $applicationsDirectory = rtrim(APPLICATION_PATH, '/') . '/Applications';
            $uploadsFolder = 'contents/applications/AppsManager/Uploads/';

            $errors = array();

            $adapter = new Zend_File_Transfer_Adapter_Http();


            if (!is_writable($applicationsDirectory))
            {
                $errors[] = $this->translate('applications_directory_not_writable');
            }
            if (!is_writable($uploadsFolder))
            {
                $errors[] = $this->translate('uploads_directory_not_writable');
            }
            else
            {
                $adapter->setDestination($uploadsFolder);
            }

            if (count($errors) == 0)
            {
                $application = $adapter->getFileInfo('file');

                $application = $application['file'];

                if ($application['type'] != 'application/zip')
                {
                    $errors[] = $this->translate('application_package_ext_should_be') . " : .zip";
                }
            }

            if (count($errors) == 0)
            {
                $compressedPackage = rtrim($uploadsFolder, '/') . '/' . $application['name'];

                if ($adapter->receive())
                {

                    //Create filter object
                    $filter = new Zend_Filter_Decompress(array(
                                'adapter' => 'Zip', //Or 'Tar', or 'Gz'
                                'options' => array(
                                'target' => $uploadsFolder
                                )
                            ));
                    $filter->filter($compressedPackage);
                    unlink($compressedPackage);

                    $uploadedApplicationDirectory = rtrim($compressedPackage, '.zip');
                    $applicationDestination = $uploadedApplicationDirectory . '/application/Applications';
                    foreach (scandir($applicationDestination) as $appName)
                    {
                        if (!in_array($appName, MC_Models_Files::$escapedFolders) && ($appName != ".DS_Store" && $appName != "__MACOSX"))
                        {
                            break;
                        }
                    }


                    if (!file_exists(rtrim(APPLICATION_PATH, '/') . '/Applications/' . $appName))
                    {

                        MC_Models_Files::copy($uploadedApplicationDirectory . '/application', rtrim(APPLICATION_PATH, '/'));
                        MC_Models_Files::copy($uploadedApplicationDirectory . '/contents', 'contents');

                        if (!$installer = MC_Core_Loader::appClass($appName, 'install', NULL, 'Admin'))
                        {
                            $errors[] = $this->translate('installer_not_found');
                        }
                        else
                        {
                            if (method_exists($installer, 'install'))
                            {
                                $installer->install();
                                $this->setMessage($this->translate('application_installed_succesfully'), 'success');
                            }
                            else
                            {
                                $errors[] = $this->translate('installer_not_found');
                            }
                        }
                    }
                    else
                    {
                        $errors[] = $this->translate('application_folder_already_exists');
                    }

                    MC_Models_Files::delete($uploadedApplicationDirectory);
                }
                else
                {
                    $errors[] = $this->translate('unexpexted_error_try_later');
                }
            }
            if (count($errors) > 0)
            {
                $this->setMessage($errors, 'error');
            }
            $this->_Zend->getRequest()->setParam('window', 'upload');
            $this->merge($this->uploadApp());

            $this->setView('uploadApp');

            return $this->application;

        }

        public function uninstall($options = array())
        {

            $app_prefix = (isset($options['prefix'])) ? $options['prefix'] : $this->_Zend->getRequest()->getParam('prefix');


            $errors = array();

            if (!$installer = MC_Core_Loader::appClass($app_prefix, 'install', NULL, 'Admin'))
            {
                $errors[] = $this->translate('installer_not_found');
            }
            else
            {
                if (!method_exists($installer, 'uninstall'))
                {

                    $errors[] = $this->translate('installer_not_found');
                }
            }


            if (count($errors) == 0)
            {

                $installer->uninstall();

                $this->setMessage($this->translate('application_unistalled_succesfuly'), 'success');

                $applicationPath = rtrim(APPLICATION_PATH, '/') . '/Applications/' . ucfirst($app_prefix);

                if (FALSE === MC_Models_Files::delete($applicationPath))
                {
                    $errors[] = $this->translate('delete_this_folder_manually') . ':<br/>' . $applicationPath;
                }

                $applicationContents = 'contents/applications/' . ucfirst($app_prefix);

                if (FALSE === MC_Models_Files::delete($applicationContents))
                {
                    $errors[] = $this->translate('delete_this_folder_manually') . ':<br/>' . $applicationContents;
                }
            }

            if (count($errors) > 0)
            {
                $this->setMessage($errors, 'error');
            }


            $this->merge($this->index());
            $this->setView('index');

            return $this->application;

        }



        public function plugins()
        {

            $pluginsList  = $this->db->select()->from('plugins_resources');

            $pluginsList = $this->db->fetchAll($pluginsList);

            foreach($pluginsList as $pluginKey=>$plugin)
            {
                $pluginConfig = MC_Core_Loader::pluginClass($plugin['plugin_name'],'Config');
            }

            $this->assign('plugins',$pluginsList);


            return $this->application;
        }

        public function uploadPlugin()
        {

            return $this->application;
        }

    }