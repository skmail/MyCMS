<?php

    class App_Language_Shared_Lang {
        
        public static function currentLang($row = 'lang_id'){
            
            $languageRow = Zend_Registry::get('language');

            if($row == '*')
            {
                return $languageRow;
            }
            else
            {
                return $languageRow[$row];
            }
        }
        
        
        public function setLanguage(array $options)
        {
            
            $db = Zend_Registry::get('db');
           
            $query = $db->select()->from('language');
            
            if(isset($options['short_lang']))
                $query->where('short_lang = ?',$options['short_lang']);
           
             if(isset($options['lang_default']))
                $query->where('lang_default = ?',$options['lang_default']);
           
            
            $query->where('lang_status = ?',1);
            
            $row = $db->fetchRow($query);
            
            if($row)
            {
             return $row;   
            }
            else
            {
                return $this->setLanguage(array('lang_default'=>1));
            }
        }


        public  static function  langsList(){
            $db = Zend_Registry::get('db');
            $lang = $db->select()->from('language')->where('lang_status = ? ',1);
            $langList = $db->fetchAll($lang);
            return $langList;
        }
      
    }