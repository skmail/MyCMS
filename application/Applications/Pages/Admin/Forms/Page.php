<?php

class App_Pages_Admin_Forms_Page extends MC_Admin_Form_BaseForm
{

    public function init()
    {

        $this->setAttrib('class', 'savePage');

        $page = new MC_Admin_Form_SubForm();

        $pageLang = new App_Pages_Admin_Forms_PageLang();

        $page_url = $page->createElement('text', 'page_url', array(
            'label'      => 'Page Url',
            'maxLength'  => '255',
            'decorators' => MC_Admin_Form_Form::$elementDecorators
                ));

        $page->addElement($page_url);


        $pageStatus = $page->createElement('select', 'page_status', array('decorators' => MC_Admin_Form_Form::$elementDecorators))
                ->setLabel('Page Status')
                ->setRequired(TRUE);

        $pageStatus->addMultiOption(1, 'Active');
        $pageStatus->addMultiOption(2, 'Hidden');

        $page->addElement($pageStatus);

        $settings_form = new  MC_Admin_Form_SubForm();

        /* ############## Page template #################### */


        $pageContent = $settings_form->createElement('select', 'page_template', array('decorators' => MC_Admin_Form_Form::$elementDecorators))
            ->setLabel('page_template')
            ->setRequired(TRUE);
        $db = Zend_Registry::get('db');
        $templates = $db->select()->from('templates')->where('cat_id = (select cat_id  from templates_categories where cat_name = "page_content")');

        foreach ($db->fetchAll($templates) as $k => $v)
        {
            $pageContent->addMultiOption($v['template_id'], $v['template_name']);
        }

        $settings_form->addElement($pageContent);

        /* ############## Page plugin group settings #################### */

        $pluginQueries = MC_Core_Loader::appClass('Plugins','Queries',NULL,'Shared');

        $contentPluginGroup = $settings_form->createElement('select', 'plugin_group', array('decorators' => MC_Admin_Form_Form::$elementDecorators))
            ->setLabel('Content plugin group')
            ->setRequired(TRUE);

        $contentPluginGroup->addMultiOption(0, 'manual_setting');

        $groupsList = $pluginQueries->group();

        foreach ($groupsList as $v)
        {
            $contentPluginGroup->addMultiOption($v['group_id'], $v['group_name']);
        }

        $settings_form->addElement($contentPluginGroup);

        $settings_form->addElement('text', 'plugin_group_order', array(
            'required' => true,
            'label'    => 'Plugin content order',
            'filters'  => array('digits',),
            'decorators' => MC_Admin_Form_Form::$elementDecorators,
            'class'      => 'input-mini',
            'value'      => 0
        ));




        $page->addSubForm($settings_form,'settings');
        $page->removeDecorator("DtDdWrapper");
        $pageLang->removeDecorator("DtDdWrapper");

        $page->addElement('hidden', 'page_id');

        $this->addSubForm($page, 'page');

        $pageLang->setElementsBelongTo('');
        $this->addSubForm($pageLang, 'page_lang');

        ///$this->addElement('hidden', 'do', array('required' => true));
        $this->addElement('submit', 'go', array('label' => 'Edit', 'class' => 'submit', 'order' => 7));

        $this->setDecorators(array(
            'formElements',
            array('HtmlTag', array('tag'   => 'div', 'class' => 'windowForm')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));

    }
}