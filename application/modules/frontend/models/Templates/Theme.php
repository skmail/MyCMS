<?php

class Frontend_Model_Templates_Theme extends Frontend_Model_Frontend
{

    private $themeView = NULL;

    public $themeFolder;

    public function __construct()
    {
        parent::__construct();

    }

    public function currentTheme($column = NULL)
    {

        $themeRow = MC_App_Themes_Themes::themeQuery();

        if ($column == NULL)
        {
            return $themeRow;
        }
        else
        {
            return $themeRow[$column];
        }

    }

    public function loadLayout()
    {


        $this->themeFolder = $this->currentTheme('theme_folder');

        echo $this->theme()->render('layout.phtml');

    }

    private function themeQuery($themeId = 0)
    {
        $themeId = intval($themeId);

        $themeQuery = $this->db->select()->from('themes');

        if ($themeId != 0)
        {
            $themeQuery->where("theme_id = ? ", $themeId);
        }
        else
        {
            $themeQuery->where(" theme_default = ? ", 1);
        }

        return $themeQuery;

    }

    public function theme($folder = '')
    {


        if ($this->themeView == NULL)
        {
            $this->themeView = new Zend_View ();
        }

        $view = $this->themeView;



        if ($themeFolder == '')
        {
            $themeFolder = $this->themeFolder;
        }

        $view->addScriptPath('themes/' . $themeFolder);

        return $view;

    }

}