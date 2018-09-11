
/*! netteForms.js | (c) 2004 David Grudl (https://davidgrudl.com) */
(function(e,p){if(e.JSON)if("function"===typeof define&&define.amd)define(function(){return p(e)});else if("object"===typeof module&&"object"===typeof module.exports)module.exports=p(e);else{var d=!e.Nette||!e.Nette.noInit;e.Nette=p(e);d&&e.Nette.initOnLoad()}})("undefined"!==typeof window?window:this,function(e){function p(a){return function(b){return a.call(this,b)}}var d={formErrors:[],version:"2.4",addEvent:function(a,b,c){"DOMContentLoaded"===b&&"loading"!==a.readyState?c.call(this):a.addEventListener?
a.addEventListener(b,c):"DOMContentLoaded"===b?a.attachEvent("onreadystatechange",function(){"complete"===a.readyState&&c.call(this)}):a.attachEvent("on"+b,p(c))},getValue:function(a){var b;if(a){if(a.tagName){if("radio"===a.type){var c=a.form.elements;for(b=0;b<c.length;b++)if(c[b].name===a.name&&c[b].checked)return c[b].value;return null}if("file"===a.type)return a.files||a.value;if("select"===a.tagName.toLowerCase()){b=a.selectedIndex;c=a.options;var f=[];if("select-one"===a.type)return 0>b?null:
c[b].value;for(b=0;b<c.length;b++)c[b].selected&&f.push(c[b].value);return f}if(a.name&&a.name.match(/\[\]$/)){c=a.form.elements[a.name].tagName?[a]:a.form.elements[a.name];f=[];for(b=0;b<c.length;b++)("checkbox"!==c[b].type||c[b].checked)&&f.push(c[b].value);return f}return"checkbox"===a.type?a.checked:"textarea"===a.tagName.toLowerCase()?a.value.replace("\r",""):a.value.replace("\r","").replace(/^\s+|\s+$/g,"")}return a[0]?d.getValue(a[0]):null}return null},getEffectiveValue:function(a){var b=d.getValue(a);
a.getAttribute&&b===a.getAttribute("data-nette-empty-value")&&(b="");return b},validateControl:function(a,b,c,f,r){a=a.tagName?a:a[0];b=b||d.parseJSON(a.getAttribute("data-nette-rules"));f=void 0===f?{value:d.getEffectiveValue(a)}:f;for(var g=0,l=b.length;g<l;g++){var h=b[g],k=h.op.match(/(~)?([^?]+)/),e=h.control?a.form.elements.namedItem(h.control):a;h.neg=k[1];h.op=k[2];h.condition=!!h.rules;if(e)if("optional"===h.op)r=!d.validateRule(a,":filled",null,f);else if(!r||h.condition||":filled"===h.op)if(e=
e.tagName?e:e[0],k=a===e?f:{value:d.getEffectiveValue(e)},k=d.validateRule(e,h.op,h.arg,k),null!==k)if(h.neg&&(k=!k),h.condition&&k){if(!d.validateControl(a,h.rules,c,f,":blank"===h.op?!1:r))return!1}else if(!h.condition&&!k&&!d.isDisabled(e)){if(!c){var p=d.isArray(h.arg)?h.arg:[h.arg];b=h.msg.replace(/%(value|\d+)/g,function(b,c){return d.getValue("value"===c?e:a.form.elements.namedItem(p[c].control))});d.addError(e,b)}return!1}}return"number"!==a.type||a.validity.valid?!0:(c||d.addError(a,"Please enter a valid value."),
!1)},validateForm:function(a,b){var c=a.form||a,f=!1;d.formErrors=[];if(c["nette-submittedBy"]&&null!==c["nette-submittedBy"].getAttribute("formnovalidate"))if(f=d.parseJSON(c["nette-submittedBy"].getAttribute("data-nette-validation-scope")),f.length)f=new RegExp("^("+f.join("-|")+"-)");else return d.showFormErrors(c,[]),!0;var e={},g;for(g=0;g<c.elements.length;g++){var l=c.elements[g];if(!l.tagName||l.tagName.toLowerCase()in{input:1,select:1,textarea:1,button:1}){if("radio"===l.type){if(e[l.name])continue;
e[l.name]=!0}if(!(f&&!l.name.replace(/]\[|\[|]|$/g,"-").match(f)||d.isDisabled(l)||d.validateControl(l,null,b)||d.formErrors.length))return!1}}f=!d.formErrors.length;d.showFormErrors(c,d.formErrors);return f},isDisabled:function(a){if("radio"===a.type){for(var b=0,c=a.form.elements;b<c.length;b++)if(c[b].name===a.name&&!c[b].disabled)return!1;return!0}return a.disabled},addError:function(a,b){d.formErrors.push({element:a,message:b})},showFormErrors:function(a,b){for(var c=[],f,e=0;e<b.length;e++){var g=
b[e].element,l=b[e].message;d.inArray(c,l)||(c.push(l),!f&&g.focus&&(f=g))}c.length&&(alert(c.join("\n")),f&&f.focus())},expandRuleArgument:function(a,b){if(b&&b.control){var c=a.elements.namedItem(b.control),f={value:d.getEffectiveValue(c)};d.validateControl(c,null,!0,f);b=f.value}return b}},t=!1;d.validateRule=function(a,b,c,f){f=void 0===f?{value:d.getEffectiveValue(a)}:f;":"===b.charAt(0)&&(b=b.substr(1));b=b.replace("::","_");b=b.replace(/\\/g,"");var e=d.isArray(c)?c.slice(0):[c];if(!t){t=!0;
for(var g=0,l=e.length;g<l;g++)e[g]=d.expandRuleArgument(a.form,e[g]);t=!1}return d.validators[b]?d.validators[b](a,d.isArray(c)?e:e[0],f.value,f):null};d.validators={filled:function(a,b,c){return"number"===a.type&&a.validity.badInput?!0:""!==c&&!1!==c&&null!==c&&(!d.isArray(c)||!!c.length)&&(!e.FileList||!(c instanceof e.FileList)||c.length)},blank:function(a,b,c){return!d.validators.filled(a,b,c)},valid:function(a){return d.validateControl(a,null,!0)},equal:function(a,b,c){function f(a){return"number"===
typeof a||"string"===typeof a?""+a:!0===a?"1":""}if(void 0===b)return null;c=d.isArray(c)?c:[c];b=d.isArray(b)?b:[b];a=0;var e=c.length;a:for(;a<e;a++){for(var g=0,l=b.length;g<l;g++)if(f(c[a])===f(b[g]))continue a;return!1}return!0},notEqual:function(a,b,c){return void 0===b?null:!d.validators.equal(a,b,c)},minLength:function(a,b,c){if("number"===a.type){if(a.validity.tooShort)return!1;if(a.validity.badInput)return null}return c.length>=b},maxLength:function(a,b,c){if("number"===a.type){if(a.validity.tooLong)return!1;
if(a.validity.badInput)return null}return c.length<=b},length:function(a,b,c){if("number"===a.type){if(a.validity.tooShort||a.validity.tooLong)return!1;if(a.validity.badInput)return null}b=d.isArray(b)?b:[b,b];return(null===b[0]||c.length>=b[0])&&(null===b[1]||c.length<=b[1])},email:function(a,b,c){return/^("([ !#-[\]-~]|\\[ -~])+"|[-a-z0-9!#$%&'*+/=?^_`{|}~]+(\.[-a-z0-9!#$%&'*+/=?^_`{|}~]+)*)@([0-9a-z\u00C0-\u02FF\u0370-\u1EFF]([-0-9a-z\u00C0-\u02FF\u0370-\u1EFF]{0,61}[0-9a-z\u00C0-\u02FF\u0370-\u1EFF])?\.)+[a-z\u00C0-\u02FF\u0370-\u1EFF]([-0-9a-z\u00C0-\u02FF\u0370-\u1EFF]{0,17}[a-z\u00C0-\u02FF\u0370-\u1EFF])?$/i.test(c)},
url:function(a,b,c,d){/^[a-z\d+.-]+:/.test(c)||(c="http://"+c);return/^https?:\/\/((([-_0-9a-z\u00C0-\u02FF\u0370-\u1EFF]+\.)*[0-9a-z\u00C0-\u02FF\u0370-\u1EFF]([-0-9a-z\u00C0-\u02FF\u0370-\u1EFF]{0,61}[0-9a-z\u00C0-\u02FF\u0370-\u1EFF])?\.)?[a-z\u00C0-\u02FF\u0370-\u1EFF]([-0-9a-z\u00C0-\u02FF\u0370-\u1EFF]{0,17}[a-z\u00C0-\u02FF\u0370-\u1EFF])?|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}|\[[0-9a-f:]{3,39}\])(:\d{1,5})?(\/\S*)?$/i.test(c)?(d.value=c,!0):!1},regexp:function(a,b,c){a="string"===typeof b?b.match(/^\/(.*)\/([imu]*)$/):
!1;try{return a&&(new RegExp(a[1],a[2].replace("u",""))).test(c)}catch(f){}},pattern:function(a,b,c){try{return"string"===typeof b?(new RegExp("^(?:"+b+")$")).test(c):null}catch(f){}},integer:function(a,b,c){return"number"===a.type&&a.validity.badInput?!1:/^-?[0-9]+$/.test(c)},"float":function(a,b,c,d){if("number"===a.type&&a.validity.badInput)return!1;c=c.replace(/ +/g,"").replace(/,/g,".");return/^-?[0-9]*\.?[0-9]+$/.test(c)?(d.value=c,!0):!1},min:function(a,b,c){if("number"===a.type){if(a.validity.rangeUnderflow)return!1;
if(a.validity.badInput)return null}return null===b||parseFloat(c)>=b},max:function(a,b,c){if("number"===a.type){if(a.validity.rangeOverflow)return!1;if(a.validity.badInput)return null}return null===b||parseFloat(c)<=b},range:function(a,b,c){if("number"===a.type){if(a.validity.rangeUnderflow||a.validity.rangeOverflow)return!1;if(a.validity.badInput)return null}return d.isArray(b)?(null===b[0]||parseFloat(c)>=b[0])&&(null===b[1]||parseFloat(c)<=b[1]):null},submitted:function(a){return a.form["nette-submittedBy"]===
a},fileSize:function(a,b,c){if(e.FileList)for(a=0;a<c.length;a++)if(c[a].size>b)return!1;return!0},image:function(a,b,c){if(e.FileList&&c instanceof e.FileList)for(a=0;a<c.length;a++)if((b=c[a].type)&&"image/gif"!==b&&"image/png"!==b&&"image/jpeg"!==b)return!1;return!0},"static":function(a,b){return b}};d.toggleForm=function(a,b){var c;d.toggles={};for(c=0;c<a.elements.length;c++)a.elements[c].tagName.toLowerCase()in{input:1,select:1,textarea:1,button:1}&&d.toggleControl(a.elements[c],null,null,!b);
for(c in d.toggles)d.toggle(c,d.toggles[c],b)};d.toggleControl=function(a,b,c,f,e){b=b||d.parseJSON(a.getAttribute("data-nette-rules"));e=void 0===e?{value:d.getEffectiveValue(a)}:e;for(var g=!1,l=[],h=function(){d.toggleForm(a.form,a)},k,p=0,r=b.length;p<r;p++){var n=b[p],u=n.op.match(/(~)?([^?]+)/),m=n.control?a.form.elements.namedItem(n.control):a;if(m){k=c;if(!1!==c){n.neg=u[1];n.op=u[2];k=a===m?e:{value:d.getEffectiveValue(m)};k=d.validateRule(m,n.op,n.arg,k);if(null===k)continue;else n.neg&&
(k=!k);n.rules||(c=k)}if(n.rules&&d.toggleControl(a,n.rules,k,f,e)||n.toggle){g=!0;if(f){u=!document.addEventListener;var t=m.tagName?m.name:m[0].name;m=m.tagName?m.form.elements:m;for(var q=0;q<m.length;q++)m[q].name!==t||d.inArray(l,m[q])||(d.addEvent(m[q],u&&m[q].type in{checkbox:1,radio:1}?"click":"change",h),l.push(m[q]))}for(var v in n.toggle||[])Object.prototype.hasOwnProperty.call(n.toggle,v)&&(d.toggles[v]=d.toggles[v]||(n.toggle[v]?k:!k))}}}return g};d.parseJSON=function(a){return"{op"===
(a||"").substr(0,3)?eval("["+a+"]"):JSON.parse(a||"[]")};d.toggle=function(a,b,c){if(a=document.getElementById(a))a.style.display=b?"":"none"};d.initForm=function(a){d.toggleForm(a);a.noValidate||(a.noValidate=!0,d.addEvent(a,"submit",function(b){d.validateForm(a)||(b&&b.stopPropagation?(b.stopPropagation(),b.preventDefault()):e.event&&(event.cancelBubble=!0,event.returnValue=!1))}))};d.initOnLoad=function(){d.addEvent(document,"DOMContentLoaded",function(){for(var a=0;a<document.forms.length;a++)for(var b=
document.forms[a],c=0;c<b.elements.length;c++)if(b.elements[c].getAttribute("data-nette-rules")){d.initForm(b);break}d.addEvent(document.body,"click",function(a){for(a=a.target||a.srcElement;a;){if(a.form&&a.type in{submit:1,image:1}){a.form["nette-submittedBy"]=a;break}a=a.parentNode}})})};d.isArray=function(a){return"[object Array]"===Object.prototype.toString.call(a)};d.inArray=function(a,b){if([].indexOf)return-1<a.indexOf(b);for(var c=0;c<a.length;c++)if(a[c]===b)return!0;return!1};d.webalize=
function(a){a=a.toLowerCase();var b="",c;for(c=0;c<a.length;c++){var e=d.webalizeTable[a.charAt(c)];b+=e?e:a.charAt(c)}return b.replace(/[^a-z0-9]+/g,"-").replace(/^-|-$/g,"")};d.webalizeTable={"\u00e1":"a","\u00e4":"a","\u010d":"c","\u010f":"d","\u00e9":"e","\u011b":"e","\u00ed":"i","\u013e":"l","\u0148":"n","\u00f3":"o","\u00f4":"o","\u0159":"r","\u0161":"s","\u0165":"t","\u00fa":"u","\u016f":"u","\u00fd":"y","\u017e":"z"};return d});

/**
 * AJAX Nette Framework plugin for jQuery
 *
 * @copyright Copyright (c) 2009, 2010 Jan Marek
 * @copyright Copyright (c) 2009, 2010 David Grudl
 * @copyright Copyright (c) 2012-2014 Vojtěch Dobeš
 * @license MIT
 *
 * @version 2.3.0
 */

(function(window, $, undefined) {

if (typeof $ !== 'function') {
	return console.error('nette.ajax.js: jQuery is missing, load it please');
}

var nette = function () {
	var inner = {
		self: this,
		initialized: false,
		contexts: {},
		on: {
			init: {},
			load: {},
			prepare: {},
			before: {},
			start: {},
			success: {},
			complete: {},
			error: {}
		},
		fire: function () {
			var result = true;
			var args = Array.prototype.slice.call(arguments);
			var props = args.shift();
			var name = (typeof props === 'string') ? props : props.name;
			var off = (typeof props === 'object') ? props.off || {} : {};
			args.push(inner.self);
			$.each(inner.on[name], function (index, reaction) {
				if (reaction === undefined || $.inArray(index, off) !== -1) return true;
				var temp = reaction.apply(inner.contexts[index], args);
				return result = (temp === undefined || temp);
			});
			return result;
		},
		requestHandler: function (e) {
			var xhr = inner.self.ajax({}, this, e);
			if (xhr && xhr._returnFalse) { // for IE 8
				return false;
			}
		},
		ext: function (callbacks, context, name) {
			while (!name) {
				name = 'ext_' + Math.random();
				if (inner.contexts[name]) {
					name = undefined;
				}
			}

			$.each(callbacks, function (event, callback) {
				inner.on[event][name] = callback;
			});
			inner.contexts[name] = $.extend(context ? context : {}, {
				name: function () {
					return name;
				},
				ext: function (name, force) {
					var ext = inner.contexts[name];
					if (!ext && force) throw "Extension '" + this.name() + "' depends on disabled extension '" + name + "'.";
					return ext;
				}
			});
		}
	};

	/**
	 * Allows manipulation with extensions.
	 * When called with 1. argument only, it returns extension with given name.
	 * When called with 2. argument equal to false, it removes extension entirely.
	 * When called with 2. argument equal to hash of event callbacks, it adds new extension.
	 *
	 * @param  {string} Name of extension
	 * @param  {bool|object|null} Set of callbacks for any events OR false for removing extension.
	 * @param  {object|null} Context for added extension
	 * @return {$.nette|object} Provides a fluent interface OR returns extensions with given name
	 */
	this.ext = function (name, callbacks, context) {
		if (typeof name === 'object') {
			inner.ext(name, callbacks);
		} else if (callbacks === undefined) {
			return inner.contexts[name];
		} else if (!callbacks) {
			$.each(['init', 'load', 'prepare', 'before', 'start', 'success', 'complete', 'error'], function (index, event) {
				inner.on[event][name] = undefined;
			});
			inner.contexts[name] = undefined;
		} else if (typeof name === 'string' && inner.contexts[name] !== undefined) {
			throw "Cannot override already registered nette-ajax extension '" + name + "'.";
		} else {
			inner.ext(callbacks, context, name);
		}
		return this;
	};

	/**
	 * Initializes the plugin:
	 * - fires 'init' event, then 'load' event
	 * - when called with any arguments, it will override default 'init' extension
	 *   with provided callbacks
	 *
	 * @param  {function|object|null} Callback for 'load' event or entire set of callbacks for any events
	 * @param  {object|null} Context provided for callbacks in first argument
	 * @return {$.nette} Provides a fluent interface
	 */
	this.init = function (load, loadContext) {
		if (inner.initialized) throw 'Cannot initialize nette-ajax twice.';

		if (typeof load === 'function') {
			this.ext('init', null);
			this.ext('init', {
				load: load
			}, loadContext);
		} else if (typeof load === 'object') {
			this.ext('init', null);
			this.ext('init', load, loadContext);
		} else if (load !== undefined) {
			throw 'Argument of init() can be function or function-hash only.';
		}

		inner.initialized = true;

		inner.fire('init');
		this.load();
		return this;
	};

	/**
	 * Fires 'load' event
	 *
	 * @return {$.nette} Provides a fluent interface
	 */
	this.load = function () {
		inner.fire('load', inner.requestHandler);
		return this;
	};

	/**
	 * Executes AJAX request. Attaches listeners and events.
	 *
	 * @param  {object|string} settings or URL
	 * @param  {Element|null} ussually Anchor or Form
	 * @param  {event|null} event causing the request
	 * @return {jqXHR|null}
	 */
	this.ajax = function (settings, ui, e) {
		if ($.type(settings) === 'string') {
			settings = {url: settings};
		}
		if (!settings.nette && ui && e) {
			var $el = $(ui), xhr, originalBeforeSend;
			var analyze = settings.nette = {
				e: e,
				ui: ui,
				el: $el,
				isForm: $el.is('form'),
				isSubmit: $el.is('input[type=submit]') || $el.is('button[type=submit]'),
				isImage: $el.is('input[type=image]'),
				form: null
			};

			if (analyze.isSubmit || analyze.isImage) {
				analyze.form = analyze.el.closest('form');
			} else if (analyze.isForm) {
				analyze.form = analyze.el;
			}

			if (!settings.url) {
				settings.url = analyze.form ? analyze.form.attr('action') || window.location.pathname + window.location.search : ui.href;
			}
			if (!settings.type) {
				settings.type = analyze.form ? analyze.form.attr('method') : 'get';
			}

			if ($el.is('[data-ajax-off]')) {
				var rawOff = $el.attr('data-ajax-off');
				if (rawOff.indexOf('[') === 0) {
					settings.off = $el.data('ajaxOff');
				} else if (rawOff.indexOf(',') !== -1) {
					settings.off = rawOff.split(',');
				} else if (rawOff.indexOf(' ') !== -1) {
					settings.off = rawOff.split(' ');
				} else {
					settings.off = rawOff;
				}
				if (typeof settings.off === 'string') settings.off = [settings.off];
				settings.off = $.grep($.each(settings.off, function (off) {
					return $.trim(off);
				}), function (off) {
					return off.length;
				});
			}
		}

		inner.fire({
			name: 'prepare',
			off: settings.off || {}
		}, settings);
		if (settings.prepare) {
			settings.prepare(settings);
		}

		originalBeforeSend = settings.beforeSend;
		settings.beforeSend = function (xhr, settings) {
			var result = inner.fire({
				name: 'before',
				off: settings.off || {}
			}, xhr, settings);
			if ((result || result === undefined) && originalBeforeSend) {
				result = originalBeforeSend(xhr, settings);
			}
			return result;
		};

		return this.handleXHR($.ajax(settings), settings);
	};

	/**
	 * Binds extension callbacks to existing XHR object
	 *
	 * @param  {jqXHR|null}
	 * @param  {object} settings
	 * @return {jqXHR|null}
	 */
	this.handleXHR = function (xhr, settings) {
		settings = settings || {};

		if (xhr && (typeof xhr.statusText === 'undefined' || xhr.statusText !== 'canceled')) {
			xhr.done(function (payload, status, xhr) {
				inner.fire({
					name: 'success',
					off: settings.off || {}
				}, payload, status, xhr, settings);
			}).fail(function (xhr, status, error) {
				inner.fire({
					name: 'error',
					off: settings.off || {}
				}, xhr, status, error, settings);
			}).always(function (xhr, status) {
				inner.fire({
					name: 'complete',
					off: settings.off || {}
				}, xhr, status, settings);
			});
			inner.fire({
				name: 'start',
				off: settings.off || {}
			}, xhr, settings);
			if (settings.start) {
				settings.start(xhr, settings);
			}
		}
		return xhr;
	};
};

$.nette = new ($.extend(nette, $.nette ? $.nette : {}));

$.fn.netteAjax = function (e, options) {
	return $.nette.ajax(options || {}, this[0], e);
};

$.fn.netteAjaxOff = function () {
	return this.off('.nette');
};

$.nette.ext('validation', {
	before: function (xhr, settings) {
		if (!settings.nette) return true;
		else var analyze = settings.nette;
		var e = analyze.e;

		var validate = $.extend(this.defaults, settings.validate || (function () {
			if (!analyze.el.is('[data-ajax-validate]')) return;
			var attr = analyze.el.data('ajaxValidate');
			if (attr === false) return {
				keys: false,
				url: false,
				form: false
			}; else if (typeof attr === 'object') return attr;
 		})() || {});

		var passEvent = false;
		if (analyze.el.attr('data-ajax-pass') !== undefined) {
			passEvent = analyze.el.data('ajaxPass');
			passEvent = typeof passEvent === 'bool' ? passEvent : true;
		}

		if (validate.keys) {
			// thx to @vrana
			var explicitNoAjax = e.button || e.ctrlKey || e.shiftKey || e.altKey || e.metaKey;

			if (analyze.form) {
				if (explicitNoAjax && analyze.isSubmit) {
					this.explicitNoAjax = true;
					return false;
				} else if (analyze.isForm && this.explicitNoAjax) {
					this.explicitNoAjax = false;
					return false;
				}
			} else if (explicitNoAjax) return false;
		}

		if (validate.form && analyze.form) {
			if (analyze.isSubmit || analyze.isImage) {
				analyze.form.get(0)["nette-submittedBy"] = analyze.el.get(0);
			}
			var notValid;
			if ((typeof Nette.version === 'undefined' || Nette.version == '2.3')) { // Nette 2.3 and older
				var ie = this.ie();
				notValid = (analyze.form.get(0).onsubmit && analyze.form.get(0).onsubmit((typeof ie !== 'undefined' && ie < 9) ? undefined : e) === false);
			} else { // Nette 2.4 and up
				notValid = ((analyze.form.get(0).onsubmit ? analyze.form.triggerHandler('submit') : Nette.validateForm(analyze.form.get(0))) === false)
			}
			if (notValid) {
				e.stopImmediatePropagation();
				e.preventDefault();
				return false;
			}
		}

		if (validate.url) {
			// thx to @vrana
			var urlToValidate = analyze.form ? settings.url : analyze.el.attr('href');
			// Check if URL is absolute
			if (/(?:^[a-z][a-z0-9+.-]*:|\/\/)/.test(urlToValidate)) {
				// Parse absolute URL
				var parsedUrl = new URL(urlToValidate);
				if (/:|^#/.test(parsedUrl['pathname'] + parsedUrl['search'] + parsedUrl['hash'])) return false;
			} else {
				if (/:|^#/.test(urlToValidate)) return false;
			}
		}

		if (!passEvent) {
			e.stopPropagation();
			e.preventDefault();
			xhr._returnFalse = true; // for IE 8
		}
		return true;
	}
}, {
	defaults: {
		keys: true,
		url: true,
		form: true
	},
	explicitNoAjax: false,
	ie: function (undefined) { // http://james.padolsey.com/javascript/detect-ie-in-js-using-conditional-comments/
		var v = 3;
		var div = document.createElement('div');
		var all = div.getElementsByTagName('i');
		while (
        		div.innerHTML = '<!--[if gt IE ' + (++v) + ']><i></i><![endif]-->',
			all[0]
		);
		return v > 4 ? v : undefined;
	}
});

$.nette.ext('forms', {
	init: function () {
		var snippets;
		if (!window.Nette || !(snippets = this.ext('snippets'))) return;

		snippets.after(function ($el) {
			$el.find('form').each(function() {
				window.Nette.initForm(this);
			});
		});
	},
	prepare: function (settings) {
		var analyze = settings.nette;
		if (!analyze || !analyze.form) return;
		var e = analyze.e;
		var originalData = settings.data || {};
		var data = {};

		if (analyze.isSubmit) {
			data[analyze.el.attr('name')] = analyze.el.val() || '';
		} else if (analyze.isImage) {
			var offset = analyze.el.offset();
			var name = analyze.el.attr('name');
			var dataOffset = [ Math.max(0, e.pageX - offset.left), Math.max(0, e.pageY - offset.top) ];

			if (name.indexOf('[', 0) !== -1) { // inside a container
				data[name] = dataOffset;
			} else {
				data[name + '.x'] = dataOffset[0];
				data[name + '.y'] = dataOffset[1];
			}
		}
		
		// https://developer.mozilla.org/en-US/docs/Web/Guide/Using_FormData_Objects#Sending_files_using_a_FormData_object
		var formMethod = analyze.form.attr('method');
		if (formMethod && formMethod.toLowerCase() === 'post' && 'FormData' in window) {
			var formData = new FormData(analyze.form[0]);
			for (var i in data) {
				formData.append(i, data[i]);
			}

			if (typeof originalData !== 'string') {
				for (var i in originalData) {
					formData.append(i, originalData[i]);
				}
			}
			
			// remove empty file inputs as these causes Safari 11 to stall
			// https://stackoverflow.com/questions/49672992/ajax-request-fails-when-sending-formdata-including-empty-file-input-in-safari
			if (formData.entries && navigator.userAgent.match(/version\/11(\.[0-9]*)? safari/i)) {
				for (var pair of formData.entries()) {
					if (pair[1] instanceof File && pair[1].name === '' && pair[1].size === 0) {
						formData.delete(pair[0]);
					}
				}
			}

			settings.data = formData;
			settings.processData = false;
			settings.contentType = false;
		} else {
			if (typeof originalData !== 'string') {
				originalData = $.param(originalData);
			}
			data = $.param(data);
			settings.data = analyze.form.serialize() + (data ? '&' + data : '') + '&' + originalData;
		}
	}
});

// default snippet handler
$.nette.ext('snippets', {
	success: function (payload) {
		if (payload.snippets) {
			this.updateSnippets(payload.snippets);
		}
	}
}, {
	beforeQueue: $.Callbacks(),
	afterQueue: $.Callbacks(),
	completeQueue: $.Callbacks(),
	before: function (callback) {
		this.beforeQueue.add(callback);
	},
	after: function (callback) {
		this.afterQueue.add(callback);
	},
	complete: function (callback) {
		this.completeQueue.add(callback);
	},
	updateSnippets: function (snippets, back) {
		var that = this;
		var elements = [];
		for (var i in snippets) {
			var $el = this.getElement(i);
			if ($el.get(0)) {
				elements.push($el.get(0));
			}
			this.updateSnippet($el, snippets[i], back);
		}
		$(elements).promise().done(function () {
			that.completeQueue.fire();
		});
	},
	updateSnippet: function ($el, html, back) {
		// Fix for setting document title in IE
		if ($el.is('title')) {
			document.title = html;
		} else {
			this.beforeQueue.fire($el);
			this.applySnippet($el, html, back);
			this.afterQueue.fire($el);
		}
	},
	getElement: function (id) {
		return $('#' + this.escapeSelector(id));
	},
	applySnippet: function ($el, html, back) {
		if (!back && $el.is('[data-ajax-append]')) {
			$el.append(html);
		} else if (!back && $el.is('[data-ajax-prepend]')) {
			$el.prepend(html);
		} else if ($el.html() != html || /<[^>]*script/.test(html)) {
			$el.html(html);
		}
	},
	escapeSelector: function (selector) {
		// thx to @uestla (https://github.com/uestla)
		return selector.replace(/[\!"#\$%&'\(\)\*\+,\.\/:;<=>\?@\[\\\]\^`\{\|\}~]/g, '\\$&');
	}
});

// support $this->redirect()
$.nette.ext('redirect', {
	success: function (payload) {
		if (payload.redirect) {
			window.location.href = payload.redirect;
			return false;
		}
	}
});

// current page state
$.nette.ext('state', {
	success: function (payload) {
		if (payload.state) {
			this.state = payload.state;
		}
	}
}, {state: null});

// abort last request if new started
$.nette.ext('unique', {
	start: function (xhr) {
		if (this.xhr) {
			this.xhr.abort();
		}
		this.xhr = xhr;
	},
	complete: function () {
		this.xhr = null;
	}
}, {xhr: null});

// option to abort by ESC (thx to @vrana)
$.nette.ext('abort', {
	init: function () {
		$('body').keydown($.proxy(function (e) {
			if (this.xhr && (e.keyCode.toString() === '27' // Esc
			&& !(e.ctrlKey || e.shiftKey || e.altKey || e.metaKey))
			) {
				this.xhr.abort();
			}
		}, this));
	},
	start: function (xhr) {
		this.xhr = xhr;
	},
	complete: function () {
		this.xhr = null;
	}
}, {xhr: null});

$.nette.ext('load', {
	success: function () {
		$.nette.load();
	}
});

// default ajaxification (can be overridden in init())
$.nette.ext('init', {
	load: function (rh) {
		$(this.linkSelector).off('click.nette', rh).on('click.nette', rh);
		$(this.formSelector).off('submit.nette', rh).on('submit.nette', rh)
			.off('click.nette', ':image', rh).on('click.nette', ':image', rh)
			.off('click.nette', ':submit', rh).on('click.nette', ':submit', rh);
		$(this.buttonSelector).closest('form')
			.off('click.nette', this.buttonSelector, rh).on('click.nette', this.buttonSelector, rh);
	}
}, {
	linkSelector: 'a.ajax',
	formSelector: 'form.ajax',
	buttonSelector: 'input.ajax[type="submit"], button.ajax[type="submit"], input.ajax[type="image"]'
});

})(window, window.jQuery);

(function($, undefined) {

$.nette.ext({
	before: function (xhr, settings) {
		if (!settings.nette) {
			return;
		}

		var question = settings.nette.el.data('confirm');
		if (question) {
			return confirm(question);
		}
	}
});

})(jQuery);

(function($, undefined) {

$.nette.ext('spinner', {
	init: function () {
		this.spinner = this.createSpinner();
		this.spinner.appendTo('body');
	},
	start: function () {
		this.counter++;
		if (this.counter === 1) {
			this.spinner.show(this.speed);
		}
	},
	complete: function () {
		this.counter--;
		if (this.counter <= 0) {
			this.spinner.hide(this.speed);
		}
	}
}, {
	createSpinner: function () {
		return $('<div>', {
			id: 'ajax-spinner',
			css: {
				display: 'none'
			}
		});
	},
	spinner: null,
	speed: undefined,
	counter: 0
});

})(jQuery);

(function($, undefined) {

/**
 * Depends on 'snippets' extension
 */
$.nette.ext('scrollTo', {
	init: function () {
		this.ext('snippets', true).before($.proxy(function ($el) {
			if (this.shouldTry && !$el.is('title')) {
				var offset = $el.offset();
				scrollTo(offset.left, offset.top);
				this.shouldTry = false;
			}
		}, this));
	},
	success: function (payload) {
		this.shouldTry = true;
	}
}, {
	shouldTry: true
});

})(jQuery);
