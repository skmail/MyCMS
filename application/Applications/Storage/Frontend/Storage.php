<?php

class App_Storage_Frontend_Storage extends Frontend_Model_Applications_Application {
    
    private $_category = '';
    private $_item  = '';
    
    
    public $data = array();
    
    public function init($appRow){
        $this->data = array_merge($this->data,$appRow);
        $request = $this->_Zend->getRequest();
        
        
        return $this->_router();
    }

    
    public function _router(){

        if($this->_Zend->getRequest()->getParam('image')!=""){
            $options = $this->_Zend->getRequest()->getParams();
            return $this->_resizeImage($options);
        }
        
    }
    
    
    
    protected function _resizeImage($options){





        $cropModel = new MC_App_Storage_ResizeImage($options);
        
          
        
        exit;
        
    }
}
    
    
    