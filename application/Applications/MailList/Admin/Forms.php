<?php

class App_MailList_Admin_Forms 
{

    
    public function __construct($application = array())
    {
        $this->application = $application;
    }

    
    public function group()
    {

        $form = new App_MailList_Admin_Forms_Group();
        
        
        
        return $form;
        
    }

}