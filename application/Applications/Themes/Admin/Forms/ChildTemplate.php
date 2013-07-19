<?php

class App_Themes_Admin_Forms_ChildTemplate extends MC_Admin_Form_SubForm
{

    function init($options = array())
    {

        $counter = $this->getAttrib('c');
        $this->removeAttrib('c');



        $this->addElement('text', 'template_name', array(
            'label'      => 'Template Name [' . $counter . ']',
            'maxLength'  => '255',
            'decorators' => MC_Admin_Form_Form::$elementDecorators
        ));

        $this->addElement('textarea', 'template_content', array(
            'label'      => 'Template Content [' . $counter . ']',
            'class'      => 'ltr editor_textarea',
            'decorators' => MC_Admin_Form_Form::$elementDecorators,
            'rows'       => 20,
            'cols'       => 20
        ));

    }

}
