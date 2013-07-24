<?php

/**
 * Class MC_Core_Instance
 */

class MC_Core_Instance {

    public static $instance = NULL;
    public static $appPath = NULL;

    private   function __construct()
    {

    }

    /**
     * @static
     * @return MC_Core_Instance|null
     */
    public static function    getInstance()
    {
        if(self::$instance === null){
            self::$instance = new self();
            self::$instance->_load();
        }

        return self::$instance;
    }

    /**
     * @param $appPath
     */
    public static  function setAppPath($appPath)
    {
        if(NULL == self::$appPath){
            self::$appPath = $appPath;
        }
    }

     /**
     * @desc load internal classes
     */

    protected  function _load()
    {
        $autoLoaders = array(
                                'db'=> Zend_Registry::get('db'),
                                'Zend'  => Zend_Controller_Front::getInstance(),
                                'config'=>Zend_Registry::get('config'),
                                'load'=> new MC_Core_Loader(),
                                'Template'=> new MC_Core_Template(),
                                'hook' => new MC_Models_Hooks()
                            );
        foreach($autoLoaders as $className=>$instance)
        {
            $this->$className = $instance;
        }
    }


}