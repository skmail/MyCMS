<?php

class MC_Admin_Form_Decorator_Inline_Element extends Zend_Form_Decorator_Abstract
{

    public function render($content)
    {

        $element = $this->getElement();


        $classes = explode(' ', $element->getAttrib('class'));


        $label = "<label class='form-label form-label-intable'>" . $element->getLabel() . "</label>";



        if (in_array('hidden', $classes))
        {
            $class = 'form-inline-elements-element-hidden';
        }

        return '<div class="form-inline-elements-element ' . $class . '">' . $label . $content . '</div>';

    }

}

