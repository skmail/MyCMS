<?php

class App_Users_Shared_Validator_UserPageAvailable
    extends Zend_Validate_Abstract{
    
    
    const NOT_AVAILABLE = 'notAvailable';

    protected $_messageTemplates = array(
        self::NOT_AVAILABLE => 'user_page_url_not_available'
    );
    
    
    
    public function isValid($userPageUrl)
    {

        $db = Zend_Registry::get('db');
        
        $userPageQuery = $db->select()->from('users_pages');

        $userPageQuery->where('user_page_url = ? ',$userPageUrl);

        $request = Zend_Controller_Front::getInstance()->getRequest();

        if($request->getPost('do') == 'edit')
        {
            $userPageQuery->where('user_page_id != ?',$request->getPost('user_page_id'));
        }

        $row = $db->fetchRow($userPageQuery);
        
        if(!$row)
        {
            return true;
        }
        
        $this->_error(self::NOT_AVAILABLE);
        return false;
    }
    
}
