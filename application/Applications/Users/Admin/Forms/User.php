<?php

class App_Users_Admin_Forms_User extends MC_Admin_Form_BaseForm {

    public function init() {

        
        $userForm = new MC_Admin_Form_SubForm();
        
        $username = $userForm->createElement('text', 'username', array('label' => 'username', 'required' => true));
        $username->addPrefixPath('App_Users_Shared_Validator', APPLICATION_PATH.'/Applications/Users/Shared/Validator', 'validate');
        $username->addValidator('UsernameAvailable');
        $username->setDecorators(MC_Admin_Form_Form::$elementDecorators);

        $userForm->addElement($username);

        
        $email = $userForm->createElement('text', 'email', array('label' => 'Email', 'required' => true));
        $email->addPrefixPath('App_Users_Shared_Validator', APPLICATION_PATH.'/Applications/Users/Shared/Validator', 'validate');

        $email->addPrefixPath('Zend_Validate', 'Zend/Validate', 'validate');
        $email->addValidator('EmailAvailable');
        $email->addValidator('EmailAddress');
        $email->setDecorators(MC_Admin_Form_Form::$elementDecorators);

        $userForm->addElement($email);


        $password = new Zend_Form_Element_Password('password');
        $password->setLabel('Password');
        
        $password->setDecorators(MC_Admin_Form_Form::$elementDecorators);
        
        
        $confirmPassword = new Zend_Form_Element_Password('confirm_password');
        $confirmPassword->setLabel('Confirm password')
                ->addValidator(new Zend_Validate_Identical('password'));
        
        $confirmPassword->setDecorators(MC_Admin_Form_Form::$elementDecorators);

        $userForm->addElement($password);
        $userForm->addElement($confirmPassword);
        
        
        $this->addSubForm($userForm, 'user');
        
        $this->addElement('hidden', 'user_id');

        $this->addElement('hidden', 'do');

        $this->addElement('submit', 'submit');
    }

}