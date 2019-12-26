/**
 * Cumtom Select Box Plugin (jQuery 1.6.2, jQuery UI 1.8.16 기반)
 * Author : ocw
 * Date : 2012.01.13
**/
if(typeof jsonFontArrayLoaded == 'undefined'){
	var jsonFontArrayLoaded = true;
	var jsonFontArray = new Array();

	jQuery.ajax({
		'url' : '/font/json_font',
		'dataType' : 'json',
		'async' : false,
		'global' : false,
		'success' : function(res){
			if(res){
				for(var i=0;i<res.length;i++){
					jsonFontArray.push({label:res[i].font_name+ ' (<span class="tx-txt">\uac00\ub098\ub2e4\ub77c</span>)',title:res[i].font_name,data:res[i].font_face,klass:"tx-"+res[i].font_face});
				}
			}
		}
	});
}
$(function() {
	$.widget( "custom.customFontDecoration", {
		// default options
		options: {
			returnFunc : function(){},		/*사용자 정의함수(필요시 활용가능)*/
			change : function(){ }
		},

		// the constructor
		_create: function() {

			var that = this;

			if($(this.element).data("fontDecorationLoaded")) {
				this.setFontDecorationData();
				return false;
			}

			$(this.element).data("fontDecorationLoaded",true);

			this.element.hide();

			var add_font = '';
			for(var i=0;i<jsonFontArray.length;i++){
				add_font += '<option value="'+jsonFontArray[i].data+'">'+jsonFontArray[i].title+'</option>';						
			}
			
			var html = '';
			html += '<span class="font_decoration">';
			html += '	<span class="font_decoration_cell_color font_decoration_cell">';
			html += '		<label><input type="text" class="font_decoration_color font_decoration_selector colorpicker" value="#000000" /></label>';
			html += '	</span>';
			html += '	<span class="font_decoration_cell_font font_decoration_cell">';
			html += '		<select class="font_decoration_font font_decoration_selector">';
			html += '			<option value="" selected="selected">= 폰트 =</option>';
			html += '			<option value="gulim">굴림</option>';
			html += '			<option value="dotum">돋움</option>';
			html += '			<option value="gungsuh">궁서</option>';
			html += '			<option value="malgun Gothic">맑은 고딕</option>';
			html += add_font;
			html += '		</select>';
			html += '	</span>';
			html += '	<span class="font_decoration_cell_size font_decoration_cell">';
			html += '		<select class="font_decoration_size font_decoration_selector">';
			html += '			<option value="" selected="selected">= 크기 =</option>';
			html += '			<option value="7">7pt</option>';
		 	html += '			<option value="8">8pt</option>';
		 	html += '			<option value="9">9pt</option>';
		 	html += '			<option value="10">10pt</option>';
		 	html += '			<option value="11">11pt</option>';
		 	html += '			<option value="12">12pt</option>';
		 	html += '		</select>';
		 	html += '	</span>';
		 	html += '	<span class="font_decoration_cell_bold font_decoration_cell">';
			html += '		<select class="font_decoration_bold font_decoration_selector">';
		 	html += ' 			<option value="normal" selected="selected">= 굵기 =</option>';
			html += ' 			<option value="bold">진하게</option>';
			html += ' 		</select>';
			html += '	</span>';
		 	html += '';	
		 	html += '	<span class="font_decoration_cell_underline font_decoration_cell">';
		 	html += '		<select class="font_decoration_underline font_decoration_selector">';
		 	html += '			<option value="none" selected="selected">= 줄긋기 =</option>';
			html += ' 			<option value="underline">밑줄</option>';
			html += ' 			<option value="overline">윗줄</option>';
			html += ' 			<option value="line-through">취소선</option>';
			html += ' 		</select>';
			html += '	</span>';
		 	html += '</span>';

		 	this.fontDecorationContainerObj = $(html);
			this.element.after(this.fontDecorationContainerObj);
		
			/*
			$(this.fontDecorationContainerObj).bind('change',function(event){
				event.preventDefault();
				that.change_font_decoration_item(this);			
			});
			*/
			
			$(".font_decoration_selector",this.fontDecorationContainerObj).bind('change',function(event){
				event.preventDefault();
				that.change_font_decoration_item(this);			
			});
			
			$(this.element).bind('change',function(event){
				event.preventDefault();
				that.setFontDecorationData();
			}).change();

			this.options.returnFunc(this.fontDecorationContainerObj);
		},
		
		setFontDecorationData: function (){
			var that = this;

			this.fontDecorationContainerObj.each(function(){
				var font_decoration_code	= $(that.element).val();
				var font_decoration_default	= {'color':'#000000','font':'','size':'','bold':'normal','underline':'none'};
				var font_decoration_data	= font_decoration_code ? eval("("+font_decoration_code+")") : {};

				/* 값 초기화 */
				for(var key in font_decoration_default){
					var cellSelector = ".font_decoration_cell_" + key;
					var selector = ".font_decoration_" + key;
					var selectValue = font_decoration_default[key];

					if($(selector,this).length){
						switch($(selector,this)[0].tagName){
							case 'INPUT':
								if($(selector,this).attr('type')=='text'){
									$(selector,this).val(selectValue);
								}else{
									if($(selector,this).val()==selectValue)
										$(selector,this).attr('checked',true);
									else
										$(selector,this).removeAttr('checked');
								}
							break;
							case 'SELECT':
								$(selector,this).val(selectValue);
							break;
						}
					}
				}
				$(".colorpicker",this).customColorPicker("destroy");
				
				/* 값 세팅 */
				for(var key in font_decoration_data){
					var cellSelector = ".font_decoration_cell_" + key;
					var selector = ".font_decoration_" + key;
					var selectValue = font_decoration_data[key];

					if($(selector,this).length){
						switch($(selector,this)[0].tagName){
							case 'INPUT':
								if($(selector,this).attr('type')=='text'){
									$(selector,this).val(selectValue);
								}else{
									$(selector,this).attr('checked',true);
								}
							break;
							case 'SELECT':
								$(selector,this).val(selectValue);
							break;
						}
					}
				}
				$(".colorpicker",this).customColorPicker();

				that.change_font_decoration_item($("select,input",that.fontDecorationContainerObj).eq(0));
			});
			
		},
		
		/* 폰트꾸미기 항목 값 변경 */
		change_font_decoration_item : function(obj){
			var font_decoration = $(obj).closest(".font_decoration");
			var font_decoration_config = ['color','font','size','bold','underline'];
			var data = {};
			var result_string = '';
			var sub_font_decoration = $(obj).closest(".compare");
			
			$(".font_decoration_cell",font_decoration).hide();
			for(var i=0;i<font_decoration_config.length;i++){

				var key = font_decoration_config[i];
				var cellSelector = ".font_decoration_cell_" + key;
				var selector = ".font_decoration_" + key;
				var selectValue = "";
				
				$(cellSelector,font_decoration).show();

				if($(selector,font_decoration).length){
					switch($(selector,font_decoration)[0].tagName){
						case 'INPUT':
							if($(selector,font_decoration).attr('type')=='text'){
								selectValue = $(selector,font_decoration).val();
							}else if($(selector,font_decoration).is(':checked')){
								selectValue = $(selector,font_decoration).val();
							}
						break;
						case 'SELECT':
							selectValue = $(selector,font_decoration).val();
						break;
					}
				}
				
				data[key] = selectValue;
			}

			result_string = (JSONtoString(data));

			$(this.element).val(result_string);
			// 비교 통화 부분은 따로 분리 되어있기때문에 따로 처리 한다 2017-01-31 jhr
			if	(sub_font_decoration.hasClass('compare')) {
				$(sub_font_decoration).find('.compare_type').change();
			}
			this.options.change();
		},
		
		destroy: function() {
			this.element.show();
			this.fontDecorationContainerObj.remove();
			$(this.element).data("fontDecorationLoaded",false);

			$.Widget.prototype.destroy.apply(this,arguments);
		},

		// _setOptions is called with a hash of all options that are changing
		// always refresh when changing options
		_setOptions: function() {
			// in 1.9 would use _superApply
			$.Widget.prototype._setOptions.apply( this, arguments );
		},

		// _setOption is called for each individual option that is changing
		_setOption: function( key, value ) {
			// in 1.9 would use _super
			$.Widget.prototype._setOption.call( this, key, value );
		}
	});

});
