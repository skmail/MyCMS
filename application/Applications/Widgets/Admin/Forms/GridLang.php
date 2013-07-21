<?php

class App_Widgets_Admin_Forms_GridLang extends MC_Admin_Form_SubForm {

    public function init() {
 $langs = MC_Core_Loader::appClass('Language','Lang',NULL,'Shared');
        
        
        
        
        
        $this->addPrefixPath('MC_Admin_Form', 'MC/Admin/Form');

        $currentLang = $langs->currentLang();

        foreach ($langs->langsList() as $langV)
            
            {
            $gridLang = new MC_Admin_Form_SubForm();

            if ($langV['lang_id'] ==  $currentLang) {
                $required = true;
                $options['active'] = 'active';
            }else{
                $required = false;
                $options['active'] = '';
            }
            
            $gridLang->addElement('text', 'grid_name', 
                                                array(
                                                    'label' => 'Grid Name', 
                                                    'required'=>$required,
                                                    'maxLength' => '255'));

            $options['id'] = 'grid_lang_' . $langV['lang_id'];
           

            $gridLang->setDecorators(array('FormElements',
                array('HtmlTag', array('tag' => 'div'))
                , array('Tab_Content', array('placement' => 'prepend', 'options' => $options))
            ));



            $gridLang->setElementsBelongTo('grid_lang[' . $langV['lang_id'] . ']');




            $this->addSubForm($gridLang, 'grid_lang_' . $langV['lang_id']);
        }

        $tabNav = array();

        foreach ($langs->langsList()  as $v) {

            $tabNav[$v['lang_id']]['label'] = $v['lang_name'];
            $tabNav[$v['lang_id']]['href'] = 'grid_lang_' . $v['lang_id'];
            if ($v['lang_id'] == $currentLang)
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