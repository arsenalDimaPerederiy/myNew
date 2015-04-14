if (!window.Modalbox)
    var Modalbox = new Object();
Modalbox.Methods = {overrideAlert: false, focusableElements: new Array, currFocused: 0, initialized: false, active: true, options: {title: "ModalBox Window", overlay: true, overlayClose: true, width: 355, height: 90, overlayOpacity: .65, overlayDuration: .25, slideDownDuration: .5, slideUpDuration: .5, resizeDuration: .25, inactiveFade: true, transitions: false, loadingString: "Please wait. Loading...", closeString: "Close window", closeValue: "×", params: {}, method: 'get', autoFocusing: false, aspnet: false}, _options: new Object, setOptions: function (options) {
    Object.extend(this.options, options || {});
}, _init: function (options) {
    Object.extend(this._options, this.options);
    this.setOptions(options);
    this.MBoverlay = new Element("div", {id: "MB_overlay", opacity: "0"});
    if (!this.options.overlay)
        $(this.MBoverlay).setStyle({display: 'none'});
    this.MBwindow = new Element("div", {id: "MB_window", style: "display: none"}).update(this.MBframe = new Element("div", {id: "MB_frame"}).update(this.MBheader = new Element("div", {id: "MB_header"}).update(this.MBcaption = new Element("div", {id: "MB_caption"}))));
    this.MBclose = new Element("a", {id: "MB_close", title: this.options.closeString, href: "#"}).update("<span>" + this.options.closeValue + "</span>");
    this.MBheader.insert({'bottom': this.MBclose});
    this.MBcontent = new Element("div", {id: "MB_content"}).update(this.MBloading = new Element("div", {id: "MB_loading"}).update(this.options.loadingString));
    this._loadingHeight = $(this.MBloading).getHeight();
    this.MBframe.insert({'bottom': this.MBcontent});
    var injectToEl = this.options.aspnet ? $(document.body).down('form') : $(document.body);
    injectToEl.insert({'top': this.MBwindow});
    injectToEl.insert({'top': this.MBoverlay});
    this.initScrollX = window.pageXOffset || document.body.scrollLeft || document.documentElement.scrollLeft;
    this.initScrollY = window.pageYOffset || document.body.scrollTop || document.documentElement.scrollTop;
    this.hideObserver = this._hide.bindAsEventListener(this);
    this.kbdObserver = this._kbdHandler.bindAsEventListener(this);
    this._initObservers();
    this.initialized = true;
}, setTitle: function (title) {
    if (title) {
        this.options.title = title;
        $(this.MBcaption).update(this.options.title);
    }
}, show: function (content, options) {
    if (this._pe)
        this._pe.stop();
    if (!this.initialized)this._init(options);
    this.content = content;
    this.setOptions(options);
    if (this.options.title)
        $(this.MBcaption).update(this.options.title); else {
        $(this.MBheader).hide();
        $(this.MBcaption).hide();
    }
    if (this.MBwindow.style.display == "none") {
        this._appear();
        this.event("onShow");
    }
    else {
        this._update();
        this.event("onUpdate");
    }
}, hide: function (options) {
    if (this._pe)
        this._pe.stop();
    if (this.initialized) {
        if (options && typeof options.element != 'function')Object.extend(this.options, options);
        this.event("beforeHide");
        if (this.options.transitions)
            Effect.SlideUp(this.MBwindow, {duration: this.options.slideUpDuration, transition: Effect.Transitions.sinoidal, afterFinish: this._deinit.bind(this)}); else {
            $(this.MBwindow).hide();
            this._deinit();
        }
    }
}, autoHide: function (seconds, options) {
    if (this._pe)
        this._pe.stop();
    this._pe = new PeriodicalExecuter(function (pe) {
        pe.stop();
        this.hide(options);
    }.bind(this), seconds);
}, _hide: function (event) {
    event.stop();
    if (event.element().id == 'MB_overlay' && !this.options.overlayClose)return false;
    this.hide();
}, alert: function (message) {
    var html = '<div class="MB_alert"><p>' + message + '</p><input type="button" onclick="Modalbox.hide()" value="OK" /></div>';
    Modalbox.show(html, {title: 'Alert: ' + document.title, width: 300});
}, _appear: function () {
    if (Prototype.Browser.IE && !navigator.appVersion.match(/\b7.0\b/) && this.options.overlay) {
        window.scrollTo(0, 0);
        this._prepareIE("100%", "hidden");
    }
    this._setWidth();
    this._setPosition();
    if (this.options.transitions) {
        $(this.MBoverlay).setStyle({opacity: 0});
        new Effect.Fade(this.MBoverlay, {from: 0, to: this.options.overlayOpacity, duration: this.options.overlayDuration, afterFinish: function () {
            new Effect.SlideDown(this.MBwindow, {duration: this.options.slideDownDuration, transition: Effect.Transitions.sinoidal, afterFinish: function () {
                this._setPosition();
                this.loadContent();
            }.bind(this)});
        }.bind(this)});
    } else {
        $(this.MBoverlay).setStyle({opacity: this.options.overlayOpacity});
        if (!this.options.overlay)
            $(this.MBoverlay).setStyle({display: 'none'});
        $(this.MBwindow).show();
        this._setPosition();
        this.loadContent();
    }
    this._setWidthAndPosition = this._setWidthAndPosition.bindAsEventListener(this);
    Event.observe(window, "resize", this._setWidthAndPosition);
}, resize: function (byWidth, byHeight, options) {
    var oWidth = $(this.MBoverlay).getWidth();
    var wHeight = $(this.MBwindow).getHeight();
    var wWidth = $(this.MBwindow).getWidth();
    var hHeight = $(this.MBheader).getHeight();
    var cHeight = $(this.MBcontent).getHeight();
    var newHeight = ((wHeight - hHeight + byHeight) < cHeight) ? (cHeight + hHeight) : (wHeight + byHeight);
    var newWidth = wWidth + byWidth;
    if (options)this.setOptions(options);
    if (this.options.transitions) {
        new Effect.Morph(this.MBwindow, {style: "width:" + newWidth + "px; height:" + newHeight + "px; left:" + ((oWidth - newWidth) / 2) + "px", duration: this.options.resizeDuration, beforeStart: function (fx) {
            fx.element.setStyle({overflow: "hidden"});
        }, afterFinish: function (fx) {
            fx.element.setStyle({overflow: "visible"});
            this.event("_afterResize");
            this.event("afterResize");
        }.bind(this)});
    } else {
        this.MBwindow.setStyle({width: newWidth + "px", height: newHeight + "px"});
        setTimeout(function () {
            this.event("_afterResize");
            this.event("afterResize");
        }.bind(this), 1);
    }
    this.MBwindow.setStyle({height: "auto"});
}, resizeToContent: function (options) {
    var byHeight = this.options.height - $(this.MBwindow).getHeight();
    if (byHeight != 0) {
        if (options)this.setOptions(options);
        Modalbox.resize(0, byHeight);
    }
}, resizeToInclude: function (element, options) {
    var el = $(element);
    var elHeight = el.getHeight() + parseInt(el.getStyle('margin-top'), 0) + parseInt(el.getStyle('margin-bottom'), 0) + parseInt(el.getStyle('border-top-width'), 0) + parseInt(el.getStyle('border-bottom-width'), 0);
    if (elHeight > 0) {
        if (options)this.setOptions(options);
        Modalbox.resize(0, elHeight);
    }
}, _update: function () {
    $(this.MBcontent).update($(this.MBloading).update(this.options.loadingString));
    $(this.MBcontent).setStyle({height: $(this.MBloading).getHeight() + 'px'});
    $(this.MBcontent).setStyle({height: 'auto'});
    this.loadContent();
}, loadContent: function () {
    if (this.event("beforeLoad") != false) {
        if (typeof this.content == 'string') {
            var htmlRegExp = new RegExp(/<\/?[^>]+>/gi);
            if (htmlRegExp.test(this.content)) {
                this._insertContent(this.content.stripScripts(), function () {
                    this.content.extractScripts().map(function (script) {
                        return eval(script.replace("<!--", "").replace("// -->", ""));
                    }.bind(window));
                }.bind(this));
            } else
                new Ajax.Request(this.content, {method: this.options.method.toLowerCase(), parameters: this.options.params, onSuccess: function (transport) {
                    if (this._pe)
                        this._pe.stop();
                    var response = new String(transport.responseText);
                    this._insertContent(transport.responseText.stripScripts(), function () {
                        try {
                            if (response != '') {
                                var script = '';
                                var scripts = response;
                                scripts = scripts.replace(/<script[^>]*>([\s\S]*?)<\/script>/gi, function () {
                                    if (scripts !== null)script += arguments[1] + '\n';
                                    return'';
                                });
                                if (script)(window.execScript) ? window.execScript(script) : window.setTimeout(script, 0);
                            }
                            return false;
                        }
                        catch (e) {
                        }
                    });
                }.bind(this), onException: function (instance, exception) {
                    Modalbox.hide();
                    throw('Modalbox Loading Error: ' + exception);
                }});
        } else if (typeof this.content == 'object') {
            this._insertContent(this.content);
        } else {
            Modalbox.hide();
            throw('Modalbox Parameters Error: Please specify correct URL or HTML element (plain HTML or object)');
        }
    }
}, _insertContent: function (content, callback) {
    $(this.MBcontent).hide().update("");
    if (typeof content == 'string') {
        this.MBcontent.update(new Element("div", {style: "display: none"}).update(content)).down().show();
    } else if (typeof content == 'object') {
        var _htmlObj = content.cloneNode(true);
        if (content.id)content.id = "MB_" + content.id;
        $(content).select('*[id]').each(function (el) {
            el.id = "MB_" + el.id;
        });
        this.MBcontent.update(_htmlObj).down('div').show();
        if (Prototype.Browser.IE)
            $$("#MB_content select").invoke('setStyle', {'visibility': ''});
    }
    if (this.options.height == this._options.height) {
        Modalbox.resize((this.options.width - $(this.MBwindow).getWidth()), $(this.MBcontent).getHeight() - $(this.MBwindow).getHeight() + $(this.MBheader).getHeight(), {afterResize: function () {
            setTimeout(function () {
                this._putContent(callback);
            }.bind(this), 1);
        }.bind(this)});
    } else {
        this._setWidth();
        this.MBcontent.setStyle({overflow: 'auto', height: $(this.MBwindow).getHeight() - $(this.MBheader).getHeight() - 13 + 'px'});
        setTimeout(function () {
            this._putContent(callback);
        }.bind(this), 1);
    }
}, _putContent: function (callback) {
    this.MBcontent.show();
    this.focusableElements = this._findFocusableElements();
    this._setFocus();
    if (callback != undefined)
        callback();
    this.event("afterLoad");
    var viewHeight = document.viewport.getHeight() * .9;
    this._contentHeight = $(this.MBcontent).getHeight();
    if (this._contentHeight > viewHeight)
        this.MBcontent.setStyle({overflow: 'auto', height: viewHeight - $(this.MBheader).getHeight() - 13 + 'px'});
}, activate: function (options) {
    this.setOptions(options);
    this.active = true;
    $(this.MBclose).observe("click", this.hideObserver);
    if (this.options.overlayClose)
        $(this.MBoverlay).observe("click", this.hideObserver);
    $(this.MBclose).show();
    if (this.options.transitions && this.options.inactiveFade)
        new Effect.Appear(this.MBwindow, {duration: this.options.slideUpDuration});
}, deactivate: function (options) {
    this.setOptions(options);
    this.active = false;
    $(this.MBclose).stopObserving("click", this.hideObserver);
    if (this.options.overlayClose)
        $(this.MBoverlay).stopObserving("click", this.hideObserver);
    $(this.MBclose).hide();
    if (this.options.transitions && this.options.inactiveFade)
        new Effect.Fade(this.MBwindow, {duration: this.options.slideUpDuration, to: .75});
}, _initObservers: function () {
    $(this.MBclose).observe("click", this.hideObserver);
    if (this.options.overlayClose)
        $(this.MBoverlay).observe("click", this.hideObserver);
    if (Prototype.Browser.Gecko)
        Event.observe(document, "keypress", this.kbdObserver); else
        Event.observe(document, "keydown", this.kbdObserver);
}, _removeObservers: function () {
    $(this.MBclose).stopObserving("click", this.hideObserver);
    if (this.options.overlayClose)
        $(this.MBoverlay).stopObserving("click", this.hideObserver);
    if (Prototype.Browser.Gecko)
        Event.stopObserving(document, "keypress", this.kbdObserver); else
        Event.stopObserving(document, "keydown", this.kbdObserver);
}, _setFocus: function () {
    if (this.focusableElements.length > 0 && this.options.autoFocusing == true) {
        var firstEl = this.focusableElements.find(function (el) {
            return el.tabIndex == 1;
        }) || this.focusableElements.first();
        this.currFocused = this.focusableElements.toArray().indexOf(firstEl);
        firstEl.focus();
    } else if ($(this.MBclose).visible())
        $(this.MBclose).focus();
}, _findFocusableElements: function () {
    this.MBcontent.select('input:not([type~=hidden]), select, textarea, button, a[href]').invoke('addClassName', 'MB_focusable');
    return this.MBcontent.select('.MB_focusable');
}, _kbdHandler: function (event) {
    var node = event.element();
    switch (event.keyCode) {
        case Event.KEY_TAB:
            event.stop();
            if (node != this.focusableElements[this.currFocused])
                this.currFocused = this.focusableElements.toArray().indexOf(node);
            if (!event.shiftKey) {
                if (this.currFocused == this.focusableElements.length - 1) {
                    this.focusableElements.first().focus();
                    this.currFocused = 0;
                } else {
                    this.currFocused++;
                    this.focusableElements[this.currFocused].focus();
                }
            } else {
                if (this.currFocused == 0) {
                    this.focusableElements.last().focus();
                    this.currFocused = this.focusableElements.length - 1;
                } else {
                    this.currFocused--;
                    this.focusableElements[this.currFocused].focus();
                }
            }
            break;
        case Event.KEY_ESC:
            if (this.active)this._hide(event);
            break;
        case 32:
            this._preventScroll(event);
            break;
        case 0:
            if (event.which == 32)this._preventScroll(event);
            break;
        case Event.KEY_UP:
        case Event.KEY_DOWN:
        case Event.KEY_PAGEDOWN:
        case Event.KEY_PAGEUP:
        case Event.KEY_HOME:
        case Event.KEY_END:
            if (Prototype.Browser.WebKit && !["textarea", "select"].include(node.tagName.toLowerCase()))
                event.stop(); else if ((node.tagName.toLowerCase() == "input" && ["submit", "button"].include(node.type)) || (node.tagName.toLowerCase() == "a"))
                event.stop();
            break;
    }
}, _preventScroll: function (event) {
    if (!["input", "textarea", "select", "button"].include(event.element().tagName.toLowerCase()))
        event.stop();
}, _deinit: function () {
    this._removeObservers();
    Event.stopObserving(window, "resize", this._setWidthAndPosition);
    if (this.options.transitions && this.options.overlay) {
        Effect.toggle(this.MBoverlay, 'appear', {duration: this.options.overlayDuration, afterFinish: this._removeElements.bind(this)});
    } else {
        this.MBoverlay.hide();
        this._removeElements();
    }
    $(this.MBcontent).setStyle({overflow: '', height: ''});
}, _removeElements: function () {
    $(this.MBoverlay).remove();
    $(this.MBwindow).remove();
    if (Prototype.Browser.IE && !navigator.appVersion.match(/\b7.0\b/)) {
        this._prepareIE("", "");
        window.scrollTo(this.initScrollX, this.initScrollY);
    }
    if (typeof this.content == 'object') {
        if (this.content.id && this.content.id.match(/MB_/)) {
            this.content.id = this.content.id.replace(/MB_/, "");
        }
        this.content.select('*[id]').each(function (el) {
            el.id = el.id.replace(/MB_/, "");
        });
    }
    this.initialized = false;
    this.event("afterHide");
    this.setOptions(this._options);
}, _setWidth: function () {
    $(this.MBwindow).setStyle({width: this.options.width + "px", height: this.options.height + "px"});
}, _setPosition: function () {
    $(this.MBwindow).setStyle({left: (($(this.MBoverlay).getWidth() - $(this.MBwindow).getWidth()) / 2) + "px"});
}, _setWidthAndPosition: function () {
    $(this.MBwindow).setStyle({width: this.options.width + "px"});
    var viewHeight = document.viewport.getHeight() * .9;
    if (this._contentHeight > viewHeight)
        this.MBcontent.setStyle({overflow: 'auto', height: viewHeight - $(this.MBheader).getHeight() - 13 + 'px'});
    this._setPosition();
}, _getScrollTop: function () {
    var theTop;
    if (document.documentElement && document.documentElement.scrollTop)
        theTop = document.documentElement.scrollTop; else if (document.body)
        theTop = document.body.scrollTop;
    return theTop;
}, _prepareIE: function (height, overflow) {
    $$('html, body').invoke('setStyle', {width: height, height: height, overflow: overflow});
    $$("select").invoke('setStyle', {'visibility': overflow});
}, event: function (eventName) {
    if (this.options[eventName]) {
        var returnValue = this.options[eventName]();
        this.options[eventName] = null;
        if (returnValue != undefined)
            return returnValue; else
            return true;
    }
    return true;
}};
Object.extend(Modalbox, Modalbox.Methods);
if (Modalbox.overrideAlert)window.alert = Modalbox.alert;