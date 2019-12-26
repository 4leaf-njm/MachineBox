try{ 
	if(!eval('facebooklikeboxok')){
		var facebooklikeboxok =true;
		//♥좋아요! <span class='desc'>♥좋아요하고 할인받자!</span>
		var title = getAlert('mb235');

		try{ 
		if(eval("gl_mobile_mode")) 
		{ 
			var gl_mobile_mode = gl_mobile_mode;
		} 
		}catch(e){ 
			var gl_mobile_mode = '';
		}


		$(function(){
			$(".fb-og-like-login").live("click",function(e){
				e.preventDefault();
				var fbhost = $(this).attr("data-host");
				var fbhref = $(this).attr("data-href");
				var returnurl = $(this).attr("data-returnurl");
				var ssid = $(this).attr("data-ssid");
				var fblikeseq = $(this).attr("data-fblikeseq");
				var fblikeid = $(this).attr("data-fblikeid"); 
				var goodsseq = $(this).attr("goodsseq");
				var mode = 'like';
				if( document.domain != returnurl ) {
					returnurl = document.domain;
				}
				var url = (document.location.protocol == "https:")?'https://'+fbhost:'http://'+fbhost;//facebooklikeopen=Y&
				url += '/member/register_sns_form?facebooklikeopen=Y&popup=1&formtype=fblike&type=f&no='+goodsseq+'&returnurl='+returnurl+'&ssid='+ssid+'&fblikeseq='+fblikeseq+'&fblikeid='+fblikeid; 
				if( $(this).attr("data-fblikeseq") > 0 ) {
					mode = 'unlike';
				}
				url += '&mode='+mode;
				
				var scrollbars = "0";
				if( gl_mobile_mode ){
					var width = "350";
				}else{
					var width = "450";
				}
				var height = "300";
				var winL = (screen.width-width)/2;
				var winT = (screen.height-height)/2;
				window.open(url,'','width='+width+',height='+height+',scrollbars=no,status=no,toolbar=no,resizable=no,menubar=no,location=no,top='+winT+',left='+winL);
				//popup( url,350,300);
				//facebookopendialog(url);
				
			});
			
			$(".fb-og-like").live("click",function(e){
				e.preventDefault();
				var fbhost = $(this).attr("data-host");
				var fbhref = $(this).attr("data-href");
				var returnurl = $(this).attr("data-returnurl");
				var ssid = $(this).attr("data-ssid");
				var fblikeseq = $(this).attr("data-fblikeseq");
				var fblikeid = $(this).attr("data-fblikeid"); 
				var goodsseq = $(this).attr("goodsseq");
				var mode = 'like';
				if( document.domain != returnurl ) {
					returnurl = document.domain;
				}
				
				var url = (document.location.protocol == "https:")?'https://'+fbhost:'http://'+fbhost;
				url += '/snsredirect/facebook_redirect?no='+goodsseq+'&returnurl='+returnurl+'&ssid='+ssid+'&fblikeseq='+fblikeseq+'&fblikeid='+fblikeid;
				
				if( gl_mobile_mode ){
					var width = "350";
				}else{
					var width = "400";
				}
				var height = "160";
				if( $(this).attr("data-fblikeseq") > 0 ) {
					mode = 'unlike';
					url += '&mode='+mode;
					//♥좋아요를 취소하시겠습니까?
					openDialogConfirmtitle(title,getAlert('mb240'),width,height,function(){facebookopendialog(url);},function(){return false});
				}else{
					url += '&mode='+mode;
					facebookopendialog( url);
				}
			});

		});

		if(!$.isFunction("facebookopendialog")){
			function facebookopendialog(url) {
				
				if( gl_mobile_mode ){
					var width = "350";
				}else{
					var width = "400";
				}
				var height = "250";

				var iframe = $('<iframe frameborder="0" width="0" height="0"  marginwidth="0"marginheight="0" scrolling="no" ></iframe>');
				var dialog = $("<div id='facebookoglikeId' class='facebookoglikeId' style='text-align:center'><br/><!img src='/admin/skin/default/images/design/img_progress_bar.gif'' align='absmiddle'></div>").append(iframe).appendTo("body").dialog({//
					autoOpen: false,
					modal: true,
					resizable: false,
					width: width,
					height: height,
					close: function () {
						iframe.attr("src", "");
					}
				});
				iframe.attr({
					src: url
				});
				dialog.dialog("option", "title", title).dialog("open"); 
				loadingStart($(".facebookoglikeId"),{segments: 12, width: 15.5, space: 6, length: 13, color: '#000000', speed: 1.5});
			}
		}

		if(!$.isFunction("facebooklay")){
			function facebooklay(mode){
				
				if( gl_mobile_mode ){
					var width = "350";
				}else{
					var width = "400";
				}
				var height = "160";
				loadingStop($(".facebookoglikeId").find("body"),true);
				$('.facebookoglikeId').dialog("close");
				if( mode == 'unlike' ){
					//페이스북 좋아요.<br/> 취소
					openDialogAlerttitle(title,getAlert('mb241'),width,height);
				}else if( mode == 'like' ){
					//페이스북 좋아요.<br/> 성공
					openDialogAlerttitle(title,getAlert('mb242'),width,height);
				}else{
					//페이스북 좋아요.<br/> 다시 시도해 주세요.
					openDialogAlerttitle(title,getAlert('mb243'),width,height);
				}
			}
		}
	}
} catch (facebooklikeboxok) {
}
