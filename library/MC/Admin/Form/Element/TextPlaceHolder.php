<?php

class MC_Admin_Form_Element_TextPlaceHolder extends Zend_Form_Element_Xhtml
{
     
   public $helper = 'TextPlaceHolder';
   protected $value = null ;
   
   public function setValue($value)
   {
       $this->value = $value;
      
   }
   
   public function getValue()
   {
       return $this->value;
   }
   
}