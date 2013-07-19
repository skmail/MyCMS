<?php

class Admin_View_Helper_AssetsUrl extends Zend_View_Helper_Abstract
{

    public function assetsUrl()
    {


        /* $frontController = Zend_Controller_Front::getInstance();



          $bootstrap = $frontController->getActionController()->getInvokeArg('bootstrap');


          $config = $bootstrap->getOptions();

         */


      $settings = MC_Core_Loader::appClass('settings', 'settings', NULL , 'Shared');
      
      $assets_url = $settings->get('settings','assets_url');
      return $assets_url;

        return 'http://localhost/cmstest/mycms/';

    }

}