<?php

class Admin_Form_Applications_Articles_Category extends Zend_Form
{

    public function init()
    {
        
        /* Form Elements & Other Definitions Here ... */
        
     
        $this->setAction('window/saveCat');
        $this->setMethod('post');
        
       $this->addElement('text','cat_name',array(
                                            'required'=>true,
                                            'label'=>'Category name',
                                            'maxLength'=>'255'
                                             ));

       $this->addElement('text','cat_url',array(
                                            'label'=>'Category Url',
                                            'filters'=>array('StringTrim','StringToLower'),
                                           ));
      
       
        $catStatus = $this->createElement('select','cat_status')->setLabel('Status: ')->setRequired(true);
        $catStatus->addMultiOptions(array(1=>'Active',2=>'Hidden'));
        $this->addElement($catStatus);
        
        
        
       $this->addElement('textarea','cat_desc',array('label'=>'Description','rows'=>'5'));
       
        
        
        
        $this->addElement('hidden','cat_id');
        $this->addElement('hidden','do');
        $this->addElement('submit','go',array('label'=>'Save','class'=>'submit'));
        
        
        $this->setDecorators(array(
            'formElements',
            array('HtmlTag',array('tag'=>'dl','class'=>'windowForm')),
            array('Description',array('placement'=>'prepend')),
            'Form'
        ));
        
        
        
    }


}

