function clearMenu(ev){

    if(ev.closest('#topMenu').length == 0){
        $('#topMenu li ul.subMenu').hide();
        $('#topMenu ul').removeClass('active');
        $('#topMenu ul li').removeClass('active');

    }
}
function openMenu(e){
    clearMenu();
    sub = e.closest('li').find('.subMenu');
    sub.show();		
}
function clearEl(){
    clearMenu();
}
function clearActiveWindow(){
    $('#dockBar ul li').removeClass('activeDock');
    $('#desktop div').removeClass('activeWindow');
}

function loading(){
    var overlay = "<div class='overlay'></div>";
    return overlay;
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
            
            clearActiveWindow();
            
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

$(function(){

   loadContentByHash();
    //############# Disable Anchors,Buttons click And form submition
    

    $('a,button').live('click',function(e){
        e.preventDefault();
       
    });

    $('form').live('submit',function(e){
        e.preventDefault();
    });

    
    //########### Windows Setting ###################


    d = $(document);

    $('.sidebar li').live('click',function(){
        
        $(this).closest('ul').find('li').removeClass('activeSidebar');
        $(this).addClass('activeSidebar');


        var id = $(this).find('a').attr('href');

        var id = id.replace('#','');

        if($('#'+id).length > 0){
            if($('#'+id).is(":hidden"))
                $('#'+id).show();

            return;    
        }
        el = $(this);

        $(this).append("<div class='openWindowLoading'></div>");  
        url = 'admin/ajax/window/id/'+id;
        
        //loadContent(el,url);
    });



    //############# Menubar Interactions ####################



    $("div.window .menubar a").live('mousedown',function(){
        $(this).addClass('focusedMenubar');
    }); 
    $("div.window .menubar a").live('mouseup',function(){
        $(this).removeClass('focusedMenubar');
    }); 
    
    $("div.window .menubar li").live('mouseenter',function(){
        $(this).find('div.subMenu').slideDown();
    }).live('mouseleave',function(){
        
        $(this).find('div.subMenu').stop(true,false).slideUp();
    });

    //############# Toolbar Interactions ####################



    //#########Sidebar Interactions #########################

    $('.window .sidebar .sidebarItem ul li a').live('click',function(){
        $(this).closest('ul').find('li a').removeClass('active');
        $(this).addClass('active');
    });



    //########## Top menu Bar ##########

    $('#topMenu ul li').live('click',function(){
       
        $(this).find('.subMenu').show(); 
        if($(this).closest('.subMenu').length == 0){
            $(this).closest('ul').addClass('active'); 
            $(this).addClass('active');
        }
    });


    $('#topMenu ul.active li').live('mouseenter',function(){
        if($(this).closest('.subMenu').length == 0){
            $('#topMenu ul.active li').removeClass('active');
            $(this).addClass('active');
            $('#topMenu ul.active li').find('.subMenu').hide(); 
            $(this).find('.subMenu').show(); 
        }

    });


    $(document).live('click',function(e){
        clearMenu($(e.target));
    });


    //############# Notifications ###############

    $('#notifications .controll .show').live('click',function(){
        $(this).closest('.controll').find('.hide').show();
        $(this).hide();
        $(this).closest('#notifications').stop(true,false).animate({
            'right':'0'
        },'fast') ;

    });
    $('#notifications .controll .hide').live('click',function(){

        $(this).closest('.controll').find('.show').show();
        $(this).hide();
        $(this).closest('#notifications').animate({
            'right':'-249px'
        }) ;
    });
    
    
    
    //############ Check all checkbox
    
    $('.check_all').live('click',function(){
        var checkbox = $(this).attr('rel');
        var window = $(this).closest('.window').find('.'+checkbox);
        if($(window).is(':checked')){
            if($(window).closest('tr').length > 0){
                $(window).closest('tr').removeClass('windowTable_tbody_tr_checked_td');
            }
          
            $(window).attr('checked',false);
          
        }
        else{
            if($(window).closest('tr').length > 0){
                $(window).closest('tr').addClass('windowTable_tbody_tr_checked_td');
            }
          
            $(window).attr('checked',true);
      
        }
      
      
    });
    
    
    $('.windowTable tbody input[type=checkbox]').live('click',function(){
       
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
    
    //######### Window Table
    
    //windowTable_tbody_tr_focus
    
    
    $(".windowTable tbody tr").live('click',function(){
        $(this).closest('tbody').find('tr').removeClass('windowTable_tbody_tr_focus');
        $(this).addClass('windowTable_tbody_tr_focus');
    }); 
    
    
    
    $(".windowGrid .item").live('mouseenter',function(){
        $(this).find('div.controll').animate({'bottom':0},'fast');
    }).live('mouseleave',function(){
        
        $(this).find('div.controll').stop(true,false).animate({'bottom':'-30px'});
    });
    
    
    
},'jQuery');