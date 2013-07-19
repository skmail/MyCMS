<?php

class Admin_View_Helper_AjaxOpenWindow {

    public function ajaxOpenWindow($params = array(), $url = '', $selector = '',$segmentReplaced = '') {
        return;
        if(!is_array($params))
            return;
        if(sizeof($params) == 0)
            return;
        if(!empty($url))
            $url = $url."/";
        
        if(is_array($selector)){
            $selector = implode (',#'.$params['namespace']. ' ' ,$selector);
            $selector =  "#".$params['namespace']. ' ' . $selector    ;  

            }else{
            $selector = "#".$params['namespace']. ' ' . $selector;
            }

        $outputs = "

          <script>
            $(function(){
                
                $( '". $selector . "').die().live('click',function(){
                    
                    el = $(this);
                    href = el.attr('href');
                     if(href == '#')
                        return;
                     $.ajax({
                         type:'POST',
                         url :'" . $url . "'+href,
                         data:{prevWindow:'".$params['windowUri']."'},
                         dataType :'json',
                         success:function(msg){
                            if(msg.app == null)
                                return;
                            if(msg.app.renderWindow == false)
                                return;
                                
                            if(msg.app.js != null){
                                msg.app.js;
                            }
                            ";
                           
                        if($segmentReplaced == '.sidebar' || $segmentReplaced == '' ){
                            $outputs.="
                            if(msg.sidebar != false){
                                if($('#" . $params['namespace'] . " .windowContent .sidebar').length > 0)
                                    $('#" . $params['namespace'] . " .windowContent .sidebar').remove();
                                $('#" . $params['namespace'] . "').addClass('hasSidebar');
                                $('#" . $params['namespace'] . "').addClass('windowBodyInsideSidebar');
                                $('#" . $params['namespace'] . " .windowContent').prepend('<div class=\"sidebar\"></div>');
                                $('#" . $params['namespace'] . " .sidebar').html(msg.sidebar);
                                
                            }else{
                                $('#" . $params['namespace'] . "').removeClass('hasSidebar');

                                $('#" . $params['namespace'] . "').removeClass('windowBodyInsideSidebar');

                                $('#" . $params['namespace'] . " .windowContent .sidebar').remove();
                            }
                            ";
                        }
                        if($segmentReplaced == '.body' || $segmentReplaced == '' ){
                            $outputs.="
                            $('#" . $params['namespace'] . " .toolbar a.prevWindow').attr('href','".$params['windowUri']."');
                                            
                            $('#" . $params['namespace'] . " .toolbar a.prevWindow').removeClass('toolbarButtonDisabled');

                            $('#" . $params['namespace'] . " .body').html(msg.window);
                                
                            ";
                        }
                        $outputs.= "
                            
                                if(el.closest('.menubar').length != 0){
                                    
                                    $('#".$params['namespace']." .breadcrumb ul li:first-child').nextAll('li').remove();

                                }
                                else
                                    $('#".$params['namespace']." .breadcrumb ul li.'+msg.app.breadcrumbClass).nextAll('li').remove();

                                if(msg.app.windowName != '".$params['windowName']."'){
                                    

                                    if(!$('#".$params['namespace']." .breadcrumb ul li ').hasClass(msg.app.breadcrumbClass) &&  msg.app.breadcrumbClass != undefined && msg.app.breadcrumb != false){
                                        
                                        var breadcrumbEl = '<li class=\"sep\">></li>' 
                                        var breadcrumbEl = breadcrumbEl + '<li class=\"active '+ msg.app.breadcrumbClass +'\"><a href=\"'+msg.app.windowUri+'\">'+msg.app.windowName+'</a></li>'
                                        $('#" . $params['namespace'] . " .breadcrumb ul').append(breadcrumbEl);    
                                    }
                                    
                                    if(msg.app.breadcrumbClass == '".$params['breadcrumbClass']."'){
                                        
                                        $('#" . $params['namespace'] . " .breadcrumb ul li.'+msg.app.breadcrumbClass + ' a').html(msg.app.windowName); 
                                        $('#" . $params['namespace'] . " .breadcrumb ul li.'+msg.app.breadcrumbClass + ' a').attr('href',msg.app.windowUri);
                        
                                    }
                                }
                                
                                        $('#" . $params['namespace'] . " .toolbar a.refresh ').attr('href',msg.app.windowUri);
                                
                         ";
                            $outputs.="
                         }
                     });

                 });

            });

          </script>
          ";


        return $outputs;
    }

}