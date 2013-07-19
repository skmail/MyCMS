<?php


class App_Settings_Admin_Forms_Settings extends MC_Admin_Form_SubForm
{

    public function init($options = array())
    {
       
        
        $this->addElement('text','site_url',array('required'=>true,'label'=>'site_url','class'=>'large-input ltr'));
        
        $this->addElement('text','assets_url',array('required'=>true,'label'=>'assets_url','class'=>'large-input ltr'));
        
        
    }
    
}