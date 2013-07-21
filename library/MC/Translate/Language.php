<?php

class MC_Translate_Language
{

    public function __construct()
    {
        $this->MC =& MC_Core_Instance::getInstance();
    }

    /**
     * @param string $field
     * @param array $options
     * @return mixed
     */
    public function current($field = '*',$options = array())
    {

        if(Zend_Registry::isRegistered('language') && $currentLanguage = Zend_Registry::get('language') )
        {
            if($currentLanguage == $options['short_lang'])
            {
                if($field == '*')
                {
                    return $currentLanguage;
                }
                return $currentLanguage[$field];
            }
        }

        $query = $this->MC->db->select()->from('language');

        if(isset($options['short_lang']))
        {
            $query->where('short_lang = ?',$options['short_lang']);
        }

        if(isset($options['lang_default']))
        {
            $query->where('lang_default = ?',$options['lang_default']);
        }

        $query->where('lang_status = ?',1);

        $row = $this->MC->db->fetchRow($query);

        if($row)
        {
            if($field == "*")
            {
                return $row;
            }

            return $row[$field];
        }
        else
        {
            return $this->setLanguage(array('lang_default'=>1));
        }
    }

    /**
     * @return array|bool
     */
    public  static function  langs(){
        $db = MC_Core_Instance::getInstance()->db;
        $lang = $db->select()->from('language')->where('lang_status = ? ',1);
        $langList = $db->fetchAll($lang);
        return $langList;
    }

}