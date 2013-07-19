<?php

class App_Items_Admin_Forms_Item extends MC_Admin_Form_BaseForm
{

    public function init()
    {

        $MC =& MC_Core_Instance::getInstance();
        $this->setMethod('post');


        $item = $this->getAttrib('data');
        $this->removeAttrib('data');


        $fields = $this->getAttrib('fields');
        $this->removeAttrib('fields');


        $item_lang = new App_Items_Admin_Forms_ItemLang(array('data'=>$item,'fields'=>$fields));

        $item_lang->setElementsBelongTo('');
        $this->addSubForm($item_lang, 'item_lang');

        $item_form = new MC_Admin_Form_SubForm();

        $item_form->addElement('text', 'item_url', array(
            'label'   => 'post_url',
            'filters' => array('StringTrim', 'StringToLower'),
            'class' => 'large-input'
        ));

        $itemStatus = $item_form->createElement('select', 'item_status', array(
                                                                'decorators'=> MC_Admin_Form_Form::$elementDecorators))
                                                                ->setLabel('Status')->setRequired(true);
        $itemsStatusArray = $MC->Functions->itemStatus();
        $itemStatus->addMultiOptions($itemsStatusArray);

        $item_form->addElement($itemStatus);


        $catsList = $item_form->createElement('select', 'cat_id', array(
                    'decorators' => MC_Admin_Form_Form::$elementDecorators))->setLabel('Category')->setRequired(TRUE);

        $db = Zend_Registry::get('db');


        $cats = $db->select()
                ->from('items_categories')
                ->join('items_categories_lang', 'items_categories.cat_id = items_categories_lang.cat_id');

        if($item['childs'])
        {
            $cats->where('items_categories.cat_id IN ('.$item['childs'].') ');
        }
        if($item['parents'])
        {
            $cats->orWhere('items_categories.cat_id IN ('.$item['parents'].')');
        }

        foreach ($db->fetchAll($cats) as $k => $v)
        {
            $catsList->addMultiOption($v['cat_id'], $v['cat_name']);
        }

        $this->addElement($catsList);

        $item_form->removeDecorator('DtDdWrapper');

        $this->addSubForm($item_form, 'item');

        $this->addElement('hidden', 'item_id');

        $this->addElement('hidden', 'do');


        if (is_array($fields['fields']) && count($fields['fields']) > 0)
        {
            $fieldsForm = new MC_Admin_Form_SubForm();

            $MC->Functions->addFieldToform($fields['fields'],$item,$fieldsForm);

            $this->addSubForm($fieldsForm, 'fields');
        }
        $this->addElement('submit', 'go', array('label' => $item['do']));
    }

    private function image($storage_id)
    {

        $file = App_Storage_Storage::fileById($storage_id);

        return "<div class='image'><img src='" . $file . "'/><div class='imageControlls'><a href='#' class='deleteImage'>x</a></div><input type='hidden' name='images[]' value='" . $storage_id . "'></div>";

    }

}

