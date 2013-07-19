<?php

class App_Themes_Admin_Forms_Category extends MC_Admin_Form_BaseForm {

    public function init() {

        /* Form Elements & Other Definitions Here ... */

        
        $this->setMethod('post');

        $this->addElement('text', 'cat_name', array(
            'required' => true,
            'label' => 'Category Name',
            'maxLength' => '255'
        ));


        $this->addElement('hidden', 'theme_id');
        $this->addElement('hidden', 'cat_id');

        $this->addElement('hidden', 'do');
        $this->addElement('submit', 'go', array('label' => 'Edit', 'class' => 'submit'));


       
    }

}

