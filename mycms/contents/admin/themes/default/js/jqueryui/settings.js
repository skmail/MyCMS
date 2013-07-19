
    
$(function(){
    
    tableSortable = function(){
        var sortHandle = 'sort-handle';
        var sortHandleClass = '.'+sortHandle;
        $("tbody.tableSortable").sortable({
            handle:sortHandleClass,
            axis:'y',
            helper: function(e, tr)
            {   
                
                var originals = tr.children();
                var helper = tr.clone();
                helper.children().each(function(index)
                {
                    $(this).width(originals.eq(index).width() )
                });
                return helper;
            },
            update:function(event,ui){
                
                var order = $(this).sortable('serialize'); 
                var href = $(this).attr('rel');
                
                 
                if(typeof href != 'undefined'){
                   
                    $.post(href,order,function(msg){
                        
                    
                    });
                   
                }

   
        
            }
        });
    }

    $( "tbody.tableSortable" ).disableSelection();
    
    



    refreshjQueryUI();    
});


function refreshjQueryUI(){
    // $("tbody.tableSortable").doSort();

    tableSortable();
}
    