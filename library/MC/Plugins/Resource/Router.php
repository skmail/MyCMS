<?php

class MC_Plugins_Resource_Router extends Zend_Application_Resource_Router
{

    public $_explicitType = 'router';

    protected $_front;

    protected $_locale;

    /**
     * Retrieve router object
     *
     * @return Zend_Controller_Router_Rewrite
     */
    public function getRouter()
    {
        $options = $this->getOptions();

        if (!isset($options['locale']['enabled']) ||
                !$options['locale']['enabled'])
        {
            return parent::getRouter();
        }

        $bootstrap = $this->getBootstrap();

        if (!$this->_front)
        {
            $bootstrap->bootstrap('FrontController');
            $this->_front = $bootstrap->getContainer()->frontcontroller;
        }

        if (!$this->_locale)
        {
            $bootstrap->bootstrap('Locale');
            $this->_locale = $bootstrap->getContainer()->locale;
        }

        $defaultLocale = array_keys($this->_locale->getDefault());
        $defaultLocale = $defaultLocale[0];

        $locales = $this->_front->getParam('locales');
        
        $langs = App_Language_Shared_Lang::langsList();
        $locales = array();
        foreach($langs as $lang)
        {
            $locales[] = $lang['short_lang'];
        }
        
        
        $requiredLocalesRegex = '^(' . join('|', $locales) . ')$';

        
        $routes = $options['routes'];
        foreach ($routes as $key => $value)
        {
             $defaults = isset($value['defaults']) ? $value['defaults'] : array();

             $value['defaults'] = array_merge(array('locale' => $defaultLocale), $defaults);

            $routes[$key] = $value;

             $routeString = $value['route'];
            $routeString = ltrim($routeString, '/\\');

             if (!isset($value['type']) ||
                    $value['type'] === 'Zend_Controller_Router_Route')
            {
                $value['route'] = ':locale/' . $routeString;

                $value['reqs']['locale'] = $requiredLocalesRegex;

                $routes['locale_' . $key] = $value;
            }
            else if ($value['type'] === 'Zend_Controller_Router_Route_Regex')
            {
                $value['route'] = '(' . join('|', $locales) . ')\/' . $routeString;

                $map = isset($value['map']) ? $value['map'] : array();
                foreach ($map as $index => $word)
                {
                    unset($map[$index++]);
                    $map[$index] = $word;
                }

                // Add our locale map
                $map[1] = 'locale';
                ksort($map);

                $value['map'] = $map;

                $routes['locale_' . $key] = $value;
            }
            else if ($value['type'] === 'Zend_Controller_Router_Route_Static')
            {
                foreach ($locales as $locale)
                {
                    $value['route'] = $locale . '/' . $routeString;
                    $value['defaults']['locale'] = $locale;
                    $routes['locale_' . $locale . '_' . $key] = $value;
                }
            }
        }

        $options['routes'] = $routes;
        $this->setOptions($options);
        return parent::getRouter();

    }

}