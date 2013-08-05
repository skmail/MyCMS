<?php


// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/application'));


defined('LIBRARY_PATH')
    || define('LIBRARY_PATH', realpath(dirname(__FILE__) . '/library'));

defined('PUBLIC_PATH')
    || define('PUBLIC_PATH', realpath(dirname(__FILE__) . '/mycms'));


// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));


ini_set("include_path", ".:/Applications/XAMPP/xamppfiles/htdocs/zend/library");

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    LIBRARY_PATH,
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

/** Zend_Loader_Autoloader */
require_once 'Zend/Loader/Autoloader.php';

$autoloader = Zend_Loader_Autoloader::getInstance()->registerNamespace('MC_');

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    MC_Config_Config::loadFromDirectory('mycms/configs/',APPLICATION_ENV)

);


$application->bootstrap()->run();