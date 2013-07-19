<?php

class App_Language_Shared_Validator_PhraseAvailable
{
    
    public function isAvailable($phrase_name){
        
        $db = Zend_Registry::get('db');
        
        $phrase_id = intval(Zend_Controller_Front::getInstance()->getRequest()->getPost('phrase_id'));
        
        $phraseQuery = $db->select()->from('language_phrases');
    
        $phraseQuery->where('phrase_name = ? ',$phrase_name);
        
        $row = $db->fetchRow($phraseQuery);
        
        if(!$row)
        {
            return true;
        }
   
        return false;
        
    }
    
}
