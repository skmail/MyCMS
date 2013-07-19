<?php 


    abstract class Frontend_Model_Applications_Application 
                                        extends Frontend_Model_Frontend{


        public $data = array();

        abstract function init($appRow);
         


        public function appendPlugin($content,$pluginGroup,$order = 0)
        {
            $this->data['content'] = $content;
            $this->data['content_plugin_group'] = $pluginGroup;
            $this->data['content_plugin_group_order'] = $order;

        }
        
    }