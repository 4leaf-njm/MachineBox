/*
* 목록 테이블의 head부분을 fixed함 ( ver.0.1 )
* @2016-08-29 kdy
*/
;(function ($, window, document, undefined){

	var pluginName		= 'fmfixedtabletitle'
	, defaults			= {
			'wrap'			: window, 
			'positionType'	: 'fixed', 
			'runPoint'		: '', 
			'cellTag'		: 'th', 
			'height'		: 0, 
			'padding'		: 0, 
			'addTop'		: 0, 
			'addStyle'		: [] 
	};

	// _construct_
	$.fn.fmfixedtabletitle	= function(options){
		var that			= this;
		this.fixedType		= false;
		this.settings		= $.extend({}, defaults, options);
		this.copyStyleList	= [	'background', 'background-color', 'font-size', 'font-weight', 'text-decoration', 
								'border', 'border-left', 'border-right', 'border-top', 'border-bottom',
								'text-align',  'line-height',  'padding',  'padding-left',  'padding-right', 
								'padding-top', 'padding-bottom' ];
		this.headObj		= '';


		this.setScrollEvent	= function(){
			that.headObj	= $(this);
			// div 추가
			var divHTML		= '';
			that.headObj.find(that.settings.cellTag).each(function(){
				divHTML		= '<div>' + $(this).html() + '</div>';
				$(this).html(divHTML);
			});

			$(that.settings.wrap).scroll(function(){
				var scrollTop	= parseInt($(that.settings.wrap).scrollTop());
				if			(scrollTop > that.settings.runPoint && !that.fixedType){
					that.fixedType	= true;
					that.headObj.find(that.settings.cellTag).each(function(){
						that.setChangeFixed($(this));
					});
				}else if	(scrollTop > that.settings.runPoint && that.fixedType){
					that.headObj.find(that.settings.cellTag).each(function(){
						that.calculateFixedPosition($(this), $(this).find('div'));
					});
				}else if	(scrollTop <= that.settings.runPoint && that.fixedType){
					that.fixedType	= false;
					that.headObj.find(that.settings.cellTag).each(function(){
						that.setChangeTitle($(this));
					});
				}
				var scrollLeft	= parseInt($(that.settings.wrap).scrollLeft());

			});
		};

		this.setChangeFixed		= function(obj){
			var divObj		= $(obj).find('div');
			if	($(obj).attr('class'))	divObj.attr('class', $(obj).attr('class'));
			divObj.css('position', that.settings.positionType);
			// class내부 style중 필수요소를 별도 style copy한다.
			var styleCnt	= that.copyStyleList.length;
			for	(var s = 0; s < styleCnt; s++){
				if	($(obj).css(that.copyStyleList[s]))
					divObj.css(that.copyStyleList[s], $(obj).css(that.copyStyleList[s]));
			}
			if	(that.settings.addStyle)	divObj.css(that.settings.addStyle);
			that.calculateFixedPosition($(obj), divObj);
			$(obj).attr('class', '').css('padding', '0');
		};

		this.calculateFixedPosition	= function(cellObj, divObj){
			var rowIdx		= that.headObj.find('tr').index(cellObj.closest('tr'));
			var rowspan		= cellObj.attr('rowspan');
			var width		= Math.floor(cellObj.width());
			var height		= that.settings.height;
			var padding		= that.settings.padding;
			var top			= that.settings.addTop + ( ( height + ( padding * 2 ) ) * rowIdx ) + rowIdx;
			if	(rowspan > 1){
				padding	= ( ( padding * 2 * rowspan ) + ( height * (rowspan - 1) ) + (rowspan - 1) ) / 2;
			}
			if	(that.settings.positionType == 'absolute'){
				top			= parseInt(top) + parseInt($(that.settings.wrap).scrollTop());
			}

			divObj.css({
				'top'				: top + 'px', 
				'padding'			: padding + 'px 0', 
				'width'				: width + 'px', 
				'height'			: height + 'px'
			});
		};

		this.setChangeTitle		= function(obj){
			var divObj		= $(obj).find('div');
			$(obj).attr('class', divObj.attr('class')).css('padding', that.settings.padding);
			divObj.attr('class', '').css({
				'position'	: 'relative', 
				'top'		: '0', 
				'padding'	: '0', 
				'width'		: 'auto', 
				'border'	: 'none'
			});
		};

		return this.each(function(){
			that.setScrollEvent();
		});
	};

})( $, window, document );