<?php


class App_Storage_Shared_Queries
{
    
    public function __construct($application = array())
    {
        $this->application = $application;
        $this->db = Zend_Registry::get('db');
    }

    public function group($options = array())
    {
        $query = $this->db->select()->from('storage_group');
        $query->join('storage_group_lang','storage_group_lang.group_id = storage_group.group_id');
        
        foreach ($options as $col=>$val)
        {
            $column = $col;
            
            if($column == 'group_id')
            {
                $column = 'storage_group.'.$column;
            }
            
            switch ($column)
            {
                case 'where_not':
                    foreach ($val as $colWhereNot => $valWhereNot)
                    {
                    if($colWhereNot == 'group_id')
                    {
                        $columnWhereNot = 'storage_group.'.$colWhereNot;
                    }

                        $query->where($columnWhereNot . " != ?",$valWhereNot);
                    }
                    break;
                default :
                    $query->where($column . " = ?",$val);
                    break;
            }
           
        }
        
       
        
        
        if((isset($options['lang_id']) && isset($options['group_id'])) || (isset($options['folder'])))
        {
           
            return $this->db->fetchRow($query);
        }
        
        
        if(!isset($options['lang_id']) && isset($options['group_id']))
        {
            $rows =  $this->db->fetchAll($query);
            
            if(!$rows)
            {
                return false;
            }
            
            $newRowsArray = array();
            
            $newRowsArray = $rows[0];
            
            foreach($rows as $row)
            {
                $newRowsArray['group_lang'][$row['lang_id']] = $row;  
            }
            
            return $newRowsArray;
            
        }
        
        
            return $this->db->fetchAll($query);
        
    }
    
}
