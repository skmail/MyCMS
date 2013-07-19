<?php

class App_Users_Shared_Validator_UsernameAvailable
    extends Zend_Validate_Abstract{
    
    
    const NOT_AVAILABLE = 'notAvailable';

    protected $_messageTemplates = array(
        self::NOT_AVAILABLE => 'username_not_available'
    );
    
    
    public function isValid($username) {
        
        $db = Zend_Registry::get('db');


        $request = Zend_Controller_Front::getInstance()->getRequest();

        $usernameQuery = $db->select()->from('users');
    
        $usernameQuery->where('username = ? ',$username);

        if($request->getPost('do') == 'edit')
        {
            $usernameQuery->where('user_id != ?',$request->getPost('user_id'));
        }

        $row = $db->fetchRow($usernameQuery);
        
        if(!$row)
        {
            return true;
        }
        
        $this->_error(self::NOT_AVAILABLE);
        
        return false;
        
    }
    
}
