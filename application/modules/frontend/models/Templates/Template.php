<?php

class Frontend_Model_Templates_Template extends Frontend_Model_Frontend
{

    protected $_data = array();

    protected $_cachedTemplates = array();
    protected $_cachedChildsTemplates = array();


    protected $_childs = array();

    public function __construct()
    {
        parent::__construct();

    }

    public function prepareData(array $data)
    {
        $this->_data = $data;

        return $this;

    }

    protected function _getData()
    {

        return $this->_data;

    }

    protected function _release()
    {

        unset($this->_childs);
        unset($this->_data);

    }

    protected function getTemplate($template_id = 0,$parent_id = 0)
    {
        
        
        if (!isset($this->_cachedTemplates[$template_id]))
        {
            $this->_cachedTemplates[$template_id] = $this->templateQuery($template_id);
        }
        
        if (!isset($this->_cachedChildsTemplates[$parent_id]))
        {
            $this->_cachedChildsTemplates[$parent_id] = $this->templateQuery(0,$parent_id);
        }
        
        if($template_id != 0)
        {
            return $this->_cachedTemplates[$template_id];
        }
        else
        {
            return $this->_cachedChildsTemplates[$parent_id];
        }
    }

    public function fetchTemplate($template_id)
    {   
        
        
        $data = $this->_getData();
        
       
        if (is_array($data))
        {
            extract($data);
        }

        if (is_array($this->_childs))
        {
            extract($this->_childs);
        }

        $template_id = intval($template_id);

        $templateRow = $this->getTemplate($template_id);

        $template = $templateRow['template_content'];

        eval("\$template =\"" . addslashes($template) . "\";");

        $this->parse($template);
        $this->parseVars($template,$data);

        $this->_template[$template_id] = $template;

        $this->_release();
        
        return $template;

    }

    public function addChildTemplate($parentId)
    {
        $data = $this->_getData();

        if (is_array($data))
        {
            extract($data);
        }

        $parentId = intval($parentId);
        
        $templateRows = $this->getTemplate(0, $parentId);

        if (!$templateRows)
        {
            return;
        }
        
        foreach ($templateRows as $row)
        {
            $this->parse($row['template_content']);
            $this->parseVars($row['template_content'],$data);
            eval("\$templateRow =\"" . addslashes($row['template_content']) . "\";");

            $this->_childs[$row['template_name']].= $templateRow;
        }

    }

    private function templateQuery($templateId = 0, $parentId = 0)
    {

        if ($templateId == 0 && $parentId == 0)
        {
            return;
        }


        $templateQuery = $this->db->select()->from('templates');


        if ($templateId != 0)
        {
            $templateQuery->where('template_id = ? ', $templateId);

            return $this->db->fetchRow($templateQuery);
        }

        if ($parentId != 0)
        {
            $templateQuery->where('parent_template  = ? ', $parentId);

            return $this->db->fetchAll($templateQuery);
        }

    }

    
    
    protected function translate(&$template)
    {
        
        $pattern = '~{lang:(.*?)}~s';
            
        preg_match_all($pattern, $template,$translations);

        if(is_array($translations[1]) && count($translations[1] > 0))
        {
        
            foreach($translations[1] as $key=>$translate)
            {
                if(!empty($translate))
                {
                    $template = str_replace('{lang:'.$translate.'}',Zend_Registry::get('Zend_Translate')->translate($translate),$template);
                }
            }
        }
    }

    protected function parse(&$template)
    {
        $this->translate($template);
        $this->parseDate($template);
    }

    protected function parseVars(&$template,$variables)
    {
        $pattern = '~{var:(.*?)}~s';
        preg_match_all($pattern, $template,$vars);

        $fullVarTag = reset($vars);
        $vars = end($vars);

        if(is_array($vars) && count($vars) > 0)
        {
            foreach($vars as $var)
            {
                $explodeVars = explode(':',$var);
                $value =  $this->_getVariableKey($variables,$explodeVars);
                $template = str_replace($fullVarTag,$value,$template);
            }
        }
        return $template;
    }

    protected function  _getVariableKey($data,$keys)
    {
        foreach($keys as $k=>$key)
        {
            if(!is_array($data[$key]))
            {
                return $data[$key];
            }
            else
            {
                unset($keys[$k]);
                return $this->_getVariableKey($data[$key],$keys);
            }
        }
    }
    protected function parseDate(&$template)
    {
        $template = str_replace('{year}',date('Y'),$template);
        $template = str_replace('{month}',date('m'),$template);
        $template = str_replace('{day}',date('d'),$template);
        $template = str_replace('{hour}',date('d'),$template);
        $template = str_replace('{minute}',date('i'),$template);
        $template = str_replace('{second}',date('s'),$template);
    }
}