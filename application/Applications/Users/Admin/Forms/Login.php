<?php

class App_Users_Admin_Forms_Login extends MC_Admin_Form_BaseForm
{

    public function init()
    {
        $this->setMethod('post');
        $username = $this->addElement('text','username',array(
            'required'=>true,
            'placeholder'=>'Username',
            'filters'=>array('StringTrim','StringToLower'),
            'validators'=>array('Alpha'),
            'required'=>true
        ));
        $password = $this->addElement('password','password',array(
            'required'=>true,
            'placeholder'=>'Password',
        ));
        $login = $this->addElement('submit','Login');
        $this->setDecorators(array(
            'formElements',
            array('HtmlTag',array('tag'=>'dl','class'=>'loginForm')),
            array('Description',array('placement'=>'prepend')),

            'Form'
        ));


    }
}

