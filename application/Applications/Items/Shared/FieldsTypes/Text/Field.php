<?php

class App_Items_Shared_FieldsTypes_Text_Field extends App_Items_Shared_Core_FieldType
{
    public $name   = "Text";

    public function init()
    {

        return parent::init();
    }

    public function settings()
    {
        return new MC_Admin_Form_SubForm();
    }
}