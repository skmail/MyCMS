<?php

interface  MC_Models_Permissions_Interface {

    function getTables();
  
    function addEntity($table,$primaryKey,$label,array $data);

    
}