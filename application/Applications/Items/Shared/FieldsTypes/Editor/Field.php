<?php

class App_Items_Shared_FieldsTypes_Editor_Field extends App_Items_Shared_Core_FieldType
{
    public $helper = "Editor";
    public $name   = "Editor";


    public function init()
    {
        return parent::init();
    }

    public function settings()
    {
        return new MC_Admin_Form_SubForm();
    }

    public static function setFieldValue($field)
    {

        self::$_fieldErrors[] = 's';
        return $field;
    }
}