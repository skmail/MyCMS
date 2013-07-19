<?php

class App_Storage_Admin_Models_Functions
{

    public function __construct( $application = array())
    {
        $this->db = Zend_Registry::get('db');
        $this->_query =  new App_Storage_Shared_Queries();
    }

    /*
     * destFolder : Destination Folder
     */

    public function storageFolder($group_id, $return = 'destFolder')
    {
        
        
       
        
        $query = $this->_query->group(array('group_id'=>$group_id));
        
        
        
            
        $destination = 'contents/userdata/' . $query['folder']  . '/'.$query['source_folder'].'/';
            
        $destinationCrop = 'contents/userdata/'.$query['folder'];
         

        if ($return == 'destCrop')
        {
            return $destinationCrop;
        }
        else
        {
            return $destination;
        }
    }
 

    public function saveFileDb(array $data)
    {

        $fileData = array();
        
        $fileData['file_ext'] = $data['ext'];
       
        $fileData['group_id'] = $data['group_id'];
         
        $fileData['upload_date'] = time();

        $this->db->insert('storage', $fileData);

        $fileData['id'] = $this->db->lastInsertId();

        return $fileData;

    }

    public function fileSize($size)
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
    
    
    public function groupsList()
    {
     
        $langs = MC_Core_Loader::appClass('Language', 'Lang', NULL, 'Shared');

        $currentLang = $langs->currentLang();
        
        
        return $this->_query->group(array('lang_id'=>$currentLang));
    }

}