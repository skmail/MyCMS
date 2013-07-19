<?php

class App_Storage_Admin_Forms_FileByUrl extends MC_Admin_Form_BaseForm {

    public function init() {


        $this->setAttrib('class', 'disSubmit uploadFileForm');

        $this->addElement('text', 'file_url', array(
            'label' => 'File Url',
            'required' => true
        ));

        $groupsList = $this->createElement('select', 'group_id', array('decorators' => MC_Admin_Form_Form::$elementDecorators))
                ->setLabel('File Group')
                ->setRequired(TRUE);

        $groups = App_Storage_Admin_Storage::groupQuery(0, false, false, true);

        $db = Zend_Registry::get('db');

        foreach ($db->fetchAll($groups) as $k => $v) {
            $groupsList->addMultiOption($v['group_id'], $v['group_name']);
        }

        $this->addElement($groupsList);

        $this->addElement('hidden', 'file_id');

        $this->addElement('hidden', 'do');

        $this->addElement('submit', 'go', array('label' => 'Upload'));
    }

}