<?php

class Custom_Admin_Form_DoJo extends Custom_Admin_Form_Form
{
    
    public function __construct($options = array())
    {
        
        
        parent::__construct($options);
        
        $this->doJo =  new Zend_Dojo_Form_SubForm();
        
        
    }
     
}
 