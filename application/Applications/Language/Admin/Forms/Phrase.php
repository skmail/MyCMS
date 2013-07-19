<?php


class App_Language_Admin_Forms_Phrase extends MC_Admin_Form_BaseForm
{
    
    
    public function init(){
        
        
        $phrase_name = $this->createElement('text','phrase_name',array('decorators'=>MC_Admin_Form_Form::$elementDecorators,'required'=>true,'label'=>'Phrase name'));

        
        $phrase_name->addFilter(new Zend_Filter_Word_DashToUnderscore());
        $phrase_name->addFilter(new Zend_Filter_StripTags());
        $phrase_name->addFilter(new Zend_Filter_StringTrim());
        //$phrase_name->addFilter(new Zend_Filter_StringToLower());
        
        
        $phrase_name->addFilter(new Zend_Filter_PregReplace(array('match'=>'/\W+/','replace'=>'-')));
        
        $phrase_name->addFilter(new Zend_Filter_PregReplace(array('match'=>"/[^a-z0-9_\s-]/",'replace'=>'')));        
        
        $phrase_name->addFilter(new Zend_Filter_PregReplace(array('match'=>"/[\s-]+/",'replace'=>"_")));
        
        $phrase_name->addFilter(new Zend_Filter_PregReplace(array('match'=>"/[\s-]+/",'replace'=>"_")));
        
        $phrase_name->addFilter(new Zend_Filter_PregReplace(array('match'=>'/[^\n\x20-\x7E]/','replace'=>'')));
        
     
        
        $this->addElement($phrase_name);
        $phraseLang  = new App_Language_Admin_Forms_PhraseLang();
        
        $phraseLang->setElementsBelongTo('');
        
        $this->addSubForm($phraseLang, 'phrase_lang');
        
        $this->addElement('hidden','do');
        
        $this->addElement('hidden','lang_id');
       
        $this->addElement('hidden','current_phrase_name');
        
        $this->addElement('submit','submit',array('label'=>'Submit'));
        
        $this->setElementsBelongTo('phrase');
    }
    
    
}