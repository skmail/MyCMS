<?php

class App_Settings_Admin_Settings extends Admin_Model_ApplicationAbstract
{

    public $application = array();

    protected $db;

    protected $_Zend;

    public function __construct($application = array())
    {

        parent::__construct($application);
        $this->setNav('Settings',$this->application['url'].'/appPrefix/settings');
        $this->_forms = new App_Settings_Admin_Forms($application);
    }

    public function index()
    {

        $applicationsQuery = $this->db->select()->from('Applications')->where('configrable = ?', 0);


        $this->application['applicationsList'] = $this->db->fetchAll($applicationsQuery);

        return $this->application;

    }

    public function settings($options = array())
    {

        $app = (isset($options['app_prefix'])) ? $options['app_prefix'] : $this->_Zend->getRequest()->getParam('app');

        if (empty($app))
        {
            return;
        }

        $app = ucfirst($app);

        $applicationsQuery = $this->db->select()->from('Applications');

        $applicationsQuery->where('configrable = ?', 0);

        $applicationsQuery->where('app_prefix = ?', $app);

        $row = $this->db->fetchRow($applicationsQuery);

        if (!$row['settings'] = @json_decode($row['settings'], true))
        {
            $row['settings'] = array();
        }

        if (!$row)
        {
            return;
        }


        $formInstanceString = 'App_%s_Admin_Forms_%s';

        $formInstanceString = sprintf($formInstanceString, $app, 'Settings');

        $this->setNav($this->translate($row['app_name']));

        if(class_exists($formInstanceString)){
            $formInstance = new $formInstanceString();

            $this->application['settingsForm'] = $this->_forms->settings($row, $formInstance);

        }else
        {
            $this->application['message']['text'] = 'settings_form_not_exists';
            $this->application['message']['type'] = 'error';
            $this->application = array_merge($this->application,$this->index());
            $this->application['window'] = 'index';
        }
        
        return $this->application;
    }

    public function saveSettings()
    {

        $dataPost = $this->_Zend->getRequest()->getPost();

        if ($dataPost['do'] == '')
        {
            return;
        }

        $row['settings'] = json_encode($dataPost['settings']);

        $app_prefix = $dataPost['app_prefix'];

        $where = $this->db->quoteInto('app_prefix = ? ', $app_prefix);

        $this->db->update('Applications', $row, $where);

        $this->application['message']['text'] = 'Settings saved successfully';

        $this->application['message']['type'] = 'success';


        $this->application = array_merge($this->application, $this->settings(array('app_prefix' => $app_prefix)));

        $this->application['window'] = 'settings';


        return $this->application;

    }

}