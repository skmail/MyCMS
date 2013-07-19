<?php

class App_Storage_Admin_Models_upload
{

    public $allowedExtensions = array();

    public $sizeLimit = null;

    public $inputName = 'qqfile';

    protected $uploadName;

    protected $request;

    protected $fn;

    protected $options;

    protected $errors = array();

    function __construct($options)
    {
        $this->options = $options;
       
        $this->fn = new App_Storage_Admin_Models_Functions();
        
        $this->sizeLimit = $this->toBytes(ini_get('upload_max_filesize'));
        
        $this->request = Zend_Controller_Front::getInstance()->getRequest();

    }

    public function handleUpload($name = null)
    {

        // Check that the max upload size specified in class configuration does not
        // exceed size allowed by server config
        if ($this->toBytes(ini_get('post_max_size')) < $this->sizeLimit ||
                $this->toBytes(ini_get('upload_max_filesize')) < $this->sizeLimit)
        {
            $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';
            return array('error' => "Server error. Increase post_max_size and upload_max_filesize to " . $size);
        }

        $UploadTypeModel = 'App_Storage_Admin_Models_UploadTypes_' . $this->options['uploadType'];

        if (class_exists($UploadTypeModel))
        {
            $this->UploadType = new $UploadTypeModel($this->options);
            if (!$this->UploadType instanceof App_Storage_Admin_Models_UploadTypes_UploadTypesAbstract)
            {
                return array('error' => 'Upload type class "' . $uploadType . '" Must implement UploadTypesInterface Interace');
            }
        }
        else
        {
            return array('error' => 'Upload Type Class "' . $uploadType . '" Not Found ');
        }

        $file = $this->UploadType->setFile($this->inputName);


        if (is_array($this->UploadType->validate($this->options)))
        {
            return $this->UploadType->validate($this->options);
        }
        // Get size and name


        if (!$this->UploadType->getErrors())
            if ($this->UploadType->upload())
            {
                $result = $this->UploadType->getResults();

                $fileData['ext_id'] = $result['storageExt']['ext_id'];
        
                $fileData['ext'] = $result['ext'];
                
                $fileData['group_id'] = $this->request->getParam('group_id');

                $fileDb = $this->fn->saveFileDb($fileData);

                $fileName = $fileDb['id'] . '.' . $fileData['ext'];

                $oldFile = $result['uploadDirectory'] . $result['name'];

                $newFile = $result['uploadDirectory'] . $fileName;

                $result['size'] = $this->fn->fileSize($file['size']);

                $result['storage_id'] = $fileDb['id'];
                
                $result['full_name'] = $result['name'];
                
                $result['name'] = MC_Models_String::cut($result['name'], 20, 'c', '...');
                
                rename($oldFile, $newFile);

                return array('success' => true, 'results' => $result);
            }

        return $this->UploadType->errors;

    }

    /**
     * Converts a given size with units to bytes.
     * @param string $str
     */
    protected function toBytes($str)
    {
        $val = trim($str);
        
        $last = strtolower($str[strlen($str) - 1]);
        
        switch ($last)
        {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;
        }
        return $val;

    }

    protected function rename($oldName, $newName)
    {
        rename($oldName, $newName);

    }

}