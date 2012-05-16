$.fn.ajaxOverlay = function(options) {
	var opts = $.extend({}, $.fn.ajaxOverlay.defaults, options); 

	if ( opts.action === 'start' )  {
		$(this).data('position', $(this).css('position'));
        $(this).data('overflow', $(this).css('overflow'));
        $(this).css('position', 'relative');
        $(this).css('overflow', 'hidden');

        // Propiedades del wrapper que contendrá los elementos del overlay
        $('<div class="ui-overlay"></div>').css({
            top: 0 + $(this).scrollTop(),
            left: 0 + $(this).scrollLeft(),
            width: $(this).outerWidth(),
            height: $(this).outerHeight()
        }).appendTo($(this));

        // agrego la capa que contiene la animación y el mensaje
        $(".ui-overlay").append('<div class="ui-block-message"><div class="ui-gif-loading"></div></div>');

        // Si hay un mensaje lo agregamos
        if( opts.message != '' )
            $(".ui-block-message").append('<span class="ui-text-loading">'+ opts.message +'</span>');
    
    // Si la acción solicitada es "end" removemos los divs
    }   else if( opts.action === 'end') {
        $(this).children('.ui-overlay').fadeOut(1000, function() {
            $(this).remove();
        });
        
    }
};

$.fn.ajaxOverlay.defaults = {
	action: 'start',
    message: 'Cargando...',

};