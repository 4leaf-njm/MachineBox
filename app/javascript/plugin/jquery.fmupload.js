/*
* 심플한 파일 Ajax 업로드 plugin firstmall upload
* @2015-06-18 kdy
*/

if(jQuery)(function(jQuery){
	jQuery.extend(jQuery.fn,{
		fmupload:function(options) {
			jQuery(this).each(function(){
				var settings = jQuery.extend({
					id			: jQuery(this).attr('id'),
					width		: 63,
					height		: 20,
					script		: '/common/fmupload',
					button		: '<img src="/app/javascript/plugin/jquploadify/uploadify-search.gif" />',
					onComplete	: function(filename, filepath){}

				},options);
				jQuery(this).data('settings',settings);
				jQuery(this).css('width', settings.width).css('height', settings.height);

				jQuery(this).fmuploadCreate(settings);
			});
		},
		fmuploadCreate:function(settings){

			var showButton			= jQuery(settings.button).appendTo(jQuery(this));
			var hideInputFile		= jQuery('<input type="file" id="inputFile'+settings.id+'" />').appendTo(jQuery(this));

			// 최상위 레이어 css 설정
			jQuery(this).css({'position'			: 'relative', 
							'overflow'				: 'hidden', 
							'width'					: settings.width + 'px', 
							'height'				: (settings.height + 1) + 'px', 
							'cursor'				: 'pointer'});
			// 노출용 버튼 css 설정
			showButton.css({'cursor'				: 'pointer',  
							'width'					: settings.width,
							'height'				: settings.height});

			// 숨김용 파일찾기 버튼 css 설정
			hideInputFile.css({'position'			: 'absolute',
								'cursor'			: 'pointer',  
								'top'				: '0px',
								'left'				: '0px',
								'width'				: settings.width,
								'height'			: settings.height,
								'opacity'			: '0',
								'filter'			: 'alpha(opacity=0)',
								'-ms-filter'		: 'alpha(opacity=0)',
								'-khtml-opacity'	: '0',
								'-moz-opacity'		: '0'});

			// 파일 선택 시 전송 이벤트 생성
			hideInputFile.change(function(){
				jQuery(this).fmuploadSend(this, settings);
			});
		},
		fmuploadSend:function(obj, settings){
			if(typeof(FormData) == "undefined"){
				//IE10 이하 file object 를 지원하지 않음.
				if($("#" + jQuery(obj).attr('id') + "_form") != 'tmp_fm_upload_form'){
					jQuery(obj).wrap('<form id="' + jQuery(obj).attr('id') + '_form" method="post" enctype="multipart/form-data" target="actionFrame"></form>');
				}

				$('#' + jQuery(obj).attr('id') + '_form').attr('action', settings.script);
				$('#'+jQuery(obj).attr('id')).attr('name', jQuery(obj).attr('id'));

				jQuery('#actionFrame').load(function(){
					var result = eval('(' + $('#actionFrame').contents().text() + ')');
					var resultLen	= result.length;
					for	(var r = 0; r < resultLen; r++){
						var data	= result[r];
						if	(data.status){
							settings.onComplete.call(r, settings.id, data.fileInfo.file_name, data.filePath);
						}
					}

					$('#actionFrame').contents().text('');
					jQuery('#actionFrame').off('load');
				});

				$('#' + jQuery(obj).attr('id') + '_form').submit();
			}else{
				jQuery.ajax({
					'url'			: settings.script,
					'type'			: "POST",
					'contentType'	: false,
					'processData'	: false,
					'data'			: function() {
										var data = new FormData();
										data.append(jQuery(obj).attr('id'), jQuery(obj).get(0).files[0]);
										return data;
										}(),
					'dataType'		: 'json',
					'success'		: function(result) {
						var resultLen	= result.length;
						for	(var r = 0; r < resultLen; r++){
							var data	= result[r];
							if	(data.status){
								settings.onComplete.call(r, settings.id, data.fileInfo.file_name, data.filePath);
							}
						}
					}
				});
			}
		}
	})
}(jQuery));