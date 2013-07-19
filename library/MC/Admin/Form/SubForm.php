<?php

class MC_Admin_Form_SubForm extends MC_Admin_Form_Form
{
    
    public function __construct($options = array())
    {
        $this->setDecorators(array(
                             'FormElements',
                             array('HtmlTag', array('tag' => 'div', 'class' => 'sub-form'))
                            ));




        parent::__construct($options);



    }


    public function init()
    {
        return $this;
    }
}
 