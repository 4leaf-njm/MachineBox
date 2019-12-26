$(document).ready(function(){

$('.pickadate').pickadate({
    monthsFull: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
    monthsShort: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'],
    weekdaysFull: ['일요일', '월요일', '화요일', '수요일', '목요일', '금요일', '토요일'],
    weekdaysShort: ['일', '월', '화', '수', '목', '금', '토'],

    labelMonthNext: '다음달',
    labelMonthPrev: '이전달',
    labelMonthSelect: '월을 선택하세요',
    labelYearSelect: '연도를 선택하세요',

    format: 'yyyy-mm-dd',
    formatSubmit: 'yyyy-mm-dd',
    hiddenSuffix: undefined,
    hiddenName: undefined,

    today: '',
    clear: '',
    close: '취소',

    min: true
});

$('.pickadate_default').pickadate({
    monthsFull: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
    monthsShort: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'],
    weekdaysFull: ['일요일', '월요일', '화요일', '수요일', '목요일', '금요일', '토요일'],
    weekdaysShort: ['일', '월', '화', '수', '목', '금', '토'],

    labelMonthNext: '다음달',
    labelMonthPrev: '이전달',
    labelMonthSelect: '월을 선택하세요',
    labelYearSelect: '연도를 선택하세요',

    format: 'yyyy-mm-dd',
    formatSubmit: 'yyyy-mm-dd',
    hiddenSuffix: undefined,
    hiddenName: undefined,
    
    selectYears: true,
    selectMonths: true,
    selectYears: 79,

    today: '',
    clear: '',
    close: '취소'
});

$('.pickatime').pickatime({
    clear: '',
    format: 'HH:i',
    formatSubmit: 'HH:i',
    interval: 10
});

var textareaLineHeight=parseInt($(".textarea-wrapper textarea").css("line-height"));

$('.textarea-wrapper').mCustomScrollbar({
    theme: "minimal-dark",
    scrollbarPosition: "inside",
    scrollInertia:0,
    advanced:{autoScrollOnFocus:false},
    mouseWheel:{disableOver:["select","option","keygen","datalist",""]},
    keyboard:{enable:false},
    snapAmount:textareaLineHeight
});

$(".select_date").click(function() {
    switch(this.id) {
		case 'today' :  
            $('#date_s').val(getDate(0));
			$('#date_f').val(getDate(0));
			break;
		case '3day' :   
            $('#date_s').val(getDate(3));
			$('#date_f').val(getDate(0));
			break;
		case '1week' :  
            $('#date_s').val(getDate(7));
			$('#date_f').val(getDate(0));
			break;
        case '15day' :   
            $('#date_s').val(getDate(15));
			$('#date_f').val(getDate(0));
			break;
		case '1month' : 
            $('#date_s').val(getDate(30));
			$('#date_f').val(getDate(0));
			break;
		case '3month' : 
            $('#date_s').val(getDate(90));
			$('#date_f').val(getDate(0));
			break;
		case '1year' : 
            $('#date_s').val(getDate(365));
			$('#date_f').val(getDate(0));
			break;
		case 'all' :
            $('#date_s').val('');
			$('#date_f').val('');
			break;
		default:
			$('#date_s').val('');
			$('#date_f').val('');
		}
	});

});

function comma(num){
    var len, point, str;  
     
    num = num + "";  
    num = num.replace(',', '');
    point = num.length % 3 ;
    len = num.length; 
   
    //<![CDATA[
    str = num.substring(0, point);  
    while (point < len) {  
        if (str != "") str += ",";  
        str += num.substring(point, point + 3);  
        point += 3;  
    }  
    //]]>
    return str;
}

function current_date() {
    var date = new Date(); 
    var year = date.getFullYear(); 
    var month = new String(date.getMonth()+1); 
    var day = new String(date.getDate()); 
    
    if(month.length == 1){ 
      month = "0" + month; 
    } 
    
    if(day.length == 1){ 
      day = "0" + day; 
    } 
    return year + '.' + month + '.' + day;
}

function setCookie(cookieName, value, exdays){
    var exdate = new Date();
    exdate.setDate(exdate.getDate() + exdays);
    var cookieValue = escape(value) + ((exdays==null) ? "" : "; expires=" + exdate.toGMTString());
    document.cookie = cookieName + "=" + cookieValue;
}
 
function deleteCookie(cookieName){
    var expireDate = new Date();
    expireDate.setDate(expireDate.getDate() - 1);
    document.cookie = cookieName + "= " + "; expires=" + expireDate.toGMTString();
}
 
function getCookie(cookieName) {
    cookieName = cookieName + '=';
    var cookieData = document.cookie;
    var start = cookieData.indexOf(cookieName);
    var cookieValue = '';
    if(start != -1){
        start += cookieName.length;
        var end = cookieData.indexOf(';', start);
        if(end == -1)end = cookieData.length;
        cookieValue = cookieData.substring(start, end);
    }
    return unescape(cookieValue);
}

//<![CDATA[
function price_format(number){
    var inputNumber  = number < 0 ? false : number;
    var unitWords    = ['', '만', '억', '조', '경'];
    var splitUnit    = 10000;
    var splitCount   = unitWords.length;
    var resultArray  = [];
    var resultString = '';

    for (var i = 0; i < splitCount; i++){
         var unitResult = (inputNumber % Math.pow(splitUnit, i + 1)) / Math.pow(splitUnit, i);
        unitResult = Math.floor(unitResult);
        if (unitResult > 0){
            resultArray[i] = unitResult;
        }
    }

    for (var i = 0; i < resultArray.length; i++){
        if(!resultArray[i]) continue;
        resultString = String(resultArray[i]) + " " + unitWords[i] + resultString;
    }

    return resultString;
}

function getTimeStamp() {
  var d = new Date();
  var s =
    leadingZeros(d.getFullYear(), 4) + '-' +
    leadingZeros(d.getMonth() + 1, 2) + '-' +
    leadingZeros(d.getDate(), 2) + ' ' +

    leadingZeros(d.getHours(), 2) + ':' +
    leadingZeros(d.getMinutes(), 2) + ':' +
    leadingZeros(d.getSeconds(), 2);

  return s;
}

function leadingZeros(n, digits) {
  var zero = '';
  n = n.toString();

  if (n.length < digits) {
    for (i = 0; i < digits - n.length; i++)
      zero += '0';
  }
  return zero + n;
}
//]]>

function data_update() {
    $.ajax({
        type: 'post',
        url: '/load/data_update',
        dataType: 'json',
        success: function(data) {
            console.log(data);
        },
        error: function() {
            console.log('error');
        }
    });
}
data_update();

//<![CDATA[
function is_date_exceed(date) {
    var today = new Date();
    var day = today.getDate();
    var month = today.getMonth()+1; 
    var year = today.getFullYear();

    if(day<10) {
        day='0'+day
    } 
    
    if(month<10) {
        month='0'+month
    }
    today = year + '-' + month + '-' + day;

    if(today > date) {
        return true;
    } else {
        return false;
    }
} 

function price_format_kor(num) {
    var hanA = new Array("","일","이","삼","사","오","육","칠","팔","구","십");
    var danA = new Array("","십","백","천","","십","백","천","","십","백","천","","십","백","천");
    var result = "";
    for(i=0; i<num.length; i++) {
        str = "";
        han = hanA[num.charAt(num.length-(i+1))];
        if(han != "")
            str += han+danA[i];
            if(i == 4) str += "만"; 
            if(i == 8) str += "억"; 
            if(i == 12) str += "조"; 
            result = str + result;
    }
    if(num != 0)
        result = result + "원"; 
    return result ; 
} 


//]]>
$(document).on("keyup", "input:text[numberOnly]", function() {
    $(this).val( $(this).val().replace(/[^0-9]/gi,"") );
});

