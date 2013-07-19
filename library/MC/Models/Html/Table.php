<?php


class MC_Models_Html_Table {
    
 
    public function start($attr = array()){
        
          $this->output("<table " .$this->attr($attr). " >");
        
    }
    
    public function end()
    {
         $this->output("</table>"); 
    }
    
    
    public function thead_start($attr = array()){
         $this->output("<thead  " .$this->attr($attr). " >");
    }
    
    public function thead_end(){
        $this->output("</thead>");
    }
    
    public function tbody_start($attr = array())
    {
        $this->output("<tbody  " .$this->attr($attr). " >");
    }
    
    public function tbody_end()
    {
        $this->output("</tbody>");
    }
    
    public function tr_start($attr = array())
    {
        $this->output("<tr " .$this->attr($attr). " >");
    }
    
    public function tr_end()
    {
        $this->output("</tr>");
    }
    
    
    public function th($str,$attr = array())
    {
        $this->output("<th  " .$this->attr($attr). " >".$str."</th>");
    }
    
    public function td($str,$attr = array())
    {
        
        
            $this->output("<td  " .$this->attr($attr). " >".$str."</td>");
    }
    
    
    protected function output($tag)
    {
        
        echo $tag;
        
    }
    
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
    
    
    
    
    
}