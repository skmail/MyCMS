<?php

class Frontend_Model_Plugins_Plugin extends Frontend_Model_Frontend
{

    public $plugins;

    private $configs = array();

    private $gridConatiner = "<div id='gridRow_%s' class='gridRow %s'>%s<div class='clear'></div></div>";

    private $gridEl = "<div class='%s gridRowCat' id='gridRowCat_%s'>%s</div>";

    public function __construct($plugin)
    {
        parent::__construct();
        
        $this->theme = $this->currentTheme();
        
        $this->configs = $plugin;
        
        parent::__construct();



    }

    public function renderGrids()
    {


        $gridQuery = $this->gridQuery();


        $gridsArray = array();

        foreach ($gridQuery as $grid)
        {
            $groups = $this->PluginGroup(0, $grid['grid_id']);

            if ($groups)
            {
                if($grid['grid_params']['allow_inner_container'] == 1)
                {
                    $groups = '<div class="container_12">'.$groups.'</div>';
                }
                $gridsArray[] = sprintf($this->gridConatiner, $grid['grid_id'], $grid['grid_params']['css_class'], $groups);
            }
        }


        $pluginsContainer = implode("", $gridsArray);

        return stripslashes($pluginsContainer);

    }

    private function gridQuery()
    {

        $gridQuery = $this->db->select()->from('grid');
        
        $gridQuery->join('grid_lang', 'grid_lang.grid_id = grid.grid_id');

        $gridQuery->where("grid.grid_status = 1");
        
        $gridQuery->where("grid_lang.lang_id =?", App_Language_Shared_Lang::currentLang());
        
        $gridQuery->where("theme_id = ? ", $this->theme);
        
        $gridQuery->order(array('grid.grid_order ASC'));

        $gridRows = $this->db->fetchAll($gridQuery);

        foreach($gridRows as $gridKey=>$grid)
        {
            $gridRows[$gridKey]['grid_params'] = Zend_Json::decode($grid['grid_params']);
        }
        return $gridRows;

    }

    private function PluginGroup($groupId = 0, $gridId = 0)
    {
        $groupQuery = $this->groupQuery($groupId, $gridId);
        $groups = $this->db->fetchAll($groupQuery);
        $groupsOutpus = "";

        foreach ($groups as $c => $groupRow)
        {
            $group_params = json_decode($groupRow['group_params'], true);
            $plugins = $this->db->fetchAll($this->pluginQuery(0, $groupRow['group_id']));

            if ($groupRow['group_id'] == $this->configs['content_plugin_group'])
            {
                $plugins = MC_Models_Array::push($plugins,$this->configs['content'],$this->configs['content_plugin_group_order']);
            }

            if (!$plugins)
            {
                continue;
            }

            $template = new Frontend_Model_Templates_Template();

            $c = 1;
            
            $pluginsNum = count($plugins);

            foreach ($plugins as $plugin)
            {

                $plugin_css_class = array();

                if($c == 1)
                {
                    $plugin_css_class[] = 'first_plugin';
                }
                else if($c == $pluginsNum)
                {
                    $plugin_css_class[] = 'last_plugin';
                }
                
                $plugin_css_class[] = 'plugin';
                $this->configs['css_class'] = $plugin_css_class;

                if (is_array($plugin))
                {
                    $data['plugin']['content'] = $this->plugin(0, $plugin);
                }
                else
                {
                    $data['plugin']['content'] = $plugin;
                }

                if (!$data['plugin']['content'])
                {
                    continue;
                }

                $data['plugin']['title'] = $plugin['plugin_name'];

                $data['plugin']['id'] = $plugin['plugin_id'];
                
                $pluginsAvailability = true;
                
                $c++;

                $template->prepareData($data)->addChildTemplate($group_params['inner_template']);
            }

            unset($data);

            if (!$pluginsAvailability)
            {
                continue;
            }

            $group = $groupRow; // array_merge(, $template->_template['childs']);
            
            $group['title'] = $group['group_name'];

            $data['group'] = $group;
            
            $outerTemplate = $template->prepareData($data)
                                      ->fetchTemplate($group_params['inner_template']);

            $outerTemplate = sprintf($this->gridEl, $group_params['grid_class'] . ' ' . $group_params['css_class'], $group['group_id'], $outerTemplate);


            $groupsOutpus.=$outerTemplate;
        }

        if ($groupsOutpus == '')
        {
            return false;
        }

        return $groupsOutpus;

    }

    public function plugin($pluginId = 0, $pluginObject = NULL)
    {

        if ($pluginObject != NULL)
        {
            $plugin = $pluginObject;
        }
        else
        {
            $pluginQuery = $this->pluginQuery($pluginId);
            $plugin = $this->db->fetchRow($pluginQuery);
        }

        if (!$plugin)
        {
            return;
        }

        $plugin['plugin_params'] = json_decode($plugin['plugin_params'], true);
        $pluginModel = 'Plugins_' . $plugin['plugin_resource_name'] . '_Model';

        if (!class_exists($pluginModel))
        {
            return;
        }

        $plugin = array_merge($plugin, $this->configs);
        $pluginModel = new $pluginModel($plugin);
        $pluginModelInit = $pluginModel->init();

        return $pluginModelInit;
    }

    private function pluginQuery($pluginId = 0, $groupId = 0)
    {

        $pluginQuery = $this->db->select()->from('plugins')
                ->join('plugins_resources', 'plugins.plugin_resource_id = plugins_resources.plugin_resource_id')
                ->join('plugins_lang', 'plugins.plugin_id = plugins_lang.plugin_id')
                ->join('plugins_applications', ' plugins_applications.plugin_id = plugins.plugin_id')
                ->where('lang_id = ?', $this->lang_id)
                ->where('plugins.plugin_status = ?', 1)
                ->where(' plugins_applications.application_id = ? ', $this->configs['application_id']);

        $request = Zend_Controller_Front::getInstance()->getRequest();
        $requestParams = $request->getParams();


        $appClass = 'App_'.ucfirst($request->getParam('app')).'_Shared_PagesPlugins';
        if(class_exists($appClass))
        {
            $appClass = new $appClass();
        }
        else
        {
            $appClass = false;
        }
        $where = array();


        foreach($requestParams as $key=>$val)
        {
            if(is_object($appClass))
            {
                $classMethod = $key.'Plugin';
                if(method_exists($appClass,$classMethod))
                {
                    $val = $appClass->$classMethod($val);
                }
            }
            $where[] = "(".$this->db->quoteInto(' page_key = ? ' , strtolower($key)).
                ' AND ' .$this->db->quoteInto('FIND_IN_SET( ? , plugins_applications.page_value)',$val) . ")";
        }

        if(count($where))
        {
            $pluginQuery->where(implode(' OR ',$where));
        }

        $pluginQuery->order(array('plugins.plugin_order ASC'));

        if ($pluginId != 0)
        {
            $pluginQuery->where('plugins.plugin_id = ?', $pluginId);
        }
        if ($groupId != 0)
        {
            $pluginQuery->where('plugins.group_id = ?', $groupId);
        }

        return $pluginQuery;

    }

    private function groupQuery($groupId = 0, $gridId = 0)
    {

        $groupQuery = $this->db->select()->from('plugins_groups')
                ->join('plugins_groups_lang', 'plugins_groups.group_id = plugins_groups_lang.group_id')
                ->where('plugins_groups_lang.lang_id = ?', $this->lang_id)
                ->where('plugins_groups.group_status = ?', 1);
        
        $groupQuery->order(array('plugins_groups.group_order ASC'));


        if ($groupId != 0)
        {
            $groupQuery->where('plugins_groups.group_id = ?', $groupId);

        }

        if ($gridId != 0)
        {
            $groupQuery->where('plugins_groups.grid_id = ?', $gridId);
        }

        return $groupQuery;

    }

    
    protected function currentTheme(){


         $themeRow = MC_App_Themes_Themes::themeQuery();
         
         return $themeRow['theme_id'];
         
    }

}