<?php

class App_Widgets_Shared_Libraries_Functions{
   
    
    
    public function __construct($application = array())
    {
        $this->MC =& MC_Core_Instance::getInstance();
    }

    public function groupUrl($groupId = 0, $do = 'edit')
    {

        $groupEditUrl = $this->MC->application['url'] . 'window/group/do/%s';

        if ($do == 'edit')
        {
            $groupEditUrl.='/groupid/%s';
            $groupEditUrl = sprintf($groupEditUrl, $do, $groupId);
        }
        else
        {
            $groupEditUrl = sprintf($groupEditUrl, $do);
        }

        return $groupEditUrl;

    }

    public function gridUrl($gridId = 0, $do = 'edit')
    {

        $gridEditUrl = $this->MC->application['url'] . 'window/grid/do/%s';

        if ($do == 'edit')
        {
            $gridEditUrl.='/gridid/%s';
            $gridEditUrl = sprintf($gridEditUrl, $do, $gridId);
        }
        else
        {
            $gridEditUrl = sprintf($gridEditUrl, $do);
        }

        return $gridEditUrl;

    }

    public function widgetUrl($pluginId)
    {
        return $this->MC->application['url'] . 'window/widget/do/edit/widgetId/' . $pluginId;
    }


    public function appsList()
    {

        $request = Zend_Controller_Front::getInstance()->getRequest();

        $applicationsList = array();
        $applicationsNames = array();

        $applications = new App_Widgets_Admin_Forms_Widget();

        $apps = $applications->applicationsPlugins();

        foreach ($apps as $k => $v)
        {

            $appData = $applications->applicationsQuery($v);

            $applicationsNames[$k] = $v['app'];

            foreach ($appData as $appDataK => $appDataV)
            {

                $applicationsList[$k][$appDataK]['id'] = $appDataV[$v['categoryKey']];

                $applicationsList[$k][$appDataK]['label'] = $appDataV[$v['categoryLabel']];
            }
        }

        $applications = array();

        $applications['applicationsList'] = $applicationsList;
        $applications['applicationsName'] = $applicationsNames;


        return $applications;
    }

    public function applicationsPlugins() {

        $db = Zend_Registry::get('db');

        $applicationsQuery = $db->select()->from('Applications')->where('widgets = ? ', 1);

        $applicationsRow = $db->fetchAll($applicationsQuery);

        $apps = array();

        foreach ($applicationsRow as $app) {

            $pagesList = array();

            if($application =  MC_Core_Loader::appClass($app['app_prefix'],'PagesPlugins',NULL,'Shared'))
            {
                if($application instanceof App_Widgets_Shared_PagesPluginsAbstract)
                {
                    if(method_exists($application,'buildPages'))
                    {
                        $pagesList = $application->getPages();
                    }
                }
            }

            if(count($pagesList))
            {
                $apps[$app['app_id']] = $pagesList;
            }
        }
        return $apps;
    }
}
    
    