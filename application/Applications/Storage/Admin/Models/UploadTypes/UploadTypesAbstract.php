<?php

abstract class App_Storage_Admin_Models_UploadTypes_UploadTypesAbstract implements App_Storage_Admin_Models_UploadTypes_UploadTypesInterface
{

    public $options = array();

    public $allowedExtensions = array();

    protected $fn;

    public $errors = array();

    /**
     * Removes a directory and all files contained inside
     * @param string $dir
     */
    public function __construct()
    {
        $this->fn = new App_Storage_Admin_Models_Functions();
    }

    protected function removeDir($dir)
    {
        foreach (scandir($dir) as $item)
        {
            if ($item == "." || $item == "..")
            {
                continue;
            }
            unlink($dir . DIRECTORY_SEPARATOR . $item);
        }
        rmdir($dir);

    }

    abstract function setFile($fileInput);
    
    abstract function upload();

    function validate($options)
    {
        if (!is_writable($this->options['uploadDirectory']) || !is_executable($this->options['uploadDirectory']))
        {
            return array('error' => "Server error. Uploads directory isn't writable or executable. " . $this->options['uploadDirectory']);
        }

    }

    function setUploadDirectory($ext)
    {
        
    }

    protected function setError($errorMessage)
    {
        $this->errors[] = $errorMessage;

    }

    public function getErrors()
    {
        if (count($this->errors) == 0)
        {
            return false;
        }

        return true;

    }

    public function getResults()
    {

        $results = $this->options;

        // unset($results['tmp_name']);

        return $results;

    }

    protected function getRandomName()
    {
        return md5(time() . mt_rand(999, 999999));

    }
}