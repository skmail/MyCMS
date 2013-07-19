<?php

class MC_Admin_Model_Order {

    
    protected $db ;


    public function __construct() {
        $this->db = Zend_Registry::get('db');
    }

    public function save($options) {

        $request = Zend_Controller_Front::getInstance()->getRequest()->getPost();
        
        $orders = $request['order'];
        
        if (!is_array($orders))
            return false;
        
        if (count($orders) == 0)
            return false;
      
        
        foreach ($orders as $order => $id) {
            
            $options['primary_val'] = $id; 
            
            $options['field_value'] = $order + 1;
            $saveQuery = $this->_saveOrderQuery($options);
        }
        
        return true;
    }

    protected function _saveOrderQuery($options = array()) {
      
        if (!isset($options['table']))
            return;

        if (!isset($options['field']))
            return;

        if (!isset($options['primary']))
            return;
      
        $where = $this->db->quoteInto($options['primary'] . " = ?", $options['primary_val']);
        
        $data[$options['field']] = $options['field_value'];
        
        $this->db->update($options['table'], $data, $where);
       
    }

}

