(function () {
    'use strict';

   //  $(window).on('load resize', function(){
   //      if(window.matchMedia('(max-width: 991px)').matches){     
	  //       $(document).on('click','.page-footer .custom-footer .title',function (){
			// 	if ($(this).next().is(':visible')) {
			// 		$(this).children().removeClass('active');
			// 		$(this).next().hide();
			// 	}
			// 	else {
			// 		$('.page-footer .custom-footer .content').hide();
			// 		$('.page-footer .custom-footer .title .caret').removeClass('active');
			// 		$(this).children().addClass('active');
			// 		$(this).next().show();
			// 	}
			// });
	  //   }
   //  });
   
   	$(function(){
   		$(document).on('click','.navigation > ul > li > a .ui-icon::before',function (){
   			console.log('test'); 
   			 $(this).hide();
            $('.navigation__back').show();
            console.log($(this).children().last());
            $('.navigation__back').html($(this).children().last());
   		
   			if($('.category-item').hasClass('opened')){
	        	$('.navigation > ul > li').hide();
	        	$(this).show();
	        }
	        else{
	        	$('.navigation > ul > li').show();
	        }
        });
        
        
        
       

   		$(document).on('click','.cms-header-menu .my-account > a',function (){
			if ($(this).next().is(':visible')) {
				$(this).children().removeClass('active');
				$(this).next().hide();
			}
			else {
				$(this).children().addClass('active');
				$(this).next().show();
			}	
		});
	    if(window.matchMedia('(max-width: 991px)').matches){     
	        $(document).on('click','.page-footer .custom-footer .title',function (){
				if ($(this).next().is(':visible')) {
					$(this).children().removeClass('active');
					$(this).next().hide();
				}
				else {
					$('.page-footer .custom-footer .content').hide();
					$('.page-footer .custom-footer .title .caret').removeClass('active');
					$(this).children().addClass('active');
					$(this).next().show();
				}
			});
	    }
	});
	
})();