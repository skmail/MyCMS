<?php

class Admin_Form_Applications_Articles_Article extends Zend_Form
{

    public function init()
    {
        
        /* Form Elements & Other Definitions Here ... */
        
     
        $this->setAction('window/save');
        $this->setMethod('post');
        
       $this->addElement('text','item_title',array(
                                            'required'=>true,
                                            'label'=>'Item title',
                                            'maxLength'=>'255'
                                             ));

       $this->addElement('text','item_url',array(
                                            'label'=>'Item URL',
                                            'filters'=>array('StringTrim','StringToLower'),
                                           ));
       
       $this->addElement('textarea','item_content',array(
                                            'required'=>true,
                                            'label'=>'Item content',
                                            'rows'=>'5'
                                        ));

       
        $itemStatus = $this->createElement('select','item_status')->setLabel('Status: ')->setRequired(true);

        $itemStatus->addMultiOptions(array(1=>'Published',2=>'Draft',3=>'Trash'));
          
        $this->addElement($itemStatus);
        
        
        $catsList = $this->createElement('select', 'category_id')->setLabel('Category')->setRequired(TRUE);
        
        $db =Zend_Registry::get('db');
        
        $cats = $db->select()->from('items_categories')->join('items_categories_lang','items_categories.cat_id = items_categories_lang.cat_id')->where('cat_status = ?',1);
            foreach($db->fetchAll($cats) as $k=>$v){
                    $catsList->addMultiOption($v['cat_id'],$v['cat_name']);
            }
                
    
       $this->addElement($catsList);
        

       $this->addElement('hidden','item_id');
       //$this->addElement('hidden','category_id');
       
        
        $this->addElement('submit','add',array('label'=>'Edit','class'=>'submit'));
        
        
        $this->setDecorators(array(
            'formElements',
            array('HtmlTag',array('tag'=>'dl','class'=>'windowForm')),
            array('Description',array('placement'=>'prepend')),
            'Form'
        ));
        
        
        
    }


}

