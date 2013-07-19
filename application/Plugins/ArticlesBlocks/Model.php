<?php

class Plugins_ArticlesBlocks_Model extends Frontend_Model_Frontend
{

    public function __construct($pluginResource)
    {
        parent::__construct();

        $this->plugin = $pluginResource;

    }

    public function init()
    {
        extract($this->plugin);

        
        $this->plugin['css_class'][] =  'plugin_' . $this->plugin['plugin_id'];
        $this->plugin['css_class'] = implode(' ', $this->plugin['css_class']);
        
        
        
        $template = new Frontend_Model_Templates_Template();

        $plugin_params['category_id'] = implode(',', $plugin_params['category_id']);


        $articlesQuery = $this->db->select()->from('items')
                ->join('items_lang', 'items.item_id = items_lang.item_id')
                ->join('items_categories', 'items_categories.cat_id = items.cat_id')
                ->join('items_categories_lang', 'items_categories_lang.cat_id = items.cat_id')
                ->where('items_lang.lang_id = ?', $this->lang_id)
                ->where('items_categories_lang.lang_id = ? ', $this->lang_id)
                ->where('items_categories.cat_id IN(?) ', $plugin_params['category_id'])
                ->where('items_categories.cat_status = ?',1)
                ->limit($plugin_params['num_rows'], $plugin_params['rows_start_from']);


        switch ($plugin_params['sort_by'])
        {
            case 'ASC':
            case 'DESC':
                $articlesQuery->order(array('items.item_id ' . $plugin_params['sort_by']));
                break;
            case 'RAND':
                $articlesQuery->order(array('RAND()'));

                break;
            default:
                $articlesQuery->order(array('items.item_id  ASC'));
        }

        if (!empty($plugin_params['hasImage']))
        {
            $articlesQuery->where('items.images IS NOT NULL and items.images != ""');
        }

        $articles = $this->db->fetchAll($articlesQuery);

        if (!$articles)
        {
            return;
        }

        $counter = 1;
        
        $articlesNum = count($articles);
        
        foreach ($articles as $article)
        {
         
            $vars = array();
            
            $css_class = array();
            
            if (!empty($article['item_url']))
            {
                $item['url'] = '-' . $article['item_url'];
            }

            $article['url'] = MC_App_Items_Items::itemUrl($article);

            $article['item_title'] =
                    MC_Models_String::cut($article['item_title'], $plugin_params['title']['length'], $plugin_params['title']['cut_type'], $plugin_params['title']['complete']);
            $article['item_content'] =
                    MC_Models_String::cut($article['item_content'], $plugin_params['content']['length'], $plugin_params['content']['cut_type'], $plugin_params['content']['complete']);


            
            if ($article['images'] != NULL)
            {
                $images = explode(',', $article['images']);
                $imagesArray = array();

                if (count($images) > 0)
                {

                    $imagesApp = new MC_App_Storage_Storage();

                    foreach ($images as $k => $image)
                    {

                        if ($k == 0)
                        {
                            $firstImage = $image;
                        }
                        $imagesArray[] = $imagesApp->getImageById($image, array(
                            'width'  => $plugin_params['image']['width'],
                            'height' => $plugin_params['image']['height'],
                            'crop'   => $plugin_params['image']['crop']));
                    }

                    $article['image_url'] = $imagesArray[0];
                    
                    if ($plugin_params['customSize']['showType'] != 'none')
                    {
                        $imagesNumbers = explode(',', $plugin_params['customSize']['noOfImage']);

                        switch ($plugin_params['customSize']['showType'])
                        {
                            case 'imageNumber':

                                if (in_array($counter, $imagesNumbers))
                                {
                                    $article['image_url'] = $imagesApp->getImageById($firstImage, array(
                                        'width'  => $plugin_params['customSize']['width'],
                                        'height' => $plugin_params['customSize']['height']));
                                    $article['image'] = ($article['image_url'] != '') ? '<img src="' . $article['image_url'] . '"/>' : '';
                        
                                }

                                break;
                                ;
                            case 'everyImage':
                                foreach ($imagesNumbers as $imgNo)
                                {

                                    if ($counter % $imgNo == 0)
                                    {
                                        $article['image_url'] = $imagesApp->getImageById($firstImage, array(
                                            'width'  => $plugin_params['customSize']['width'],
                                            'height' => $plugin_params['customSize']['height']));
                                        $article['image'] = ($article['image_url'] != '') ? '<img src="' . $article['image_url'] . '"/>' : '';
                        
                                    }
                                }
                                break;
                        }
                        
                        
                    }

                    if($plugin_params['thumb']['use'] == 1)
                    {
                        if(($plugin_params['thumb']['width'] != 0 && !empty($plugin_params['thumb']['width']))
                            &&
                           ($plugin_params['thumb']['height'] != 0 && !empty($plugin_params['thumb']['height'])))
                        {

                            $article['thumb_url'] = $imagesApp->getImageById($image, 
                                    array(
                                     'width'  => $plugin_params['thumb']['width'],
                                     'height' => $plugin_params['thumb']['height']
                            ));

                            $article['thumb'] = "<img src='".$article['thumb_url']."'/>";

                        }
                    }
                    
                    if($plugin_params['image']['use'] == 1)
                    { 
                        $article['image'] = ($article['image_url'] != '') ? '<img src="' . $article['image_url'] . '"/>' : '';
                    }
                    
                    $article['images'] = $imagesArray;
                }
            }

            $article['c'] = $counter;
            
            
            $css_class[] = 'item_'.$counter;
            
            if($counter == 1)
                $css_class[] =  'first_item';
            else if($counter == $articlesNum)
                $css_class[] =  'last_item';
            if($counter % 2 == 0)
                $css_class[] = 'even';
            else
                $css_class[] = 'odd';
            $article['css_class'] =   implode(' ',$css_class);
            
            $vars['item'] = $article;
            
            $vars['css_class'] = $this->plugin['css_class'];
            
            $template->prepareData($vars)->addChildTemplate($plugin_params['inner_template']);
           
            $counter++;
        }
        
        $data['plugin']['css_class'] = $this->plugin['css_class'];
        $data['plugin']['title'] = $this->plugin['plugin_name'];
        
        
        $innerTemplate = $template->prepareData($data)->fetchTemplate($plugin_params['inner_template']);

        $templateData['title'] = $this->plugin['plugin_name'];
        
        $templateData['content'] = $innerTemplate;
        
        
        $templateData['css_class'] = $this->plugin['css_class'];
        
        $data['temp'] = $templateData;
        
        $outerTemplate = $template->prepareData($data)->fetchTemplate($plugin_params['outer_template']);

        return $outerTemplate;

    }


    
    
    protected function generateImages(){
        
    }
}