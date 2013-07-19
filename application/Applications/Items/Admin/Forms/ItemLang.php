<?php

class App_Items_Admin_Forms_ItemLang extends MC_Admin_Form_SubForm
{

    public function init()
    {

        $MC =& MC_Core_Instance::getInstance();

        $langs = MC_Core_Loader::appClass('Language', 'Lang', NULL, 'Shared');

        $currentLang = $langs->currentLang();
        $item = $this->getAttrib('data');
        $this->removeAttrib('data');

        $fields = $this->getAttrib('fields');
        $this->removeAttrib('fields');

        $this->addPrefixPath('MC_Admin_Form', 'MC/Admin/Form');

        foreach ($langs->langsList() as $langV)
        {
            $item_lang = new MC_Admin_Form_SubForm();

            $itemTitle = $item_lang->createElement('text', 'item_title', array(
                'label'      => 'Post Title',
                'maxLength'  => '255',
                'class'      => 'large-input',
                'decorators' => MC_Admin_Form_Form::$elementDecorators
                    ));

            /*
            $itemContent = $item_lang->createElement('textarea', 'item_content', array(
                'label'      => 'Post content',
                'rows'       => '5',
                'class'      => 'editor',
                'decorators' => MC_Admin_Form_Form::$elementDecorators
                    ));
            $item_lang->addElement($itemContent);
            */

            if ($langV['lang_id'] == $currentLang)
            {
                $itemTitle->setRequired();
                $options['active'] = 'active';
            }
            else
            {
                $options['active'] = '';
            }

            $item_lang->addElement($itemTitle);

            if (is_array($fields['lang_fields']) && count($fields['lang_fields']) > 0)
            {
                $fieldsForm = new MC_Admin_Form_SubForm();

                $MC->Functions->addFieldToform($fields['lang_fields'],$item,$fieldsForm);
                
                $item_lang->addSubForm($fieldsForm,'fields_lang');
            }

            $item_lang->setElementsBelongTo('item_lang[' . $langV['lang_id'] . ']');

            $options['id'] = 'item_lang_' . $langV['lang_id'];

            $item_lang->setDecorators(array('FormElements',
                array('HtmlTag', array('tag' => 'div'))
                , array('Tab_Content', array('placement' => 'prepend', 'options'   => $options))
            ));

            $this->addSubForm($item_lang, 'item_lang_' . $langV['lang_id']);
        }

        $tabNav = array();

        foreach ($langs->langsList() as $v)
        {

            $tabNav[$v['lang_id']]['label'] = $v['lang_name'];
            $tabNav[$v['lang_id']]['href'] = 'item_lang_' . $v['lang_id'];


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

