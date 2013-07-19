<?php


class App_Themes_Admin_Functions{
    
    public function __construct($application = array())
    {
        
        $this->application = $application;

    }

    public function templateUrl(){

    }

    public function categoryUrl($categoryId,$do='edit')
    {
        return $this->application['url'] = 'window/category/do/'.$do.'/cat_id/'.$categoryId;
    }
    
    
}
