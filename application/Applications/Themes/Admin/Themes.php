<?php

class App_Themes_Admin_Themes extends Admin_Model_ApplicationAbstract
{


    public function __construct($application = array())
    {

        $this->application = $application;
        
        parent::__construct($application);

        $this->Functions = new App_Themes_Admin_Functions($application);
        
        $this->_query = new App_Themes_Admin_Queries();

        $this->setNav($this->translate('Themes'));

        $this->assign('menubar',$this->menu);
    }

    public function index()
    {
        $this->assign('themes',$this->db->fetchAll($this->themesQuery()));
        return $this->application;
    }

    public function theme()
    {

        $themeId = $this->_Zend->getRequest()->getParam('themeid');

        $themeId = intval($themeId);

        if ($themeId == 0)
        {
            return $this->setError();
        }

        $theme = $this->db->fetchRow($this->themesQuery($themeId));

        if (!$theme)
        {
            return $this->setError();
        }

        $catsInfoArray = $this->db->fetchAll($this->categoryQuery(0, $themeId));

        $templates = array();

        foreach ($catsInfoArray as $cat)
        {
            $template_query = $this->db->fetchAll($this->templateQuery(0, $cat['cat_id']));

            if ($template_query)
            {
                $templates[$cat['cat_id']] = $template_query;
            }
        }
        

        $this->assign('templates',$templates);
        $this->setNav($theme['theme_name']);
        $this->assign('theme',$theme);
        $this->assign('cats',$catsInfoArray);
        $this->setSidebar('themeSidebar');

        return $this->application;
    }

    public function category($options = array())
    {

        $do = (isset($options['do']))?$options['do']:$this->_Zend->getRequest()->getParam('do');

        if ($do != 'edit' && $do != 'add' && $do != "delete")
        {
            return $this->setError();
        }

        if ($do == 'add')
        {

            $themeId = isset($options['themeid'])?$options['themeid']:$this->_Zend->getRequest()->getParam('themeid');
            $themeId = intval($themeId);

            if ($themeId == 0)
            {
                return $this->setError();
            }

            $query = $this->db->fetchRow($this->themesQuery($themeId));

            if (!$query)
            {
                return $this->setError();
            }

            $this->setNav($query['theme_name'],'window/theme/themeid/'.$query['theme_id']);

            $this->setNav($this->translate('add_new_templates_category'));
            
            $query['do'] = 'add';
        }

        if ($do == 'edit' || $do == 'delete')
        {

            $cat_id = ($options['cat_id'])?$options['cat_id']:$this->_Zend->getRequest()->getParam('cat_id');

            $cat_id = intval($cat_id);

            if ($cat_id == 0)
            {
                return $this->setError();
            }

            $query = $this->db->fetchRow($this->categoryQuery($cat_id));

            if (!$query)
            {
                return $this->setError();

            }
            $this->setNav($query['theme_name'],'window/theme/themeid/'.$query['theme_id']);

            $this->setNav( $this->translate('edit') . ': ' . $query['cat_name']);

            $query['do'] = 'edit';
        }

        if ($do == 'delete')
        {

            $deleteWhere = $this->db->quoteInto('cat_id = ? ', $cat_id);

            $this->db->delete('templates_categories', $deleteWhere);

            $this->application['js'] = '$("#template_cat_' . $cat_id . '").remove();$(".template_cat_' . $cat_id . '").remove();';

            $deleteWhere = $this->db->quoteInto('cat_id = ? ', $cat_id);

            $this->db->delete('templates_categories', $deleteWhere);

            $this->application['js'] = '$("#template_cat_' . $cat_id . '").remove();$(".template_cat_' . $cat_id . '").remove();';

            $this->application['renderWindow'] = false;

            return $this->application;
        }

        $this->setSidebar('categorySidebar');

        $this->assign('do',$do);

        $this->assign('catForm',$this->categoryForm($query));

        return $this->application;
    }

    public function template($options = array())
    {

        $do = (isset($options['do']))?$options['do']:$this->_Zend->getRequest()->getParam('do');

        if ($do != 'edit' && $do != 'add')
        {
            return;
        }

        if ($do == 'add')
        {

            $cat_id = (isset($options['cat_id']))?$options['cat_id']:$this->_Zend->getRequest()->getParam('cat_id');
            
            $cat_id = intval($cat_id);

            if ($cat_id == 0)
            {
                return;
            }

            $query = $this->db->fetchRow($this->categoryQuery($cat_id));

            if (!$query)
            {
                return;
            }

            $this->nav($query['theme_name'],'window/theme/themeid/'.$query['theme_id']);
            $this->nav($query['cat_name'],'window/theme/themeid/'.$query['theme_id']);
            $this->nav($this->translate('add_new_template'));
            
            $query['do'] = 'add';
        }

        if ($do == 'edit')
        {

            $templateId = (isset($options['templateid']))?$options['templateid']:$this->_Zend->getRequest()->getParam('templateid');
            
            $templateId = intval($templateId);

            if ($templateId == 0)
            {
                return;
            }

            $query = $this->db->fetchRow($this->templateQuery($templateId));

            if (!$query)
            {
                return;
            }
            
            $themeQuery = $this->db->select()->from('themes')->where('theme_id = ? ', $query['theme_id']);
            $theme = $this->db->fetchRow($themeQuery);
            
            $this->setNav($query['theme_name'],'window/theme/themeid/'.$query['theme_id']);
            $this->setNav($query['cat_name'],'window/theme/themeid/'.$query['theme_id']);
            $this->setNav($query['template_name']);

            $childs_templates = $this->db->fetchAll($this->templateQuery(0, 0, $templateId));

            $childs = array();

            foreach ($childs_templates as $childTemplate)
            {
                $childs[$childTemplate['template_id']] = $childTemplate;
            }

            $query['child_templates'] = $childs;
            $query['do'] = 'edit';
        }

        $this->setSidebar('templateSidebar');

        $this->assign('templateForm',$this->templateForm($query));

        return $this->application;
    }

    public function saveCategory()
    {

        $request = $this->_Zend->getRequest();
        $data = $request->getPost();

        if ($data['do'] == 'add')
        {

            $query = $this->db->fetchRow($this->themesQuery($data['theme_id']));

            if (!$query)
            {
                return $this->setError();
            }
        }

        if ($data['do'] == 'edit')
        {

            $cat_id = $data['cat_id'];

            $cat_id = intval($cat_id);

            if ($cat_id == 0)
            {
                return $this->setError();
            }

            $query = $this->db->fetchRow($this->categoryQuery($cat_id));

            if (!$query)
            {
                return $this->setError();
            }
        }

        $categoryForm = $this->categoryForm($query);

        $categoryArray = array();

        if ($categoryForm->isValid($request->getPost()))
        {

            $category['cat_name'] = $data['cat_name'];
            $categoryArray['do']  = $data['do'];
            if ($data['do'] == 'add')
            {

                $category['theme_id'] = $data['theme_id'];

                $this->db->insert('templates_categories', $category);

                $this->setMessage($this->translate('category_added_successfuly'),'success');
                $categoryArray['do'] = 'edit';
                $categoryArray['cat_id'] = $this->db->lastInsertId();
            }

            if ($data['do'] == 'edit')
            {

                $where = $this->db->quoteInto('cat_id = ?', $data['cat_id']);

                $this->db->update('templates_categories', $category, $where);

                $this->setMessage($this->translate('categoyr_save_successfuly'),'success');
                $categoryArray['cat_id'] = $data['cat_id'];
                $this->replaceUrl($this->Functions->categoryUrl($data['cat_id']));
            }
        }
        else
        {
            if($data['do'] == 'add')
            {
                $categoryArray['themeid'] = $data['theme_id'];
            }else{
                $categoryArray['cat_id'] = $data['cat_id'];
            }

            $this->setMessage($this->translate('fields_empty'),'error');
        }

        $this->application = array_merge($this->application,$this->category($categoryArray));
        $this->setView('category');

        return $this->application;
    }

    public function saveTemplate()
    {

        $request = $this->_Zend->getRequest();

        $data = $request->getPost();

        if($data['do'] != "add" && $data['do'] != 'edit')
        {
            return $this->setError();
        }
        
        if ($data['do'] == 'add')
        {
            $query = $this->db->fetchRow($this->categoryQuery($data['cat_id']));
            if (!$query)
            {
                return $this->setError();
            }
        }

        if ($data['do'] == 'edit')
        {
         
            $query = $this->db->fetchRow($this->templateQuery($templateId));
            
            if (!$query)
            {
                return $this->setError();
            }
        }

        $this->assign('templateForm',$this->templateForm($request->getPost()));

        if ($this->application['templateForm']->isValid($request->getPost()))
        {
            
            $template['template_name'] = $data['template_name'];

            $template['template_content'] = $data['template_content'];

            $template['cat_id'] = intval($data['cat_id']);

            $template_id = $this->_query->saveTemplate($data['template_id'], $template);

            $childTemplates = $data['child_templates'];
            
            foreach ($childTemplates as $childTemplateId=>$childTemplate)
            {   
                if (trim($childTemplate['template_name']) == '')
                {
                    continue;
                }

                $childTemplate['parent_template'] = $template_id;

                $this->_query->saveTemplate($childTemplateId, $childTemplate);
            }

            if ($data['do'] == 'add')
            {
                $this->setMessage('template_added','success');
            }
            else
            {
                $this->setMessage('template_saved','success');
            }
            $this->merge($this->template(array('templateid'=>$template_id,'do'=>'edit')));
        }
        else
        {
            $this->setMessage('fields_empty','error');
        }

        $this->setView('template');

        return $this->application;

    }

    private function themesQuery($themeId = 0)
    {

        $themesQuery = $this->db->select()->from('themes');

        if ($themeId != 0)
        {
            $themesQuery->where('theme_id = ? ', $themeId);
        }

        return $themesQuery;

    }

    private function categoryQuery($cat_id = 0, $themeId = 0)
    {

        $catQuery = $this->db->select()->from('templates_categories');

        $catQuery->join('themes','themes.theme_id  = templates_categories.theme_id');

        if ($cat_id != 0)
        {
            $catQuery->where('cat_id = ?', $cat_id);
        }

        if ($themeId != 0)
        {
            $catQuery->where('templates_categories.theme_id = ?', $themeId);
        }

        return $catQuery;

    }

    private function templateQuery($template_id = 0, $cat_id = 0, $parent_template = 0)
    {
        $templateQuery = $this->db->select()->from('templates');

        if($parent_template == 0)
        {
            $templateQuery->join('templates_categories','templates.cat_id = templates_categories.cat_id');
            $templateQuery->join('themes','themes.theme_id = templates_categories.theme_id');
        }
        
        if ($template_id != 0)
        {
            $templateQuery->where('template_id = ?', $template_id);
        }

        if ($parent_template != 0)
        {
            $templateQuery->where('parent_template = ?', $parent_template);
        }

        if ($cat_id != 0)
        {
            $templateQuery->where('templates.cat_id = ?', $cat_id);
        }

        return $templateQuery;
    }

    private function categoryForm($query)
    {
        $categoryForm = new App_Themes_Admin_Forms_Category(array('action' => $this->application['url'] . 'window/saveCategory'));

        $categoryForm->populate($query);

        return $categoryForm;
    }

    private function templateForm($query = array())
    {

        $templateForm = new App_Themes_Admin_Forms_Template(array(
                                                                'action'=> $this->application['url'] . 'window/saveTemplate',
                                                                'child_templates' => $query['child_templates']
                                                                 )
                                                            );

        $templateForm->populate($query);
        return $templateForm;
    }
}


