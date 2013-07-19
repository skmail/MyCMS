<?php

class App_MailList_Admin_MailList extends Admin_Model_ApplicationAbstract
{

    public $renderWindow = true;

    public $plugin = array();

    protected $query = null;

    public function __construct($application = array())
    {
        parent::__construct($application);

        $this->application['nav']->append('Maillists');
        
        $this->_forms = new App_MailList_Admin_Forms($application);
        
        $this->application['renderWindow'] = $this->renderWindow;
    }

    public function index()
    {

        
        $this->application['sidebar'] = 'indexSidebar';
        
        return $this->application;

    }
    
    public function group()
    {
        
        $do = (isset($options['do']))?$options['do']: $this->_Zend->getRequest()->getParam('do');
     
        $this->application['nav']->append(Zend_Registry::get('Zend_Translate')->translate('add') .' '.Zend_Registry::get('Zend_Translate')->translate('maillist_group'));
        
        
        $this->application['groupForm'] = (isset($options['groupForm']))?$options['groupForm']: $this->_forms->group();
      
        return $this->application;
    }

}