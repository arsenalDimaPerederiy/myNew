function PopOauthWindow(e){

    new Ajax.Request(e.title, {
        method: 'post',
        onSuccess: function(transport) {
            var response = transport.responseText;
            showPopup(response);
        }
    });

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
    oPopup.setZIndex(100);
    oPopup.showCenter(true);
}

/*
function OauthFunc(e){
    switch (e.class){
        case 'vk': VkOauth(e); break;
    }
}

function VkOauth(e){

}*/
