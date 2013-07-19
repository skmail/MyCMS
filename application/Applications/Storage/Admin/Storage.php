<?php

class App_Storage_Admin_Storage extends Admin_Model_ApplicationInstance
{

    public $renderWindow = true;

    public $_do = array('add', 'edit', 'delete');

    protected $_sharedQuery = NULL;

    static $_targetDestination = 'contents/userdata/';

    public function __construct($application = array())
    {

        parent::__construct($application);

        $this->menu = array(
            array('title' => 'Add New Group', 'url'   => 'window/group/do/add'),
            array('title' => 'Upload New File', 'url'   => 'window/file/do/add')
        );

        $this->_sharedQuery = new App_Storage_Shared_Queries($application);
        
        $this->Functions  = new App_Storage_Admin_Models_Functions($application);
        
        
        $this->application['renderWindow'] = $this->renderWindow;

        $this->application['nav']->append(Zend_Registry::get('Zend_Translate')->translate('Storage'), 'window/index');

    }

    public function index()
    {

        $this->application['groups'] = $this->db->fetchAll($this->groupQuery(0, false));

        $this->application['sidebar'] = 'indexSidebar';

        return $this->application;

    }

    public function group($options = array())
    {
        $do = (isset($options['do'])) ? $options['do'] : $this->_Zend->getRequest()->getParam('do');

        $group_id = (isset($options['group_id'])) ? $options['group_id'] : $this->_Zend->getRequest()->getParam('group_id');

        $data = array();


        if (!in_array($do, $this->_do))
        {
            return;
        }


        if ($do == 'edit')
        {
            if (!$data = $this->_sharedQuery->group(array('group_id' => intval($group_id))))
            {
                return;
            }
            
            $data['old_folder_name'] = $data['folder'];
            $data['old_source_folder'] = $data['source_folder'];
            
            
        }

        $data['do'] = $do;

        $this->application['groupForm'] = isset($options['groupForm']) ? $options['groupForm'] : $this->groupForm($data);

        return $this->application;

    }

    public function saveGroup()
    {

        $request = $this->_Zend->getRequest()->getPost();

        $error = true;
        $group = array();


        if (!in_array($request['do'], $this->_do))
        {
            return;
        }


        $groupForm = $this->groupForm();


        if ($groupForm->isValid($request))
        {

            $group['folder'] = $request['folder'];
            $group['source_folder'] = $request['source_folder'];
            
            
            
            if ($request['do'] == 'add')
            {

                if ($this->_sharedQuery->group(array('folder' => $request['folder'])))
                {
                    $this->application['message']['text'] = 'Destination Folder already eixsts';

                    $this->application['message']['type'] = 'error';
                }
                else
                {

                    $this->db->insert('storage_group', $group);

                    $group['group_id'] = $this->db->lastInsertId();

                    foreach ($request['group_lang'] as $lang_id => $langData)
                    {
                        if (!empty($langData['group_name']))
                        {
                            $groupLang['group_name'] = $langData['group_name'];
                            $groupLang['lang_id'] = $lang_id;
                            $groupLang['group_id'] = $group['group_id'];
                            $this->db->insert('storage_group_lang', $groupLang);
                        }
                    }

                    $this->application['message']['text'] = 'Group added successfuly';
                    $this->application['message']['type'] = 'success';
                    $group['do'] = 'edit';
                    $error = false;
                    
                    mkdir(self::$_targetDestination.$request['folder'],0777);
                    mkdir(self::$_targetDestination.$request['folder'].'/'.$request['source_folder'],0777);
                    
                }
            }
            else if ($request['do'] == 'edit')
            {
                $group['group_id'] = $request['group_id'];

                if ($this->_sharedQuery->group(array('folder'    => $request['folder'], 'where_not' => array('group_id' => $group['group_id']))))
                {
                    $this->application['message']['text'] = 'Destination Folder already eixsts';
                    $this->application['message']['type'] = 'error';
                }
                else
                {

                    $this->db->update('storage_group', $group, $this->db->quoteInto("group_id = ?", $group['group_id']));

                    foreach ($request['group_lang'] as $lang_id => $langData)
                    {
                        if (!empty($langData['group_name']))
                        {
                            $groupLang = array();
                            $groupLang['group_name'] = $langData['group_name'];
                            $groupLang['lang_id'] = $lang_id;
                            $groupLang['group_id'] = $group['group_id'];

                            if ($this->_sharedQuery->group(array('lang_id'                     => $lang_id, 'storage_group_lang.group_id' => $group['group_id'])))
                            {
                                $this->db->update('storage_group_lang', $groupLang, $this->db->quoteInto("group_id = ? AND ", $group['group_id']) . $this->db->quoteInto("lang_id = ?", $lang_id));
                            }
                            else
                            {
                                $this->db->insert('storage_group_lang', $groupLang);
                            }
                        }
                    }
                    
                   
                    if(!is_dir(self::$_targetDestination.$request['old_folder_name']))
                    {
                        mkdir(self::$_targetDestination.$request['old_folder_name'],0777);
                    } 
                    
                    
                    if(!is_dir(self::$_targetDestination.$request['old_folder_name'].'/'.$request['source_folder']))
                    {
                        mkdir(self::$_targetDestination.$request['old_folder_name'].'/'.$request['source_folder'],0777);
                    }
                    
                    if($request['folder'] != $request['old_folder_name'])
                    {   
                        rename(self::$_targetDestination.$request['old_folder_name'], self::$_targetDestination.$request['folder']);
                    }
                    
               
                    if($request['old_source_folder'] != $request['source_folder'])
                    {   
                        rename(self::$_targetDestination.$request['folder']."/".$request['old_source_folder'], self::$_targetDestination.$request['folder'].'/'.$request['source_folder']);
                    }
                    
                    
                    $this->application['message']['text'] = 'Storage group saved successfully';
                    $this->application['message']['type'] = 'success';
                    $error = false;
                }
            }
            else
            {
                return;
            }
        }


        if ($error)
        {
            $group['groupForm'] = $groupForm;
            $group['do'] = $request['do'];
            if ($group['do'] == 'edit')
            {
                $group['group_id'] = $request['group_id'];
            }
        }

        $this->application = array_merge($this->application, $this->group($group));

        $this->application['window'] = 'group.phtml';

        return $this->application;

    }

    public function files()
    {
        $request = $this->_Zend->getRequest();

        $groupId = $request->getParam('group_id');

        $files = $this->db->fetchAll($this->fileQuery(0, $groupId));

        $this->application['files'] = $files;

        return $this->application;

    }

    public function file()
    {
        $request = $this->_Zend->getRequest();

        $this->application['include_tabs'] = explode('|', $request->getParam('include-tabs'));

        $this->application['include_buttons'] = explode('|', $request->getParam('include-buttons'));

        $this->application['fileForm'] = $this->fileForm();

        $this->application['uploadFileByUrlForm'] = $this->FileByUrlForm();

        $this->application['filesLibraryGrid'] = '';

        $this->application['groupsList'] = $this->Functions->groupsList();
             
             
        $this->application['pageTitle'] = 'Upload New File';

        return $this->application;

    }

    private function listGroups()
    {
        $this->application['windowView'] = 'listGroups.phtml';

        $this->application['groups'] = $this->db->fetchAll($this->groupQuery(0, false));

        return $this->application;

    }

    private function listExtensions()
    {
        $this->application['windowView'] = 'listExtensions.phtml';

        $this->application['extensions'] = $this->db->fetchAll($this->extensionQuery(0, false));

        return $this->application;

    }

    public static function groupQuery($groupId = 0, $onlyRow = true, $onlyLang = true, $static = false)
    {

        $db = Zend_Registry::get('db');

        $groupQuery = $db->select()->from('storage_group');

        $langs = MC_Core_Loader::appClass('Language', 'Lang', NULL, 'Shared');

        //
        
        if(!$onlyRow && $onlyLang) 
        { 
            $groupQuery->joinLeft('storage_group_lang', "storage_group.group_id = storage_group_lang.group_id
                             AND (lang_id = " . $langs->currentLang()." OR lang_id =(select lang_id from language where lang_default = 1)  ) group by storage_group.group_id");

        }else
        {
            $groupQuery->join('storage_group_lang', 'storage_group.group_id = storage_group_lang.group_id');

        }
        
        
        if ($groupId != 0 && $onlyRow)
        {
            $groupQuery->where('storage_group_lang.group_id = ? ', $groupId);
        }

        return $groupQuery;

    }

    private function groupForm($groupRows = NULL)
    {
        $groupForm = new App_Storage_Admin_Forms_Group(array('action' => $this->application['url'] . 'window/saveGroup'));

        if ($groupRows != NULL)
        {
            $groupForm->populate($groupRows);
        }

        return $groupForm;

    }

    private function extensionQuery($extId = 0, $onlyRow = true, $onlyLang = true)
    {

        $extId = intval($extId);

        $db = Zend_Registry::get('db');

        $extQuery = $db->select()->from('storage_extensions');

        $extQuery = $extQuery->join('storage_extensions_lang', 'storage_extensions_lang.ext_id=storage_extensions.ext_id');

        if ($onlyRow && $extId != 0)
        {
            $extQuery->where('storage_extensions_lang = ?', $extId);
        }

        if ($onlyLang)
        {
            $extQuery->where('lang_id = ? ', $this->application['lang_id']);
        }

        return $extQuery;

    }

    private function fileQuery($fileId = 0, $groupId = 0, $extId = 0)
    {


        $fileQuery = $this->db->select()->from('storage');

        if ($fileId != 0)
        {
            $fileQuery->where('storage_id = ?', $fileId);
        }
        if ($groupId != 0)
        {
            $fileQuery->where('group_id = ?', $groupId);
        }
        if ($extId != 0)
        {
            $fileQuery->where('ext_id = ?', $extId);
        }

        return $fileQuery;

    }

    private function fileForm($fileRow = NULL)
    {

        $fileForm = new App_Storage_Admin_Forms_File(array('action' => $this->application['url'] . 'window/saveFile/do/byForm'));

        if ($fileRow != NULL)
        {
            $fileForm->populate($fileRow);
        }

        return $fileForm;

    }

    protected function FileByUrlForm()
    {
        $fileForm = new App_Storage_Admin_Forms_FileByUrl(array('action' => $this->application['url'] . 'window/saveFile/uploadType/byUrl'));

        if ($fileRow != NULL)
        {
            $fileForm->populate($fileRow);
        }

        return $fileForm;

    }

    public function saveFile()
    {

        $uploadType = $this->_Zend->getRequest()->getParam('uploadType');

        if ($uploadType == 'byForm')
        {
            $uploaderData = array('input' => '');
        }
        else if ($uploadType == 'byUrl')
        {
            $uploaderData = array('input' => $this->_Zend->getRequest()->getPost('file_url'));
        }
        else
        {
            return false;
        }

        $uploaderData['uploadType'] = $uploadType;

        $userdata = $this->uploadFileByForm($uploaderData);

        $this->application['result'] = $userdata;

        return $this->application;

    }

    private function fileExt($filename)
    {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);

        $ext = strtolower($ext);

        return $ext;

    }

    private function newFileName()
    {
        return md5(time() . mt_rand(999, 999999));

    }

    private function fileSize($size)
    {
        $unit = null;

        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        for ($i = 0, $c = count($units); $i < $c; $i++)
        {
            if ($size > 1024)
            {
                $size = $size / 1024;
            }
            else
            {
                $unit = $units[$i];
                break;
            }
        }

        return round($size, 2) . $sep . $unit;

    }

    protected function uploadFileByForm($options)
    {


        $request = $this->_Zend->getRequest()->getParams();

        $uploader = new App_Storage_Admin_Models_upload($options);

        // Specify the list of valid extensions, ex. array("jpeg", "xml", "bmp")
        $uploader->allowedExtensions = array();

        // Specify max file size in bytes.
        $uploader->sizeLimit = 10 * 1024 * 1024;

        // Specify the input name set in the javascript.
        $uploader->inputName = 'qqfile';

        // If you want to use resume feature for uploader, specify the folder to save parts.
        $uploader->chunksFolder = 'chunks';

        // Call handleUpload() with the name of the folder, relative to PHP's getcwd()
        $result = $uploader->handleUpload();

        // To save the upload with a specified name, set the second parameter.
        // $result = $uploader->handleUpload('uploads/', md5(mt_rand()).'_'.$uploader->getName());
        // To return a name used for uploaded file you can use the following line.
        //$result['uploadName'] = $uploader->getUploadName();

        return $this->application['output'] = $result;

        die();

        $adapter = new Zend_File_Transfer_Adapter_Http();
        $files = $adapter->getFileInfo();

        foreach ($files as $file)
        {
            $ext = $this->fileExt($file['name']);

            $storageExt = $this->getStorageExtByExt($ext);

            $destination = $this->storageFolder($storageExt);

            $destinationCrop = $this->storageFolder($storageExt, 'destCrop');

            $adapter->setDestination($destination);

            //rename file
            $newFileName = $this->newFileName();

            $adapter->addFilter('Rename', $destination . $newFileName . '.' . $ext);

            if ($adapter->isUploaded($file['name']))
            {

                $adapter->receive($file['name']);

                $fileData['ext_id'] = $storageExt['ext_id'];

                $fileData['ext'] = $ext;

                $fileData['group_id'] = $request['group_id'];

                $fileDb = $this->saveFileDb($fileData);

                $fileName = $fileDb['id'] . '.' . $ext;

                $oldFile = $destination . $newFileName . '.' . $ext;

                $newFile = $destination . $fileName;

                $file['url'] = $destinationCrop . '/135x135/' . $fileName;

                $file['size'] = $this->fileSize($file['size']);

                $file['upload_date'] = date('Y-m-d h:i:s A', $fileDb['upload_date']);

                $file['storage_id'] = $fileDb['id'];

                $file['full_name'] = $file['name'];

                $file['name'] = MC_Models_String::cut($file['name'], 20, 'c', '...');

                rename($oldFile, $newFile);

                $userdata[] = $file;
            }
        }

        return $userdata;

    }

    protected function uploadFileByUrl()
    {

        $request = $this->_Zend->getRequest()->getPost();

        $fileUrl = $request['file_url'];

        $ext = $this->fileExt($fileUrl);

        $adapter = new Zend_Http_Client();

        $storageExt = $this->getStorageExtByExt($ext);

        $destination = $this->storageFolder($storageExt);

        $destinationCrop = $this->storageFolder($storageExt, 'destCrop');

        $c = new Zend_Http_Client();

        $c->setUri($fileUrl);

        $result = $c->request('GET');

        $newFileName = $this->newFileName();

        $img = imagecreatefromstring($result->getBody());

        imagejpeg($img, $destination . $newFileName . '.' . $ext);

        imagedestroy($img);

        $fileData['ext_id'] = $storageExt['ext_id'];

        $fileData['ext'] = $ext;

        $fileData['group_id'] = $request['group_id'];

        $fileDb = $this->saveFileDb($fileData);

        $fileName = $fileDb['id'] . '.' . $ext;

        $oldFile = $destination . $newFileName . '.' . $ext;

        $newFile = $destination . $fileName;

        rename($oldFile, $newFile);

        $file['url'] = $destinationCrop . '/135x135/' . $fileName;

        $file['size'] = $this->fileSize(filesize($newFile));

        $file['upload_date'] = date('Y-m-d h:i:s A', $fileDb['upload_date']);

        $file['storage_id'] = $fileDb['id'];

        $file['full_name'] = $file['name'];

        $file['name'] = MC_Models_String::cut($fileUrl, 20, 'c', '...');

        $userdata[] = $file;

        return $userdata;

    }

}