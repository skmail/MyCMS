<?php

class MC_Core_Template {


    protected $start_delm = "{{";
    protected $end_delm   = "}}";
    protected $tags_prefix = 'mycms';
    protected $classes_pointer = ':';

    public function __construct()
    {
        $this->CC =& MC_Core_Instance::getInstance();
    }

    public function fetchTemplate($categoryName = '',$templateName = '',$isSiteIndex = false,$isCategoryIndex = false)
    {
        if((empty($categoryName) && empty($templateName)) && !$isSiteIndex)
        {
            return false;
        }
        $query = $this->CC->db->select()->from('templates');
        $query->join('templates_categories','templates_categories.cat_id = templates.cat_id');
        if($categoryName != "")
        {
            $query->where('templates_categories.cat_name = ?' ,$categoryName);

            if($templateName != "")
            {
                $query->where('templates.template_name = ?',$templateName);
            }
            if($isCategoryIndex)
            {
                $query->where('templates.isCategoryIndex = ?' , 1);
            }
        }
        else if($isSiteIndex)
        {
            $query->where('templates.isSiteIndex = ?' , 1);
        }

        $result = $this->CC->db->fetchRow($query);

        if($result)
        {
            return $result['template_content'];
        }

        return false;
    }



    public function parse($templateContent)
    {

        $templateContent = $this->_parsePairTags($templateContent);

        echo $templateContent;

        $pattern = '|\{{(.+?)\}}|s';

        preg_match_all($pattern,$templateContent,$singleTags);


        if(is_array($singleTags))
        {
            $singleTags = reset($singleTags);
        }
    }

    protected function _parsePairTags($templateContent)
    {

        $pattern = '|\\'.$this->start_delm.$this->tags_prefix.'(.+?)\\'.$this->end_delm.'(.+?)\\'.$this->start_delm.'/\\'.$this->tags_prefix.$this->classes_pointer.'(.+?)\\'.$this->end_delm.'|s ';

        preg_match_all($pattern,$templateContent,$pairTags);

        if(is_array($pairTags))
        {
            $tags  = reset($pairTags);
        }

        if(is_array($tags))
        {
            foreach($tags as $tag)
            {

                $pattern = "~".$this->start_delm.$this->tags_prefix.$this->classes_pointer."(.+?) ~s";

                preg_match($pattern,$tag,$match);

                print_r($match);

                if(!isset($match[1]))
                {
                    continue;
                }

                $match = explode($this->classes_pointer,$match[1]);

                if(strtolower($match[0]) == 'app')
                {
                    $class = 'App_'.ucfirst($match[1]).'_Frontend_T'.ucfirst($match[1]);
                }

                $object = new $class();

                if(isset($match[2]))
                {
                    $output = $object->$match[2]();
                }
                else
                {
                    $output = $object;
                }
                $templateContent = str_replace($tag,$output,$templateContent);
            }
        }

        return $templateContent;

    }
}