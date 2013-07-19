<?php

class Admin_View_Helper_LangSwitcher
{

    public function langSwitcher()
    {
        $output = array();
        $frontController = Zend_Controller_Front::getInstance();

        $locales = $frontController->getParam('locales');
        $request = $frontController->getRequest();
        $baseUrl = $request->getBaseUrl();


        $baseUrl = $this->solidUrl($baseUrl);


        $path =  $this->pathInfo(trim($request->getPathInfo(), '/\\'));
        
        if (count($locales) > 0)
        {
            $locale = Zend_Registry::get('Zend_Locale');
            $localeLanguage = $locale->getLanguage();
            $defaultLocaleLanguage = array_keys($locale->getDefault());
            $defaultLocaleLanguage = $defaultLocaleLanguage[0];


            foreach ($locales as $languageKey => $language)
            {

                $urlLanguage = $defaultLocaleLanguage == $language ? '' : $language;
                
                $localeUrl =  ($urlLanguage == ""?"":$urlLanguage."/") . $path;
                

                $output[$languageKey]['url'] = $localeUrl;
                $output[$languageKey]['language'] = $language;
            }
        }

        return $output;

    }

    protected function solidUrl($url)
    {
        $baseUrl = trim($url, '/');

        $baseUrlSegments = explode('/', $baseUrl);

        $langCode = end($baseUrlSegments);

        if (strlen($langCode) == 2)
        {
            if (preg_match('/^(ar|en)$/', $langCode))
            {
                $langCodeIndex = count($baseUrlSegments) - 1;
                
                unset($baseUrlSegments[$langCodeIndex]);
                
                $url = implode('/', $baseUrlSegments);
            }
        }
        return $url;

    }
    
    
    protected function pathInfo($path)
    {
        
        $path = trim($path, '/');

        $pathSegments = explode('/', $path);

        $langCode = reset($pathSegments);

        if (strlen($langCode) == 2)
        {
            if (preg_match('/^(ar|en)$/', $langCode))
            {
                unset($pathSegments[0]);
                
                $path = implode('/', $pathSegments);
            }
        }
        return $path;
        
    }

}