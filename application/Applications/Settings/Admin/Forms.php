<?php

class App_Settings_Admin_Forms
{

    public function __construct($application = array())
    {

        $this->application = $application;

    }

    public function settings($data = array(), $formInstance)
    {

        
        $options = array();
        
        $options['action'] = $this->application['url'].'window/saveSettings';

        $form = new App_Settings_Admin_Forms_SettingsWrapper($options);

        $form->addSubForm($formInstance, 'settings',1);

        $form->populate($data);


        return $form;

    }

}