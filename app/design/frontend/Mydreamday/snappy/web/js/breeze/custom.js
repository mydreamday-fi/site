window.addEventListener('scroll', function(){
    var header = document.querySelector('.header-content-s');
    header.classList.toggle("sticky", window.scrollY > 0);
});

(function () {
    'use strict';
    $(function(){
		$(document).on('click', '.sidebar.sidebar-main .block-title.filter-title', function(){
		    $('.sidebar.sidebar-main .block.filter').addClass('active');
		    if($('.button-close.filter-content-close').length == 0){
		        var btnClose = $('<button>').addClass('button-close filter-content-close');
		        $('.filter-content').prepend(btnClose);
		    }
		    
		});
		$(document).on('click', '.button-close.filter-content-close', function(){
		    $('.sidebar.sidebar-main .block.filter').removeClass('active');
		});

		$('#myBtnReadMore').click(function(){
			$('#more').toggleClass('long');
			if ($(this).attr('value') == "more-s") {
				$(this).find('.transalte-cusmore').hide();
				$(this).find('.transalte-cusless').show();
				$(this).attr('value',$(this).find('.transalte-cusless').text());
				$('#shadow').hide();
			} else {
				$(this).find('.transalte-cusmore').show();
				$(this).attr('value','more-s');
				$(this).find('.transalte-cusless').hide();
				$('#shadow').show();
			}
		})
	});
})();