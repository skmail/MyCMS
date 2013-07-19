<?php

class App_Music_Music extends Admin_Model_ApplicationInstance {

    public $renderWindow = true;
    public $plugin = array();



    public function __construct($application = NULL) {

        parent::__construct($application);

        
        $this->plugin['categoryTable'] = 'music';
        $this->plugin['categoryKey'] = 'music_id';
        $this->plugin['categoryLabel'] = 'music_name';
            
        
    }
}
