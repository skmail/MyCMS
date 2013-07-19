<?php
class App_Items_Shared_FieldsTypes_Images_View_Images extends  Zend_View_Helper_FormElement
{
    public function Images($name,$values = '',$attr = array())
    {
        /*
        $buttons->addElement('button', 'uploadFile',
            array('label'   => 'Upload File',
                'class'   => 'btn openPopup disState',
                'href'    => Admin_Model_System_Application::appUrl('storage') .
                    '/window/file/do/add/include-tabs/library|x/include-buttons/addToPost',
                'decorators' => array(
                    'ViewHelper',
                    array('HtmlTag', array('tag' => 'label')
                    )
                )
            )
        );

        if(!is_array($value))
        {
            return;
        }*/

        $img = '';

        if ($attr['data']['images'] != "")
        {
            if (is_array($attr['data']['images']))
            {
                if (count($attr['data']['images']) > 0)
                {
                    $storageModel = new MC_App_Storage_Storage();
                    foreach ($attr['data']['images'] as $image_id)
                    {
                        $imageUrl = $storageModel->getImageById($image_id, array('width'  => 135, 'height' => 135));
                        $img  .= '<div class="image"><img src="'.$imageUrl.'"  height="150"/>
                                  <div class="imageControlls">
                                  <a href="#" class="deleteImage">x</a></div><input type="hidden" name="images[]" value="'.$image_id.'"></div>';
                    }
                }
            }
        }




        return "<div class='imagesContainer' id='postImagesContainer'>".$img."</div>";
    }
}