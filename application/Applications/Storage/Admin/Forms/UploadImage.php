<?php

class App_Storage_Admin_Forms_GroupLang extends MC_Admin_Form_SubForm {

    public function init() {
        
        
        
        $langs = MC_Core_Loader::appClass('Language','Lang',NULL,'Shared')->LangsList();
        $this->addPrefixPath('MC_Admin_Form', 'MC/Admin/Form');



        foreach ($langs as $langV) {

            $groupLang = new MC_Admin_Form_SubForm();

            if ($langV['lang_default'] ==  $langs->currentLang()) {
                $required = true;
            }else{
                $required = false;
            }
            
            
           $groupLang->addElement('text', 'group_name', 
                                                array(
                                                    'label' => 'Group name', 
                                                    'required'=>$required,
                                                    'maxLength' => '255'));

            $options['id'] = 'cat_lang_' . $langV['lang_id'];

            if ($langV['lang_default'] ==  $langs->currentLang())
                $options['active'] = 'active';
            else
                $options['active'] = '';


            $groupLang->setDecorators(array('FormElements',
                array('HtmlTag', array('tag' => 'div'))
                , array('Tab_Content', array('placement' => 'prepend', 'options' => $options))
            ));



            $groupLang->setElementsBelongTo('group_lang[' . $langV['lang_id'] . ']');




            $this->addSubForm($groupLang, 'group_lang_' . $langV['lang_id']);
        }

        $tabNav = array();

        foreach ($langs->langsList()  as $v) {

            $tabNav[$v['lang_id']]['label'] = $v['lang_name'];
            $tabNav[$v['lang_id']]['href'] = 'cat_lang_' . $v['lang_id'];
            if ($v['lang_default'] == 1)
                $tabNav[$v['lang_id']]['active'] = 'active';
            else
                $tabNav[$v['lang_id']]['active'] = '';
        }

        $this->setDecorators(array('FormElements',
            array('HtmlTag', array('tag' => 'div'))
            , array('Tab_Tab', array('placement' => 'prepend', 'nav' => $tabNav))
        ));
        
        
    }

}
