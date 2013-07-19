<?php

abstract class MC_Models_Permissions_Abstract
/* implements MC_Models_Permissions_Interface */
{

    private $permissions = array();

    public $initialPermissions = array();

    //abstract function setEntities();



    private function _rebuildPermissions()
    {

        foreach ($this->initialPermissions as $table => $permVal)
        {

            $this->permissions[$table] = $this->_rebuildPermission($perm, $permVal);
        }

    }

    private function _rebuildPermission($primaryKey, $data)
    {

        $array = array();

        foreach ($data as $key => $val)
        {
            $array[$key]['id'] = $val['data'][$primaryKey];
            $array[$key]['idName'] = $primaryKey;
            $array[$key]['label'] = $val['label'];
            $array[$key]['labelName'] = $val['data'][$val['label']];
        }

        $this->permissions = $array;

    }

    final public function getTables()
    {
        if (method_exists($this, 'setEntities'))
        {
            $this->setEntities();

            return $this->permissions;
        }
        else
        {
            return false;
        }

    }

    function addEntity($table, $primaryKey, $label, array $data)
    {

        $this->permissions[$table] = array(
            'key'   => $primaryKey,
            'label' => $label,
            'data'  => $data);

    }

    //  abstract function perm();
    //  abstract function checkPerms($method);

}