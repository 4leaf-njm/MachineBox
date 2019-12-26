/*
* 검색어 검색 inputbox에 dropdown 추가하는 plugin
* @2015-06-18 kdy
*/

if(jQuery)(function(jQuery){
	jQuery.extend(jQuery.fn,{
		fmsrckeyword:function(options) {
			jQuery(this).each(function(){
				var settings = jQuery.extend({
					border			: '1px solid #7b7b7b', 
					background		: '#ffffff', 
					fontcolor		: '#929292', 
					keywordcolor	: '#f0830b', 
					defaultStype	: '', 
					onSelect		: function(type){}
				},options);
				jQuery(this).data('settings',settings);
				jQuery(this).fmsrckeywordCreate(settings);
			});
		},
		fmsrckeywordCreate:function(settings){

			// Add Elements
			var obj					= jQuery(this);
			var inputObj			= obj.find('input');
			var dropdownLay			= jQuery('<ul style="display:none;"></ul>').appendTo(obj);
			var typeInput			= jQuery('<input type="hidden" name="' + inputObj.attr('name') + '_sType" value="' + settings.defaultStype + '" />').appendTo(obj);
			var inputWidth			= inputObj.width();
			var inputHidth			= inputObj.height();
			var typeArr				= inputObj.attr('title').split(',');
			var typeLen				= typeArr.length;
			var item				= '';
			var liCss				= 'color:' + settings.fontcolor + ';margin:5px;cursor:pointer;';
			var overlay				= jQuery('<div><span class="defaultStype"></span><span class="keyword-value" style="color:' + settings.keywordcolor + ';">' + inputObj.val() + '</span></div>').appendTo(obj);

			// add Css each Elements
			obj.css({'position'	: 'relative'});
			overlay.css({
				'position'		: 'absolute', 
				'top'			: '0',
				'left'			: '0', 
				'overflow'		: 'hidden', 
				'width'			: inputWidth + 'px', 
				'height'		: inputHidth + 'px', 
				'line-height'	: inputHidth + 'px', 
				'text-align'	: 'center', 
				'background'	: settings.background, 
				'border'		: settings.border 
			});
			dropdownLay.css({
				'position'		: 'absolute',
				'top'			: inputHidth + 'px',
				'left'			: '0',
				'background'	: settings.background, 
				'border'		: settings.border, 
				'width'			: inputWidth + 'px'
			});

			// add EventListener 
			if			(settings.defaultStype){
				overlay.find('.defaultStype').html(settings.defaultStype + ' : ');
			}else if	(!inputObj.val()){
				overlay.find('.defaultStype').html(inputObj.attr('title'));
			}
			overlay.bind('click', function(){
				jQuery(this).hide();
				inputObj.focus();
			});
			inputObj.bind('focus', function(){
				obj.find('.keyword-value').html(jQuery(this).val());
				if	(jQuery(this).val())	dropdownLay.show();
				else						dropdownLay.hide();
			});
			inputObj.bind('keyup', function(){
				if	(jQuery(this).val().length > 0){
					dropdownLay.show();
					if	(typeInput.val())	overlay.find('.defaultStype').html(typeInput.val() + ' : ');
					else					overlay.find('.defaultStype').html('');
				}else{
					typeInput.val('');
					dropdownLay.hide();
					overlay.find('.defaultStype').html(inputObj.attr('title'));
				}
				obj.find('.keyword-value').html(jQuery(this).val());
			});
			inputObj.bind('blur', function(){
				dropdownLay.hide();
				overlay.show();
			});			
			dropdownLay.bind('mouseover', function(){
				inputObj.unbind('blur');
			});
			dropdownLay.bind('mouseout', function(){
				inputObj.bind('blur', function(){
					dropdownLay.hide();
					overlay.show();
				});
			});

			// add sType Items
			item				= jQuery('<li style="' + liCss + '" sType="">-전체검색</li>').appendTo(dropdownLay);
			item.bind('click', function(){
				typeInput.val(jQuery(this).attr('sType'));
				dropdownLay.hide();
				if	(jQuery(this).attr('sType'))	overlay.find('.defaultStype').html(jQuery(this).attr('sType') + ' : ');
				else								overlay.find('.defaultStype').html(jQuery(this).attr('sType'));
				overlay.show();
				settings.onSelect.call(jQuery(this).attr('sType'));
			});
			for	( var t = 0; t < typeLen; t++){
				item				= jQuery('<li style="' + liCss + '" sType="' + typeArr[t] + '">' + typeArr[t] + ':<span class="keyword-value" style="color:' + settings.keywordcolor + ';"></span> - ' + typeArr[t] + ' 찾기</li>').appendTo(dropdownLay);
				item.bind('click', function(){
					typeInput.val(jQuery(this).attr('sType'));
					dropdownLay.hide();
					if	(jQuery(this).attr('sType'))	overlay.find('.defaultStype').html(jQuery(this).attr('sType') + ' : ');
					else								overlay.find('.defaultStype').html(jQuery(this).attr('sType'));
					overlay.show();
					settings.onSelect.call(jQuery(this).attr('sType'));
				});
			}
		} 
	})
}(jQuery));