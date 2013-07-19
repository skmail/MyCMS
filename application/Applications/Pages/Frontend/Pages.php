<?php

class App_Pages_Frontend_Pages extends Frontend_Model_Applications_Application{


    protected $_query;
    public function init($appRow)
    {

        $this->_query = new App_Pages_Shared_Queries();

        $view = new Zend_View();

        $pageUrl = $this->_Zend->getRequest()->getParam('page');

        $pageQuery = $this->_query->pageQuery(array('page_url'=>$pageUrl,'lang_id'=>$this->lang_id));

        $template = new Frontend_Model_Templates_Template();

        $vars['page'] = $pageQuery;
        $content = $template->prepareData($vars)->fetchTemplate($pageQuery['settings']['page_template']);

        $this->appendPlugin($content,$pageQuery['settings']['plugin_group'],$pageQuery['settings']['plugin_group_order']);

        $view->getHelper('headTitle')->prepend($pageQuery['page_name']);

    }
}