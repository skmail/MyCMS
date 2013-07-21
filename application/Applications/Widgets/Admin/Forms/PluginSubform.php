<?php

class App_Widgets_Admin_Forms_Widgetsubform extends MC_Admin_Form_BaseForm {
 
    
    public function init() {
 
        $db = Zend_Registry::get('db');

        $groupsList = $plugin->createElement('select', 'group_id',array('decorators'=>MC_Admin_Form_Form::$elementDecorators))
                             ->setLabel('Plugin Group')
                             ->setRequired(TRUE);
        
        
        $groups = $db->select()
                     ->from('plugins_groups')
                     ->join('plugins_groups_lang', 'plugins_groups.group_id = plugins_groups_lang.group_id')
                     ->where('plugins_groups.group_status = ?', 1);

        foreach ($db->fetchAll($groups) as $k => $v) {
            $groupsList->addMultiOption($v['group_id'], $v['group_name']);
        }

        $plugin->addElement($groupsList);

        $pluginStatus = $this->createElement('select', 'plugin_status',array(
                                            'decorators'=>MC_Admin_Form_Form::$elementDecorators))
                               ->setLabel('Plugin Status')
                               ->setRequired(TRUE);
        $pluginStatus->addMultiOption(1, 'Active');
        $pluginStatus->addMultiOption(2, 'Hidden');
        
        
        $plugin->addElement($pluginStatus);
 
        }

    
}