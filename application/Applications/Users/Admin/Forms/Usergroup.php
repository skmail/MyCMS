<?php

class App_Users_Admin_Forms_Usergroup extends MC_Admin_Form_BaseForm {

    public function init() {


        

        $usergroup_lang = new App_Users_Admin_Forms_UsergroupLang();


        $usergroup_lang->removeDecorator('DtDdWrapper');

           

        $this->addSubForm($usergroup_lang, 'usergroup_lang');
        
        

        $this->addElement('hidden','usergroup_id');
        

        $this->addElement('hidden', 'do');

        $this->addElement('submit', 'submit');
    }

}