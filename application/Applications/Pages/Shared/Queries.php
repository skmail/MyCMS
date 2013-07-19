<?php

class App_Pages_Shared_Queries
{

    public function __construct()
    {
        $this->db = Zend_Registry::get('db');
    }

    public  function pageQuery( array $page = array())
    {
        $pageQuery = $this->db->select()->from('pages')
            ->join('pages_lang', 'pages_lang.page_id = pages.page_id');
        if (isset($page['page_id']))
        {
            $pageQuery->where('pages.page_id = ? ', $page['page_id']);
        }

        if(isset($page['lang_id']))
        {
            $pageQuery->where('pages_lang.lang_id = ? ',$page['lang_id']);
        }

        if(isset($page['page_url']))
        {
            $pageQuery->where('pages.page_url = ? ',$page['page_url']);
        }

        $result = array();

        if(isset($page['page_id']) && !isset($page['lang_id']))
        {
            $initialResult = $this->db->fetchAll($pageQuery);
            if(!$initialResult)
            {
                return false;
            }

            $result['page']['page_id'] = $initialResult[0]['page_id'];
            $result['page']['page_url'] = $initialResult[0]['page_url'];
            $result['page']['page_status'] = $initialResult[0]['page_status'];

            $result['page']['settings'] = Zend_Json::decode($initialResult[0]['settings']);

            foreach($initialResult as $page_lang)
            {
                $result['page_lang'][$page_lang['lang_id']]['lang_id'] = $page_lang['lang_id'];
                $result['page_lang'][$page_lang['lang_id']]['page_name'] = $page_lang['page_name'];
                $result['page_lang'][$page_lang['lang_id']]['page_content'] = $page_lang['page_content'];
            }
        }else
        if((isset($page['page_id']) || isset($page['page_url'])) && isset($page['lang_id']))
        {
            $result = $this->db->fetchRow($pageQuery);
            if(!$result)
            {
                return false;
            }
            $result['settings'] = Zend_Json::decode($result['settings']);
        }
        else
        {
            $result = $this->db->fetchAll($pageQuery);
            if(!$result)
            {
                return false;
            }

            $result['settings'] = Zend_Json::decode($result['settings']);
        }
        return $result;
    }

}