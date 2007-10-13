/**
 *  @name highlight
 *  @description Animates the background color to create a highlight animation
 *  @param String color (optional) color to highlight from, default "yellow"
 *  @param Mixed speed (optional) animation speed: integer for miliseconds, string ['slow' | 'normal' | 'fast']
 *  @param String easing (optional) The name of the easing effect that you want to use.
 *  @type jQuery
 *  @cat Plugins/Interface
 *  @author Stefan Petre
 *  @author Paul M. Jones
 */
jQuery.fn.highlight = function(color, speed, easing, callback) {
    
	/* current color of the element */
	var originalColor = jQuery(this).css('backgroundColor');
	
	/* find the first "real" color from the parent elements */
	var parentEl = this.parentNode;
	while(originalColor == 'transparent' && parentEl) {
		originalColor = jQuery(parentEl).css('backgroundColor');
		parentEl = parentEl.parentNode;
	}
	
	/* swap element to the highlight color */
	jQuery(this).css('backgroundColor', color);
	
	/* in IE, style is an object */
	if(typeof this.oldStyleAttr == 'object') {
	    this.oldStyleAttr = this.oldStyleAttr["cssText"];
	}
	
	/* animate back to the original color */
	jQuery(this).animate(
		{'backgroundColor':originalColor},
		speed,
		easing,
		callback
	);
};