<?php

class Plugins_Menu_Decorator_Menu_Menu extends Zend_Form_Decorator_Abstract
{

    public function render($content)
    {

        $app = $this->getOption('app');

        $application = new MC_Models_AppSegment();

        $fn_class = MC_Core_Loader::appClass('plugins','Functions',$application,'Admin');

        $fn = $fn_class->appsList();

        $div = "<div id='MenuListElements' class='span6 box'>";
        $div.= "<div class='box_head'>Available items</div>";
        $div.= "<div class='box_content'>";
        foreach($fn['applicationsList'] as $app_id=>$apps)
        {
            $div.="<ul>";
            $div.="<li class='app_na'>".$fn['applicationsName'][$app_id]."</li>";
            foreach($apps as $app)
            {
                $div.='<li item-type="item" app-id="'.$app_id.'" id="'.$app['id'].'">'.$app['label'].'</li>';
            }
            $div.="</ul>";
        }
        $div.="</div></div>";

        $content = "<div class='span6 box'>";

        $content.= "<div class='box_head'>Menu structure</div>";

        $content.= "<div class='box_content'>";

        $content.= "<ul id='addesItems'>";

        if (isset($app['params']['el']) && is_array($app['params']['el']))
        {
            foreach ($app['params']['el'] as $k => $v)
            {
                $content.='<li>
                            <a href="#" class="closeMenuEl">x</a>
                            ' . $application->getSegment($v['appid'], $v['id']) . '
                            <input type="hidden" value="' . $v['id'] . '" name="params[el][' . $k . '][id]">
                            <input type="hidden" value="' . $v['appid'] . '" name="params[el][' . $k . '][appid]">
                            <a class="openPopup disState" href="' . $app['url'] . 'window/appsList/do/appendChild/el/' . $k . '">Add child</a>
                            <a class="openPopup disState getIndexMenu" href="#"><i>getIndex</i></a>
                            </li>';
            }
        }
        $content.="</ul></div></div>";
        $mainContent = "<div class='clear'></div><div class='row-fluid'>";
        $mainContent.=$div. $content;
        $mainContent.="</div>";

        return $tabNavContent . $mainContent;

    }

}