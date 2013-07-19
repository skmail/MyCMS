<?php

abstract class App_Search_Shared_SearchAbstract
{

    protected $_mapper = array();

    abstract function search();

    protected function setMapper($key,$equ)
    {
        $this->_mapper[$key] = $equ;
    }
    public function getMapper($key)
    {
        if(!$this->_mapper[$key])
        {
            return false;
        }

        return $this->_mapper[$key];
    }

}