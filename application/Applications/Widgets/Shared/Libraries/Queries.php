<?php

class App_Widgets_Shared_Libraries_Queries
{

    public function __construct($application = array())
    {
        $this->MC =& MC_Core_Instance::getInstance();

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

    public function widget($args = array())
    {

        $pluginQuery = $this->MC->db->select()->from('widgets')
                ->join('widgets_lang', 'widgets.widget_id = widgets_lang.widget_id');
        $pluginQuery->join('widgets_groups', 'widgets_groups.group_id =  widgets.group_id');
        $pluginQuery->join('grid', 'widgets_groups.grid_id =  grid.grid_id');

        if (isset($args['widget_id']))
        {
            $pluginQuery->where("widgets.widget_id = ? ", $args['widget_id']);
        }

        if ($args['group_id'] != 0)
        {
            $pluginQuery->where("widgets.group_id = ? ", $args['group_id']);
        }

        if (isset($args['lang_id']))
        {
            $pluginQuery->where('widgets_lang.lang_id = ? ', $args['lang_id']);
        }

        if (!isset($args['widget_id']))
        {
            $pluginQuery->order(array('widgets.widget_order ASC'));
        }


        if (isset($args['widget_id']) && !isset($args['lang_id']) )
        {

            $pluginRow = $this->MC->db->fetchAll($pluginQuery);

            if(!$pluginRow)
            {
                return false;
            }

            foreach ($pluginRow as $langRow)
            {
                $pluginLangRow[$langRow['lang_id']] = $langRow;
            }

            $pluginRow = $pluginRow[0];

            $pluginRow['grid_params'] = Zend_Json::decode($pluginRow['grid_params']);

            $pluginRow['group_params'] = Zend_Json::decode($pluginRow['group_params']);

            $pluginRow['widget_params'] = Zend_Json::decode($pluginRow['widget_params']);


            $pluginRow['plugin_lang'] = $pluginLangRow;

            return $pluginRow;
        }

        if (!isset($args['widget_id']) && isset($args['lang_id']))
        {
            return $this->MC->db->fetchAll($pluginQuery);
        }

    }

    public function groupQuery($options = array())//$groupId = 0, $onlyRow = false, $onlyLang = false, $gridId = 0)
    {


        $groupQuery = $this->MC->db->select()->from('widgets_groups');

        $groupQuery->join('widgets_groups_lang', 'widgets_groups.group_id = widgets_groups_lang.group_id');
        $groupQuery->join('grid', 'widgets_groups.grid_id = grid.grid_id');

        if (isset($options['group_id']))
        {
            $groupQuery->where('widgets_groups.group_id = ? ', $options['group_id']);
        }

        if (isset($options['lang_id']))
        {
            $groupQuery->where('widgets_groups_lang.lang_id = ? ', $options['lang_id']);
        }
        if (isset($options['grid_id']))
        {
            $groupQuery->where('widgets_groups.grid_id = ? ', $options['grid_id']);
        }

        if (!isset($options['group_id']))
        {
            $groupQuery->order(array('widgets_groups.group_order ASC'));
        }

        if(isset($options['group_id']) && isset($options['lang_id']))
        {
            $groupRow = $this->MC->db->fetchRow($groupQuery);
            $groupRow['group_params'] = Zend_Json::decode($groupRow['group_params']);
            $groupRow['grid_params'] = Zend_Json::decode($groupRow['grid_params']);
        }
        else if(!isset($options['group_id']) && isset($options['lang_id']))
        {
            $groupRows = $this->MC->db->fetchAll($groupQuery);
            foreach($groupRows as $key=>$row)
            {
                $groupRows[$key]['group_params'] = Zend_Json::decode($row['group_params']);
                $groupRows[$key]['grid_params'] = Zend_Json::decode($row['grid_params']);
            }

            return $groupRows;
        }
        else if(isset($options['group_id']) && !isset($options['lang_id']))
        {

            $groupRow = $this->MC->db->fetchAll($groupQuery);

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
        $gridRow = $this->MC->db->fetchRow($this->MC->db->select()->from('grid')->where('grid_id = ? ', $gridId));

        unset($gridRow['grid_id']);

        $this->MC->db->insert('grid', $gridRow);
        $newGridId = $this->MC->db->lastInsertId();

        $gridLangs = $this->MC->db->fetchAll($this->MC->db->select()->from('grid_lang')->where('grid_id = ? ', $gridId));

        foreach ($gridLangs as $gridLang)
        {
            $gridLang['grid_id'] = $newGridId;
            $this->MC->db->insert('grid_lang', $gridLang);
        }


        $groups = $this->MC->db->fetchAll($this->MC->db->select()->from('widgets_groups')->where('grid_id = ? ', $gridId));

        foreach ($groups as $group)
        {
            $this->duplicateGroup($group['group_id'], array('grid_id' => $newGridId));
        }

    }

    public function duplicateGroup($groupId, $options = array())
    {



        $groupRow = $this->MC->db->fetchRow($this->MC->db->select()->from('widgets_groups')->where('group_id = ? ', $groupId));

        $groupRow = @array_merge($groupRow, $options);

        unset($groupRow['group_id']);

        $this->MC->db->insert('widgets_groups', $groupRow);
        $newGroupId = $this->MC->db->lastInsertId();

        $groupLangs = $this->MC->db->fetchAll($this->MC->db->select()->from('widgets_groups_lang')->where('group_id = ? ', $groupId));

        foreach ($groupLangs as $groupLang)
        {
            $groupLang['group_id'] = $newGroupId;
            $this->MC->db->insert('widgets_groups_lang', $groupLang);
        }


        $plugins = $this->MC->db->fetchAll($this->MC->db->select()->from('widgets')->where('group_id = ? ', $groupId));

        foreach ($plugins as $plugin)
        {
            $this->duplicatePlugin($plugin['widget_id'], array('group_id' => $newGroupId));
        }

    }

    public function duplicatePlugin($pluginId, $options = array())
    {

        $pluginRow = $this->MC->db->fetchRow($this->MC->db->select()->from('widgets')->where('widget_id = ?', $pluginId));

        $pluginRow = array_merge($pluginRow, $options);

        unset($pluginRow['widget_id']);

        $this->MC->db->insert('widgets', $pluginRow);
        $newPluginId = $this->MC->db->lastInsertId();

        $pluginLangs = $this->MC->db->fetchAll($this->MC->db->select()->from('widgets_lang')->where('widget_id = ? ', $pluginId));

        foreach ($pluginLangs as $pluginLang)
        {
            $pluginLang['widget_id'] = $newPluginId;
            $this->MC->db->insert('widgets_lang', $pluginLang);
        }

    }

    public function deleteGrid($gridId = NULL)
    {
        $where = $this->MC->db->quoteInto("grid_id = ? ", $gridId);

        $this->MC->db->delete('grid', $where);

        $groups = $this->MC->db->fetchAll($this->MC->db->select()->from('widgets_groups')->where('grid_id = ? ', $gridId));

        foreach ($groups as $group)
        {
            $this->deleteGroup($group['group_id'], array('grid_id' => $newGridId));
        }

    }

    public function deleteGroup($groupId = NULL)
    {


        $where = $this->MC->db->quoteInto("group_id = ? ", $groupId);

        $groupRow = $this->MC->db->delete('widgets_groups', $where);

        $groupRow = $this->MC->db->delete('widgets_groups_lang', $where);


        $plugins = $this->MC->db->fetchAll($this->MC->db->select()->from('widgets')->where('group_id = ? ', $groupId));

        foreach ($plugins as $plugin)
        {
            $this->deletePlugin($plugin['widget_id']);
        }

    }

    /**
     * @param null $widgetSourceId
     * @return mixed
     */
    public function widgetSource($widgetSourceId = NULL)
    {
        $query = $this->MC->db->select()->from('widgets_sources');

        if(NULL != $widgetSourceId || 0 != $widgetSourceId)
        {
            $query->where("widget_source_id = ? ",$widgetSourceId );

            $widgetRow = $this->MC->db->fetchRow($query);
            $widgetRow['widgetForm'] = 'Plugins_'.ucfirst($widgetRow['plugin_resource_name']).'_Form';

            return $widgetRow;
        }

        return $this->MC->db->fetchAll($query);
    }

    public function deletePlugin($pluginId = NULL)
    {
        $where = $this->MC->db->quoteInto("widget_id = ? ", $pluginId);
        $this->MC->db->delete('widgets', $where);
        $this->MC->db->delete('widgets_lang', $where);

    }
}