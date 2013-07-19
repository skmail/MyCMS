<?php

class Admin_Model_System_Breadcrumb
{

    protected $_breadrumb = array();

    protected $_options;

    protected  $_container = "<div class='breadcrumb'><ul>%s</ul><div class='clear'></div></div>";

    public function __construct($options = array())
    {
        $this->_options = $options;
    }

    public function render()
    {
        return $this->_refactorBreadcrumb();

    }

    public function append($label = '', $link = '', $options = array())
    {
        $element = array();
        if (!is_array($options))
        {
            $options == array();
        }
        $this->_refactorElement($label, $link, $options);
    }

    private function _refactorElement($label, $link, $options)
    {

        if ($link == '' || $link == false || $link == '#')
        {
            $link = '#';
        }

        $element['label'] = $label;

        $element['link'] = $link;

        if (isset($options['order']))
        {
            $order = intval($options['order']);
            
            if ($order == 0)
            {
                $order = false;
            }
            
            unset($options['order']);
        }

        $element['options'] = $options;

        if ($order)
        {
            $this->_breadrumb[$order] = $element;
        }
        else
        {
            array_push($this->_breadrumb, $element);
        }

    }

    private function _refactorBreadcrumb()
    {

        $outputs = '';
        
        if (count($this->_breadrumb) == 0)
        {
            return ;
        }
      
        foreach ($this->_breadrumb as $counter => $el)
        {
            if (count($el['options']) > 0)
            {
                $options = '';
               
                foreach ($el['options'] as $opK => $opV)
                {
                    $options.= " " . $opK . "='" . $opV . "'";
                }
            }
            if ($counter == (count($this->_breadrumb) - 1))
            {
                // last element
                $outputs.="<li class='active' " . $options . ">" . $el['label'] . "</li>";
            }
            else
            {
                $outputs.="<li><a href='" . $this->_options['url'] . $el['link'] . "' " . $options . ">" . $el['label'] . "</a> <span class='divider'></span></li>";
            }
        }




        if($outputs != "")
        {
            $container = sprintf($this->_container, $outputs);
        }
        else
        {
            $container = '';
        }
        return $container;

    }

}