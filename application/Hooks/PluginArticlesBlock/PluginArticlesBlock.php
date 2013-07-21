<?php

class Hooks_PluginArticlesBlock_PluginArticlesBlock
{
    public function setOptions($form)
    {


        $thumb = new MC_Admin_Form_SubForm();

        $thumb->addPrefixPath('MC_Admin_Form', 'MC/Admin/Form');

        $thumb->addElement('checkbox', 'use', array(
            'decorators' => MC_Admin_Form_Form::$elementDecorators,
            'value'      => 1));

        $thumb->addElement('text', 'width', array(
            'label'      => 'Width',
            'required'   => true,
            'validators' => array('Digits'),
            'decorators' => MC_Admin_Form_Form::$elementDecorators,
            'value'      => 60,
            'class'      => 'input-mini'));

        $thumb->addElement('text', 'height', array(
            'label'      => 'Height',
            'decorators' => MC_Admin_Form_Form::$elementDecorators,
            'value'      => 45,
            'class'      => 'input-mini'
        ));

        $thumb->setElementDecorators(array(
            'ViewHelper',
            'Errors',
            array('Inline_Element')
        ));

        $thumb->setDecorators(array('FormElements',
            array('Inline_Wrapper', array('title'    => 'Thumb Image Settings', 'elements' => $thumb->getElements()))
        ));

        $form->addSubForm($thumb, 'thumb');

        $customSize = new MC_Admin_Form_SubForm();

        $customSize->addPrefixPath('MC_Admin_Form', 'MC/Admin/Form');


        $customSize->addElement('text', 'width', array(
            'label'      => 'Width',
            'validators' => array('Digits'),
            'decorators' => MC_Admin_Form_Form::$elementDecorators,
            'value'      => 100,
            'class'      => 'input-mini'));

        $customSize->addElement('text', 'height', array(
            'label'      => 'Height',
            'decorators' => MC_Admin_Form_Form::$elementDecorators,
            'value'      => 80,
            'class'      => 'input-mini'
        ));

        $customSize->addElement('text', 'noOfImage', array(
            'label'      => 'Image no.',
            'decorators' => MC_Admin_Form_Form::$elementDecorators,
            'class'      => 'input-mini'));


        $showType = new Zend_Form_Element_Select('showType');
        $showType->setRequired(true)
            ->setLabel('Show?')
            ->setMultiOptions(array('none'        => 'Don\'t use', 'everyImage'  => 'Every Image', 'imageNumber' => 'By image no.'))
            ->setSeparator('&nbsp;')
            ->setAttrib('class', 'input-small2')
        ;

        $customSize->addElement($showType);


        $customSize->setElementDecorators(array(
            'ViewHelper',
            'Errors',
            array('Inline_Element')
        ));

        $customSize->setDecorators(array('FormElements',
            array('Inline_Wrapper', array('title' => 'Custom Image Settings', 'help'  => 'You can add change images sizes for every item (by set number of image strat from 1) or (By the rate of image that will be changed) '))
        ));


        $form->addSubForm($customSize, 'customSize');

    }


    function create_group_params($form,$data)
    {

        $db = Zend_Registry::get('db');

        $groupTemplatesCategories = $db->select()->from('templates_categories');

        $groupTemplatesCategories->where('theme_id = ?',$data['theme_id'] );

        $groupTemplatesCategoriesRow = $db->fetchAll($groupTemplatesCategories);

        $groupOuterTemplatesCategoryEL =
            $form->createElement('select','articleBlocks_inner_templates_category',
                array('decorators' => MC_Admin_Form_Form::$elementDecorators))
                ->setLabel('articleBlocks_inner_templates_category')
                ->setRequired(TRUE);

        foreach($groupTemplatesCategoriesRow as $category)
        {
            $groupOuterTemplatesCategoryEL->addMultiOption($category['cat_id'], $category['cat_name']);
        }

        $form->addElement($groupOuterTemplatesCategoryEL);

    }
}