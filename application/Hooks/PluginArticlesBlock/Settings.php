<?php

class Hooks_PluginArticlesBlock_Settings
{


    public function setOptions()
    {
        $form = new MC_Admin_Form_SubForm();

        $form->addElement('text','ss',array('label'=>'Max. rows'));

        $form->addElement('text','sss',array('label'=>'allow pagination'));

        return $form;
    }


}