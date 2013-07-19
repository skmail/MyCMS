<?php

class App_Users_Admin_Functions
{

    public function __construct($application)
    {
        $this->application = $application;
    }

    public function permissionsUrl($options)
    {
        $params = array();
        
        foreach ($options as $key => $val)
        {
            $params[] = $key . '/' . $val;
        }

        $params = implode('/', $params);

        $url = $this->application['url'] . 'window/permissions/' . $params;

        return $url;

    }

    public function sideMenu()
    {
        return array(
            array(
                'title' => 'Users Search',
                'url'   => '#'
            ),
            array(
                'title' => 'Add new usergroup',
                'url'   => 'window/usergroup/do/add'
            ),
            array(
                'title' => 'IP Addresses',
                'url'   => '#'
            )
        );

    }


    public function userPageUrl(array $options = array())
    {
        if(isset($options['user_page_id']))
        {
            return $this->application['url'].'window/page/do/edit/user_page_id/'.$options['user_page_id'];
        }
    }

}