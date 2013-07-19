<?php

class App_Items_Shared_Core_FieldType extends Zend_Form_Element_Xhtml
{

    public  $_helper = null;
    protected $value = null ;
    protected $data = NULL;
    public static $_fieldErrors = array();

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getData()
    {
        return $this->data;
    }
    public function setData($data)
    {
        return $this->data = $data;
    }

    public static  function setFieldValue($fieldValue)
    {
        return $fieldValue;
    }

}