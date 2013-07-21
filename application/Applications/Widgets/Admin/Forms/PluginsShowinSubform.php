<?php

class App_Widgets_Admin_Forms_WidgetsShowinSubform extends MC_Admin_Form_BaseForm {
 
    
    public function init() {
        
        
        $pagesList = $showInForm->createElement('MultiCheckbox', "0",
                array('label' => 'HomePage',
            'isArray' => true,
            'belongsTo' => 'app',
                                            'decorators'=>MC_Admin_Form_Form::$elementDecorators
                ));
        $pagesList->addMultiOption('0', 'Homepage');

        $showInForm->addElement($pagesList);

        $apps = $this->applicationsPlugins();
        
        foreach ($apps as $k => $v) {

            $appData = $this->applicationsQuery($v);

            $pagesList = $showInForm->createElement('MultiCheckbox', "'" . $k . "'", array('label' => 'Show in',
                'isArray' => true,
                'belongsTo' => 'app',
                                            'decorators'=>MC_Admin_Form_Form::$elementDecorators
                    ));
            $pagesList->setAttrib('escape', false);
            foreach ($appData as $appDataK => $appDataV) {
                $cat_id = $appDataV[$v['categoryKey']];
                $catLabel = $appDataV[$v['categoryLabel']];
                $pagesList->addMultiOption($cat_id, $catLabel);
            }

            $showInForm->addElement($pagesList);
            unset($pagesList);
        }
        
        
    }
   
    
     private function applicationsPlugins() {

        $db = Zend_Registry::get('db');
        
        $applicationsQuery = $db->select()->from('Applications')->where('plugins = ? ', 1);

        $applicationsRow = $db->fetchAll($applicationsQuery);

        $apps = array();

        foreach ($applicationsRow as $app) {
            $appPrefix = ucfirst($app['app_prefix']);
            $appModel = 'App_' . $appPrefix . '_' . $appPrefix;

            $appModel = new $appModel();

            $apps[$app['app_id']] = $appModel->plugin;
        }
        return $apps;
    }

    private function applicationsQuery($appArray) {


        $db = Zend_Registry::get('db');
        $query = $db->select()->from($appArray['categoryTable']);

        if (isset($appArray['dependOn'])) {

            if (isset($appArray['dependOnSecKey'])) {
                $dependOnSecKeyWhere = $appArray['dependOnSecKey'] . ' = ?';
                $appArray['dependOnSecVal'];
            }
            else
                $dependOnSecKeyWhere = '';
            $query->join($appArray['dependOn'], $appArray['dependOn'] . '.' . $appArray['dependOnPriKey'] . ' = ' .
                    $appArray['categoryTable'] . '.' . $appArray['categoryKey']
            );
        }

        if ($dependOnSecKeyWhere != "")
            $query->where($dependOnSecKeyWhere, $appArray['dependOnSecVal']);

        $row = $db->fetchAll($query);

        return $row;
    }


    
    
   }