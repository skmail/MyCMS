<?php

class App_Users_Admin_Forms_UsergroupLang extends MC_Admin_Form_SubForm
{

    public function init()
    {

        $langs = MC_Core_Loader::appClass('Language', 'Lang', NULL, 'Shared');





        $this->addPrefixPath('MC_Admin_Form', 'MC/Admin/Form');



        foreach ($langs->langsList() as $langV)
        {

            $usergroup_lang = new MC_Admin_Form_SubForm();

            if ($langV['lang_default'] ==  $langs->currentLang())
            {
                $required = true;
            }
            else
            {
                $required = false;
            }


            $usergroup_name = $usergroup_lang->addElement('text', 'usergroup_name', array(
                'label'     => 'Usergroup name',
                'required'  => $required,
                'maxLength' => '255',
                'class'     => 'input-xlarge'));

            $options['id'] = 'cat_lang_' . $langV['lang_id'];
            if ($langV['lang_default'] ==  $langs->currentLang())
                $options['active'] = 'active';
            else
                $options['active'] = '';


            $usergroup_name->setDecorators(array('FormElements',
                array('HtmlTag', array('tag' => 'div'))
                , array('Tab_Content', array('placement' => 'prepend', 'options'   => $options))
            ));



            $usergroup_name->setElementsBelongTo('usergroup_lang[' . $langV['lang_id'] . ']');




            $this->addSubForm($usergroup_name, 'usergroup_name_' . $langV['lang_id']);
        }

        $tabNav = array();

        foreach ($langs->langsList()  as $v)
        {

            $tabNav[$v['lang_id']]['label'] = $v['lang_name'];
            $tabNav[$v['lang_id']]['href'] = 'cat_lang_' . $v['lang_id'];
            if ($v['lang_default'] == 1)
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