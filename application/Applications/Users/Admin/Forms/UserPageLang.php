<?php

class App_Users_Admin_Forms_UserPageLang extends MC_Admin_Form_SubForm
{

    public function init()
    {

        $langs = MC_Core_Loader::appClass('Language', 'Lang', NULL, 'Shared');

        $this->addPrefixPath('MC_Admin_Form', 'MC/Admin/Form');

        $langsList = $langs->langsList();

        $currentLang = $langs->currentLang();

        foreach ($langsList as $langV)
        {
            $user_page_lang = new MC_Admin_Form_SubForm();

            if ($langV['lang_id'] ==  $currentLang)
            {
                $required = true;
            }
            else
            {
                $required = false;
            }

            $user_page_lang->addElement('text', 'user_page_name', array(
                'label'     => 'Page name',
                'required'  => $required,
                'maxLength' => '255',
                'class'     => 'input-xlarge'));

            $options['id'] = 'user_page_lang_' . $langV['lang_id'];

            if ($langV['lang_id'] ==  $currentLang)
            {
                $options['active'] = 'active';
            }
            else
            {
                $options['active'] = '';
            }

            $user_page_lang->setDecorators(array('FormElements',
                array('HtmlTag', array('tag' => 'div'))
                , array('Tab_Content', array('placement' => 'prepend', 'options'   => $options))
            ));
            $this->addSubForm($user_page_lang,$langV['lang_id']);
        }

        $tabNav = array();

        foreach ($langs->langsList() as $v)
        {


            $tabNav[$v['lang_id']]['label'] = $v['lang_name'];
            $tabNav[$v['lang_id']]['href'] = 'user_page_lang_' . $v['lang_id'];

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