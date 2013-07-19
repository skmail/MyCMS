<?php

class Frontend_Model_Applications_MailList_MailList extends Frontend_Model_Applications_Application {
    
    
    


     public function init($appRow){
        
        if($this->_Zend->getRequest()->getParam('do') == 'subscription')
        {
           return $this->subscription();
        }
        
    }
 
    
    
    protected function subscription()
    {
        
        $email = $this->_Zend->getRequest()->getPost('email');
        
        $groupId = $this->_Zend->getRequest()->getPost('group_id');
        $groupId = intval($groupId);
        $errors = array();
       
         
        if(empty($email))
        {
           $this->data = json_encode(array('error'=>$this->translate('email_input_is_empty')));
        }
        else if(!filter_var($email, FILTER_VALIDATE_EMAIL))
        {
            $this->data = json_encode(array('error'=>$this->translate('invalid_email_input')));
        }
        else
        {
           
            $db = Zend_Registry::get('db');
            
            $checkGroup = $db->select()->from('maillist_group');
            $checkGroup->where('group_id = ? ',$groupId);
            
            $checkEmail = $db->select()->from('maillist_mails');
            $checkEmail->where('email = ? ',$email);
            $checkEmail->where('group_id = ? ',$groupId);
                       
            if($checkGroup){
                $this->data = json_encode(array('error'=>$this->translate('maillist_group_not_exists')));
            }else
            if($db->fetchAll($checkEmail))
            {
                $this->data = json_encode(array('error'=>$this->translate('email_already_registered')));
            }
            else
            {
                $db->insert('maillist_mails',array('email'=>$email,'group_id'=>$groupId));
                $this->data = json_encode(array('success'=>$this->translate('you_are_subscribe_now')));
            }
            
        }
        
        return true;
    }


     protected function translate($phrase)
    {
        
         
        return  Zend_Registry::get('Zend_Translate')->translate($phrase);
      
        
        
    }
}