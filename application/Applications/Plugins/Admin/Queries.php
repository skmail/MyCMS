<?php

class App_Plugins_Admin_Queries
{

    public function __construct($application = array())
    {
        $this->application = $application;

        $this->db = Zend_Registry::get('db');

    }

    public function gridQuery($options = array())
    {
        $db = Zend_Registry::get('db');

        $gridQuery = $db->select()->from('grid');

        $gridQuery->join('grid_lang', 'grid_lang.grid_id = grid.grid_id');

        if ($options['grid_id'])
        {
            $gridQuery->where("grid_lang.grid_id = ?", $options['grid_id']);
        }

        if (isset($options['theme_id']))
        {
            $gridQuery->where('theme_id = ?', $options['theme_id']);
        }

        if (isset($options['lang_id']))
        {
            $gridQuery->where('lang_id = ?', $options['lang_id']);
        }

        $gridQuery->order(array('grid.grid_order ASC'));

        if (!$options['lang_id'])
        {
            if ($options['grid_id'])
            {
                $gridRows = $db->fetchAll($gridQuery);

                foreach ($gridRows as $grid_lang)
                {
                    $gridLang[$grid_lang['lang_id']] = $grid_lang;
                }

                $gridRows = $gridRows[0];
                $gridRows['grid_params'] = Zend_Json::decode($gridRows['grid_params']);
                $gridRows['grid_lang'] = $gridLang;
            }
        }
        else
        {
            if (!$options['grid_id'])
            {
                return $db->fetchAll($gridQuery);
            }
            else
            {
                $gridRow = $db->fetchRow($gridQuery) ;
                $gridRow['grid_params'] = Zend_Json::decode($gridRow['grid_params']);


                return $gridRow;
            }
        }

        return $gridRows;

    }

    public function pluginQuery($pluginId = 0, $groupId = 0, $onlyRow = false, $onlyLang = true)
    {

        $pluginId = intval($pluginId);

        $groupId = intval($groupId);

        $pluginQuery = $this->db->select()->from('plugins')
                ->join('plugins_lang', 'plugins.plugin_id = plugins_lang.plugin_id');

        $pluginQuery->join('plugins_groups', 'plugins_groups.group_id =  plugins.group_id');

        $pluginQuery->join('grid', 'plugins_groups.grid_id =  grid.grid_id');

        if ($onlyRow)
        {
            $pluginQuery->where("plugins.plugin_id = ? ", $pluginId);
        }

        if ($groupId != 0)
        {
            $pluginQuery->where("plugins.group_id = ? ", $groupId);
        }


        if ($onlyLang)
        {
            $pluginQuery->where('plugins_lang.lang_id = ? ', $this->application['lang_id']);
        }

        if (!$onlyRow)
        {
            $pluginQuery->order(array('plugins.plugin_order ASC'));
        }


        if ($onlyRow && !$onlyLang)
        {

            $pluginRow = $this->db->fetchAll($pluginQuery);

            foreach ($pluginRow as $langRow)
            {
                $pluginLangRow[$langRow['lang_id']] = $langRow;
            }

            $pluginRow = $pluginRow[0];

            $pluginRow['grid_params'] = Zend_Json::decode($pluginRow['grid_params']);

            $pluginRow['group_params'] = Zend_Json::decode($pluginRow['group_params']);

            $pluginRow['plugin_params'] = Zend_Json::decode($pluginRow['plugin_params']);


            $pluginRow['plugin_lang'] = $pluginLangRow;

            return $pluginRow;
        }

        if (!$onlyRow && $onlyLang)
        {
            return $this->db->fetchAll($pluginQuery);
        }

    }

    public function groupQuery($options = array())//$groupId = 0, $onlyRow = false, $onlyLang = false, $gridId = 0)
    {


        $groupQuery = $this->db->select()->from('plugins_groups');

        $groupQuery->join('plugins_groups_lang', 'plugins_groups.group_id = plugins_groups_lang.group_id');
        $groupQuery->join('grid', 'plugins_groups.grid_id = grid.grid_id');

        if (isset($options['group_id']))
        {
            $groupQuery->where('plugins_groups.group_id = ? ', $options['group_id']);
        }

        if (isset($options['lang_id']))
        {
            $groupQuery->where('plugins_groups_lang.lang_id = ? ', $options['lang_id']);
        }
        if (isset($options['grid_id']))
        {
            $groupQuery->where('plugins_groups.grid_id = ? ', $options['grid_id']);
        }

        if (!isset($options['group_id']))
        {
            $groupQuery->order(array('plugins_groups.group_order ASC'));
        }

        if(isset($options['group_id']) && isset($options['lang_id']))
        {
            $groupRow = $this->db->fetchRow($groupQuery);
            $groupRow['group_params'] = Zend_Json::decode($groupRow['group_params']);
            $groupRow['grid_params'] = Zend_Json::decode($groupRow['grid_params']);
        }
        else if(!isset($options['group_id']) && isset($options['lang_id']))
        {
            $groupRows = $this->db->fetchAll($groupQuery);
            foreach($groupRows as $key=>$row)
            {
                $groupRows[$key]['group_params'] = Zend_Json::decode($row['group_params']);
                $groupRows[$key]['grid_params'] = Zend_Json::decode($row['grid_params']);
            }

            return $groupRows;
        }
        else if(isset($options['group_id']) && !isset($options['lang_id']))
        {

            $groupRow = $this->db->fetchAll($groupQuery);

            $groupLang = array();


            foreach ($groupRow as $group)
            {
                $groupLang[$group['lang_id']] = $group;
            }

            $groupRow = reset($groupRow);

            $groupRow['group_lang'] = $groupLang;

            $groupRow['group_params'] = Zend_Json::decode($groupRow['group_params']);
            $groupRow['grid_params'] = Zend_Json::decode($groupRow['grid_params']);

        }
        else
        {
            return array();
        }

        return $groupRow;
    }
    
    public function duplicateGrid($gridId)
    {
        $gridRow = $this->db->fetchRow($this->db->select()->from('grid')->where('grid_id = ? ', $gridId));

        unset($gridRow['grid_id']);

        $this->db->insert('grid', $gridRow);
        $newGridId = $this->db->lastInsertId();

        $gridLangs = $this->db->fetchAll($this->db->select()->from('grid_lang')->where('grid_id = ? ', $gridId));

        foreach ($gridLangs as $gridLang)
        {
            $gridLang['grid_id'] = $newGridId;
            $this->db->insert('grid_lang', $gridLang);
        }


        $groups = $this->db->fetchAll($this->db->select()->from('plugins_groups')->where('grid_id = ? ', $gridId));

        foreach ($groups as $group)
        {
            $this->duplicateGroup($group['group_id'], array('grid_id' => $newGridId));
        }

    }

    public function duplicateGroup($groupId, $options = array())
    {



        $groupRow = $this->db->fetchRow($this->db->select()->from('plugins_groups')->where('group_id = ? ', $groupId));

        $groupRow = @array_merge($groupRow, $options);

        unset($groupRow['group_id']);

        $this->db->insert('plugins_groups', $groupRow);
        $newGroupId = $this->db->lastInsertId();

        $groupLangs = $this->db->fetchAll($this->db->select()->from('plugins_groups_lang')->where('group_id = ? ', $groupId));

        foreach ($groupLangs as $groupLang)
        {
            $groupLang['group_id'] = $newGroupId;
            $this->db->insert('plugins_groups_lang', $groupLang);
        }


        $plugins = $this->db->fetchAll($this->db->select()->from('plugins')->where('group_id = ? ', $groupId));

        foreach ($plugins as $plugin)
        {
            $this->duplicatePlugin($plugin['plugin_id'], array('group_id' => $newGroupId));
        }

    }

    public function duplicatePlugin($pluginId, $options = array())
    {

        $pluginRow = $this->db->fetchRow($this->db->select()->from('plugins')->where('plugin_id = ?', $pluginId));

        $pluginRow = array_merge($pluginRow, $options);

        unset($pluginRow['plugin_id']);

        $this->db->insert('plugins', $pluginRow);
        $newPluginId = $this->db->lastInsertId();

        $pluginLangs = $this->db->fetchAll($this->db->select()->from('plugins_lang')->where('plugin_id = ? ', $pluginId));

        foreach ($pluginLangs as $pluginLang)
        {
            $pluginLang['plugin_id'] = $newPluginId;
            $this->db->insert('plugins_lang', $pluginLang);
        }

    }

    public function deleteGrid($gridId = NULL)
    {
        $where = $this->db->quoteInto("grid_id = ? ", $gridId);

        $this->db->delete('grid', $where);

        $groups = $this->db->fetchAll($this->db->select()->from('plugins_groups')->where('grid_id = ? ', $gridId));

        foreach ($groups as $group)
        {
            $this->deleteGroup($group['group_id'], array('grid_id' => $newGridId));
        }

    }

    public function deleteGroup($groupId = NULL)
    {


        $where = $this->db->quoteInto("group_id = ? ", $groupId);

        $groupRow = $this->db->delete('plugins_groups', $where);

        $groupRow = $this->db->delete('plugins_groups_lang', $where);


        $plugins = $this->db->fetchAll($this->db->select()->from('plugins')->where('group_id = ? ', $groupId));

        foreach ($plugins as $plugin)
        {
            $this->deletePlugin($plugin['plugin_id']);
        }

    }

    public function deletePlugin($pluginId = NULL)
    {

        $where = $this->db->quoteInto("plugin_id = ? ", $pluginId);
        $this->db->delete('plugins', $where);
        $this->db->delete('plugins_lang', $where);

    }

}