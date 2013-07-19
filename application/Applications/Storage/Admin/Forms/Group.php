<?php

class App_Storage_Admin_Forms_Group extends MC_Admin_Form_BaseForm {

    public function init() {

        $groupLang = new App_Storage_Admin_Forms_GroupLang();

        $groupLang->setElementsBelongTo('');
        
        $this->addSubForm($groupLang, 'group_lang');
        
        
        $this->addElement('text','folder',array('required'=>true,'label'=>'Destination folder'));
        $this->addElement('text','source_folder',array('required'=>true,'label'=>'Soruce folder'));
        
        
        $this->addElement('hidden', 'old_folder_name');
        $this->addElement('hidden', 'old_source_folder');
        
        
        $this->addElement('hidden', 'group_id');
        

        $this->addElement('hidden', 'do');

        $this->addElement('submit', 'go');
    }

}