<?php

class Plugins_HtmlContent_Model extends Frontend_Model_Frontend {

    public function __construct($pluginResource) {
        parent::__construct();

        $this->plugin = $pluginResource;
    }

    public function init() {
        
        
        extract($this->plugin);

        $template = new Frontend_Model_Templates_Template();
        
        $lang_params = json_decode($lang_params,true);
        
        $templateData['title'] = $this->plugin['plugin_name'];

        $templateData['content'] =  $lang_params['plugin_content'];

        $data['temp'] = $templateData;
        
        $outerTemplate = $template->prepareData($data)->fetchTemplate($plugin_params['outer_template']);

        return $outerTemplate;
    }

}