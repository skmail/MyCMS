<?php

class Admin_LoginController extends MC_Admin_Controller_Action
{

    public function init()
    {
        parent::init();

    }

    public function getAuthAdapter(array $params)
    {

        Zend_Registry::_unsetInstance('db');
        
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        
        $authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);

        $authAdapter->setTableName('users')
                ->setIdentityColumn('username')->
                setCredentialColumn('password')->setCredentialTreatment('MD5(?)');

        return $authAdapter->setIdentity($params['username'])->setCredential($params['password']);

    }

    public function LoginForm()
    {

        $form = new Admin_Form_Login(array('action' => 'login/checklogin'));
        
        return $form;

    }

    public function indexAction()
    {

        $this->view->loginForm = $this->LoginForm();

    }

    public function checkloginAction()
    {

        $request = $this->getRequest();

        if (!$request->isPost())
        {
            $this->_helper->redirector('index', 'login');
        }

        $form = $this->LoginForm();

        if (!$form->isValid($request->getPost()))
        {
            $this->view->loginForm = $form;
            return $this->render('index');
        }

        $params = $form->getValues();

        $adapter = $this->getAuthAdapter(array_shift($params));
      
        $auth = Zend_Auth::getInstance();

        $result = $auth->authenticate($adapter);

        if (!$result->isValid())
        {
            $form->setDescription("Invalid Creditnitial Provided");

            $this->view->loginForm = $form;

            return $this->render('index');
        }
        else
        {
            $this->_helper->redirector('index', 'index');
        }

    }

    public function logoutAction()
    {

        Zend_Auth::getInstance()->clearIdentity();

        $this->_helper->redirector('index', 'login');

    }

}