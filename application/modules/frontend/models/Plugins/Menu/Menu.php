<?php

class Frontend_Model_Plugins_Menu_Menu extends Frontend_Model_Frontend
{

    public function __construct($pluginResource)
    {
        parent::__construct();

        $this->plugin = $pluginResource;

    }

    public function init()
    {


        extract($this->plugin);

        $template = new Frontend_Model_Templates_Template();

        $appSegment = new MC_Models_AppSegment();

        if (is_array($plugin_params['el']))
            foreach ($plugin_params['el'] as $k => $el)
            {

                $segment = $appSegment->getSegment($el['appid'], $el['id'], false);

                $appModel = 'MC_App_' . ucfirst($segment['app_prefix']) . '_' . ucfirst($segment['app_prefix']);

                $appModel = new $appModel();

                $segment['active'] = '';

                $segment = $appModel->segmentSettings($segment);

                $el['label'] = $segment['label'] . ' ';
                
                $el['link'] = $segment['url'];

                $el['list_class'] = 'menu_list_' . ($k + 1);

                if ($segment['active'] === true)
                {
                    $el['list_class'].=" active";
                }

                $list['list'] = $el;

                $template->fetchChildsTemplate($plugin_params['menu_template'], $list, false);
            }

        $innerTemplate = $template->fetchTemplate($plugin_params['menu_template'], $template->_template['childs'], 'innerTemplate', true);

        $templateData['title'] = $this->plugin['plugin_name'];

        $templateData['content'] = $innerTemplate;

        $data['temp'] = $templateData;

        $outerTemplate = $template->fetchTemplate($plugin_params['outer_template'], $data, 'outerTemplate', true);

        return $outerTemplate;

    }

}