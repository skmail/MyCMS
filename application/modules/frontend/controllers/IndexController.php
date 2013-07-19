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

        $doc = new MC_Models_Grid_Grid960_Grid960();

        $dc = $doc->getGrid('grid_12');

        $coreStart = new Frontend_Model_Core_Start();

        $coreStart->run();
    }

}