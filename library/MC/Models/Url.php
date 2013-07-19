<?php


class MC_Models_Url{
    
    public static function friendly($url){
        
        $url = strtolower($url);
        
        $url = preg_replace('/\W+/', '-', $url);
    
        $url = preg_replace("/[^a-z0-9_\s-]/", "", $url);
        
        $url = preg_replace("/[\s-]+/", " ", $url);
 
        $url = preg_replace("/[\s_]/", "-", $url);
        
        $url = trim($url,'-');
        
        return $url;
    }
    
    
    
    
    public function adminApp($prefix){
        
        $url = Admin_Model_System_Application::appUrl($prefix);
        
        return $url;
    }
}