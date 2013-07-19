<?php

class App_Items_Admin_Forms_CustomField extends MC_Admin_Form_BaseForm
{

    public function init($options = array())
    {
        
        $this->addElement('text', 'field_name', array('label'=>'Field Name','required'=>true));

        $fieldsTypes = array('text'=>'text','select'=>'select','radio'=>'radio','checkbox'=>'checkbox');
        $fieldsTypesSettings = array();

        $filesTypesPath = rtrim(APPLICATION_PATH,'/') . '/Applications/Items/Shared/FieldsTypes/';

        if ($handle = opendir($filesTypesPath)) {
            while (false !== ($fieldDir = readdir($handle))) {
                if(is_dir($filesTypesPath.$fieldDir) && $fieldDir != "." && $fieldDir != "..")
                {

                    $fieldTypeClass =  'App_Items_Shared_FieldsTypes_'.$fieldDir.'_Field';
                    $fieldTypeObject = new $fieldTypeClass($fieldDir);
                    if($fieldTypeObject instanceof Zend_Form_Element)
                    {
                        $fieldName = (isset($fieldTypeObject->name) && $fieldTypeObject->name != "")?$fieldTypeObject->name:$fieldDir;
                        $fieldsTypes[$fieldName] = $fieldName;
                        if(method_exists($fieldTypeObject,'settings'))
                        {
                            $fieldTypeSetting = $fieldTypeObject->settings();
                            if($fieldTypeSetting instanceof MC_Admin_Form_SubForm)
                            {
                                $fieldTypeSetting->setAttrib('class','settings_'.$fieldDir);
                                $fieldsTypesSettings[$fieldDir] = $fieldTypeSetting;
                            }
                        }
                    }
                }
            }
            closedir($handle);
        }


        $catsList = $this->createElement('select', 'field_type', array(
                    'decorators' => MC_Admin_Form_Form::$elementDecorators))->setLabel('Field Type')->setRequired(TRUE);
        
        foreach ($fieldsTypes as $fieldKey=>$fieldName)
        {
            $catsList->addMultiOption($fieldKey, $fieldName);
        }
        
        $this->addElement($catsList);

        $customFieldLang = new App_Items_Admin_Forms_CustomFieldLang();
        
        $customFieldLang->setElementsBelongTo('');
        
        
        $this->addElement('checkbox', 'multi_lang', array('label' => 'Multilingual','id'=>'multilingual_field'));
        $this->addSubForm($customFieldLang, 'MC_key_lang');

        $this->setElementsBelongTo('MC_field');
        
        
        $this->addElement('hidden','folder_id');
        $this->addElement('hidden','field_id');
        $this->addElement('hidden','do');
        
        
        $this->addElement('submit','submit',array('label'=>'Submit','class'=>'btn btn-primary'));

    }

}

