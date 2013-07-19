<?php

class Admin_View_Helper_CheckActiveMenu {
    
    
    
    public function CheckActiveMenu($pointers){
        
        $request = 
        $this->_Zend = Zend_Controller_Front::getInstance()->getRequest();
        
        $active = array();
        
        if(!is_array($pointers))
            return false;
        
        if(count($active) == false)
            return;
        
        foreach($pointers as $pointerK=>$pointer){
            
            if($request->getParam($pointerK) == $pointer)
                $active[] = true;
            
        }
        
        if(count($active) === count($pointers))
            return true;
        
        
        return false;
        
        
        
    }
    
}