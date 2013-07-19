<?php

class App_HomePage_Frontend_HomePage extends Frontend_Model_Applications_Application {

    public $data = array();

    public function init($appRow){

        $this->data = array_merge($this->data,$appRow);

        $this->_Zend->getRequest()->setParam('homepage','home');


    }
}