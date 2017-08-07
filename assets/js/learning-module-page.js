(function($){
	$(document).ready(function(){
		$('#begin-module').on('click', function(){
			$('#intro').fadeOut(1000, function(){
				$('.widget-area').fadeIn(1000);

			});
		})
	})
}(jQuery));
