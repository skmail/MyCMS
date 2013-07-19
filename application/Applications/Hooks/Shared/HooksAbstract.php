<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mac
 * Date: 5/1/13
 * Time: 4:13 AM
 * To change this template use File | Settings | File Templates.
 */

abstract class App_Hooks_Shared_HooksAbstract {

    protected $view = NULL;




    protected function view()
    {

        if ($this->view == NULL)
        {
            $this->view = new Zend_View();
            $scriptPath = APPLICATION_PATH . '/Hooks/' . $this->childClassName() . '/views';
            $this->view->addScriptPath($scriptPath);
        }

        return $this->view;

    }


    protected function assignToView($key,$value)
    {
        $this->view()->assign($key,$value);
    }

    protected function viewRender($viewFile)
    {
        return $this->view()->render($viewFile);
    }



    private function childClassName()
    {
        return end(explode('_',get_class($this)));
    }
}