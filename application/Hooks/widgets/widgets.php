<?php

class Hooks_Widgets_Widgets{

    public function frontendStart($data)
    {
        $this->MC =& MC_Core_Instance::getInstance();
        $theme = new Frontend_Model_Templates_Theme();
        $themeLayout = new Zend_View();
        $query = $this->MC->db->select()->from('Applications')->where('app_prefix = ?','widgets');
        $app = $this->MC->db->fetchRow($query);
        $settings = MC_Json::decode($app['settings']);
        $gridsLib = new App_Widgets_Shared_Libraries_Grids();
        $gridDom = $gridsLib->gridDom($settings['grid_name']);
        $gridResources = $gridDom->resources;
        $themeFolder = $theme->currentTheme('theme_folder');
        $themeLayout->setScriptPath('mycms/Themes/' . $themeFolder);
        if(is_object($gridResources)){
            foreach($gridDom->resources as $resourcesVal)
            {
                foreach($resourcesVal->resource as $resource)
                {
                    if($resource->type == 'js'){
                        $themeLayout->headScript()->appendFile($resource->src);
                    }elseif($resource->type == 'css'){
                        $themeLayout->headLink()->appendStylesheet($resource->href, $resource->media);
                    }
                }
            }
        }
        $themeLayout->assign('plugins',new App_Widgets_Frontend_Widgets($data));
        echo $themeLayout->render('layout.phtml');
    }
}