<?php

class App_Users_Admin_Forms_UserPage extends MC_Admin_Form_BaseForm {

    public function init() {

        $this->addSubForm(new App_Users_Admin_Forms_UserPageLang(),'user_page_lang');

        $pageUrl = $this->createElement('text','user_page_url',array('label'=>'page_url','required'=>true));

        $pageUrl->addPrefixPath('App_Users_Shared_Validator', APPLICATION_PATH.'/Applications/Users/Shared/Validator', 'validate');
        $pageUrl->addValidator('UserPageAvailable');
        $pageUrl->setDecorators(MC_Admin_Form_Form::$elementDecorators);

        $this->addElement($pageUrl);
        $this->addElement('hidden', 'user_page_id');

        $this->addElement('hidden', 'do');

        $this->addElement('submit', 'submit');
    }

}