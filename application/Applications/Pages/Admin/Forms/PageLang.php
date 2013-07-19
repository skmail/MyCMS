<?php

class App_Pages_Admin_Forms_PageLang extends MC_Admin_Form_SubForm
{

    public function init()
    {
        $langs = MC_Core_Loader::appClass('Language','Lang',NULL,'Shared');
        
        
        $currentLang = $langs->currentLang();
        $langList  = $langs->langsList();
        
        $this->addPrefixPath('MC_Admin_Form', 'MC/Admin/Form');
        foreach ( $langList as $langV)
        {

            $category_lang = new MC_Admin_Form_SubForm();

            if ($langV['lang_id'] ==  $currentLang)
            {
                $required = true;
            }
            else
            {
                $required = false;
            }


            $cat_name = $category_lang->addElement('text', 'page_name', array(
                'label'     => 'Page name',
                'required'  => $required,
                'maxLength' => '255',
                'class'     => 'large-input'));

            $category_lang->addElement('textarea', 'page_content', array('label' => 'Page content', 'rows'  => '5', 'class' => 'editor'));

            $options['id'] = 'page_lang_' . $langV['lang_id'];
            if ($langV['lang_id'] ==  $currentLang)
            {
                $options['active'] = 'active';
            }
            else
            {
                $options['active'] = '';
            }


            $category_lang->setDecorators(array('FormElements',
                array('HtmlTag', array('tag' => 'div'))
                , array('Tab_Content', array('placement' => 'prepend', 'options'   => $options))
            ));



            $category_lang->setElementsBelongTo('page_lang[' . $langV['lang_id'] . ']');

            $this->addSubForm($category_lang, 'page_lang' . $langV['lang_id']);
        }

        $tabNav = array();

        foreach ($langList  as $v)
        {

            $tabNav[$v['lang_id']]['label'] = $v['lang_name'];
            
            $tabNav[$v['lang_id']]['href'] = 'page_lang_' . $v['lang_id'];
            
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