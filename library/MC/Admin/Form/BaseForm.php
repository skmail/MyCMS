<?php

class MC_Admin_Form_BaseForm extends MC_Admin_Form_Form
{
    
    public function __construct($options = array())
    {
        $this->setDecorators(array(
                             'FormElements',
                             array('HtmlTag', array('tag' => 'div', 'class' => 'form')),
                             'form'));
        $this->removeDecorator('DtDdWrapper');
        $this->setTranslator(MC_Core_Instance::getInstance()->lang);
        parent::__construct($options);
    }
}
 