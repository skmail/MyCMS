<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{



        protected function _initAutoloader()
        {

            $autoloader = Zend_Loader_Autoloader::getInstance();
            $autoloader->setFallbackAutoloader(true);

          return $autoloader;
        }

        function _initLoaderResource()
        {


            $applicationsLoader = new Zend_Loader_Autoloader_Resource(array(
                'basePath'  => APPLICATION_PATH,
                'namespace' => ''
            ));
            $applicationsLoader->addResourceType('App', 'Applications', 'App');


            $pluginsLoader = new Zend_Loader_Autoloader_Resource(array(
                'basePath'  => APPLICATION_PATH,
                'namespace' => ''
            ));
            $pluginsLoader->addResourceType('Plugins', 'Plugins', 'Plugins');

            $hooksLoader = new Zend_Loader_Autoloader_Resource(array(
                'basePath'  => APPLICATION_PATH,
                'namespace' => ''
            ));
            $hooksLoader->addResourceType('Hooks', 'Hooks', 'Hooks');

        }

        protected function _initAppAutoload()
        {

            $autoloader = new Zend_Application_Module_Autoloader(
                            array(
                                'namespace' => 'App',
                                'basePath'  => dirname(__FILE__)
                    ));
            return $autoloader;
        }

        protected function _initLayoutHelper()
        {
            $this->bootstrap('frontController');
            $layout = Zend_Controller_Action_HelperBroker::addHelper(
                            new MC_Controller_Action_Helper_LayoutLoader());

        }


            protected function _initView()
            {


                $view = new Zend_View();
                // add Helper Path
                $view->addHelperPath(APPLICATION_PATH . '/modules/admin/views/helpers', 'Admin_View_Helper');

            }

            protected function _initConfig()
            {
                $config = $this->getOptions();// new Zend_Config($this->getOptions());
                Zend_Registry::set('config', $config);
                return $config;

            }


    protected function _initDatabase()
        {

            $config = $this->getOptions();

            $db = Zend_Db::factory($config['resources']['db']['adapter'], $config['resources']['db']['params']);

            Zend_Db_Table_Abstract::setDefaultAdapter($db);

            Zend_Registry::set('db', $db);

        }
}