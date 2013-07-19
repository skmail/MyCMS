<?php

class App_Language_Admin_Forms_PhraseLang extends MC_Admin_Form_SubForm
{

    public function init()
    {

        $langs = MC_Core_Loader::appClass('Language', 'Lang', NULL, 'Shared');

        $currentLang = $langs->currentLang();

        $this->addPrefixPath('MC_Admin_Form', 'MC/Admin/Form');



        foreach ($langs->langsList() as $langV)
        {
            $category_lang = new MC_Admin_Form_SubForm();

            if ($langV['lang_id'] ==  $currentLang)
            {
                $required = true;
            
                $options['active'] = 'active';
            
            } else
            {
                $options['active'] = '';
                
                $required = false;
            }


            $cat_name = $category_lang->addElement('text', 'phrase_value', array(
                'label'     => 'Phrase value',
                'required'  => $required,
                'maxLength' => '255',
                'class'     => 'large-input'));

            $options['id'] = 'phrase_value_' . $langV['lang_id'];
            

            $category_lang->setDecorators(array('FormElements',
                array('HtmlTag', array('tag' => 'div'))
                , array('Tab_Content', array('placement' => 'prepend', 'options'   => $options))
            ));

            $category_lang->setElementsBelongTo('phrase_value[' . $langV['lang_id'] . ']');

            $this->addSubForm($category_lang, 'phrase_value_' . $langV['lang_id']);
        }

        $tabNav = array();

        foreach ($langs->langsList()  as $v)
        {

            $tabNav[$v['lang_id']]['label'] = $v['lang_name'];
           
            $tabNav[$v['lang_id']]['href'] = 'phrase_value_' . $v['lang_id'];
            
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
            , array('Tab_Tab', array('placement' => 'prepend', 'nav'       => $tabNav))
        ));

    }

}