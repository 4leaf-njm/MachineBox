/**
 * Cumtom Select Box Plugin (jQuery 1.6.2, jQuery UI 1.8.16 기반)
 * Author : pjm
 * Date : 2016.6.30
**/


$(function() {
	$.widget( "custom.customCompare", {
		// default options
		options: {
			returnFunc : function(){},		/*사용자 정의함수(필요시 활용가능)*/
			change : function(){}
		},

		// the constructor
		_create: function() {

			var that = this;
			
			if($(this.element).data("compareLoaded")) {
				this.setCompareData();
				return false;
			}

			$(this.element).data("compareLoaded",true);

			this.element.hide();

			var html = '';

			html += '<span class="compare">';
			html += '<div>';
			html += '	<span style="padding-left:32px;line-height:30px;">';
			html += '		└ ';
			html += '	<select class="compare_kind compare_selector" style="width:130px;">';
			html += '		<option value="compare_consumer_price">정가:비교통화</option>';
			html += '		<option value="compare_price">판매가:비교통화</option>';
			html += '		<option value="compare_sale_price">(혜택적용)판매가:비교통화</option>';
			html += '	</select>';
			html += '	<select class="compare_position compare_selector">';
			html += '		<option value="">=노출위치=</option>';
			html += '		<option value="side">옆</option>';
			html += '		<option value="bottom">밑</option>';
			html += '	</select>';
			html += '	<input type="text" value="" class="compare_font_decoration compare_selector customFontDecoration " />';
			html += '	<select class="compare_type compare_selector">';
			html += '		<option value="">=괄호여부=</option>';
			html += '		<option value="()">( )</option>';
			html += '	</select>';
			html += '	</span>';
			html += '</div>';

			html += '<div class="info_item_container_sub hide">';
			html += '	<input type="text" value="" class="compare_currency_symbols compare_selector hide" />';
			for(key in currency_list){
				if(key != basic_currency){
			html += '	<div class="compare_list" style="margin-left:172px;border:0 solid #ddd;min-height:28px;margin-bottom:1px;background-color:#fff;z-index:9999;">';
			html += '	<input type="text" value="" class="compare_currency_symbol compare_selector hide" style="width:100%;">';
			html += '		<img src="/admin/skin/default/images/common/icon_move.gif" align="absmiddle" style="margin-left:15px" class="move" />';
			html += '		<select class="compare_currency compare_selector hide">';
			html += '		<option value="'+key+'">'+key+'</option>';
			html += '		</select><span style="display:inline-block; min-width:39px;">'+key+'</span>';
			html += '		<select class="compare_symbol_view compare_selector">';
			html += '			<option value="">=노출여부=</option>';
			html += '			<option value="none">미노출</option>';
			html += '			<option value="view">노출</option>';
			html += '		</select>';
			html += '		<select class="compare_symbol_position compare_selector">';
			html += '			<option value="">=노출위치=</option>';
			html += '			<option value="before">금액 앞</option>';
			html += '			<option value="after">금액 뒤</option>';
			html += '		</select>';
			html += '		<select class="compare_symbol_postfix compare_selector">';
			html += '			<option value="">=노출심볼=</option>';
				for(var j=0; j<currency_list[key].length; j++){
					if(currency_list[key][j].substring(0,1) == "&" ){//if(currency_list[key][j].match("&") != null){
						symbol_key = "symbol";
					}else{
						symbol_key = currency_list[key][j];
					}
				
			html += '			<option value="'+symbol_key+'">'+currency_list[key][j]+'</option>';
				}
			html += '		</select>';
			html += '	</div>';
				}
			}
			html += '</div>';
			html += '</span>';

		 	this.CompareContainerObj = $(html);
			this.element.after(this.CompareContainerObj);

			$(".compare_selector",this.CompareContainerObj).bind('change',function(event){
				event.preventDefault();
				that.change_compare_item_sub(this);
				that.change_compare_item(this);
			});

			$(".compare_position",this.CompareContainerObj).bind('change',function(event){
				event.preventDefault();
				if ($(this).val()) {
					if	(!$(this).closest('.info_item_cell_compare').find('.compare_currency').val()) {
						openDialogAlert('설정된 비교통화가 없습니다.<br />설정>상점정보 메뉴에서 비교통화를 설정하세요.',320,150,function(){});
						$(".compare_position",this.CompareContainerObj).val('');
					}else{
						$(this).closest('.info_item_cell_compare').find('.info_item_container_sub').show();
					}
				}else{
					$(this).closest('.info_item_cell_compare').find('.info_item_container_sub').hide();
				}
			});
			
			$(this.element).bind('change',function(event){
				event.preventDefault();
				that.setCompareData();
			}).change();
			
			this.options.returnFunc(this.CompareContainerObj);
		},
		
		setCompareData: function (){
			var that = this;

			this.CompareContainerObj.each(function(){
				var compare_code	= $(that.element).val();
				if(compare_code != ''){
					compare_code = compare_code.replaceAll("\"{","{");
					compare_code = compare_code.replaceAll("}\"","}");
				}
				var compare_default	= {'kind':'','position':'','type':'','font_decoration':'','currency_symbols':''};
				var compare_data	= compare_code ? eval("("+compare_code+")") : {};
				
				/* 값 초기화 */
				for(var key in compare_default){
					var selector		= ".compare_" + key;
					var selectValue		= compare_default[key];

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

				/* 값 세팅 */
				for(var key in compare_data){

					var selector	= ".compare_" + key;
					var selectValue	= '';
					if(typeof compare_data[key] == 'object'){
						for(var key2 in compare_data[key]){
							if(typeof compare_data[key][key2] == "object"){
								compare_data[key][key2] = JSONtoString(compare_data[key][key2]);
							}
						}
						selectValue = JSONtoString(compare_data[key]);
					}else{
						selectValue = compare_data[key];
					}

					if($(selector,this).length){
						switch($(selector,this)[0].tagName){
							case 'INPUT':
								if($(selector,this).attr('type')=='text'){
									//$(selector,this).attr("style","width:100%;display:block;background-color:gray");
									if(key == "currency_symbols"){
										that.setCompareData_sub(selectValue,this);
										$(selector,this).val(selectValue);
									}else{
										$(selector,this).val(selectValue).change();
									}
								}else{
									$(selector,this).attr('checked',true);
								}
							break;
							case 'SELECT':
								$(selector,this).val(selectValue).change();
							break;
						}
					}
				}

				$(".customFontDecoration",that.CompareContainerObj).customFontDecoration({"change":function(){
					change_info_item(that.CompareContainerObj);
				}});

				that.change_compare_item_sub($("select,input",that.CompareContainerObj).eq(0));
				that.change_compare_item($("select,input",that.CompareContainerObj).eq(0));

			});
		},
		/*  비교통화 데이터 넣기*/
		setCompareData_sub : function(compare_code,obj){

			if(compare_code){
				compare_code = compare_code.replaceAll("\"{","{");
				compare_code = compare_code.replaceAll("}\"","}");
			}

			var compare_default	= {'currency':'','symbol_view':'','symbol_position':'','symbol_postfix':''};
			var compare_data	= compare_code ? eval("("+compare_code+")") : {};

			/* 값 초기화 */
			for(var key in compare_default){

				var selector		= ".compare_" + key;

				$(selector,obj).each(function(key2){

					/* 데이터가 있으면 넣기 */
					if(compare_data[key2]){

						var selectValue		= compare_data[key2][key];

						switch($(this)[0].tagName){

							case 'INPUT':
								if($(this).attr('type')=='text'){
									$(this).val(selectValue).change();
								}else{
									$(this).attr('checked',true);
								}
							break;
							case 'SELECT':
								$(this).val(selectValue).change();
							break;
						}
					}
				});
			}
		},
		
		/* 비교통화 노출 위치, 폰트꾸미기 항목 값 변경 */
		change_compare_item : function(obj){

			var compare				= $(obj).closest(".compare");
			var compare_config		= ['kind','position','type','font_decoration','currency_symbols'];
			var data				= {};
			var result_string		= '';

			for(var i=0;i<compare_config.length;i++){

				var key			= compare_config[i];
				var selector	= ".compare_" + key;
				var selectValue = "";

				if($(selector,compare).length){
					switch($(selector,compare)[0].tagName){
						case 'INPUT':
							if($(selector,compare).attr('type')=='text'){
								selectValue = $(selector,compare).val();
							}else if($(selector,compare).is(':checked')){
								selectValue = $(selector,compare).val();
							}
						break;
						case 'SELECT':
							selectValue = $(selector,compare).val();
						break;
					}
				}

				data[key] = selectValue;

			}

			result_string	= (JSONtoString(data));

			$(this.element).val(result_string);
			
			this.options.change();
		},
		
		/* 비교통화 종류 노출 설정 항목 값 변경 */
		change_compare_item_sub : function(obj){
			var compare				= $(obj).closest(".compare");
			var compare_list		= $(obj).closest(".compare_list");
			var compare_config		= ['currency','symbol_view','symbol_position','symbol_postfix'];
			var data_sub			= {};
			var result_string_sub	= '';

			for(var i=0;i<compare_config.length;i++){

				var key			= compare_config[i];
				var selector	= ".compare_" + key;
				var selectValue = "";

				if($(selector,compare_list).length){
					switch($(selector,compare_list)[0].tagName){
						case 'INPUT':
							if($(selector,compare_list).attr('type')=='text'){
								selectValue = $(selector,compare_list).val();
							}else if($(selector,compare_list).is(':checked')){
								selectValue = $(selector,compare_list).val();
							}
						break;
						case 'SELECT':
							selectValue = $(selector,compare_list).val();
						break;
					}
				}
				data_sub[key] = selectValue;
			}

			result_string_sub	= JSONtoString(data_sub);

			$(".compare_currency_symbol",compare_list).val(result_string_sub);

			var data_symbol = {};
			$(".compare_currency_symbol",compare).each(function(e){
				data_symbol[e] = $(this).val();
			});
			var compare_currency_symbols = JSONtoString(data_symbol);
			$(".compare_currency_symbols",compare).val(compare_currency_symbols);
			
			this.options.change();
		},

		destroy: function() {
			this.element.show();
			this.CompareContainerObj.remove();
			$(this.element).data("compareLoaded",false);

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
