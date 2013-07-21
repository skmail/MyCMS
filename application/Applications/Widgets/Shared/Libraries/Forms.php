<?php

class App_Widgets_Shared_Libraries_Forms{
    
    public function __construct($application = array())
    {
        $this->MC =& MC_Core_Instance::getInstance();
    }

    public function gridForm($rows = NULL)
    {

        if(isset($rows['grid_params']))
        {
            $rows['params'] = $rows['grid_params'];
        }
        $gridForm = new App_Widgets_Admin_Forms_Grid(array(
                                                        'action' =>
                                                            $this->MC->application['url'] . 'window/saveGrid','id'=>'saveGrid',
                                                        'data'=>$rows
                                                           )
                                                    );
        if ($rows != NULL)
        {
            $gridForm->populate($rows);
        }

        return $gridForm;
    }
    
    public function groupForm($row = NULL)
    {
        $form = new App_Widgets_Admin_Forms_Group(array(
                                                        'action' => $this->MC->application['url'] . 'window/saveGroup',
                                                        'data'=>$row
                                                       )
                                                 );
        if ($row != NULL)
        {
            $form->populate($row);
        }
        return $form;
    }

    public function widgetForm($pluginResource)
    {
        $pluginForm = $pluginResource['plugin_resource_name'];

        $subForm = 'Plugins_' . ucfirst($pluginForm) . '_Form' ;

        $applicationParams = array_merge($this->MC->application,$pluginResource);


        $attr = array('app' => $applicationParams);

        $subForm = new $subForm($attr);

        $form =
                new App_Widgets_Admin_Forms_Widget(
                        array('action' => $this->MC->application['url'] . 'window/saveWidget', 'app'    => $applicationParams));

        $form->addSubForm($subForm, 'params', 4);

        $form->populate($pluginResource);

        return $form;

    }
}