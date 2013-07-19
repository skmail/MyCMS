<?php

class App_Storage_Admin_Forms_File extends MC_Admin_Form_BaseForm {

    public function init() {

        $this->setAttrib('enctype', 'multipart/form-data');



        $this->setAttrib('class', 'disSubmit uploadFileForm');

        $doc_file = new Zend_Form_Element_File('file');

        $doc_file->setLabel('File')
                ->setRequired(true)
                ->setDecorators($this->fileDecorators);

        $this->addElement($doc_file);

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