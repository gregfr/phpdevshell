/*
 * jSpotlight 1.0.0
 *
 * Copyright (c) 2010 <a href="mailto:me@ovaistariq.net">Ovais Tariq</a>
 * License MIT/GPL
 * Update URL: http://downloads.ovaistariq.net/browse/?dir=jquery/jspotlight
 * http://tech.ovaistariq.net/2010/08/jquery-plugin-jspotlight/
 */

(function($)
{

	$.fn.spotlight = function( options )
	{
		var config = {
			color      : '#ffff99',
			mode       : 'show',
			duration   : 1000,
			easing     : 'linear',
			oncomplete : null
		};

		var save_props = function($element)
		{
			var data = {
				backgroundImage : $element.css( 'backgroundImage' ),
				backgroundColor : $element.css( 'backgroundColor' ),
				opacity         : $element.css( 'opacity' )
			};

			$element.data( 'spotlight_original_props', data );
		}

		var restore_props = function($element)
		{
			var data = $element.data( 'spotlight_original_props' );

			$element.css( data );
		}

		if( options ) $.extend( config, options );

		/*
	 * jQuery Color Animations
	 * Copyright 2007 John Resig
	 * Released under the MIT and GPL licenses.
	 */

		// We override the animation for all of these color styles
		$.each(['backgroundColor', 'borderBottomColor', 'borderLeftColor', 'borderRightColor', 'borderTopColor', 'color', 'outlineColor'], function(i,attr){
			$.fx.step[attr] = function(fx) {
				if ( fx.state == 0 ) {
					fx.start = getColor( fx.elem, attr );
					fx.end = getRGB( fx.end );
				}

				fx.elem.style[attr] = "rgb(" + [
				Math.max(Math.min( parseInt((fx.pos * (fx.end[0] - fx.start[0])) + fx.start[0],10), 255), 0),
				Math.max(Math.min( parseInt((fx.pos * (fx.end[1] - fx.start[1])) + fx.start[1],10), 255), 0),
				Math.max(Math.min( parseInt((fx.pos * (fx.end[2] - fx.start[2])) + fx.start[2],10), 255), 0)
				].join(",") + ")";
			};
		});

		// Color Conversion functions from highlightFade
		// By Blair Mitchelmore
		// http://jquery.offput.ca/highlightFade/

		// Parse strings looking for color tuples [255,255,255]
		function getRGB(color) {
			var result;

			// Check if we're already dealing with an array of colors
			if ( color && color.constructor == Array && color.length == 3 )
				return color;

			// Look for rgb(num,num,num)
			if (result = /rgb\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*\)/.exec(color))
				return [parseInt(result[1],10), parseInt(result[2],10), parseInt(result[3],10)];

			// Look for rgb(num%,num%,num%)
			if (result = /rgb\(\s*([0-9]+(?:\.[0-9]+)?)\%\s*,\s*([0-9]+(?:\.[0-9]+)?)\%\s*,\s*([0-9]+(?:\.[0-9]+)?)\%\s*\)/.exec(color))
				return [parseFloat(result[1])*2.55, parseFloat(result[2])*2.55, parseFloat(result[3])*2.55];

			// Look for #a0b1c2
			if (result = /#([a-fA-F0-9]{2})([a-fA-F0-9]{2})([a-fA-F0-9]{2})/.exec(color))
				return [parseInt(result[1],16), parseInt(result[2],16), parseInt(result[3],16)];

			// Look for #fff
			if (result = /#([a-fA-F0-9])([a-fA-F0-9])([a-fA-F0-9])/.exec(color))
				return [parseInt(result[1]+result[1],16), parseInt(result[2]+result[2],16), parseInt(result[3]+result[3],16)];

			// Look for rgba(0, 0, 0, 0) == transparent in Safari 3
			if (result = /rgba\(0, 0, 0, 0\)/.exec(color))
				return colors['transparent'];

			// Otherwise, we're most likely dealing with a named color
			return colors[$.trim(color).toLowerCase()];
		}

		function getColor(elem, attr) {
			var color;

			do {
				color = $.curCSS(elem, attr);

				// Keep going until we find an element that has color, or we hit the body
				if ( color != '' && color != 'transparent' || $.nodeName(elem, "body") )
					break;

				attr = "backgroundColor";
			} while ( elem = elem.parentNode );

			return getRGB(color);
		};

		// Some named colors to work with
		// From Interface by Stefan Petre
		// http://interface.eyecon.ro/

		var colors = {
			aqua:[0,255,255],
			azure:[240,255,255],
			beige:[245,245,220],
			black:[0,0,0],
			blue:[0,0,255],
			brown:[165,42,42],
			cyan:[0,255,255],
			darkblue:[0,0,139],
			darkcyan:[0,139,139],
			darkgrey:[169,169,169],
			darkgreen:[0,100,0],
			darkkhaki:[189,183,107],
			darkmagenta:[139,0,139],
			darkolivegreen:[85,107,47],
			darkorange:[255,140,0],
			darkorchid:[153,50,204],
			darkred:[139,0,0],
			darksalmon:[233,150,122],
			darkviolet:[148,0,211],
			fuchsia:[255,0,255],
			gold:[255,215,0],
			green:[0,128,0],
			indigo:[75,0,130],
			khaki:[240,230,140],
			lightblue:[173,216,230],
			lightcyan:[224,255,255],
			lightgreen:[144,238,144],
			lightgrey:[211,211,211],
			lightpink:[255,182,193],
			lightyellow:[255,255,224],
			lime:[0,255,0],
			magenta:[255,0,255],
			maroon:[128,0,0],
			navy:[0,0,128],
			olive:[128,128,0],
			orange:[255,165,0],
			pink:[255,192,203],
			purple:[128,0,128],
			violet:[128,0,128],
			red:[255,0,0],
			silver:[192,192,192],
			white:[255,255,255],
			yellow:[255,255,0],
			transparent: [255,255,255]
		};

		return this.queue(function()
		{
			var $this     = $( this );
			var animation = {
				backgroundColor : $this.css( 'backgroundColor' )
			};

			if( config.mode == 'hide' ) animation.opacity = 0;

			save_props( $this );

			$this
			.show()
			.css({
				backgroundImage : 'none',
				backgroundColor : config.color
			})
			.animate( animation, {
				duration : config.duration,
				easing   : config.easing,
				queue    : false,
				complete : function()
				{
					if( config.mode == 'hide' ) $this.hide();

					restore_props( $this );

					if( config.oncomplete ) config.oncomplete.apply( this, arguments );

					$this.dequeue();
				}
			});
		});
	}

})(jQuery);