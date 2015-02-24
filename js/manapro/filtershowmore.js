/**
 * @category    Mana
 * @package     ManaPro_FilterShowMore
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
	var prefix = 'm-more-less-';
	var _inAjax = false;
	var _states = {};
	var _itemCounts = {};
	var _time = {};
	
	function _calculateHeights(l, code) {
		var heights = {less: 0, more: 0};
		l.children().each(function(index, item) {
			if (index < _itemCounts[code] || jQuery(item).hasClass('m-selected-ln-item')) {
				heights.less += jQuery(item).outerHeight(true);
			}
			heights.more += jQuery(item).outerHeight(true);
		});
		return heights;
	}
	function apply(code, withTransition) {
		var div = jQuery('#'+prefix+code);
		var l = div.parent().children().first();

        l.addClass('m-expandable-filter');
		if (_states[code]) {
			l.children().each(function(index, item) {
				if (! (index < _itemCounts[code] || jQuery(item).hasClass('m-selected-ln-item'))) {
					jQuery(item).show();
				}
			});

			var heights = _calculateHeights(l, code);
			if (withTransition) {
				l.animate({height: heights.more+'px'}, _time[code]);
			}
			else {
				l.height(heights.more);
			}
			div.find('.m-show-less-action').show();
			div.find('.m-show-more-action').hide();
		}
		else {
			l.children().each(function(index, item) {
				if (! (index < _itemCounts[code] || jQuery(item).hasClass('m-selected-ln-item'))) {
					jQuery(item).hide();
				}
			});
			
			var heights = _calculateHeights(l, code);
			if (withTransition) {
				l.animate({height: heights.less+'px'}, _time[code]);
			}
			else {
				l.height(heights.less);
			}
			div.find('.m-show-less-action').hide();
			div.find('.m-show-more-action').show();
		}
	}
	
	function clickHandler() {
		var code = jQuery(this).parent()[0].id;
		if (!code.match("^"+prefix)==prefix) {
			throw 'Unexpected show more/show less id';
		}
		code = code.substring(prefix.length);
		_states[code] = !_states[code];
		apply(code, true);
		return false;
	}
	
	jQuery(document).bind('m-show-more-reset', function(e, code, itemCount, showAll, time) {
		if (!_inAjax){
			_states[code] = showAll;
		}
		_itemCounts[code] = itemCount;
		_time[code] = time;
		apply(code, false);
	});
    jQuery(document).bind('m-filter-scroll-reset', function (e, code, itemCount) {
        _itemCounts[code] = itemCount;
        var div = jQuery('#' + prefix + code);
        var l = div.parent().children().first();

        l.addClass('m-scrollable-filter');
        var heights = _calculateHeights(l, code);
        l.height(heights.less);
    });

    jQuery(document).bind('m-ajax-before', function(e, selectors) {
		_inAjax = true;
	});
	jQuery(document).bind('m-ajax-after', function(e, selectors) {
		for (var code in _states) {
			apply(code, false);
		}
		_inAjax = false;
	});
	jQuery('a.m-show-less-action').live('click', clickHandler);
	jQuery('a.m-show-more-action').live('click', clickHandler);
})(jQuery);
