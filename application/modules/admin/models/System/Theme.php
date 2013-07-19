<?php

class Admin_Model_System_Theme
{

    public function setTheme($theme)
    {

        setcookie('adminTheme', $theme, time() * 3600);

    }

    public function currentTheme()
    {

        return $_COOKIE['adminTheme'];

    }

}