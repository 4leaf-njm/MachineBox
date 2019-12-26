var jsGridCommonOptions	= {width: "100%", height: "100%", sorting: false, paging: false};
var addObj				= {};
jsGrid.locale("kr");

//jsGrid 기능 추가 - sjp
jsGrid.Grid.prototype.setData		= function(data) {
	for (cnt = data.length, i = 0; i < cnt ; i++ )
		data[i]['_jsGridIdx']	= i;
	
	this.data	= data;
	this._renderGrid();
}


jsGrid.Grid.prototype.selectDoRow	= function(target) {
	gridBody	= this._body;
	className	= 'jsgrid-do-row';

	$(target).removeClass('jsgrid-checked-row');
	$('.'+this.selectedRowClass, gridBody).removeClass(className);
	$(target).addClass(className);
	

	if(this.autoScrolling == true) {
		nowScroll	= gridBody.scrollTop();

		rowHeight	= $(target).height();
		nowPosition	= $(target).position();
		
		newHeight		= 0;

		if (nowPosition.top < 1)
			newHeight	=  nowScroll + nowPosition.top - rowHeight;
		else if (nowScroll < 1)
			newHeight	= nowPosition.top - nowScroll - rowHeight;
		else if(nowScroll < nowPosition.top)
			newHeight	= nowScroll + nowPosition.top  - rowHeight;
		else
			newHeight	= nowScroll + rowHeight;

		gridBody.scrollTop(newHeight);
	}
}




jsGrid.Grid.prototype.allReset		= function() {
	gridBody	= this._body;
	nowScroll	= gridBody.scrollTop(0);

	$('.jsgrid-single-select-row', gridBody).removeClass('jsgrid-single-select-row');
	$('.jsgrid-checked-row', gridBody).removeClass('jsgrid-checked-row');
	$('.jsgrid-done-row', gridBody).removeClass('jsgrid-done-row');
	$('.jsgrid-do-row', gridBody).removeClass('jsgrid-do-row');
}

jsGrid.Grid.prototype.doneSelectRow		= function(target) {
	$(target).removeClass('jsgrid-checked-row');
	$(target).removeClass('jsgrid-do-row');
	$(target).addClass('jsgrid-done-row');
}


jsGrid.Grid.prototype.oneSelectRow		= function(target) {
	gridBody	= this._body;
	$('.jsgrid-single-select-row', gridBody).removeClass('jsgrid-single-select-row');
	$(target).addClass('jsgrid-single-select-row');
}


jsGrid.Grid.prototype.checkedRow		= function(mode,item) {
	nowRow		= this.rowByItem(item);
	if(mode == 'checked')
		$(nowRow).addClass('jsgrid-checked-row');
	else
		$(nowRow).removeClass('jsgrid-checked-row');
}

jsGrid.Grid.prototype.addClass			= function(item, className) {
	nowRow		= this.rowByItem(item);
	$(nowRow).addClass(className);
}

jsGrid.Grid.prototype.removeClass		= function(item, className) {
	nowRow		= this.rowByItem(item);
	$(nowRow).removeClass(className);
}

jsGrid.Grid.prototype.resetRowSelect	= function(target) {
	$('.'+this.selectedRowClass, this._body).removeClass(this.selectedRowClass);
	$(target).removeClass('jsgrid-done-row');
}
jsGrid.Grid.prototype.moveToBottom		= function() {this._body.scrollTop(this._body.prop("scrollHeight"));}
jsGrid.Grid.prototype.getData			= function() {return this.data;}					// 전체 데이터 가져오기
jsGrid.Grid.prototype.getDataKeys		= function() {return Object.keys(this.data);}		// 전체 Key 리스트 가져오기
jsGrid.Grid.prototype.getDataByIdx		= function(gridIdx)	{return	this.data[gridIdx];}	// 특정 Key 데이터 가져오기

// core prototype 수정
jsGrid.Grid.prototype._attachRowHoverBase	= jsGrid.Grid.prototype._attachRowHover;
jsGrid.Grid.prototype._attachRowHover		= function($row) {
	var selectedRowClass = this.selectedRowClass;
	$row.hover(function() {
			$(this).addClass(selectedRowClass);
		}, function() {
			$(this).removeClass(selectedRowClass);
		});
};


jsGrid.Grid.prototype.rowClickBase	= jsGrid.Grid.prototype.rowClick;
jsGrid.Grid.prototype.rowClick		= function(args) {
	if(this.editing)
		this.rowClickBase(args);

	if(typeof this.clickFunction == 'function')
		this.clickFunction(args);

	if(this.clickSelect == true) {
		//console.log(args);	
	}

}


// 그리드 생성 함수
function makeJsGrid(gridId, fields, addOptObj, gridData) {
	var jsGridOpt		= $.extend(jsGridCommonOptions, addOptObj); 
	jsGridOpt.fields	= fields;
	jsGridOpt.Data		= gridData;
	gridId.jsGrid(jsGridOpt);
}