<?php

class MC_Models_Html_Form
{

    protected function attr($attributes = array())
    {

        $attr = array();

        foreach ($attributes as $key => $val)
        {
            $attr[] = $key . '="' . $val . '"';
        }

        $newAttr = implode(' ', $attr);


        return $newAttr;

    }

    public function form_start($attr = array())
    {
        $this->output("<form ".$this->attr($attr).">");
    }
    
    public function form_end()
    {
        $this->output("</form>");
    }
    
    public function submit($name,$value = '',$attr = array())
    {
        $this->output("<input type='submit' name='".$name,"' value='". $value ."' " . $this->attr($attr) . " />" );
    }
    public function output($tag)
    {
        echo $tag;
    }

}