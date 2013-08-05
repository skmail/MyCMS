$(document).ready(function() {
    var errorHandler = function(event, id, fileName, reason) {
        qq.log("id: " + id + ", fileName: " + fileName + ", reason: " + reason);
    };
    var fileNum = 0;

    uploaderTemplate = '<li>' +
    '<div class="qq-progress-bar"></div>' +
    '<span class="qq-upload-spinner"></span>' +
    '<span class="qq-upload-finished"></span>' +
    '<span class="qq-upload-fileid"></span>' +
    '<span class="qq-upload-file"></span>' +
    '<span class="qq-upload-size"></span>' +
    '<a class="qq-upload-cancel" href="#">{cancelButtonText}</a>' +
    '<a class="qq-upload-retry" href="#">{retryButtonText}</a>' +
    '<span class="qq-upload-status-text">{statusText}</span>' +
    '</li>';
                
    $('#uploadWithVariousOptionsExample').fineUploader({
        
        
        fileTemplate:uploaderTemplate,
        button: $("#fubUploadButton"),
        request: {
            endpoint: "/admin/storage/window/saveFile/uploadType/byForm",
            params:{
                'group_id':1 //$('#storage_group_id').val()
            }
        },
        text: {
            uploadButton: "Click Or Drop"
        },
        dragAndDrop:{
            hideDropzones:false
        },
        resume:{
            enabled:true,
            id:'uploaderResumeCoin1'
        }   
    })
    .on('complete',function(id,fileName,response){
        
        //
        //$(this).find('.qq-upload-type').append(response.type);    
        })
    .on('error', errorHandler).off();


    $.UploaderByLink({
        'uploadForm' : '.uploadFileForm',
        'template':uploaderTemplate,
        'uploadsResult':'.qq-upload-list'
    }); 

    $('#addFilesToPost').die().live('click',function(){
            
        $('.qq-upload-list li').each(function(){
             
            if(!$(this).find('input[name=fileid]').is(':checked'))
                return;
            
            
            var fileId = $(this).find('input[name=fileid]').val();
            var cropFolder = $(this).find('input[name=destCrop]').val();
            var ext = $(this).find('input[name=ext]').val();
                
           
            image ="<div class='image'><img src='"+cropFolder+"/120x120/"+fileId+"."+ext+"'/><div class='imageControlls'><a href='#' class='deleteImage'>x</a></div><input type='hidden' name='images[]' value='"+fileId+"'></div>";
                    
            $('#postImagesContainer').append(image);
           
            $(this).fadeOut(300,function(){
                $(this).remove()
            });
        });
    }).off();
    
});
 