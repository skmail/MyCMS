<?php

class Admin_Model_System_TableDependency
{

    public function getTables()
    {

        $db = Zend_Registry::get('db');

        $applications = $db->select()->from('Applications')->where;

    }

}

