;(function($){
    $.formSubmit = function(Form,callback,options){
    
        var defaults = {
            callback : function(){
                
            }
        }
        
        options = $.extend({}, defaults, options);
        
        
        
        
        $(uploadForm).live('submit',function(){
            $(this).ajaxSubmit({
                'type':'POST',
                'data':$(this).serialize(),
                'dataType' :'json',
                'success':function(response){ 
                 
                    options.callback
                 
                }
            });
            
        });
        
        
        


    }
},(jQuery));