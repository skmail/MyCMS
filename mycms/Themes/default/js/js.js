$(function(){

    $('#showLoginBox').click(function(){
        $_loginBox = $('#loginBox');

        if($_loginBox.is(':visible'))
        {

            $_loginBox.hide();
        }else
        {
            $_loginBox.show();
        }
    });

    $(document).click(function(){
        /*
        if($(this).attr('id') != 'loginBox'  && !$(this).parentsUntil($(this).closest('li')))
        {
            $('#loginBox').hide();
        }
        */
    });
});