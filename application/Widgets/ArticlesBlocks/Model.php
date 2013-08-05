<?php

class Widgets_ArticlesBlocks_Model extends Frontend_Model_Frontend
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

        $widget_params['category_id'] = implode(',', $widget_params['category_id']);

        $articlesQuery = $this->db->select()->from('items')
                ->join('items_lang', 'items.item_id = items_lang.item_id')
                ->join('items_categories', 'items_categories.cat_id = items.cat_id')
                ->join('items_categories_lang', 'items_categories_lang.cat_id = items.cat_id')
                ->where('items_lang.lang_id = ?', $this->lang_id)
                ->where('items_categories_lang.lang_id = ? ', $this->lang_id)
                ->where('items_categories.cat_id IN(?) ', $widget_params['category_id'])
                ->where('items_categories.cat_status = ?',1)
                ->limit($widget_params['num_rows'], $widget_params['rows_start_from']);

        switch ($widget_params['sort_by'])
        {
            case 'ASC':
            case 'DESC':
                $articlesQuery->order(array('items.item_id ' . $widget_params['sort_by']));
                break;
            case 'RAND':
                $articlesQuery->order(array('RAND()'));

                break;
            default:
                $articlesQuery->order(array('items.item_id  ASC'));
        }

        $articles = $this->db->fetchAll($articlesQuery);

        if (!$articles){
            return;
        }

        $counter = 1;
        $articlesNum = count($articles);
        foreach ($articles as $article)
        {
            $itemFieldsData = $this->db->fetachRow($this->db->select()->from('items_fields_data')->where('item_id = ?',$article['item_id']));
            $itemFieldsLangData = $this->db->fetachRow($this->db->select()->from('items_fields_lang_data')->where('item_id = ?',$article['item_id']))->where('lang_id = ?',$this->lang_id);
            print_r($itemFieldsData);
            print_r($itemFieldsLangData);


            die();
            $vars = array();
            $css_class = array();
            if (!empty($article['item_url'])){
                $item['url'] = '-' . $article['item_url'];
            }

            $article['url'] = MC_App_Items_Items::itemUrl($article);
            $article['item_title'] =
                    MC_Models_String::cut($article['item_title'], $widget_params['title']['length'], $widget_params['title']['cut_type'], $widget_params['title']['complete']);

            $article['c'] = $counter;

            $css_class[] = 'item_'.$counter;
            
            if($counter == 1){
                $css_class[] =  'first_item';
            }else if($counter == $articlesNum){
                $css_class[] =  'last_item';
            }
            if($counter % 2 == 0){
                $css_class[] = 'even';
            }else{
                $css_class[] = 'odd';
            }

            $article['css_class'] =   implode(' ',$css_class);
            $vars['item'] = $article;
            $vars['css_class'] = $this->plugin['css_class'];
            $template->prepareData($vars)->addChildTemplate($widget_params['inner_template']);
            $counter++;
        }


        $data['plugin']['css_class'] = $this->plugin['css_class'];
        $data['plugin']['title'] = $this->plugin['plugin_name'];

        $innerTemplate = $template->prepareData($data)->fetchTemplate($widget_params['inner_template']);

        $templateData['title'] = $this->plugin['widget_name'];
        
        $templateData['content'] = $innerTemplate;
        
        
        $templateData['css_class'] = $this->plugin['css_class'];
        
        $data['temp'] = $templateData;

        $outerTemplate = $template->prepareData($data)->fetchTemplate($widget_params['outer_template']);

        return $outerTemplate;
    }
    protected function generateImages(){
        
    }
}