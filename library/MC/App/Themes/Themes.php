<?php

class MC_App_Themes_Themes
{

    public function currentTheme()
    {
        $db = Zend_Registry::get('db');

        $themeQuery = $db->select()->from('themes');

        $themeQuery->where('theme_default = 1');

        $themeRow = $db->fetchRow($themeQuery);

        return $themeRow['theme_id'];
    }

    public function themes()
    {

        $db = Zend_Registry::get('db');

        $themesQuery = $db->select()->from('themes');

        return $db->fetchAll($themesQuery);

    }

    public function themeQuery($theme_id = 0)
    {


        if ($theme_id == 0)
            $theme_id = intval(Zend_Controller_Front::getInstance()->getRequest()->getParam('theme'));

        $db = Zend_Registry::get('db');

        $themeQuery = $db->select()->from('themes');

        if ($theme_id != 0)
        {
            $themeQuery->where("theme_id = ? ", $theme_id);
        }
        else
        {
            $themeQuery->where(" theme_default = ? ", 1);
        }
        $themeRow = $db->fetchRow($themeQuery);
        if (!$themeRow)
        {
            $themeQuery = $db->select()->from('themes');
            $themeQuery->where(" theme_default = ? ", 1);
            $themeRow = $db->fetchRow($themeQuery);
        }
        return $themeRow;

    }

}