<?php

class Plugins_Menu_Form extends MC_Admin_Form_SubForm {

    public function init() {
        $db = Zend_Registry::get('db');

        $app = $this->getAttrib('app');
        $this->removeAttrib('app');
        
        
        $outerTemplates = $this->createElement('select', 'outer_template',
                                         array('decorators' => $this->elementDecorators))
                                         ->setLabel('Outer Template')
                ->setRequired(TRUE);
        $templates = $db->select()->from('templates')
                                  ->where('cat_id = (select cat_id  from templates_categories where cat_name = "outer_templates")');

        foreach ($db->fetchAll($templates) as $k => $v) {
            $outerTemplates->addMultiOption($v['template_id'], $v['template_name']);
        }

        $this->addElement($outerTemplates);
        
        
        $outerTemplates = $this->createElement('select', 'menu_template',
                                         array('decorators' => $this->elementDecorators))
                                         ->setLabel('Menu List Template')
                ->setRequired(TRUE);
        $templates = $db->select()->from('templates')
                                  ->where('cat_id = (select cat_id  from templates_categories where cat_name = "menus")');

        foreach ($db->fetchAll($templates) as $k => $v) {
            $outerTemplates->addMultiOption($v['template_id'], $v['template_name']);
        }

        $this->addElement($outerTemplates);

        
        $this->addPrefixPath('Plugins_Menu', rtrim(APPLICATION_PATH,'/').'/Plugins/Menu');
        
        $menuElements = new MC_Admin_Form_SubForm();

        $menuElements->setDecorators(array('FormElements',
                                        array('HtmlTag',array('tag'=>'div'))
                                         ,array('Menu_Menu',array('placement'=>'append','app'=>$app))
                                        ));

        $this->addSubForm($menuElements,'menu');
        
    }

    public function process(array $data) {

        
        return $data;
    }

}