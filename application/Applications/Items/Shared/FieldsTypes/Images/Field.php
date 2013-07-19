<?php

class App_Items_Shared_FieldsTypes_Images_Field extends Zend_Form_Element_Xhtml
{
    public $helper = "Images";
    public $name   = "Images";


    public function init()
    {
        return parent::init();
    }

    public function settings()
    {
        return new MC_Admin_Form_SubForm();
    }
}