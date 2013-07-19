<?php

class Admin_View_Helper_Html extends Zend_View_Helper_Abstract
{

    public function html()
    {
        return $this;

    }

    public function __call($name, $arguments)
    {
        
        
        $html = new MC_Models_Html_Html();
        
        
        if (method_exists($html, "{$name}"))
        {
            return $html->$name();
        }
        else
        {
            $class_name = 'MC_Models_Html_'.ucfirst($name);
        
            return new $class_name($arguments);
        }

    }

}


