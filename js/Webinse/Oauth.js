function PopOauthWindow(e){
    if($('ajaxLogin').value=='ajax'){
        new Ajax.Request(e.href, {
            method: 'post',
            onSuccess: function(transport) {
                var response = transport.responseText;
                showPopup(response);
                return false;
            }
        });
    }
    else{
        window.location= e.href;
    }
return true;
};


function showPopup(data) {
    oPopup = new Window({id: 'modal_window',className: 'magento', width:400, height:700, destroyOnClose: true,
        showEffectOptions: {
        duration: 0
    },
        hideEffectOptions:{
            duration: 0.6
        }
    });
    oPopup.getContent().update(data);
    oPopup.setZIndex(2000);
    oPopup.showCenter(true);

}


function formModalValidation(e){

    var validator = new Validation(e.id);

    Validation.add('validate-formLoginModal','',function(v){
        return true;
    });

    if(validator.validate()) {

        new Ajax.Request(e.title, {
            method: 'post',
            parameters:{email: $('email').value, password: $('pass').value},
            onSuccess: function(transport) {
                var response = transport.responseJSON;
                if(response['error']){
                    Validation.add('validate-formLoginModal',response['error'],function(v){
                        return false;
                    });
                    validator.validate();
                }
                if(response['href']){

                    window.location.href = response['href'];
                }
            }
        });

    }

}

function ModalFormCreate(element){
    var validator = new Validation(element.id);

    Validation.add('validate-formCreateModal','',function(v){
        return true;
    });

    if(validator.validate()) {

        new Ajax.Request(element.title, {
            method: 'post',
            parameters:{email: $('emailReg').value, password: $('password').value, firstname: $('firstname').value, lastname: $('lastname').value, confirmation: $('confirmation').value, persistent_remember_me: $('is_subscribed').value},
            onSuccess: function(transport) {
                var response = transport.responseJSON;
                if(response['error']){
                    Validation.add('validate-formCreateModal',response['error'],function(v){
                        return false;
                    });
                    validator.validate();
                }
                if(response['href']){

                    window.location.href = response['href'];
                }
            }
        });

    }
}

function CreateAccountShow(){
    $('modal-login-form-main').hide();
    $('modal-create-form-main').show();
}
function CreateAccountHide(){
    $('modal-login-form-main').show();
    $('modal-create-form-main').hide();
}


