/*
* 진행바 생성 plugin ( ver.0.1 )
* @2015-11-18 kdy
* options 
	'debugMode'			: 디버그 모드 ( bloom )
	'loadMode'				: 사전 로드 모드 ( bloom )
	'useModal'				: 모달형태 사용여부 ( bloom ), 
	'bgColor'				: 모달배경 레이어 배경색 ( string color code ), 
	'bgOpacity'				: 모달배경 레이어 투명도 ( decimal max 1 ), 
	'zIndex'					: 진행바 z-index 시작값 ( int ), 
	'barWidth'				: 진행바 가로사이즈 ( int ), 
	'barHeight'				: 진행바 세로사이즈 ( int ), 
	'barOutPadding'		: 진행바 외곽 여백 크기 ( int ), 
	'barOutBgColor'		: 진행바 외곽 여백 색상 ( string color code ), 
	'barOutBorderColor'	: 진행바 외곽 테두리 색상 ( string color code ), 
	'barInBg'					: 진행바 배경색 ( string color code ), 
	'barInBorderColor'	: 진행바 테두리 색상 ( string color code ), 
	'barColor'				: 진행바 색상 ( string color code ), 
	'barEndColor'			: 진행바 끝 테두리 색상 ( string color code ), 
	'useTitle'					: 진행바 타이틀 사용여부 ( bloom ), 
	'titleBarText'			: 진행바 타이틀 문구 ( string ), 
	'titleFontColor'			: 진행바 타이틀 문구 색상 ( string color code ), 
	'useBarText'				: 진행바 진행율 text 표시여부 ( bloom ), 
	'barTextColor'			: 진행바 진행율 text 색상 ( string color code ), 
	'useBarFlow'			: 진행바 흐름효과 사용여부 ( bloom ),
	'defaultLink'			: 진행바 처리 영역 기본URL ( string url )

*/
;(function ($, window, document, undefined){

	var pluginName	= 'fmprogressbar'
	, defaults		= {
		'debugMode'		: false, 
		'loadMode'			: false, 
		'useDetail'			: false, 
		'useModal'			: true, 
		'bgColor'			: '#333', 
		'bgOpacity'			: '0.5', 
		'zIndex'				: '50000', 
		'barWidth'			: '300', 
		'barHeight'			: '16', 
		'barOutPadding'	: '0', 
		'barOutBgColor'	: '#fff', 
		'barOutBorderColor'	: '#888', 
		'barInBg'				: '#fff', 
		'barInBorderColor': '#888', 
		'barColor'			: '#75bfff', 
		'barEndColor'		: '#888', 
		'useTitle'				: false, 
		'titleBarText'		: 'Loading....', 
		'titleFontColor'		: '#666', 
		'useBarText'			: true, 
		'barTextColor'		: '#fff',
		'useBarFlow'		: true, 
		'detailHeight'		: '300',
		'detailBgColor'		: '#ffffff',
		'defaultLink'		: '', 
		'procgressEnd'		: function(){} 
	};

	// _construct_
	$.fn.fmprogressbar	= function(options){
		var that				= this;
		this.percent			= 0;
		this.endStatus			= false;
		this.intervalObj		= '';
		this.frameNum			= '1';
		this.settings			= $.extend({}, defaults, options);

		// 기본 Progressbar UI 생성
		this.createProgressBar	= function(){
			if	(that.settings.useModal){
				var bgLay	= $('<div id="progressbar_background"></div>').appendTo($('body'));
				bgLay.css({
					'position':'absolute',
					'top':'0',
					'left':'0',
					'z-index':that.settings.zIndex, 
					'width':'100%', 
					'height':$(document).height(), 
					'background-color':that.settings.bgColor, 
					'opacity':that.settings.bgOpacity
				});
			}


			// 외곽 Lay 생성
			var outwidth	= parseInt(that.settings.barWidth) + (parseInt(that.settings.barOutPadding) * 2);
			var outHeight	= that.settings.barHeight;
			if	(that.settings.useDetail){
				outHeight	= parseInt(that.settings.barHeight) + parseInt(that.settings.detailHeight);
			}
			if	(that.settings.useTitle)	outHeight	= parseInt(outHeight) + 14;
			var margin_top	= Math.round(outHeight / 2) * -1;
			var margin_left	= Math.round(outwidth / 2+15) * -1;
			var zIndex		= that.settings.zIndex + 1;
			$(this).css({
				'position':'absolute',
				'top':'50%',
				'left':'50%',
				'width':outwidth + 'px',
				'height':outHeight + 'px',
				'padding':that.settings.barOutPadding + 'px', 
				'padding-bottom':'20px',
				'margin-top':margin_top + 'px',
				'margin-left':margin_left + 'px',
				'background-color':that.settings.barOutBgColor, 
				'border':'1px solid ' + that.settings.barOutBorderColor,
				'z-index':zIndex
			});

			// 타이틀 영역
			if	(that.settings.useTitle)	{
				// Bar Lay 생성
				var titLay		= $('<div class="prtitle"></div>').appendTo($(this));
				titLay.css({
					'width':'100%', 
					'padding-bottom':'5px',
					'color':that.settings.titleFontColor
				});
				titLay.html(that.settings.titleBarText);
			}


			// Bar Lay 생성
			var inLay		= $('<div></div>').appendTo($(this));
			inLay.css({
				'height':that.settings.barHeight + 'px', 
				'border':'1px solid ' + that.settings.barInBorderColor, 
				'background-color':that.settings.barInBg 
			});

			// 진행바 생성
			var bar			= $('<div class="prbar"></div>').appendTo(inLay);
			bar.css({
				'position':'relative', 
				'width':that.percent + '%', 
				'height':that.settings.barHeight + 'px', 
				'background-color':that.settings.barColor, 
				'border-right':'1px solid ' + that.settings.barEndColor 
			});

			// 자세히 보기 영역
			if	(that.settings.useDetail){
				var detailArea	= $('<div class="prdetail"></div>').appendTo($(this));
				detailArea.css({
					'width':'100%', 
					'height':that.settings.detailHeight + 'px', 
					'margin-top':'3px', 
					'overflow-y':'auto',
					'background-color':that.settings.detailBgColor, 
					'border':'1px solid ' + that.settings.barOutBorderColor 
				});
			}

			// 처리영역 생성
			if	(that.settings.defaultLink){
				var procName	= $(this).attr('id') + 'Frame' + that.frameNum;
				var procArea	= $('<iframe name="' + procName + '" class="prArea" src="' + that.settings.defaultLink + '"></iframe>').appendTo($(this));
				procArea.attr('frameborder', '0');
				procArea.css({
					'width':'100%', 
					'height':'0'
				});
				if	(that.settings.debugMode){
					procArea.attr('frameborder', '1');
					procArea.css('height',600);
					var debugHeight	= parseInt($(this).css('height')) + 600;
					$(this).css({'top':'0','left':'170px','width':'90%', 'height':debugHeight + 'px'});
				}
				that.frameNum++;
			}

			// 효과
			if	(that.settings.useBarFlow){
				zIndex++;
				var barmotion	= $('<div></div>').appendTo(bar);
				barmotion.css({
					'position':'absolute', 
					'top':'0', 
					'left':'0', 
					'height':that.settings.barHeight + 'px', 
					'width':'0', 
					'background-color':'#ffffff', 
					'opacity':'0.5', 
					'-moz-opacity':'0.5', 
					'z-index':zIndex 
				});
				that.setFlowAnimate(barmotion);
			}

			// 퍼센트 표시
			if	(that.settings.useBarText){
				// zIndex
				zIndex++;
				var bartext		= $('<div class="prbartext">0%</div>').appendTo(bar);
				bartext.css({
					'position':'absolute', 
					'width':'100%', 
					'top':'2px', 
					'left':'0', 
					'z-index':zIndex, 
					'color':that.settings.barTextColor, 
					'text-align':'center' 
				});
			}

			if	(that.settings.loadMode)	that.closeProgress();
		};

		// 효과동작
		this.setFlowAnimate	= function(obj){
			that.intervalObj	= setInterval(function(){
				$(obj).animate({'width':that.settings.barWidth - 1},3000,function(){
					$(obj).animate({'left':that.settings.barWidth - 1,'width':0},3000,function(){
						$(obj).css('left',0);
					});
				});
			}, 6000);
		};

		// 처리영역 src변경
		this.chgProcFrameSrc	= function(link, num){
			if	(!num)	num	= '1';
			var procName	= $(this).attr('id') + 'Frame' + num;
			$(this).find("iframe[name='" + procName + "']").attr('src', link);
		};

		// 처리영역 추가 생성
		this.addProcFrame	= function(addLink){
			var procName	= $(this).attr('id') + 'Frame' + that.frameNum;
			var procArea	= $('<iframe name="' + procName + '" class="prArea" src="' + addLink + '"></iframe>').appendTo($(this));
			procArea.attr('frameborder', '0');
			procArea.css({
				'width':'100%', 
				'height':'0'
			});
			if	(that.settings.debugMode){
				procArea.attr('frameborder', '1');
				procArea.css('height',100);
				var debugHeight	= parseInt($(this).css('height')) + 600;
				$(this).css({'height':debugHeight + 'px'});
			}
			that.frameNum++;
		};

		// 퍼센트 적용
		this.movePercent	= function(){
			$(this).find('div.prbar').animate({'width':this.percent+'%'}, function(){
				$(that).find('div.prbartext').html(that.percent+'%');
				// progress 종료

				if	(!that.endStatus && that.percent >= 100){
					that.endStatus	= true;
					if	(that.settings.procgressEnd){
						var func	= window[that.settings.procgressEnd];
						func();
					}else{
						that.closeProgress();
					}
				}
			});
		};

		// 타이틀 변경
		this.chgTitle	= function(title){
			if	(that.settings.useTitle){
				$(this).find('div.prtitle').html(title);
			}
		};

		// 퍼센트 증가
		this.addPercent	= function(per){
			this.percent	= parseInt(this.percent) + parseInt(per);
			if		(this.percent > 100)	this.percent	= 100;
			else if	(this.percent < 0)		this.percent	= 0;

			this.movePercent();
		};

		// 퍼센트 적용
		this.setPercent	= function(per){
			this.percent	= per;
			if		(this.percent > 100)	this.percent	= 100;
			else if	(this.percent < 0)		this.percent	= 0;

			this.movePercent();
		};

		// load mode일 때 open 처리
		this.openProgress	= function(){
			$('div#progressbar_background').show();
			$(this).show();
		};

		// progressbar close
		this.closeProgress	= function(){
			$('div#progressbar_background').hide();
			$(this).hide();
		};

		// 자세히 보기에 log 정보 추가
		this.addDetailLog	= function(logStr){
			$(this).find('div.prdetail').append('<br/>' + logStr);
			$(this).find('div.prdetail').scrollTop($(this).find('div.prdetail').prop('scrollHeight'));
		};

		return this.each(function(){
			that.createProgressBar();
		});
	};


})( $, window, document );
