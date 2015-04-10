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
    oPopup = new Window({id: 'modal_window',className: 'magento', width:400, height:515, destroyOnClose: true,
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
    $('modal_window_close').insert({before:'<div id="oblogka" class="mainWindow aling-central-modal" style="display: none; width: 100%;height: 100%; z-index:20000; position: absolute;margin-right: auto; margin-left: auto; background-color: #000000; opacity: 0.5"><i class="fa fa-spinner fa-pulse fa-5x" style="margin-top: 65%; color: #ffffff"></i></div>'});
}


function formModalValidation(e){

    var validator = new Validation(e.id);
    Validation.add('validate-formLoginModal','',function(v){
        return true;
    });
    Validation.add('validate-formLoginModalEm','',function(v){
        return true;
    });
    if(validator.validate()) {
        new Ajax.Request(e.title, {
            method: 'post',
            parameters:{email: $('email').value, password: $('pass').value},
            onSuccess: function(transport) {
                $('oblogka').hide();
                var response = transport.responseJSON;
                if(response['error']){
                    Validation.add('validate-formLoginModal',response['error'],function(v){
                        return false;
                    });

                }
                else{
                    Validation.add('validate-formLoginModal','',function(v){
                        return true;
                    });
                }
                if(response['erMail']){
                    Validation.add('validate-formLoginModalEm',response['erMail'],function(v){
                        return false;
                    });

                }
                else{
                    Validation.add('validate-formLoginModalEm','',function(v){
                        return true;
                    });
                }
                validator.validate();

                if(response['href']){
                    window.location.href = response['href'];
                }
            },
            onLoading: function(){
                $('oblogka').show();
            }
        });

    }

}

function ModalFormCreate(element){
    var validator = new Validation(element.id);
    Validation.add('validate-formCreateModal','',function(v){
        return true;
    });
    Validation.add('validate-formCreateModalEm','',function(v){
        return true;
    });
    if(validator.validate()) {

        new Ajax.Request(element.title, {
            method: 'post',
            parameters:{email: $('emailReg').value, password: $('password').value, firstname: $('firstname').value, lastname: $('lastname').value, confirmation: $('confirmation').value, persistent_remember_me: $('is_subscribed').value},
            onSuccess: function(transport) {
                var response = transport.responseJSON;
                $('oblogka').hide();
                if(response['error']){
                    Validation.add('validate-formCreateModal',response['error'],function(v){
                        return false;
                    });
                }
                else{
                    Validation.add('validate-formCreateModal','',function(v){
                        return true;
                    });
                }
                if(response['erMail']){
                    Validation.add('validate-formCreateModalEm',response['erMail'],function(v){
                        return false;
                    });
                }
                else{
                    Validation.add('validate-formCreateModalEm','',function(v){
                        return true;
                    });
                }

                    validator.validate();

                if(response['href']){
                    window.location.href = response['href'];
                }
            },
            onLoading: function(){
                $('oblogka').show();
            }
        });

    }
}

function ForgotPassword(element){
    var validator = new Validation(element.id);

    if(validator.validate()) {

        new Ajax.Request(element.title, {
            method: 'post',
            parameters:{email: $('email_address').value},
            onSuccess: function(transport) {
                var response = transport.responseJSON;
                $('oblogka').hide();
                if(response['error']){
                    Validation.add('validate-ForgotPasswordModal',response['error'],function(v){
                        return false;
                    });

                }
                else{
                    Validation.add('validate-ForgotPasswordModal','',function(v){
                        return true;
                    });
                }
                validator.validate();

                if(response['message']){

                    $('ok_message').update(response['message']);
                    $('ok_message').setStyle({
                        color: 'green'
                    });
                    $('email_address').value='';
                    $('ok_message').innerHTML;
                }
            },
            onLoading: function(){
                $('oblogka').show();
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
function ForgotPasswordShow(){
    $('modal-forgot-password-form').show();
    $('modal-login-form-main').hide();
}
function ForgotPasswordHide(){
    $('modal-forgot-password-form').hide();
    $('modal-login-form-main').show();
}
