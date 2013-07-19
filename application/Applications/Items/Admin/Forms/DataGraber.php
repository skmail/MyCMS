<?php

class App_Items_Admin_Forms_DataGraber extends MC_Admin_Form_BaseForm
{

    public function init($options = array())
    {




        $results = $this->getAttrib('results');

        $this->removeAttrib('results');

        $presets = $this->createElement('select', 'preset', array(
                    'decorators' => MC_Admin_Form_Form::$elementDecorators))->setLabel('Preset');

        $presetsList = App_Items_Admin_DataGraber::presets();

        $presets->addMultiOption('', 'None');

        foreach ($presetsList as $k => $v)
        {
            $presets->addMultiOption($k, $k);
        }

        $this->addElement($presets);




        $this->addElement('text', 'site_item_url', array('label' => 'Item url', 'class' => 'large-input'));





        $searchForm = new MC_Admin_Form_SubForm();

        $searchForm->addElement('text', 'item_title', array('label' => 'Item title tags', 'class' => 'large-input'));

        $searchForm->addElement('text', 'item_content', array('label' => 'Item content tags', 'class' => 'large-input'));

        $searchForm->addElement('text', 'item_image', array('label' => 'Image tag', 'class' => 'large-input'));

        $this->addSubForm($searchForm, 'search');

        if (is_array($results))
        {
            foreach ($results as $resultKey => $resultVal)
            {
                $resultOutput = new MC_Admin_Form_Element_TextPlaceHolder($resultKey);

                $resultOutput->setValue($resultVal);

                $this->addElement($resultOutput);
            }
        }



        $this->addElement('submit', 'grap');

    }

}