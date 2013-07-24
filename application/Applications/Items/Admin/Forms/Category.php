<?php

class App_Items_Admin_Forms_Category extends MC_Admin_Form_BaseForm
{

    public function init($options = array())
    {



        $MC =& MC_Core_Instance::getInstance();

        //init application data
        $data = $this->getAttrib('data');
        $this->removeAttrib('data');

        //Set form method
        $this->setMethod('post');

        //init custom field form
        $customFields = new MC_Admin_Form_SubForm();

        //remove default decorators for custom fields form
        $customFields->removeDecorator('DtDdWrapper');

        //set new decorators for custom fields form
        $customFields->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag'   => 'div', 'class' => 'form cutomFields')),
        ));

        //add custom fields form to the  main field
        $this->addSubForm($customFields, 'cutomFields');
        
        $category = new MC_Admin_Form_SubForm();

        $category_lang = new App_Items_Admin_Forms_CategoryLang();

        $params = new MC_Admin_Form_SubForm();

        $category->removeDecorator('DtDdWrapper');

        $category_lang->removeDecorator('DtDdWrapper');

        $params->removeDecorator('DtDdWrapper');

        $this->addSubForm($category_lang, 'category_lang');

        $catsList = $category->createElement('select', 'parent_id', array(
                    'decorators' => MC_Admin_Form_Form::$elementDecorators))->setLabel('parent_category')->setRequired(TRUE);


        $cats = $MC->db->select()
                ->from('items_categories')
                ->join('items_categories_lang', 'items_categories.cat_id = items_categories_lang.cat_id')
                ->where('cat_status = ?', 1)
                ->where('folder_id = ?',$data['folder_id']);

        $catsList->addMultiOption(0, 'Main Category');

        foreach ($MC->db->fetchAll($cats) as  $v)
        {
            $childs = explode(',',$data['childs']);

            if (in_array($v['cat_id'],$childs))
            {
                continue;
            }

            $catsList->addMultiOption($v['cat_id'], $v['cat_name']);
        }


        $category->addElement($catsList);


        $category->addElement('text', 'cat_url', array(
            'label'   => 'cat_url',
            'filters' => array('StringTrim', 'StringToLower'),
            'decorators' => MC_Admin_Form_Form::$elementDecorators,
            'class'      => 'large-input'
        ));


        $catStatus = $category->createElement('select', 'cat_status', array(
                    'decorators'    => MC_Admin_Form_Form::$elementDecorators))
                ->setLabel('status')
                ->setRequired(true);
        
        $catStatusArray = $MC->Functions->catStatus();

        $catStatus->addMultiOptions($catStatusArray);

        $category->addElement($catStatus);


        $this->addSubForm($category, 'category');

        $this->addElement('hidden', 'cat_id');
        $this->addElement('hidden', 'folder_id');
        $this->addElement('hidden', 'do');
        $this->addElement('submit', 'go', array('value' => 'Save'));

    }

}

