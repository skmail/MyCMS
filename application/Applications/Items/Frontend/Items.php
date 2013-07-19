<?php

class App_Items_Frontend_Items extends Frontend_Model_Applications_Application {

    private $_category = '';
    private $_item  = '';


    public $data = array();

    public function init($appRow){

        $this->data = array_merge($this->data,$appRow);
        $request = $this->_Zend->getRequest();
        $this->_category = $request->getParam('category');



        $this->_item = $request->getParam('item');
        return $this->_router();
    }




    private function category(){

        $query = $this->categoryQuery();
        $view = new Zend_View();

        $view->getHelper('headTitle')->prepend($this->data['cat_name']);

        $pluignVars = array();
        $pluginVars['plugin_name'] = $this->data['cat_name'];
        $pluginVars['plugin_params'] = $this->data['cat_params'];

        $pluginVars['plugin_params']['category_id'][] = $this->data['cat_id'];

        $content  = new Frontend_Model_Plugins_articlesBlocks_articlesBlocks($pluginVars);

        $this->data['content'] = $content->init();
        return $query;

    }


    public function item(){

       // $this->_Zend->layout()->headTitle()->prepend('Control Application -');

        $view = new Zend_View();

        $item = $this->itemQuery();

        $template = new Frontend_Model_Templates_Template();

            if ($this->data['images'] != NULL) {

                $images = explode(',', $this->data['images']);
                $imagesArray = array();
                if (count($images) > 0) {

                    $imagesApp = new MC_App_Storage_Storage();

                    foreach ($images as $k => $image) {

                        if ($k == 0)
                            $firstImage = $image;

                        $imagesArray[] = $imagesApp->getImageById($image, array(
                            'width' => $this->data['cat_params']['postImage']['width'],
                            'height' =>$this->data['cat_params']['postImage']['height']
                            ));
                    }
                    $this->data['image_url'] = $imagesArray[0];
                }

                $this->data['image'] = ($this->data['image_url'] != '') ? '<img src="' . $this->data['image_url'] . '"/>' : '';

                $this->data['images'] = $imagesArray;

            }


        $vars['item'] = $this->data;

        $this->data['content'] =
                $template->fetchTemplate($this->data['cat_params']['post_content'], $vars, 'item_content_show', true);

        $view->getHelper('headTitle')->prepend($this->data['item_title']);

        return $item;
    }


    private function _router(){

        if($this->_category == '' && ($this->_item == '' && $this->_item == 0))
        {
            return false;
        }

        if($this->_category != '' && ($this->_item == '' && $this->_item == 0)){
            return $this->item();
        }else{
           return  $this->category();
        }

    }


    private function categoryQuery($cat_id =  0){

        $query = $this->db->select()->from('items_categories')
                           ->join('items_categories_lang','items_categories.cat_id = items_categories_lang.cat_id')
                           ->where('cat_url = ?',$this->_category);

        if($cat_id != 0 )
            $query->where('cat_id = ? ',$cat_id);


        $data =  $this->db->fetchRow($query);

        if(!$data) return false;

        $data['category_id'] = $data['cat_id'];
        $data['cat_params'] = json_decode($data['cat_params'],true);
        $this->data = array_merge($this->data,$data);

        return true;
    }


    private function itemQuery($itemId =  0){

        $itemId = intval($itemId);

        if($itemId == 0 )
            $itemId = $this->_item;

        $query = $this->db->select()->from('items')
                           ->join('items_lang','items.item_id = items_lang.item_id')
                           ->join('items_categories','items.cat_id = items_categories.cat_id AND cat_url = "' . $this->_category . '"')
                           ->where('items.item_id = ?',$this->_item);



        $data =  $this->db->fetchRow($query);

        if(!$data) return false;

        $data['category_id'] = $data['cat_id'];

        $data['cat_params'] = json_decode($data['cat_params'],true);



        $this->data = array_merge($this->data,$data);

        return true;
    }


}