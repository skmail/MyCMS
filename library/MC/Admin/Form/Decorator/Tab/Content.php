<?php

class MC_Admin_Form_Decorator_Tab_Content extends Zend_Form_Decorator_Abstract{
    
    
    
    
    public function render($content) {
        
        $options = $this->getOption('options');

        return "<div class='tab-pane " .$options['active']. "' id='".$options['id']."'>".$content."</div>";
    }
    
}