// Copyright CJM Creative Designs
// Function to switch list and grid view images

function listSwitcher(a, id, src, lk, extra, color) {
	
	//Set base image
	if (src) { $$('#the-' + id + ' a.product-image img').first().setAttribute("src", src); }
	if (extra) { $$('#the-' + id + ' a.product-image img').first().setAttribute("onmouseover", 'imageswitch(\'' + id + '\',\'' + extra + '\')'); }
	if (src) { $$('#the-' + id + ' a.product-image img').first().setAttribute("onmouseout", 'imageswitch(\'' + id + '\',\'' + src + '\')'); }
	$$('#the-' + id + ' div.coverlay').first().setAttribute("id", color);
	//Set selected swatch
	$('ul-attribute' + lk + '-' + id).select('img', 'div').invoke('removeClassName', 'swatchSelected');
	a.addClassName('swatchSelected'); 
        $$('#the-' + id + ' div.coverlay').first().setAttribute("id", color); 
	
}

function imageswitch(id, src)
{
if (src) { $$('#the-' + id + ' a.product-image img').first().setAttribute("src", src); }
}

/*function changecolor(id, attrid, color) { 

var elements = document.getElementById('ul-attribute' + attrid + '-' + id).children;

for (var i = 0; i < elements.length; i++)
    if (elements[i].id == 'a' + attrid + '-' + color)
        elements[i].click();
$$('#the-' + id + ' div.coverlay').first().setAttribute("id", color);  

}*/



function writeCookie(name,value,days) {
    var date, expires;
    if (days) {
        date = new Date();
        date.setTime(date.getTime()+(days*24*60*60*1000));
        expires = "; expires=" + date.toGMTString();
            }else{
        expires = "";
    }
    document.cookie = name + "=" + value + expires + "; path=/";
}





function setProductColor(id) {
var color = $$('#the-' + id + ' div.coverlay').first().id;
var productCurrentColor = id + '-' + color;
writeCookie('productcolors', productCurrentColor, '1');
}




