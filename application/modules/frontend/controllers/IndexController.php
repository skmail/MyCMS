<?php

class Frontend_IndexController extends Zend_Controller_Action
{

    public function indexAction()
    {
        $this->app();
    }

    public function app()
    {

        $this->_helper->viewRenderer->setNoRender(true);

        $coreStart = new Frontend_Model_Core_Start();

        $coreStart->run();
    }

}