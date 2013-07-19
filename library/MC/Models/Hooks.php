<?php

class MC_Models_Hooks
{

    public function call($hookName, &$data = array(),&$data2 = array())
    {

        $db = Zend_Registry::get('db');

        $query = $db->select()->from('hooks')->where('event = ?', $hookName)->where('status = 1');

        $results = $db->fetchAll($query);

        if ($results)
        {
            foreach ($results as $row)
            {

                $class = MC_Core_Loader::hook($row['hook_name']);

                if($class)
                {
                    if(method_exists($class, $row['method']))
                    {
                        $class->$row['method'](&$data,$data2);
                    }
                }
            }
        }

        return true;

    }

}