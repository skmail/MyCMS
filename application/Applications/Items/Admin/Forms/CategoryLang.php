<?php

class App_Items_Admin_Forms_CategoryLang extends MC_Admin_Form_SubForm
{

    public function init()
    {

        $langs = MC_Core_Loader::appClass('Language','Lang',NULL,'Shared');
        
        $langList = $langs->langsList();
        $currentLang = $langs->currentLang();
        
        $this->addPrefixPath('MC_Admin_Form', 'MC/Admin/Form');



        foreach ($langList as $langV)
        {

            $category_lang = new MC_Admin_Form_SubForm();

            if ($langV['lang_id'] ==  $currentLang)
            {
                $options['active'] = 'active';
                $required = true;
            }
            else
            {
                $options['active'] = '';
                $required = false;
            }


            $cat_name = $category_lang->addElement('text', 'cat_name', array(
                'label'     => 'cat_name',
                'required'  => $required,
                'maxLength' => '255',
                'class'     => 'large-input'));

            $category_lang->addElement('textarea', 'cat_desc', array('label' => 'description', 'rows'  => '5', 'class' => 'large-input'));

            $options['id'] = 'cat_lang_' . $langV['lang_id'];

            
            $category_lang->setDecorators(array('FormElements',
                array('HtmlTag', array('tag' => 'div'))
                , array('Tab_Content', array('placement' => 'prepend', 'options'   => $options))
            ));



            $category_lang->setElementsBelongTo('cat_lang[' . $langV['lang_id'] . ']');




            $this->addSubForm($category_lang, 'cat_lang_' . $langV['lang_id']);
        }

        $tabNav = array();

        foreach ($langList  as $v)
        {
            $tabNav[$v['lang_id']]['label'] = $v['lang_name'];
            $tabNav[$v['lang_id']]['href'] = 'cat_lang_' . $v['lang_id'];
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