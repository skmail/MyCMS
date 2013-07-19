<?php

class MC_App_Language_Shared_Validator_PhraseCharacters
    extends Zend_Validate_Abstract
{
    
    public function isValid($value)
    {
     if (!preg_match("/[^\x00-\x7F]/",$value)) {
        return true;
         
     }  
        return false;
        
    }
    
}
