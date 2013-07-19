<?php

class MC_Models_Html_Html
{

    
    public function __construct()
    {
    
    }
    
    public function attr($attributes = array())
    {

        $attr = array();

        foreach ($attributes as $key => $val)
        {
            $attr[] = $key . '="' . $val . '"';
        }

        return implode(' ', $attr);

    }
    
    protected function output($tag)
    {
        echo $tag;
    }
    
    
    public function div_start($attr = array())
    {
        
        $this->output("<div ".$this->attr($attr)." >");
        
    }
    
    public function div_end()
    {
        
        $this->output("</div>");
        
    }
    

}