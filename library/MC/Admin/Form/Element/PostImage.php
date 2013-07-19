<?php

class MC_Admin_Form_Element_PostImage extends Zend_Form_Element
{
     
   public $helper = 'PostImage';
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