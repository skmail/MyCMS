<?php


class MC_Models_Array {

    
    
    /*
     * Filter array by custom filter
     */
    
    
    public function filter($array,$filter = array()){
        $newArray = array();
        
        if(count($array) == 0)
            return false;
        
    }
    
    
    protected function _isNumeric($array = array()){
       
    }

    public static  function push($array,$element,$offset = NULL)
    {


        if(($offset == null && $offset !== 0) || $offset > count($array) )
        {
            array_push($array,$element);
            return $array;
        }

        $offset = intval($offset);

        $finalArray = array();


        foreach($array as $key=>$val)
        {
            if($key == $offset - 1 || ($key == 0 && $offset == 0))
            {
                array_push($finalArray,$element);
            }

            array_push($finalArray,$val);
        }


        return $finalArray;

    }
}