<?php

class App_Users_Admin_Forms_UsergroupLang extends MC_Admin_Form_SubForm
{
    public function init()
    {
        $MC =& MC_Core_Instance::getInstance();
        $this->addPrefixPath('MC_Admin_Form', 'MC/Admin/Form');

        foreach ($MC->model->lang->langsList() as $lang)
        {
            $usergroup_lang = new MC_Admin_Form_SubForm();

            if ($lang['lang_id'] ==  $MC->model->lang->currentLang('lang_id')){
                $required = true;
            }else{
                $required = false;
            }
            $usergroup_name = $usergroup_lang->addElement('text', 'usergroup_name', array(
                'label'     => 'usergroup_name',
                'required'  => $required,
                'maxLength' => '255',
                'class'     => 'large-input'));
            $options['id'] = 'usergroup_lang_' . $lang['lang_id'];

            if ($lang['lang_id'] ==  $MC->model->lang->currentLang('lang_id')){
                $options['active'] = 'active';

            }else{
                $options['active'] = '';
            }
            $usergroup_name->setDecorators($this->tabContentDeco($options));
            $usergroup_name->setElementsBelongTo('usergroup_lang[' . $lang['lang_id'] . ']');
            
            $this->addSubForm($usergroup_name, 'usergroup_name_' . $lang['lang_id']);
        }
        $tabNav = array();
        foreach ($MC->model->lang->langsList()  as $v)
        {
            $tabNav[$v['lang_id']]['label'] = $v['lang_name'];
            $tabNav[$v['lang_id']]['href'] = 'usergroup_lang_' . $v['lang_id'];
            if ($v['lang_id'] == $MC->model->lang->currentLang('lang_id')){
                $tabNav[$v['lang_id']]['active'] = 'active';
            }
            else{
                $tabNav[$v['lang_id']]['active'] = '';
            }
        }
        $this->setDecorators($this->tabDeco($tabNav));
    }

}