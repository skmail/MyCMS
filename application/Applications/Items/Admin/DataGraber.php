<?php

class App_Items_Admin_DataGraber
{

    protected $_search;

    protected $_errors = array();

    static $presets = array();
    
    public function __construct($application = array())
    {
        $this->application = $application;
    }

    public function search($name, $pattern)
    {

        if ($name != "" && $pattern != "")
        {
            $this->_search[$name] = $pattern;
        }

    }
    
    public function setPreset($preset)
    {
        $this->_search = $this->presets($preset);
    }

    public function getData($url)
    {

        if ($this->_verifyUrl($url))
        {
            return $this->_fetchUrlData($url);
        }
        else
        {
            return false;
        }

    }

    public function setData()
    {
        
    }

    protected function _fetchUrlData($url)
    {

        if(!$data = $this->_connect($url))
        {
            return false;
        } 
        
        if (!count($this->_search))
        {
            return false;
        }
        
        $results = array();
        
        foreach ($this->_search as $key => $pattern)
        {
            
            if($pattern == '')
            {
                continue;
            }
            
            $matches = array();
            
            $pattern = str_replace('{content}', '(.*?)', $pattern);
          
            $pattern = '~'. addslashes($pattern) .'~i';
        
            if(preg_match($pattern, $data,$matches))
            {
                $results['result_' . $key] = $matches[1];                
            }
        }
        
        
        return $results;

    }

    protected function _verifyUrl($url)
    {

        if (!filter_var($url, FILTER_VALIDATE_URL))
        {
            $this->_errors[] = Zend_Registry::get('Zend_Translate')->translate('Invalid Url');
            return false;
        }

        return true;

    }

    public function errors()
    {


        if (count($this->_errors))
        {
            return $this->_errors;
        }

        return false;

    }

    protected function _connect($url)
    {
        
        $client = new Zend_Http_Client($url);
        
        $response = $client->request();


        if (! $response->isError())
        {
            return $response->getBody();
        }
        else
        {
            $this->_errors[] = Zend_Registry::get('Zend_Translate')->translate('Unable connect with Url');
            return false;
        }

    }
    
    public function presets($preset = '')
    {
        
        $presets = array();
        
        $presets['raya']['item_title'] = '';
        $presets['raya']['item_content'] = '';
        $presets['raya']['item_image'] = '';
        
        
        
        $presets['wikipedia']['item_title'] = '<h1 lang="en" class="firstHeading" id="firstHeading"><span dir="auto">{content}</span></h1>';
        $presets['wikipedia']['item_content'] = '';
        $presets['wikipedia']['item_image'] = '';
        
        
        
        if($preset == '')
        {
            return $presets;
        }
        else
        {
            return $presets[$preset];
        }
        
    }
    
    
    


    

}