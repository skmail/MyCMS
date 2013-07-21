<?php

class App_Widgets_Admin_Forms_Grid extends MC_Admin_Form_BaseForm {

    public function init() {

        $this->setAttrib('class', 'saveGroup');
        $data = $this->getAttrib('data');
        $this->removeAttrib('data');

        $gridLang = new App_Widgets_Admin_Forms_GridLang();

        $gridLang->setElementsBelongTo('');

        $this->addSubForm($gridLang, 'grid_lang');


        $themes = $this->createElement('select', "theme_id", array('label' => 'Theme', 'required' => 'true', 'decorators' => MC_Admin_Form_Form::$elementDecorators));
        
        foreach (MC_App_Themes_Themes::themes() as $theme) 
        {
            $themes->addMultiOption($theme['theme_id'], $theme['theme_name']);
        }

        $this->addElement($themes);

        $gridStatus = $this->createElement('select', 'grid_status', array(
                    'decorators' => MC_Admin_Form_Form::$elementDecorators))->setLabel('Grid status')->setRequired(TRUE);

        $gridStatus->addMultiOption(1, 'Active');

        $gridStatus->addMultiOption(0, 'Hidden');
        
        $this->addElement($gridStatus);

        $this->addSubForm(new App_Widgets_Admin_Forms_GirdParams(array('data'=>$data)), 'params');

        $this->addElement('hidden', 'do', array('required' => true));

        $this->addElement('hidden', 'grid_id')->removeDecorator("DtDdWrapper");

        $this->addElement('submit', 'go', array('label' => 'Edit', 'class' => 'submit', 'order' => 7));

    }

}

