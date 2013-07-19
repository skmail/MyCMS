<?php

class Admin_View_Helper_AjaxSubmitForm {

    public function ajaxSubmitForm($params = array(), $url = '', $selector = '',$segmentReplaced = '') {
        return;
        //if(!empty($url))
        //    $url = $url."/";
        
        $outputs = "

          <script>
            $(function(){
                
                $('#" . $params['namespace'] ."  " . $selector . "').die().live('submit',function(e){
                     e.preventDefault();
                     
                     //$('.windowContent').append(loading);
                     
                     href = $(this).attr('action');
                     $.ajax({
                         type:'POST',
                         url :'" . $url . "'+href,
                         data:$(this).serialize(),
                         dataType :'json',
                         success:function(msg){";
                            
                         
                         
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

                            $('#" . $params['namespace'] . " .body').html(msg.window);
                                
                            ";
                            }
                            
                            $message = Admin_View_Helper_AppendMessage::appendMessage('','');
                             
                            $outputs.="
                          
                            
                            if(msg.app.message != null){
                            if($('#" . $params['namespace'] . " .body .messageBox').length > 0 ){
                               $('#" . $params['namespace'] . " .body .messageBox').remove(); 
                            }
                            
                            var messageText = msg.app.message.text;
                            var messageType = msg.app.message.type;
                            
                            $('#" . $params['namespace'] . " .body').prepend('".$message."');
                            
                            $('#" . $params['namespace'] . " .body .messageBox .box').addClass(messageType);
                            $('#" . $params['namespace'] . " .body .messageBox span.messageText').html(messageText);
                            }
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