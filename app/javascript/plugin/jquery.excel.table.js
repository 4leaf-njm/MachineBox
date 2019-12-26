/*
* 상품목록 테이블형태로 노출 ( ver.0.1 )
* @2015-08-12 kdy
*/
;(function ($, window, document, undefined){

	var pluginName	= 'fmexceltable'
	, defaults		= {
		'wrapWidth'			: '100%', 
		'viewMode'			: false, 
		'rowController'		: true, 
		'tableClass'		: '', 
		'selectedBorder'	: '', 
		'colBatch'			: new Array(), 
		'thDefaultClass'	: '', 
		'tdDefaultClass'	: '', 
		'thClass'			: [], 
		'tdClass'			: '', 
		'colWidth'			: [], 
		'thHeader'			: [], 
		'tdBody'			: [], 
		'hideTh'			: new Array(), 
		'thMerge'			: [], 
		'tdMerge'			: [], 
		'data'				: [], 
		'dataPer'			: '0', 
		'dataCount'			: '0', 
		'totalRow'			: {},
		'getNextDataFunc'	: '', 
		'chgViewDataFunc'	: ''
	};

	// _construct_
	$.fn.fmexceltable	= function(options){
		var that				= this;
		this.useTotalRow		= false;
		this.settings			= $.extend({}, defaults, options);
		this.tbodyObj			= '';
		this.tfootObj			= '';
		this.currnetPosition	= '';

		// 테이블 생성 및 타이틀 영역 생성
		this.createTable	= function(){
			// 브라우저 자동완성 방지용
			if	(!$('head').find("meta[name='autocomplete']").attr('content')){
				$('head').append('<meta name="autocomplete" content="off" />');
			}
			$(this).closest('form').attr('autocomplete', 'off');

			var wrap_id		= $(this).attr('id');
			var tbHTML		= '<table cellpadding="0" cellspacing="0"></table>';
			var tbObj		= $(tbHTML).appendTo($(this));
			var colGroupObj	= $('<colgroup></colgroup>').appendTo(tbObj);
			var theadObj	= $('<thead></thead>').appendTo(tbObj);
			that.tbodyObj	= $('<tbody></tbody>').appendTo(tbObj);
			that.tfootObj	= $('<tfoot></tfoot>').appendTo(tbObj);
			var thRow		= '';
			var trObj		= '';
			var thObj		= '';

			// table에 class 및 width 추가
			if	(that.settings.tableClass)	tbObj.addClass(that.settings.tableClass);
			if	(that.settings.wrapWidth)	tbObj.css('width', that.settings.wrapWidth);

			// tbody에 class 추가
			if	(that.settings.tbodyClass) that.tbodyObj.addClass(that.settings.tbodyClass);

			// 타이틀 영역 생성
			if	(that.settings.thHeader.length > 0){
				for	(var t = 0; t < that.settings.thHeader.length; t++){
					thRow	= that.settings.thHeader[t];
					if	(thRow.length){
						trObj	= $('<tr></tr>').appendTo(theadObj);
						// row 추가/삭제 버튼 존재유무
						if	(t == 0 && that.settings.rowController){
						// th 추가
							thObj	= $('<th style="cursor:pointer;">+</th>').appendTo(trObj);
							// th class 추가
							if	(that.settings.thClass){
								if	(typeof(that.settings.thClass) == 'string')	thObj.addClass(that.settings.thClass);
								else											thObj.addClass(that.settings.thClass[0]);
							}
							thObj.bind('click', function(){
								that.addDefaultRow('', []);
							});
						}

						for	(var r = 0; r < thRow.length; r++){
							thObj	= $('<th></th>').appendTo(trObj);
							// th rowspan 추가
							if	(thRow[r].rowspan > 0)			thObj.attr('rowspan', thRow[r].rowspan);
							// th colspan 추가
							if	(thRow[r].colspan > 0)			thObj.attr('colspan', thRow[r].colspan);
							// 숨김 처리
							if	(thRow[r].hide == 'y')			thObj.hide();
							// th class 추가
							if	(that.settings.thDefaultClass)	thObj.addClass(that.settings.thDefaultClass);
							if	(thRow[r].thClass)				thObj.addClass(thRow[r].classs);
							else if	(that.settings.thClass)		thObj.addClass(that.settings.thClass);

							// th title 추가
							if	(thRow[r].title)				thObj.html(thRow[r].title);
							// 일괄 checkbox 기능 추가
							if	(thRow[r].chkClass){
								if	(thRow[r].title)			thObj.append('<br/>');
								if	(!that.settings.viewMode)	thObj.append('<input type="checkbox" class="' + thRow[r].chkClass + '-all-check" onclick="excelTableAllCheck(this, \'' + wrap_id + '\', \'' + thRow[r].chkClass + '\', \'' + thRow[r].userFunc + '\');" />');
							}
							// 값 일괄 변경 기능 추가
							if	(thRow[r].addHTML)				thObj.append(thRow[r].addHTML);
						}
					}
				}
			}

			// colgroup 추가
			for	(var t = 0; t < that.settings.colWidth.length; t++){
				// colgroup 추가
				if	(that.settings.tdBody[t].type != 'hide'){
					if	(that.settings.colWidth[t])	colGroupObj.append('<col width="' + that.settings.colWidth[t] + '" />');
					else							colGroupObj.append('<col />');
				}
			}
		};

		// 테이블 생성 및 타이틀 영역 생성
		this.defaultRows	= function(){

			var tbodyObj	= that.tbodyObj;
			var trObj		= '';
			var tdObj		= '';
			var colspan		= '';

			// 데이터가 있으면 데이터를 먼저 추가한다.
			if	(that.settings.data.length > 0){
				that.currentScroll	= 0;
				that.currentPage	= 1;
				that.loadStatus		= false;
				that.setDataRow(that.settings.data);
				if	(that.settings.getNextDataFunc && parseInt(that.settings.dataPer) > 0 && 
					parseInt(that.settings.dataCount) > 0 && 
					parseInt(that.settings.dataCount) > parseInt(that.settings.dataPer)){
					$(window).bind('scroll', function(){
						if	(!that.loadStatus && that.currentScroll < $(this).scrollTop()){
							that.loadStatus	= true;
							that.currentPage++;
							var func	= window[that.settings.getNextDataFunc];
							func(that.currentPage, that.settings.dataPer, function(data){
								that.loadStatus	= false;
								that.setDataRow(data);
							});

							if	(that.settings.dataCount <= (that.currentPage * that.settings.dataPer)){
								$(window).unbind('scroll');
								that.addDefaultRow('', []);
							}
							that.currentScroll = $(this).scrollTop();
						}
					});
				}else{
					that.addDefaultRow('', []);
				}
			}else{
				that.addDefaultRow('', []);
			}
		};

		// 초기 데이터 세팅
		this.setDataRow			= function(datas){
			if	(datas.length > 0){
				// 데이터 row 추가
				var trObj	= '';
				for	(var t = 0; t < datas.length; t++){
					trObj	= that.addDefaultRow('datas', datas[t]);
					if	(that.settings.viewMode)
						trObj.find('td').css('position', '').css('background-color', '#f7f7f7');
				}
			}
		};

		// 기본 빈row를 추가
		this.addDefaultRow		= function(type, data){
			if	(type == 'datas' || !that.settings.viewMode){
				var tbodyObj	= that.tbodyObj;
				var trObj		= '';
				var tdObj		= '';

				trObj	= $('<tr></tr>').appendTo(tbodyObj);

				// row 추가/삭제 버튼 존재유무
				if	(that.settings.rowController){
					// th 추가
					tdObj	= $('<td style="text-align:center;cursor:pointer;">-</td>').appendTo(trObj);
					// td class 적용
					if	(that.settings.tdClass)	tdObj.addClass(that.settings.tdClass);

					tdObj.bind('click', function(){
						$(this).closest('tr').remove();
					});
				}

				var dataVal		= '';
				var tdBody		= '';
				for	( var c = 0; c < that.settings.tdBody.length; c++){
					tdBody	= that.settings.tdBody[c];

					// td 추가
					tdObj	= $('<td></td>').appendTo(trObj);

					// td class 적용
					if		(that.settings.tdDefaultClass)		tdObj.addClass(that.settings.tdDefaultClass);
					if		(tdBody.tdClass)					tdObj.addClass(tdBody.tdClass);
					else if	(that.settings.tdClass)				tdObj.addClass(that.settings.tdClass);

					// td box 추가
					if	(tdBody){
						if	(data[c] != 0 && !data[c])	dataVal		= '';
						else							dataVal		= data[c];
						that.makeInputForm(tdObj, tdBody, dataVal);
						that.setBind(tdObj, tdBody);
					}
				}

				return trObj;
			}
		};

		// 빈 row를 삭제한다.
		this.delDefaultRow		= function(standardInputName){
			var tbodyObj	= that.tbodyObj;
			$(tbodyObj).find('tr').each(function(){
				if	(!$(this).find("input[name='" + standardInputName + "']").val()){
					$(this).remove();
				}
			});
		};

		// 중복 데이터 체크
		this.chkDuplicateRow	= function(standardInputName, wantAddVal){
			var tbodyObj	= that.tbodyObj;
			var chkResult	= false;
			$(tbodyObj).find('tr').each(function(){
				if	($(this).find("input[name='" + standardInputName + "']").val() == wantAddVal){
					chkResult	= true;
					return false;
				}
			});

			return chkResult;
		};

		// td에 넣을 내용을 설정에 따라 생성함
		this.makeInputForm		= function(obj, cfg, val){
			var html	= '';
			var type	= cfg.type;
			if	(that.settings.viewMode && type != 'plain' && type != 'checkbox' && type != 'image' && type != 'hide')	type	= 'view';
			
			switch(type){
				case 'checkbox':
					var checked	= (cfg.checked) ? 'checked' : '';
					if		(val == 'checked')		checked	= 'checked';
					else if	(val == 'unchecked')	checked	= '';
					if	(cfg.value)	val		= cfg.value;

					if	(that.settings.viewMode)
						obj.append('<input type="checkbox" name="' + cfg.boxName + '" value="' + val + '" ' + checked + ' disabled />');
					else
						obj.append('<input type="checkbox" name="' + cfg.boxName + '" value="' + val + '" ' + checked + ' />');
				break;
				case 'hide':
					obj.css('display', 'none');
					obj.append('<input type="hidden" name="' + cfg.boxName + '" value="' + val + '" />');
				break;
				case 'view':
					if	(cfg.boxName && that.settings.chgViewDataFunc){
						var func	= window[that.settings.chgViewDataFunc];
						html	= '<span>' + func(cfg.boxName, val, obj) + '</span>';
					}else{
						html	= '<span>' + val + '</span>';
					}
					if	(cfg.boxName)	html	+= '<input type="hidden" name="' + cfg.boxName + '" value="' + val + '" />';
					else				html	+= '<input type="hidden" value="' + val + '" />';
					obj.append(html);
				break;
				case 'select':
					var selected	= '';
					html	= '<select name="' + cfg.boxName + '" style="border:none;width:90%;height:80%;">';
					for	(var o = 0; o < cfg.optionList.length; o++){
						selected	= '';
						if	(val == cfg.optionList[o])	selected	= 'selected';
						html	+= '<option value="' + cfg.optionList[o] + '" ' + selected + '>' + cfg.optionList[o] + '</option>';
					}
					html	+= '</select>';
					html	+= '<input type="hidden" value="' + val + '" />';

					obj.append(html);
				break;
				case 'autoComplete':
					obj.css('position', 'relative');
					if	(cfg.boxName && that.settings.chgViewDataFunc){
						var func	= window[that.settings.chgViewDataFunc];
						html	= '<span>' + func(cfg.boxName, val, obj) + '</span>';
					}else{
						html	= '<span>' + val + '</span>';
					}
					html	+= '<input type="text" name="' + cfg.boxName + '" value="' + val + '" style="border:none;width:90%;height:80%;display:none;" />';
					html	+= '<div class="excel-table-drop-down-lay" style="position:absolute;top:100%;left:0;width:100%;background-color:#fff;border:1px solid #a2a2a2;display:none;text-align:left;z-index:1004;"></div>'
					obj.append(html);
				break;
				case 'image':
					var size	= '';
					if	(cfg.w > 0)	size	+= ' width="' + cfg.w + '" ';
					if	(cfg.h > 0)	size	+= ' height="' + cfg.h + '" ';
					if	(val){
						if (cfg.noimg)	html	= '<img src="' + val + '" ' + size + ' onerror="this.src=\'' + cfg.noimg + '\';" />';
						else			html	= '<img src="' + val + '" ' + size + ' />';
					}else if (cfg.noimg){
						html	= '<img src="' + cfg.noimg + '" ' + size + ' />';
					}
					if	(cfg.boxName)	html	+= '<input type="hidden" name="' + cfg.boxName + '" value="' + val + '" />';
					obj.append(html);
				break;
				case 'plain':
					if	(cfg.boxName && that.settings.chgViewDataFunc){
						var func	= window[that.settings.chgViewDataFunc];
						html	= '<span class="' + cfg.boxName + '">' + func(cfg.boxName, val, obj) + '</span>';
					}else{
						html	= val;
					}
					obj.append(html);
				break;
				case 'text':
				default:
					if	(cfg.boxName && that.settings.chgViewDataFunc){
						var func	= window[that.settings.chgViewDataFunc];
						html	= '<span>' + func(cfg.boxName, val, obj) + '</span>';
					}else{
						html	= '<span>' + val + '</span>';
					}
					html	+= '<input type="text" name="' + cfg.boxName + '" value="' + val + '" style="border:none;width:80%;height:80%;display:none;" />';
					obj.append(html);
				break;
			}
		};

		this.setBind		= function(obj, cfg){
			if	(!that.settings.viewMode){
				// 종류별 bind
				switch(cfg.type){
					case 'checkbox':
						obj.find("input[type='checkbox']").bind('click', function(){
							if	(cfg.userFunc){
								var func	= window[cfg.userFunc];
								func(obj, $(this).val());
							}
						});
					break;
					case 'select':
						obj.find('select').bind('change', function(){
							if	(cfg.userFunc){
								var func	= window[cfg.userFunc];
								func(obj, $(this).val());
							}

							var row_idx	= that.tbodyObj.find('tr').index($(this).closest('tr'));
							if	( (that.tbodyObj.find('tr').length - 1) == row_idx)	that.addDefaultRow('', []);
						});
						obj.find('select').bind('blur', function(){
							obj.blur();
						});

					break;
					case 'autoComplete':
						obj.unbind('dblclick');
						obj.bind('dblclick', function(){
							$(this).find('span').hide();
							$(this).find('input').show();
							$(this).find('input').focus();

							// input box blur 이벤트
							$(this).find('input').attr('autocomplete', 'off');
							$(this).find('input').bind('blur', function(){
								$(obj).find('span').show();
								$(obj).find('input').val($(obj).find('span').html()).hide();
								$(obj).find('div.excel-table-drop-down-lay').hide();
								$(obj).blur();
							});

							$(this).find('input').bind('keyup', function(){
								if	(cfg.userFunc){
									var keyword		= $(this).val();
									var func1		= window[cfg.userFunc];
									func1(this, function(result, returnFunc, userParam){
										var divObj	= '';
										$(obj).find('div.excel-table-drop-down-lay').html('');
										if	(result){
											var txt	= '';
											for	( var r = 0; r < result.length; r++){
												txt		= result[r].replace(keyword, '<B>' + keyword + '</B>');
												divObj	= $('<div style="margin-left:5px;cursor:pointer;">' + txt + '</div>').appendTo($(obj).find('div.excel-table-drop-down-lay'));
												divObj.unbind('mouseover');
												divObj.unbind('mouseout');
												divObj.unbind('mousedown');
												divObj.bind('mouseover', function(){	$(this).css('background-color', '#eaeaea');	});
												divObj.bind('mouseout', function(){	$(this).css('background-color', '#ffffff');	});
												divObj.bind('mousedown', function(){
													$(obj).find('span').html($(this).text()).show();
													$(obj).find('input').val($(this).text()).hide();

													var idx	= $(obj).find('div.excel-table-drop-down-lay').find('div').index(this);
													if	(returnFunc){
														var func2	= window[returnFunc];
														if	(userParam){
															func2(idx, obj, $(this).html(), userParam);
														}else{
															func2(idx, obj, $(this).html());
														}
													}
													$(obj).find('div.excel-table-drop-down-lay').hide();
													$(obj).focus();

													var row_idx	= that.tbodyObj.find('tr').index($(this).closest('tr'));
													if	( (that.tbodyObj.find('tr').length - 1) == row_idx)	that.addDefaultRow('', []);
												});
											}
										}
										$(obj).find('div.excel-table-drop-down-lay').show();
									});
								}else{
									var divObj	= '';
									$(obj).find('div.excel-table-drop-down-lay').html('');
									divObj	= $('<div>' + $(this).val() + '</div>').appendTo($(obj).find('div.excel-table-drop-down-lay'));
									divObj.bind('mousedown', function(){
										$(obj).find('span').html($(this).html()).show();
										$(obj).find('input').val($(this).html()).hide();
										$(obj).find('div.excel-table-drop-down-lay').hide();
										$(obj).focus();

										var row_idx	= that.tbodyObj.find('tr').index($(this).closest('tr'));
										if	( (that.tbodyObj.find('tr').length - 1) == row_idx)	that.addDefaultRow('', []);
									});
									$(obj).find('div.excel-table-drop-down-lay').show();
								}
							});

							if	(!$(this).find('input').val()){
								$(this).find('input').val(' ');
								$(this).find('input').keyup();
								$(this).find('input').val('');
							}else{
								$(this).find('input').keyup();
							}
						});
					break;
					case 'text':
						obj.unbind('dblclick');
						obj.bind('dblclick', function(){
							$(this).find('span').hide();
							if	(cfg.focusFunc){
								$(this).find('input').unbind('focus');
								$(this).find('input').bind('focus', function(){
									var func	= window[cfg.focusFunc];
									func($(this));
								});
							}
							$(this).find('input').show();
							$(this).find('input').focus();
							$(this).find('input').unbind('blur');
							$(this).find('input').bind('blur', function(){
								if	(cfg.userFunc){
									var func	= window[cfg.userFunc];
									func($(this), $(this).val());
								}
								if	(that.settings.chgViewDataFunc){
									var func	= window[that.settings.chgViewDataFunc];
									obj.find('span').html(func($(this).attr('name'), $(this).val(), obj));
								}else{
									obj.find('span').html($(this).val());
								}
								obj.find('input').hide();
								obj.find('span').show();
								if	($(this).val()){
									var row_idx	= that.tbodyObj.find('tr').index($(this).closest('tr'));
									if	( (that.tbodyObj.find('tr').length - 1) == row_idx)	that.addDefaultRow('', []);
								}
							});
						});
					break;
				}
			}

			that.setDefaultBind(obj, cfg);
		};

		this.setDefaultBind		= function(obj, cfg){
			// 기본 td bind
			obj.focusin(function(){
				that.resetTdSelect();

				var border	= that.settings.selectedBorder;
				if	(border)	$(this).css('border', border);
				var rowidx	= that.tbodyObj.find('tr').index($(this).closest('tr'));
				var colidx	= $(this).closest('tr').find('td').index(this);
				that.currnetPosition	= rowidx + '-' + colidx;

				// blur event
				$(this).bind('blur', function(){
					that.resetTdSelect();
				});
			});
		};

		// td에 선택표시를 초기화
		this.resetTdSelect		= function(){

			var border	= that.settings.selectedBorder;
			if	(that.currnetPosition){
				var tmp			= that.currnetPosition.split('-');
				if	(border){
					var orgBorder	= '';
					$(this).closest('tr').find('td').each(function(){
						if	($(this).css('border') && $(this).css('border') != border){
							orgBorder	= $(this).css('border');
							return false;
						}
					});
					that.tbodyObj.find('tr').eq(tmp[0]).find('td').eq(tmp[1]).css('border', orgBorder);
				}
				that.tbodyObj.find('tr').eq(tmp[0]).find('td').eq(tmp[1]).unbind('keyup');
				that.tbodyObj.find('tr').eq(tmp[0]).find('td').eq(tmp[1]).unbind('blur');
			}
			that.currnetPosition	= '';
		};

		// 현재 설정된 옵션 정보 추출
		this.getSettingInfo	= function(){
			return that.settings;
		};

		// plugin 제거
		this.destroyTable	= function(){
			that.html('');
		};

		return this.each(function(){
			that.createTable();
			that.defaultRows();
		});
	};


})( $, window, document );


// 모두 체크 함수 처리
function excelTableAllCheck(obj, wrap_id, chkClass, userFunc){
	var chkStatus	= ($(obj).attr('checked')) ? true : false;
	$('div#' + wrap_id).find('td.' + chkClass + ' input').attr('checked', chkStatus);

	if	(userFunc){
		var func	= window[userFunc];
		func();
	}
}