$(function(){
    $('.main-content form , #popupContent form').live('submit',function(){
        form = $(this);
        if($(form).hasClass('disSubmit')){
            return false;
        }
        loading('Saving...');
        var _data = $(this).serialize();
        form.ajaxSubmit({
            'type':'POST',
            'enctype':'multipart/form-data',
            //'data':_data,
            'dataType' :'json',
            'success':function(response){
                
                if(form.closest('#popupContent').length > 0 ){
                 //ToDo popup
                }else{
                    deleteLoading();
                    printPage(response);
                    if(typeof response.app.replaceUrl != 'undefined'){
                        history.pushState(null,"",response.app.replaceUrl);
                    }
                }
                removeEditor();
                createEditor();
                refreshjQueryUI();
            }
        });
    });
    


    $('#saveGrid #theme_id').live('change',function(){

        $.ajax({
            'url':'admin/ajax/window/appPrefix/plugins/window/_templateGroupCategoriesList',
            'type':'POST',
            'dataType':'json',
            'data':'themeid='+$(this).val(),
            'success':function(data)
            {

                $_selectMenu = $('#saveGrid').find('#params-group_outer_templates_category');

                $_selectMenu.find('option').remove();

                $.each(data.app.categories,function(key,value){
                    $_selectMenu.append('<option value="'+value.cat_id+'">'+value.cat_name+'</option>');
                });

            }
        });


    });
                            
                             
});