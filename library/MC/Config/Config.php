<?php
/**
 * @author Solaiman Kmail <psokmail@gmail.com>
 * @package     MC_Config
 */

class MC_Config_Config extends Zend_Config
{

    protected static $_env = null;

    /**
     * @static
     * @param array $Arr1
     * @param array $Arr2
     * @return array
     */
    public static function mergeArrays($Arr1, $Arr2)
    {
        foreach($Arr2 as $key => $value) {
            if (is_string($key)) {
                if (array_key_exists($key, $Arr1) && is_array($value)) {
                    $Arr1[$key] = self::mergeArrays($Arr1[$key], $Arr2[$key]);
                } else {
                    $Arr1[$key] = $value;
                }
            } else {
                $Arr1[] = $value;
            }
        }
        return $Arr1;
    }


    /**
     * @param $path
     * @return array()
     * @throws MC_Exception
     */



    public static function loadFromDirectory($path,$env)
    {
        self::$_env = $env;
        if(!is_dir($path)){
            throw new MC_Exception("Directory path not exists", 500);
        }
        $configDir = new MC_File_Iterator_Directory($path);
        $configsArray = array();
        foreach($configDir as $configFile)
        {
            if($configFile->isDot()){
                continue;
            }
            $configType = strtolower(pathinfo($configFile, PATHINFO_EXTENSION));
            switch($configType)
            {
                case 'ini':
                case 'php':
                    $config = self::_loadConfig(rtrim($path,'/').'/'.$configFile);
                    $configsArray = self::mergeArrays($configsArray,$config);
                break;
            }
        }
        return $configsArray;
    }

    /**
     * @static
     * @param  $configFile
     * @throws Zend_Application_Exception
     * @return array()
     */
    protected static function _loadConfig($configFile)
    {
        $configType = strtolower(pathinfo($configFile, PATHINFO_EXTENSION));

        switch($configType)
        {
            case 'ini':
                    $config = new Zend_Config_Ini($configFile,self::$_env);
                    return $config->toArray();
                break;
            case 'php':
                $config = include($configFile);
                if (!is_array($config)) {
                    throw new Zend_Application_Exception('Invalid configuration file; The config file should be an array');
                }
                return $config;
                break;
        }
    }
}