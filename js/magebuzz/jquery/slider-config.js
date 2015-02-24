var $newslider = jQuery.noConflict(); 
$newslider(document).ready(function(){
    $newslider.easing.backout = function(x, t, b, c, d){
			var s=1.70158;
			return c*((t=t/d-1)*t*((s+1)*t + s) + 1) + b;
		};
		
		$newslider('#features-screen').scrollShow({
			elements:'div.item',//elements selector (relative to view)
			view:'#view',
			content:'#images',
			easing:'backout',
			wrappers:'link,crop',
			navigators:'a[id]',
			navigationMode:'s',
			circular:true,
			start:0
		});

});