$(document).ready(function(){


    $('#maillist').submit(function(ev){
        ev.preventDefault();
        $.ajax({
            type: "POST",
            data:$(this).serialize(),
            url: $(this).attr('action'),
            dataType: "json",
            success: function(data){

                if(data.error){
                    $('#maillist-results').removeClass('success').addClass('error').html(data.error);
                }else if(data.success){
                    $('#maillist-results').removeClass('error').addClass('success').html(data.success);
                }
            }

        });
    });


});