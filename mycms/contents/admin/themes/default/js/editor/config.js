/*
Copyright (c) 2003-2012, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
    // Define changes to default configuration here. For example:
    // config.language = 'fr';
    // config.uiColor = '#AADC6E';
        
    };


var editor, html = '';

function createEditor()
{ 
    
    
    $.each(CKEDITOR.instances, function(index, value) {
        
        var editor = CKEDITOR.instances[index];
        if (editor) {
            editor.destroy(true);
        }                        
        
                            
    });
    
    
        
    // Create a new editor inside the <div id="editor">, setting its value to html
        
    //var config = {};
    //editor = CKEDITOR.replace( 'editor', config, html );
    $('.editor').ckeditor();
    
//alert(CKEDITOR.instances);

//

    
}




function removeEditor()
{

    //CKEDITOR.instances.editor.destroy(true);
    console.log(CKEDITOR);
}



createEditor();