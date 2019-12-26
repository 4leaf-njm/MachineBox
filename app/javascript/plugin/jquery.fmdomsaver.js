/*
* 실시간 자동 저장 plugin ( ver.0.1 )
* @2016-10-17 kdy
* options 

*/
;(function ($, window, document, undefined){

	var pluginName	= 'fmdomsaver'
	, defaults		= {
		'useBind'		: true, 
		'formName'		: 'tmpFmDomSaverForm', 
		'method'		: 'post', 
		'action'		: '',
		'target'		: '', 
		'addDataFunc'	: '', 
		'callBackFunc'	: '', 
		'ignore'		: [] 
	};

	// _construct_
	$.fn.fmdomsaver	= function(options){
		var that				= this;
		this.func				= '';
		this.frmObj				= '';
		this.settings			= $.extend({}, defaults, options);
		if	(this.settings.addDataFunc){
			this.addDataFunc	= window[this.settings.addDataFunc];
		}
		if	(this.settings.callBackFunc){
			this.callBackFunc	= window[this.settings.callBackFunc];
		}

		// 폼 생성
		this.create_form		= function(){
			if	(!that.frmObj){
				var formHTML	= '<form name="' + that.settings.formName + '" method="' + that.settings.method + '" ';
				if	(that.settings.action)	formHTML		+= 'action="' + that.settings.action + '" ';
				if	(that.settings.target)	formHTML		+= 'target="' + that.settings.target + '" ';
				formHTML		+= '></form>';
				that.frmObj			= $(formHTML).appendTo($('body'));
			}
		};

		// 기본 bind 처리
		this.set_bind			= function(obj){
			if	($(obj).attr('name') && $(obj).attr('type') != 'hidden'){
				var name			= $(obj).attr('name');
				name				= name.replace(/\[[^\]]*\]/g, '');	// 배열문자 제거
				var tag				= $(obj).prop('tagName');
				var type			= $(obj).attr('type');
				if	(that.settings.ignore.length > 0){
					for (var i in that.settings.ignore){
						if	(name == that.settings.ignore[i]){
							return false;
						}
					}
				}

				if	(tag == 'SELECT'){
					$(obj).unbind('change');
					$(obj).bind('change', function(){that.sendData(this);});
				}else{
					switch(type){
						case 'checkbox':
						case 'radio':
							$(obj).unbind('click');
							$(obj).bind('click', function(){that.sendData(this);});
						break;
						case 'text':
							$(obj).unbind('blur');
							$(obj).bind('blur', function(){that.sendData(this);});
						break;
					}
				}
			}
		};

		// 전송처리
		this.sendData			= function(obj){
			that.frmObj.html('');
			if	(typeof(that.addDataFunc) == 'function'){
				var addData			= that.addDataFunc(obj);
				for	( var name in addData ){
					that.frmObj.append('<input type="hidden" name="' + name + '" value="' + addData[name] + '" />');
				}
			}

			if	($(obj).attr('type') == 'checkbox'){
				$(obj).each(function(){
					if	($(this).attr('checked')){
						that.frmObj.append('<input type="hidden" name="' + $(this).attr('name') + '" value="' + $(this).val() + '" />');
					}
				});
			}else{
				if	($(obj).val() == $(obj).attr('title'))	$(obj).val('');
				var name	= $(obj).attr('name');
				name		= name.replace(/\[[^\]]*\]/g, '');	// 배열문자 제거
				that.frmObj.append('<input type="hidden" name="' + name + '" value="' + $(obj).val() + '" />');
			}
			that.frmObj.submit();

			if	(typeof(that.callBackFunc) == 'function')	that.callBackFunc(obj);
		};

		// 수동 전송처리
		this.requestSendData	= function(data){
			that.frmObj.html('');
			for	( var name in data ){
				that.frmObj.append('<input type="hidden" name="' + name + '" value="' + data[name] + '" />');
			}
			that.frmObj.submit();
		};

		// 현재 설정값을 전달
		this.getSettingData		= function(){
			return that.settings;
		};

		// 현재 지정된 임시 form의 action이나 target을 변경
		this.changeFormAttr		= function(method, action, target){
			if	(method)	that.frmObj.attr('method', method);
			if	(action)	that.frmObj.attr('action', action);
			if	(target)	that.frmObj.attr('target', target);
		};

		return this.each(function(){
			that.create_form();
			if	(that.settings.useBind){
				that.set_bind(this);
			}
		});
	};


})( $, window, document );
