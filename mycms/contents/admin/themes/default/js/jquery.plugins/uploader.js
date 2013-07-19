;
(function($){
    
    /*
     * Options:
     *    form
     *    button: 
     *    dataOutput : table selector
     *    
     */
    
    $.UploaderByLink = function(options){
       
        var defaults = {
            
            'uploadsResult':'.qq-upload-list',
            'template':''
        }

        options = $.extend(defaults,options);
        
        var uploadForm = options.uploadForm;
        
        $(uploadForm).live('submit',function(){
            
           /// template = $(options.template).remove('span.qq-upload-status-text');
            template = $(options.template);

            template.find('.qq-upload-status-text').html('Uploading');
            template.find('.qq-upload-cancel').hide();

         //   template = $(options.template).remove('.qq-progress-spinner');
         //   template = $(options.template).remove('.qq-progress-cancel');
         //   template = $(options.template).remove('.qq-progress-status-text');


            $(options.uploadsResult).append(template);

            $(uploadForm).ajaxSubmit({
                'type':'POST',
                'data':$(uploadForm).serialize(),
                'dataType' :'json',
                'success':function(response){

                    if(response.success == true)
                    {
                        template.find('.qq-upload-fileid').append('<input type="checkbox" name="fileid" value="'+response.results.storage_id+'">');
                        template.find('.qq-upload-fileid').append('<input type="hidden" name="destCrop" value="'+response.results.destCrop+'">');
                        template.find('.qq-upload-fileid').append('<input type="hidden" name="ext" value="'+response.results.ext+'">');
                        template.addClass('qq-upload-success');
                        template.find('.qq-progress-bar').hide();
                        template.find('.qq-upload-spinner').hide();
                        template.find('.qq-upload-finished').hide();
                        template.find('.qq-upload-cancel').hide();
                        template.find('.qq-upload-retry').hide();
                        template.find('.qq-upload-status-text').empty();
                        template.find('.qq-upload-file').html(response.results.name);
                        template.find('.qq-upload-size').html(response.results.size);
                    }

                    console.log(response);
                  ///console.log(template);

                //function(){
                //    $(this).find('.closeThis').hide().delay(5000).fadeIn();
                //});
                                
                }
            });   
               
                
        });
        
        
        $('.popupContent #myTab li a[data-toggle=tab]').live('click',function(){
            
            var resultsContainer = $('#uploadResults');
            var resultsTable = resultsContainer.find('table#resultsTable');
            var resultsTableRowsBody = resultsTable.find('tbody#FilesUploadedResultsRows'); 
            var resultsRows =  resultsTableRowsBody.find('tr');

            if($(this).attr('href') == '#upload_from_url' || $(this).attr('href') == '#upload_from_computer'){
                resultsContainer.show();
            }else{
                resultsContainer.hide();
            }
           
        });
        
        
    }
})(jQuery);