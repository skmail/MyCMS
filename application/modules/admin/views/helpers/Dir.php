<?php

class Admin_View_Helper_Dir {

    public function dir() {
        
        return MC_Core_Loader::appClass('Language','Lang',NULL,'Shared')->currentLang('dir');
        
    }

}