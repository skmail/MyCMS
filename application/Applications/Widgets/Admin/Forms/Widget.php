<?php

class App_Widgets_Admin_Forms_Widget extends MC_Admin_Form_BaseForm {

    public function init() {

        $this->setAttrib('class', 'savePlugin');

        $app = $this->getAttrib('app');

        $this->removeAttrib('app');

        $MC =& MC_Core_Instance::getInstance();

        $widget = new MC_Admin_Form_SubForm();

        $showInForm = new MC_Admin_Form_SubForm();

        $apps = $MC->Functions->applicationsPlugins();

        foreach ($apps as $appId => $app) {

            $appForm = new MC_Admin_Form_SubForm();

            foreach ($app as $pageKey => $pages) {

                $pageForm = new MC_Admin_Form_SubForm();

                $pagesList = $pageForm->createElement('MultiCheckbox', "'" . $pageKey . "'",
                    array(
                        'label' => 'Show in',
                        'isArray' => true,
                        'decorators' => MC_Admin_Form_Form::$elementDecorators,
                        'class'=>'showin-list'
                    ));
                foreach($pages as $pageId=>$pageLabel)
                {
                    $pagesList->addMultiOption($pageId, $pageLabel);
                }
                $appForm->addElement($pagesList);
            }
            $showInForm->addSubForm($appForm,$appId);
        }

        $showInForm->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'div', 'class' => 'sub-form show-in'))
        ));


        $this->addSubForm($showInForm, 'application', 1);

        $db = Zend_Registry::get('db');

        $widgetLang = new App_Widgets_Admin_Forms_WidgetLang();

        $widgetLang->setElementsBelongTo('');

        $this->addSubForm( $widgetLang,'plugin_lang',2);

        $groupsList = $widget->createElement('select', 'group_id', array('decorators' => MC_Admin_Form_Form::$elementDecorators))
                ->setLabel('Plugin Group')
                ->setRequired(TRUE);


        $groups = $db->select()
                ->from('plugins_groups')
                ->join('plugins_groups_lang', 'plugins_groups.group_id = plugins_groups_lang.group_id')
                ->where('plugins_groups.group_status = ?', 1);


        $groupsList->addMultiOption(0, 'Manual Call');

        foreach ($db->fetchAll($groups) as $k => $v) {
            $groupsList->addMultiOption($v['group_id'], $v['group_name']);
        }

        $widget->addElement($groupsList);

        $pluginStatus = $this->createElement('select', 'plugin_status', array(
                    'decorators' => MC_Admin_Form_Form::$elementDecorators))
                ->setLabel('Plugin Status')
                ->setRequired(TRUE);

        $pluginStatus->addMultiOption(1, 'Active');

        $pluginStatus->addMultiOption(2, 'Hidden');


        $widget->addElement($pluginStatus);

        $widget->removeDecorator('DtDdWrapper');
        $widgetLang->removeDecorator('DtDdWrapper');

        $this->addSubForm($widget, 'plugin', 3);

        $this->addElement('hidden', 'do', array('required' => true, 'order' => 5));
        $this->addElement('hidden', 'plugin_resource_id', array('required' => true, 'order' => 6, 'belongsTo' => 'plugin'));
        $this->addElement('hidden', 'plugin_id', array('order' => 7));
        $this->addElement('submit', 'go', array('label' => 'Edit', 'order' => 8));

    }
}

