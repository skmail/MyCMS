<?php

class App_AppsManager_Admin_Forms_Upload extends MC_Admin_Form_BaseForm
{

    public function init()
    {
        $this->setAttrib('enctype', 'multipart/form-data');


        $doc_file = new Zend_Form_Element_File('file');

        $doc_file->setLabel('application_file')
                ->setRequired(true)
                ->setDecorators($this->fileDecorators);

        $this->addElement($doc_file);

        $this->addElement('submit','go',array('label'=>'upload'));
    }

}