var cert_cnt = 1;
var pofol_cnt = 1;
var file_cnt = 1;
var images = new Array();
$(document).ready(function(){

$('#option-01').keydown(function(key){
    if(key.keyCode == 13) {
        option_01();
    }
});

$('#option-01-div button').click(function(){
    option_01();
});

$('#option-02').keydown(function(key){
    if(key.keyCode == 13) {
        option_02();
    }
});

$('#option-03').keydown(function(key){
    if(key.keyCode == 13) {
        option_03();
    }
});

$('#option-02-div button').click(function(){
    option_02();
});

$('#profile-add').change(function(e){
    var files = e.target.files;
    var filesArr = Array.prototype.slice.call(files);

    var reader = new FileReader();
    var preview = $('.profile-label');

    var id = $(this).attr('id').split('-')[2];

    filesArr.forEach(function(f) {
        if(!f.type.match('image.*')) {
            alert('이미지 파일이 아닙니다.');
            return;
        }

        reader.onload = function(e) {
           preview.css('font-size', '0');
           preview.css('background-image', 'url(\"' + e.target.result + '\")');
        }
       reader.readAsDataURL(f);
    });
});

//<![CDATA[
$('.preview-delete-btn').click(function(){
    $(this).parent('.preview-back').css('background-image', '');
    var idx = $(this).parents('.preview-div').index();

    var target = $(this).data('target');
    $('.target_' + target).remove();
    
    setTimeout(function(){
        for(var i=idx+1; i<images.length; i++) {
            $('.preview-div').eq(i-1).find('.preview-back').css('background-image', 'url(\"' + images[i] + '\")');
            $('.preview-div').eq(i-1).find('.preview-delete-btn').addClass('preview-delete-btn-active');
            $('.preview-div').eq(i-1).find('.preview-delete-btn').data('target', $('.preview-div').eq(i).find('.preview-delete-btn').data('target'));
            images[i-1] = images[i];
        }
           
        $('.preview-div').eq(images.length-1).find('.preview-back').css('background-image', '');
        $('.preview-div').eq(images.length-1).find('.preview-delete-btn').removeClass('preview-delete-btn-active');
        $('.preview-div').eq(images.length-1).find('.preview-delete-btn').data('target', '');
        images.pop();
    }, 300);
    
    file_cnt --;
    $('#pofol-label').attr('for', 'pofol-add-' + pofol_cnt + '-' + file_cnt);
});


$('.eval-div span img').on('click', function(){
    var index = $(this).index();
    for(var i=0; i<=index; i++) {
        $(this).parent('span').find('img').eq(i).attr('src', $(this).parent('span').find('img').eq(i).attr('src').replace('empty', 'fill'));
    }
    for(var i=index+1; i<5; i++) {
        $(this).parent('span').find('img').eq(i).attr('src', $(this).parent('span').find('img').eq(i).attr('src').replace('fill', 'empty'));
    }
});
//]]>


$('.search-filter-btn').on('click', function(){
    var $this = $(this);
    var type = $(this).data('type');
    if(type == 'cate') {
        $('#filter-area').slideUp(500, function(){
            $('#filter-cate').slideDown(1000);
            $('#filter-area').hide();
            $this.addClass('search-filter-btn-active');
        });
    } else if (type == 'area') {
        $('#filter-cate').slideUp(500, function(){
            $('#filter-area').slideDown(1000);
            $('#filter-cate').hide();
            $('.search-filter-btn').removeClass('search-filter-btn-active');
            $this.addClass('search-filter-btn-active');
        }); 
    }
});

$('input[name=my-work]').on('change', function(){
    var id = $(this).attr('id');
    $('label.my-work-wrap').find('.work-div').removeClass('my-work-selected');
    $('label[for=' + id + ']').find('.work-div').addClass('my-work-selected');
});


});

function option_01() {
    var val = $('#option-01').val();
        
    var result = true;
    var html = '';
    
    if(val == '') {
        alert('발행기관을 입력해주세요.');
        $('#option-01').focus();
        return;
    }
    $('input[name=option_01_arr]').each(function(index, value) {
        var prefix = $(this).data('prefix').replace('#', '').replace('_', '');
        if(value.value == val && prefix == cert_cnt) {
            alert('이미 추가된 기관입니다.');
            result = false;
            return;
        }
    });
    if(result == true) {
        html += '<div class="options-div mr-2 mb-1">';
        html += '  <input type="hidden" name="option_01_arr" data-prefix="#' + cert_cnt + '_" value="' + val + '" class="machine_option cert_org_' + cert_cnt + '"/>';
        html += '  <pre class="options-txt">' + val + '</pre>';
        html += '  <i class="options-delete-btn"></i>';
        html += '</div>';
    }

    $('#option-01').val('');
    $('#option-01-append').append(html);
}

function option_02() {
    var val = $('#option-02').val();
        
    var result = true;
    var html = '';
    
    if(val == '') {
        alert('보유기술명을 입력해주세요.');
        $('#option-02').focus();
        return;
    }
    $('input[name=option_02_arr]').each(function(index, value) {
        if(value.value == val) {
            alert('이미 추가된 기술입니다.');
            result = false;
            return;
        }
    });
    if(result == true) {
        html += '<div class="options-div mr-2 mb-1">';
        html += '  <input type="hidden" name="option_02_arr" value="' + val + '" class="machine_option"/>';
        html += '  <pre class="options-txt">' + val + '</pre>';
        html += '  <i class="options-delete-btn"></i>';
        html += '</div>';
    }

    $('#option-02').val('');
    $('#option-02-append').append(html);
}

function option_03() {
    var val = $('#option-03').val();
        
    var result = true;
    var html = '';
    
    if(val == '') {
        alert('관련 기술을 입력해주세요.');
        $('#option-03').focus();
        return;
    }
    $('input[name=\'option_03_arr[]\']').each(function(index, value) {
        if(value.value == val) {
            alert('이미 추가된 기술입니다.');
            result = false;
            return;
        }
    });
    if(result == true) {
        html += '<div class="options-div mr-2 mb-1">';
        html += '  <input type="hidden" name="option_03_arr[]" value="' + val + '"/>';
        html += '  <pre class="options-txt">' + val + '</pre>';
        html += '  <i class="options-delete-btn"></i>';
        html += '</div>';
    }

    $('#option-03').val('');
    $('#option-03-append').append(html);
}

function certificate_add() {
    var cert_name = $('#certName').val();
    var cert_date = $('#certDate').val();
    var cert_org = '';
    
    if(cert_name == '') {
        alert('자격증명을 입력해주세요.');
        $('#certName').focus();
        return;
    } else if(cert_date == '') {
        alert('자격증 발행일을 설정해주세요.');
        $('#certDate').focus();
        return;
    } else if($('.cert_org_' + cert_cnt).length == 0) {
        alert('자격증 발행기관을 1개 이상 추가해주세요.');
        $('#option-01').focus();
        return;
    }
    
    $.each($('.cert_org_' + cert_cnt), function(index, value) {
        cert_org += index == 0 ? $(this).val() : ', ' + $(this).val();
    });
    
    var html = '';
    html += '<div class="cert-div modify-div" title="수정하기">';
    html += '   <input type="hidden" name="cert_name_arr[]" value="' + cert_name + '" />';
    html += '   <input type="hidden" name="cert_org_arr[]" value="' + cert_org + '" />';
    html += '   <input type="hidden" name="cert_date_arr[]" value="' + cert_date + '" />';
    html += '    <h3 class="cert-name">' + cert_name + '</h3>';
    html += '    <p class="cert-org">' + cert_org + '</p>';
    html += '    <p class="cert-date">' + cert_date + '</p>';
    html += '    <span class="btn-delete-cert"></span>';
    html += '</div>';
    
    $('.cert-list-div').append(html);
    $('#certName').val('');
    $('#certDate').val('');
    $('#option-01').val('');
    $('#option-01-append .options-div').hide();

    cert_cnt ++;
}

//<![CDATA[
function portfolio_add() {
    var pofol_name = $('#pofolName').val();
    var pofol_cate = $('#pofolCate').val();
    var pofol_startdate = $('#pofolStartdate').val();
    var pofol_enddate = $('#pofolEnddate').val();
    var pofol_content = $('#pofolContent').val();

    if(pofol_name == '') {
        alert('포트폴리오명을 입력해주세요.');
        $('#pofolName').focus();
        return;
    } else if(pofol_startdate == '') {
        alert('시작일을 설정해주세요.');
        $('#pofolStartdate').focus();
        return;
    } else if(pofol_enddate == '') {
        alert('종료일을 설정해주세요.');
        $('#pofolEnddate').focus();
        return;
    } else if(pofol_startdate > pofol_enddate) {
        alert('시작일이 종료일 보다 큽니다.');
        $('#pofolStartdate').focus();
        return;
    } 
    var pofol_date = pofol_startdate + ' ~ ' + pofol_enddate;
    var html = '';
    html += '<div class="pofol-div modify-div" title="수정하기">';
    html += '   <input type="hidden" name="pofol_name_arr[]" value="' + pofol_name + '" />';
    html += '   <input type="hidden" name="pofol_cate_arr[]" value="' + pofol_cate + '" />';
    html += '   <input type="hidden" name="pofol_startdate_arr[]" value="' + pofol_startdate + '" />';
    html += '   <input type="hidden" name="pofol_enddate_arr[]" value="' + pofol_enddate + '" />';
    html += '   <input type="hidden" name="pofol_content_arr[]" value="' + pofol_content + '" />';
    for(var i=0; i<file_cnt-1; i++) {
        html += '   <div class="pofol_picture_div">';
        html += '       <input type="hidden" name="pofol_picture_name_arr[]" value="' + images[i] + '">';
        var sub_html = $('.pofol-picture-div .pofol-picture').eq(i).find('.pofol_picture_div').html();
        if(sub_html) {
            html += '       <input type="hidden" name="pofol_picture_prev_index_arr[]" value="' + (pofol_cnt-1) + '" />';
            html += sub_html;
        } else {
            html += '       <input type="hidden" name="pofol_picture_index_arr[]" value="' + (pofol_cnt-1) + '" />';
        }
        html += '   </div>';
    }
    html += '    <h3 class="pofol-name">' + pofol_name + '</h3>';
    html += '    <p class="pofol-date">' + pofol_date + '</p>';
    html += '    <p class="pofol-text">' + pofol_cate + '</p>';
    html += '    <span class="btn-delete-pofol"></span>';
    html += '</div>';
    
    $('.pofol-list-div').append(html);

    for(var i=file_cnt; i<=4; i++) {
        $('#pofol-add-' + pofol_cnt + '-' + i).remove();
    }
    $('#pofolName').val('');
    $('#pofolCate').val('');
    $('#pofolStartdate').val('');
    $('#pofolEnddate').val('');
    $('#pofolContent').val('');

    file_cnt = 1;
    pofol_cnt ++;
    images = new Array();

    $('.pofol-picture-div .pofol-picture').find('p').text('');
    $('#pofol-label').attr('for', 'pofol-add-' + pofol_cnt + '-' + file_cnt);

    for(var i=0;i<4;i++) {
        var idx = $('.pofol-picture-div .pofol-picture').eq(i).index();
        $('.pofol-picture').eq(i).find("input[name='pofol_picture_" + pofol_cnt + "[]']").remove();
        if(idx == i) {
            html = '<input type="file" name="pofol_picture_' + pofol_cnt + '[]" class="input-file" id="pofol-add-' + pofol_cnt + '-' + (i+1) + '">';
            $('.pofol-picture-div .pofol-picture').eq(i).append(html);
        }
    }
    $('.pofol-picture .pofol_picture_div').remove();
    setTimeout(function(){
        $.each($('.pofol_picture_div'), function(index, value) {
            var idx = $(this).parents('.pofol-div').index();
            var index_arr = $(this).find("input[name='pofol_picture_index_arr[]']");
            var prev_index_arr = $(this).find("input[name='pofol_picture_prev_index_arr[]']");
            if(index_arr.length == 1) {
                index_arr.val(idx);
            }
            if(prev_index_arr.length == 1) {
                prev_index_arr.val(idx);
            }
        });
    },100);
}
//]]>

$(document).on('click', '.cert-div .btn-delete-cert', function(e){
    e.stopPropagation();
    $(this).parent('.cert-div').remove();
});
$(document).on('click', '.pofol-div .btn-delete-pofol', function(e){
    e.stopPropagation();
    $(this).parent('.pofol-div').remove();
});

$(document).on('click', '#pofol-label', function(e){
    if(file_cnt > 4) {
        alert('더이상 추가하실 수 없습니다.');
        return false;
    }
});

$(document).on('change', '.pofol-picture-div input[type=file]', function(e){
    var files = e.target.files;
    var filesArr = Array.prototype.slice.call(files);

    var reader = new FileReader();
    var div = $('.pofol-picture').eq(file_cnt-1);
    
    if(file_cnt > 4) {
        alert('더이상 추가하실 수 없습니다.');
        return false;
    }
    filesArr.forEach(function(f) {
       if(!/\.(gif|jpg|jpeg|png|hwp|pdf)$/i.test(f.name)) {
            alert('이미지, 한글, pdf 파일만 첨부 가능합니다.');
            return;
       }
        reader.onload = function(e) {
            div.find('p').text(f.name);
            images[file_cnt-1] = f.name;
            file_cnt ++;
            $('#pofol-label').attr('for', 'pofol-add-' + pofol_cnt + '-' + file_cnt);
        }
        reader.readAsDataURL(f);
    });
});

function go_partner_complete() {
    if($('input[name=career_year]').val() == '') {
        alert('업력을 입력해주세요.');
        $('input[name=career_year]').focus();
        return false;
    } else if($('input[name=career_type]').val() == '') {
        alert('업종을 입력해주세요.');
        $('input[name=career_type]').focus();
        return false;
    } else if(!$('select[name=sel_area] option:selected').data('seq')) {
        alert('지역을 선택해주세요.');
        $('select[name=sel_area]').focus();
        return false;
    } else if($('input[name=main_service]').val() == '') {
        alert('주서비스를 입력해주세요.');
        $('input[name=main_service]').focus();
        return false;
    } else if(!$('select[name=sel_cate] option:selected').data('seq')) {
        alert('카테고리를 선택해주세요.');
        $('select[name=sel_cate]').focus();
        return false;
    } 

    $('input[name=pofol_cnt]').val(pofol_cnt);
    $('input[name=area_seq]').val($('select[name=sel_area] option:selected').data('seq'));
    $('input[name=cate_seq]').val($('select[name=sel_cate] option:selected').data('seq'));
    
    var tech_list = '';
    $.each($('input[name=option_02_arr]'), function(index, value) {
        tech_list += index == 0 ? $(this).val() : ', ' + $(this).val();
    });
    $('input[name=tech_list]').val(tech_list);

    $('.pofol-picture .pofol_picture_div').remove();
    setTimeout(function(){
        $.each($('.pofol_picture_div'), function(index, value) {
            var idx = $(this).parents('.pofol-div').index();
            var index_arr = $(this).find("input[name='pofol_picture_index_arr[]']");
            var prev_index_arr = $(this).find("input[name='pofol_picture_prev_index_arr[]']");
            if(index_arr.length == 1) {
                index_arr.val(idx);
            }
            if(prev_index_arr.length == 1) {
                prev_index_arr.val(idx);
            }
        });
    },100);

    $('#frm_partner').submit();
}

//<![CDATA[
function go_osc_complete() {
    if(!$('select[name=sel_cate] option:selected').data('seq')) {
        alert('카테고리를 선택해주세요.');
        $('select[name=sel_cate]').focus();
        return false;
    } else if($('input[name=osc_name]').val() == '') {
        alert('외주명을 입력해주세요.');
        $('input[name=osc_name]').focus();
        return false;
    } else if($('input[name=expect_date]').val() == '') {
        alert('예상기간을 입력해주세요.');
        $('input[name=expect_date]').focus();
        return false;
    } else if($('input[name=budget').val() == '') {
        alert('지출예산을 입력해주세요.');
        $('input[name=budget]').focus();
        return false;
    } else if(isNaN($('input[name=budget').val()) == true) {
        alert('지출예산은 숫자만 입력이 가능합니다.');
        $('input[name=budget]').focus();
        return false;
    } else if(!$('select[name=sel_area] option:selected').data('seq')) {
        alert('업무 지역을 선택해주세요.');
        $('select[name=sel_area]').focus();
        return false;
    } else if($('input[name=osc_content').val() == '') {
        alert('업무 내용을 입력해주세요.');
        $('input[name=osc_content]').focus();
        return false;
    }  else if($('input[name=osc_end_date').val() == '') {
        alert('모집마감일을 설정 해주세요.');
        $('input[name=osc_end_date]').focus();
        return false;
    } else if($('input[name=start_expect_date').val() == '') {
        alert('업무시작 예상일을 설정 해주세요.');
        $('input[name=start_expect_date]').focus();
        return false;
    } else if($('input[name=start_expect_date').val() < $('input[name=osc_end_date').val()) {
        alert('업무시작일은 마감일 이후여야 합니다.');
        $('input[name=start_expect_date]').focus();
        return false;
    } 
    var osc_tech = '';
    $.each($('input[name=\'option_03_arr[]\']'), function(index, value) {
        osc_tech += index == 0 ? $(this).val() : ', ' + $(this).val();
    });
    $('input[name=file_cnt]').val(file_cnt);
    $('input[name=osc_tech]').val(osc_tech);
    $('input[name=area_seq]').val($('select[name=sel_area] option:selected').data('seq'));
    $('input[name=cate_seq]').val($('select[name=sel_cate] option:selected').data('seq'));
    $('input[name=cate_sub_seq]').val($('select[name=sel_cate_sub].active option:selected').data('seq'));
    $('input[name=budget]').val($('input[name=budget]').val() + '0000');
    $('#frm_osc').submit();
}
//]]>

function change_cate_sub() {
    var cate_seq = $('select[name=sel_cate] option:selected').data('seq');
    var isChange = false;
    $.each($('select[name=sel_cate_sub]'), function(index, value) {
        var target = $(this).data('target');
        if(target == 'sub-' + cate_seq) {
            $('select[name=sel_cate_sub]').addClass('d-none');
            $('select[name=sel_cate_sub]').removeClass('active');
            $(this).removeClass('d-none');
            $(this).addClass('active');
            isChange = true;
        }
    });
    if(isChange == false) {
        $('select[name=sel_cate_sub]').addClass('d-none');
        $('select[name=sel_cate_sub]').eq(0).removeClass('d-none');
        $('select[name=sel_cate_sub]').removeClass('active');
        $('select[name=sel_cate_sub]').eq(0).addClass('active');
    }
}

function osc_temp_save() {
    var osc_tech = '';
    $.each($('input[name=\'option_03_arr[]\']'), function(index, value) {
        osc_tech += index == 0 ? $(this).val() : ', ' + $(this).val();
    });
    $('input[name=file_cnt]').val(file_cnt);
    $('input[name=osc_tech]').val(osc_tech);
    $('input[name=area_seq]').val($('select[name=sel_area] option:selected').data('seq'));
    $('input[name=cate_seq]').val($('select[name=sel_cate] option:selected').data('seq'));
    $('input[name=cate_sub_seq]').val($('select[name=sel_cate_sub].active option:selected').data('seq'));
    
    var temp_list = JSON.parse(localStorage.getItem('osc_temp_list'));
    var temp_obj = {
        'file_cnt': file_cnt,
        'osc_tech': $('input[name=osc_tech]').val(),
        'area_seq': $('input[name=area_seq]').val(),
        'cate_seq': $('input[name=cate_seq]').val(),
        'cate_sub': $('input[name=cate_sub]').val(),
        'osc_name': $('input[name=osc_name]').val(),
        'expect_date': $('input[name=expect_date]').val(),
        'budget': $('input[name=budget]').val(),
        'osc_content': $('textarea[name=osc_content]').val(),
        'osc_end_date': $('input[name=osc_end_date]').val(),
        'start_expect_date': $('input[name=start_expect_date]').val(),
        'save_time': getTimeStamp()
    };
    if(!temp_list) {
        temp_list = [];
        temp_list.push(temp_obj);
    } else {
        temp_list.push(temp_obj);
    }
    localStorage.setItem('osc_temp_list', JSON.stringify(temp_list));

    
/*
    //<![CDATA[
    for(var i=1; i<file_cnt; i++) {
        localStorage.setItem('images_' + i, images[i-1]);
    }
    //]]>
*/
    alert('작성하신 데이터가 저장되었습니다.\n임시 저장된 데이터는 마이페이지에서 재작성 하실 수 있습니다.');
}

function osc_load_data(temp_seq) {
    var temp_list = JSON.parse(localStorage.getItem('osc_temp_list'));
    var temp_obj = temp_list[temp_seq];

    $.each($('select[name=sel_area] option'), function(index, value) {
        if($(this).data('seq') == temp_obj.area_seq) {
            $(this).prop('selected', true);
            return false;
        }
    });
    $.each($('select[name=sel_cate] option'), function(index, value) {
        if($(this).data('seq') == temp_obj.cate_seq) {
            $(this).prop('selected', true);
            return false;
        }
    });
    $('input[name=cate_sub]').val(temp_obj.cate_sub);
    $('input[name=expect_date]').val(temp_obj.expect_date);
    $('input[name=budget]').val(temp_obj.budget);
    $('input[name=osc_name]').val(temp_obj.osc_name);
    $('textarea[name=osc_content]').val(temp_obj.osc_content);
    $('input[name=osc_end_date]').val(temp_obj.osc_end_date);
    $('input[name=start_expect_date]').val(temp_obj.start_expect_date);
    
    var osc_tech = temp_obj.osc_tech;
    var osc_tech_arr = osc_tech.split(', ');

    $.each(osc_tech_arr, function(index, value) {
        var html = '';
        html += '<div class="options-div mr-2 mb-1">';
        html += '  <input type="hidden" name="option_03_arr[]" value="' + value + '"/>';
        html += '  <pre class="options-txt">' + value + '</pre>';
        html += '  <i class="options-delete-btn"></i>';
        html += '</div>';
        $('#option-03-append').append(html);
    });
/*
    //<![CDATA[
    file_cnt = localStorage.getItem('file_cnt');
    for(var i=1; i<file_cnt; i++) {
        images[i-1] = localStorage.getItem('images_' + i);
        $('.preview-div').eq(i-1).find('.preview-back').css('background-image', 'url(\"' + images[i-1] + '\")');
        $('.preview-div').eq(i-1).find('.preview-delete-btn').addClass('preview-delete-btn-active');
    }
    //]]>
*/

    temp_list.splice(temp_seq, 1);
    localStorage.setItem('osc_temp_list', JSON.stringify(temp_list));

}

function showPartnerFilter(type, value) {
    if(type == 'c') {
        $('#filter-cate').show();
        $('#filter-cate ul li').eq(value-1).addClass('accent-txt');
    } else if (type == 'a') {
        $('#filter-area').show();
        $('#filter-area ul li').eq(value-1).addClass('accent-txt');
    }
}

function go_osc_req() {
    if($('.my-work-selected').length == 0) {
        alert('지원 요청할 외주를 선택해주세요.');
        return;
    }
    if(confirm('이 외주작업을 파트너에게 지원요청하시겠습니까 ?')) {
        var osc_seq = $('.my-work-selected').parent('label').data('seq');
        $('input[name=osc_seq]').val(osc_seq);
        $('#frm_osc_req').submit();
    }
}

function go_osc_apply() {
    if(confirm('해당 외주를 수주하시겠습니까 ?')) {
        $('#frm_osc_apply').submit();
    }
}

function go_meeting_process(po_seq, userid) {
    if(confirm('해당 파트너에게 미팅신청을 하시겠습니까 ?')) {
        $('input[name=po_seq]').val(po_seq);
        $('input[name=userid]').val(userid);
        $('input[name=meet_state]').val('1');
        $('#frm_meeting').submit();
    }
}

function go_meeting_res_process(po_seq, userid) {
    if(confirm('미팅 신청을 받으시겠습니까 ?')) {
        $('input[name=po_seq]').val(po_seq);
        $('input[name=userid]').val(userid);
        $('#frm_meeting').submit();
    }
}

function go_meeting_final_process(po_seq, userid) {
    if(confirm('해당 수주사로 최종 선택하시겠습니까 ?')) {
        $('input[name=po_seq]').val(po_seq);
        $('input[name=userid]').val(userid);
        $('input[name=meet_state]').val('3');
        $('#frm_meeting').submit();
    }
}

function go_partner_eval_process() {
    if($('#evalContent').val() == '') {
        alert('평가 내용을 입력해주세요.');
        $('#evalContent').focus();
        return;
    }
    if(confirm('입력하신 내용으로 해당 파트너를 평가하시겠습니까 ?')) {
        var grade = 0;
        var tot_grade = 0;

        $.each($('#grade_01 img'), function(index, value) {
            if($(this).attr('src').indexOf('fill') != -1)
                grade ++;
        });
        $('input[name=grade_01]').val(grade);
        tot_grade += grade;
        grade = 0;

         $.each($('#grade_02 img'), function(index, value) {
            if($(this).attr('src').indexOf('fill') != -1)
                grade ++;
        });
        $('input[name=grade_02]').val(grade);
        tot_grade += grade;
        grade = 0;

         $.each($('#grade_03 img'), function(index, value) {
            if($(this).attr('src').indexOf('fill') != -1)
                grade ++;
        });
        $('input[name=grade_03]').val(grade);
        tot_grade += grade;
        grade = 0;

     $.each($('#grade_04 img'), function(index, value) {
            if($(this).attr('src').indexOf('fill') != -1)
                grade ++;
        });
        $('input[name=grade_04]').val(grade);
        tot_grade += grade;
        grade = 0;

         $.each($('#grade_05 img'), function(index, value) {
            if($(this).attr('src').indexOf('fill') != -1)
                grade ++;
        });
        $('input[name=grade_05]').val(grade);
        tot_grade += grade;

        $('input[name=grade]').val(parseInt(tot_grade/5));

        $('#frm_partner_eval').submit();
    }
}

function go_sale_eval_process() {
    if($('#evalContent').val() == '') {
        alert('평가 내용을 입력해주세요.');
        $('#evalContent').focus();
        return;
    }
    if(confirm('입력하신 내용으로 해당 판매자를 평가하시겠습니까 ?')) {
        var grade = 0;
        var tot_grade = 0;

        $.each($('#grade_01 img'), function(index, value) {
            if($(this).attr('src').indexOf('fill') != -1)
                grade ++;
        });
        $('input[name=grade_01]').val(grade);
        tot_grade += grade;
        grade = 0;

         $.each($('#grade_02 img'), function(index, value) {
            if($(this).attr('src').indexOf('fill') != -1)
                grade ++;
        });
        $('input[name=grade_02]').val(grade);
        tot_grade += grade;
        grade = 0;

         $.each($('#grade_03 img'), function(index, value) {
            if($(this).attr('src').indexOf('fill') != -1)
                grade ++;
        });
        $('input[name=grade_03]').val(grade);
        tot_grade += grade;
        grade = 0;

     $.each($('#grade_04 img'), function(index, value) {
            if($(this).attr('src').indexOf('fill') != -1)
                grade ++;
        });
        $('input[name=grade_04]').val(grade);
        tot_grade += grade;
        grade = 0;

         $.each($('#grade_05 img'), function(index, value) {
            if($(this).attr('src').indexOf('fill') != -1)
                grade ++;
        });
        $('input[name=grade_05]').val(grade);
        tot_grade += grade;

        $('input[name=grade]').val(parseInt(tot_grade/5));

        $('#frm_sale_eval').submit();
    }
}

$(document).on('click', '.cert-div.modify-div', function(){
    var cert_name = $(this).find('input[name=\'cert_name_arr[]\']').val();
    var cert_org_arr = $(this).find('input[name=\'cert_org_arr[]\']').val().split(',');
    var cert_date = $(this).find('input[name=\'cert_date_arr[]\']').val();

    cert_cnt = cert_cnt - 1;

    $('#option-01-append').html('');
    $.each(cert_org_arr, function(index, value){
        var html = '';
        html += '<div class="options-div mr-2 mb-1">';
        html += '  <input type="hidden" name="option_01_arr" data-prefix="#' + cert_cnt + '_" value="' + value + '" class="machine_option cert_org_' + cert_cnt + '"/>';
        html += '  <pre class="options-txt">' + value + '</pre>';
        html += '  <i class="options-delete-btn"></i>';
        html += '</div>';    
        $('#option-01-append').append(html);
    });

    $('#option-02-append').html('');

    $('#certName').val(cert_name);
    $('#certDate').val(cert_date);

    $(this).remove();
});

//<![CDATA[
$(document).on('click', '.pofol-div.modify-div', function(){
    var pofol_name = $(this).find('input[name=\'pofol_name_arr[]\']').val();
    var pofol_cate = $(this).find('input[name=\'pofol_cate_arr[]\']').val();
    var pofol_startdate = $(this).find('input[name=\'pofol_startdate_arr[]\']').val();
    var pofol_enddate = $(this).find('input[name=\'pofol_enddate_arr[]\']').val();
    var pofol_content = $(this).find('input[name=\'pofol_content_arr[]\']').val();
    
    $('.pofol-picture .pofol_picture_div').remove();
    $('.pofol-picture').find('p').text('');

    pofol_cnt = pofol_cnt - 1;
    images = new Array();
    file_cnt = 1;
    $.each($(this).find('.pofol_picture_div'), function(index, value) {
        var picture_name = $(this).find('input[name=\'pofol_picture_name_arr[]\']').val();
        var picture_seq = $(this).find('input[name=\'pofol_picture_seq_arr[]\']').val();
        var picture_path = $(this).find('input[name=\'pofol_picture_path_arr[]\']').val();
        var picture_prev_index = $(this).find('input[name=\'pofol_picture_prev_index_arr[]\']').val();
        $('.pofol-picture').eq(index).find('p').text(picture_name);
        if(picture_seq) {
            var html = '';
            html += '<div class="pofol_picture_div">';
            html += '<input type="hidden" name="pofol_picture_seq_arr[]" value="' + picture_seq + '" class="target_' + picture_seq + '" />';
            html += '<input type="hidden" name="pofol_picture_path_arr[]" value="' + picture_path + '" class="target_' + picture_seq + '" />';
        }
        $('.pofol-picture').eq(index).append(html);

        file_cnt ++;
        images[index] = picture_name;
    });
    $('#pofol-label').attr('for', 'pofol-add-' + pofol_cnt + '-' + file_cnt);
    for(var i=file_cnt; i<=4; i++) {
        var html = '<input type="file" name="pofol_picture_' + pofol_cnt + '[]" class="input-file" id="pofol-add-' + pofol_cnt + '-' + i + '">';
        $('.pofol-picture').eq(i-1).find("input[name='pofol_picture_" + pofol_cnt + "[]']").remove();
        $('.pofol-picture').eq(i-1).append(html);
    }
    $('#pofolName').val(pofol_name);
    $('#pofolCate').val(pofol_cate);
    $('#pofolStartdate').val(pofol_startdate);
    $('#pofolEnddate').val(pofol_enddate);
    $('#pofolContent').val(pofol_content);

    $(this).remove();
});
//]]>

function default_load_data(data_cert_cnt, data_pofol_cnt) {
    cert_cnt = data_cert_cnt;
    pofol_cnt = data_pofol_cnt;
    $('#pofol-label').attr('for', 'pofol-add-' + pofol_cnt + '-1');
}