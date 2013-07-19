$(function(){
    
    
    
    $('.ajaxOpen ,.breadcrumb a, .main-content a , .main-content button  , #popupContent button , .disAjax,#goToApplication').live('click',function(){

        if($(this).attr('id') == 'goToApplication')
        {
            href = $(this).val();
        }else
        {
            href = $(this).attr('href');
        }


        var enableState = true;

        if(typeof href === 'undefined'){
            return false;
        }

        if($(this).hasClass('disAjax') && $(this).hasClass('noRedirect')){
            return false;
        }

        if($(this).hasClass('under-cons')){
            loading('This Component Under Construction',true);
            return false;
        }




        if($(this).hasClass('disState'))
            var enableState = false;
	 
        if($(this).hasClass('disAjax')){
            if(typeof $(this).attr('target') === 'undefined')
                target = ' _self';
            else
                target = $(this).attr('target');

            window.open(href,target);
            return false;
        }



        if($(this).attr('data-modal-type') != 'undefined')
        {

            modalType = $(this).attr('data-modal-type');
            modalMessage = $(this).attr('data-modal-msg');

            switch(modalType)
            {

                case 'confirm':
                    bootbox.confirm(modalMessage,function(result){

                        if(result == true)
                        {
                            sendAjaxRequest(href,enableState);
                        }
                    });

                    break
                default:
                    sendAjaxRequest(href,enableState);
                    break;
            }

        }
        el = $(this);
        
        if(href == '#' || href.length == 0 )
        {
            return false;
        }
        
        

    });
});

function sendAjaxRequest(href,enableState)
{
    $.ajax({
        'type':'post',
        'url':href,
        cache:false,
        beforeSend: function(xhr){
            xhr.setRequestHeader('X-PJAX', 'true');
            loading('Loading Content');
        },
        'success':function(response){
            try {
                dataJSON = $.parseJSON(response);

                if(el.hasClass('openPopup')){
                    if(dataJSON.window.length > 0)
                        pageTitle = (typeof dataJSON.app.pageTitle === "undefined") ? "Popup" : dataJSON.app.pageTitle;
                    appendPopup(dataJSON.window,pageTitle);
                    $('.loadingOverlay').remove();
                }else if(el.closest('#popupContent').length > 0){
                    alert('s');
                }else{

                    printPage(dataJSON);

                    if(typeof dataJSON.app.message != "undefined" ){
                        messageType = (typeof dataJSON.app.message.type === "undefined") ? false : dataJSON.app.message.type;

                        if(messageType == 'error')
                        {
                            enableState = false;
                        }
                    }

                    if(typeof dataJSON.app.refresh != "undefined" ){

                    }
                }
            } catch (e) {

            }

            deleteLoading()

            removeEditor();

            createEditor();

            refreshjQueryUI();

            if(enableState)
                history.pushState(null,"Welcome",href);


        }
    });
}