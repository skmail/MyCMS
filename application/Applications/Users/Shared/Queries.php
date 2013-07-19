<?php

class App_Users_Shared_Queries
{
    protected $db;

    public function __construct()
    {
        $this->db = Zend_Registry::get('db');
    }

    public function page(array $page = array())
    {
        $pageQuery = $this->db->select()->from('users_pages');

        $pageQuery->join('users_pages_lang','users_pages.user_page_id = users_pages_lang.user_page_id');

        if(isset($page['lang_id']))
        {
            $pageQuery->where('lang_id = ?',$page['lang_id']);
        }

        if(isset($page['user_page_id']))
        {
            $pageQuery->where('users_pages_lang.user_page_id = ?',$page['user_page_id']);
        }

        if(isset($page['lang_id']) && isset($page['user_page_id']))
        {

            $results = $this->db->fetchRow($pageQuery);
        }
        else if(!isset($page['lang_id']) && isset($page['user_page_id']))
        {

            $pageRows = $this->db->fetchAll($pageQuery);

            $results = $pageRows[0];

            foreach($pageRows as $k=>$v)
            {
                $results['users_pages_lang'][$v['lang_id']] = $v;
            }
        }else if(isset($page['lang_id']))
        {

            $results = $this->db->fetchAll($pageQuery);
        }
        return $results;
    }

    protected function  savePage($page)
    {


    }
}