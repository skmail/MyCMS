$(function(){
    $('input[type=submit]').live('click',function(){
        $(this).attr('clicked',true);
    });
    $('.main-content form , #popupContent form').live('submit',function(){

        form = $(this);

        $_clickedButton = $(this).find('input[type=submit][clicked=true]');

        $_clickedButton.removeAttr('clicked');

        if(form.hasClass('disSubmit')){
            return false;
        }

        modalType = $_clickedButton.attr('data-modal-type');
        modalMessage = $_clickedButton.attr('data-modal-msg');

        switch(modalType)
        {

            case 'confirm':
                bootbox.confirm(modalMessage,function(result){

                    if(result == true)
                    {
                        submitForm(form);
                    }
                });

                break
            default:
                submitForm(form);
                break;
        }


    });
});


function submitForm(form)
{
    loading('Saving...');
    form.ajaxSubmit({
        'type':'POST',
        'enctype':'multipart/form-data',
        'dataType' :'json',
        'success':function(response){

            if(form.closest('#popupContent').length > 0 ){
                //ToDo popup
            }else{
                deleteLoading();
                printPage(response);
                if('undefined' !== typeof response.replaceUrl){
                    history.pushState(null,"",response.replaceUrl);
                }
            }
            removeEditor();
            createEditor();
            refreshjQueryUI();
        }
    });
}