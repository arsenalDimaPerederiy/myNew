function PopOauthWindow(e){

    new Ajax.Request(e.href, {
        method: 'post',
        onSuccess: function(transport) {
            var response = transport.responseText;
            showPopup(response);
            return false;
        },
        onLoading: function(){
            $('modal_window_content').insert('Onload');
        }
    });
    return false;
};


function showPopup(data) {
    oPopup = new Window({id: 'modal_window',className: 'magento', width:300, height:450, destroyOnClose: true,
        showEffectOptions: {
        duration: 0
    },
        hideEffectOptions:{
            duration: 0.6
        }
    });
    oPopup.getContent().update(data);
    oPopup.setZIndex(200);
    oPopup.showCenter(true);

}


/*
function CheckOauthCookie(e){

    new Ajax.Request(e.title, {
        method: 'post',
        parameters:{socialId: e.id},
        onSuccess: function(transport) {
            var response = transport.responseText;
            if(response=='0'){
                window.location= e.href;
            }
            else
            {
                window.location=response;
            }
        }
    });


};*/
