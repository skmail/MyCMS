<?php

class App_Widgets_Admin_Permissions extends MC_Models_Permissions_Abstract
{

    
    public function perms()
    {
        return array('duplicate' => array('grid','savegrid'));
    }

}