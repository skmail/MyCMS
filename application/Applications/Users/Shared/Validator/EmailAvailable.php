<?php

class App_Users_Shared_Validator_EmailAvailable
    extends Zend_Validate_Abstract{
    
    
    const NOT_AVAILABLE = 'notAvailable';

    protected $_messageTemplates = array(
        self::NOT_AVAILABLE => 'email_not_available'
    );
    
    
    
    public function isValid($email) {
        
        
        $db = Zend_Registry::get('db');
        
        $emailQuery = $db->select()->from('users');
       
        $emailQuery->where('email = ? ',$email);

        $request = Zend_Controller_Front::getInstance()->getRequest();

        if($request->getPost('do') == 'edit')
        {
            $emailQuery->where('user_id != ?',$request->getPost('user_id'));
        }

        $row = $db->fetchRow($emailQuery);
        
        if(!$row)
        {
            return true;
        }
        
        $this->_error(self::NOT_AVAILABLE);
        
        return false;
        
    }
    
}
