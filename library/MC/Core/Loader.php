<?php

class MC_Core_Loader
{
    public function appClass($app,$class,$arg = NULL,$nameSpace = 'frontend'){
        
        $classPath = 'App_'.ucfirst($app)."_".ucfirst($nameSpace).'_'.ucfirst($class);

        if(!class_exists($classPath))
        {
            return false;
        }
        
        if(is_null($arg) || $arg == '')
        {
            $class = new $classPath();
        }
        else
        {
            $class = new $classPath($arg);
        }
        
        return $class;
    }

    public function pluginClass($pluginName,$class,$arg = NULL){

        $classPath = 'App_'.ucfirst($pluginName).'_'.ucfirst($class);

        if(!class_exists($classPath))
        {
            return false;
        }

        if(is_null($arg) || $arg == '')
        {
            $class = new $classPath();
        }
        else
        {
            $class = new $classPath($arg);
        }

        return $class;
    }


    public function hook($hookClass,$otherClass = ''){

        if($otherClass == '')
        {
            $otherClass = $hookClass;
        }

        $classPath = 'Hooks_'.ucfirst($hookClass).'_'.ucfirst($otherClass);

        if(!class_exists($classPath))
        {
            return false;
        }

        $class = new $classPath();

        return $class;
    }
    public function appLibrary($libraryName,$libraryNamespace = '')
    {

        $appPath = MC_Core_Instance::$appPath;

        $applicationLibrariesPath = 'App_'.ucfirst($appPath) . '_Shared_Libraries_';

        $fullLibraryName = $applicationLibrariesPath  . ucfirst($libraryName);

        if(!class_exists($fullLibraryName))
        {
            throw new MC_Core_Exception(sprintf("Can't find <b>%s</b> library in path <b>%s</b>",$libraryName,str_replace('_','/',rtrim(APPLICATION_PATH,'/').'/'.$applicationLibrariesPath)));
        }

        $libraryObject = new $fullLibraryName();

        if($libraryNamespace != "")
        {
            $this->_loadObject($libraryNamespace,$libraryObject);
        }
        else
        {
            $this->_loadObject($libraryName,$libraryObject);
        }
    }

    public function model($modelName,$modelNamespace = '')
    {

        $fullCoreModelName = 'MC_Models_'.ucfirst($modelName);
        $fullUserModelName = 'Models_'.ucfirst($modelName);

        $checkCoreModelName = class_exists($fullCoreModelName);
        $checkUserModelName = false ;// class_exists($fullUserModelName);


        if(!$checkCoreModelName && !$checkUserModelName){
            throw new MC_Core_Exception(sprintf("Can't find <b>%s</b> model",$modelName));
        }elseif($checkCoreModelName){
            $fullModelName = $fullCoreModelName;

        }else{
            $fullModelName = $fullUserModelName;
        }

        $object = new $fullModelName();

        if($modelNamespace == "")
        {
            $this->_loadObject($modelName,$object,'model');
        }else
        {
            $this->_loadObject($modelNamespace,$object,'model');
        }
    }

    protected function _loadObject($objectName,$object,$chainObject = "")
    {
        $MC =& MC_Core_Instance::getInstance();

        if($chainObject != "")
        {
            if(!isset($MC->$chainObject))
            {
                $MC->$chainObject = NULL ;
            }

            $MC->$chainObject->$objectName = $object;
        }
        else
        {
            $MC->$objectName = $object;
        }
    }
}