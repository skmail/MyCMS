<?php

class App_Plugins_Admin_Forms_Group extends MC_Admin_Form_BaseForm {

    public function init() {
        
        $query = new App_Plugins_Admin_Queries();

        $this->setAttrib('class', 'saveGroup');
        $data = $this->getAttrib('data');
        $this->removeAttrib('data');

        $group = new MC_Admin_Form_SubForm();

        $groupParams = new MC_Admin_Form_SubForm();

        $groupLang = new App_Plugins_Admin_Forms_GroupLang();

        $groupLang->setElementsBelongTo('');
        
        $groupStatus = $group->createElement('select', 'group_status', array('decorators' => MC_Admin_Form_Form::$elementDecorators))
                ->setLabel('Group Status')
                ->setRequired(TRUE);

        $groupStatus->addMultiOption(1, 'Active');

        $groupStatus->addMultiOption(2, 'Hidden');

        $group->addElement($groupStatus);


        $db = Zend_Registry::get('db');

        $outerTemplates = $groupParams->createElement('select', 'outer_template', array('decorators' => MC_Admin_Form_Form::$elementDecorators))
                ->setLabel('Outer Template')
                ->setRequired(TRUE);

        $templates = $db->select()->from('templates')->where('cat_id = ?',$data['grid_params']['group_outer_templates_category']);

        foreach ($db->fetchAll($templates) as $k => $v) {
            $outerTemplates->addMultiOption($v['template_id'], $v['template_name']);
        }
        $groupParams->addElement($outerTemplates);


        $innerTemplate = $groupParams->createElement('select', 'inner_template',array('decorators' => MC_Admin_Form_Form::$elementDecorators))
                ->setLabel('Innert Template')
                ->setRequired(TRUE);
        $templates = $db->select()->from('templates');
        if(isset($data['grid_params']['group_inner_templates_category']))
        {
            $templates->where('cat_id = ?',$data['grid_params']['group_inner_templates_category']);
        }
        foreach ($db->fetchAll($templates) as $k => $v) {
            $innerTemplate->addMultiOption($v['template_id'], $v['template_name']);
        }
        $groupParams->addElement($innerTemplate);

        
        $grid = new MC_Models_Grid_Grid();

        $gridClass = $groupParams->createElement('select', "grid_class", array('label' => 'Grid Class', 'required' => 'true','class'=>'ltr', 'decorators' => MC_Admin_Form_Form::$elementDecorators));
        foreach ($grid->getGrids() as $grid) {
            $gridClass->addMultiOption($grid->class, $grid->class . " (" . $grid->size . ")");
        }
        $groupParams->addElement($gridClass);


        $pluginOuterTemplateCategory = $groupParams->createElement('select', "plugin_outer_template_category", array('label' => 'plugin_outer_templates', 'required' => 'true','class'=>'ltr', 'decorators' => MC_Admin_Form_Form::$elementDecorators));

        $templatesCategories = $db->select()->from('templates_categories');
        $templatesCategories->where('theme_id = ? ',$data['theme_id']);

        foreach ($db->fetchAll($templatesCategories) as $k => $v) {
            $pluginOuterTemplateCategory->addMultiOption($v['cat_id'], $v['cat_name']);
        }
        $groupParams->addElement($pluginOuterTemplateCategory);

        $groupParams->addElement('text','css_class',array('label'=>'css_class'));

        $group->removeDecorator("DtDdWrapper");
        $groupParams->removeDecorator("DtDdWrapper");
        $groupLang->removeDecorator("DtDdWrapper");

        $this->addSubForm($groupLang, 'group_lang');
        
        $gridDb = $group->createElement('select', 'grid_id', array('required'=>true,'decorators' => MC_Admin_Form_Form::$elementDecorators))
                ->setLabel('Grid')
                ->setRequired(TRUE);

        $langs = MC_Core_Loader::appClass('Language', 'Lang', NULL, 'Shared');

        $grids = $query->gridQuery(array('lang_id'=>$langs->currentLang()));
        
        foreach($grids as $grid)
        {
            $gridDb->addMultiOption($grid['grid_id'], $grid['grid_name']);
        }

        $group->addElement($gridDb);

        MC_Models_Hooks::call('create_group_params',$groupParams,$data);

        $group->addSubForm($groupParams, 'group_params');


        $this->addSubForm($group, 'group');

        $this->addElement('hidden', 'do', array('required' => true));
        $this->addElement('hidden', 'group_id')->removeDecorator("DtDdWrapper");
        $this->addElement('submit', 'go', array('label' => 'Edit', 'class' => 'submit', 'order' => 7));
    }

}

