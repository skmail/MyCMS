<?php

class App_Users_Shared_Libraries_Api
{


    protected $_errors;
    protected $_userPageId;

    public function __construct()
    {
        $this->db = Zend_Registry::get('db');
    }
    public function saveUserPage(array $page = array())
    {

        $errors = array();

        if(count($errors) == 0)
        {
            if(intval($page['page']['user_page_id']) == 0)
            {
                #-------------------------------
                #  Add new user page
                #-------------------------------

                unset($page['page']['user_page_id']);

                $this->db->insert('users_pages',$page['page']);
                $userPageId = $this->db->lastInsertId();

                foreach($page['user_page_lang'] as $langId => $userPageLang)
                {
                    $userPageLang['lang_id'] = $langId;
                    $userPageLang['user_page_id'] = $userPageId;
                    $this->db->insert('users_pages_lang',$userPageLang);
                }
            }
            else
            {
                #-------------------------------
                #  Update exists user page
                #-------------------------------

                $userPageId = $page['page']['user_page_id'];
                unset($page['page']['user_page_id']);

                $where = $this->db->quoteInto('user_page_id = ? ', $userPageId);
                $this->db->update('users_pages',$page['page'],$where);


                foreach($page['user_page_lang'] as $langId => $userPageLang)
                {
                    $where = $this->db->quoteInto('user_page_id = ? AND ', $userPageId);
                    $where.= $this->db->quoteInto('lang_id = ?  ', $langId);
                    $this->db->update('users_pages_lang',$userPageLang,$where);
                }
            }
            $this->_userPageId = $userPageId;
            return array('success'=>true);
        }
        else
        {
            return array('success'=>false,'errors',$errors);
        }
    }

    public function isValidSaveUserPage(array $results)
    {
        if(isset($results['errors']))
        {
            $this->_errors = $results['errors'];
            return false;
        }
        else
        {
            return true;
        }
    }


    public function getErrors()
    {
        return $this->_errors;
    }

    public function userPageId()
    {
        return $this->_userPageId;
    }
}