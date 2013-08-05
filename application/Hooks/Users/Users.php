<?php
class Hooks_Users_Users {
    public function loginForm($controllerParams)
    {
        $this->MC =& MC_Core_Instance::getInstance();
        if (Zend_Auth::getInstance()->getIdentity()){
            if ($this->MC->Zend->getRequest()->getParam('appPrefix') == "users"
                && $this->MC->Zend->getRequest()->getActionName() != "logout"){
            }
        }else{
            if (($this->MC->Zend->getRequest()->getParam('appPrefix') != "users" && $this->MC->Zend->getRequest()->getParam('window') != "login") && ($this->MC->Zend->getRequest()->getParam('appPrefix') != "users" && $this->MC->Zend->getRequest()->getParam('window') != "submitLogin") ){
                $this->MC->Zend->getRequest()->setParam('appPrefix','users');
                $this->MC->Zend->getRequest()->setParam('window','login');
            }
        }
    }

    public function adminUserMenu($array)
    {
        $array[] = array('label'=>'my_profile');
        $array[] = array('label'=>'logout','url'=>'/admin/users/window/logout','attribs'=>array('class'=>'disAjax'));
    }
}