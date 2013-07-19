<?php

class App_Plugins_Admin_Forms_PluginLangSubform extends MC_Admin_Form_SubForm
{

    public function init()
    {

        $langs = $langs = MC_Core_Loader::appClass('Language', 'Lang', NULL, 'Shared');





        $this->addPrefixPath('MC_Admin_Form', 'MC/Admin/Form');

        $currentLang = $langs->currentLang();

        foreach ($langs->langsList() as $langV)
        {
            $pluginLang = new MC_Admin_Form_SubForm();

            if ($langV['lang_id'] == $currentLang)
            {
                $options['active'] = 'active';

                $required = true;
            }
            else
            {
                $required = false;
                $options['active'] = '';
            }

            $pluginLang->addElement('text', 'plugin_name', array(
                'required'   => $required,
                'label'      => 'Plugin Name',
                'maxLength'  => '255',
                'decorators' => MC_Admin_Form_Form::$elementDecorators
            ));


            $options['id'] = 'plugin_lang' . $langV['lang_id'];



            $pluginLang->setDecorators(array('FormElements',
                array('HtmlTag', array('tag' => 'div'))
                , array('Tab_Content', array('placement' => 'prepend', 'options'   => $options))
            ));



            $pluginLang->setElementsBelongTo('plugin_lang[' . $langV['lang_id'] . ']');




            $this->addSubForm($pluginLang, 'plugin_lang' . $langV['lang_id']);
        }

        $tabNav = array();

        foreach ($langs->langsList() as $v)
        {

            $tabNav[$v['lang_id']]['label'] = $v['lang_name'];
            $tabNav[$v['lang_id']]['href'] = 'plugin_lang' . $v['lang_id'];
            if ($v['lang_id'] == $currentLang)
                $tabNav[$v['lang_id']]['active'] = 'active';
            else
                $tabNav[$v['lang_id']]['active'] = '';
        }

        $this->setDecorators(array('FormElements',
            array('HtmlTag', array('tag' => 'div'))
            , array('Tab_Tab', array('placement' => 'prepend', 'nav'       => $tabNav))
        ));

    }

}