<?php

class App_Language_Admin_Language extends Admin_Model_ApplicationAbstract
{

    public function __construct($application)
    {
        parent::__construct($application);

        $this->_query = new App_Language_Admin_Queries($application);

        $this->_form = new App_Language_Admin_Forms($application);

        $this->application['nav']->append('Languages', 'window/index');



        $tabs = array(
            array('key'   => 'index', 'label' => Zend_Registry::get('Zend_Translate')->translate('Languages')),
            array('key'   => 'phrases', 'label' => Zend_Registry::get('Zend_Translate')->translate('Phrases'))
        );

        $currentTab = $this->_Zend->getRequest()->getParam('window');

        $currentTab = ($currentTab == "") ? "index" : $currentTab;

        foreach ($tabs as $tabKey => $tabVal)
        {
            if ($tabVal['key'] == $currentTab)
            {
                $tabs[$tabKey]['active'] = 'active';
            }
            else
            {
                $tabs[$tabKey]['active'] = '';
            }

            $tabs[$tabKey]['url'] = 'window/' . $tabVal['key'];
        }

        $this->application['tabs'] = $tabs;

    }

    public function index()
    {


        $this->application['languages'] = $this->_query->langQuery();

        $this->application['sidebar'] = 'indexSidebar';

        return $this->application;

    }

    public function phrases($options = array())
    {
        $this->application['languages'] = $this->application['languages'] = MC_Core_Loader::appClass('Language', 'Lang', NULL, 'Shared')->langsList();

        $this->application['language'] = $langQuery;

        $this->application['phrasesNames'] = $this->_query->phrasesNames();

        $this->application['phrasesValues'] = $this->_query->phrasesValues();



        $this->application['nav']->append('List Phrases');

        $this->application['sidebar'] = 'phrasesSidebar';

        return $this->application;

    }

    public function phrase($options = array())
    {

        $do = (isset($options['do'])) ? $options['do'] : $this->_Zend->getRequest()->getParam('do');

        if ($do != 'add' && $do != 'edit')
        {
            return;
        }


        $this->application['nav']->append(Zend_Registry::get('Zend_Translate')->translate('phrases'), 'window/phrases/');

        if ($do == 'add')
        {
            $this->application['nav']->append('Add new phrase');
        }

        if ($do == 'edit')
        {



            $phrase_name = (isset($options['phrase_name'])) ? $options['phrase_name'] : $this->_Zend->getRequest()->getParam('phrase_name');

            $this->application['nav']->append('Edit phrase : ' . $phrase_name);

            $lang_id = $langQuery['lang_id'];
            $langQuery = $this->_query->getPhrase(array('phrase_name'                     => $phrase_name));
            $langQuery['lang_id'] = $lang_id;
            $langQuery['current_phrase_name'] = $phrase_name;
        }

        $langQuery['do'] = $do;

        $this->application['phraseForm'] = (isset($options['phraseForm'])) ? $options['phraseForm'] : $this->_form->phraseForm($langQuery);

        return $this->application;

    }

    public function savePhrase()
    {

        $request = $this->_Zend->getRequest()->getPost();

        $data = $request['phrase'];


        if ($data['do'] != 'edit' && $data['do'] != 'add')
        {
            return;
        }

        $errors = false;

        $phraseForm = $this->_form->phraseForm();


        if ($phraseForm->isValid($data))
        {

            $data = array_shift($phraseForm->getValues());

            $data['phrase_value'] = $data['phrase_lang']['phrase_value'];
            unset($data['phrase_lang']);

            $phrase_name = $data['phrase_name'];

            $isAvailable = MC_Core_Loader::appClass('Language', 'Validator_PhraseAvailable', NULL, 'Shared')
                    ->isAvailable($data['phrase_name']);

            if ($data['do'] == 'add')
            {
                if ($isAvailable)
                {
                    foreach ($data['phrase_value'] as $lang_id => $phrase)
                    {
                        if (empty($phrase['phrase_value']))
                        {
                            continue;
                        }
                        $phraseData = array(
                            'lang_id'      => $lang_id,
                            'phrase_name'  => $phrase_name,
                            'phrase_value' => $phrase['phrase_value']
                        );

                        $this->db->insert('language_phrases', $phraseData);
                    }

                    $this->application['message']['text'] = 'Phrase added';
                    $this->application['message']['type'] = 'success';
                    
                    $this->setRefresh('sidebar');
                        
                    $phraseOptions['do'] = 'edit';
                    $phraseOptions['phrase_name'] = $phrase_name;
                    unset($data['do']);

                    App_Language_Shared_Cache::start();
                }
                else
                {

                    $this->application['message']['text'] = 'Phrase exists already';
                    $this->application['message']['type'] = 'error';
                    $phraseOptions['do'] = 'add';
                    $phraseOptions['phraseForm'] = $phraseForm;
                    unset($data['do']);
                }
            }
            else
            {
                if (!$isAvailable && ($data['current_phrase_name'] != $phrase_name))
                {
                    $this->application['message']['text'] = 'Phrase exists already';
                    $this->application['message']['type'] = 'error';
                    $phraseOptions['phraseForm'] = $phraseForm;
                    $phraseOptions['do'] = 'edit';
                    unset($data['do']);
                }
                else
                {

                    $checkPhrase = $this->_query->getPhrase(array('phrase_name' => $data['current_phrase_name']));
                    foreach ($data['phrase_value'] as $lang_id => $phrase)
                    {
                        if (empty($phrase['phrase_value']))
                        {
                            continue;
                        }

                        $phraseData = array();
                        $phraseData['lang_id'] = $lang_id;
                        $phraseData['phrase_name'] = $phrase_name;
                        $phraseData['phrase_value'] = $phrase['phrase_value'];

                        if (isset($checkPhrase[$lang_id]))
                        {
                            $where = $this->db->quoteInto(' phrase_name = ?  ', $data['current_phrase_name']);
                            $where.= $this->db->quoteInto(' AND lang_id = ?  ', $lang_id);
                            $this->db->update('language_phrases', $phraseData, $where);
                        }
                        else
                        {
                            $this->db->insert('language_phrases', $phraseData);
                        }

                        App_Language_Shared_Cache::start();
                    }

                    $this->application['message']['text'] = 'Phrase saved';
                    $this->application['message']['type'] = 'success';
                    $phraseOptions['do'] = 'edit';
                    $phraseOptions['phrase_name'] = $phrase_name;
                    $this->setRefresh('sidebar');
                }
            }
        }
        else
        {
            $this->application['message']['text'] = 'Some Fields empty';
            $this->application['message']['type'] = 'error';
            $phraseOptions['phraseForm'] = $phraseForm;
        }

        $phraseOptions = array_merge($phraseOptions, $data);

        $phraseMethod = $this->phrase($phraseOptions);

        if (!$phraseMethod)
        {
            return;
        }

        $this->application = array_merge($this->application, $phraseMethod);

        $this->application['window'] = 'phrase.phtml';
        
        return $this->application;

    }

    public function language($options = array())
    {


        $languageData = array();

        $do = (isset($options['do'])) ? $options['do'] : $this->_Zend->getRequest()->getParam('do');
        
        if(count($options))
        {
          $languageData = $options;  
        }
        
        if ($do == 'edit')
        {
            $lang_id = (isset($options['lang_id'])) ? $options['lang_id'] : $this->_Zend->getRequest()->getParam('lang_id');
            $lang_id = intval($lang_id);

            if (!$languageData = $this->_query->langQuery(array('lang_id' => $lang_id)))
            {
                return;
            }
            $this->application['nav']->append(Zend_Registry::get('Zend_Translate')->translate('Edit language'). ": " .$languageData['lang_name']);

        }
        else
        {
            $this->application['nav']->append(Zend_Registry::get('Zend_Translate')->translate('Add new language'));
        }

        $languageData['do'] = $do;


        $this->application['languageForm'] = (isset($options['languageForm'])) ? $options['languageForm'] :  $this->_form->languageForm($languageData);


        return $this->application;

    }

    public function saveLanguage()
    {

        $request = $this->_Zend->getRequest()->getPost();
        $language = array();
        $error = true;

        if ($request['do'] != 'add' && $request['do'] != 'edit')
        {
            return;
        }

        $languageForm = $this->_form->languageForm();


        if ($languageForm->isValid($request))
        {


            $language['lang_name'] = $request['lang_name'];
            $language['short_lang'] = $request['short_lang'];
            $language['lang_status'] = $request['lang_status'];
            $language['dir'] = $request['dir'];

            if ($request['lang_default'] == 1)
            {
                $language['lang_default'] = 1;
            }
            else
            {
                $language['lang_default'] = 0;
            }


            if ($request['do'] == 'add')
            {
                if (!$this->_query->langQuery(array('short_lang' => $request['short_lang'])))
                {

                    if($language['lang_default'] == 1)
                    {
                        $this->db->update('language',array('lang_default'=>0),$this->db->quoteInto('lang_default = ? ', 1));
                    }
                    
                    $this->db->insert('language', $language);

                    $language['lang_id'] = $this->db->lastInsertId();
                    $language['do'] = 'edit';

                    $error = false;
                    $this->application['message']['text'] = 'Language Added succesfully';
                    $this->application['message']['type'] = 'success';
                }
                else
                {
                    $this->application['message']['text'] = 'This language already exists';
                    $this->application['message']['type'] = 'error';
                }
            }
            else
            {
                //Edit
                
                $request['lang_id'] = intval($request['lang_id']);
                
                if (!$this->_query->langQuery(array('lang_id' => $request['lang_id'])))
                {
                    $this->application['message']['text'] = 'Language not exists';
                    $this->application['message']['type'] = 'error';
                    
                }else if ($this->_query->langQuery(array('short_lang' => $request['short_lang'],'where_not'=>array('lang_id'=>$request['lang_id']))))
                {
                    $this->application['message']['text'] = 'Language already exists';
                    $this->application['message']['type'] = 'error';
                }
                else
                {
                    if($language['lang_default'] == 1)
                    {
                        $this->db->update('language',array('lang_default'=>0),$this->db->quoteInto('lang_default = ? ', 1));
                    }

                    $this->db->update('language',$language,$this->db->quoteInto('lang_id = ? ', $request['lang_id'] ));

                    $this->application['message']['text'] = 'Language saved successfuly';
                    $this->application['message']['type'] = 'success';

                }
            }
        }
        else
        {


            $this->application['message']['text'] = 'An error occured';
            $this->application['message']['type'] = 'error';
        }


        if ($error)
        {
            $language['languageForm'] = $languageForm;
            $language['do'] = $request['do'];
        }
        
        $this->application = array_merge($this->application, $this->language($language));
        

        $this->application['window'] = 'language.phtml';


        return $this->application;

    }

}