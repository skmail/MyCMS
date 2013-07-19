<?php

class MC_App_Storage_ResizeImage extends Frontend_Model_Frontend
{

    protected $options = array();

    protected $_errors = array();

    protected $maxWidth = 1000;

    protected $maxHieght = 1000;

    protected $imageQuality = 100;

    protected $zoom = 2;

    protected $userdataDir = 'contents/userdata/';

    public function __construct($options)
    {

        parent::__construct();

        $this->imagesDir = $this->_Zend->getRequest()->getParam('folder');
        $db = Zend_Registry::get('db');
        $folder_query = $db->select()->from('storage_group')->where('folder = ?',$this->imagesDir);
        $folderRow = $db->fetchRow($folder_query);
        if($folderRow['source_folder'] != "")
        {
            $sourceFolder = $folderRow['source_folder'] . '/';
        }
        else
        {
            $sourceFolder = '';
        }
        
        $this->dir = $this->userdataDir . $this->imagesDir . '/'.$sourceFolder;
        $this->options = $options;
        $this->_getImageSize($options['crop']);


        $resizerInit = new App_Storage_Shared_Adapter_ImageWorkshop_ImageWorkshop();
        $layer1 = $resizerInit->initFromPath($this->dir.$options['image']);

        if($this->options['crop'] == 1)
        {
            $layer1->cropInPixel($this->options['width'],$this->options['height']);
        }
        else
        {
            $layer1->resizeInPixel($this->options['width'],$this->options['height']);
        }

        //$norwayLayer = App_Storage_Shared_Adapter_ImageWorkshop_ImageWorkshop::initFromPath('contents/admin/themes/default//_layout/images/logo.png');
        //$norwayLayer->resizeInPixel(($this->options['width']/2));
        //$layer1->addLayerOnTop($norwayLayer, 5, 5, "RB");

        $image =   $layer1->getResult();
        $saveDir = $this->userdataDir . $this->imagesDir . '/' . $this->options['cropFolder'] . '/';
        $layer1->save($saveDir, $options['image'], true,  null, 100);

        header('Content-type: image/png');
        imagepng($image); // We choose to show a GIF
        exit;

        //$this->imageExists($options['image']);
        //$this->_resize();

    }

    protected function _resize()
    {

        $this->_pushErrors();

        $image = $this->_createImage();

        $orginalAspect = $this->options['orginalWidth'] / $this->options['orginalHeight'];

        $thumbAspect = $this->options['width'] / $this->options['height'];


        if ($orginalAspect >= $thumbAspect)
        {
            $height = $this->options['height'];
            $width = $this->options['orginalWidth'] / ($this->options['orginalHeight'] / $this->options['height']);
        }
        else
        {
            $width = $this->options['width'];
            $height = $this->options['orginalHeight'] / ($this->options['orginalWidth'] / $this->options['width']);
        }

        $width = floor($width);

        $height = floor($height);


        $thumbImage = imagecreatetruecolor($this->options['width'], $this->options['height']);

        $dimY = 0;
        $dimX = 0;


        imagecopyresized($thumbImage,
                         $image,
                         $dimX,
                         $dimY,
                         0,
                         0,
                         $width,
                         $height,
                         $this->options['orginalWidth'],
                         $this->options['orginalHeight']
        );

        $dir = $this->userdataDir . $this->imagesDir . '/' . $this->options['cropFolder'] . '/';

        if (!is_dir($dir))
        {
            mkdir($dir);
        }

        header('Content-Type: image/jpeg');

        imagejpeg($thumbImage, $dir . $this->options['image'], $this->imageQuality);
        
        imagejpeg($thumbImage, null, $this->imageQuality);

        imagedestroy($thumbImage);

    }

    protected function _clearImageId($imageId)
    {
        
    }

    protected function _getImageSize($imageSizes)
    {

        if ($imageSizes != "")
        {

            $options = array();

            $imageCropType = explode('_', $imageSizes);



            $this->options['width'] = intval($imageCropType[0]);

            $this->options['height'] = intval($imageCropType[1]);


            if ($imageCropType[2] == 0)
            {
                $this->options['crop'] = 0;

                $this->options['cropFolder'] = $this->options['width'] . '_' . $this->options['height'].'_0';
            }
            elseif ($imageCropType[2] == 1)
            {
                $this->options['crop'] = 1;

                
                $this->options['cropFolder'] = $this->options['width'] . '_' . $this->options['height'].'_1';
            }
            else
            {
                $this->_errors[] = 'Undefined Sizes';
            }

            if ($this->options['width'] > $this->maxWidth)
            {
                $this->_errors[] = 'Max Width for image is ' . $this->maxWidth;
            }

            if ($this->options['height'] > $this->maxHieght)
            {
                $this->_errors[] = 'Max height for image is ' . $this->maxHieght;
            }
        } else
        {
            $this->_errors[] = 'Undefined Sizes';
        }

    }

    protected function _pushErrors()
    {
        if (count($this->_errors) > 0)
        {
            echo "<p>Some erros Occured</p>";
            
            echo "<ul>";
          
            foreach ($this->_errors as $error)
            {
                echo "<li>" . $error . "</li>";
            }
            
            echo "</ul>";
            
            exit();
        }

    }

    protected function imageExists($image)
    {

        $image = $this->dir . $image;

        $this->options['image_url'] = $image;
        
        if (file_exists($image))
        {
            $this->options['image_url'] = $image;
        }
        else
        {
            $this->_errors[] = 'image Not Found';
        }

    }

    protected function imageDecode($image)
    {
        
    }

    protected function imageEncode($image)
    {
        
    }

    protected function _createImage()
    {

        $imageUrl = $this->options['image_url'];

        $imageInfo = getimagesize($imageUrl);

        $this->options['orginalWidth'] = floor($imageInfo[0]);
       
        $this->options['orginalHeight'] = floor($imageInfo[1]);



        switch ($imageInfo['mime'])
        {
            case "image/gif":
                $image = imagecreatefromgif($imageUrl);
                break;
        
            case "image/jpeg":
                $image = imagecreatefromjpeg($imageUrl);
                break;
            
            case "image/png":
                $image = imagecreatefrompng($imageUrl);
                break;
            
            case "image/bmp":
                $image = imagecreatefromwbmp($imageUrl);
                break;
        }

        return $image;

    }

    protected function saveImage($images)
    {
        
    }

}