var find_cnt = 0;
$(document).ready(function(){

$('select').on('change', function(){
    var mode = $(this).find('option:selected').data('mode');
    var id = $(this).data('id');

    if(!id) {
        return;
    }
    if(mode == 'input') {
        $('#' + id).find('input[type=text]').val('');
        $('#' + id).removeClass('d-none');
    } else {
        $('#' + id).addClass('d-none');
    }
});

$('.filter-icon-div').on('click', function(){
    var cate = $(this).data('cate');
    $('.filter-icon-div').removeClass('icon-selected');
    $(this).addClass('icon-selected');
    showFilter(cate);
});

$('.detail-img-wrap .sm-img-div').hover(function(){
    $(this).addClass('on');
}, function(){
    $(this).removeClass('on');
});

$('.detail-img-wrap .sm-img-div').click(function(){
    var index = $(this).index() + 1;
    $('.detail-img-wrap .img-div').addClass('d-none');
    $('.detail-img-wrap #img-' + index).removeClass('d-none');
    $('.detail-img-wrap .sm-img-div').removeClass('active');
    $(this).addClass('active');
});

$('#btn_eval_modify').click(function(){
    var $content = $(this).parents('.user-eval-div').find('#eval_content');
    if($(this).text() == '수정') {
        $content.prop('readonly', false);
        $content.addClass('write');
        $(this).text('변경');
    } else {
        if($content.val() == '') {
            alert('평가 내용을 입력해주세요.');
            $content.focus();
            return;
        }
        if(confirm('내용을 변경하시겠습니까 ?')) {
            var meval_seq = $(this).data('seq');
            var content = $content.val();
            var $this = $(this);
            $.ajax({
                type: 'post',
                url: '/sch/ajax_eval_modify',
                dataType: 'json',
                data: {'meval_seq': meval_seq, 'content': content},
                success: function(data) {
                    if(data.result == 'true') {
                        $this.text('수정'); 
                        alert('변경이 완료되었습니다.');
                        $content.prop('readonly', true);
                        $content.removeClass('write');
                    } else {
                        alert('변경에 실패했습니다. 다시 시도해주세요.');
                    }
                },
                error: function() {
                    console.log('error');
                }
            });
        }
    }
});

$('input[name=recommend]').on('change', function(){
    var id = $(this).attr('id');
    $('label.my-work-wrap').find('.scroll-div').removeClass('my-work-selected');
    $('label[for=' + id + ']').find('.scroll-div').addClass('my-work-selected');

    var price = $('.my-work-selected').data('price');
    $('#current-price').text(comma(price) + '원');
    $('#current-price').data('price', price);
});

// end
});

function search_year() {
    var min = $('input[name=min_year]').val();
    var max = $('input[name=max_year]').val();
    if(min == '' && max == '') {
        alert('둘 중에 하나는 입력해야 합니다.');
        $('input[name=min_year]').focus();
        return;
    }
    if(min == '') {
        min = -1;
    }
    if(max == '') {
        max = -1;
    }
    $('input[name=selected]').val('y');
    $('input[name=cate_y]').val(min + ':' + max);
    $('input[name=h]').val('');
    $('input[name=d]').val('');
    $('input[name=o]').val('');
    $('input[name=more]').val('');
    $('input[name=focus]').val('');
    $('#frm_search').submit();
}

function search_price() {
    var min = $('input[name=min_price]').val();
    var max = $('input[name=max_price]').val();
    if(min == '' && max == '') {
        alert('둘 중에 하나는 입력해야 합니다.');
        $('input[name=min_price]').focus();
        return;
    }
    if(min == '') 
        min = -1;
    else
        min = min + '0000';
    if(max == '') 
        max = -1;
    else 
        max = max + '0000';
    $('input[name=selected]').val('p');
    $('input[name=cate_p]').val(min + ':' + max);
    $('input[name=h]').val('');
    $('input[name=d]').val('');
    $('input[name=o]').val('');
    $('input[name=more]').val('');
    $('input[name=focus]').val('');
    $('#frm_search').submit();
}

function search_kind_group(gubun, no) {
    var h = '';
    var d = '';
    var o = '';
    var focus = '';
    if(gubun == 'h') {
        h = no;
        d = $('#dealerZone .box-filter .accent-txt').data('no');
        o = $('#normalZone .box-filter .accent-txt').index() + 1;
        focus = '#highlightZoneFocus';
    } else if (gubun == 'd') {
        d = no;
        h = $('#highlightZone .box-filter .accent-txt').data('no');
        o = $('#normalZone .box-filter .accent-txt').index() + 1;
        focus = '#dealerZoneFocus';
    }
    $('input[name=h]').val(h);
    $('input[name=d]').val(d);
    $('input[name=o]').val(o);
    $('input[name=focus]').val(focus);
    $('#frm_search').submit();
}

function search_order(no) {
    h = $('#highlightZone .box-filter .accent-txt').data('no');
    d = $('#dealerZone .box-filter .accent-txt').data('no');
    $('input[name=h]').val(h);
    $('input[name=d]').val(d);
    $('input[name=o]').val(no);
    $('input[name=focus]').val('#normalZoneFocus');
    $('#frm_search').submit();
}

function search_filter(gubun, no) {
    $('input[name=h]').val('');
    $('input[name=d]').val('');
    $('input[name=o]').val('');
    $('input[name=more]').val('');
    $('input[name=focus]').val('');

    if(gubun == 'k') {
        $('input[name=selected]').val('k');
        $('input[name=cate_k]').val(no);
    } else if(gubun == 't') {
        $('input[name=selected]').val('t');
        $('input[name=cate_t]').val(no);
    } else if (gubun == 'f') {
        $('input[name=selected]').val('f');
        $('input[name=cate_f]').val(no);
    } else if (gubun == 'm') {
        $('input[name=selected]').val('m');
        $('input[name=cate_m]').val(no);
    } else if (gubun == 'a') {
        $('input[name=selected]').val('a');
        $('input[name=cate_a]').val(no);
    }
    $('#frm_search').submit();
}

function showFilter(selected) {
    if(selected == 'k') {
        $('.filter-text-wrap > ul').removeClass('d-block');
        $('#kindList').addClass('d-block');
    } else if(selected == 't') {
        $('.filter-text-wrap > ul').removeClass('d-block');
        $('#kindTypeList').addClass('d-block');
    } else if(selected == 'f') {
        $('.filter-text-wrap > ul').removeClass('d-block');
        $('#mnfList').addClass('d-block');
    } else if(selected == 'm') {
        $('.filter-text-wrap > ul').removeClass('d-block');
        $('#modelList').addClass('d-block');
    } else if(selected == 'y') {
        $('.filter-text-wrap > ul').removeClass('d-block');
        $('#yearList').addClass('d-block');

    } else if(selected == 'p') {
        $('.filter-text-wrap > ul').removeClass('d-block');
        $('#priceList').addClass('d-block');

    } else if(selected == 'a') {
        $('.filter-text-wrap > ul').removeClass('d-block');
        $('#areaList').addClass('d-block');
    } 
}

function show_more(type) {
    $('input[name=more]').val(type);
    $('input[name=focus]').val('');
    $('#frm_search').submit();
}

function show_search_history(cate_k, cate_t, cate_f, cate_m, cate_y, cate_p, cate_a) {
    var html = '';
    var value = '';
    if(cate_k != '0') {
        $.each($('#kindList li'), function(i, v) {
            if($(this).data('seq') == cate_k) {
                value = $(this).text();
                return;
            }
        });
        html += '<li>';
        html += '    <span>' + value + '</span>';
        html += '    <button type="button" data-cate="k">삭제</button>';
        html += '</li>';
    }
    if(cate_t != '0') {
        $.each($('#kindTypeList li'), function(i, v) {
            if($(this).data('seq') == cate_t) {
                value = $(this).text();
                return;
            }
        });
        html += '<li>';
        html += '    <span>' + value + '</span>';
        html += '    <button type="button" data-cate="t">삭제</button>';
        html += '</li>';
    }
    if(cate_f != '0') {
        $.each($('#mnfList li'), function(i, v) {
            if($(this).data('seq') == cate_f) {
                value = $(this).text();
                return;
            }
        });
        html += '<li>';
        html += '    <span>' + value + '</span>';
        html += '    <button type="button" data-cate="f">삭제</button>';
        html += '</li>';
    }
    if(cate_m != '0') {
        $.each($('#modelList li'), function(i, v) {
            if($(this).data('seq') == cate_m) {
                value = $(this).text();
                return;
            }
        });
        html += '<li>';
        html += '    <span>' + value + '</span>';
        html += '    <button type="button" data-cate="m">삭제</button>';
        html += '</li>';
    }
    if(cate_y != '0') {
        var values = cate_y.split(':');
        if(values[0] == '-1') {
            value = '~' + values[1] + '년형';
        } else if(values[1] == '-1') {
            value = values[0] + '년형~';
        } else {
            value = values[0] + '년형~' + values[1] + '년형';
        }
        html += '<li>';
        html += '    <span>' + value + '</span>';
        html += '    <button type="button" data-cate="y">삭제</button>';
        html += '</li>';
    }
    if(cate_p != '0') {
        var values = cate_p.split(':');
        if(values[0] == '-1') {
            value = '~' + price_format(values[1]) + '원';
        } else if(values[1] == '-1') {
            value = price_format(values[0]) + '원~';
        } else {
            value = price_format(values[0]) + '원~' + price_format(values[1]) + '원';
        }
        html += '<li>';
        html += '    <span>' + value + '</span>';
        html += '    <button type="button" data-cate="p">삭제</button>';
        html += '</li>';
    }
    if(cate_a != '0') {
        $.each($('#areaList li'), function(i, v) {
            if($(this).data('seq') == cate_a) {
                value = $(this).text();
                return;
            }
        });
        html += '<li>';
        html += '    <span>' + value + '</span>';
        html += '    <button type="button" data-cate="a">삭제</button>';
        html += '</li>';
    }
    $('#ulSearchHistory').html(html);
}

$(document).on('click', '#ulSearchHistory li button', function(){
     var cate = $(this).data('cate');
     $('input[name=cate_' + cate + ']').val('');
     $('#frm_search').submit();
});

function go_ajax_like(info_seq) {
    var like_yn = '';

    if($('#btnLike').hasClass('btn-type-01-active')) {
        like_yn = 'y';
    } else {
        like_yn = 'n';
    }
    
    $.ajax({
        type: 'post',
        url: '/sch/ajax_update_like/' + info_seq + '/' + like_yn,
        dataType: 'json',
        data: {'info_seq': info_seq},
        success: function(data) {
                console.log(data);
            $('#likecnt').text(data.like_cnt);
            if(data.like_yn == 'y') 
                $('#btnLike').addClass('btn-type-01-active');
            else
                $('#btnLike').removeClass('btn-type-01-active');
        },
        error: function() {
            console.log('error');
        }
    });
}

function go_visit() {
    var result = true;
    $.each($('input[name=\'hope_date[]\']'), function(index, value) {
        if($(this).val() == '') {
            alert('방문일을 설정해주세요.');
            $(this).focus();
            result = false;
            return false;
        }
    });
    if(result == false) return;

    $.each($('input[name=\'hope_time[]\']'), function(index, value) {
        if($(this).val() == '') {
            alert('방문시간을 설정해주세요.');
            $(this).focus();
            result = false;
            return false;
        }
    });
    if(result == false) return;
    $('input[name=\'hope_date[]\']').remove();
    $('input[name=\'hope_time[]\']').remove();
    if(confirm('위의 시간대로 방문예약을 신청하시겠습니까 ?')) {
        $('#frm_visit').submit();    
    }
}

function go_imdbuy() {
    if($('input[name=hope_date]').val() == '') {
        alert('배송일을 설정해주세요.');
        $(this).focus();
        return;
    } else if($('input[name=hope_time]').val() == '') {
        alert('배송시간을 설정해주세요.');
        $(this).focus();
        return;
    }

    if(confirm('위의 입력사항으로 즉시구매를 하시겠습니까 ?')) {
        var val = '';
        $.each($('.deliver-service-div button'), function(index, value) {
            if($(this).hasClass('btn-type-02-active')) {
                val += (val == '' ? '' : ',') + $(this).text();
            }
        });
        $('input[name=deliver_service]').val(val);
        $('#frm_imdbuy').submit();    
    }
}

//<![CDATA[
function go_proposal(fixed_price, price_proposal, mode) {
    if(mode == 'counter') {
        if($('input[name=counter_price]').val() == '') {
            alert('카운터딜 금액을 입력해주세요.');
            $('input[name=counter_price]').focus();
            return;
        } else if(isNaN($('input[name=counter_price]').val()) == true) {
            alert('카운터딜 금액은 숫자만 입력할 수 있습니다.');
            $('input[name=counter_price]').focus();
            return;
        } else if($('input[name=counter_date]').val() == '') {
            alert('메시지 답변기한을 설정해주세요.');
            $('input[name=counter_date]').focus();
            return;
        } 
        if(confirm('위의 입력사항으로 카운터딜을 하시겠습니까 ?')) {
            $('input[name=counter_price]').val($('input[name=counter_price]').val() + '0000');
            $('#frm_proposal_res').submit();    
        }
    } else {
        if($('input[name=prop_price]').val() == '') {
            alert('제안금액을 입력해주세요.');
            $('input[name=prop_price]').focus();
            return;
        } else if($('input[name=prop_date]').val() == '') {
            alert('메시지 답변기한을 설정해주세요.');
           $('input[name=prop_date]').focus();
            return;
        } 
        var range = fixed_price * price_proposal / 100;
        var prop_price = $('input[name=prop_price]').val() + '0000';
        if(Number(prop_price) < Number(fixed_price)-range ||
           Number(prop_price) > Number(fixed_price)+range) {
            alert('판매자 가격의 ' + price_proposal + '% 이내로 제안이 가능합니다.');
            $('input[name=prop_price]').focus();
            return;
        }

        if(confirm('위의 입력사항으로 가격을 제안하시겠습니까 ?')) {
            $('input[name=prop_price]').val(prop_price);
            $('#frm_proposal').submit();    
        }
    }
    
}
//]]>

function go_perform(perform_seq, upload) {
    if(!perform_seq) {
        alert('성능검사를 신청하지 않은 기계입니다.');
    } else {
        if(!upload) {
            alert('성능검사 보고서가 작성되지 않았습니다.');
        } else {
            window.open('/admin/com/perform_report?seq=' + perform_seq + '&popup=y', "성능검사 보고서", "width=900, height=" + window.innerHeight + ", left=300, top=0");
        }
    }
}

function go_mch_eval(type, info_seq) {
    if($('#evalContent').val() == '') {
        alert('평가 내용을 입력해주세요.');
        $('#evalContent').focus();
        return;
    }
    if(confirm('입력하신 내용으로 해당 기계를 평가하시겠습니까 ?')) {
        $('input[name=type]').val(type);
        $('input[name=info_seq]').val(info_seq);

        var grade = 0;
        $.each($('#grade_01 img'), function(index, value) {
            if($(this).attr('src').indexOf('fill') != -1)
                grade ++;
        });
        $('input[name=grade_01]').val(grade);
        
        grade = 0;

         $.each($('#grade_02 img'), function(index, value) {
            if($(this).attr('src').indexOf('fill') != -1)
                grade ++;
        });
        $('input[name=grade_02]').val(grade);
        
        grade = 0;

         $.each($('#grade_03 img'), function(index, value) {
            if($(this).attr('src').indexOf('fill') != -1)
                grade ++;
        });
        $('input[name=grade_03]').val(grade);
        
        grade = 0;

     $.each($('#grade_04 img'), function(index, value) {
            if($(this).attr('src').indexOf('fill') != -1)
                grade ++;
        });
        $('input[name=grade_04]').val(grade);
        
        grade = 0;

         $.each($('#grade_05 img'), function(index, value) {
            if($(this).attr('src').indexOf('fill') != -1)
                grade ++;
        });
        $('input[name=grade_05]').val(grade);
        
        $('#frm_mch_eval').submit();
    }
}

var day, hour, min, sec; 
function bid_timer_load(isEnd, restTime, info_seq) {
    day = Math.floor(restTime/86400); 
	restHour = restTime%86400; 
	hour = Math.floor(restHour/3600); 
	restMin = restHour%3600; 
	min = Math.floor(restMin/60); 
	sec = Math.floor(restMin%60); 
	
    bid_timer(isEnd, info_seq);
}

var bid_time = '';
//<![CDATA[
function bid_timer(isEnd, info_seq) {
    if(isEnd == true || (sec == 0 && min == 0 && hour == 0 && day == 0)) { 
        bid_time = '입찰기간이 종료되었습니다.';
        $('.bidTime, #bid_time_' + info_seq).text(bid_time);    
        return; 
    } 
    else { 
        bid_time = day +'일 ' + hour + '시간 ' + min + '분 ' + sec + '초 ';
        $('.bidTime, #bid_time_' + info_seq).text(bid_time); 
    } 
    
    sec=sec-1;        
    if(sec == -1) { 
        sec = 59; 
        min = min-1; 
    } 
    
    if(min == -1) {                                            
        min=59; 
        hour = hour - 1; 
    } 
    
    if(hour == -1) {                                            
        hour = 23; 
        day = day - 1; 
    } 
    window.setTimeout('bid_timer()',1000); 
} 
//]]>

function get_bid_time() {
    return bid_time;
}

function show_mob_filter() {
    $('.col-filter').css('display', 'initial');
}

function find_add() {
    if(confirm('아래 기입한 정보를 추가하시겠습니까 ?')) {
        add_find_list();
    }
}

function check_find() {
    if(!$('select[name=sel_kind] option:selected').data('seq')) {
        alert('기계 종류를 선택해주세요.');
        $('select[name=sel_kind]').focus();
        return false;
    } else if(!($('select[name=sel_mnf] option:selected').data('seq') || $('select[name=sel_mnf] option:selected').data('val')) && $('#input-mnf').hasClass('d-none')) {
        alert('제조사를 선택해주세요.');
        $('select[name=sel_mnf]').focus();
        return false;
    } else if($('select[name=sel_mnf] option:selected').data('mode') == 'input' && $('input[name=txt_mnf]').val() == '') {
        alert('제조사를 입력해주세요.');
        $('input[name=txt_mnf]').focus();
        return false;
    } else if(!$('select[name=sel_model] option:selected').data('seq') && $('#input-model').hasClass('d-none')) {
        alert('모델을 선택해주세요.');
        $('select[name=sel_model]').focus();
        return false;
    } else if($('select[name=sel_model] option:selected').data('mode') == 'input' && $('input[name=txt_model]').val() == '') {
        alert('모델을 입력해주세요.');
        $('input[name=txt_model]').focus();
        return false;
    } else if($('select[name=sel_year]').eq(0).find('option:selected').data('val') == undefined) {
        alert('연식을 선택해주세요.');
        $('select[name=sel_year]').eq(0).focus();
        return false;
    } else if($('select[name=sel_year]').eq(1).find('option:selected').data('val') == undefined) {
        alert('연식을 선택해주세요.');
        $('select[name=sel_year]').eq(1).focus();
        return false;
    } else if($('select[name=sel_year]').eq(0).find('option:selected').data('val') == 0 && $('#model_year_01').val() == '') {
        alert('연식을 입력해주세요.');
        $('#model_year_01').focus();
        return false;
    } else if($('select[name=sel_year]').eq(1).find('option:selected').data('val') == 0 && $('#model_year_02').val() == '') {
        alert('연식을 입력해주세요.');
        $('#model_year_02').focus();
        return false;
    } else if($('select[name=sel_year]').eq(0).find('option:selected').data('val') == 0 && $('#model_year_01').val().length < 4 || $('#model_year_01').val().length > 5) {
        alert('연식이 올바르지 않습니다.');
        $('#model_year_01').focus();
        return false;
    } else if($('select[name=sel_year]').eq(1).find('option:selected').data('val') == 0 && $('#model_year_02').val().length < 4 || $('#model_year_02').val().length > 5) {
        alert('연식이 올바르지 않습니다.');
        $('#model_year_02').focus();
        return false;
    } else if($('select[name=sel_year]').eq(0).find('option:selected').data('val') == 0 && $('#model_year_01').val().length == 4 && isNaN($('#model_year_01').val()) == true) {
        alert('연식이 올바르지 않습니다.');
        $('#model_year_01').focus();
        return false;
    } else if($('select[name=sel_year]').eq(1).find('option:selected').data('val') == 0 && $('#model_year_02').val().length == 4 && isNaN($('#model_year_02').val()) == true) {
        alert('연식이 올바르지 않습니다.');
        $('#model_year_02').focus();
        return false;
    } else if($('select[name=sel_year]').eq(0).find('option:selected').data('val') > $('select[name=sel_year]').eq(1).find('option:selected').data('val')) {
        alert('왼쪽 연식 값이 더 클 수 없습니다.');
        $('select[name=sel_year]').eq(0).focus();
        return false;
    } else if($('select[name=sel_price] option:selected').data('val') == 0) {
        alert('가격대를 선택해주세요.');
        $('select[name=sel_price]').focus();
        return false;
    }  else if($('.options-div').length == 0) {
        alert('지역을 선택해주세요.');
        $('select[name=sel_area]').focus();
        return false;
    } else if($('input[name=buy_expect_date]').val() == 0) {
        alert('구입 예정일을 선택해주세요.');
        $('select[name=buy_expect_date]').focus();
        return false;
    } 
    return true;
}

function add_find_list() {
    var html = '';
    
    var kind_seq = $('select[name=sel_kind] option:selected').data('seq');
    var mnf_name = $('select[name=sel_mnf] option:selected').data('seq');
    var model_name = $('select[name=sel_model] option:selected').data('seq');
    
    if(mnf_name) {
        mnf_name = $('select[name=sel_mnf] option:selected').text();
    } else if($('select[name=sel_mnf] option:selected').data('val') == '선택안함') {
        mnf_name = '선택안함';
    } else if($('select[name=sel_mnf] option:selected').data('mode') == 'input') {
        mnf_name = $('input[name=txt_mnf]').val();
    }
    if(model_name) {
        model_name = $('select[name=sel_model] option:selected').text();
    } else if($('select[name=sel_model] option:selected').data('mode') == 'input') {
        model_name = $('input[name=txt_model]').val();
    }

    var area_list = '';
    $.each($('.options-div'), function(index, value){
        area_list += area_list == '' ? $(this).find('.options-txt').text() : ',' + $(this).find('.options-txt').text();
    });
    
    if($('select[name=sel_year]').eq(0).find('option:selected').data('val') != 0) {
        $('#model_year_01').val($('select[name=sel_year]').eq(0).find('option:selected').data('val')); 
    }
    if($('select[name=sel_year]').eq(1).find('option:selected').data('val') != 0) {
        $('#model_year_02').val($('select[name=sel_year]').eq(1).find('option:selected').data('val')); 
    }
    var model_year = $('#model_year_01').val().replace('년', '') + '~' + $('#model_year_02').val().replace('년', '');
    var hope_price_mode = $('select[name=sel_price] option:selected').data('mode');
    var hope_price = $('select[name=sel_price] option:selected').text();
    var option = $('textarea[name=option]').val();
    var buy_expect_date = $('input[name=buy_expect_date]').val();
    
    var deliver_service = '';
    $.each($('.deliver-service-div button'), function(index, value) {
        if($(this).hasClass('btn-type-02-active')) {
            deliver_service += (deliver_service == '' ? '' : ',') + $(this).text();
        }
    });

    if(hope_price_mode == 'input') {
        hope_price = $('input[name=hope_price]').val();
    }
    html += '<div class="found-list-01">';
    html += '    <div class="flex-div" style="justify-content: center;">';
    html += '        <input type="hidden" name="kind_seq_arr[]" value="' + kind_seq + '" />';
    html += '        <input type="hidden" name="mnf_name_arr[]" value="' + mnf_name + '" />';
    html += '        <input type="hidden" name="model_name_arr[]" value="' + model_name + '" />';
    html += '        <input type="hidden" name="area_list_arr[]" value="' + area_list + '" />';
    html += '        <input type="hidden" name="model_year_arr[]" value="' + model_year + '" />';
    html += '        <input type="hidden" name="hope_price_arr[]" value="' + hope_price + '" />';
    html += '        <input type="hidden" name="option_arr[]" value="' + option + '" />';
    html += '        <input type="hidden" name="buy_expect_date_arr[]" value="' + buy_expect_date + '" />';
    html += '        <input type="hidden" name="deliver_service_arr[]" value="' + deliver_service + '" />';
    html += '        <span>기계종류: <i>' + $('select[name=sel_kind] option:selected').text() + '</i></span>';
    html += '        <span>제조사: <i>' + mnf_name + '</i></span>';
    html += '        <span>모델명: <i>' + model_name + '</i></span>';
    html += '        <span>연식: <i>' + model_year + '</i></span>';
    html += '        <span>가격범위: <i>' + hope_price + '</i></span>';
    html += '        <span>지역: <i>' + area_list + '</i></span>';
    html += '    </div>';
    html += '</div>';
    $('#find-list').append(html);
    
    $('.options-div').remove();
    find_cnt ++;
    sale_info_form_reset();
}

function find_complete() {
    var no_add = false;
    if(find_cnt == 0 && !check_find()) {
        return;
    } else if(find_cnt == 0 && check_find()) {
        find_cnt = 1;
        no_add = true;
    }
    if(confirm('총 ' + find_cnt + '개의 기계를 찾으시겠습니까 ?')) {
        if(no_add) {
            add_find_list();
            find_cnt = 1;
        }
        $('input[name=find_cnt]').val(find_cnt);
        $('#frm_find').submit();
    }
}

function go_find_sch(type, state, kind, mnf, model) {
    var c_seq = $('select[name=sch_state] option:selected').data('seq');
    var kind_seq = $('select[name=sch_kind] option:selected').data('seq');
    var mnf_name = $('select[name=sch_mnf] option:selected').data('val');
    var model_name = $('select[name=sch_model] option:selected').data('val');
    
    if(type == 'state') {
        location.href="/sch/find_sch?state=" + state_seq + "&kind=" + kind + "&mnf=" + mnf + "&model=" + model;
    } else if(type == 'kind') {
        location.href="/sch/find_sch?state=" + state + "&kind=" + kind_seq + "&mnf=" + mnf + "&model=" + model;
    } else if(type == 'mnf') {
        location.href="/sch/find_sch?state=" + state + "&kind=" + kind + "&mnf=" + mnf_name + "&model=" + model;
    } else if(type == 'model') {
        location.href="/sch/find_sch?state=" + state + "&kind=" + kind + "&mnf=" + mnf + "&model=" + momodel_namedel;
    }
}

function go_find_rec() {
    if($('.my-work-selected').length == 0) {
        alert('추천할 기계를 선택해주세요.');
        return;
    }

    var type = '';
    if($('#rec_type_01').prop('checked') == true)
        type = '01';
    else if($('#rec_type_02').prop('checked') == true)
        type = '02';
    
    if(type == '') {
        alert('추천 금액 방식을 선택해주세요.');
        return;
    }
    var price = $('#current-price').data('price');
    if(type == '01') {
        if($('#modify-price').val() == '') {
            alert('변경 금액을 입력해주세요.');
            $('#modify-price').focus();
            return;
        } else if (isNaN($('#modify-price').val()) == true) {
            alert('변경 금액은 숫자만 입력할 수 있습니다.');
            $('#modify-price').focus();
            return;
        }
        if(confirm('변경하신 금액으로 기계를 추천하시겠습니까 ?')) {
            $('input[name=price]').val($('#modify-price').val());
        }
    } else if (type == '02') {
        if(confirm('현재 판매금액으로 기계를 추천하시겠습니까 ?')) {
            $('input[name=price]').val(price);
        }
    }
    $('input[name=info_seq]').val($('.my-work-selected').data('seq'));
    $('#frm_find_rec').submit();
}

function go_proposal_process(permit_yn, prop_seq, userid, sale_userid) {
    var permit = permit_yn == 'y' ? '승인' : '거절';

    if(confirm('해당 가격제안을 ' + permit + ' 하시겠습니까 ?')) {
        $('input[name=permit_yn]').val(permit_yn);
        $('input[name=prop_seq]').val(prop_seq);
        $('input[name=userid]').val(userid);
        $('input[name=sale_userid]').val(sale_userid);
        $('#frm_proposal').submit();
    }
}

function go_proposal_counter_process(permit_yn, prop_seq, sale_userid) {
    var permit = permit_yn == 'y' ? '승인' : '거절';

    if(confirm('해당 카운터 제안을 ' + permit + ' 하시겠습니까 ?')) {
        $('input[name=counter_permit_yn]').val(permit_yn);
        $('input[name=prop_seq]').val(prop_seq);
        $('input[name=sale_userid]').val(sale_userid);
        $('#frm_proposal').submit();
    }
}

function go_visit_cancel(visit_seq, state) {
    if(state == '5') {
        alert('이미 취소하였습니다.');
        return;
    }
    if(confirm('※빈번한 현장방문의 취소는 불이익이 따를 수 있습니다.\n현장방문을 취소하시겠습니까 ?')) {
        $('#frm_visit input[name=visit_seq]').val(visit_seq);
        $('#frm_visit').attr('action', '/sch/visit_cancel_process');
        $('#frm_visit').submit();
    }
}

function go_visit_permit(visit_seq) {
    if(confirm('승인하시겠습니까 ?')) {
        $('#frm_visit input[name=visit_seq]').val(visit_seq);
        $('#frm_visit').attr('action', '/sch/visit_permit_process');
        $('#frm_visit').submit();
    }
}