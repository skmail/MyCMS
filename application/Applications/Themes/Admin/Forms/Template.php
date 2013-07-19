<?php

class App_Themes_Admin_Forms_Template extends MC_Admin_Form_BaseForm
{

    public function init($options = array())
    {


        $child_templates = $this->getAttrib('child_templates');
        $this->removeAttrib('child_templates');


        $this->setMethod('post');

        $this->addElement('text', 'template_name', array(
            'required'  => true,
            'label'     => 'Template Name',
            'maxLength' => '255',
            'isArray'   => false
        ));

        $this->addElement('textarea', 'template_content', array(
            'label'   => 'Template Name',
            'class'   => 'editor_textarea ltr',
            'isArray' => false
        ));



        
        if (is_array($child_templates))
        {
            
            $c = 1;
        
            foreach ($child_templates as $child)
            {

                $childTemplate = new App_Themes_Admin_Forms_ChildTemplate(array('c' => $c));
                $childTemplate->setElementsBelongTo('child_templates[' . $child['template_id'] . ']');
                $this->addSubForm($childTemplate, 'child_templates[' . $child['template_id'] . ']');
                $c++;
            }
        }


        $childTemplate = new App_Themes_Admin_Forms_ChildTemplate(array('c' => 'new'));
        $childTemplate->setElementsBelongTo('child_templates[new]');
        $this->addSubForm($childTemplate, 'child_templates[new]');



        $this->addElement('hidden', 'template_id');
        $this->addElement('hidden', 'cat_id');

        $this->addElement('hidden', 'do');
        $this->addElement('submit', 'go', array('label' => 'Edit', 'class' => 'submit'));

    }

}

