<?php

class App_Users_Admin_Forms
{

    public function __construct($application)
    {
     
        $this->application = $application;

    }

    public function usergroupForm($usergroupData = array())
    {

        $form = new App_Users_Admin_Forms_Usergroup(array('action' => $this->application['url'] . 'window/saveUsergroup'));
      
        $form->populate($usergroupData);
        
        return $form;

    }

    public function userForm($userData = array())
    {
        $form = new App_Users_Admin_Forms_User(array('action' => $this->application['url'] . 'window/saveUser'));
        
        $form->populate($userData);

        return $form;

    }


    public function userPage($data = array())
    {

        $options = array();
        $options['action'] = $this->application['url'].'window/saveUserPage';
        $form = new App_Users_Admin_Forms_UserPage($options);

        $form->populate($data);

        return $form;
    }

}