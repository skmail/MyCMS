<?php

class Admin_View_Helper_TimeAgo {

    public function timeAgo($time) {
        
        $seconds =  (time() - $time) ;
        
        
        $minutes = $seconds / 60;
        
        $hours   = $minutes /60;
        
        $days = $hours /24;
        
        $seconds = floor($seconds);
        $minutes = floor($minutes);
        $hours = floor($hours);
        $days = floor($days);
        if($seconds < 60 ){
            if($seconds == 1)
                $type = ' Second ';
            else
                $type = ' Seconds ';
            return $seconds . $type . "  ago;";
        }else if($minutes < 60 ){
            if($minutes == 1)
                $type = ' Minute ';
            else
                $type = ' Minutes ';
            
            return $minutes . $type .  " ago";
        }else if($hours < 24){
             if($hours == 1)
                $type = ' Hour ';
            else
                $type = ' Hour ';
            return $hours .  $type . " ago";
        }else{
            if($days == 1)
                $type = ' Day ';
            else
                $type = ' Days ';
            return $days . $type . "  ago ";
        
            
        }
        
    }
}