<?php

class App_Users_Admin_Queries
{

    public function __construct($application)
    {
        $this->application = $application;
        $this->db = Zend_Registry::get('db');

    }


    public function usergroupList($options = array())
    {
        $options['lang_id'] = (isset($options['lang_id'])) ? $options['lang_id'] : $this->application['lang_id'];

        $langs = MC_Core_Loader::appClass('Language', 'Lang', NULL, 'Shared');

        $currentLang = $langs->currentLang();

        $usergroups = array();

        $query = $this->db->select()->from('usergroups');

        foreach($this->db->fetchAll($query) as $usergroup)
        {
            $usergroupLang = $this->db->fetchRow($this->db->select()->from('usergroups_lang')->where('usergroup_id = ?',$usergroup['usergroup_id'])->where('lang_id = ?',$options['lang_id']));

            if(!$usergroupLang)
            {
                $usergroupLang = $this->db->fetchRow($this->db->select()->from('usergroups_lang')->where('usergroup_id = ?',$usergroup['usergroup_id'])->where('lang_id = ?',$currentLang));
            }

            if(is_array($usergroupLang) && is_array($usergroup))
            {
                $usergroups[] =$usergroupLang;
            }
        }

        return $usergroups;
    }

    public function usergroupQuery($ugid = 0, $onlyRow = true, $onlyLang = true, $options = array())
    {


        $options['lang_id'] = (isset($options['lang_id'])) ? $options['lang_id'] : $this->application['lang_id'];

        $query = $this->db->select()->from('usergroups');

        $query->join('usergroups_lang', 'usergroups_lang.usergroup_id = usergroups.usergroup_id');

        if ($onlyRow)
        {
            $query->where('usergroups.usergroup_id = ?', $ugid);
        }
        if ($onlyLang)
        {
            $query->where('lang_id = ?', $options['lang_id']);
        }

        if ($onlyRow == true && $onlyLang == false)
        {
            $row = $this->db->fetchAll($query);

            $query = array();
         
            $query[0] = $row[0];

            $query['usergroup_lang'] = array();

            foreach ($row as $usergroupData)
            {
                $query['usergroup_lang'][$usergroupData['lang_id']] = $usergroupData;
            }
            
            return $query;
        }


        if ($onlyLang == true && $onlyRow == true)
        {

            return $this->db->fetchRow($query);

        }

        return $query;

    }

    public function userQuery($options = array())
    {

        $query = $this->db->select()->from('users');

        $query->join('usergroups', 'usergroups.usergroup_id = users.usergroup_id');
        $query->join('usergroups_lang', 'usergroups_lang.usergroup_id = usergroups.usergroup_id');

        $query->group('usergroups_lang.usergroup_id');
        if (isset($options['usergroup_id']))
        {
            $query->where('users.usergroup_id = ?', intval($options['usergroup_id']));
        }

        if (isset($options['user_id']))
        {
            $query->where("user_id = ? ", $options['user_id']);

            return $this->db->fetchRow($query);
        }

        return $this->db->fetchAll($query);

    }

}