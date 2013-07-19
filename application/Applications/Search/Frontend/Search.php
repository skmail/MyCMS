<?php

class App_Search_Frontend_Search extends Frontend_Model_Applications_Application {

    public function init($appRow){

        print_R($appRow);

        die();
        $apps = $this->getSearchableApplications(MC_Core_Applications::getApp(array('listAll'=>true)));


        foreach($apps as $app)
        {
          $results =  $app['searchObject']->search(array('keyword'=>$_GET['keyword']));

          if(count($results) > 0)
          {
            foreach($results as $result_id=>$result)
            {
                foreach($result as $key=>$val)
                {
                    if($app['searchObject']->getMapper($key) !== false)
                    {
                        $results[$result_id][$app['searchObject']->getMapper($key)] = $val;
                        unset($results[$result_id][$key]);
                    }
                }
            }
          }

        }
        print_r($results);
        die();

    }

    protected function  getSearchableApplications(array $applications)
    {
        $searchableApplications = array();
        foreach($applications as $appId=>$app)
        {
            if(!class_exists('App_'.ucfirst($app['app_prefix'])."_Shared_Search"))
            {
                continue;
            }
            $searchClassName = 'App_'.ucfirst($app['app_prefix'])."_Shared_Search";

            $searchObject = new $searchClassName();

            if(!method_exists($searchObject,'search'))
            {
                continue;
            }


            $searchableApplications[$appId] = $app;
            $searchableApplications[$appId]['searchObject'] = $searchObject;
        }

        return $searchableApplications;
    }
}