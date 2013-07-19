<?php

class App_Themes_Admin_Queries
{

    public function __construct()
    {
        
        $this->db = Zend_Registry::get('db');
        
    }

    public function saveTemplate($template_id, $data)
    {
     
        $template_id = intval($template_id);
        
        
        if($template_id == 0)
        {
            
            $this->db->insert('templates',$data);
            
            return $this->db->lastInsertId();
        }
        
        $where = $this->db->quoteInto('template_id = ? ',$template_id);
                
        $this->db->update('templates',$data,$where);
        
        return $template_id;
        
    }
    
    
    
    public function templateQuery($options = array()){
        
        $query = $this->db->select()->from('templates');
        
        if(isset($options['template_id']))
        {
            $query->where('template_id = ?',$options['template_id']);
            $options['onlyRow'] = true;
        }
        
        if(isset($options['cat_id']))
        {
            $query->where('cat_id = ?',$options['cat_id']);
        }
        
        if(isset($options['parent_template']))
        {
            $query->where('parent_template = ?',$options['parent_template']);
        }
        
        if($options['onlyRow'] === true){
            return $this->db->fetchRow($query);
        }
        
        return $this->db->fetchAll($query);

    }

}