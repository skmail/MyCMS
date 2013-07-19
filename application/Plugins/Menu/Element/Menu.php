<?php

class Plugins_Element_Element_Menu extends Zend_Form_Element_Xhtml
{
     
   public $helper = 'Menu';
   
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