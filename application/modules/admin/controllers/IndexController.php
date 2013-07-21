<?php

class Admin_IndexController extends MC_Admin_Controller_Action
{

    public function init()
    {

        parent::init();

    }

    public function indexAction()
    {
        $this->forward('window','ajax');
    }

    public function layout()
    {


    }
}