<?php

class MC_Models_String
{

    public function cut($string, $length, $type, $complete = '', $stripTags = true)
    {
        if ($stripTags)
        {
            $string = strip_tags($string);
        }
        
        switch ($type)
        {
            case 'w':
            default:
                
                $words = explode(' ', $string);
                
                if (count($words) > $length)
                {
                    $string = implode(' ', array_slice($words, 0, $length));
                    $string.= $complete;
                }
                
                break;

            case 'c':
                
                if (mb_strlen($string) > $length)
                {
                    $string = mb_substr($string, 1, $length, 'utf-8') . $complete;
                }
                
                break;
        }
        return $string;

    }

}