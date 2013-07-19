<?php

class MC_Admin_Controller_Action extends MC_Controller_Action_MainController
{

    public function preDispatch()
    {

        return true;
        if (Zend_Auth::getInstance()->getIdentity())
        {
            if ($this->getRequest()->getControllerName() == "login"
                    && $this->getRequest()->getActionName() != "logout")
            {
                $this->_helper->redirector('index', 'index');
            }
        }
        else
        {
            if ($this->getRequest()->getControllerName() != "login")
            {
                $this->_helper->redirector('index', 'login');
            }
        }

    }

    public function init()
    {
        parent::init();

        //$this->view->headTitle()->prepend('Welcome to My World');

    }

}