<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mac
 * Date: 5/6/13
 * Time: 2:00 AM
 * To change this template use File | Settings | File Templates.
 */

class  App_Widgets_Admin_Forms_GirdParams extends MC_Admin_Form_SubForm
{


    public function init()
    {
        $data = $this->getAttrib('data');

        $this->removeAttrib('data');


        $this->addElement('checkbox','allow_inner_container',array('label'=>'allow_inner_container'));

        $this->addElement('text','css_class',array('label'=>'css_class'));

        MC_Models_Hooks::call('create_grid_params',$this);

    }
}