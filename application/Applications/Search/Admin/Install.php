<?php

class App_Items_Admin_Install extends App_AppsManager_Admin_Abstract_Install
{

    public function __construct()
    {
        $this->config = MC_Core_Loader::appClass('Items', 'Config', NULL, 'shared');

        parent::__construct();

    }

    public function install()
    {
        parent::install();

        $this->upgrade100();

    }

    public function upgrade()
    {
        if ($this->version >= $this->current_version())
        {
            return false;
        }

        parent::upgrade();

        if ($this->version > 0)
        {
            $this->upgrade100();
        }

    }

    public function upgrade100()
    {

        $db = Zend_Registry::get('db');

        $db->getConnection()->exec(file_get_contents(rtrim(APPLICATION_PATH, '/') . "/Applications/Items/Admin/upgrade100.sql"));

    }

    public function uninstall()
    {
        $dbTables = array();

        $dbTables[] = 'items';
        $dbTables[] = 'items_categories';
        $dbTables[] = 'items_categories_lang';
        $dbTables[] = 'items_custom_fields';
        $dbTables[] = 'items_custom_fields_lang';
        $dbTables[] = 'items_fields';
        $dbTables[] = 'items_fields_lang';
        $dbTables[] = 'items_lang';

        $db = Zend_Registry::get('db');


        foreach ($dbTables as $table)
        {
            $db->getConnection()->exec("DROP TABLE IF EXISTS " . $table);
        }

        parent::uninstall();

    }

}