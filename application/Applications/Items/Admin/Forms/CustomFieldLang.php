<?php

class App_Items_Admin_Forms_CustomFieldLang extends MC_Admin_Form_SubForm
{

    public function init()
    {

        
        $this->setAttrib('id', 'multilingual_field_form');
        $langs = MC_Core_Loader::appClass('Language', 'Lang', NULL, 'Shared');

        $listLangs = $langs->langsList();
        $currentLang = $langs->currentLang();

        $this->addPrefixPath('MC_Admin_Form', 'MC/Admin/Form');

        foreach ($listLangs as $langV)
        {

            $category_lang = new MC_Admin_Form_SubForm();

            if ($langV['lang_id'] == $currentLang)
            {
                $options['active'] = 'active';
                $required = true; 
            }
            else
            {
                $options['active'] = '';
                $required = false;
            }

            $cat_name = $category_lang->addElement('text', 'field_label', array(
                'label'     => 'Field Label',
                'required'  => $required,
                'maxLength' => '255'));

            $options['id'] = 'MC_key_lang_' . $langV['lang_id'];

            $category_lang->setDecorators(array('FormElements',
                array('HtmlTag', array('tag' => 'div'))
                , array('Tab_Content', array('placement' => 'prepend', 'options'   => $options))
            ));

            $category_lang->setElementsBelongTo('MC_key_lang[' . $langV['lang_id'] . ']');

            $this->addSubForm($category_lang, 'MC_key_lang_' . $langV['lang_id']);
        }

        $tabNav = array();

        foreach ($listLangs as $v)
        {

            $tabNav[$v['lang_id']]['label'] = $v['lang_name'];
            $tabNav[$v['lang_id']]['href'] = 'MC_key_lang' . "_" .  $v['lang_id'];

            if ($v['lang_id'] == $currentLang)
            {
                $tabNav[$v['lang_id']]['active'] = 'active';
            }
            else
            {
                $tabNav[$v['lang_id']]['active'] = '';
            }
        }
        
        

        $this->setDecorators(array('FormElements',
            array('HtmlTag', array('tag' => 'div'))
            , array('Tab_Tab', array('placement' => 'prepend','id'=>'multilingual_field_form', 'nav'       => $tabNav))
        ));

    }

}