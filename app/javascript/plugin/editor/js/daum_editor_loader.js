if(typeof(file_use) == 'undefined') { //Virtual Function
var file_use = '';
}else{
var file_use = (file_use)?file_use:'';
}
if(typeof(board_id) == 'undefined') { //Virtual Function
	var board_id = '';
}else{
	var board_id = (board_id)?board_id:'';
}

if(typeof(video_use) == 'undefined') { //Virtual Function
	var videouse = '';
}else{
	var videouse = (video_use=='Y')?'Y':'';
}

var DaumEditorLoader = {
	'editor_loading' : false,
	'editor_count' : 0,
	'editor_ready' : new Array(),
	
	'init'	: function(){
		
		$(".tx-toolbar-basic .tx-btn-trans").hide();
				
		$(".daumeditor").each(function(){
			if($(this).data("daumeditorLoadingStart")==true) {	
				return;
			}
			$(this).data("daumeditorLoadingStart",true);
			DaumEditorLoader.editor_ready.push(this);
		});
				
		DaumEditorLoader.set_editor_ready();

	},
	'set_editor_ready' : function(){
		if(DaumEditorLoader.editor_ready.length){
			DaumEditorLoader.set_editor(DaumEditorLoader.editor_ready[0]);
			DaumEditorLoader.editor_ready.shift();
		}else{
			$(".tx-toolbar-basic .tx-btn-trans").show();
		}
	},
	'set_editor' : function(textareaObj){
		DaumEditorLoader.editor_loading = true;
		DaumEditorLoader.editor_count++;
		
		var initializedId = DaumEditorLoader.editor_count;
		$(textareaObj).data('initializedId',initializedId);
		
		var tinyMode = $(textareaObj).attr('tinyMode') ? 1 : 0;
		var fullMode = $(textareaObj).attr('fullMode') ? 1 : 0;
		
		if(!textareaObj.form){
			alert("에디터 로딩 실패 : Textarea를 Form태그로 감싸야합니다.");
			return;
		}

		if(textareaObj.form.id){
			var form_id = textareaObj.form.id;	
		}else if(textareaObj.form.name){
			var form_id = textareaObj.form.name;	
		}else{
			textareaObj.form.id = 'form_' + initializedId;
			var form_id = textareaObj.form.id;
		}
 
		if(typeof(board_id) == 'undefined' || !board_id) { //Virtual Function
			file_use = 'Y';
		} 

		var fsubdomain = '';
		var kordomain_img = '#host/common/editor_image';
		var kordomain_file = '#host#path/pages/trex/file.html';
		var fdomain = document.domain;
		//한글도메인체크@2012-10-31
		for(i=0; i<fdomain.length; i++){
			 if (((fdomain.charCodeAt(i) > 0x3130 && fdomain.charCodeAt(i) < 0x318F) || (fdomain.charCodeAt(i) >= 0xAC00 && fdomain.charCodeAt(i) <= 0xD7A3))) 
			{
				 //기본도메인추출
				$.ajax({
					url: '/common/domainjson',global:false,async: false,dataType: 'json',
					success: function(data) {
						fsubdomain = data.subdomain;
				}
				}); 
				kordomain_file += '?fsubdomain='+fsubdomain;//한글도메인관련 최종점검@2015-07-24
				break;
			}
		}
		
		//editor fontlist 
		var fontList = [
			{label:TXMSG("@fontfamily.gulim")+' (<span class="tx-txt">\uac00\ub098\ub2e4\ub77c</span>)',title:TXMSG("@fontfamily.gulim"),data:"Gulim,\uad74\ub9bc,AppleGothic,sans-serif",klass:"tx-gulim"},
			{label:TXMSG("@fontfamily.batang")+' (<span class="tx-txt">\uac00\ub098\ub2e4\ub77c</span>)',title:TXMSG("@fontfamily.batang"),data:"Batang,\ubc14\ud0d5",klass:"tx-batang"},
			{label:TXMSG("@fontfamily.dotum")+' (<span class="tx-txt">\uac00\ub098\ub2e4\ub77c</span>)',title:TXMSG("@fontfamily.dotum"),data:"Dotum,\ub3cb\uc6c0",klass:"tx-dotum"},
			{label:TXMSG("@fontfamily.gungsuh")+' (<span class="tx-txt">\uac00\ub098\ub2e4\ub77c</span>)',title:TXMSG("@fontfamily.gungsuh"),data:"Gungsuh,\uad81\uc11c",klass:"tx-gungseo"},
			{label:'Arial (<span class="tx-txt">abcde</span>)',title:"Arial",data:"Arial",klass:"tx-arial"},
			{label:'Verdana (<span class="tx-txt">abcde</span>)',title:"Verdana",data:"Verdana",klass:"tx-verdana"}
		];
		//font add
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
		for(var ii=0;ii<jsonFontArray.length;ii++){
			fontList.push(jsonFontArray[ii]);
		}
		$.ajax({
			'url' : "/app/javascript/plugin/editor/_editor.html?initializedId="+initializedId+'&board_id='+board_id+'&file_use='+file_use+'&videouse='+videouse+'&tinyMode='+tinyMode+'&fullMode='+fullMode,
			'global' : false,
			'success' : function(result){
				$(textareaObj).after($("<div></div>").append(result)).hide();
				
				var config = {
					'txHost': '', /* 런타임 시 리소스들을 로딩할 때 필요한 부분으로, 경로가 변경되면 이 부분 수정이 필요. ex) http://xxx.xxx.com */
					'txPath': '/app/javascript/plugin/editor/', /* 런타임 시 리소스들을 로딩할 때 필요한 부분으로, 경로가 변경되면 이 부분 수정이 필요. ex) /xxx/xxx/ */
					'txIconPath': '/app/javascript/plugin/editor/images/icon/editor/',/*에디터에 사용되는 이미지 디렉터리, 필요에 따라 수정한다. */
					'txDecoPath': '/app/javascript/plugin/editor/images/deco/contents/', /*본문에 사용되는 이미지 디렉터리, 서비스에서 사용할 때는 완성된 컨텐츠로 배포되기 위해 절대경로로 수정한다. */
					'txService': 'sample', /* 수정필요없음. */
					'txProject': 'sample', /* 수정필요없음. 프로젝트가 여러개일 경우만 수정한다. */
					'initializedId': initializedId, /* 대부분의 경우에 빈문자열 */
					'wrapper': "tx_trex_container"+initializedId, /* 에디터를 둘러싸고 있는 레이어 이름(에디터 컨테이너) */
					'form': form_id, /* 등록하기 위한 Form 이름 */
					'sidebar': {
						'attachbox': {
							'show': true
						},
						'attacher': {
							'image': {
								'checksize': true,
								'popPageUrl'	: kordomain_img,
								'features'		:{left:250,top:65,width:500,height:240,resizable:1}
							},
							'file': {
								'checksize': true,
								'popPageUrl'	: kordomain_file,
								'features'		:{left:250,top:65,width:500,height:240,resizable:1}
							},
							'media': {
								'popPageUrl'	: '#host#path/pages/trex/multimedia.html',
								'features'		:{left:250,top:65,width:500,height:240}
							}
						}
					},
					'toolbar': {
						fontfamily: {options : fontList},
					},
					'events': {
						'preventUnload': false
					}
				};

		        var editor = new Editor(config);
				if(!tinyMode) Editor.getTool("advanced").forceOpen(); 
				
				if($(textareaObj).attr('contentHeight')) {
					var contentHeight = parseInt($(textareaObj).attr('contentHeight'));
				} 

				if(contentHeight > 0 ) Editor.canvas.setCanvasSize({height:contentHeight});
		        
		        Editor.onPanelLoadComplete(function () {
		        	Editor.switchEditor(initializedId);
					/**
					* 게시글 수정페이지의 이미지삭제시 본문에서 제거 또는 등록 못하는 문제로 소스개선
					* 기존함수 readfilelist(Editor) -> 개선함수 readfilelistNew(attachments)
					* @2016-10-06 ysm
					**/
					try{
					  var attachments = {};
					  attachments = readfilelistNew(attachments);
					  Editor.modify({
						"attachments": function () {
							var allattachments = [];
							for (var i in attachments) {
								allattachments = allattachments.concat(attachments[i]);
							}
							return allattachments;
						}(),
						"content" : textareaObj
					  });
					}catch(e){ 
		        	  Editor.modify({"content" : textareaObj});
					}
					DaumEditorLoader.set_editor_ready();
					DaumEditorLoader.editor_loading = false;
					$(textareaObj).data("daumeditorLoadingEnd",true);
					if($(textareaObj).attr('infomation') != undefined) $('.'+$(textareaObj).attr('infomation')).css({'height':$('.tx-editor-container').css('height'),'width':$('.tx-editor-container').css('width'),'background-color':'#eaeaea'});
					
					
					/**
					* 기존함수로 패치전 게시판 스킨에서 사용하기 때문에 그대로 적용합니다.
					* @2016-10-06 ysm
					**/
					try{ 
						readfilelist(Editor);
					}catch(e){ 
					}
		        });
				
			}
		});
	}
};

/* 에디터를 포함하고있는 폼 Submit 처리 */
function submitEditorForm(frm){
	if(readyEditorForm(frm)){
		// title값이 있는 input박스 비우기
		$("input[title]",frm).each(function(){
			if($(this).val()==$(this).attr('title')) $(this).val('');		
		});
	
		$(function(){
			frm.submit();
		});
	}
}

/* 에디터의 content를 textarea로 카피 */
function readyEditorForm(frm){
	
	var flag = true;
	
	$(frm).find(".daumeditor").each(function(){
		
		if(!$(this).data('daumeditorLoadingEnd')){
			alert("아직 에디터가 로드되지 않았습니다.\n잠시만 기다려주세요.");
			flag = false;
			return false;
		}
		
		var initializedId = $(this).data('initializedId');
		Editor.switchEditor(initializedId);

		
		// OCW : 2012-07-19
		var content = Editor.getContent();
		var dns;
		dns=location.href;
		dns=dns.split("//");
		dns="http://"+dns[1].substr(0,dns[1].indexOf("/"))+"/";

		content = content.replace(new RegExp(dns,'gi'),"/"); 
		Editor.modify({"content" : content});


		
		var formGenerator = Editor.getForm();
		var images = Editor.getAttachments('image', true); 
		for (var i = 0, len = images.length; i < len; i++) { 
			//if (images[i].existStage) {/* existStage는 현재 본문에 존재하는지 여부로 제외 @2012-08-24 */
	           //alert('attachment information - image[' + i + '] \r\n' + JSON.stringify(images[i].data));
	            formGenerator.createField(
	                    tx.input({
	                        'type': "hidden",
	                        'name': 'tx_attach_files[]',
	                        'value': images[i].data.imageurl // 예에서는 이미지경로만 받아서 사용
	                    })
	            ); 
				formGenerator.createField(
				tx.input({
					'type': "hidden",
					'name': 'tx_attach_files_name[]',
					'value': images[i].data.filename
				})
				 ); 
	        //}
		}//endfor
		
		var files = Editor.getAttachments('file' , true); 
        for (var i = 0, len = files.length; i < len; i++) {
          // alert('attachment information - file[' + i + '] \r\n' + JSON.stringify(files[i].data));
            formGenerator.createField(
                    tx.input({
                        'type': "hidden",
                        'name': 'tx_attach_files[]',
                        'value': files[i].data.attachurl
                    })
            );
				formGenerator.createField(
				tx.input({
					'type': "hidden",
					'name': 'tx_attach_files_name[]',
					'value': files[i].data.filename
				})
				 ); 
        } 
			
		//var content = Editor.getContent();
		
		content = content=='<p>&nbsp;<\/p>' ? '' : content; // 내용이 비었을때에도 <p>&nbsp;</p>가 들어가는 문제 수정
		
		$(this).val(content);
		
	});
	
	return flag;
}

$(function(){
	EditorJSLoader.ready(function(Editor) {
		DaumEditorLoader.init(".daumeditor");
	});
});