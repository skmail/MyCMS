<?php

class App_Plugins_Admin_Permissions extends MC_Models_Permissions_Abstract
{

    
    public function perms()
    {
        return array('duplicate' => array('grid','savegrid'));
    }

}