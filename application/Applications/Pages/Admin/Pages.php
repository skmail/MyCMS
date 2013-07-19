<?php
class App_Pages_Admin_Pages extends Admin_Model_ApplicationAbstract
{
    public $renderWindow = true;

    public function __construct($application = NULL)
    {
        parent::__construct($application);
        $this->Functions =  MC_Core_Loader::appClass('Pages','Functions',$application,'Admin');
       // $this->_form =  MC_Core_Loader::appClass('Pages','Forms',$application,'Admin');

    }

    public function index()
    {
        $this->assign('pages',$this->pageQuery(array('lang_id'=>$this->application['lang_id'])));
        $this->setSidebar('indexSidebar');
        return $this->application;
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
        }

        if(isset($page['page_id']) && isset($page['lang_id']))
        {
            $result = $this->db->fetchRow($pageQuery);
            if(!$result)
            {
                return false;
            }
        }

        if(!isset($page['page_id']) && isset($page['lang_id']))
        {
            $result = $this->db->fetchAll($pageQuery);
            if(!$result)
            {
                return false;
            }
        }
        return $result;
    }

    public function page($options = array())
    {
        $do = (isset($options['do']))?$options['do']:$this->_Zend->getRequest()->getParam('do');
        $pageId = (isset($options['page_id']))?$options['page_id']: $this->_Zend->getRequest()->getParam('pageid');
        if ($do != 'add' && $do != "edit")
        {
            return $this->setError();
        }
        if ($do == 'edit')
        {
            $pageRow = $this->pageQuery(array('page_id'=>$pageId));

            if (!$pageRow)
            {
                return $this->setError();
            }

            $pageRow['do'] = 'edit';
        }
        if ($do == 'add')
        {
            $pageRow['do'] = 'add';
        }
        $this->assign('pageForm',(isset($options['pageForm']))?$options['pageForm']:$this->pageForm($pageRow));
        return $this->application;
    }

    public function savePage()
    {
        $request = $this->_Zend->getRequest();
        $data = $request->getPost();
        $do = $data['do'];

        if ($request->getParam('do') == 'delete')
        {
            $pageId = $request->getParam('pageid');
            $whereDelete = $this->db->quoteInto('page_id = ?', $pageId);
            $this->db->delete('pages', $whereDelete);
            $this->db->delete('pages_lang', $whereDelete);
            $this->setView('index');
            $this->merge($this->index());
            return $this->application;
        }

        if ($do != 'edit' && $do != 'add' && $do != 'delete')
        {
            return $this->setError();
        }
        if ($do == 'edit' || $do == 'delete')
        {
            $pageId = $data['page']['page_id'];

            $pageRow = $this->pageQuery(array('page_id'=>$pageId));
            if (!$pageRow)
            {
                return $this->setError();
            }

        }

        $pageForm = $this->pageForm();

        if ($pageForm->isValid($data))
        {

            $page = $data['page'];
            unset($page['page_id']);

            $pageLang = $data['page_lang'];

            $page['settings']  = Zend_Json::encode($page['settings']);
            if ($do == 'add')
            {
                $this->db->insert('pages', $page);
                $page_id = $this->db->lastInsertId();

                foreach($pageLang as $lang_id=>$page_lang)
                {
                    if(empty($page_lang['page_name']))
                    {
                        continue;
                    }
                    $page_lang['lang_id'] = $lang_id;
                    $page_lang['page_id'] = $page_id;
                    $this->db->insert('pages_lang', $page_lang);
                }
                $this->setMessage($this->translate('page_added_success'),'success');
            }

            if ($do == 'edit')
            {
                $page_id = $data['page']['page_id'];
                $where = $this->db->quoteInto('page_id = ?', $page_id);
                $this->db->update('pages', $page, $where);


                foreach($pageLang as $langId=>$page_lang)
                {
                    if($this->pageQuery(array('page_id'=>$page_id,'lang_id'=>$langId)) !== FALSE)
                    {
                        $where = $this->db->quoteInto('page_id = ?',$page_id);
                        $whereLang = $where . $this->db->quoteInto(' AND lang_id = ?', $langId);

                        $this->db->update('pages_lang', $page_lang, $whereLang);
                    }
                    else
                    {
                        $page_lang['page_id'] = $page_id;
                        $page_lang['lang_id'] = $langId;
                        $this->db->insert('pages_lang', $page_lang);
                    }
                }

                $this->setMessage('page_save_success','success');
            }

            $pageOptions['do'] = 'edit';
            $pageOptions['page_id'] = $page_id;
        }
        else
        {
            $pageOptions['pageForm'] = $pageForm;
            $this->setMessage('some_fields_empty','error');
        }


        $this->merge($this->page($pageOptions));
        $this->setView('page');
        return $this->application;
    }

    private function pageForm($pageRow = NULL)
    {
        $pageForm = new App_Pages_Admin_Forms_Page(array('action' => $this->application['url'] . 'window/savePage'));
        if ($pageRow != NULL)
        {
            $pageForm->populate($pageRow);
        }
        return $pageForm;
    }
}