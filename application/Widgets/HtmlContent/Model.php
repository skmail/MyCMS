<?php

class Widgets_HtmlContent_Model extends Frontend_Model_Frontend {

    public function __construct($pluginResource) {
        parent::__construct();

        $this->plugin = $pluginResource;
    }

    public function init() {


        extract($this->plugin);

        $template = new Frontend_Model_Templates_Template();

        $lang_params = MC_Json::decode($lang_params,true);
        
        $templateData['title'] = $this->plugin['plugin_name'];

        $templateData['content'] =  $lang_params['plugin_content'];

        $data['temp'] = $templateData;

        $outerTemplate = $template->prepareData($data)->fetchTemplate($widget_params['outer_template']);

        return $outerTemplate;
    }

}