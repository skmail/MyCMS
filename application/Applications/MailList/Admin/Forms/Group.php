<?php

class App_MailList_Admin_Forms_Group extends MC_Admin_Form_BaseForm {

    public function init() {

        
        $groupLang = new App_MailList_Admin_Forms_GroupLang();
        $groupLang->setElementsBelongTo('');
        
        
        $this->addSubForm($groupLang, 'group_lang');
        
        
        $this->addElement('hidden', 'do', array('required' => true));
        $this->addElement('hidden', 'group_id')->removeDecorator("DtDdWrapper");
        $this->addElement('submit', 'go');
    }

}

