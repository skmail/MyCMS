<?php


    
class App_Language_Admin_Queries {
    
    public function __construct($application = array())
    {
        $this->application = $application;

        $this->db = Zend_Registry::get('db');
    }
    
    
    
    public function langQuery($options = array()){
        
        
        $langQuery =  $this->db->select()->from('language');
        
        
        if(isset($options['where_not']))
        {
            foreach($options['where_not'] as $col=>$val)
            {
                $langQuery->where($col.' != ?',$val);
            }
            
        }
        
        
        if(isset($options['lang_id']))
        {
            $langQuery->where('lang_id = ?',$options['lang_id']);
        
            return $this->db->fetchRow($langQuery);
        }
        
        if(isset($options['short_lang']))
        {
            $langQuery->where('short_lang = ?',$options['short_lang']);
        
            return $this->db->fetchRow($langQuery);
        }
        
        
        return $this->db->fetchAll($langQuery);
        
    }
    
    
    
    public function getPhrase($options = array()){
        
        $phraseQuery = $this->db->select()->from('language_phrases');
        
        if(isset($options['phrase_name']))
        {
            $phraseQuery->where('phrase_name = ?',$options['phrase_name']);
        }
        
        if(isset($options['lang_id']))
        {
            $phraseQuery->where('language_phrases.lang_id = ?',$options['lang_id']);
        }
        
        
        if(isset($options['phrase_name']) && isset($options['lang_id']))
        {
            return $this->db->fetchRow($phraseQuery);
        }
        
        if(isset($options['phrase_name']) && !isset($options['lang_id']))
        {
            $phrase = $this->db->fetchAll($phraseQuery);
            
            $rows = array();
            
            $rows['phrase_name'] = $phrase[0]['phrase_name'];
            
            foreach($phrase as $row)
            {
                $rows[$row['lang_id']] = $row;
            }
            
            return $rows;
            
        }
        
            return $this->db->fetchAll($phraseQuery);
    }
    
    
    
    public function phrasesNames()
    {
        $phraseQuery = $this->db->select()->from('language_phrases');
        $phraseQuery->group(array('phrase_name'));
        return $this->db->fetchAll($phraseQuery);
    }
    
    
    public function phrasesValues()
    {
        $phraseQuery = $this->db->select()->from('language_phrases');
        
        
        $rows =  $this->db->fetchAll($phraseQuery);
        
        $valuesArray = array();
        foreach($rows as $key => $row)
        {
            
            $valuesArray[$row['phrase_name']][$row['lang_id']] = $row;
            
        }
        
        return $valuesArray;
    }
    
    
}