<?php

class MC_Models_AppSegment
{

    protected $db;

    protected $applicationPrefix;

    public function __construct()
    {
        $this->db = Zend_Registry::get('db');

    }

    public function getSegment($appId, $segment, $field = true)
    {


        $appQuery = $this->db->select()->from('Applications');
     
        $appQuery->where('app_id = ? ', $appId);

        $appRow = $this->db->fetchRow($appQuery);

        $this->applicationPrefix = $appRow['app_prefix'];

        return $this->segmentData($segment, $field);

        //return  $appRow[$field];

    }

    protected function segmentData($segment, $field = true)
    {

        $applicationModel = 'Admin_Model_Applications_' . ucfirst($this->applicationPrefix) . '_' . ucfirst($this->applicationPrefix);

        $applicationModel = new $applicationModel();

        $dbTable = $applicationModel->plugin;

        $query = $this->db->select()->from($dbTable['categoryTable']);

        $query->where($dbTable['categoryTable'] . '.' . $dbTable['categoryKey'] . ' = ?', $segment);

        if (!empty($dbTable['dependOn']))
        {
            $query->join($dbTable['dependOn'], $dbTable['categoryTable'] . '.' . $dbTable['categoryKey'] . '=' . $dbTable['dependOn'] . '.' . $dbTable['dependOnPriKey']);

            if (!empty($dbTable['dependOnSecKey']))
            {
                $query->where($dbTable['dependOnSecKey'] . ' = ?', $dbTable['dependOnSecVal']);
            }
        }

        $row = $this->db->fetchRow($query);
        
        if ($field === true)
        {
            return $row[$dbTable['categoryLabel']];
        }
        else
        {
            $row['app_prefix'] = $this->applicationPrefix;
        
            $row['label'] = $row[$dbTable['categoryLabel']];
            
            return $row;
        }
    }
}