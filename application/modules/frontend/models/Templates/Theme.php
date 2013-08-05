<?php

class Frontend_Model_Templates_Theme extends Frontend_Model_Frontend
{
    private $layoutModel = NULL;
    public $themeFolder;

    public function __construct()
    {
        parent::__construct();

    }

    public function currentTheme($column = NULL)
    {
        $themeRow = MC_App_Themes_Themes::themeQuery();
        if ($column == NULL){
            return $themeRow;
        }else{
            return $themeRow[$column];
        }
    }

    private function themeQuery($themeId = 0)
    {
        $themeId = intval($themeId);
        $themeQuery = $this->db->select()->from('themes');
        if ($themeId != 0){
            $themeQuery->where("theme_id = ? ", $themeId);
        }else{
            $themeQuery->where(" theme_default = ? ", 1);
        }
        return $themeQuery;
    }

    public function theme($themeFolder = '')
    {
        if ($this->layoutModel == NULL){
            $this->layoutModel = new Zend_Layout();
        }
        return $this->layoutModel;
    }
}