var mageBaseUrl = '';

function isEmail(email) {
    var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return regex.test(email);
}

function removeValidation(element) {
    element.removeClass('validation-failed');
    if(element.next().hasClass('validation-advice'))
        element.next().remove();
}
function validatePhone() {
    if (jQuery('.format-phone').val().indexOf('_') == -1 ) {
        jQuery('.format-phone').css('border-color','green');
        jQuery('.format-phone').css('color','black');
        jQuery('.format-phone').removeClass('validation-failed');
        if(jQuery('.format-phone').next().hasClass('validation-advice'))
            jQuery('.format-phone').next().remove();
    }
    else if(jQuery('.format-phone').val().match(/\d+/g) == null) {

        jQuery('.format-phone').css('color','black');
        jQuery('.format-phone').removeClass('validation-failed');
        if(jQuery('.format-phone').next().hasClass('validation-advice'))
            jQuery('.format-phone').next().remove();
    }
    else {
        jQuery('.format-phone').css('border-color','red');
        jQuery('.format-phone').css('color','red');
        jQuery('.format-phone').addClass('validation-failed');
    }
}



jQuery(document).ready(function(){
    var office_service =  jQuery('#shipping_method');
    jQuery('.format-phone').mask('+38(999) 999-9999');


	
    jQuery('.autocomplete').live("change", function(e) {
        var url = mageBaseUrl + '/novaposhta/ajax/warehouse';
        if(jQuery('#shipping_method_choice').val() == "store_pickup_city_") {
            url = mageBaseUrl + '/store_pickup/ajax/warehouse';
            jQuery("#billing_city").val(jQuery('.autocomplete  option:selected').text());
        }
        else {
            url = mageBaseUrl + '/novaposhta/ajax/warehouse';
            jQuery("#billing_city").val(jQuery('.autocomplete').select2('data').text);
        }
        jQuery('.select2-choice').removeClass('validation-failed');
        removeValidation(jQuery("#billing_city"));
        jQuery('#ajax_loader_shipping').show();
        var data = 'isAjax=1&id='+jQuery(this).val();

        if(jQuery('#shipping_method_choice').val() == "novaposhta_warehouse_" || jQuery('#shipping_method_choice').val() == "store_pickup_city_")
        try {
            jQuery.ajax({
                url: url,
                dataType: 'json',
                type : 'post',
                data: data,
                success: function(response){
                    office_service.empty();
                    if(response.count > 0) {
                        office_service.removeAttr('disabled');
                        jQuery.each(response.items, function(i, value) {
                            office_service.append(jQuery('<option>').text(value.label).attr('value', value.id));
                        });
                        removeValidation(office_service);
                    }
                    else {
                        office_service.attr('disabled','disabled');
                    }
                    office_service.trigger('change');
                    if(jQuery('#shipping_method_choice').val() == "store_pickup_city_") {
                        jQuery("#billing_city").val(jQuery('.autocomplete  option:selected').text());
                    }
                    else {
                        jQuery("#billing_city").val(jQuery('.autocomplete').select2('data').text);
                    }
                    jQuery('#ajax_loader_shipping').hide();
                },
                complete: function(response){
                    jQuery('#ajax_loader_shipping').hide();
                }

            });
        } catch (e) {
            console.log(e);
        }
        jQuery('#ajax_loader_shipping').hide();
    });
    jQuery('#shipping_method_choice').change(function () {
        jQuery('#shipping_method').empty();
    });
    jQuery('select#shipping_method').live("change", function(e) {
        if(jQuery('#shipping_method_choice').val() == "store_pickup_city_") {
            jQuery("#billing_city").val(jQuery('.autocomplete  option:selected').text());
        }
        else {
            jQuery("#billing_city").val(jQuery('.autocomplete').select2('data').text);
        }
        if(jQuery(this).val().length && jQuery(this).hasClass("validation-failed")) {
            removeValidation(jQuery(this));
        }
    });
    jQuery('#billing-new-address-form input:text').blur(function() {
        jQuery(this).removeClass('validation-failed');
        if(jQuery(this).hasClass('validate-name')) {
            if (jQuery(this).val().length > 0 && jQuery(this).val().match(/\d+/g) == null) {
                jQuery(this).css('border-color','green');
                jQuery(this).css('color','black');
                jQuery(this).removeClass('validation-failed');
                if(jQuery(this).next().hasClass('validation-advice'))
                    jQuery(this).next().remove();
            }
            else if(jQuery(this).val().length == 0) {

                jQuery(this).css('color','black');
                jQuery(this).removeClass('validation-failed');
                if(jQuery(this).next().hasClass('validation-advice'))
                    jQuery(this).next().remove();
            }
            else {
                jQuery(this).css('border-color','red');
                jQuery(this).css('color','red');
                jQuery(this).addClass('validation-failed');
            }
        }
        if(jQuery(this).hasClass('validate-email')) {
            if (jQuery(this).val().length > 0 && isEmail(jQuery(this).val()) ) {
                jQuery(this).css('border-color','green');
                jQuery(this).css('color','black');
                removeValidation(jQuery(this));
            }
            else if(jQuery(this).val().length == 0) {

                jQuery(this).css('color','black');
                removeValidation(jQuery(this));
            }
            else {
                jQuery(this).css('border-color','red');
                jQuery(this).css('color','red');
                jQuery(this).addClass('validation-failed');
            }
        }

    });
});
