<?php
class App_Widgets_Admin_Forms_Settings extends MC_Admin_Form_SubForm
{
    public function init($options = array())
    {
        $MC =& MC_Core_Instance::getInstance();
        $MC->load->appLibrary('Grids','Grids','Widgets');
        $gridsList = $MC->Grids->gridsList();
        $gridsListElement = $this->createElement('select', 'grid_name', array(
            'decorators' => MC_Admin_Form_Form::$elementDecorators))->setLabel('grid_name')->setRequired(TRUE);
        if(is_array($gridsList)){
            foreach($gridsList as $gridName)
            {
                $gridsListElement->addMultiOption($gridName, $gridName);
            }
        }
        $this->addElement($gridsListElement);

    }
}