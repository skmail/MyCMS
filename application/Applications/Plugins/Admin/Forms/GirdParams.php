<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mac
 * Date: 5/6/13
 * Time: 2:00 AM
 * To change this template use File | Settings | File Templates.
 */

class  App_Plugins_Admin_Forms_GirdParams extends MC_Admin_Form_SubForm
{


    public function init()
    {
        $data = $this->getAttrib('data');

        $this->removeAttrib('data');

        $db = Zend_Registry::get('db');

        $groupTemplatesCategories = $db->select()->from('templates_categories');

        $groupTemplatesCategories->where('theme_id = ?',$data['theme_id'] );

        $groupTemplatesCategoriesRow = $db->fetchAll($groupTemplatesCategories);

        $groupOuterTemplatesCategoryEL =
            $this->createElement('select','group_outer_templates_category',
                array('decorators' => MC_Admin_Form_Form::$elementDecorators))
                ->setLabel('group_outer_templates_category')
                ->setRequired(TRUE);

        foreach($groupTemplatesCategoriesRow as $category)
        {
            $groupOuterTemplatesCategoryEL->addMultiOption($category['cat_id'], $category['cat_name']);
        }

        $this->addElement($groupOuterTemplatesCategoryEL);


        $groupInnerTemplatesCategoryEL =
            $this->createElement('select','group_inner_templates_category',
                                                                           array('decorators' => MC_Admin_Form_Form::$elementDecorators))
                                                                ->setLabel('group_inner_templates_category')
                                                                ->setRequired(TRUE);

        foreach($groupTemplatesCategoriesRow as $category)
        {
            $groupInnerTemplatesCategoryEL->addMultiOption($category['cat_id'], $category['cat_name']);
        }

        $this->addElement($groupInnerTemplatesCategoryEL);

        $this->addElement('checkbox','allow_inner_container',array('label'=>'allow_inner_container'));

        $this->addElement('text','css_class',array('label'=>'css_class'));

        MC_Models_Hooks::call('create_grid_params',$this);

    }
}