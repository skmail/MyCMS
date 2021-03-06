<?php

class App_Widgets_Shared_Queries
{

    protected $db = null;

    public function __construct()
    {
        $this->db = Zend_Registry::get('db');

    }

    public function group($options = array())
    {

        $options['onlyLang'] = true;

        if (isset($options['groupId']))
        {
            $options['onlyRow'] = true;
        }
        else
        {
            $options['onlyRow'] = false;
        }


        $groupQuery = $this->db->select()->from('widgets_groups')
                ->join('widgets_groups_lang', 'widgets_groups.group_id = widgets_groups_lang.group_id')
                ->join('grid', 'widgets_groups.grid_id = grid.grid_id')
                ->where('widgets_groups.group_status = ?', 1);

        if (isset($options['groupId']))
        {
            $groupQuery->where('widgets_groups.group_id = ? ', intval($options['groupId']));
        }

        if (isset($options['gridId']))
        {
            $groupQuery->where('widgets_groups.grid_id = ? ', intval($options['gridId']));
        }

        if ($options['onlyLang'] == true)
        {
            $groupQuery->where('widgets_groups_lang.lang_id = ? ',   MC_Core_Loader::appClass('Language','Lang',NULL,'Shared')->currentLang());
        }



        if ($options['onlyLang'] === false && $options['onlyRow'])
        {

            $groupRows = $this->db->fetchAll($groupQuery);

            $groupRow = $groupRows[0];

            foreach ($groupRow as $langId => $groupLang)
            {

                $groupRow['group_lang'][$langId] = $groupLang;
            }

            return $groupRow;
        }

        if ($options['onlyLang'] && $options['onlyRow'])
        {
            return $this->db->fetchRow($groupQuery);
        }

        if ($options['onlyLang'] && !$options['onlyRow'])
        {
            return $this->db->fetchAll($groupQuery);
        }

    }

}