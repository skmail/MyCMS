<?php

class App_Items_Admin_Forms_CategorySubform extends MC_Admin_Form_BaseForm {

    public function init() {
     
        
        $this->addElement('text', 'cat_url', array(
            'label' => 'Category Url',
            'filters' => array('StringTrim', 'StringToLower')
        ));


        $catStatus = $this->createElement('select', 'cat_status',array(
                                            'decorators'=>MC_Admin_Form_Form::$elementDecorators))
                          ->setLabel('Status: ')
                          ->setRequired(true);

        $catStatus->addMultiOptions(array(1 => 'Active', 2 => 'Hidden'));
        
        $this->addElement($catStatus);
        
        
        
        
    }
    }