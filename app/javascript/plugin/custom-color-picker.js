/**
 * Cumtom Select Box Plugin (jQuery 1.6.2, jQuery UI 1.8.16 기반)
 * Author : ocw
 * Date : 2012.01.13
**/
$(function() {
	$.widget( "custom.customColorPicker", {
		// default options
		options: {
			hide : true,
			done : null
		},

		// the constructor
		_create: function() {

			var that = this;
			
			if($(this.element).data("colorPickerLoaded")) return false;
			
			$(this.element).attr('readonly',true).data("colorPickerLoaded",true);

			if(this.options['hide']) this.element.hide();
			this.pickerBtnObj = $('<div class="colorPickerBtn"></div>');
			this.element.after(this.pickerBtnObj);
						
			var html = '';
			html += '<div class="colorPickerLayer">';
			html += '<div class="colorPickerLayerInner">';
			html += '<div class="clearbox">';
			html += '<div class="colorPickerBody"></div>';
			html += '<div class="colorPickerSlide"></div>';
			html += '</div>';
			html += '<div class="mt3 clearbox">';
			html += '<div class="fl"><div class="colorPickerPreview"></div></div>';
			html += '<div class="fr"><input type="text" class="colorPickerHex" size="7" maxlength="7" class="input-text-small" /> <span class="btn small"><input type="button" class="colorPickerDoneBtn" value="확인" /></span> <span class="btn small"><input type="button" class="colorPickerCancelBtn" value="취소" /></span></div>';
			html += '</div>';
			html += '</div>';
			html += '</div>';
			
			this.colorPickerLayerObj = $(html);
			this.pickerBtnObj.after(this.colorPickerLayerObj);
			
			this.colorPickerLayerObj.find(".colorPickerHex").bind('keydown change',function(){
				that.colorPickerLayerObj.find(".colorPickerPreview").css('background-color',that._adjustHex($(this).val()));
			});
			
			this.pickerBtnObj.css('background-color',this.element.val());
			this.pickerBtnObj.bind('click',function(){
				
				$(".colorPickerLayer svg").remove();
				
				var cp = ColorPicker(
						that.colorPickerLayerObj.find(".colorPickerSlide")[0],
						that.colorPickerLayerObj.find(".colorPickerBody")[0],
					function(hex, hsv, rgb) {
						that.colorPickerLayerObj.find(".colorPickerHex").val(hex).focus();
						that.colorPickerLayerObj.find(".colorPickerPreview").css('background-color',hex);
					}
				);
				
				cp.setHex(that.element.val());

				
				that.colorPickerLayerObj.find(".colorPickerPreview").css('background-color',that.element.val());
				that.colorPickerLayerObj.find(".colorPickerHex").val(that.element.val()).focus();
				
				$(".colorPickerLayer").hide();
				that.colorPickerLayerObj.css({
					'position': 'absolute',
					'z-index': '10100', 
					'left': that.pickerBtnObj.position().left,
					'top': that.pickerBtnObj.position().top
				});

				if($(window).width() < that.pickerBtnObj.position().left+$(".colorPickerLayerInner",that.colorPickerLayerObj).width()){
					that.colorPickerLayerObj.css({
						'left' : that.pickerBtnObj.position().left+that.pickerBtnObj.width()-$(".colorPickerLayerInner",that.colorPickerLayerObj).width()
					});
				}		
					
				that.colorPickerLayerObj.show();

				if($(window).height() < that.pickerBtnObj.position().top+$(".colorPickerLayerInner",that.colorPickerLayerObj).height()){
					that.colorPickerLayerObj.css({
						'top' : that.pickerBtnObj.position().top+that.pickerBtnObj.height()-$(".colorPickerLayerInner",that.colorPickerLayerObj).height()
					});
				}
				
				that.colorPickerLayerObj.find(".colorPickerDoneBtn").click(function(){
					that.colorPickerLayerObj.hide();
					var hex = that._adjustHex(that.colorPickerLayerObj.find(".colorPickerHex").val());
					that.element.val(hex).change();
					that.pickerBtnObj.css('background-color',hex);
				});
				
				that.colorPickerLayerObj.find(".colorPickerCancelBtn").click(function(){
					that.colorPickerLayerObj.hide();
				});
			});
			
			$(this.element).bind('keyup change',function(){
				var hex = that._adjustHex($(this).val());
				that.pickerBtnObj.css('background-color',hex);
			});

		},
		
		_adjustHex: function(hex){
			if(hex.substring(0,1)!='#') hex = "#" + hex;
			if(hex=='#') hex = '';
			return hex;
		},

		destroy: function() {
			this.element.show();
			this.pickerBtnObj.remove();
			this.colorPickerLayerObj.remove();
			$(this.element).data("colorPickerLoaded",false);

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
