<?php
class MC_Date extends Zend_Date
{

    public function mcDate($timestamp,$format = '')
    {
        if($format == ''){
            return time();
        }
    }

}