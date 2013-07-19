<?php

class App_Language_Admin_Forms
{
    
    public function __construct($application = array())
    {
        $this->application = $application;
    }
    
    
    public function phraseForm($data = array()){
        
        $phraseForm = new App_Language_Admin_Forms_Phrase(array('action'=>$this->application['url'].'window/savePhrase'));
        
        
        $phraseForm->populate($data);
        
        return $phraseForm;
        
    }
    
    
    
    
    public function languageForm($data = array())
    {
        
        $languageForm = new App_Language_Admin_Forms_Language(array('action'=>$this->application['url'].'window/saveLanguage'));
        
        
        $languageForm->populate($data);
        
        return $languageForm;
        
    }
    
    
}