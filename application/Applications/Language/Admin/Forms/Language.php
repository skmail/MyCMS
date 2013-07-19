<?php

class App_Language_Admin_Forms_Language extends MC_Admin_Form_BaseForm
{

    public function init()
    {


        $this->addElement('text', 'lang_name', array('decorators' => MC_Admin_Form_Form::$elementDecorators, 'required'   => true, 'label'      => 'Language name'));


        $this->addElement('text', 'short_lang', array('class'=>'input-mini','decorators' => MC_Admin_Form_Form::$elementDecorators, 'required'   => true, 'label'      => 'Language code'));

        $dir = new Zend_Form_Element_Select('dir');
        $dir->setRequired(true)
                ->setLabel('Layout direction')
                ->setMultiOptions(array('ltr'        => 'ltr', 'rtl'  => 'rtl'))
                ->setSeparator('&nbsp;')
                ->setAttrib('class', 'input-small2')
                ->setDecorators(MC_Admin_Form_Form::$elementDecorators)
        ;

        $this->addElement($dir);

        $langStatus = new Zend_Form_Element_Select('lang_status');
        $langStatus->setRequired(true)
                ->setLabel('Language Status')
                ->setMultiOptions(array('1'        => 'Active', '0'  => 'inActive'))
                ->setSeparator('&nbsp;')
                ->setAttrib('class', 'input-small2')
                ->setDecorators(MC_Admin_Form_Form::$elementDecorators);
        
       $this->addElement($langStatus);

        
       $this->addElement('checkbox', 'lang_default',  array('label' => 'Set language as default','value'=>'1','checked'=>false));

       $this->addElement('hidden','lang_id');
       $this->addElement('hidden','do');
       
       
       $this->addElement('submit','submit');
        
    }

}