<?php

class App_Widgets_Frontend_Widgets extends Frontend_Model_Frontend
{

    public $widgets;

    private $configs = array();
    
    private $gridConatiner = "<div id='gridRow_%s' class='gridRow %s'>%s<div class='clear'></div></div>";

    private $gridEl = "<div class='%s gridRowCat' id='gridRowCat_%s'>%s</div>";

    public function __construct($plugin)
    {
        parent::__construct();
        
        $this->theme = $this->currentTheme();

        $this->configs = $plugin;
        $this->MC =& MC_Core_Instance::getInstance();

        parent::__construct();


    }

    public function renderGrids()
    {


        $gridQuery = $this->gridQuery();


        $gridsArray = array();

        foreach ($gridQuery as $grid)
        {
            $groups = $this->pluginGroup(0, $grid['grid_id']);

            if ($groups)
            {
                if($grid['grid_params']['allow_inner_container'] == 1)
                {
                    $groups = '<div class="container_12">'.$groups.'</div>';
                }
                $gridsArray[] = sprintf($this->gridConatiner, $grid['grid_id'], $grid['grid_params']['css_class'], $groups);
            }
        }


        $widgetsContainer = implode("", $gridsArray);

        return stripslashes($widgetsContainer);

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

    private function pluginGroup($groupId = 0, $gridId = 0)
    {
        $groupQuery = $this->groupQuery($groupId, $gridId);
        $groups = $this->db->fetchAll($groupQuery);
        $groupsOutpus = "";

        foreach ($groups as $c => $groupRow)
        {
            $group_params = json_decode($groupRow['group_params'], true);
            $widgets = $this->db->fetchAll($this->pluginQuery(0, $groupRow['group_id']));
            if (!$widgets){
                continue;
            }
            $template = new Frontend_Model_Templates_Template();
            $c = 1;
            $widgetsNum = count($widgets);
            foreach ($widgets as $plugin)
            {
                $widget_css_class = array();
                if($c == 1){
                    $widget_css_class[] = 'first_plugin';
                }else if($c == $widgetsNum){
                    $widget_css_class[] = 'last_plugin';
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
                $data['plugin']['title'] = $plugin['widget_name'];
                $data['plugin']['id'] = $plugin['widget_id'];
                $widgetsAvailability = true;
                $c++;
                $template->prepareData($data)->addChildTemplate($group_params['inner_template']);
            }
            unset($data);
            if (!$widgetsAvailability){
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
        if ($groupsOutpus == ''){
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

        $plugin['widget_params'] = json_decode($plugin['widget_params'], true);
        $pluginModel = 'Widgets_' . $plugin['widget_source_name'] . '_Model';
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

        $pluginQuery = $this->db->select()->from('widgets')
                ->join('widgets_sources', 'widgets.widget_source_id = widgets_sources.widget_source_id')
                ->join('widgets_lang', 'widgets.widget_id = widgets_lang.widget_id')
                ->join('widgets_applications', ' widgets_applications.widget_id = widgets.widget_id')
                ->where('lang_id = ?', $this->lang_id)
                ->where('widgets.widget_status = ?', 1)
                ->where(' widgets_applications.application_id = ? ', $this->configs['application_id']);

        $request = Zend_Controller_Front::getInstance()->getRequest();
        $requestParams = $request->getParams();


        $appClass = 'App_'.ucfirst($request->getParam('app')).'_Shared_Pageswidgets';
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
                $classMethod = $key.'plugin';
                if(method_exists($appClass,$classMethod))
                {
                    $val = $appClass->$classMethod($val);
                }
            }
            $where[] = "(".$this->db->quoteInto(' page_key = ? ' , strtolower($key)).
                ' AND ' .$this->db->quoteInto('FIND_IN_SET( ? , widgets_applications.page_value)',$val) . ")";
        }

        if(count($where))
        {
            $pluginQuery->where(implode(' OR ',$where));
        }

        $pluginQuery->order(array('widgets.widget_order ASC'));

        if ($pluginId != 0)
        {
            $pluginQuery->where('widgets.widget_id = ?', $pluginId);
        }
        if ($groupId != 0)
        {
            $pluginQuery->where('widgets.group_id = ?', $groupId);
        }

        return $pluginQuery;

    }

    private function groupQuery($groupId = 0, $gridId = 0)
    {

        $groupQuery = $this->db->select()->from('widgets_groups')
                ->join('widgets_groups_lang', 'widgets_groups.group_id = widgets_groups_lang.group_id')
                ->where('widgets_groups_lang.lang_id = ?', $this->lang_id)
                ->where('widgets_groups.group_status = ?', 1);
        
        $groupQuery->order(array('widgets_groups.group_order ASC'));


        if ($groupId != 0)
        {
            $groupQuery->where('widgets_groups.group_id = ?', $groupId);

        }

        if ($gridId != 0)
        {
            $groupQuery->where('widgets_groups.grid_id = ?', $gridId);
        }

        return $groupQuery;

    }

    
    protected function currentTheme(){
         $themeRow = MC_App_Themes_Themes::themeQuery();
         return $themeRow['theme_id'];
    }

}