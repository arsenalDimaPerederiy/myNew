/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


            function showMainCate(rowId,proId){
                if($(rowId)){
                $(rowId).parentNode.parentNode.show();
                $(rowId).disabled = false;
                $(rowId).removeClassName('disabled');
                }
                if($(proId)){
		$(proId).parentNode.parentNode.hide();
                $(proId).disabled = true;
		$(proId).addClassName('disabled');
                }
            }
			
            function showMainProduct(rowId,proId){
                if($(rowId)){
                $(rowId).parentNode.parentNode.hide();
                $(rowId).disabled = true;
                $(rowId).addClassName('disabled');
                }
                if($(proId)){
                $(proId).parentNode.parentNode.show();
                $(proId).disabled = false;
		$(proId).removeClassName('disabled');
                }
            }
            
            function hideMaincontentGroup(rowId){
                if($(rowId)){
                $(rowId).parentNode.hide();
                $(rowId).disabled = true;
                $(rowId).addClassName('disabled');
                }
            }
            
            function showMaincontentGroup(rowId){
                if($(rowId)){
                $(rowId).parentNode.show();
                $(rowId).disabled = false;
                $(rowId).removeClassName('disabled');
                }
            }
			
    function reloadTemplate(value,url,template_value,el,load_url){
        if(value == -1){
            hideMaincontentGroup('megamenu_maincontent');
            return;
        }
        if(value == 1 || value == 4){
            hideMaincontentGroup('megamenu_maincontent');
        }

         if(value == 3){
             showMaincontentGroup('megamenu_maincontent');
            showMainProduct('products','categories');
         }

         if(value == 2){
             showMaincontentGroup('megamenu_maincontent');
             showMainCate('products','categories');
         }
        this.variables = null;
        new Ajax.Request(url, {
            parameters: $('menu_type').serialize(true),
            onComplete: function (transport) {
                    if (transport.responseText.isJSON()) {
                        var field = transport.responseText.evalJSON();
                        var i=0;
                         var el = document.getElementById('template_id');
                         while( el.hasChildNodes() ){
                             el.removeChild(el.lastChild);
                         }
                         for(i=0;i<field.length;i++){
                             var newOption = document.createElement('option');
                              newOption.className='option template_id';
                              newOption.text = field[i].name;
                              newOption.value = field[i].id;
                             // newOption.innerHTML = '<option value="'+field[i].value+'">'+newOption+'</option>';
                             $('template_id').appendChild(newOption);
                         }
                          if(template_value){
                            $('template_id').value = template_value;
                          }
                          if(el != null && load_url != null && template_value != null)
                          loadTemplate(el,load_url,template_value);
                    }
            }.bind(this)
        });
    }
    
    function loadTemplate(el,url,template_value){
        var value= el.value;
        var request = new Ajax.Request(url,{
            parameters: $('template_id').serialize(true),
            onComplete:function(transport){
                var result = JSON.parse(transport.responseText);
                if(result.template_map)
                    $('layout').innerHTML = result.template_map;
                if(result.headerfooter){
                    if(result.headerfooter.header)
                        tinymce.get('header').setContent(result.headerfooter.header);
                    if(result.headerfooter.footer)
                        tinymce.get('footer').setContent(result.headerfooter.footer);
                }
                if(!template_value){
                if(result.general_style){
                    var default_style = result.general_style;
                    $('background_color').value = default_style.background_color.substr(1);
                    $('border_color').value = default_style.border_color.substr(1);
                    $('border_size').value = default_style.border_size;
                }
                
                if(result.title_style){
                    var title_style = result.title_style;
                    $('title_color').value = title_style.title_color.substr(1);
                    $('title_background_color').value = title_style.title_background_color.substr(1);
                    $('title_font').value = title_style.title_font;
                    $('title_font_size').value = title_style.title_font_size;
                }
                
                if(result.subtitle_style){
                    var subtitle_style = result.subtitle_style;
                    $('subtitle_color').value = subtitle_style.subtitle_color.substr(1);
                    $('subtitle_font').value = subtitle_style.subtitle_font;
                    $('subtitle_font_size').value = subtitle_style.subtitle_font_size;
                }
                
                if(result.link_style){
                    var link_style = result.link_style;
                    $('link_color').value = link_style.link_color.substr(1);
                    $('hover_color').value = link_style.hover_color.substr(1);
                    $('link_font').value = link_style.link_font;
                    $('link_font_size').value = link_style.link_font_size;
                }
                
                if(result.text_style){
                    var text_style = result.text_style;
                    $('text_color').value = text_style.text_color.substr(1);
                    $('text_font').value = text_style.text_font;
                    $('text_font_size').value = text_style.text_font_size;
                }   
                    loadColor();
                }
            }
        })
    }
    
    function json_decode(str_json) {
    // Decodes the JSON representation into a PHP value  
    // 
    // version: 901.2515
    // discuss at: http://phpjs.org/functions/json_decode
    // +      original by: Public Domain (http://www.json.org/json2.js)
    // + reimplemented by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // *     example 1: json_decode('[\n    "e",\n    {\n    "pluribus": "unum"\n}\n]');
    // *     returns 1: ['e', {pluribus: 'unum'}]
    /*
        http://www.JSON.org/json2.js
        2008-11-19
        Public Domain.
        NO WARRANTY EXPRESSED OR IMPLIED. USE AT YOUR OWN RISK.
        See http://www.JSON.org/js.html
    */

    var cx = /[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g;
    var j;
    var text = str_json;

    var walk = function(holder, key) {
        // The walk method is used to recursively walk the resulting structure so
        // that modifications can be made.
        var k, v, value = holder[key];
        if (value && typeof value === 'object') {
            for (k in value) {
                if (Object.hasOwnProperty.call(value, k)) {
                    v = walk(value, k);
                    if (v !== undefined) {
                        value[k] = v;
                    } else {
                        delete value[k];
                    }
                }
            }
        }
        return reviver.call(holder, key, value);
    }

    // Parsing happens in four stages. In the first stage, we replace certain
    // Unicode characters with escape sequences. JavaScript handles many characters
    // incorrectly, either silently deleting them, or treating them as line endings.
    cx.lastIndex = 0;
    if (cx.test(text)) {
        text = text.replace(cx, function (a) {
            return '\\u' +
            ('0000' + a.charCodeAt(0).toString(16)).slice(-4);
        });
    }

    // In the second stage, we run the text against regular expressions that look
    // for non-JSON patterns. We are especially concerned with '()' and 'new'
    // because they can cause invocation, and '=' because it can cause mutation.
    // But just to be safe, we want to reject all unexpected forms.

    // We split the second stage into 4 regexp operations in order to work around
    // crippling inefficiencies in IE's and Safari's regexp engines. First we
    // replace the JSON backslash pairs with '@' (a non-JSON character). Second, we
    // replace all simple value tokens with ']' characters. Third, we delete all
    // open brackets that follow a colon or comma or that begin the text. Finally,
    // we look to see that the remaining characters are only whitespace or ']' or
    // ',' or ':' or '{' or '}'. If that is so, then the text is safe for eval.
    if (/^[\],:{}\s]*$/.
        test(text.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g, '@').
            replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']').
            replace(/(?:^|:|,)(?:\s*\[)+/g, ''))) {

        // In the third stage we use the eval function to compile the text into a
        // JavaScript structure. The '{' operator is subject to a syntactic ambiguity
        // in JavaScript: it can begin a block or an object literal. We wrap the text
        // in parens to eliminate the ambiguity.

        j = eval('(' + text + ')');

        // In the optional fourth stage, we recursively walk the new structure, passing
        // each name/value pair to a reviver function for possible transformation.

        return typeof reviver === 'function' ?
        walk({
            '': j
        }, '') : j;
    }

    // If the text is not JSON parseable, then a SyntaxError is thrown.
    throw new SyntaxError('json_decode');
}


