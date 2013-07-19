<?php


class App_Search_Admin_Forms_Settings extends MC_Admin_Form_SubForm
{

    public function init($options = array())
    {
       
        $this->addSubForm(new Plugins_ArticlesBlocks_Form(),'set');
        
    }
    
}