<?php
class App_Users_Admin_Forms_Usergroup extends MC_Admin_Form_BaseForm {
    public function init() {
        $usergroupLang = new App_Users_Admin_Forms_UsergroupLang();
        $usergroupLang->setElementsBelongTo('');
        $this->addSubForm($usergroupLang, 'usergroup_lang');
        $this->addElement('hidden','usergroup_id');
        $this->addElement('hidden', 'do');
        $this->addElement('submit', 'submit');
    }
}