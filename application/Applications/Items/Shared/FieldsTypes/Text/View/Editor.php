<?php
class App_Items_Shared_FieldsTypes_Text_View_Text extends  Zend_View_Helper_FormElement
{
    public function Editor($name,$value, $attr = array())
    {
        return "<textarea name='".$name."' class='editor'>".$value."</textarea>";
    }
}