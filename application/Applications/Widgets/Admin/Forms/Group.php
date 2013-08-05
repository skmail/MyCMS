<?php

class App_Widgets_Admin_Forms_Group extends MC_Admin_Form_BaseForm {

    public function init() {
        
        $MC = MC_Core_Instance::getInstance();

        $this->setAttrib('class', 'saveGroup');
        $data = $this->getAttrib('data');
        $this->removeAttrib('data');

        $group = new MC_Admin_Form_SubForm();
        $groupParams = new MC_Admin_Form_SubForm();
        $groupLang = new App_Widgets_Admin_Forms_GroupLang();

        $groupLang->setElementsBelongTo('');
        
        $groupStatus = $group->createElement('select', 'group_status', array('decorators' => MC_Admin_Form_Form::$elementDecorators))
                ->setLabel('Group Status')
                ->setRequired(TRUE);

        $groupStatus->addMultiOption(1, 'Active');

        $groupStatus->addMultiOption(2, 'Hidden');

        $group->addElement($groupStatus);

        $outerTemplates = $groupParams->createElement('select', 'outer_template', array('decorators' => MC_Admin_Form_Form::$elementDecorators))
                ->setLabel('outer_template')
                ->setRequired(TRUE);

        $templates = $MC->db->select()->from('templates')->join('templates_categories','templates_categories.cat_id=templates.cat_id')->where('parent_template = ?',0);

        foreach ($MC->db->fetchAll($templates) as $v) {
            $outerTemplates->addMultiOption($v['template_id'], $v['cat_name'].'/'.$v['template_name']);
        }

        $groupParams->addElement($outerTemplates);

        $innerTemplate = $groupParams->createElement('select', 'inner_template',array('decorators' => MC_Admin_Form_Form::$elementDecorators))
                ->setLabel('inner_tempalate')
                ->setRequired(TRUE);

        $templates = $MC->db->select()->from('templates')->join('templates_categories','templates_categories.cat_id=templates.cat_id')->where('parent_template = ?',0);

        foreach ($MC->db->fetchAll($templates) as  $v) {
            $innerTemplate->addMultiOption($v['template_id'], $v['cat_name'].'/'.$v['template_name']);
        }

        $groupParams->addElement($innerTemplate);

        $MC->load->appLibrary('Grids','Grids');

        $gridClass = $groupParams->createElement('select', "grid_class", array('label' => 'grid_type', 'required' => 'true','class'=>'ltr', 'decorators' => MC_Admin_Form_Form::$elementDecorators));

        foreach ($MC->Grids->getGrids($MC->settings['grid_name']) as $grid) {
            $gridClass->addMultiOption($grid->class, $grid->class . " (" . $grid->size . ")");
        }

        $groupParams->addElement($gridClass);

        $groupParams->addElement('text','css_class',array('label'=>'css_class'));

        $group->removeDecorator("DtDdWrapper");

        $groupParams->removeDecorator("DtDdWrapper");

        $groupLang->removeDecorator("DtDdWrapper");

        $this->addSubForm($groupLang, 'group_lang');

        $gridDb = $group->createElement('select', 'grid_id', array('required'=>true,'decorators' => MC_Admin_Form_Form::$elementDecorators))
                ->setLabel('Grid')
                ->setRequired(TRUE);

        $langs = MC_Core_Loader::appClass('Language', 'Lang', NULL, 'Shared');

        $grids = $MC->Queries->gridQuery(array('lang_id'=>$langs->currentLang()));
        
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

