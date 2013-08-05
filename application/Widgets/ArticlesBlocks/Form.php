<?php

class Widgets_ArticlesBlocks_Form extends MC_Admin_Form_SubForm
{

    public function init()
    {

        $MC =& MC_Core_Instance::getInstance();
        $MC->load->appLibrary('Queries','itemQueries','Items');

        $data = $this->getAttrib('app');
        $this->removeAttrib('app');

        $catsList = $this->createElement('select', 'category_id', array('isArray'    => true, 'decorators' => MC_Admin_Form_Form::$elementDecorators))
                ->setLabel('Articles Categories')
                ->setRequired(TRUE)
                ->setAttrib('multiple', true);


        $categroiesArray = array();
        $folders = $MC->itemQueries->getFolderByLangId($MC->model->lang->currentLang('lang_id'));
        if( is_array($folders)){
            foreach ($folders as $folder)
            {
                $categories = $MC->itemQueries->categoriesTreeBySequence(0,2,array('folder_id'=>$folder['folder_id']));
                if(is_array($categories)){
                    foreach($categories as $cat)
                    {
                        $categroiesArray[$folder['folder_name']][$cat['cat_id']] = $cat['cat_name'];
                    }
                }
            }
        }
        $catsList->setMultiOptions($categroiesArray);
        $this->addElement($catsList);
        $this->addElement('checkbox', 'hasImage', array('label' => 'Show only has image'));
        $outerTemplates = $this->createElement('select', 'outer_template', array('decorators' => MC_Admin_Form_Form::$elementDecorators))
                ->setLabel('Outer Template')
                ->setRequired(TRUE);

        
        $templates = $MC->db->select()->from('templates')->where('parent_template = ?',0);

        foreach ($MC->db->fetchAll($templates) as $k => $v)
        {
            $outerTemplates->addMultiOption($v['template_id'], $v['template_name']);
        }
        $this->addElement($outerTemplates);
        $innerTemplates = $this->createElement('select', 'inner_template', array('decorators' => MC_Admin_Form_Form::$elementDecorators))
                ->setLabel('inner_template')
                ->setRequired(TRUE);
        $templates = $MC->db->select()->from('templates')->where('parent_template = ?',0);
        foreach ($MC->db->fetchAll($templates) as $k => $v)
        {
            $innerTemplates->addMultiOption($v['template_id'], $v['template_name']);
        }

        $this->addElement($innerTemplates);
        $sortBy = $this->createElement('select', 'sort_by', array('label'      => 'Sort By',
            'decorators' => MC_Admin_Form_Form::$elementDecorators));

        $sortBy->addMultiOption('DESC', 'Descending');
        $sortBy->addMultiOption('ASC', 'Ascending');
        $sortBy->addMultiOption('RAND', 'Random');

        $this->addElement($sortBy);

        $rowsShow = new MC_Admin_Form_SubForm();

        $rowsShow->addPrefixPath('MC_Admin_Form', 'MC/Admin/Form');

        $rowsShow->addElement('text', 'num_rows', array('label'      => 'Articles Number',
            'required'   => true,
            'validators' => array('Digits'),
            'decorators' => MC_Admin_Form_Form::$elementDecorators,
            'value'      => 5,
            'class'      => 'input-mini'));

        $rowsShow->addElement('text', 'rows_start_from', array(
            'label'      => 'Articles Start From',
            'required'   => true,
            'validators' => array('Digits'),
            'decorators' => MC_Admin_Form_Form::$elementDecorators,
            'value'      => 0,
            'class'      => 'input-mini'));

        $rowsShow->setElementsBelongTo('');

        $this->addSubForm($rowsShow, 'rowsshow');

        $title = new MC_Admin_Form_SubForm();

        $title->addPrefixPath('MC_Admin_Form', 'MC/Admin/Form');

        $title->addElement('text', 'length', array(
            'label'      => 'Length',
            'required'   => true,
            'validators' => array('Digits'),
            'decorators' => MC_Admin_Form_Form::$elementDecorators,
            'value'      => 10,
            'class'      => 'input-mini'));

        $title->addElement('text', 'complete', array(
            'label'      => 'Last chars',
            'decorators' => MC_Admin_Form_Form::$elementDecorators,
            'value'      => '...',
            'class'      => 'input-mini'
        ));
        $cutType = $title->createElement('select', 'cut_type', array(
            'label'      => 'Cut type',
            'decorators' => MC_Admin_Form_Form::$elementDecorators,
            'class'      => 'input-small2'
                ));

        $cutType->addMultiOption('w', 'Words');
        $cutType->addMultiOption('c', 'Character');

        $title->addElement($cutType);

        $title->setDecorators(array('FormElements',
            array('Elements_Group', array('title'    => 'Title Settings', 'elements' => $title->getElements()))
        ));

        $this->addSubForm($title, 'title');

        $content = new MC_Admin_Form_SubForm();

        $content->addPrefixPath('MC_Admin_Form', 'MC/Admin/Form');

        $content->addElement('text', 'length', array(
            'label'      => 'Length',
            'required'   => true,
            'validators' => array('Digits'),
            'decorators' => MC_Admin_Form_Form::$elementDecorators,
            'value'      => 20,
            'class'      => 'input-mini'));

        $content->addElement('text', 'complete', array(
            'label'      => 'Last chars',
            'decorators' => MC_Admin_Form_Form::$elementDecorators,
            'value'      => '...',
            'class'      => 'input-mini'
        ));

        $cutType = $content->createElement('select', 'cut_type', array(
            'label'      => 'Cut Type',
            'decorators' => MC_Admin_Form_Form::$elementDecorators,
            'class'      => 'input-small2'
                ));

        $cutType->addMultiOption('w', 'Words');

        $cutType->addMultiOption('c', 'Character');

        $content->addElement($cutType);

        $content->setDecorators(array('FormElements',
            array('HtmlTag', array('tag' => 'div')),
            array('Elements_Group', array('title'    => 'Content Settings', 'isHidden' => true))
        ));

        $this->addSubForm($content, 'content');

        $image = new MC_Admin_Form_SubForm();

        $image->addPrefixPath('MC_Admin_Form', 'MC/Admin/Form');

        $image->addElement('checkbox', 'use', array(
            'decorators' => MC_Admin_Form_Form::$elementDecorators,
            'value'      => 1));

        $image->addElement('text', 'width', array(
            'label'      => 'Width',
            'required'   => true,
            'validators' => array('Digits'),
            'decorators' => MC_Admin_Form_Form::$elementDecorators,
            'value'      => 100,
            'class'      => 'input-mini'));

        $image->addElement('text', 'height', array(
            'label'      => 'Height',
            'decorators' => MC_Admin_Form_Form::$elementDecorators,
            'value'      => 80,
            'class'      => 'input-mini'
        ));

        $image->addElement('checkbox', 'crop', array(
            'decorators' => MC_Admin_Form_Form::$elementDecorators,
            'value'      => 1,
            'label'=>'crop'));

        $image->setDecorators(array('FormElements',
            array('Elements_Group', array('title' => 'Main Image Settings'))
        ));

        $this->addSubForm($image, 'image');

        MC_Models_Hooks::call('build_PluginArticlesBlock_Form',$this);

    }

    public function process(array $data)
    {

        return $data;

    }

}

