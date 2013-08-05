<?php

class App_Users_Shared_Libraries_Forms
{

    public function __construct()
    {
        $this->MC =& MC_Core_Instance::getInstance();
    }

    public function usergroupForm($usergroupData = array())
    {
        $form = new App_Users_Admin_Forms_Usergroup(array('action' => $this->MC->application['url'] . 'window/saveUsergroup'));
        $form->populate($usergroupData);
        return $form;
    }

    public function userForm($userData = array())
    {
        $form = new App_Users_Admin_Forms_User(array('user'=>$userData,'action' => $this->MC->application['url'] . 'window/saveUser'));
        
        $form->populate($userData);

        return $form;

    }


    public function userPage($data = array())
    {

        $options = array();
        $options['action'] = $this->MC->application['url'].'window/saveUserPage';
        $form = new App_Users_Admin_Forms_UserPage($options);

        $form->populate($data);

        return $form;
    }



    public function login($data = array())
    {
        $options = array();
        $options['action'] = $this->MC->application['url'].'window/submitLogin';
        $form = new App_Users_Admin_Forms_Login($options);
        $form->populate($data);
        return $form;
    }




}