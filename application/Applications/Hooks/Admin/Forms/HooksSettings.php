<?php

class App_Hooks_Admin_Forms_HooksSettings extends MC_Admin_Form_BaseForm
{

    public function init($options = array())
    {
        $hooksMethods = $this->getAttrib('hooksMethods');

        $this->removeAttrib('hooksMethods');

        $hookMethodsForm = new MC_Admin_Form_SubForm();

        foreach($hooksMethods as $method=>$forms)
        {

            $hookEventForm = new MC_Admin_Form_SubForm();

            foreach($forms as $event=>$args)
            {

                $methodForm = new MC_Admin_Form_SubForm();

                if(isset($args['form']))
                {
                    $methodForm->addSubForm($args['form'],'settings');
                }

                $methodForm->addElement('text','event',array('order'=>0,'label'=>'event','disabled'=>true,'value'=>$event,'class'=>'mid-input'));
                $methodForm->addElement('checkbox','status',array('order'=>1,'label'=>'active'));
                $methodForm->addPrefixPath('MC_Admin_Form', 'MC/Admin/Form');
                $methodForm->setDecorators(array('FormElements',
                    array('Inline_Wrapper', array('title'    => $method , 'elements' => $methodForm->getElements()))
                ));
                $methodForm->populate($args);

                $hookEventForm->addSubForm($methodForm,$event);
            }



            $hookMethodsForm->addSubForm($hookEventForm,$method);
        }

        $this->addSubForm($hookMethodsForm,'method');

        $this->addElement('hidden','hook_name');
        $this->addElement('submit','do');
    }
}
