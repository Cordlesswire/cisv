jQuery(function ($) {
$(document).ready(function(){  

	$('#featured').cycle({
		fx:     fx,
		timeout: timeout, 
		speed:  transitionspeed,
		next:   '#nextslide', 
		prev:   '#previousslide'
	});
			
})
});