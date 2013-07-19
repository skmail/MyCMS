<?php


class App_Settings_Admin_Forms_SettingsWrapper extends MC_Admin_Form_BaseForm
{

    public function init($options = array())
    {
       
        
        
        $this->addElement('hidden','app_prefix',array('order'=>2));
        $this->addElement('hidden','do',array('order'=>3,'value'=>'edit'));
        
        $this->addElement('submit','go',array('order'=>4));
    }
    
}