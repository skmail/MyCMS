<?php

class App_Storage_Admin_Models_UploadTypes_ByUrl extends App_Storage_Admin_Models_UploadTypes_UploadTypesAbstract
{

    public function __construct($options)
    {

        $this->options = $options;

        $this->request = Zend_Controller_Front::getInstance()->getRequest();
       
        parent::__construct();

    }

    public function setFile($fileInput)
    {

        $this->options['name'] = basename($this->options['input']);

        if (!$tmp_name= @file_get_contents($this->options['input']))
        {
            $this->setError('File Content ');
        }else
        {
            $this->options['tmp_name'] = $tmp_name;
        }



        if (is_null($this->options['tmp_name']))
        {
            $this->setError('Invalid Url or illegal Content');
        }

        $this->options['size'] = count($this->options['tmp_name']);

        $pathinfo = pathinfo($this->options['name']);

        $this->options['ext'] = isset($pathinfo['extension']) ? $pathinfo['extension'] : '';

        $this->options['name'] = $this->getRandomName() . '.' . $this->options['ext'];


        $pathinfo = pathinfo($this->options['name']);


        $this->options['ext'] = isset($pathinfo['extension']) ? $pathinfo['extension'] : '';

        $this->options['uploadDirectory'] = $this->fn->storageFolder($this->request->getParam('group_id'));

        $this->options['destCrop'] = $this->fn->storageFolder($this->request->getParam('group_id'), 'destCrop');




    }

    public function upload()
    {


        $img = imagecreatefromstring($this->options['tmp_name']);

        imagejpeg($img, $this->options['uploadDirectory'] . $this->options['name']);

        imagedestroy($img);

        return true;

    }

    public function validate($options)
    {


        if (!filter_var($this->options['input'], FILTER_VALIDATE_URL))
        {
            $this->setError('Invalid Url');
        }
        parent::validate($options);

    }

}
