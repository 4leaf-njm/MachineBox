/**
 * Animation Banner (jQuery 1.7.2, jQuery UI 1.8.16 기반)
 * Author : Ok Chang Won
 * Date : 2014.08.01
**/
if(typeof animationBannerScriptLoaded == 'undefined'){
	var animationBannerScriptLoaded = true;
	$(function() {
		$.widget( "custom.anibanner", {
			// default options
			options: {
				'platform' : "",
				'modtime' : "",
				'style' : "",
				'height' : "310",
				'background_color' : "#ffffff",
				'background_image' : "",
				'background_repeat' : "",
				'background_position' : "",
				'image_border_use' : "n",
				'image_border_width' : "0",
				'image_border_color' : "#000000",
				'image_opacity_use' : "n",
				'image_opacity_percent' : "0",
				'image_top_margin' : "0",
				'image_side_margin' : "0",
				'image_width' : "852",
				'image_height' : "310",
				'navigation_btn_style' : "",
				'navigation_btn_visible' : "fixed",
				'navigation_paging_style' : "custom",
				'navigation_paging_align' : "center",
				'navigation_paging_position' : "bottom",
				'navigation_paging_margin' : "10",
				'navigation_paging_spacing' : "0",
				'slide_event' : "auto",
				'images' : [
					/*{"link":"/main/index","image":"/admin/skin/default/images/design/banner/pc_style_4/st4_sample1.jpg"}*/
				],
				'navigation_paging_custom_images' : [
					/*{
						"active":"/admin/skin/default/images/design/banner/pc_style_4/tab1_on.jpg",
						"inactive":"/admin/skin/default/images/design/banner/pc_style_4/tab1.jpg"
					}
					*/
				]
			},

			// the constructor
			_create: function() {
				var that = this;

				this.primary_key = Math.floor(Math.random()*1000000000);

				if(this.options['style']==null) return;
				if(this.options['style'].length==0) return;
				if(this.options['images'].length==0) return;

				$(this.element).addClass('anibanner').attr('primary_key',this.primary_key).empty().unbind('mouseenter').unbind('mouseleave').unbind('selectstart');
				if(typeof that.timer != 'undefined'){
					clearInterval(that.timer);
				}
				this.wrapObj = $('<div></div>').appendTo(this.element).hide();

				this.adjust_margin_left = 0;
				this.now_page = 0;
				this.perpage = 1;
				this.item_cnt = this.options['images'].length;
				this.now_idx = this.item_cnt;
				this.slide_event_sec = 4; // 자동 슬라이딩 대기시간(초)
				this._move_page_complete = function(){};
				this.image_fullsize = false;

				if(this.options['style']=='mobile_style_2' || this.options['style']=='mobile_style_3') {
					this.image_fullsize = true;
				}

				if(this.options['images'].length) this._add_images();								// 이미지 엘리먼트 추가
				if(this.item_cnt>this.perpage){
					if(this.options['navigation_btn_style'].length) this._add_navigation_btn();			// 네비게이션 버튼 추가
					if(this.options['navigation_paging_style'].length) this._add_navigation_paging();	// 네비게이션 페이징 추가
				}

				if(this.options['style']=='pc_style_1') {
					this.perpage = 3;
				}

				$(this.element).css({
					'width'					: 'auto',
					'position'				: 'relative',
					'overflow'				: 'hidden',
					'min-height'			: this.options['height']?this.options['height']+'px':'auto',
					'background-color'		: this.options['background_color']?this.options['background_color']:'#ffffff'
				}).bind('selectstart',function(){return false;});

				if(this.options['background_repeat']=='none'){
					$(this.element).css({
						'background-image'		: 'none'
					});
				}else{
					$(this.element).css({
						'background-image'		: this.options['background_image']?"url('"+this.options['background_image']+"?"+this.options['modtime']+"')":'none',
						'background-repeat'		: this.options['background_repeat']?this.options['background_repeat']:'repeat',
						'background-position'	: this.options['background_position']?this.options['background_position']:'left top'
					});
				}

				switch(this.options['style']){
					case "pc_style_1":
						this.set_pc_style_1();
					break;
					case "pc_style_2":
						this.set_pc_style_2();
					break;
					case "pc_style_3":
						this.set_pc_style_3();
					break;
					case "pc_style_4":
						this.set_pc_style_4();
					break;
					case "pc_style_5":
						this.set_pc_style_5();
					break;
					case "mobile_style_1":
						this.set_mobile_style_1();
						this._set_swipe();
					break;
					case "mobile_style_2":
						this.set_mobile_style_2();
						this._set_swipe();
					break;
					case "mobile_style_3":
						this.set_mobile_style_3();
						this._set_swipe();
					break;
					case "light_style_1":
						this.set_mobile_style_1();
						this._set_swipe();
					break;
					case "light_style_2":
						this.set_mobile_style_2();
						this._set_swipe();
					break;
					default:
						alert(this.options['style'] + " - 정의되지 않은 스타일입니다.");
					break;
				}

				// 타이머
				if(this.item_cnt>this.perpage){
					if(this.options['slide_event']=='auto'){
						that.timer = setInterval(function(){
							if($(".anibanner[primary_key='"+that.primary_key+"']").length){
								that._move_page(+1);
							}
						},that.slide_event_sec*1000);
						$(that.element).bind(this.options['platform']=='mobile' ? 'dragstart' : 'mouseenter',function(){
							clearInterval(that.timer);
						}).bind(this.options['platform']=='mobile' ? 'dragstop' : 'mouseleave',function(){
							that.timer = setInterval(function(){
								if($(".anibanner[primary_key='"+that.primary_key+"']").length){
									that._move_page(+1);
								}
							},that.slide_event_sec*1000);
						});
					}
				}
				this.wrapObj.show();

			},
			_destroy: function() {
				$(this.element).empty();
				$.Widget.prototype.destroy.apply(this,arguments);
			},
			_setOptions: function() {
				// in 1.9 would use _superApply
				$.Widget.prototype._setOptions.apply( this, arguments );
				this._create();
			},
			// _setOption is called for each individual option that is changing
			_setOption: function( key, value ) {
				// in 1.9 would use _super
				$.Widget.prototype._setOption.call( this, key, value );
			},

			// 이미지 엘리먼트 추가
			_add_images: function(){
				this.listObj = $('<ul class="anibanner_image_list"></ul>').appendTo(this.wrapObj);

				for(var i=0 ; i < this.options['images'].length ; i++){
					if(this.image_fullsize){
						var listItemObj = $('<li><div class="anibanner_image_wrapper"><img class="anibanner_image_element" src="'+this.options['images'][i]['image']+'?'+this.options['modtime']+'" width="100%" /></div></li>');
					}else{
						var listItemObj = $('<li><div class="anibanner_image_wrapper"><img class="anibanner_image_element" src="'+this.options['images'][i]['image']+'?'+this.options['modtime']+'" width="'+this.options['image_width']+'" height="'+this.options['image_height']+'" /></div></li>');
					}
					if(this.options['images'][i]['link']){
						listItemObj.find('img.anibanner_image_element').wrap("<a href='"+this.options['images'][i]['link']+"'/>");
						if(this.options['images'][i]['target']){
							listItemObj.find('a').attr('target',this.options['images'][i]['target']);
						}
					}
					this.listObj.append(listItemObj);
				}

				if(this.options['image_opacity_use']=='y'){
					var opacity = parseInt(this.options['image_opacity_percent']);
					$(">li",this.listObj).bind('mouseenter',function(){
						$(this).find("img.anibanner_image_element").css('opacity',(100-opacity)/100);
					}).bind('mouseleave',function(){
						$(this).find("img.anibanner_image_element").css('opacity',1);
					});
				}

				if(this.options['image_border_use']=='y' && parseInt(this.options['image_border_width'])>0){
					$("img.anibanner_image_element",this.listObj).after('<span class="anibanner_image_border" />');

					var border_width = parseInt(this.options['image_border_width']);
					var border_color = this.options['image_border_color'];

					$("span.anibanner_image_border",this.listObj).css({
						'position' : 'absolute',
						'left' : '0px',
						'top' : '0px',
						'width' : (this.options['image_width']-border_width*2)+'px',
						'height' : (this.options['image_height']-border_width*2)+'px',
						'border':border_width+'px solid '+border_color
					});

					$(">li",this.listObj)
					.bind('mouseenter',function(){
						$(this).find("span.anibanner_image_border").show();
					})
					.bind('mouseleave',function(){
						$(this).find("span.anibanner_image_border").hide();
					});
				}
			},

			// 네비게이션 버튼 추가
			_add_navigation_btn: function(){
				var that = this;

				this.prevBtnObj = $('<span class="anibanner_navigation_btn anibanner_navigation_btn_prev anibanner_navigation_'+this.options['navigation_btn_style']+'_prev"></span>');
				this.nextBtnObj = $('<span class="anibanner_navigation_btn anibanner_navigation_btn_next anibanner_navigation_'+this.options['navigation_btn_style']+'_next"></span>');

				if(this.options['navigation_btn_visible']=='fixed'){
					$(this.element).append(this.prevBtnObj).append(this.nextBtnObj);
				}else{
					$(this.element).append(this.prevBtnObj).append(this.nextBtnObj);

					$(this.element).bind('mouseenter',function(){
						$(".anibanner_navigation_btn",this).stop(true,true).fadeIn();
					}).bind('mouseleave',function(){
						$(".anibanner_navigation_btn",this).stop(true,true).fadeOut();
					});

					$(".anibanner_navigation_btn",this.element).hide();
				}

				// 이전 버튼 클릭이벤트
				this.prevBtnObj.bind('click',function(){
					that._move_page(-1);
				});

				// 다음 버튼 클릭이벤트
				this.nextBtnObj.bind('click',function(){
					that._move_page(+1);
				});

			},

			// 네비게이션 페이징 추가
			_add_navigation_paging: function(){
				var that = this;

				this.pagingObj = $('<ul class="anibanner_navigation_paging anibanner_navigation_'+this.options['navigation_paging_style']+'"></ul>');
				this.max_page = Math.ceil(this.options['images'].length/this.perpage);

				$(this.element).append(this.pagingObj);

				if(this.options['navigation_paging_align']=='left' || this.options['navigation_paging_align']=='right'){
					this.pagingObj.css('float',this.options['navigation_paging_align']);
				}else{
					$(this.element).css('text-align','center');
				}

				switch(this.options['navigation_paging_style']){
					case "custom":
						for(var i=0 ; i < this.max_page ; i++){
							if(this.options['navigation_paging_custom_images'][i])
							this.pagingObj.append("<li><img src='"+this.options['navigation_paging_custom_images'][i]['inactive']+"?"+this.options['modtime']+"' class='inactiveImg' /><img src='"+this.options['navigation_paging_custom_images'][i]['active']+"?"+this.options['modtime']+"' class='activeImg' /></li>");
						}
						this.pagingObj.children().first().addClass('current');

						if(parseInt(this.options['navigation_paging_spacing'])){
							this.pagingObj.children(":gt(0)").css('margin-left',this.options['navigation_paging_spacing']+'px');
						}

						// 페이징 클릭이벤트
						this.pagingObj.height(this.options['navigation_paging_height']);
						this.pagingObj.children().each(function(i){
							$(this).bind(that.options['slide_event']=='mouseover'?'mouseover':'click',function(){
								that._move_page(i-that.now_page);
							});
						});
						that.pagingObj.find('img').last().load(function(){
							that._set_paging_position();
						});
					break;
					case "paging_style_4":
					case "paging_style_5":
						this.prevPagingBtnObj = $('<li class="paging_btn_prev"></li>');
						this.nextPagingBtnObj = $('<li class="paging_btn_next"></li>');
						$(this.pagingObj).append(this.prevPagingBtnObj);
						$(this.pagingObj).append('<li class="paging_btn_body"><span class="paging_btn_num_now"></span>/<span class="paging_btn_num_max"></span></li>');
						$(this.pagingObj).append(this.nextPagingBtnObj);

						this.pagingObj.find(".paging_btn_num_now").html("1");
						this.pagingObj.find(".paging_btn_num_max").html(this.max_page);

						// 페이징 클릭이벤트
						this.prevPagingBtnObj.bind('click',function(){
							that._move_page(-1);
						});
						this.nextPagingBtnObj.bind('click',function(){
							that._move_page(+1);
						});
					break;
					case "paging_style_1":
					case "paging_style_2":
					case "paging_style_3":
					default:
						for(var i=0 ; i < this.max_page ; i++){
							this.pagingObj.append("<li>"+(i+1)+"</li>");
						}
						this.pagingObj.children().first().addClass('current');

						// 페이징 클릭이벤트
						this.pagingObj.children().each(function(i){
							$(this).bind(that.options['slide_event']=='mouseover'?'mouseover':'click',function(){
								that._move_page(i-that.now_page);
							});
						});
					break;
				}

				that.pagingObj.hide();
				if( this.options['style'] != 'mobile_style_2' && this.options['style'] != 'mobile_style_3' ) {//@pageheight 
					if(this.options['navigation_paging_style'] != 'custom'){
						setTimeout(function(){
							that._set_paging_position();
						},100);
					}
				}

			},

			_set_paging_position: function(){
				var that = this;
				that.options['image_top_margin'] = that.options['image_top_margin'] ? parseInt(that.options['image_top_margin']) : 0;
				that.options['image_height'] = that.options['image_height'] ? parseInt(that.options['image_height']) : 0;

				var margin_top = that.options['image_top_margin']+that.options['image_height'];
				if(that.options['navigation_paging_position']=='over'){
					margin_top = margin_top - parseInt(that.options['navigation_paging_margin']) - that.pagingObj.height();
				}else{
					margin_top = margin_top + parseInt(that.options['navigation_paging_margin']);
				}
				var margin_side = that.options['navigation_paging_position']=='over' ? that.options['navigation_paging_margin'] : 0;
				if(that.options['navigation_paging_style']=='custom'){
					that.pagingObj.css({'margin-top':margin_top+'px','margin-right':margin_side+'px','margin-left':margin_side+'px'}).fadeIn();
				}else{
					that.pagingObj.css({'margin-top':margin_top+'px','margin-right':margin_side+'px','margin-left':margin_side+'px'}).show();
				}
			},

			// 페이징 메소드
			_move_page: function(mod_page){
				var that = this;
				var move_idx = mod_page * this.perpage;

				// 스타일 1에서 음영부분이 이동시 메인 프레임에 보여지는 부분이 있어 추가
				switch(that.options['style']){
					case "pc_style_1":
						that.listObj.find('.anibanner_image_wrapper').css({'opacity':1});
					break;
				}

				// 맨오른쪽끝에 도달했을때 앞쪽으로 보정
				if(mod_page>0 && this.now_idx+move_idx > (this.item_cnt*2)-1-this.perpage){
					this.now_idx = this.now_idx-this.item_cnt;
					this.listObj.stop(true,true).css({
						'margin-left' : '+='+(this.item_cnt*that.move_width)+'px'
					});
				}

				// 맨왼쪽끝에 도달했을때 뒷쪽으로 보정
				if(mod_page<0 && this.now_idx+move_idx < 1){
					this.now_idx = this.now_idx+this.item_cnt;
					this.listObj.stop(true,true).css({
						'margin-left' : '-='+(this.item_cnt*that.move_width)+'px'
					});
				}

				this.listObj.stop(true,true).animate({
					'margin-left' : '-='+(move_idx*that.move_width+that.adjust_margin_left)+'px'
				},function(){
					that._move_page_complete();
				});

				this.adjust_margin_left = 0;
				this.now_idx = this.now_idx + move_idx;
				this.now_page = this.now_page + mod_page;
				if(this.now_page<0) this.now_page = this.max_page-1;
				if(this.now_page>=this.max_page) this.now_page = 0;

				if(this.pagingObj){
					this.pagingObj.children(".current").removeClass('current');
					this.pagingObj.children("li").eq(this.now_page).addClass('current');
					this.pagingObj.find(".paging_btn_num_now").html(this.now_page+1);
				}

			},

			// 페이징 완료시 처리
			_move_page_complete: function(){},

			// 스와이프 구현
			_set_swipe: function(){

				var that = this;

				var start_left = null;
				var end_left = null;

				var msTouchFlag = false;
				var mzSwipingFlag = false;		// 스와이핑중인지 여부

				var mzSwipingDirection = null;	// 방향 : w(가로) or h(세로)
				var mzSwipingStartX = null;		// 시작위치 x
				var mzSwipingStartY = null;		// 시작위치 y
				var mzSwipingMoveX = null;		// 이동한거리 x
				var mzSwipingMoveY = null;		// 이동한거리 y
				var mzSwipingLastTime = 0;

				$(document).on("click",this,function(e){
					if(that._get_timestamp()-mzSwipingLastTime < 1){
						return false;
					}
				});

				$(document).on("mousedown touchstart",this,function(e){
					if($(e.target).closest(that.listObj).length){
						msTouchFlag = true;
						if(typeof e.clientX == 'undefined'){
							var touch = e.changedTouches[0];
							mzSwipingStartX = touch.clientX;
							mzSwipingStartY = touch.clientY;
						}else{
							mzSwipingStartX = e.clientX;
							mzSwipingStartY = e.clientY;
						}
					}
				});

				$(document).on("mousemove touchmove",function(e){
					if(msTouchFlag){

						if(typeof e.clientX == 'undefined'){
							var touch = e.changedTouches[0];
							mzSwipingMoveX = touch.clientX-mzSwipingStartX;
							mzSwipingMoveY = touch.clientY-mzSwipingStartY;
						}else{
							mzSwipingMoveX = e.clientX-mzSwipingStartX;
							mzSwipingMoveY = e.clientY-mzSwipingStartY;
						}

						if(mzSwipingFlag){
							if(mzSwipingDirection=='x'){
								e.preventDefault();
								end_left = start_left+mzSwipingMoveX;
								// 상품 스와이핑
								that.listObj.stop(true,true).css({'margin-left':end_left});
							}
						}else if(Math.abs(mzSwipingMoveY)>5){
							mzSwipingDirection = 'y';
							mzSwipingFlag = false;
						}else if(Math.abs(mzSwipingMoveX)>5){
							e.preventDefault();
							mzSwipingDirection = 'x';
							mzSwipingFlag = true;
							that.listObj.stop(true,true);
							start_left = parseInt(that.listObj.css('margin-left'));
						}
					}
				});

				$(document).on("mouseup touchend",function(e){
					// 상품 스와이핑
					if(mzSwipingFlag){
						var move = 0;
						if(mzSwipingMoveX>50){
							move = -1;
						}else if(mzSwipingMoveX<-50){
							move = 1;
						}
						that.adjust_margin_left = mzSwipingMoveX;
						that._move_page(move);

						mzSwipingLastTime = that._get_timestamp();
					}
					
					msTouchFlag = false;
					mzSwipingTarget = null;
					mzSwipingDirection = null;
					mzSwipingStartX = null
					mzSwipingStartY = null;
					mzSwipingMoveX = null;
					mzSwipingMoveY = null;
					mzSwipingFlag = false;
				});

				/*
				var that = this;
				var start_left = null;
				var end_left = null;
				var start_client_y = null;

				this.listObj.draggable({axis:"x"});
				this.listObj.on("dragstart",function(e,ui){
					that.listObj.stop(true,true);
					start_left = null;

					start_client_y = event.clientY;
				});
				this.listObj.on("drag",function(e,ui){
					if(start_left == null)	start_left = parseInt(that.listObj.css('left'));

					if(parseInt(that.listObj.css('left'))-start_left>that.move_width*0.9 || start_left-parseInt(that.listObj.css('left'))>that.move_width*0.9){
						this.listObj.trigger('dragstop');
						return;
					}

					$('html,body').stop(true,true).animate({'scrollTop':'+='+(start_client_y-event.clientY)}).stop(true,true);
					start_client_y = event.clientY;

				});
				this.listObj.on("dragstop",function(e,ui){
					end_left = parseInt(that.listObj.css('left'));

					if(end_left-start_left > that.move_width/5){
						that.adjust_margin_left = end_left-start_left;
						
						that.listObj.css({
							'left': '50%',
							'margin-left' : '+='+that.adjust_margin_left+'px'
						});

						that._move_page(-1);
					}else if(start_left-end_left > that.move_width/5){
						that.adjust_margin_left = end_left-start_left;

						that.listObj.css({
							'left': '50%',
							'margin-left' : '+='+that.adjust_margin_left+'px'
						});

						that._move_page(+1);
					}else{
						that.listObj.animate({
							'left': '50%'
						});
					}

				});
				*/
			},

			// PC STYLE 1
			set_pc_style_1: function(){
				var that = this;

				this.move_width = parseInt(this.options['image_width'])+parseInt(this.options['image_side_margin']);

				var main_width = parseInt(this.options['image_width'])*this.perpage + parseInt(this.options['image_side_margin'])*(this.perpage-1);
				var main_height = parseInt(this.options['image_height']);
				var main_x = (main_width/2*-1);
				var main_y = parseInt(this.options['image_top_margin']);

				this.listObj.children('li').clone(true).appendTo(this.listObj);

				this.listObj.css({
					'position'		: 'absolute',
					'top'			: main_y+'px',
					'left'			: '50%',
					'margin-left'	: (main_x-(this.now_idx*(parseInt(this.options['image_width'])+parseInt(this.options['image_side_margin']))))+'px',
					'white-space'	: 'nowrap',
					'width'			: (parseInt(this.options['image_width'])+parseInt(this.options['image_side_margin']))*this.item_cnt*2
				});

				this.listObj.children('li').css({
					'float'				: 'left',
					'margin-right'		: this.options['image_side_margin']+'px'
				});

				if(this.prevBtnObj){
					this.prevBtnObj.css({
						'left' : '50%',
						'margin-left' : main_x-(this.prevBtnObj.width()/2)-(this.options['image_side_margin']/2),
						'top' : main_y+(main_height/2)-(this.prevBtnObj.height()/2)
					});
				}
				if(this.nextBtnObj){
					this.nextBtnObj.css({
						'left' : '50%',
						'margin-left' : main_x+main_width-(this.prevBtnObj.width()/2)+(this.options['image_side_margin']/2),
						'top' : main_y+(main_height/2)-(this.prevBtnObj.height()/2)
					});
				}

				// 페이지 완료시 좌우 투명처리
				this._move_page_complete = function(){
					this.listObj.find('li').css({'background-color':'#000'});
					this.listObj.find('.anibanner_image_wrapper').css({'opacity':0.5});
					for(var i=0;i<this.perpage;i++){
						this.listObj.find('.anibanner_image_wrapper:eq('+((this.now_idx%this.item_cnt)+i)+')').css('opacity',1);
						this.listObj.find('.anibanner_image_wrapper:eq('+((this.now_idx%this.item_cnt+this.item_cnt)+i)+')').css('opacity',1);
					}

				};
				this._move_page_complete();

			},

			// PC STYLE 2
			set_pc_style_2: function(){
				var that = this;

				this.move_width = parseInt(this.options['image_width'])+parseInt(this.options['image_side_margin']);

				var main_width = parseInt(this.options['image_width']) + parseInt(this.options['image_side_margin'])*2;
				var main_height = parseInt(this.options['image_height']) + parseInt(this.options['image_top_margin'])*2;
				var main_x = (main_width/2*-1);
				var main_y = parseInt(this.options['image_top_margin']);

				$(this.element).css({
					'width'			: main_width+'px',
					'height'		: main_height+'px'
				});

				this.listObj.children('li').clone(true).appendTo(this.listObj);

				this.listObj.css({
					'position'		: 'absolute',
					'top'			: main_y+'px',
					'left'			: '50%',
					'margin-left'	: (main_x-(this.now_idx*(parseInt(this.options['image_width'])+parseInt(this.options['image_side_margin']))))+'px',
					'white-space'	: 'nowrap',
					'width'			: (parseInt(this.options['image_width'])+parseInt(this.options['image_side_margin']))*this.item_cnt*2
				});

				this.listObj.children('li').css({
					'float'				: 'left',
					'margin-right'		: this.options['image_side_margin']+'px'
				});

				if(this.prevBtnObj){
					this.prevBtnObj.css({
						'left' : '0px',
						'top' : main_y+(main_height/2)-(this.prevBtnObj.height()/2)
					});
				}
				if(this.nextBtnObj){
					this.nextBtnObj.css({
						'right' : '0px',
						'top' : main_y+(main_height/2)-(this.prevBtnObj.height()/2)
					});
				}

			},

			// PC STYLE 3
			set_pc_style_3: function(){
				var that = this;
				var pagingObjHeight = this.pagingObj ? this.pagingObj.height() : 0;

				this.move_width = parseInt(this.options['image_width'])+parseInt(this.options['image_side_margin']);

				var main_width = parseInt(this.options['image_width']) + parseInt(this.options['image_side_margin'])*2;
				var main_height = parseInt(this.options['image_height']) + parseInt(this.options['image_top_margin'])*2;
				var main_x = (main_width/2*-1);
				var main_y = parseInt(this.options['image_top_margin']);

				var element_height = main_height;
				if(this.options['navigation_paging_position']=='bottom'){
					element_height += (pagingObjHeight + parseInt(this.options['navigation_paging_margin']));
				}

				$(this.element).css({
					'width'			: main_width+'px',
					'height'		: element_height+'px',
					'background'	: 'none'
				});

				this.wrapObj.css({
					'position'		: 'absolute',
					'top'			: '0px',
					'left'			: '0px',
					'width'			: main_width+'px',
					'height'		: main_height+'px',
					'white-space'	: 'nowrap',
					'overflow'		: 'hidden',
					'background-color'		: this.options['background_color']?this.options['background_color']:'#ffffff'
				});

				if(this.options['background_repeat']=='none'){
					this.wrapObj.css({
						'background-image'		: 'none'
					});
				}else{
					this.wrapObj.css({
						'background-image'		: this.options['background_image']?"url('"+this.options['background_image']+"?"+this.options['modtime']+"')":'none',
						'background-repeat'		: this.options['background_repeat']?this.options['background_repeat']:'repeat',
						'background-position'	: this.options['background_position']?this.options['background_position']:'left top'
					});
				}

				this.listObj.children('li').clone(true).appendTo(this.listObj);

				this.listObj.css({
					'position'		: 'absolute',
					'top'			: main_y+'px',
					'left'			: '50%',
					'margin-left'	: (parseInt(this.options['image_side_margin'])+main_x-(this.now_idx*(parseInt(this.options['image_width'])+parseInt(this.options['image_side_margin']))))+'px',
					'white-space'	: 'nowrap',
					'width'			: (parseInt(this.options['image_width'])+parseInt(this.options['image_side_margin']))*this.item_cnt*2
				});

				this.listObj.children('li').css({
					'float'				: 'left',
					'margin-right'		: this.options['image_side_margin']+'px'
				});

				if(this.prevBtnObj){
					this.prevBtnObj.css({
						'left' : '0px',
						'top' : main_y+(main_height/2)-(this.prevBtnObj.height()/2)
					});
				}
				if(this.nextBtnObj){
					this.nextBtnObj.css({
						'right' : '0px',
						'top' : main_y+(main_height/2)-(this.prevBtnObj.height()/2)
					});
				}
			},

			// PC STYLE 4
			set_pc_style_4: function(){
				var that = this;

				this.move_width = parseInt(this.options['image_width'])+parseInt(this.options['image_side_margin']);

				var main_width = parseInt(this.options['image_width']) + parseInt(this.options['image_side_margin'])*2;
				var main_height = parseInt(this.options['image_height']) + parseInt(this.options['image_top_margin'])*2;
				var main_x = (main_width/2*-1);
				var main_y = parseInt(this.options['image_top_margin']);

				$(this.element).css({
					'width'			: main_width+'px',
					'height'		: main_height+'px'
				});

				this.wrapObj.css({
					'position'		: 'absolute',
					'top'			: '0px',
					'left'			: '0px',
					'width'			: main_width+'px',
					'height'		: main_height+'px',
					'white-space'	: 'nowrap',
					'overflow'		: 'hidden'
				});

				this.listObj.children('li').clone(true).appendTo(this.listObj);

				this.listObj.css({
					'position'		: 'absolute',
					'top'			: main_y+'px',
					'left'			: '50%',
					'margin-left'	: (parseInt(this.options['image_side_margin'])+main_x-(this.now_idx*(parseInt(this.options['image_width'])+parseInt(this.options['image_side_margin']))))+'px',
					'white-space'	: 'nowrap',
					'width'			: (parseInt(this.options['image_width'])+parseInt(this.options['image_side_margin']))*this.item_cnt*2
				});

				this.listObj.children('li').css({
					'float'				: 'left',
					'margin-right'		: this.options['image_side_margin']+'px'
				});

				if(this.prevBtnObj){
					this.prevBtnObj.css({
						'left' : '0px',
						'top' : main_y+(main_height/2)-(this.prevBtnObj.height()/2)
					});
				}
				if(this.nextBtnObj){
					this.nextBtnObj.css({
						'right' : '0px',
						'top' : main_y+(main_height/2)-(this.prevBtnObj.height()/2)
					});
				}
			},

			// PC STYLE 5
			set_pc_style_5: function(){
				var that = this;

				this.move_width = parseInt(this.options['image_width'])+parseInt(this.options['image_side_margin']);

				var main_width = parseInt(this.options['image_width']) + parseInt(this.options['image_side_margin'])*2;
				var main_height = parseInt(this.options['image_height']) + parseInt(this.options['image_top_margin'])*2;
				var main_x = (main_width/2*-1);
				var main_y = parseInt(this.options['image_top_margin']);

				$(this.element).css({
					'width'			: main_width+'px',
					'height'		: main_height+'px'
				});

				this.wrapObj.css({
					'position'		: 'absolute',
					'top'			: '0px',
					'left'			: '0px',
					'width'			: main_width+'px',
					'height'		: main_height+'px',
					'white-space'	: 'nowrap',
					'overflow'		: 'hidden'
				});

				this.listObj.children('li').clone(true).appendTo(this.listObj);

				this.listObj.css({
					'position'		: 'absolute',
					'top'			: main_y+'px',
					'left'			: '50%',
					'margin-left'	: (parseInt(this.options['image_side_margin'])+main_x-(this.now_idx*(parseInt(this.options['image_width'])+parseInt(this.options['image_side_margin']))))+'px',
					'white-space'	: 'nowrap',
					'width'			: (parseInt(this.options['image_width'])+parseInt(this.options['image_side_margin']))*this.item_cnt*2
				});

				this.listObj.children('li').css({
					'float'				: 'left',
					'margin-right'		: this.options['image_side_margin']+'px'
				});

				if(this.prevBtnObj){
					this.prevBtnObj.css({
						'left' : '0px',
						'top' : main_y+(main_height/2)-(this.prevBtnObj.height()/2)
					});
				}
				if(this.nextBtnObj){
					this.nextBtnObj.css({
						'right' : '0px',
						'top' : main_y+(main_height/2)-(this.prevBtnObj.height()/2)
					});
				}
			},

			// MOBILE STYLE 1
			set_mobile_style_1: function(){
				var that = this;

				this.move_width = parseInt(this.options['image_width'])+parseInt(this.options['image_side_margin']);

				var main_width = parseInt(this.options['image_width'])*this.perpage + parseInt(this.options['image_side_margin'])*(this.perpage-1);
				var main_height = parseInt(this.options['image_height']);
				var main_x = (main_width/2*-1);
				var main_y = parseInt(this.options['image_top_margin']);

				this.listObj.children('li').clone(true).appendTo(this.listObj);

				this.listObj.css({
					'position'		: 'absolute',
					'top'			: main_y+'px',
					'left'			: '50%',
					'margin-left'	: (main_x-(this.now_idx*(parseInt(this.options['image_width'])+parseInt(this.options['image_side_margin']))))+'px',
					'white-space'	: 'nowrap',
					'width'			: (parseInt(this.options['image_width'])+parseInt(this.options['image_side_margin']))*this.item_cnt*2
				});

				this.listObj.children('li').css({
					'float'				: 'left',
					'margin-right'		: this.options['image_side_margin']+'px'
				});

				if(this.prevBtnObj){
					this.prevBtnObj.css({
						'left' : '50%',
						'margin-left' : main_x-(this.prevBtnObj.width()/2)-(this.options['image_side_margin']/2),
						'top' : main_y+(main_height/2)-(this.prevBtnObj.height()/2)
					});
				}
				if(this.nextBtnObj){
					this.nextBtnObj.css({
						'left' : '50%',
						'margin-left' : main_x+main_width-(this.prevBtnObj.width()/2)+(this.options['image_side_margin']/2),
						'top' : main_y+(main_height/2)-(this.prevBtnObj.height()/2)
					});
				}

				this._move_page_complete();
			},

			// MOBILE STYLE 2
			set_mobile_style_2: function(){
				var that = this;

				this.listObj.children('li').clone(true).appendTo(this.listObj);

				var min_main_height = 0;
				var max_main_height	= 0;
				var max_main_width	= 0;
				var min_main_width	= 0;
				var reset			= false;

				var set_mobile_style_2_reset = function(){

					that.move_width = $(that.element).width();

					var main_width = parseInt($(that.element).width());
					var main_height = parseInt(that.listObj.find('img.anibanner_image_element').first().height());
					var main_x = (main_width/2*-1);

					//console.log("main_height  : " + main_height);
					// 모바일 기기 가로 회전 후 세로로 재회전시 이미지 div height 사이즈 오류로 최초 min_height 활용
					if(min_main_height == 0) min_main_height = main_height;
					if(max_main_height == 0) max_main_height = main_height;
					if(max_main_width == 0) max_main_width = main_width;
					if(min_main_width == 0) min_main_width = main_width;

					if(main_height < min_main_height ){ min_main_height = main_height; }
					if(main_height > min_main_height ){ max_main_height = main_height; }
					if(main_width > max_main_width){ max_main_width = main_width; }
					if(main_width < min_main_width){ min_main_width = main_width; }

					// 가로모드 -> 세로모드 변경 시
					if(max_main_width >= main_width && main_height <= max_main_height ){
						main_height = min_main_height;
					}
					// 세로모드 -> 가로모드 변경 시
					if(min_main_width < main_width && main_height >= min_main_height ){
						main_height = max_main_height;
					}

					// height 변경 시 초기 비율에 따른 변화 체크
					var org_width	= main_width;
					var org_height	= main_height;
					if	(org_width > 0 && org_height > 0){
						var chk_per	= ( org_height / org_width );
						var chk_height	= main_width * chk_per;
						if	(chk_height != main_height)	main_height	= chk_height;
					}

					$(that.element).css({'height': main_height+'px'});

					that.listObj.css({
						'position'		: 'absolute',
						'top'			: '0px',
						'left'			: '50%',
						'margin-left'	: (main_x-(that.now_idx*main_width))+'px',
						'white-space'	: 'nowrap',
						'width'			: (main_width*that.item_cnt*2)+'px'
					});

					that.listObj.children('li').css({
						'width'			: main_width+'px',
						'float'			: 'left'
					});

					if(that.prevBtnObj){
						that.prevBtnObj.css({
							'left' : '0px',
							'top' : (main_height/2)-(that.prevBtnObj.height()/2)
						});
					}
					if(that.nextBtnObj){
						that.nextBtnObj.css({
							'right' : '0px',
							'top' : (main_height/2)-(that.prevBtnObj.height()/2)
						});
					}

					var margin_top = main_height;
					if(that.options['navigation_paging_position']=='over' && that.pagingObj) {
						margin_top = margin_top - parseInt(that.options['navigation_paging_margin']) - that.pagingObj.height();
					}else{
						margin_top = margin_top + parseInt(that.options['navigation_paging_margin']);
					}
					var margin_side = that.options['navigation_paging_position']=='over' ? that.options['navigation_paging_margin'] : 0;
					if(that.options['navigation_paging_position']=='bottom') {
						margin_top = parseInt(margin_top) + parseInt(that.options['navigation_paging_margin'])+10;
						$(that.element).css({'height': margin_top+'px'});
						if(that.pagingObj) that.pagingObj.css({'margin-top':main_height+'px','margin-right':margin_side+'px','margin-left':margin_side+'px'}).show();
					}else{
					if(that.pagingObj) that.pagingObj.css({'margin-top':margin_top+'px','margin-right':margin_side+'px','margin-left':margin_side+'px'}).show();
					}
					
					if(main_width != max_main_width && reset == false){
						reset = true;
						set_mobile_style_2_reset();
					}

				};

				set_mobile_style_2_reset();

				this.listObj.find('img.anibanner_image_element').load(function(){
					set_mobile_style_2_reset();
				});

				$(window).bind('resize orientationchange',function(){
					set_mobile_style_2_reset();
				});

			},

			// MOBILE STYLE 3
			set_mobile_style_3: function(){
				var that = this;
				var pagingObjHeight = this.pagingObj ? this.pagingObj.height() : 0;

				this.listObj.children('li').clone(true).appendTo(this.listObj);

				var set_mobile_style_3_reset = function(){
					var image_width = parseInt($(that.element).width()) - parseInt(that.options['image_side_margin'])*2;
					var image_height = parseInt(that.listObj.find('img.anibanner_image_element').first().height());
					var main_x = (image_width/2*-1);
					var main_y = parseInt(that.options['image_top_margin']);

					that.move_width = image_width+parseInt(that.options['image_side_margin']);

					var element_width = parseInt($(that.element).width()) + parseInt(that.options['image_side_margin'])*2;
					var element_height = image_height + parseInt(that.options['image_top_margin'])*2;
					if(that.options['navigation_paging_position']=='bottom'){
						element_height += (pagingObjHeight + parseInt(that.options['navigation_paging_margin']));
					}

					$(that.element).css({
						'height': element_height+'px',
						'background'	: 'none'
					});

					var wrap_height = (image_height + parseInt(that.options['image_top_margin'])*2);

					that.wrapObj.css({
						'position'		: 'absolute',
						'top'			: '0px',
						'left'			: '0px',
						'width'			: element_width+'px',
						'height'		: wrap_height+'px',
						'white-space'	: 'nowrap',
						'overflow'		: 'hidden',
						'background-color'		: that.options['background_color']?that.options['background_color']:'#ffffff'
					});

					if(that.options['background_repeat']=='none'){
						that.wrapObj.css({
							'background-image'		: 'none'
						});
					}else{
						that.wrapObj.css({
							'background-image'		: that.options['background_image']?"url('"+that.options['background_image']+"?"+that.options['modtime']+"')":'none',
							'background-repeat'		: that.options['background_repeat']?that.options['background_repeat']:'repeat',
							'background-position'	: that.options['background_position']?that.options['background_position']:'left top'
						});
					}

					that.listObj.css({
						'position'		: 'absolute',
						'top'			: main_y+'px',
						'left'			: '50%',
						'margin-left'	: (main_x-parseInt(that.options['image_side_margin'])-(that.now_idx*(image_width+parseInt(that.options['image_side_margin']))))+'px',
						'white-space'	: 'nowrap',
						'width'			: (image_width*that.item_cnt*2)+'px'
					});

					that.listObj.children('li').css({
						'width'			: image_width+'px',
						'margin-right'	: parseInt(that.options['image_side_margin']),
						'float'			: 'left'
					});

					if(that.prevBtnObj){
						that.prevBtnObj.css({
							'left' : '0px',
							'top' : (image_height/2)-(that.prevBtnObj.height()/2)
						});
					}
					if(that.nextBtnObj){
						that.nextBtnObj.css({
							'right' : '0px',
							'top' : (image_height/2)-(that.prevBtnObj.height()/2)
						});
					}

					if(that.options['navigation_paging_position']=='over' && that.pagingObj){
						margin_top = wrap_height - parseInt(that.options['navigation_paging_margin']) - that.pagingObj.height();
					}else{
						margin_top = wrap_height + parseInt(that.options['navigation_paging_margin']);
					}
					var margin_side = that.options['navigation_paging_position']=='over' ? that.options['navigation_paging_margin'] : 0;
					if(that.pagingObj) that.pagingObj.css({'margin-top':margin_top+'px','margin-right':margin_side+'px','margin-left':margin_side+'px'}).show();
				};

				set_mobile_style_3_reset();

				this.listObj.find('img.anibanner_image_element').load(function(){
					set_mobile_style_3_reset();
				});

				$(window).bind('resize',function(){
					set_mobile_style_3_reset();
				});

				// 이벤트 발생 후 이미지가 리사이즈 되기까지 약간 텀을 두고 함수 실행 2016-07-08
				$(window).bind('orientationchange',function(){
					setTimeout(set_mobile_style_3_reset,200);
				});
			},

			_get_timestamp: function(){
				return parseInt(new Date().getTime().toString().substring(0, 10));
			}
		});
	});
}