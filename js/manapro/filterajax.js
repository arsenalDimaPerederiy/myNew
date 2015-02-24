/**
 * @category    Mana
 * @package     ManaPro_FilterAjax
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
// the following function wraps code block that is executed once this javascript file is parsed. Lierally, this 
// notation says: here we define some anonymous function and call it once during file parsing. THis function has
// one parameter which is initialized with global jQuery object. Why use such complex notation: 
// 		a. 	all variables defined inside of the function belong to function's local scope, that is these variables
//			would not interfere with other global variables.
//		b.	we use jQuery $ notation in not conflicting way (along with prototype, ext, etc.)
(function($) {
	if (window.History && window.History.enabled) {
		jQuery(window).bind('statechange',function(){
			var State = window.History.getState();
			applyFilter(State.url);
		});
	}
	function applyFilter(url) {
		var selectors = jQuery.options('#m-filter-ajax').selectors;
		jQuery(document).trigger('m-ajax-before', [selectors]);
		jQuery.get(window.encodeURI(url + (url.indexOf('?') == -1 ? '?' : '&') + 'm-ajax=1'))
			.done(function(response) {
				try {
					response = jQuery.parseJSON(response);
					if (!response) {
						if (jQuery.options('#m-filter-ajax').debug) {
							alert('No response.');
						}
					}
					else if (response.error) {
						if (jQuery.options('#m-filter-ajax').debug) {
							alert(response.error);
						}
					}
					else {
						jQuery.dynamicReplace(response.update, jQuery.options('#m-filter-ajax').debug);
						if (response.options) {
							jQuery.options(response.options);
						}
						if (response.script) {
							jQuery.globalEval(response.script);
						}
						if (response.title) {
						    document.title = response.title;
						}
						rebind();
					}
				}
				catch (error) {
					if (jQuery.options('#m-filter-ajax').debug) {
						alert(response && typeof(response)=='string' ? response : error);
					}
				}					
			})
			.fail(function(error) {
				if (jQuery.options('#m-filter-ajax').debug) {
					alert(error.status + (error.responseText ? ': ' + error.responseText : ''));
				}
			})
			.complete(function() {
				jQuery(document).trigger('m-ajax-after', [selectors]);
			});
		return false; // prevent default link click behavior
	}
	
	function hrefClick() {
		var locationUrl = window.decodeURIComponent(this.href);
		if (window.History && window.History.enabled) {
			
			window.History.pushState(null,window.title,locationUrl);
			return false;
		}
		else {
			return applyFilter(locationUrl);  
		}
	}
	function rebind() {
		jQuery.each(jQuery.options('#m-filter-ajax').exactUrls, function(urlIndex, url) {
			jQuery('*[href="' + url + '"]').each(function() {
				var anchor = this;
				var isException = false;
				jQuery.each(jQuery.options('#m-filter-ajax').urlExceptions, function(urlExceptionIndex, urlException) {
					if (!isException && anchor.href.indexOf(urlException) != -1) {
						isException = true;
					}
				});
				if (!isException) {
					jQuery(anchor).unbind('click', hrefClick).bind('click', hrefClick);
				}
			}); 
		});
		jQuery.each(jQuery.options('#m-filter-ajax').partialUrls, function(urlIndex, url) {
			jQuery('*[href*="' + url + '"]').each(function() {
				var anchor = this;
				var isException = false;
				jQuery.each(jQuery.options('#m-filter-ajax').urlExceptions, function(urlExceptionIndex, urlException) {
					if (!isException && anchor.href.indexOf(urlException) != -1) {
						isException = true;
					}
				});
				if (!isException) {
					jQuery(anchor).unbind('click', hrefClick).bind('click', hrefClick);
				}
			}); 
		});
	}
        
	var oldSetLocation = null;
	function setLocation(locationUrl) {
		var handled = false;
		jQuery.each(jQuery.options('#m-filter-ajax').exactUrls, function(urlIndex, url) {
			if (!handled && locationUrl == url) {
				var isException = false;
				jQuery.each(jQuery.options('#m-filter-ajax').urlExceptions, function(urlExceptionIndex, urlException) {
					if (!isException && locationUrl.indexOf(urlException) != -1) {
						isException = true;
					}
				});
				if (!isException) {
					handled = true;

test=locationUrl.split('.html');
locationUrl = test[0]+'/where/limit/20.html';
//without this remove last filter not working

                                        locationUrl = window.decodeURIComponent(locationUrl);
					if (window.History && window.History.enabled) {
						window.History.pushState(null,window.title,locationUrl);
					}
					else {
						applyFilter(locationUrl);
					}
				}
			}
		});
		jQuery.each(jQuery.options('#m-filter-ajax').partialUrls, function(urlIndex, url) {
			if (!handled && locationUrl.indexOf(url) != -1) {
				var isException = false;
				jQuery.each(jQuery.options('#m-filter-ajax').urlExceptions, function(urlExceptionIndex, urlException) {
					if (!isException && locationUrl.indexOf(urlException) != -1) {
						isException = true;
					}
				});
				if (!isException) {
					handled = true;
                                        locationUrl = window.decodeURIComponent(locationUrl);
					if (window.History && window.History.enabled) {
						window.History.pushState(null,window.title,locationUrl);
					}
					else {
						applyFilter(locationUrl);
					}
				}
			}
		});
		if (!handled) {
			oldSetLocation(locationUrl);
		}
	}
	// the following function is executed when DOM ir ready. If not use this wrapper, code inside could fail if
	// executed when referenced DOM elements are still being loaded.


	jQuery(function() {
    		rebind();
    		if (window.setLocation) {
    			oldSetLocation = window.setLocation;
    			window.setLocation = setLocation;
   		}
	});

	
})(jQuery);
