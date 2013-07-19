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

function refactorWindowContent(el){
    var window = el.height();
    var windowHead = el.find('.windowHead').height();
    var controlls = el.find('.controll').height();
    controlls = windowHead + controlls;
    
    
    //alert(controlls);
    
    return window - controlls;
    
}
function resizeWindow(el){
    
    
    if(el.hasClass('normalWindow')){

        el.css({
            'left':0,
            'top':0
        });   
        el.removeClass('normalWindow');
        el.addClass('maxiWindow');
        refactorWindowContent(el);
        
        el.find('.body').css('height',refactorWindowContent(el)-el.find('.footer').height());
       
    }else{
        el.css({
            'left':20+'px',
            'top':20+'px'
        });
       
        el.find('.windowContent').css('height','300px');
        
        el.find('.body').css('height','inherit');
       
        el.removeClass('maxiWindow');
        el.addClass('normalWindow');
    }
       
    
}

$(function(){


    //############# Disable Anchors,Buttons click And form submition
    

    $('a,button').live('click',function(e){
        e.preventDefault();
       
    });

    $('form').live('submit',function(e){
        e.preventDefault();
    });

    //############# Icons settings ############
    $('.icons li').draggable({
        'containment':'#desktop',
        'revert':true
    });
    _window = $(window).height();	
    initTop = 0;
    initLeft = 2 
    $('.icons li').focus(function(){
        $(this).addClass('icons_li_focus'); 
    });


    $(".icons li a").live('mousedown',function(){
        $(this).addClass('icons_li_focus');   
    }); 
    $(document).live('click',function(){
        $('.icons li a').removeClass('icons_li_focus');
    }); 


    $.each($('.icons li'),function(index,value){   
        $(this).css({
            'left':initLeft+'px',
            'top':initTop+"px"
        });
        initTop = initTop + 95; 
        if($(this).position().top + 200 >  _window){
            initTop = 0;
            initLeft = initLeft+100;
        } 
    });


    //########### Windows Setting ###################

    $('.window').resizable({
        'containment':'#desktop'
    });

    $('.window .head .headButtons .closeWindow').live('click',function(){
        // $('#'+$(this).closest('.window').attr('id')).attr('href').remove();

        $('#dock_'+$(this).closest('.window').attr('id')).remove();

        $(this).closest('.window').remove();
    });

    $('.window .head').live('dblclick',function(){
       
        resizeWindow($(this).closest('.window'));
       
    });
    
    
    $('.window .head .headButtons .maxWindow').live('click',function(){
        resizeWindow($(this).closest('.window'));
        $(this).hide();
        $(this).closest('.window').find('.head .restWindow').show();
        
    });

    $('.window .head .headButtons .restWindow').live('click',function(){
        resizeWindow($(this).closest('.window'));
        $(this).hide();
        $(this).closest('.headButtons').find('.maxWindow').show();
    });


    $('.window .head .headButtons .miniWindow').live('click',function(){
        $(this).closest('.window').hide();
    });

    d = $(document);

    $('.icons li').live('dblclick',function(){
        var label = $(this).find('a label').html();
        var iconImage = $(this).find('a img').attr('src');
        var id = $(this).find('a').attr('href');
        var id = id.replace('#','');

        if($('#'+id).length > 0){
            if($('#'+id).is(":hidden"))
                $('#'+id).show();

            return;    
        }
        el = $(this);

        $(this).append("<div class='openWindowLoading'></div>");  

        $.post('admin/ajax/window/id/'+id,function(msg){
            el.find('.openWindowLoading').remove();
            clearActiveWindow();
            $('.window').draggable("destroy");
            $('#desktop').append(msg);                
            $('.window').draggable({
                'containment':'#desktop',
                'handle':'.windowHead'
            });
            $('#dockBar ul').append('<li class="activeDock" id="dock_'+id+'"><a href="#'+id+'"><img src="'+iconImage+'"/></a></li>');

        }
        );
    });


    $('#dockBar ul li a').live('click',function(){
        var id = $($(this).attr('href'));

        if(id.is(':visible')){

            if(id.hasClass('activeWindow')){
                clearActiveWindow();
                id.hide();
                return;
            }
        }
        clearActiveWindow();
        ; 
        id.addClass('activeWindow');
        $(this).closest('li').addClass('activeDock');
        id.show();

    });

    $('.window').live('mousedown',function(){

        clearActiveWindow();


        $(this).addClass('activeWindow');
        $('#dock_'+$(this).attr('id')).addClass('activeDock');


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