<?php

class MC_App_Storage_Storage
{

    public static $_upliadDir = 'contents/userdata/';

    protected $db;

    public function __construct()
    {

        $this->db = Zend_Registry::get('db');

    }

    public static function upload()
    {
        
    }

    public function extFile()
    {
        $db = Zend_Registry::get('db');

    }

    public function getImageById($imageId, $options = array())
    {


        $query = $this->db->select()->from('storage');

        $query->where('storage_id = ?', $imageId);

        $query->join('storage_group', 'storage_group.group_id = storage.group_id');

        $image = $this->db->fetchRow($query);

        $options['crop'] = intval($options['crop']);
        
        
        if ($options['width'] == '')
        {
            $options['width'] = 100;
        }
        
        
        if ($options['height'] == '')
        {
            $options['height'] = 80;
        }


        if (!$image)
        {
            return;
        }

        
        
        if ($image['soruce_folder'] != null)
        {
            $hidden_src = '/' . $image['soruce_folder'];
        }
        else
        {
            $hidden_src = '';
        }


       return 'contents/userdata/' . $image['folder'] . '/' . $options['width'] . '_' . $options['height'] . '_' .$options['crop'] . '/' . $image['storage_id'] . '.' . $image['file_ext'];
    }

    public function mime($ext)
    {
        
        $mimes = array();
      
        $mimes['jpg'] = 'image/jpeg';
        
        $mimes['jpeg'] = 'image/jpeg';
        
        $mimes['gif'] = 'image/gif';
        
        $mimes['png'] = 'image/png';
        
        return $mimes[$ext];

    }

    public function extByMime($mime)
    {
        $ext['image/png'] = 'png';
        
        $ext['image/jpeg'] = 'jpg';
        
        $ext['image/gif'] = 'gif';

        return $ext[$mime];

    }

}