<?php

class Admin_View_Helper_AppendMessage {

    public function AppendMessage($message = '',$messageType = '' , $js = false,$heading = '') {
        
       if($js == true){
           return $this->jsAppendMessage();
           return;
       }
       $messageType = ($messageType == 'success')?'accept':$messageType;
       
       return
        '<div class="messages-container"><div class=" message-popup popup-'.$messageType.'"><span><strong>'.$heading.'</strong>'.$message.'</span></div></div>';
    }
    
    
    private function jsAppendMessage(){
        
        
        $output = "<script>";
            
            $output.="function appendMessage(message,messageType,messageHeading){";
                $output.="if(messageHeading == false){ messageHeading = '';}";
                $output.="return '";
                $output.= $this->AppendMessage("'+message+'","'+messageType+'",false,"'+messageHeading+'");
                $output.="';";
            $output.="}";
        
        $output.="</script>";
        return $output;
    }
    
    
}