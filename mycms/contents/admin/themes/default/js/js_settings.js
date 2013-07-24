 
function loading(msg ,fadeOut,boxClass){
    if(typeof msg === 'undefined'){
        msg = "Loadings";
    }
    
    
    if(typeof fadeOut != 'undefined'){
        loadingInnerOverlay = '';
        loadingOverlay = '';
        
    }else{
        loadingInnerOverlay='loadingInnerOverlay';
        loadingOverlay = 'loadingOverlay';
    }
    
    
    var overlay = "<div class='"+loadingOverlay+"'><div class='"+loadingInnerOverlay+"'>"+msg+"</h1></div>";
    $('.loadingOverlay').remove();
    $('body').append(overlay);
    
    windowHeight = $(window).height();
    windowWidth = $(window).width();
    totalMarginToTop = windowHeight / 2 - $('.loadingOverlay').height() ;
    totalMarginToTop = Math.floor( totalMarginToTop);
    
    totalMarginToLeft = (windowWidth -  $('.loadingOverlay').width())   / 2;
    totalMarginToLeft = Math.floor( totalMarginToLeft);
   
    $('.loadingOverlay').css({
        'position':'fixed',
        'left':totalMarginToLeft+'px',
        'top':totalMarginToTop+'px'
    });
        
    if(typeof fadeOut != 'undefined'){
        $('.loadingOverlay').delay(1100).fadeOut(300,function(){
            $(this).remove();
        });
    }
    return overlay;
}
function deleteLoading(){
    $('.loadingOverlay').fadeOut();
}


 
function elementCenter(element,parentElement){
    
    overlay = '<div id="popupOverlay"></div>';
    
    $('body').append(overlay);
    
    overlayWidth = $(element).width();
 
 
    if(typeof parentElement != "undefined")
    {
        bodyWidth = $(parentElement).width();
    }
    else
    {
        bodyWidth = $('body').width();
    }
    totalMarginToLeft = (bodyWidth - overlayWidth) / 2;
    
    totalMarginToLeft = Math.floor( totalMarginToLeft);
    
    $(element).css('left',totalMarginToLeft+'px');
    
}
function printPage(response){
    var innerWrapper = '.main-content';

    $('.breadcrumb').remove();

    if(response.app.nav){
        $(response.app.nav).insertAfter('#top-navigation');
    }

    //Print the top bar
    if(response.sidebar){
        if($(innerWrapper).find('.content-controlls').length == 0 ){
          $('<div class="content-controlls clear" style="display:block;">'+response.sidebar+'<div class="clear"></div></div>').insertBefore('#content-side .body');
        
        }else{
            $('.main-content .content-controlls').html(response.sidebar);
            $('.main-content .content-controlls').append("<div class='clear'></div>");
        }
    }else{
        $('.main-content .content-controlls').remove();
    }
    
    
        
        
    //Print page Body content

    if(response.window){
       
        if($(innerWrapper).find('.body').length == 0 )
            $(innerWrapper).append('<div class="body">'+response.window+'</div>');
        else
            $('#content-side .body').html(response.window);
        
        $(innerWrapper).find('.body').append("<div class='clear'></div>");
    }
    else
    {
        $('.main-content .body').empty();
    }   
    
    if(typeof response.app.refresh != "undefined"){
   
        if(typeof response.app.refresh.sidebar != "undefined"){
            $.ajax({
                'type':'post',
                'url':'admin/ajax/sidebar',
                cache:false,
                beforeSend: function(xhr){
                    xhr.setRequestHeader('X-PJAX', 'true');
                },
                'success':function(response){
                    $('#sidebar').html(response);
                }
            });
        }
                    
    }
    if(typeof response.app.message != "undefined"){
        
        messageText = (typeof response.app.message.text === "undefined") ? false : response.app.message.text;
        messageType = (typeof response.app.message.type === "undefined") ? false : response.app.message.type;
        messageHeading = (typeof response.app.message.heading === "undefined") ?false: response.app.message.heading;
    
        if($('.main-content .messages-container').length > 0)
            $('.main-content .messages-container').remove();
    
        var message = appendMessage(messageText,messageType,messageHeading);
        $('.main-content .body').prepend(message);
    
    //loading(message,1500,true);
    }
                   
    
            
}

function appendPopup(content,title){
    
    
    title = (typeof title === "undefined") ? "Popup" : title;
    
    overlay = '<div id="popupOverlay"></div>';
    
    content = '<div id="popupContent"><div class="popupHead"><a href="#" class="popupClose">X</a><p>'+title+'</p></div><div class="popupContent">'+content+'</div></div>';
    
    
    $('body').append(overlay);
    $('body').append(content);
    
    
    overlayWidth = $('#popupContent').width();
    bodyWidth = $('body').width();
    totalMarginToLeft = (bodyWidth - overlayWidth) / 2;
    
    totalMarginToLeft = Math.floor( totalMarginToLeft);
    
    $('#popupContent').css('left',totalMarginToLeft+'px');

    
    $('.popupClose').live('click',function(){
        $('#popupContent').fadeOut(300,function(){
            $(this).remove();
        });
        
        $('#popupOverlay').fadeOut(300,function(){
            $(this).remove();
        });
    });
    
}
function setHash(hash){
    
    location.hash = hash;

}
function getHash(){
    return location.hash;
}


function loadContent(el,url , type){
    $(function(){
        
        type = (typeof type === "undefined") ? "html" : type;
        
        
        $.post(url,function(msg){
            
            setHash(url);
            
            // el.find('.openWindowLoading').remove();
             
            $('#mainContent').html(msg);                
            
            
        },type); 
    });
}

function loadContentByHash(){
    var hash = getHash();
   
    if(hash.length == 0)
        return;
   
   
    url = hash.replace('#','');
    //  alert(url);
    loadContent($(document),url,'json');
}


function uniform(){
//$(" textarea, select, input[type=radio],input[type=checkbox]").uniform();
  
}

function init_notices(){

    $('.popup-info , .popup-warning , .popup-accept,.popup-success , .popup-error').live('click',function(){
        $(this).slideUp('fast');
    })
		
}

function init_windowGrid(){
    $(".windowGrid .item").live('mouseenter',function(){
        $(this).find('div.controll').animate({
            'bottom':0
        },'fast');
    }).live('mouseleave',function(){
        
        $(this).find('div.controll').stop(true,false).animate({
            'bottom':'-30px'
        });
    });
}

function init_sidebarActive(){
    $('#sidebar ul li a').live('click',function(){
        
        var subMenu = $(this).closest('li').find('ul');
        $(this).closest('ul').find('li ul').slideUp();
        if(!$(this).closest('li').hasClass('sidebarActive')){
            if(subMenu.length > 0){
                subMenu.stop(true,false).slideDown();
                
            }else{
                
            }
        }
        $(this).closest('ul').find('li a').removeClass('sidebarActive');
        $(this).addClass('sidebarActive');
        
            
        
    });
}



function handleMenuDrop(event,ui)
{
    ui.draggable.addClass( 'correct' );
 //   ui.draggable.draggable( 'disable' );
    $(this).droppable( 'disable' );
//    ui.draggable.position( { of: $(this), my: 'left top', at: 'left top' } );
    ui.draggable.draggable( 'option', 'revert', false );
}

$(function(){

    $('.dropdown-toggle').click(function(){
        $(this).closest('.dropdown').find('.menu-container').toggle();
    });

    $('.disable_htm_editor').live('click',function(){

        if($(this).is(':checked'))
        {
            removeEditor();
        }

    });
    $( "#MenuListElements ul li[item-type=item]" ).draggable({
        stack: '#addesItems',
        cursor: 'move',
        revert: true
    });


    $( "#addesItems" ).droppable({
        drop:handleMenuDrop,
        tolerance: 'touch'
    });
    
    $('#addNewCustomField').live('click',function(){
        elementCenter('.custom_field_container','.windowContent');
        $('.custom_field_container').fadeIn();
    });
    
    $('#closeCustomFieldContainer').live('click',function(){
        $('.custom_field_container').fadeOut();
        $('#popupOverlay').fadeOut(300,function(){
            $(this).remove();
        });
    });
 
 
    
    
    $('.info_box a.info_box_link').live('mouseenter',function(){
        $(this).closest('.info_box').find('.info_box_content').show();
    });
    $('.info_box').live('mouseenter',function(){
        $(this).find('.info_box_content').show();
    });
    $('.info_box').live('mouseleave',function(){
        $('.info_box').find('.info_box_content').stop(true,false).fadeOut(300);
    });
    
    //$('.showin-list').closest('label').css('margin-left','165px');
    
    $('.showHiddenElements').live('click',function(){
       
        elements = $(this).closest('.form-element').find('.form-inline-elements-element-hidden');
       
        if($(this).hasClass('hideHiddenElements')){
           
            $(this).addClass('hideHiddenElements');
       
            elements.hide();
           
            $(this).removeClass('hideHiddenElements');
       
        }else{
            elements.show();
           
            $(this).addClass('hideHiddenElements');
       
        }
       
       
       
    });



    $('#postImagesContainer .imageControlls  a.deleteImage').live('click',function(){
        $(this).closest('.image').fadeOut(100,function(){
            $(this).remove();
        });
    });
    
    $('#postImagesContainer .image').live('mouseenter',function(){
        $(this).find('.imageControlls').slideDown();
    }).live('mouseleave',function(){
        $(this).find('.imageControlls').stop(true,false).slideUp();
    });
    
     
    
   
    
    $('#myTab li.active a').tab('show');
    
    init_notices();
    init_windowGrid();
    init_sidebarActive();
    uniform();     
      
    loadContentByHash();
    //############# Disable Anchors,Buttons click And form submition
    

    $('a,button').live('click',function(e){
        e.preventDefault();
       
    });

    $('form').live('submit',function(e){
        e.preventDefault();
    });
    
    //########### show-profile-settings-list Setting ###################

 
    
    $('.show-profile-settings-list').hover(function(){
        $(this).find('ul.profile-settings-list').slideDown('fast');
    },function(){
        $(this).find('.profile-settings-list').stop(true,false).delay(300).slideUp('fast');     
    });
    

	
    //############ Check all checkbox
    
    $('.check_all').live('click',function(){
        
        var checkbox = $(this).attr('rel');
        
        var window = $(this).closest('table').find('.'+checkbox);
        
        if($(this).is(':checked')){
            $(window).attr('checked',true);
        }else{
            $(window).attr('checked',false);
        }
        
      
      
    });
    
    
    $('table tbody input[type=checkbox]').live('click',function(){
       
        if($(this).is(':checked')){
            if($(this).closest('tr').length > 0){
                $(this).closest('tr').addClass('windowTable_tbody_tr_checked_td');
            }
          
          
        }
        else{
            if($(this).closest('tr').length > 0){
                $(this).closest('tr').removeClass('windowTable_tbody_tr_checked_td');
            }
          
          
        }
           
    });


    $('.pop-list-action .listSubMenu').live('click',function(){
        $(this).closest('.pop-list-action').find('.pop-list').show('fast');
    });
	
	
    
    $(".windowGrid .item").live('mouseenter',function(){
        $(this).find('div.controll').animate({
            'bottom':'5px'
        },'fast');
    }).live('mouseleave',function(){
        
        $(this).find('div.controll').stop(true,false).animate({
            'bottom':'-30px'
        });
    });
	





    $('.addMenuSegment').live('click',function(){
    
        el =  $(this).closest('li');
    
        appId = el.find('.appid').val();
    
        segmentId = el.find('.segmentid').val();
        
        menuElementId = $('#MenuListContainer ul#MenuListElements li').length;  
        
        $('#MenuListContainer ul#MenuListElements').append(segmentContent(menuElementId,appId,segmentId));
    
    });


    $('a.getIndexMenu').live('click',function(){
        el = $(this).closest('li');
        
        var inputs = el.find(':input');
        
        inputs.each(function() {
            
            alert(this.name);
            
            
        });
    });
    
    $('.closeMenuEl').live('click',function(){
        $(this).closest('li').fadeOut(300,function(){
            $(this).remove();
        });
    });
},'jQuery');

function hiddenInput(name,value){
    return "<input type='hidden' name='"+name+"' value = '"+value+"'>";
}

function segmentContent(menuElementId,appId,segmentId){
    MenuSegment = hiddenInput('params[el]['+menuElementId+'][id]',segmentId);
    MenuSegment += hiddenInput('params[el]['+menuElementId+'][appid]',appId);
    
    MenuSegment += "<a href='"+$('#addMenuListElement a').attr('href')+"/do/appendChildel/"+menuElementId+"' class='openPopup disState'>Add child</div>";
    MenuSegment = "<li>"+el.find('.appTitle').html()+MenuSegment+"</li>";
    return MenuSegment;
}