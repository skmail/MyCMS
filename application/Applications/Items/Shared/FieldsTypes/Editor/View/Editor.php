<?php
class App_Items_Shared_FieldsTypes_Editor_View_Editor extends  Zend_View_Helper_FormElement
{
    public function Editor($name,$value,$attr = array())
    {
        return "<textarea name='".$name."' class='editor'>".$value."</textarea>";
    }
}