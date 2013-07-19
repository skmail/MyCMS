<?php

    class MC_Models_Html_Img{
        
        
        public function __construct($src , array $attributes = array())
        {
            
            if(!empty($src))
            {
                $attributes['src'] = $src;
            }
            
            $attr = array();
            
            foreach ($attributes as $key=>$val)
            {
                $attr[] = $key . '="'.$val.'"';
            }
            
            $newAttr  = implode(' ',$attr);
            
            return  "<img ".$newAttr." />";
            

        }

        

    }