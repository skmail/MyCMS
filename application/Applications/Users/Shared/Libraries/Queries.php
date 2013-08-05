<?php
class App_Users_Shared_Libraries_Queries
{
    public function __construct()
    {
        $this->MC =& MC_Core_Instance::getInstance();
    }

    public function usergroupList($langId)
    {
        $query = $this->MC->db->select()->from('usergroups');
        $query->join('usergroups_lang','usergroups_lang.usergroup_id = usergroups.usergroup_id');
        $usergroups = $this->MC->db->fetchAll($query);
        if(!$usergroups){
            return false;
        }
        $usergroupsList = array();
        $finalUsergroups = array();
        foreach($usergroups as $usergroup)
        {
            $usergroupsList[$usergroup['usergroup_id']][$usergroup['lang_id']] = $usergroup;
        }
        foreach($usergroupsList as $usergroupId=>$usergroup)
        {
            if(isset($usergroup[$langId])){
                $finalUsergroups[$usergroupId] = $usergroup[$langId];
            }else{
                $finalUsergroups[$usergroupId] = reset($usergroup);
            }
        }
        return $finalUsergroups;
    }

    public function getUsergroupByLang($usergroupId,$langId = NULL)
    {
        $query = $this->MC->db->select()->from('usergroups');
        $query->join('usergroups_lang', 'usergroups_lang.usergroup_id = usergroups.usergroup_id');
        $query->where('usergroups.usergroup_id = ?', $usergroupId);
        if(NULL !== $langId){
            $query->where('lang_id = ?', $langId);
        }
        $result = $this->MC->db->fetchRow($query);
        return $result;
    }
    public function getUsergroupById($usergroupId,$oneRow = false)
    {
        $query = $this->MC->db->select()->from('usergroups');
        $query->join('usergroups_lang', 'usergroups_lang.usergroup_id = usergroups.usergroup_id');
        $query->where('usergroups.usergroup_id = ?', $usergroupId);
        return $this->MC->db->fetchRow($query);
    }

    public function usergroupQuery($ugid = 0, $onlyRow = true, $onlyLang = true, $options = array())
    {
        $options['lang_id'] = (isset($options['lang_id'])) ? $options['lang_id'] : $this->application['lang_id'];
        $query = $this->MC->db->select()->from('usergroups');
        $query->join('usergroups_lang', 'usergroups_lang.usergroup_id = usergroups.usergroup_id');
        if ($onlyRow){
            $query->where('usergroups.usergroup_id = ?', $ugid);
        }
        if ($onlyLang){
            $query->where('lang_id = ?', $options['lang_id']);
        }
        if ($onlyRow == true && $onlyLang == false){
            $row = $this->MC->db->fetchAll($query);
            $query = array();
            $query[0] = $row[0];
            $query['usergroup_lang'] = array();
            foreach ($row as $usergroupData)
            {
                $query['usergroup_lang'][$usergroupData['lang_id']] = $usergroupData;
            }
            return $query;
        }
        if ($onlyLang == true && $onlyRow == true){
            return $this->MC->db->fetchRow($query);
        }
        return $query;
    }

    public function userQuery($options = array())
    {
        $query = $this->MC->db->select()->from('users');
        $query->join('usergroups', 'usergroups.usergroup_id = users.usergroup_id');
        $query->join('usergroups_lang', 'usergroups_lang.usergroup_id = usergroups.usergroup_id');
        $query->group('usergroups_lang.usergroup_id');
        if (isset($options['usergroup_id'])){
            $query->where('users.usergroup_id = ?', intval($options['usergroup_id']));
        }

        if (isset($options['user_id'])){
            $query->where("user_id = ? ", $options['user_id']);
            return $this->MC->db->fetchRow($query);
        }
        return $this->MC->db->fetchAll($query);
    }

    public function getUsersByUsergroupId($usergroupId)
    {
        $query = $this->MC->db->select()->from('users');
        $query->join('usergroups', 'usergroups.usergroup_id = users.usergroup_id');
        $query->where('users.usergroup_id = ?', intval($usergroupId));
        return $this->MC->db->fetchAll($query);
    }
}