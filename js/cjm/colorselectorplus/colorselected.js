// Swatch Selector Javascript - Copyright 2011 CJM Creative Designs

function clicker(id){
	if($('anchor' + id)){ jQuery('#anchor' + id).click(); }
}

var spConfigIndex = 0;
Event.observe(window, "load", function() {
	doPreSelection();
});  

document.observe("dom:loaded", function() {
	
	// If Custom Stock Status extension is installed and dynamic status change enabled, set vars
	if (dynamicStatus == 1) { var availTxt, shipTxt; }
	
	var firstAttribute = $$('.super-attribute-select').first().id;		

	// If the first option has swatches, disable unavailable swatches
	if ($('ul-' + firstAttribute)) {
		var theoptions = [];
			
		// Get all available option values
		$(firstAttribute).select('option').each(function(item) {
			if (item.value) { theoptions.push(item.value); }
		});
		
		// Disable swatches that are not available
		$$('#ul-' + firstAttribute + ' .swatch').each(function(item) {
			theoptions.contains(item.id.substr(6)) ? $(item.id).removeClassName('disabledSwatch') : $(item.id).addClassName('disabledSwatch');
		});
	}

	// Make the selects act as swatches
	$$('select').each(function(item) {
		if(item.hasClassName('super-attribute-select')){ setClickHandler(item); }
	});
	
	// Disable all swatches below the first selectable swatch
	$$('ul[id^="ul-attribute"]').each(function(item) {
		var attributeId = item.id.split('-')[1];
		if($(attributeId).disabled){
			$(item.id).select('img', 'div').invoke('addClassName', 'disabledSwatch'); }
	});

});


// Dropdown observer function
		
function setClickHandler(element) {
	element.onchange = function () {
		
		var id = this.options[this.selectedIndex].value,
			runMeArgs = [this.id, '', 'optionzero'],
			selectorid = this.id,
			selectors = $$('.super-attribute-select'),
			optionArr = [],
			l = 0,
			isSwatch = 0,
			theIndex = 0,
			visibleViews = false,
			nextSelect;
		
		// Decide if this attribute has swatches
		if($('ul-' + selectorid)){ isSwatch = 1; }
		
		// Set what the next attribute is and the index of the selected attribute
		$$('.super-attribute-select').each(function(item, index) {
			if(selectorid == item.id){
				theIndex = index; 
				if(selectors[index+1]){ nextSelect = selectors[index+1].id; }
			}
		});
		
		// If Please Select is selected
		if (!id) {
			
			// If this is a swatch attribute
			if (isSwatch == 1) {
			
				// Apply the onclick function
				colorSelected.apply(this, runMeArgs);
			
				if ($('ul-moreviews') !== null) {
					themoreviewimgs = $$('#ul-moreviews div');
					selectedmoreviewtitle = $('moreviews-title');
					$$('ul.onload-moreviews').invoke('show');
				}
			
				// If there are moreviews displayed, display the more views title
				if (themoreviewimgs !== undefined && themoreviewimgs.length > 0) {
					$$('#ul-moreviews div').each(function(item) {
						if($(item).visible()){
							visibleViews = true;
							throw $break; }
					});
					
					if (visibleViews) {
						selectedmoreviewtitle.show();
					} else {
						selectedmoreviewtitle.hide(); }
				}
			
			} else {
			
				// Disable all swatch attributes after the current attribute
				$$('.super-attribute-select').each(function(item, index) {
					if (index > theIndex){
						$$('#ul-' + item.id + ' .swatch').invoke('removeClassName', 'swatchSelected').invoke('addClassName', 'disabledSwatch'); }
				});
			}
		
		} else {
			
			// If this drop-down is for a swatch attribute, give it the same onclick
			if ($('swatch' + id) && $('swatch' + id) !== null) {
			
				clickHandler = $('swatch' + id).onclick;
				clickHandler.apply(this);
				if($('anchor' + id)){ jQuery('#anchor' + id).click(); }
		
			// If the next attribute is a swatch attribute, reset the swatches
			} else {
			
				// Disable all swatch attributes after the current attribute
				$$('.super-attribute-select').each(function(item, index) {
					if (index > theIndex){
						$$('#ul-' + item.id + ' .swatch').invoke('removeClassName', 'swatchSelected').invoke('addClassName', 'disabledSwatch'); }
				});
				
				// If the next attribute has swatches
				if($('ul-' + nextSelect)) {
			
					//Enable selected swatches
					$$('#ul-' + nextSelect + ' .swatch').invoke('removeClassName', 'disabledSwatch');
				
					// Get all available option values
					$(nextSelect).select('option').each(function(item) {
						if (item.value) {
							optionArr.push(item.value); }
					});
				
					// Disable swatches that are not available
					$$('#ul-' + nextSelect + ' .swatch').each(function(item) {
						if (optionArr[l]) {
							if ('swatch' + optionArr[l] === item.id) {
								$(item.id).removeClassName('disabledSwatch');
								l += 1;
							} else {
								$(item.id).addClassName('disabledSwatch'); }
						} else {
							$(item.id).addClassName('disabledSwatch'); }
					});
				}
			}
		}
		
		// If Custom Stock Status extension is installed and dynamic status change enabled
		if (dynamicStatus == 1) {
			
			if ($$('.availability')) { $$('.availability').invoke('update', spConfig.config.mainstock); }
			if ($$('.shipsin')) { $$('.shipsin').invoke('update', spConfig.config.mainship); }
		
			if (element.options[element.selectedIndex].config !== undefined && element.options[element.selectedIndex].config.shipsby !== undefined) {
				availTxt = element.options[element.selectedIndex].config.customstock;
				shipTxt = element.options[element.selectedIndex].config.shipsby;
				
				$$('.availability').invoke('update', availTxt);
				if ($$('.shipsin') !== null) {
					$$('.shipsin').invoke('update', shipTxt); }
			}
			
			if(element.options[element.selectedIndex].config.oos == 0 && element.options[element.selectedIndex].config.oos !== undefined)
			{
        		$$('.add-to-cart').invoke('hide');
          		var selectedId = element.options[element.selectedIndex].config.allowedProducts[0];
           		$$('p.product-'+selectedId+'-alert').invoke('show');
        	} else {
            	$$('p.product-alert').invoke('hide');
           		$$('.add-to-cart').invoke('show');
        	}  
		}
	};
}


// Main Swatch Function

function colorSelected(id, value, product_image_src, front_label) {
	//"use strict";
	
	var theswitchis = 'off',
	    howMany = '',
		switchCounter = 0,
		l = 0,
		nextAttribute = '',
		nextAttrib = [],
		theoptions = [],
		selectedmoreview = [],
		onloadMoreViews = [],
		moreviews = [],
		dropdownEl = $(id),
		i, dropdown, themoreviewimgs, textdiv, theattributeid, thedropdown, thetextdiv, dropdownval, base_image, theSwatch, selectedmoreviewtitle, visibleViews, isAswatch;
	
	// If the dropdown is disabled, do nothing because we are not allowed to select an option yet
	if (dropdownEl.disabled) { return; }
		
	// Get the swatch that was just clicked
	//theSwatch = $$('#ul-' + id + ' li img[id="' + value + '"], div[id="' + value + '"]');
	
	// If swatch is disabled or already selected, do nothing
	//if (theSwatch.invoke('hasClassName', 'disabledSwatch') == 'true' || theSwatch.invoke('hasClassName', 'swatchSelected') == 'true') { return; }
	if(value){
		if ($('swatch' + value).hasClassName('disabledSwatch') || $('swatch' + value).hasClassName('swatchSelected')) { return; } }
	
	// Set the base image and more views to the selected swatch
	if (value) {
		
		//if (jQuery('#anchor' + value).lenght == 0) {

			base_image = $('image');
			base_image_href = $('cloudZoom');
			if (product_image_src && base_image && (base_image.src !== product_image_src)) {
				base_image.hide();
				Element.show.delay(0.3, 'loadingImage');
				base_image.src = product_image_src;
				base_image_href.href = product_image_src;
				base_image.onload = function(){
					Element.hide.delay(0.3, 'loadingImage');
  					base_image.appear({ duration: 0.25, from: 0.0, to: 1.0 }); };
			}
		//}
		
		if ($('ul-moreviews') !== null) {
			themoreviewimgs = $$('#ul-moreviews div');
			onloadMoreViews = $$('#ul-moreviews div.onload-moreviews');
			if (themoreviewimgs) {
				selectedmoreview = $$('#ul-moreviews div.moreview' + value);
				howMany = selectedmoreview.length;
				selectedmoreviewtitle = $('moreviews-title'); }
			if (onloadMoreViews) {
				onloadMoreViews.invoke('hide'); }
		}
	}
		
	// ------------------------------------------------------------------------------------------
	// --- RESET ALL SWATCH BORDERS, DROPDOWNS, MORE VIEWS AND TEXT BELOW THE SELECTED SWATCH ---
	// ------------------------------------------------------------------------------------------
	
	// If Custom Stock Status extension is installed, reset the availability text
	if (dynamicStatus == 1) {
		if ($$('.availability')) { $$('.availability').invoke('update', spConfig.config.mainstock); }
		if ($$('.shipsin')) { $$('.shipsin').invoke('update', spConfig.config.mainship); }
	}
				
	// Go through every attribute on product
	$$('.super-attribute-select').each(function(item, index) {
		thedropdown = 'attribute' + item.id.replace(/[a-z]*/, '');
		isAswatch = 0;
		theattributeid = item.id.replace(/[a-z]*/, '');
		thetextdiv = 'divattribute' + theattributeid;
		ulId = 'ul-' + thedropdown;
		
		// If this attribute is a swatch attribute, set to yes
		if($('ul-' + thedropdown)){ isAswatch = 1; }
	
		// If we are on the selected swatch dropdown, turn the switch on
		if (id === thedropdown) {
			theswitchis = 'on'; } 
				
		// If we are either on the dropdown we selected the swatch from or a dropdown below
		if (theswitchis === 'on') {
			// If we are on the dropdown after the selected swatch dropdown, get the next attribute id
			if (switchCounter === 1) {
				if(isAswatch == 1){ nextAttribute = theattributeid; } else { nextAttribute = '' ; } 
			} 
			if (isAswatch == 1){
				dropdown = $(thedropdown);
				textdiv =  $(thetextdiv);	
				if (textdiv !== null) { textdiv.update(selecttitle); }
				
				dropdown.selectedIndex = 0;
			
				// Go through all the swatches of this attribute and reset
				$$('#' + ulId + ' ' + ' .swatch').invoke('removeClassName', 'swatchSelected');
			
				// Hide the more view images of the swatch
				$(thedropdown).select('option').each(function(option) {
					$$('#ul-moreviews div.moreview' + option.value).invoke('hide');
				});

				// Disable all swatches below the first selectable swatch
				if (switchCounter >= 1) {
					$$('#' + ulId + ' ' + ' .swatch').invoke('addClassName', 'disabledSwatch'); }
			}
			
			switchCounter += 1;
		}
	});
	
	// If there is only one attribute on this product, set the next attribute to none
	if (nextAttribute === null || nextAttribute === '') { nextAttribute = 'none'; }
			
	// ------------------------------------------------------------------------
	// ------------------- SELECT THE CORRECT SWATCH --------------------------
	// ------------------------------------------------------------------------
			
	if (value) {
		
		// Set the swatch and dropdown to selected option
		$('swatch' + value).addClassName('swatchSelected');
		dropdownEl.value = value;
		
		// Set the title of the option
		if ($('div' + id) !== null) {
			if (front_label !== 'null') {
				$('div' + id).update(front_label);
			} else {
				$('div' + id).update(dropdownEl.options[dropdownEl.selectedIndex].text); }
		}
		
		
		// Show the correct more view images and if there are moreviews displayed, display the more views title
		if (selectedmoreview !== null && selectedmoreview !== undefined && howMany > 0) {
			//selectedmoreviewtitle.show();
			selectedmoreview.invoke('show');
		} else {
			if(howMany > 0){ selectedmoreviewtitle.hide(); }
		}
		
		spConfig.configureElement(dropdownEl);
	}
	
	// If Custom Stock Status extension is installed and set to enable dynamic change
	if (dynamicStatus == 1) {
		// Set the availability text and shipping date when a swatch is clicked
		if (dropdownEl.options[dropdownEl.selectedIndex].config !== undefined && dropdownEl.options[dropdownEl.selectedIndex].config.shipsby !== undefined) {
			availTxt = dropdownEl.options[dropdownEl.selectedIndex].config.customstock;
			shipTxt = dropdownEl.options[dropdownEl.selectedIndex].config.shipsby;
				
			$$('.availability').invoke('update', availTxt);
			if ($$('.shipsin') !== null) {
				$$('.shipsin').invoke('update', shipTxt); }
		}
		
		if(dropdownEl.options[dropdownEl.selectedIndex].config.oos == 0 && dropdownEl.options[dropdownEl.selectedIndex].config.oos !== undefined)
		{
        	$$('.add-to-cart').invoke('hide');
          	var selectedId = dropdownEl.options[dropdownEl.selectedIndex].config.allowedProducts[0];
           	$$('p.product-'+selectedId+'-alert').invoke('show');
        } else {
            $$('p.product-alert').invoke('hide');
           	$$('.add-to-cart').invoke('show');
        }  
   	}
   		
	// -------------------------------------------------------------------------
	// -------------------- HIDE UNAVAILABLE SWATCHES --------------------------
	// -------------------------------------------------------------------------
	
	// If there is more then one swatch attribute on this product
	if (nextAttribute !== 'none' && value) {
		
		// Set the next attributes dropdown
		nextAttrib = $('attribute' + nextAttribute);
		
		// Get all available option values
		$(nextAttrib).select('option').each(function(item) {
			if (item.value) { theoptions.push(item.value); }
		});

		// Disable swatches that are not available
		$$('#ul-attribute' + nextAttribute + ' .swatch').each(function(item) {
			theoptions.contains(item.id.substr(6)) ? $(item.id).removeClassName('disabledSwatch') : $(item.id).addClassName('disabledSwatch');
		});
	}
	
	//slideimage(0);
	
	// Not sure if this is still needed
	//this.reloadPrice();
}
function doPreSelection()
{
	if (spConfigIndex >= spConfig.settings.length) { return; }
 
	var spi = spConfigIndex;
	var obj = spConfig.settings[spConfigIndex];
	
	if (spConfig.settings[spi].config.preselect)
	{
		var selectThis = spConfig.settings[spi].config.preselect;
		
		for (var spj=0; spj<spConfig.settings[spi].options.length; ++spj)
		{
			if (spConfig.settings[spi].options[spj].value == selectThis || selectThis === 'one')
			{
				if(selectThis === 'one'){
					spConfig.settings[spi].selectedIndex = 1; }
				else{
					spConfig.settings[spi].selectedIndex = spj; }
					
    			Event.observe(obj, "change", function(){});

    			var isIE9Plus =  Prototype.Browser.IE && parseInt(navigator.userAgent.substring(navigator.userAgent.indexOf("MSIE")+5)) >= 9;
    			
				if (!isIE9Plus && document.createEventObject)
				{
					var evt = document.createEventObject();
					obj.fireEvent("onchange", evt);
				}
				else
				{
					var evt = document.createEvent("HTMLEvents");
					evt.initEvent("change", true, true ); 
					!obj.dispatchEvent(evt);
				}
				break;
			}
		}
	}
	++spConfigIndex;
	window.setTimeout("doPreSelection()", 1);
}

Array.prototype.contains = function(obj) {
    var i = this.length;
    while (i--) {
        if (this[i] === obj) {
            return true;
        }
    }
    return false;
}


function readCookie(name) {
    var i, c, ca, nameEQ = name + "=";
    ca = document.cookie.split(';');
    for(i=0;i < ca.length;i++) {
        c = ca[i];
        while (c.charAt(0)==' ') {
            c = c.substring(1,c.length);
        }
        if (c.indexOf(nameEQ) == 0) {
            return c.substring(nameEQ.length,c.length);
        }
    }
    return '';
}

jQuery(document).ready(function() {

  if(readCookie('productcolors') != '')
    {  
       var str = readCookie('productcolors');
       var colorParams = str.split('-'); 
           el = $$('#swatch' + colorParams[1] + '').first();  
           disabled = $$('#anchor' + colorParams[1] + ' .disabledSwatch').first();

           if (el && !disabled) {
           el.click();
           } else { 

           clicked=0;
           elements = jQuery('.swatchContainer .swatch');
           elements.each(function() { 
               if(jQuery(this).attr('class') != 'swatch disabledSwatch' && clicked == 0) {
                   jQuery(this).click();
                   clicked=1;
               } 
           });


           }          

    } else {
	
           clicked=0;
           elements = jQuery('.swatchContainer .swatch');
           elements.each(function() { 
               if(jQuery(this).attr('class') != 'swatch disabledSwatch' && clicked == 0) {
                   jQuery(this).click();
                   clicked=1;
               } 
           });
		   
	}


})

function slideimage(dir) {

    var elements = document.getElementById('ul-moreviews').children;
    
    var imagesrc = $$('#cloudZoom #image').first().src;
    
    for (var i = 0; i < elements.length; i++) {
    
        if (elements[i].style.display == '') {
           var targetclassname = elements[i].className;
        }
    
    }
    
    var currentcolor=0;
    var colors = 1;
	var morev = targetclassname.split(' ');
	
	if(dir == 1) {
	
		var button = $$('#ul-moreviews .' + morev[0] + ' .slick-next').first();
		if (button != undefined) { 
			button.click();
		}
		
	}
	if(dir == -1) {
	
		var button = $$('#ul-moreviews .' + morev[0] + ' .slick-prev').first();
		if (button != undefined) { 
			button.click();
		}
	
	}
	
    var imgelements = $$('#ul-moreviews .' + morev[0] +' .item:not(.slick-cloned) a');
    for (var j = 0; j < imgelements.length; j++) {
		var thumbimgsrcfull = imgelements[j].href;

		var thumbimgsrc = thumbimgsrcfull.split('/');
		var imgsrc = imagesrc.split('/');
		var lastfullel = thumbimgsrc.length-1;
		var lastel = imgsrc.length-1;

		if (thumbimgsrc[lastfullel] == imgsrc[lastel]) {
		   currentcolor = colors;
		}
		colors++;
    
    }

    var numbercolors=colors-1;
    
    var newpos = currentcolor+parseInt(dir);
    if (newpos == 0)
    newpos = numbercolors;
    
    if (newpos > numbercolors)
    newpos = 1;


    for (var k = 0; k < imgelements.length; k++) {
    
        if (k == newpos-1) {
           imgelements[k].click();
        }
    
    }


}


function slideMore(that) {

	var slider = that.up('.more-views-product-slider');
	if (slider != undefined) {
		slider.select('button.slick-next').first().click();
	}

}

/*
function showmainphotoarrows() {

    var elements = document.getElementById('ul-moreviews').children;
    var colorimages = 0;
    for (var i = 0; i < elements.length; i++) {
    
        if (elements[i].style.display == '') {
           colorimages++;
        }
    
    }
    if(colorimages > 1) {
        document.getElementById('mainphoto-next-arrow').style.display = 'block';
        document.getElementById('mainphoto-prev-arrow').style.display = 'block';
    }

}



function hidemainphotoarrows() {

    document.getElementById('mainphoto-next-arrow').style.display = 'none';
    document.getElementById('mainphoto-prev-arrow').style.display = 'none';

}
*/


		jQuery(document).ready(function() {
		
		
			jQuery('.more-views-product-slider').slick({
			  vertical: true,
			  slidesToShow: 3,
			  slidesToScroll: 1,
			  arrows: true
			});
			
			jQuery(".product-shop #ul-attribute85 li a img").click(function() {
				jQuery("#product-color-clone li a img.swatchSelected").removeClass("swatchSelected");
				jQuery("#product-color-clone li a img.swatchClicked").removeClass("swatchClicked");
				jQuery("#product-color-clone #" + jQuery(this).attr("id")).addClass("swatchClicked");
			});		

			jQuery(".product-img-box #ul-moreviews .item a").click(function() {
			
				jQuery(".product-img-box #ul-moreviews .item a.swatchClicked").removeClass("swatchClicked");
				jQuery(this).addClass("swatchClicked");				

			});				

						
		});
		
		
		function fancyInit(id) {
		
			jQuery(id).fancybox({
			parent      : '#main-image-popup',
			autoSize   : false,
			autoHeight : false,
			autoWidth  : false,
			autoResize : false,
			autoCenter  : false,
			fitToView   : false,
			padding    : 0,
			helpers    : {
			   overlay : false,
			   title   : false
			},
			'beforeShow' : function(arg) {
			// Here does the magic starts
			jQuery('.fancybox-image').wrap('<a id="fancyboxImage" class="cloud-zoom"  href = "' + jQuery('.fancybox-image').attr("src") + '" rel="position: \'inside\', showTitle: false, adjustX:0, adjustY:0"></div>');
			// That's it
			jQuery('.cloud-zoom, .cloud-zoom-gallery').CloudZoom();
			}
			});		
		
		}
		
		function cloneColor() {
		
			
			var count = 0;
			jQuery.each(jQuery("#product-options-wrapper #ul-attribute85 li"), function() {
				count++;
			});
			
			if(count > 1) {
				jQuery("#ul-attribute85").clone().appendTo("#product-color-clone .clone-inner");
			} else {
				jQuery("#product-color-clone").css('display', 'none');
			}
			
			var width = count*78;
			if(count > 5) {
				jQuery( "#product-color-clone #ul-attribute85" ).addClass('cloneslider');
				jQuery('.cloneslider').jcarousel({
					vertical: false,
					scroll: 1,
					wrap: 'circular' 
				});
			} else {
				jQuery( "#product-color-clone .clone-inner" ).css('width', width);
			}
		
		}
		
		

