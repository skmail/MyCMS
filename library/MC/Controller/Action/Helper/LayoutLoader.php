<?php

class MC_controller_Action_Helper_LayoutLoader extends Zend_Controller_Action_Helper_Abstract
{

    public function preDispatch()
    {

        $bootstrap = $this->getActionController()->getInvokeArg('bootstrap');

        $config = $bootstrap->getOptions();

        $module = $this->getRequest()->getModuleName();

        $controller = $this->getRequest()->getControllerName();



        if (isset($config[$module]['resources']['layout']['layout'])
                || isset($config[$module][$controller]['resources']['layout']['layout']))
        {


            if (isset($config[$module][$controller]['resources']['layout']['layout']))
                $layoutScript = $config[$module][$controller]['resources']['layout']['layout'];
            else
            {
                $theme = $this->getRequest()->getParam('theme');
                if (isset($theme))
                {
                    if (in_array($theme, $config[$module]['resources']['layout']['layoutDir']))
                    {
                        $layoutScript = $theme . '/' . $config[$module]['resources']['layout']['layout'];
                        $themeModel = new Admin_Model_System_Theme();
                        $themeModel->setTheme($theme);
                    }
                }else
                    $layoutScript = 'default' . '/' . $config[$module]['resources']['layout']['layout'];
            }




            $this->getActionController()
                    ->getHelper('layout')
                    ->setLayout($layoutScript);
        }

    }

}