<?php

class App_Users_Admin_Permissions
{

    public function defaultPerms()
    {

        $perms = array('view'=>array(), 'add'=>array(), 'edit'=>array(), 'delete'=>array());

        return $perms;

    }

}