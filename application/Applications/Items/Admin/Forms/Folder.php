<?php

class App_Items_Admin_Forms_Folder extends MC_Admin_Form_BaseForm
{

    public function init($options = array())
    {

        $MC =& MC_Core_Instance::getInstance();

        //init application data
        $data = $this->getAttrib('data');
        $this->removeAttrib('data');

        //Set form method
        $this->setMethod('post');

        $this->addElement('text','folder_url',array('label'=>'folder_url','required'=>true));
        //init custom field form
        $folderStatus = $this->createElement('select', 'folder_status', array('decorators' => MC_Admin_Form_Form::$elementDecorators))
            ->setLabel('status')
            ->setRequired(TRUE);

        $folderStatus->addMultiOption(0, 'folder_status_0');
        $folderStatus->addMultiOption(1, 'folder_status_1');

        $this->addElement($folderStatus);



        $folderLang = new App_Items_Admin_Forms_FolderLang();

        $folderLang->setElementsBelongTo(NULL);
        $this->addSubForm($folderLang,'folder_lang');

        $this->addElement('hidden','folder_id');

        $this->addElement('hidden','do');
        $this->addElement('submit','save',array('label'=>'submit'));

        $this->setElementsBelongTo('folder');
    }
}

