<?php

class Admin_View_Helper_PostImage extends Zend_View_Helper_FormElement {

    public function postImage($name, $value = '', $attribs = '') {

        if(!is_array($value))
        {
            return;
        }
        
        $image['id'] = $value['image_id'];
        $image['url'] = $value['url'];
        
        $img  = '<img src="'.$image['url'].'"  height="150"/>
                    <div class="imageControlls">
                    <a href="#" class="deleteImage">x</a></div><input type="hidden" name="images[]" value="'.$image['id'].'">';
        
        return $img;
        
    }

}