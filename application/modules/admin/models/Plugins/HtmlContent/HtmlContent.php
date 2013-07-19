<?php

class Admin_Model_Plugins_HtmlContent_HtmlContent extends MC_Admin_Form_SubForm {

    public function init() {
        
        
        
        $db = Zend_Registry::get('db');

        $outerTemplates = $this->createElement('select', 'outer_template', 
                                                array('decorators' => $this->elementDecorators))
                                                ->setLabel('Outer Template')
                                                ->setRequired(TRUE)
                                                ;

        $templates = $db->select()->from('templates')
                ->where('cat_id = (select cat_id  from templates_categories where cat_name = "outer_templates")');

        foreach ($db->fetchAll($templates) as $k => $v) {
            $outerTemplates->addMultiOption($v['template_id'], $v['template_name']);
        }

        $this->addElement($outerTemplates)->isArray(true);
        
        
        $plugin_Content = new MC_Admin_Form_SubForm();
        
        $langs = MC_Core_Loader::appClass('Language','Lang',NULL,'Shared');
        
        $langsList = $langs->LangsList();
        
        $plugin_Content->addPrefixPath('MC_Admin_Form', 'MC/Admin/Form');
        
        foreach ($langsList as $langV) {
            $pluginLang = new MC_Admin_Form_SubForm();

            if ($langV['lang_default'] ==  $langs->currentLang()) {
                $required = true;
            }else{
                $required = false;
            }
            
            $pluginLang->addElement('textarea', 'plugin_content', array(
                'required'=>$required,
                'label' => 'Plugin Content',
                'maxLength' => '255',
                'class'=>'editor',
                'decorators' => $this->elementDecorators
            ));
            
        
            $options['id'] = 'plugin_params_lang' . $langV['lang_id'];
            if ($langV['lang_default'] ==  $langs->currentLang())
                $options['active'] = 'active';
            else
                $options['active'] = '';


            $pluginLang->setDecorators(array('FormElements',
                array('HtmlTag', array('tag' => 'div'))
                , array('Tab_Content', array('placement' => 'prepend', 'options' => $options))
            ));



            $pluginLang->setElementsBelongTo($langV['lang_id']);

            $plugin_Content->addSubForm($pluginLang, 'plugin_params_lang' . $langV['lang_id']);
        }

        $tabNav = array();

        foreach ($langs->langsList()  as $v) {

            $tabNav[$v['lang_id']]['label'] = $v['lang_name'];
            $tabNav[$v['lang_id']]['href'] = 'plugin_params_lang' . $v['lang_id'];
            if ($v['lang_default'] == 1)
                $tabNav[$v['lang_id']]['active'] = 'active';
            else
                $tabNav[$v['lang_id']]['active'] = '';
        }

        $plugin_Content->setDecorators(array('FormElements',
            array('HtmlTag', array('tag' => 'div'))
            , array('Tab_Tab', array('placement' => 'prepend', 'nav' => $tabNav))
        ));

        
        $this->addSubForm($plugin_Content, 'lang_params');
        
        
        
        
    }

}