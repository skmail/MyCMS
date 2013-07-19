<?php

class App_Plugins_Admin_Forms{
    
    public function __construct($application = array())
    {
        $this->application = $application;
    }

    public function gridForm($rows = NULL)
    {

        if(isset($rows['grid_params']))
        {
            $rows['params'] = $rows['grid_params'];
        }
        $gridForm = new App_Plugins_Admin_Forms_Grid(array(
                                                        'action' =>
                                                            $this->application['url'] . 'window/saveGrid','id'=>'saveGrid',
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
        $form = new App_Plugins_Admin_Forms_Group(array(
                                                        'action' => $this->application['url'] . 'window/saveGroup',
                                                        'data'=>$row
                                                       )
                                                 );
        if ($row != NULL)
        {
            $form->populate($row);
        }
        return $form;
    }

    public function pluginForm($pluginResource)
    {

        $pluginForm = $pluginResource['plugin_resource_name'];

        $subForm = 'Plugins_' . ucfirst($pluginForm) . '_Form' ;

        $applicationParams = array_merge($pluginResource, $this->application);

        $attr = array('app' => $applicationParams);

        $subForm = new $subForm($attr);

        $this->subForm = $subForm;

        $form =
                new App_Plugins_Admin_Forms_Plugin(
                        array('action' => $this->application['url'] . 'window/savePlugin', 'app'    => $applicationParams));

        $form->addSubForm($subForm, 'params', 4);

        $form->populate($pluginResource);

        return $form;

    }
}