<?php

class App_Items_Admin_Forms_FolderLang extends MC_Admin_Form_SubForm
{

    public function init()
    {

        $MC =& MC_Core_Instance::getInstance();

        $MC->load->model('language','lang');

        $langList = $MC->model->lang->langsList();
        $currentLang = $MC->model->lang->currentLang('lang_id');

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

            $category_lang->addElement('text', 'folder_name', array(
                'label'     => 'folder_lang',
                'required'  => $required,
                'maxLength' => '255',
                'class'     => 'input-large'));

            $options['id'] = 'folder_lang_' . $langV['lang_id'];
            $category_lang->setDecorators($this->tabContentDeco($options));
            $category_lang->setElementsBelongTo('folder_lang[' . $langV['lang_id'] . ']');
            $this->addSubForm($category_lang, 'folder_lang_' . $langV['lang_id']);
        }
        $tabNav = array();
        foreach ($langList  as $v)
        {
            $tabNav[$v['lang_id']]['label'] = $v['lang_name'];
            $tabNav[$v['lang_id']]['href'] = 'folder_lang_' . $v['lang_id'];
            if ($v['lang_id'] == $currentLang)
                $tabNav[$v['lang_id']]['active'] = 'active';
            else
                $tabNav[$v['lang_id']]['active'] = '';
        }

        $this->setDecorators($this->tabDeco($tabNav));

    }

}