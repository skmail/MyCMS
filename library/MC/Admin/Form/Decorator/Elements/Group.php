<?php

class MC_Admin_Form_Decorator_Elements_Group extends Zend_Form_Decorator_Abstract
{

    protected $hideButton = '';

    public function render($content)
    {
        $title = $this->getOption('title');

        $isHidden = $this->getOption('isHidden');

        $help = $this->getOption('help');

        if ($isHidden === true)
        {
            $hideButton = '<small>(<a href="#" class="disState showHiddenElements">'.Zend_Registry::get('Zend_Translate')->translate('More settings').'</a>)</small>';
        }

        $output = "<div class='form-element form-inline-elements'>";


        if ($help != "")
        {
            $help = "<span class='info_box'><a href='#' class='info_box_link'></a><p class='info_box_content'><span class='info_box_content_arrow'></span>" . $help . "</p></span>";
        }

        if ($title != "")
        {
            $output .= "<div class='form-inline-elements-title'>$help ".Zend_Registry::get('Zend_Translate')->translate($title)." $hideButton</div>";
        }

        $output.="<div class='form-inline-elements-content'>";
        $output .= $content;


        $output .= "<div class='clear'></div></div></div>";


        return $output;

    }

}
