<?php

class App_Storage_Admin_Models_UploadTypes_ByForm extends App_Storage_Admin_Models_UploadTypes_UploadTypesAbstract
{

    public $fileInfo = array();

    public $chunksFolder = 'contents/userdata/images/chunks';

    public $chunksCleanupProbability = 0.001; // Once in 1000 requests on avg

    public $chunksExpireIn = 604800; // One week

    public function __construct($options)
    {
        parent::__construct();
     
        $this->options = $options;
        
        $this->request = Zend_Controller_Front::getInstance()->getRequest();

    }

    public function setFile($inputName)
    {


        $file = $_FILES[$inputName];

        $req = $this->request->getParam('qqfilename');

        $this->options = array_merge($file, $this->options);

        if (!empty($req))
        {
            $this->options['name'] = $this->request->getParam('qqfilename');
        }

        $pathinfo = pathinfo($this->options['name']);


        $this->options['ext'] = isset($pathinfo['extension']) ? $pathinfo['extension'] : '';
        
        $this->options['uploadDirectory'] = $this->fn->storageFolder($this->request->getParam('group_id'));

        $this->options['destCrop'] = $this->fn->storageFolder($this->request->getParam('group_id'), 'destCrop');

    }

    public function upload()
    {

        if ($this->getErrors())
        {
            return;
        }

        $totalParts = isset($_REQUEST['qqtotalparts']) ? (int) $_REQUEST['qqtotalparts'] : 1;

        if ($totalParts > 1)
        {

            $chunksFolder = $this->chunksFolder;

            $partIndex = (int) $_REQUEST['qqpartindex'];

            $uuid = $_REQUEST['qquuid'];

            if (!is_writable($chunksFolder) || !is_executable($this->options['uploadDirectory']))
            {
                $this->setError("Server error. Chunks directory isn't writable or executable.");
                return false;
            }

            $targetFolder = $this->chunksFolder . DIRECTORY_SEPARATOR . $uuid;

            if (!file_exists($targetFolder))
            {
                mkdir($targetFolder);
            }

            $target = $targetFolder . '/' . $partIndex;

            $success = $this->uploadType->upload($file['tmp_name'], $target);

            // move_uploaded_file($_FILES[$this->inputName]['tmp_name'], );
            // Last chunk saved successfully
            if ($success AND ($totalParts - 1 == $partIndex))
            {

                $target = $this->getUniqueTargetPath($$this->options['uploadDirectory'], $name);
                
                $this->uploadName = basename($target);

                $target = fopen($target, 'w');

                for ($i = 0; $i < $totalParts; $i++)
                {
                    $chunk = fopen($targetFolder . '/' . $i, "w");
                
                    stream_copy_to_stream($chunk, $target);
                    
                    fclose($chunk);
                }

                // Success
                fclose($target);

                for ($i = 0; $i < $totalParts; $i++)
                {
                    $chunk = fopen($targetFolder . '/' . $i, "r");
               
                    unlink($targetFolder . '/' . $i);
                }
             
                rmdir($targetFolder);

                return true;
            }

            return true;
        }
        else
        {


            $target = $this->getUniqueTargetPath($this->options['uploadDirectory'], $this->options['name']);

            if ($target)
            {
                $this->uploadName = basename($target);

                if (move_uploaded_file($this->options['tmp_name'], $target))
                {
                    return true;
                }
            }
            
            $this->options['targetFile'] = $target;

            $this->setError('Could not save uploaded file.' .
                    'The upload was cancelled, or server error encountered');
            return false;
        }

    }

    public function validate($options)
    {
        if (!isset($_SERVER['CONTENT_TYPE']))
        {
            $this->setError("No files were uploaded.");
         
            return false;
        }
        else if (strpos(strtolower($_SERVER['CONTENT_TYPE']), 'multipart/') !== 0)
        {
            
            $this->setError("Server error. Not a multipart request. 
                            Please set forceMultipart to default value (true).");
            
            return false;
            
        }

        if (is_writable($this->chunksFolder) &&
                1 == mt_rand(1, 1 / $this->chunksCleanupProbability))
        {
            // Run garbage collection
            $this->cleanupChunks();
        }
        return true;

    }

    /**
     * Deletes all file parts in the chunks folder for files uploaded
     * more than chunksExpireIn seconds ago
     */
    protected function cleanupChunks()
    {
        foreach (scandir($this->chunksFolder) as $item)
        {
            if ($item == "." || $item == "..")
            {
                continue;
            }

            $path = $this->chunksFolder . DIRECTORY_SEPARATOR . $item;

            if (!is_dir($path))
            {
                continue;
            }

            if (time() - filemtime($path) > $this->chunksExpireIn)
            {
                $this->removeDir($path);
            }
        }

    }

    /**
     * Returns a path to use with this upload. Check that the name does not exist,
     * and appends a suffix otherwise.
     * @param string $uploadDirectory Target directory
     * @param string $filename The name of the file to use.
     */
    protected function getUniqueTargetPath($uploadDirectory, $filename)
    {
        // Allow only one process at the time to get a unique file name, otherwise
        // if multiple people would upload a file with the same name at the same time
        // only the latest would be saved.

        if (function_exists('sem_acquire'))
        {
            $lock = sem_get(ftok(__FILE__, 'u'));
            sem_acquire($lock);
        }

        $pathinfo = pathinfo($filename);
        
        $base = $pathinfo['filename'];
        
        $ext = isset($pathinfo['extension']) ? $pathinfo['extension'] : '';
        
        $ext = $ext == '' ? $ext : '.' . $ext;

        $unique = $base;
        
        $suffix = 0;

        // Get unique file name for the file, by appending random suffix.

        while (file_exists($uploadDirectory . DIRECTORY_SEPARATOR . $unique . $ext))
        {
            $suffix += rand(1, 999);
        
            $unique = $base . '-' . $suffix;
        }

        $result = $uploadDirectory . DIRECTORY_SEPARATOR . $unique . $ext;

        // Create an empty target file
        if (!touch($result))
        {
            // Failed
            $result = false;
        }

        if (function_exists('sem_acquire'))
        {
            sem_release($lock);
        }

        return $result;

    }

}

