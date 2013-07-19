<?php

class App_Plugins_Admin_Forms_GroupLang extends MC_Admin_Form_SubForm
{

    public function init()
    {


        $langs = MC_Core_Loader::appClass('Language', 'Lang', NULL, 'Shared');

        $currentLang = $langs->currentLang();
        $langsList = $langs->langsList();

        $this->addPrefixPath('MC_Admin_Form', 'MC/Admin/Form');



        foreach ($langsList as $langV)
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

            $cat_name = $category_lang->addElement('text', 'group_name', array(
                'label'     => 'Category name',
                'required'  => $required,
                'maxLength' => '255'));

            $options['id'] = 'group_lang_' . $langV['lang_id'];


            $category_lang->setDecorators(array('FormElements',
                array('HtmlTag', array('tag' => 'div'))
                , array('Tab_Content', array('placement' => 'prepend', 'options'   => $options))
            ));



            $category_lang->setElementsBelongTo('group_lang[' . $langV['lang_id'] . ']');




            $this->addSubForm($category_lang, 'group_lang_' . $langV['lang_id']);
        }

        $tabNav = array();

        foreach ($langsList as $v)
        {

            $tabNav[$v['lang_id']]['label'] = $v['lang_name'];
            $tabNav[$v['lang_id']]['href'] = 'group_lang_' . $v['lang_id'];
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