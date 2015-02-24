jQuery(document).ready(function($){
					jQuery('#vertnav').dcAccordion({
						eventType: 'click',
						autoClose: true,
						saveState: false,
						disableLink: false,
						speed: 'fast',
						autoExpand: true,
						classActive: 'vertnav-active',
						showCount: false
					});

});

jQuery(document).ready(function() {
//collapsible management
	jQuery('.collapsible-block').collapsible({
	    defaultOpen: 'recently-section'
	});
});
	
