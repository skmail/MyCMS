<?php


class MC_Models_Language {

    public function __construct()
    {
        $this->MC =& MC_Core_Instance::getInstance();
    }
    public function currentLang($row = '*')
    {
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



        $query = $this->MC->db->select()->from('language');

        if(isset($options['short_lang']))
            $query->where('short_lang = ?',$options['short_lang']);

        if(isset($options['lang_default']))
            $query->where('lang_default = ?',$options['lang_default']);


        $query->where('lang_status = ?',1);

        $row = $this->MC->db->fetchRow($query);

        if($row)
        {
            return $row;
        }
        else
        {
            return $this->setLanguage(array('lang_default'=>1));
        }
    }

    public  function  langsList(){


        $lang = $this->MC->db->select()->from('language')->where('lang_status = ? ',1);

        $langList = $this->MC->db->fetchAll($lang);

        return $langList;

    }

    public function translate($translation)
    {

        $translate = Zend_Registry::get('Zend_Translate')->translate($translation);

        return $translate;

    }
}