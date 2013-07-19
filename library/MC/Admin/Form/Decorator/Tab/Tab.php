<?php

class MC_Admin_Form_Decorator_Tab_Tab extends Zend_Form_Decorator_Abstract {

    public function render($content) {

        $placement = $this->getPlacement();


        $tabNav = $this->getOption('nav');

        $id = $this->getOption('id');
        $content = "<div class='tab-content'>".$content."</div>";
        
        $tabNavContent = "<ul id='myTab' class='nav nav-tabs'>";
        
        if(is_array($tabNav))
        {

            foreach($tabNav as $k=>$v){
                $tabNavContent.="<li class='".$v['active']."'>
                                    <a href='#".$v['href']."' class='disAjax noRedirect ' data-toggle='tab'>".
                                        $v['label']."
                                    </a>
                                </li>";
            }
        }

        $tabNavContent.="</ul>";
        
        switch ($placement) {
            case 'PREPEND':
                $finalContent =  $tabNavContent.$content;
            break;
            case 'APPEND':
            default:
                $finalContent =  $content.$tabNavContent;
            break;
        }
        
        if(!empty($id))
        {
            $finalContent = "<div id ='".$id."'>" . $finalContent . "</div>";
        }else
        {
            $finalContent = "<div>".$finalContent."</div>";
        }
        
        
        
        
        return  $finalContent;
    }

}