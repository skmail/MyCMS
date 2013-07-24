<?php

class App_Widgets_Admin_Forms_Widget extends MC_Admin_Form_BaseForm {

    public function init() {

        $this->setAttrib('class', 'saveWidget');
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
                        'label' => $appId,
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

        $rightForm = new MC_Admin_Form_SubForm();
        $rightForm->setSubForms(array('application'=>$showInForm));
        //$this->addSubForm($showInForm, 'application', 1);

        $leftForm = new MC_Admin_Form_SubForm();

        $widgetLang = new App_Widgets_Admin_Forms_WidgetLang();
        $widgetLang->setElementsBelongTo('');
        //$this->addSubForm( $widgetLang,'plugin_lang',2);

        $groupsList = $widget->createElement('select', 'group_id',
                                                array('decorators' => MC_Admin_Form_Form::$elementDecorators))
                ->setLabel('widget_group')
                ->setRequired(TRUE);
        $groups = $MC->db->select()
                ->from('widgets_groups')
                ->join('widgets_groups_lang', 'widgets_groups.group_id = widgets_groups_lang.group_id')
                ->where('widgets_groups.group_status = ?', 1)
                ->where('lang_id = ?',$MC->model->lang->currentLang('lang_id'));
        $groupsList->addMultiOption(0, 'Manual Call');
        foreach ($MC->db->fetchAll($groups) as $k => $v) {
            $groupsList->addMultiOption($v['group_id'], $v['group_name']);
        }
        $widget->addElement($groupsList);
        $pluginStatus = $this->createElement('select', 'widget_status', array(
                    'decorators' => MC_Admin_Form_Form::$elementDecorators))
                    ->setLabel('widget_status')
                    ->setRequired(TRUE);
        $pluginStatus->addMultiOption(1, 'Active');
        $pluginStatus->addMultiOption(2, 'Hidden');
        $widget->addElement($pluginStatus);
        $widget->removeDecorator('DtDdWrapper');
        $widgetLang->removeDecorator('DtDdWrapper');
        $leftForm->setSubForms(array('widget_lang'=>$widgetLang,'plugin'=>$widget,'widget_params'=>$this->getAttrib('widget_params')));
        //$this->addSubForm($widget, 'plugin', 3);
        $this->removeAttrib('widgetForm');

        $rightForm->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'div','class'=>'float_1 one-fifth')),
        ));
        $leftForm->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'div','class'=>'float_2 four-fifth')),
        ));
        $rightForm->setElementsBelongTo('');
        $leftForm->setElementsBelongTo('');
        $this->setSubForms(array(
            'left'  => $rightForm,
            'right' => $leftForm
        ));
        $this->addElement('hidden', 'do', array('required' => true, 'order' => 5));
        $this->addElement('hidden', 'widget_source_id', array('required' => true, 'order' => 6, 'belongsTo' => 'plugin'));
        $this->addElement('hidden', 'widget_id', array('order' => 7,'belongsTo' => 'plugin'));
        $this->addElement('submit', 'go', array('label' => 'Edit', 'order' => 8));
    }
}

