$(function(){
    $('.ajaxOpen , .window a , .window button').live('click',function(){
      
        href = $(this).attr('href');
      
      
        if(href == '#' || href.length == 0 )
            return false;
        
        $.ajax({
            'type':'post',
            'url':href,
            'success':function(response){
                setHash(href);
                try {
                    dataJSON = $.parseJSON(response);
                    if(dataJSON.window.length > 0)
                        $('#mainContent').find('.window .body').html(dataJSON.window);
                    if(dataJSON.window.length > 0)
                        $('#mainContent').find('.window .sidebar').show().html(dataJSON.sidebar);
                    else
                        $('#mainContent').find('.window .sidebar').hide().empty();
           } catch (e) {
                    $('#mainContent').html(response);
                }


            }
        });
    });
});