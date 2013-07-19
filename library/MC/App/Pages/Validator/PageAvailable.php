<?php

class MC_App_Pages_Validator_PageAvailable extends Zend_Validate_Abstract
{
    
    
    const NOT_AVAILABLE = 'notAvailable';

    protected $_messageTemplates = array(
        self::NOT_AVAILABLE => 'Page already exists'
    );
    
    
    public function isValid($page_url)
    {
        
        $page_url = MC_Models_Url::friendly($page_url);
        
        $db = Zend_Registry::get('db');
        
        $pageQuery = $db->select()->from('pages');
        
        $pageQuery->where('page_url = ?',$page_url);
        
        if(!$pageQuery)
        {
            return true;
        }
        
        
        $this->_error(self::NOT_AVAILABLE);
        
        return false;

    }
    
}