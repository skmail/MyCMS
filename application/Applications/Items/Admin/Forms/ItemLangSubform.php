<?php

class App_Items_Admin_Forms_ItemLangSubform extends MC_Admin_Form_BaseForm {

    public function init() {
      
        
       $this->addElement('text','item_title',array(
                                            'required'=>true,
                                            'label'=>'Item title',
                                            'maxLength'=>'255',
                                            'decorators'=>MC_Admin_Form_Form::$elementDecorators
                                             ));

       
       
       $this->addElement('textarea','item_content',array(
                                            'required'=>true,
                                            'label'=>'Item content',
                                            'rows'=>'5',
                                            'decorators'=>MC_Admin_Form_Form::$elementDecorators
                                        ));
     
    }
    

}

