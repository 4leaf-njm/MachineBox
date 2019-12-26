var machine_cnt;
var machine_remove = '';
var image;
var view_cnt = 0;
var sale_type;
var method;
var reg_mode = '';

$(document).ready(function(){
machine_cnt = 1;
sale_type = $('#sale-form').data('type');
method = $('#method-div input[name=method]').val();

/*
setTimeout(function(){
    change_mnf_list();
   
console.log('첫번째 로드 mnf');
    setTimeout(function(){
        change_model_list();
        console.log('첫번째 로드 model');
        var kind_name = '';
        var mnf_name = '';
        var model_name = '';
        if(!$('select[name=sel_kind] option:selected').data('seq') == false) {
            kind_name = $('select[name=sel_kind] option:selected').text();
        }
        if(!$('select[name=sel_mnf] option:selected').data('seq') == false) {
            mnf_name = $('select[name=sel_mnf] option:selected').text();
        }
        setTimeout(function(){
            if(!$('select[name=sel_model] option:selected').data('seq') == false) {
                model_name = $('select[name=sel_model] option:selected').text();
            }
            if(!(kind_name == '' && mnf_name == '' && model_name == '')) {
                var data = {'kind_name': kind_name, 'mnf_name': mnf_name, 'model_name': model_name};
                ajaxLowestModel(data);
            }
        console.log('첫번째 로드 최저가');
        }, 100);
    }, 100);
}, 100);
*/
var html = '';
for(var i=2020; i>=1980; i--) {
    html += '<option data-val="' + i + '">' + i + '년</option>';
}
$('select[name=sel_year]').append(html);
setTimeout(function(){
    $('select[name=sel_year] option').eq(0).prop('selected', true);
}, 1);

$('select[name=sel_year]').change(function(){
    var val = $(this).find('option:selected').data('val');
    var target = $(this).data('target');

    if(target != '') {
        var res = false;
        $.each($('select[name=sel_year]'), function(index, value) {
            if($(this).find('option:selected').data('val') == 0)
                res = true;
        });
        if(res == false)
            $('#year-div').addClass('d-none');
        else
            $('#year-div').removeClass('d-none');
        if(val == 0) {
            $('#' + target).removeClass('invisible');
            $('#' + target).focus;
        } else {
            $('#' + target).addClass('invisible');
        }
    } else {
        if(val == 0) {
            $('#year-div').removeClass('d-none');
            $('#year-div input').focus();
        } else {
            $('#year-div').addClass('d-none');
        }
    }
});

$('#sel_area_option').change(function(){
    var seq = $(this).find('option:selected').data('seq');
    var val = $(this).find('option:selected').text();
    var result = true;
    var html = '';
    if(seq == '') {
        return;
    } else {
        $('.options-div').each(function(index, value) {
            var txt = $(this).find('.options-txt').text();
            if(txt == val && val != '상관없음') {
                alert('이미 추가된 지역입니다.');
                result = false;
                return;
            }
        });
        if(val == '상관없음') {
            $('.options-div').remove();
        } else {
            $('.options-div').each(function(index, value) {
                var txt = $(this).find('.options-txt').text();
                if(txt == '상관없음') {
                    $(this).remove();
                    return;
                }
            });
        }
        if(result == true) {
            html += '<div class="options-div mr-2 mb-1">';
            html += '  <pre class="options-txt">' + val + '</pre>';
            html += '  <i class="options-delete-btn"></i>';
            html += '</div>';
        }
        $('#sel_area_option').find('option').eq(0).prop('selected', true);
        $('#option-append').append(html);
    }
});

$('#option').keydown(function(key){
    if(key.keyCode == 13) {
        var val = $('#option').val();
        
        var result = true;
        var html = '';
        
        if(val == '') {
            alert('옵션을 입력해주세요.');
            $('#option').focus();
            return;
        }
        $('input[name=\'option_name_arr[]\']').each(function(index, value) {
            var prefix = $(this).data('prefix').replace('#', '').replace('_', '');
            if(value.value == val && prefix == machine_cnt) {
                alert('이미 추가된 옵션입니다.');
                result = false;
                return;
            }
        });
        if(result == true) {
            html += '<div class="options-div mr-2 mb-1">';
            html += '  <input type="hidden" name="option_name_arr[]" data-prefix="#' + machine_cnt + '_" value="' + val + '" class="machine_option machine_' + machine_cnt + '"/>';
            html += '  <pre class="options-txt">' + val + '</pre>';
            html += '  <i class="options-delete-btn"></i>';
            html += '</div>';
        }

        $('#option').val('');
        $('#option-append').append(html);
    }
});

$('#deliver-condition-div button').on('click', function(){
    $(this).toggleClass('btn-type-02-active');
    if($(this).text() == '없음') {
        $('#deliver-condition-div button').removeClass('btn-type-02-active');
        $(this).addClass('btn-type-02-active');
    } else {
        if($(this).hasClass('btn-type-02-active')) {
            $.each($('#deliver-condition-div button'), function(index, value) {
                if($(this).text() == '없음') {
                    $(this).removeClass('btn-type-02-active');
                    return false;
                }
            });
        }
    }
});

$('#method-div button').on('click', function(){
    $('#method-div button').removeClass('btn-type-02-active');
    $(this).addClass('btn-type-02-active');
    $('input[name=method]').val($(this).text());
    method = $(this).text();
});

$('.preview-div').on('click', function(){
    $('.preview-div').removeClass('preview-div-active');
    $(this).addClass('preview-div-active');
});

$('.btn-picture-add').on('click', function(){
    var has = false;
    var active_id;
    var $this = $(this);
    setTimeout(function(){
        $.each($('.preview-div'), function(index, value) {
            if($(this).hasClass('preview-div-active')) {
                has = true;
                active_id = $(this).find('input[name=\'machine_picture_' + machine_cnt + '[]\']').attr('id');
                return;
            }
        });
        if(has == false) {
            alert('추가할 위치를 지정해주세요.');
            return false;
        }
        $this.attr('for', active_id);
    }, 1);
});

$('#btn-machine-add').on('click', function(){
    if(sale_info_form_check() == false) {
        return;
    }
    if(!confirm('위의 정보로 기계를 추가하시겠습니까 ?')) {
        return;
    }
    if($('select[name=sel_year] option:selected').data('val') != 0) {
        $('input[name=model_year]').val($('select[name=sel_year] option:selected').data('val')); 
    }
    $('input[name=model_year]').val($('input[name=model_year]').val().replace('년', ''));
    var size = $('input[name=size_01]').val() + ' x ' + $('input[name=size_02]').val() + ' x ' + $('input[name=size_03]').val() + ' m'
    $('input[name=size]').val(size);
    $('input[name=weight]').val($('input[name=weight]').val() + ' kg');

    var val = '';
    $.each($('#deliver-condition-div button'), function(index, value) {
        if($(this).hasClass('btn-type-02-active')) {
            val += (val == '' ? '' : ',') + $(this).text();
        }
    });
    $('input[name=deliver_condition]').val(val);

    $('input[name=hope_price]').val(parseInt($('input[name=hope_price]').val().replaceAll(',', '') + '0000'));

    var txt_mnf = $('#input-mnf');
    var txt_model = $('#input-model');
    var input_mnf = 'false';
    var input_model = 'false';
    if(!txt_mnf.hasClass('d-none'))
        input_mnf = 'true';
    if(!txt_model.hasClass('d-none'))
        input_model = 'true';
    var kind = $('select[name=sel_kind] option:selected').text();
    var model = $('select[name=sel_model] option:selected').text();
    var mnf = $('select[name=sel_mnf] option:selected').text();
    if(input_model == 'true')
        model = txt_model.val();
    if(input_mnf == 'true')
        mnf = txt_mnf.val();
    
    var html = '';
    html += '<div class="scroll-div machine-item" id="machine_' + machine_cnt + '" data-no="' + machine_cnt + '">';
    html += '    <input type="hidden" name="kind_no_arr[]" value="' + $('select[name=sel_kind] option:selected').parent('optgroup').data('no') + '" />';
    html += '    <input type="hidden" name="kind_seq_arr[]" value="' + $('select[name=sel_kind] option:selected').data('seq') + '" />';
    html += '    <input type="hidden" name="mnf_seq_arr[]" value="' + $('select[name=sel_mnf] option:selected').data('seq') + '" />';
    html += '    <input type="hidden" name="input_mnf_arr[]" value="' + input_mnf + '" />';
    html += '    <input type="hidden" name="txt_mnf_arr[]" value="' + txt_mnf.val() + '" />';
    html += '    <input type="hidden" name="model_seq_arr[]" value="' + $('select[name=sel_model] option:selected').data('seq') + '" />';
    html += '    <input type="hidden" name="input_model_arr[]" value="' + input_model + '" />';
    html += '    <input type="hidden" name="txt_model_arr[]" value="' + txt_model.val() + '" />';
    html += '    <input type="hidden" name="area_seq_arr[]" value="' + $('select[name=sel_area] option:selected').data('seq') + '" />';
    html += '    <input type="hidden" name="model_year_arr[]" value="' + $('input[name=model_year]').val().replace('년', '') + '" />';
    html += '    <input type="hidden" name="serial_num_arr[]" value="' + $('input[name=serial_num]').val() + '" />';
    html += '    <input type="hidden" name="size_arr[]" value="' + $('input[name=size]').val() + '" />';
    html += '    <input type="hidden" name="weight_arr[]" value="' + $('input[name=weight]').val() + '" />';
    html += '    <input type="hidden" name="controller_arr[]" value="' + $('input[name=controller]').val() + '" />';
    html += '    <input type="hidden" name="hope_price_arr[]" value="' + $('input[name=hope_price]').val() + '" />';
    html += '    <input type="hidden" name="deliver_condition_arr[]" value="' + $('input[name=deliver_condition]').val() + '" />';
    html += '    <input type="hidden" name="option_index_arr[]" value="' + machine_cnt + '" />';
    html += '   <i class="product-delete-btn"></i>';
    html += '   <img style="width: 100%; height: 100%; background-repeat: no-repeat; background-size: 100% 100%; background-position: center; min-height: 230px;" class="machine-image">';
    html += '   <div class="scroll-desc">';
html += '       <h4>' + kind + '<span>' + model + '</span>' + mnf + '</h4>';
    html += '       <span>' + $('input[name=model_year]').val().replace("년", "") + '년형</span>';
    html += '      <span>' + current_date() + '</span>';
    html += '      <span>' + $('select[name=sel_area] option:selected').text() + '</span>';
    html += '       <p><span class="accent-txt">' + comma($('input[name=hope_price]').val()) + '</span>원</p>';
    html += '   </div>';
    html += '</div>';

    $('#machine-add-list').append(html);
    $('#machine_' + machine_cnt).find('.machine-image').css('background-image', 'url(\"' + image + '\")');
    machine_cnt ++;
    
    html = '';

    //<![CDATA[
    for(var i=0;i<8;i++) {
        var idx = $('.preview-div').eq(i).index();

        if(idx == i) {
            html = '<input type="file" name="machine_picture_' + machine_cnt + '[]" class="input-file" id="picture-' + machine_cnt + '-' + (i+1) + '">';
            $('.preview-div').eq(i).append(html);
        }
    }
    //]]>
    //$('.scroll-wrap.scroll-x').mCustomScrollbar("update");
    sale_info_form_reset();
});

$(document).on('click', '#btn-complete', function(){
    if($(this).text() == '변경' || $(this).text() == '재등록하기') {
        if(sale_info_form_check('modify') == false) {
            return;
        }

        if(sale_type == 'self') {
            if(!sale_info_form_check2()) {
                return;
            }
            var total_price = 0;

            $.each($('input[name="chk_ad"]'), function(index, value) {
                if($(this).is(':checked')) {
                    var name = $(this).data('name');
                    var price = $(this).data('price');

                    if(name == '없음') {} else {
                        total_price += price;
                    }
                }
            });

            if($('#offLine').is(':checked')) 
                total_price += 150000;

            if($('input[name=online_eval_yn]').val() == 'y') {
                var price = $('input[name=chk_option]:checked').data('price');
                total_price += price;
            }

            var message = '';
            var msg = '';
            if($(this).text() == '변경') 
                msg = '위의 내용으로 기계정보를 변경하시겠습니까 ?';
            else
                msg = '위의 내용으로 기계를 재등록하시겠습니까 ?';

            if($(this).data('type') == 'x') {
                message = msg;
            } else if($(this).data('type') == 'y') {
                var diff_price = total_price - $('input[name=prev_price]').val();
                if(diff_price > 0) {
                    message = msg + "\n서비스를 변경하여 차액 " + comma(diff_price) + "원에 대한 비용이 문자로 청구됩니다.";
                } else {
                    message = msg;
                }
            }
            if(confirm(message)) {
                $('input[name=total_price]').val(total_price);

                self_complete();
            }
            return false;
        } else {
            if(confirm('위의 내용으로 판매정보를 변경하시겠습니까 ?')) {
                if(sale_type == 'emergency') {
                    $('#sale-form').attr('action', '/sale/emergency_process');
                } else if (sale_type == 'direct') {
                    $('#sale-form').attr('action', '/sale/direct_process');
                }
    
                var txt_mnf = $('#input-mnf');
                var txt_model = $('#input-model');
                var input_mnf = 'false';
                var input_model = 'false';
                if(!txt_mnf.hasClass('d-none'))
                    input_mnf = 'true';
                if(!txt_model.hasClass('d-none'))
                    input_model = 'true';
    
                $('input[name=input_mnf]').val(input_mnf);
                $('input[name=input_model]').val(input_model);
    
                $('input[name=kind_no]').val($('select[name=sel_kind] option:selected').parent('optgroup').data('no')); 
                $('input[name=kind_seq]').val($('select[name=sel_kind] option:selected').data('seq')); 
                $('input[name=mnf_seq]').val($('select[name=sel_mnf] option:selected').data('seq')); 
                $('input[name=model_seq]').val($('select[name=sel_model] option:selected').data('seq')); 
                $('input[name=area_seq]').val($('select[name=sel_area] option:selected').data('seq'));
                if($('select[name=sel_year] option:selected').data('val') != 0) {
                    $('input[name=model_year]').val($('select[name=sel_year] option:selected').data('val')); 
                }
                $('input[name=model_year]').val($('input[name=model_year]').val().replace('년', ''));
                var size = $('input[name=size_01]').val() + ' x ' + $('input[name=size_02]').val() + ' x ' + $('input[name=size_03]').val() + ' m'
                $('input[name=size]').val(size);
                $('input[name=weight]').val($('input[name=weight]').val() + ' kg');
            
                var val = '';
                $.each($('#deliver-condition-div button'), function(index, value) {
                    if($(this).hasClass('btn-type-02-active')) {
                        val += (val == '' ? '' : ',') + $(this).text();
                    }
                });
                $('input[name=deliver_condition]').val(val);
            
                var hope_price = $('input[name=hope_price]').val();
                if(hope_price) {
                    $('input[name=hope_price]').val(parseInt(hope_price.replaceAll(',', '') + '0000'));
                }
                $('#sale-form').submit();
            }
        }
    } else if($(this).text() == '신청') {
        if(confirm('위의 내용으로 유료서비스를 신청하시겠습니까 ?\n서비스 신청 시 차액에 대한 비용이 문자로 청구됩니다.')) {
            var total_price = 0;

            $.each($('input[name="chk_ad"]'), function(index, value) {
                if($(this).is(':checked')) {
                    var name = $(this).data('name');
                    var price = $(this).data('price');
                    if(name == '없음') {} else {
                        total_price += price;
                    }
                }
            });
            if($('#offLine').is(':checked')) 
                total_price += 150000;
            
            if($('input[name=online_eval_yn]').val() == 'y') {
                var price = $('input[name=chk_option]:checked').data('price');
                total_price += price;
            }
            $('input[name=total_price]').val(total_price);

            $.each($('input[name=chk_ad]'), function(index, value) {
                if($(this).data('name') == '없음')
                    return;
                else {
                    if($(this).prop('checked') == false) {
                        $(this).parent('div').find('input[type=hidden]').remove();
                    }
                }
            });

            $('#frm_service').submit();
        }
        return false;
    } else {
        if($('.machine-item').size() == 0) {
            alert('추가된 기계가 없습니다. 기계를 등록해주세요.');
            return;
        }
    
        if(!confirm('총 ' + $('.machine-item').size() + ' 대의 기계를 판매하시겠습니까 ? \n(위에 작성하신 내용은 사라집니다.)')) {
            return;
        }
    
        $('.step-wrap').removeClass('d-block');
        $('#owner-check').addClass('d-block');
    }
});

$('.btn-regist-complete').on('click', function() {
    if($('#check-01-div .btn-type-02-active').length == 0) {
        alert('질문 1에 대한 답변을 해주세요.');
        return;
    } else if($('#check-01-div .btn-type-02-active').text() == '있음' && $('input[name=check_01_det]').val() == '') {
        alert('질문 1에 대한 답변을 해주세요.');
        $('input[name=check_01_det]').focus();
        return;
    } else if($('#check-02-div .btn-type-02-active').length == 0) {
        alert('질문 2에 대한 답변을 해주세요.');
        return;
    } else if($('#check-02-div .btn-type-02-active').text() == '있음' && $('input[name=check_02_det]').val() == '') {
        alert('질문 2에 대한 답변을 해주세요.');
        $('input[name=check_02_det]').focus();
        return;
    } else if($('#check-03-div .btn-type-02-active').length == 0) {
        alert('질문 3에 대한 답변을 해주세요.');
        return;
    } else if($('#check-04-div .btn-type-02-active').length == 0) {
        alert('질문 4에 대한 답변을 해주세요.');
        return;
    }

    if(!confirm('등록을 완료하시겠습니까 ?')) {
        return;
    }
    if(sale_type == 'emergency') {
        $('#sale-form').attr('action', '/sale/emergency_process');
    } else if (sale_type == 'direct') {
        $('#sale-form').attr('action', '/sale/direct_process');
    }
    
    var html = '';
    html += '<input type="hidden" name="machine_cnt" value="' + (machine_cnt-1) + '" />';
    html += '<input type="hidden" name="machine_remove" value="' + machine_remove + '" />';
    $(this).parent().append(html);
    $.each($('.machine_option'), function(index, value) {
        var prefix = $(this).data('prefix');
        var val = $(this).val();
        $(this).val(prefix + val);
    });

    var txt_mnf = $('#input-mnf');
    var txt_model = $('#input-model');
    var input_mnf = 'false';
    var input_model = 'false';
    if(!txt_mnf.hasClass('d-none'))
        input_mnf = 'true';
    if(!txt_model.hasClass('d-none'))
        input_model = 'true';

    $('input[name=input_mnf]').val(input_mnf);
    $('input[name=input_model]').val(input_model);

    $('input[name=check_01_res]').val($('#check-01-div button.btn-type-02-active').text());
    $('input[name=check_02_res]').val($('#check-02-div button.btn-type-02-active').text());
    $('input[name=check_03_res]').val($('#check-03-div button.btn-type-02-active').text());
    $('input[name=check_04_res]').val($('#check-04-div button.btn-type-02-active').text());
    $('#sale-form').submit();
});

$('.bid-duration-div button').on('click', function(){
    $('.bid-duration-div button').removeClass('btn-type-02-active');
    $(this).addClass('btn-type-02-active');
    $('input[name=bid_duration]').val($(this).data('value'));
});

$('.reduction-rate-div button').on('click', function(){
    $('.reduction-rate-div button').removeClass('btn-type-02-active');
    $(this).addClass('btn-type-02-active');
    var value = $(this).data('value');
    if(value == -1) {
        $('#setReductionRate').removeClass('d-none');
    } else {
        $('#setReductionRate input').val('');
        $('#setReductionRate').addClass('d-none');
        $('input[name=reduction_rate]').val(value);
    }
});

$('.repeat-no-div button').on('click', function(){
    $('.repeat-no-div button').removeClass('btn-type-02-active');
    $(this).addClass('btn-type-02-active');
    var value = $(this).data('value');
    if(value == -1) {
        $('#setRepeatNo').removeClass('d-none');
    } else {
        $('#setRepeatNo input').val('');
        $('#setRepeatNo').addClass('d-none');
        $('input[name=repeat_no]').val(value);
    }
});

$('.self-deliver-condition-div button').on('click', function(){
    $(this).toggleClass('btn-type-02-active');
    if($(this).text() == '없음') {
        $('.self-deliver-condition-div button').removeClass('btn-type-02-active');
        $(this).addClass('btn-type-02-active');
    } else {
        if($(this).hasClass('btn-type-02-active')) {
            $.each($('.self-deliver-condition-div button'), function(index, value) {
                if($(this).text() == '없음') {
                    $(this).removeClass('btn-type-02-active');
                    return false;
                }
            });
        }
    }
});

$('.deliver-service-div button').on('click', function(){
    $(this).toggleClass('btn-type-02-active');
    if($(this).text() == '신청안함') {
        $('.deliver-service-div button').removeClass('btn-type-02-active');
        $(this).addClass('btn-type-02-active');
    } else {
        if($(this).hasClass('btn-type-02-active')) {
            $.each($('.deliver-service-div button'), function(index, value) {
                if($(this).text() == '신청안함') {
                    $(this).removeClass('btn-type-02-active');
                    return false;
                }
            });
        }
    }
});

$('#step-04 .check-div button').on('click', function() {
    $(this).parent('div').find('button').removeClass('btn-type-02-active');
    $(this).addClass('btn-type-02-active');
});

$('.price-proposal-div button').on('click', function(){
    $('.price-proposal-div button').removeClass('btn-type-02-active');
    $(this).addClass('btn-type-02-active');
    $('input[name=price_proposal]').val($(this).data('value'));
});

$('input[name=chk_ad]').on('change', function(){
    var name = $(this).data('name');
    
    if(name == '없음') {
        $('input[name=chk_ad]').prop('checked', false);
        $(this).prop('checked', true);
        $('.ad-img-lg').attr('src', '');
    } else {
        $('input[name=chk_ad]').eq(0).prop('checked', false);
    
        if($(this).prop('checked') == true) {
            $('.ad-img-lg').attr('intrinsicsize', "468 x 634");
            if(name == '핫마크') {
                $('.ad-img-lg').attr('src', '/data/skin/responsive_ver1_default_gl/images/custom/img/hotmark.png');
            } else if (name == '자동 업데이트') {
                $('.ad-img-lg').attr('src', '/data/skin/responsive_ver1_default_gl/images/custom/img/update.png');
            } else if (name == '하이라이트') {
                $('.ad-img-lg').attr('src', '/data/skin/responsive_ver1_default_gl/images/custom/img/highlight2.png');
            } else if (name == '딜러존') {
                $('.ad-img-lg').attr('src', '/data/skin/responsive_ver1_default_gl/images/custom/img/dealer.png');
            }
        }
    }

    if($('input[name=chk_ad]:checked').length == 0) {
        $('input[name=chk_ad]').eq(0).prop('checked', true);
    }
    update_price();
});

$('#offLine').on('change', function(){
    var value = ($(this).is(':checked')) == true ? 'y' : 'n';
    $('input[name=perform_check_yn]').val(value);
    update_price();
});

$('input[name=chk_option]').on('change', function(){
    var eval_yn = 'n';
    var eval_option = '';
    var id = $(this).attr('id');
    
    alert('서비스 준비중입니다.');
    $(this).prop('checked', false);
    return;
    if(id == 'onLine3') {
        $('#onLine5').prop('checked', false);
        if($(this).prop('checked') == true) {
            eval_yn = 'y';
            eval_option = $(this).val();
        }
    } else if(id == 'onLine5') {
        $('#onLine3').prop('checked', false);
        if($(this).prop('checked') == true) {
            eval_yn = 'y';
            eval_option = $(this).val();
        }
    }
    $('input[name=online_eval_yn]').val(eval_yn);
    $('input[name=online_eval_option').val(eval_option);
    update_price();
});

$('.btn-self-complete').on('click', function(){
    self_complete();
});

$('select[name=sel_kind]').on('change', function(){
    $('select[name=sel_mnf] option').eq(0).prop('selected', true);
	$('select[name=sel_model] option').eq(0).prop('selected', true);
	$('input[name=txt_mnf]').addClass('d-none');
	$('input[name=txt_model]').addClass('d-none');
    change_mnf_list();
    change_model_list();
    var kind_name = '';
    var mnf_name = '';
    var model_name = '';
    if(!$('select[name=sel_kind] option:selected').data('seq') == false) {
        kind_name = $('select[name=sel_kind] option:selected').text();
    }
    if(!$('select[name=sel_mnf] option:selected').data('seq') == false) {
        mnf_name = $('select[name=sel_mnf] option:selected').text();
    }
    if(!$('select[name=sel_model] option:selected').data('seq') == false) {
        model_name = $('select[name=sel_model] option:selected').text();
    }
    var data = {'kind_name': kind_name, 'mnf_name': mnf_name, 'model_name': model_name};
    if(!(kind_name == '' && mnf_name == '' && model_name == '')) 
        ajaxLowestModel(data);
    else 
        ajaxLowestModel(data, 'y');
});

$('select[name=sel_mnf]').on('change', function(){
    $('select[name=sel_model] option').eq(0).prop('selected', true);
    $('select[name=sel_model] option').eq(0).prop('selected', true);
    $('input[name=txt_model]').addClass('d-none');
    change_model_list();
    var kind_name = '';
    var mnf_name = '';
    var model_name = '';
    if(!$('select[name=sel_kind] option:selected').data('seq') == false) {
        kind_name = $('select[name=sel_kind] option:selected').text();
    }
    if(!$('select[name=sel_mnf] option:selected').data('seq') == false) {
        mnf_name = $('select[name=sel_mnf] option:selected').text();
    }
    if(!$('select[name=sel_model] option:selected').data('seq') == false) {
        model_name = $('select[name=sel_model] option:selected').text();
    }
    var data = {'kind_name': kind_name, 'mnf_name': mnf_name, 'model_name': model_name};
    if(!(kind_name == '' && mnf_name == '' && model_name == '')) 
        ajaxLowestModel(data);
    else 
        ajaxLowestModel(data, 'y');
});

$('select[name=sel_model]').on('change', function(){
    var kind_name = '';
    var mnf_name = '';
    var model_name = '';
    if(!$('select[name=sel_kind] option:selected').data('seq') == false) {
        kind_name = $('select[name=sel_kind] option:selected').text();
    }
    if(!$('select[name=sel_mnf] option:selected').data('seq') == false) {
        mnf_name = $('select[name=sel_mnf] option:selected').text();
    }
    if(!$('select[name=sel_model] option:selected').data('seq') == false) {
        model_name = $('select[name=sel_model] option:selected').text();
    }
    var data = {'kind_name': kind_name, 'mnf_name': mnf_name, 'model_name': model_name};
    if(!(kind_name == '' && mnf_name == '' && model_name == '')) 
        ajaxLowestModel(data);
    else 
        ajaxLowestModel(data, 'y');
});

$('input[name=txt_mnf]').on('focusout', function() {
	var mnf_name = $(this).val();
    var kind_name = $('select[name=sel_kind] option:selected').text();
	$.ajax({
      type: 'post',
      url: '/sale/get_mnf_one',
      dataType: 'json',
      data: {'kind_name': kind_name, 'mnf_name': mnf_name},
      success: function(data) {
          if(data.result == true) {
        	   alert('제조사 [' + mnf_name + ']은(는) 이미 등록되어 있습니다.\n등록된 데이터로 변경시키겠습니다.');
        	   $.each($('select[name=sel_kind] option'), function(index, value) {
        		   	 if($(this).text() == data.item.mnf_kind) {
        		   		 $(this).prop('selected', true);
        		   		 return;
        		   	 }
        	   });
        	   setTimeout(function(){
	        	   change_mnf_list();
	        	   setTimeout(function(){
		        	   $.each($('select[name=sel_mnf] option'), function(index, value) {
		      		   	 if($(this).text() == data.item.mnf_name) {
		      		   		 $(this).prop('selected', true);
		      		   		 return;
		      		   	 }
		      	     });
		        	   $('input[name=txt_mnf]').addClass('d-none');
                       change_model_list();
	        	   }, 100);
        	   }, 100);
          }
      },
      error: function() {
          console.log('error');
      }
  });
    var kind_name = '';
    var model_name = '';
    if(!$('select[name=sel_kind] option:selected').data('seq') == false) {
        kind_name = $('select[name=sel_kind] option:selected').text();
    }
    if(!$('select[name=sel_model] option:selected').data('seq') == false) {
        model_name = $('select[name=sel_model] option:selected').text();
    }
    var data = {'kind_name': kind_name, 'mnf_name': mnf_name, 'model_name': model_name};
    if(!(kind_name == '' && mnf_name == '' && model_name == '')) 
        ajaxLowestModel(data);
    else 
        ajaxLowestModel(data, 'y');
});
$('input[name=txt_model]').on('focusout', function() {
    var mnf_name = $('select[name=sel_mnf] option:selected').text();
	var model_name = $(this).val();
	$.ajax({
      type: 'post',
      url: '/sale/get_model_one',
      dataType: 'json',
      data: {'mnf_name': mnf_name, 'model_name': model_name},
      success: function(data) {
          if(data.result == true) {
        	   alert('모델명 [' + model_name + ']은(는) 이미 등록되어 있습니다.\n등록된 데이터로 변경시키겠습니다.');
        	   $.each($('select[name=sel_kind] option'), function(index, value) {
        		   	 if($(this).text() == data.item.model_kind) {
        		   		 $(this).prop('selected', true);
        		   		 return;
        		   	 }
        	   });
        	   setTimeout(function(){
	        	   change_mnf_list();
	        	   setTimeout(function(){
	        		   $.each($('select[name=sel_mnf] option'), function(index, value) {
           		   	 if($(this).text() == data.item.model_mnf) {
           		   		 $(this).prop('selected', true);
           		   		 return;
           		   	 }
           	     });
	        		   $('input[name=txt_mnf]').addClass('d-none');
	        		   setTimeout(function(){
		        		   change_model_list();
		        		   setTimeout(function(){
		            	   $.each($('select[name=sel_model] option'), function(index, value) {
		           		   	 if($(this).text() == data.item.model_name) {
		           		   		 $(this).prop('selected', true);
		           		   		 return;
		           		   	 }
		           	     });
		            	   $('input[name=txt_model]').addClass('d-none');
		        		   }, 100);
	        		   }, 100);
	        	   }, 100);
        	   }, 100);
          }
      },
      error: function() {
          console.log('error');
      }
  });
    var kind_name = '';
    var mnf_name = '';
    if(!$('select[name=sel_kind] option:selected').data('seq') == false) {
        kind_name = $('select[name=sel_kind] option:selected').text();
    }
    if(!$('select[name=sel_mnf] option:selected').data('seq') == false) {
        mnf_name = $('select[name=sel_mnf] option:selected').text();
    }
    var data = {'kind_name': kind_name, 'mnf_name': mnf_name, 'model_name': model_name};
    if(!(kind_name == '' && mnf_name == '' && model_name == '')) 
        ajaxLowestModel(data);
    else 
        ajaxLowestModel(data, 'y');
    
});

$('.check-div button').on('click', function(){
    $(this).parent('.check-div').find('button').removeClass('btn-type-02-active');
    $(this).addClass('btn-type-02-active');
});

// end
});
function change_mnf_list() {
    var kind_name = $('select[name=sel_kind] option:selected').val();

    $.ajax({
        type: 'post',
        url: '/sale/get_mnf_list',
        dataType: 'json',
        data: {'kind_name': kind_name},
        success: function(data) {
            var html = '';
            html += '<option>제조사를 선택해주세요.</option>';
            if($('select[name=sel_mnf]').data('id'))
                html += '<option data-mode="input">직접입력</option>';
            if($('select[name=sel_mnf]').data('none'))
                html += '<option data-val="선택안함">선택안함</option>';
            $.each(data.mnf_list, function(index, value) {
                html += '<option data-seq="' + value.mnf_seq + '">' + value.mnf_name + '</option>';
            });
            $('select[name=sel_mnf]').html(html);
        },
        error: function() {
            console.log('error');
        }
    });
}

function change_model_list() {
    var kind_name = $('select[name=sel_kind] option:selected').val();
    var mnf_name = $('select[name=sel_mnf] option:selected').val();

    $.ajax({
        type: 'post',
        url: '/sale/get_model_list',
        dataType: 'json',
        data: {'kind_name': kind_name, 'mnf_name': mnf_name},
        success: function(data) {
            var html = '';
            html += '<option>모델을 선택해주세요.</option>';
            if($('select[name=sel_model]').data('id'))
                html += '<option data-mode="input">직접입력</option>';
            $.each(data.model_list, function(index, value) {
                html += '<option data-seq="' + value.model_seq + '">' + value.model_name + '</option>';
            });
            $('select[name=sel_model]').html(html);
        },
        error: function() {
            console.log('error');
        }
    });
}

function ajaxLowestModel(data, reset) {
    $.ajax({
        type: 'post',
        url : "/sale/getLowestModel",
        data: data,
        dataType:"json",
        success : function(data) {
            var self_fix = data.result3;
            var self_bid = data.result4;
            var direct = data.result1;
            var emerge = data.result2;

            var fix_price = self_fix == null ? -1 : self_fix.fixed_price;
            var bid_price = self_bid == null ? -1 : self_bid.bid_price;
            var direct_price = direct == null ? -1 : direct.real_price;
            var emerge_price = emerge == null ? -1 : emerge.real_price;

            var min_price_arr = [];
            var index = 0;
            if(fix_price != -1) min_price_arr[index++] = fix_price;
            if(bid_price != -1) min_price_arr[index++] = bid_price;
            if(direct_price != -1) min_price_arr[index++] = direct_price;
            if(emerge_price != -1) min_price_arr[index++] = emerge_price;

            var min_data_arr = [];
            var index = 0;
            if(self_fix != null) min_data_arr[index++] = self_fix;
            if(self_bid != null) min_data_arr[index++] = self_bid;
            if(direct != null) min_data_arr[index++] = direct;
            if(emerge != null) min_data_arr[index++] = emerge;
    
            var min;
            var min_data;
            var isModel = 'n';

            if(min_price_arr == undefined || min_price_arr.length == 0) 
                isModel = 'n';
            else if (min_price_arr.length == 1) {
                isModel = 'y';
                min = min_price_arr[0];
                min_data = min_data_arr[0];
      
            } else {
                isModel = 'y';
                min = min_price_arr.reduce(function(previous, current, index) {
                    min_data = min_data_arr[index];
                    return previous > current ? current : previous;
                });
            }
            
            if(isModel == 'n' || reset == 'y') {
                $('.product-img-div .product-img-lg').attr('src', $('#skin-path').text() + '/images/custom/common/no-image.png');
                $('.product-img-div .desc-kind').text('없음');
                $('.product-img-div .desc-mnf').text('없음');
                $('.product-img-div .desc-model').text('없음');
                $('.product-img-div .desc-year').text('없음');
                $('.product-img-div .desc-price').text('0 원');
                $('.product-img-div .desc-message').text('해당 모델은 판매자님이 현재 처음 판매자입니다.');
                var html = $('.product-img-div').html();
                $('.product-img-div').html(html);
            } else {
                $('.product-img-div .product-img-lg').attr('src', min_data.path);
                $('.product-img-div .desc-kind').text(min_data.kind_name);
                $('.product-img-div .desc-mnf').text(min_data.mnf_name);
                $('.product-img-div .desc-model').text(min_data.model_name);
                $('.product-img-div .desc-year').text(min_data.model_year.replace('년', '') + '년형');
                $('.product-img-div .desc-price').text(price_format(min) + '원');
                $('.product-img-div .desc-message').text('해당 모델은 판매된 적이 있는 모델입니다.');
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(jqXHR.responseText);
        }
    });
}

$(document).on('click', '.options-delete-btn', function(){
    $(this).parent('.options-div').remove();
});

$(document).on('click', '.preview-delete-btn', function(){
    $(this).parents('.btn-picture-add').attr('for', '');

    $(this).removeClass('preview-delete-btn-active');
    $(this).parent('.preview-back').css('background-image', '');
    $('.preview-div-active .upload_yn').text('n');

    var idx = $(this).parents('.preview-div').index();

    $('#picture-' + machine_cnt + '-' + (idx+1)).remove();
    var html = '';
    html += '<input type="file" name="machine_picture_' + machine_cnt + '[]" class="input-file" id="picture-' + machine_cnt + '-' + (idx+1) + '">';
    $('.preview-div').eq(idx).append(html);
});

$(document).on('click', '.product-delete-btn', function(){
    var id = $(this).parent('.machine-item').attr('id');
    var no = $(this).parent('.machine-item').data('no');
    $('input.' + id).remove();
    $(this).parent('.machine-item').remove();
    machine_remove += no + ' ';
    view_cnt --;
    machine_add_scroll(view_cnt);
});

$(document).on('focus', 'select, input, textarea', function(){
    $('select, input, textarea').removeClass('select-active');
    $(this).addClass('select-active');
});

$(document).on('change', '.preview-div .input-file', function(e){
    var files = e.target.files;
    var filesArr = Array.prototype.slice.call(files);

    var reader = new FileReader();
    var preview = $('.preview-div-active .preview-back');

    var id = $(this).attr('id').split('-')[2];

    filesArr.forEach(function(f) {
        if(!f.type.match('image.*')) {
            alert('이미지 파일이 아닙니다.');
            return;
        }

        reader.onload = function(e) {
           preview.css('background-image', 'url(\"' + e.target.result + '\")');
           $('.preview-div-active .preview-delete-btn').addClass('preview-delete-btn-active');
           $('.preview-div-active .upload_yn').text('y');
            if(id == '2') {
                image = e.target.result;
            }
        }
       reader.readAsDataURL(f);
    });
});

//<![CDATA[
function sale_info_form_check(mode) {
    if(!$('select[name=sel_kind] option:selected').data('seq')) {
        alert('기계 종류를 선택해주세요.');
        $('select[name=sel_kind]').focus();
        return false;
    } else if(!$('select[name=sel_mnf] option:selected').data('seq') && $('#input-mnf').hasClass('d-none')) {
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
    } else if(!$('select[name=sel_area] option:selected').data('seq')) {
        alert('지역을 선택해주세요.');
        $('select[name=sel_area]').focus();
        return false;
    } else if($('select[name=sel_year] option:selected').data('val') == undefined) {
        alert('연식을 선택해주세요.');
        $('select[name=sel_year]').focus();
        return false;
    } else if($('select[name=sel_year] option:selected').data('val') == 0 && $('input[name=model_year]').val() == '') {
        alert('연식을 입력해주세요.');
        $('input[name=model_year]').focus();
        return false;
    } else if($('select[name=sel_year] option:selected').data('val') == 0 && $('input[name=model_year]').val().length < 4 || $('input[name=model_year]').val().length > 5) {
        alert('연식이 올바르지 않습니다.');
        $('input[name=model_year]').focus();
        return false;
    } else if($('select[name=sel_year] option:selected').data('val') == 0 && $('input[name=model_year]').val().length == 4 && isNaN($('input[name=model_year]').val()) == true) {
        alert('연식이 올바르지 않습니다.');
        $('input[name=model_year]').focus();
        return false;
    } else if($('input[name=serial_num]').val() == '' && sale_type != 'turnkey') {
        alert('시리얼 넘버를 입력해주세요.');
        $('input[name=serial_num]').focus();
        return false;
    } else if($('input[name=size_01]').val() == '' && sale_type != 'turnkey') {
        alert('기계크기를 입력해주세요.');
        $('input[name=size_01]').focus();
        return false;
    } else if(isNaN($('input[name=size_01]').val()) == true && sale_type != 'turnkey') {
        alert('기계크기가 올바르지 않습니다.');
        $('input[name=size_01]').focus();
        return false;
    } else if($('input[name=size_02]').val() == '' && sale_type != 'turnkey') {
        alert('기계크기를 입력해주세요.');
        $('input[name=size_02]').focus();
        return false;
    } else if(isNaN($('input[name=size_02]').val()) == true && sale_type != 'turnkey') {
        alert('기계크기가 올바르지 않습니다.');
        $('input[name=size_02]').focus();
        return false;
    } else if($('input[name=size_03]').val() == '' && sale_type != 'turnkey') {
        alert('기계크기를 입력해주세요.');
        $('input[name=size_03]').focus();
        return false;
    } else if(isNaN($('input[name=size_03]').val()) == true && sale_type != 'turnkey') {
        alert('기계크기가 올바르지 않습니다.');
        $('input[name=size_03]').focus();
        return false;
    } else if($('input[name=weight]').val() == '' && sale_type != 'turnkey') {
        alert('기계중량을 입력해주세요.');
        $('input[name=weight]').focus();
        return false;
    } else if(isNaN($('input[name=weight]').val()) == true && sale_type != 'turnkey') {
        alert('기계중량이 올바르지 않습니다.');
        $('input[name=weight]').focus();
        return false;
    } 

    if(reg_mode != 'modify') {
        var result = true;
        if(sale_type != 'turnkey') {
            $.each($('.preview-div'), function(index, value) {
                if($(this).find('.upload_yn').text() != 'y' && index < 7) {
                    alert($(this).find('.preview-txt').text() + ' 사진을 등록해주세요.');
                    $('.preview-div').removeClass('preview-div-active');
                    $(this).addClass('preview-div-active');
                    $(this).find('input').focus();
                    result = false;
                    return false;
                } else if($(this).find('.upload_yn').text() != 'y' && index == 7) {
                    $('.preview-div').eq(index).find('input[type=file]').attr('name', '');
                } else if($(this).find('.upload_yn').text() == 'y' && index == 7) {
                    $('.preview-div').eq(index).find('input[type=file]').attr('name', 'machine_picture_' + machine_cnt + '[]');
                }
            });
        }
        if(result == false) {
            return false;
        }
    }

    if($('input[name=controller]').val() == '' && sale_type != 'turnkey') {
        alert('컨트롤러를 입력해주세요.');
        $('input[name=controller]').focus();
        return false;
    } else if($('input[name=hope_price]').val() == '' && sale_type != 'turnkey' && sale_type != 'self') {
        alert('판매 희망 금액을 입력해주세요.');
        $('input[name=hope_price]').focus();
        return false;
    } else if(isNaN($('input[name=hope_price]').val()) == true && sale_type != 'turnkey' && sale_type != 'self') {
        alert('금액은 숫자만 입력할 수 있습니다.');
        $('input[name=hope_price]').focus();
        return false;
    } else if($('input[name=method]').val() == '' && sale_type == 'self') {
        alert('판매 방법을 선택해주세요.');
        $('input[name=method]').focus();
        return false;
    } else if($('input[name=pur_price]').val() == '' && sale_type == 'turnkey') {
        alert('매입가를 입력해주세요.');
        $('input[name=pur_price]').focus();
        return false;
    } else if(isNaN($('input[name=pur_price]').val()) == true && sale_type == 'turnkey') {
        alert('매입가는 숫자만 입력할 수 있습니다.');
        $('input[name=pur_price]').focus();
        return false;
    } 
    return true;
}
function sale_info_form_check2() {
    if(method == '고정가격판매') {
        if($('input[name=fixed_price]').val() == '') {
            alert('고정가격 판매 금액을 입력해주세요.');
            $('input[name=fixed_price]').focus();
            return false;
        } else if(isNaN($('input[name=fixed_price]').val()) == true) {
            alert('금액은 숫자만 입력할 수 있습니다.');
            $('input[name=fixed_price]').focus();
            return false;
        }
    } else {
        if($('input[name=bid_start_price]').val() == '') {
            alert('입찰 시작가를 입력해주세요.');
            $('input[name=bid_start_price]').focus();
            return false;
        } else if(isNaN($('input[name=bid_start_price]').val()) == true) {
            alert('금액은 숫자만 입력할 수 있습니다.');
            $('input[name=bid_start_price]').focus();
            return false;
        } else if($('input[name=bid_price]').val() == '') {
            alert('즉시판매가를 입력해주세요.');
            $('input[name=bid_price]').focus();
            return false;
        } else if(isNaN($('input[name=bid_price]').val()) == true) {
            alert('금액은 숫자만 입력할 수 있습니다.');
            $('input[name=bid_price]').focus();
            return false;
        } else if($('input[name=bid_start_price]').val() > $('input[name=bid_price]').val()) {
            alert('시작가는 즉시판매가 보다 크게 입력할 수 없습니다.');
            $('input[name=bid_start_price]').focus();
            return false;
        }
         if($('.reduction-rate-div button.btn-type-02-active').data('value') == -1) {
            if($('#setReductionRate input').val() == '') {
                alert('재입찰 가격 인하율을 설정해주세요.');
                $('#setReductionRate input').focus();
                return false;
            } else if(isNaN($('#setReductionRate input').val()) == true) {
                alert('재입찰 가격 인하율은 숫자만 입력할 수 있습니다.');
                $('#setReductionRate input').focus();
                return false;
            } else if($('#setReductionRate input').val() < 1 || $('#setReductionRate input').val() > 50) {
                alert('재입찰 가격 인하율을 1 ~ 50 사이로 입력해주세요.');
                $('#setReductionRate input').focus();
                return false;
            } else {
                $('input[name=reduction_rate]').val($('#setReductionRate input').val());
            }
        } 
        if($('.repeat-no-div button.btn-type-02-active').data('value') == -1) {
            if($('#setRepeatNo input').val() == '') {
                alert('재입찰 반복 횟수를 설정해주세요.');
                $('#setRepeatNo input').focus();
                return false;
            } else if(isNaN($('#setRepeatNo input').val()) == true) {
                alert('재입찰 반복 횟수는 숫자만 입력할 수 있습니다.');
                $('#setRepeatNo input').focus();
                return false;
            } else if($('#setRepeatNo input').val() < 1 || $('#setRepeatNo input').val() > 10) {
                alert('재입찰 반복 횟수를 1 ~ 10 사이로 입력해주세요.');
                $('#setRepeatNo input').focus();
                return false;
            } else {
                $('input[name=repeat_no]').val($('#setRepeatNo input').val());
            }
        }
    }
    return true;
}
//]]>

function sale_info_form_reset() {
    $('select[name=sel_kind] option:eq(0)').prop('selected', true);
    $('select[name=sel_mnf] option:eq(0)').prop('selected', true);
    $('select[name=sel_model] option:eq(0)').prop('selected', true);
    $('select[name=sel_year] option:eq(0)').prop('selected', true);
    $('select[name=sel_year]').eq(1).find('option:eq(0)').prop('selected', true);
    $('select[name=sel_price] option:eq(0)').prop('selected', true);
    if(sale_type != 'turnkey')
        $('select[name=sel_area] option:eq(0)').prop('selected', true);
    $('input[name=txt_mnf]').val('');
    $('input[name=txt_model]').val('');
    $('input[name=input_mnf]').val('');
    $('input[name=input_model]').val('');
    $('#input-mnf').addClass('d-none');
    $('#input-model').addClass('d-none');
    $('input[name=model_year]').val('');
    $('input[name=serial_num]').val('');
    $('input[name=size_01]').val('');
    $('input[name=size_02]').val('');
    $('input[name=size_03]').val('');
    $('input[name=weight]').val('');
    $('input[name=controller]').val('');
    $('input[name=option]').val('');
    $('input[name=hope_price]').val('');
    $('input[name=pur_price]').val('');
    $('textarea[name=remark]').val('');
    $('#deliver-condition-div button').removeClass('btn-type-02-active');
    $('#deliver-condition-div button:first').addClass('btn-type-02-active');
    $('#method-div button').removeClass('btn-type-02-active');
    $('#method-div button:first').addClass('btn-type-02-active');
    $('#deliver-condition-div input').val('없음');
    $('.preview-back').css('background-image', '');
    $('.options-div').hide();
    $('#option').val('');
    $('.preview-div .preview-delete-btn').removeClass('preview-delete-btn-active');

    $('#model_year_01').val('');
    $('#model_year_02').val('');
    $('#year-div').addClass('d-none');
    $('textarea[name=option]').val('');
    $('input[name=buy_expect_date]').val('');
    $('.deliver-service-div button').removeClass('btn-type-02-active');
    $('.deliver-service-div button:first').addClass('btn-type-02-active');
    
    image = '';
}

function prev_step_01() {
    $('#step-01').addClass('d-block');
    $('#step-02-1').removeClass('d-block');
    $('#step-02-2').removeClass('d-block');
    $('.tab-step .step-list').eq(1).removeClass('d-inline-block');
    $('.tab-step .step-list').eq(2).removeClass('d-inline-block');
}

function next_step_02() {
    if(!sale_info_form_check()) {
            return;
    }

    $('#step-01').removeClass('d-block');
    if(method == '고정가격판매') {
        $('#step-02-1').addClass('d-block');
        $('.tab-step .step-list').eq(1).addClass('d-inline-block');
    }
    else {
        $('#step-02-2').addClass('d-block');
        $('.tab-step .step-list').eq(2).addClass('d-inline-block');
    }
}

function prev_step_02() {
    if(method == '고정가격판매') {
        $('#step-02-1').addClass('d-block');
    }
    else {
        $('#step-02-2').addClass('d-block');
    }
    $('#step-03').removeClass('d-block');
    $('.tab-step .step-list').eq(3).removeClass('d-inline-block');
}


function next_step_03() {
    if(!sale_info_form_check2()) {
        return;
    }
    $('#step-02-1').removeClass('d-block');
    $('#step-02-2').removeClass('d-block');
    $('#step-03').addClass('d-block');
    $('.tab-step .step-list').eq(3).addClass('d-inline-block');
}


function prev_step_03() {
    $('#step-03').addClass('d-block');
    $('#step-04').removeClass('d-block');
    $('.tab-step .step-list').eq(4).removeClass('d-inline-block');
}

function next_step_04() {
    $('#step-03').removeClass('d-block');
    $('#step-04').addClass('d-block');
    $('.tab-step .step-list').eq(4).addClass('d-inline-block');
}

function next_step_05() {
    if($('#check-01-div .btn-type-02-active').length == 0) {
        alert('질문 1에 대한 답변을 해주세요.');
        return;
    } else if($('#check-01-div .btn-type-02-active').text() == '있음' && $('input[name=check_01_det]').val() == '') {
        alert('질문 1에 대한 답변을 해주세요.');
        $('input[name=check_01_det]').focus();
        return;
    } else if($('#check-02-div .btn-type-02-active').length == 0) {
        alert('질문 2에 대한 답변을 해주세요.');
        return;
    } else if($('#check-02-div .btn-type-02-active').text() == '있음' && $('input[name=check_02_det]').val() == '') {
        alert('질문 2에 대한 답변을 해주세요.');
        $('input[name=check_02_det]').focus();
        return;
    } else if($('#check-03-div .btn-type-02-active').length == 0) {
        alert('질문 3에 대한 답변을 해주세요.');
        return;
    } else if($('#check-04-div .btn-type-02-active').length == 0) {
        alert('질문 4에 대한 답변을 해주세요.');
        return;
    }
    $('#step-04').removeClass('d-block');
    $('#step-05').addClass('d-block');
    $('.tab-step .step-list').eq(5).addClass('d-inline-block');
}

function next_step_06() {
    var total_price = 0;
    var html = '';
    $.each($('input[name="chk_ad"]'), function(index, value) {
        if($(this).is(':checked')) {
            var name = $(this).data('name');
            var price = $(this).data('price');
            if(name == '없음') {
                html += '<p class="list-row"><span class="name">광고 상품</span><span class="price">신청 안함</span></p>';
                return;
            } else {
                total_price += price;
                html += '<p class="list-row"><span class="name">' + name + '</span><span class="price">' + comma(price) + ' 원</span></p>';
            }
        }
    });
    $('#list-ad').html(html);

    html = '';

    if($('#offLine').is(':checked')) {
        total_price += 150000;
        html += '<p class="list-row"><span class="name">오프라인 성능검사</span><span class="price">150,000원</span></p>';
    } else {
        html += '<p class="list-row"><span class="name">오프라인 성능검사</span><span class="price">신청 안함</span></p>';
    }
    if($('input[name=online_eval_yn]').val() == 'y') {
        var price = $('input[name=chk_option]:checked').data('price');
        total_price += price;
        html += '<p class="list-row"><span class="name">' + $('input[name=online_eval_option]').val() + '</span><span class="price">' + comma(price) + '원</span></p>';
    } else {
        html += '<p class="list-row"><span class="name">온라인 기계평가</span><span class="price">신청 안함</span></p>';
    }
    $('#list-perform').html(html);
    $('#total-price').html(comma(total_price) + ' 원');

    $('#desc-img').css('background-image', 'url(\"' + image + '\")');

    var kind = $('select[name=sel_kind] option:selected').val(); 
    var mnf = $('select[name=sel_mnf] option:selected').val(); 
    var model = $('select[name=sel_model] option:selected').val(); 
    var area = $('select[name=sel_area] option:selected').val(); 
    var year = '';
    if($('select[name=sel_year] option:selected').data('val') != 0) {
        year = $('select[name=sel_year] option:selected').data('val'); 
    } else {
        year = $('input[name=model_year]').val().replace('년');
    }
    if(mnf == '직접입력') {
        mnf = $('input[name=txt_mnf]').val();
    }
    if(model == '직접입력') {
        model = $('input[name=txt_model]').val();
    }
    $('#desc-01').text(kind);
    $('#desc-02').text(mnf + ' / ' + model);
    $('#desc-03').text(year + '년 / ' + area);
    $('input[name=total_price]').val(total_price);

    if(total_price == 0) {
        var txt = '';
        if(reg_mode == 'insert') {
            txt = '등록';
        } else
            txt = '변경';
        if(confirm('위의 사항으로 기계를 ' + txt + ' 하시겠습니까 ?')) {
           self_complete();  
        }
    } else {
        if(confirm('위의 사항대로 결제 진행하시겠습니까 ?')) {
            $('#step-05').removeClass('d-block');
            $('#step-06').addClass('d-block');
            $('.tab-step .step-list').eq(6).addClass('d-inline-block'); 
        }
    }
}

function self_complete() {
    $('input[name=kind_no]').val($('select[name=sel_kind] option:selected').parent('optgroup').data('no')); 
    $('input[name=kind_seq]').val($('select[name=sel_kind] option:selected').data('seq')); 
    $('input[name=mnf_seq]').val($('select[name=sel_mnf] option:selected').data('seq')); 
    $('input[name=model_seq]').val($('select[name=sel_model] option:selected').data('seq')); 
    $('input[name=area_seq]').val($('select[name=sel_area] option:selected').data('seq'));
    if($('select[name=sel_year] option:selected').data('val') != 0) {
        $('input[name=model_year]').val($('select[name=sel_year] option:selected').data('val')); 
    }
    $('input[name=model_year]').val($('input[name=model_year]').val().replace('년', ''));
    var size = $('input[name=size_01]').val() + ' x ' + $('input[name=size_02]').val() + ' x ' + $('input[name=size_03]').val() + ' m'
    $('input[name=size]').val(size);
    $('input[name=weight]').val($('input[name=weight]').val() + ' kg');
    var val = '';
    $.each($('.deliver-service-div button'), function(index, value) {
        if($(this).hasClass('btn-type-02-active')) {
            val += (val == '' ? '' : ',') + $(this).text();
        }
    });
    $('input[name=deliver_service]').val(val);

    val = '';
    $.each($('.self-deliver-condition-div button'), function(index, value) {
        if($(this).hasClass('btn-type-02-active')) {
            val += (val == '' ? '' : ',') + $(this).text();
        }
    });
    $('input[name=self_deliver_condition]').val(val);
    
    $.each($('input[name=chk_ad]'), function(index, value) {
        if($(this).data('name') == '없음')
            return;
        else {
            if($(this).prop('checked') == false) {
                $(this).parent('div').find('input[type=hidden]').remove();
            }
        }
    });
    var txt_mnf = $('#input-mnf');
    var txt_model = $('#input-model');
        var input_mnf = 'false';
    var input_model = 'false';
    if(!txt_mnf.hasClass('d-none'))
        input_mnf = 'true';
    if(!txt_model.hasClass('d-none'))
        input_model = 'true';
    $('input[name=input_mnf]').val(input_mnf);
    $('input[name=input_model]').val(input_model);
    
    $('input[name=fixed_price]').val(parseInt($('input[name=fixed_price]').val() + '0000'));
    $('input[name=bid_start_price]').val(parseInt($('input[name=bid_start_price]').val() + '0000'));
    $('input[name=bid_price]').val(parseInt($('input[name=bid_price]').val() + '0000'));

    $('input[name=check_01_res]').val($('#check-01-div button.btn-type-02-active').text());
    $('input[name=check_02_res]').val($('#check-02-div button.btn-type-02-active').text());
    $('input[name=check_03_res]').val($('#check-03-div button.btn-type-02-active').text());
    $('input[name=check_04_res]').val($('#check-04-div button.btn-type-02-active').text());

    $('#sale-form').submit();
}

function update_price() {
    var html = ''
    var total_price = 0;
    $.each($('input[name=chk_ad]'), function(index, value) {
        var name = $('input[name=chk_ad]').eq(index).data('name');
        var price = $('input[name=chk_ad]').eq(index).data('price');

        if(name == '없음') {
            if($(this).prop('checked') == true) {
                html += '<div class="form-row-01 price-row">';
                html += '    <span class="txt-left">광고상품 없음</span>'; 
                html += '    <span class="txt-right">0원</span>';
                html += '</div>';
            }
        }
        else {
            if($(this).prop('checked') == true) {
                html += '<div class="form-row-01 price-row">';
                html += '    <span class="txt-left">' + name + '</span>'; 
                html += '    <span class="txt-right">' + comma(price) + '원</span>';
                html += '</div>';
                total_price += price;
            }
        }
    });
    if($('input[name=perform_check_yn]').val() == 'y') {
        html += '<div class="form-row-01 price-row">';
        html += '    <span class="txt-left">성능검사</span>'; 
        html += '    <span class="txt-right">150,000원</span>';
        html += '</div>';
        total_price += 150000;
    }
    if($('input[name=online_eval_yn]').val() == 'y') {
        var price = $('input[name=chk_option]:checked').data('price');
        html += '<div class="form-row-01 price-row">';
        html += '    <span class="txt-left">' + $('input[name=online_eval_option]').val() + '</span>'; 
        html += '    <span class="txt-right">' + comma(price) + '원</span>';
        html += '</div>';
        total_price += price;
    }
    $('input[name=total_price]').val(total_price);
    $('#price-list').html(html);
    $('#price-total .price-number').text(comma(total_price) + '원');

    if(total_price > 0) {
        $('#btn-step-05').text('결제하기');
    } else {
        $('#btn-step-05').text('등록하기');
    }
}

function turnkey_add() {
    if(sale_info_form_check() == false) {
        return;
    }

    if(!confirm('아래의 정보로 기계를 추가하시겠습니까 ?')) {
        return;
    }
   
    if($('select[name=sel_year] option:selected').data('val') != 0) {
        $('input[name=model_year]').val($('select[name=sel_year] option:selected').data('val')); 
    }
    $('input[name=model_year]').val($('input[name=model_year]').val().replace('년', ''));

    var txt_mnf = $('#input-mnf');
    var txt_model = $('#input-model');
    var input_mnf = 'false';
    var input_model = 'false';
    if(!txt_mnf.hasClass('d-none'))
        input_mnf = 'true';
    if(!txt_model.hasClass('d-none'))
        input_model = 'true';
        
    var kind = $('select[name=sel_kind] option:selected').text();
    var model = $('select[name=sel_model] option:selected').text();
    var mnf = $('select[name=sel_mnf] option:selected').text();
    if(input_model == 'true')
        model = txt_model.val();
    if(input_mnf == 'true')
        mnf = txt_mnf.val();

    $('input[name=pur_price]').val(parseInt($('input[name=pur_price]').val().replaceAll(',', '') + '0000'));

    var html = '';
    html += '<div class="scroll-div machine-item" id="machine_' + machine_cnt + '" data-no="' + machine_cnt + '">';
    html += '    <input type="hidden" name="kind_no_arr[]" value="' + $('select[name=sel_kind] option:selected').parent('optgroup').data('no') + '" />';
    html += '    <input type="hidden" name="kind_seq_arr[]" value="' + $('select[name=sel_kind] option:selected').data('seq') + '" />';
    html += '    <input type="hidden" name="mnf_seq_arr[]" value="' + $('select[name=sel_mnf] option:selected').data('seq') + '" />';
    html += '    <input type="hidden" name="input_mnf_arr[]" value="' + input_mnf + '" />';
    html += '    <input type="hidden" name="txt_mnf_arr[]" value="' + txt_mnf.val() + '" />';
    html += '    <input type="hidden" name="model_seq_arr[]" value="' + $('select[name=sel_model] option:selected').data('seq') + '" />';
    html += '    <input type="hidden" name="input_model_arr[]" value="' + input_model + '" />';
    html += '    <input type="hidden" name="txt_model_arr[]" value="' + txt_model.val() + '" />';
    html += '    <input type="hidden" name="model_year_arr[]" value="' + $('input[name=model_year]').val() + '" />';
    html += '    <input type="hidden" name="pur_price_arr[]" value="' + $('input[name=pur_price]').val() + '" />';
    html += '    <input type="hidden" name="remark_arr[]" value="' + $('textarea[name=remark]').val() + '" />';
    html += '    <input type="hidden" name="option_index_arr[]" value="' + machine_cnt + '" />';
    html += '   <i class="product-delete-btn"></i>';
    html += '   <div class="scroll-desc pt-3 pb-3">';
    html += '       <h4>' + kind + '<span>' + model + '</span>' + mnf + '</h4>';
    html += '       <span style="font-size: 13.8px;">' + $('input[name=model_year]').val().replace("년", "") + '년형</span>';
    html += '      <span style="font-size: 13.8px;">' + current_date() + '</span>';
    html += '      <span style="font-size: 13.8px;">' + $('select[name=sel_area] option:selected').text() + '</span>';
    html += '       <p><span class="accent-txt">' + comma($('input[name=pur_price]').val()) + '</span>원</p>';
    html += '   </div>';
    html += '</div>';

    $('#machine-add-list').append(html);
    machine_cnt ++;

    html = '';

    sale_info_form_reset();
}

function turnkey_complete() {
    if($('input[name=factory]').val() == '') {
        alert('공장명을 입력해주세요.');
        $('input[name=factory]').focus();
        return;
    } else if($('input[name=production]').val() == '') {
        alert('생산내용을 입력해주세요.');
        $('input[name=production]').focus();
        return;
    } else if($('input[name=quantity]').val() == '') {
        alert('총기계 수량을 입력해주세요.');
        $('input[name=quantity]').focus();
        return;
    } else if(isNaN($('input[name=quantity]').val()) == true) {
        alert('수량은 숫자만 입력할 수 있습니다.');
        $('input[name=quantity]').focus();
        return;;
    } else if($('input[name=last_date]').val() == '') {
        alert('마지막 기계 가동일을 입력해주세요.');
        $('input[name=last_date]').focus();
        return;
    } else if($('input[name=creditor]').val() == '') {
        alert('채권자 내역을 입력해주세요.');
        $('input[name=creditor]').focus();
        return;
    } else if($('input[name=expect_date]').val() == '') {
        alert('매각 예정일을 입력해주세요.');
        $('input[name=expect_date]').focus();
        return;
    } 
    if($('.machine-item').size() == 0) {
        alert('추가된 기계가 없습니다. 기계를 등록해주세요.');
        return;
    }
    if(!confirm('총 ' + $('.machine-item').size() + ' 대의 기계를 매각하시겠습니까 ? \n(위에 작성하신 내용은 사라집니다.)')) {
        return;
    }
    
    var html = '';
    html += '<input type="hidden" name="machine_cnt" value="' + (machine_cnt-1) + '" />';
    html += '<input type="hidden" name="machine_remove" value="' + machine_remove + '" />';

    $(this).parent().append(html);
    $.each($('.machine_option'), function(index, value) {
        var prefix = $(this).data('prefix');
        var val = $(this).val();
        $(this).val(prefix + val);
    });
    $('input[name=area_seq]').val($('select[name=sel_area] option:selected').data('seq'));
    
    $('.step-wrap').removeClass('d-block');
    $('#owner-check').addClass('d-block');
}

function turnkey_check_complete() {
    if(confirm('작성하신 내용으로 턴키매각을 신청하시겠습니까 ?')) {
        $('input[name=check_01_res]').val($('#check-01-div button.btn-type-02-active').text());
        $('input[name=check_02_res]').val($('#check-02-div button.btn-type-02-active').text());
        $('input[name=check_03_res]').val($('#check-03-div button.btn-type-02-active').text());
        $('input[name=check_04_res]').val($('#check-04-div button.btn-type-02-active').text());
        $('#sale-form').submit();
    }
}

function sale_temp_save() {
    var kind_seq = $('select[name=sel_kind] option:selected').data('seq'); 
    var mnf_seq = $('select[name=sel_mnf] option:selected').data('seq'); 
    var model_seq = $('select[name=sel_model] option:selected').data('seq'); 
    var mnf_mode = $('select[name=sel_mnf] option:selected').data('mode'); 
    var model_mode = $('select[name=sel_model] option:selected').data('mode'); 
    var mnf_txt = $('input[name=txt_mnf]').val();
    var model_txt = $('input[name=txt_model]').val();
    var area_seq = $('select[name=sel_area] option:selected').data('seq');
    var model_year_val = $('select[name=sel_year] option:selected').data('val'); 
    var model_year_txt = $('input[name=model_year]').val();
    
    var size = $('input[name=size_01]').val() + ' x ' + $('input[name=size_02]').val() + ' x ' + $('input[name=size_03]').val() + ' m'
    $('input[name=size]').val(size);
    $('input[name=weight]').val($('input[name=weight]').val() + ' kg');
    var val = '';
    $.each($('.deliver-service-div button'), function(index, value) {
        if($(this).hasClass('btn-type-02-active')) {
            val += (val == '' ? '' : ',') + $(this).text();
        }
    });
    $('input[name=deliver_service]').val(val);

    val = '';
    $.each($('.self-deliver-condition-div button'), function(index, value) {
        if($(this).hasClass('btn-type-02-active')) {
            val += (val == '' ? '' : ',') + $(this).text();
        }
    });
    $('input[name=self_deliver_condition]').val(val);

    $.each($('#deliver-condition-div button'), function(index, value) {
        if($(this).hasClass('btn-type-02-active')) {
            val += (val == '' ? '' : ',') + $(this).text();
        }
    });
    $('input[name=deliver_condition]').val(val);
    
    val = '';
    $.each($('#method-div button'), function(index, value) {
        if($(this).hasClass('btn-type-02-active')) {
            val += (val == '' ? '' : ',') + $(this).text();
        }
    });
    $('input[name=method]').val(val);

    var options = '';
    $.each($('.options-div'), function(index, value) {
        var display = $(this).css('display');
        if(display == 'block') {
            options += options == '' ? $(this).find('.options-txt').text() : ',' + $(this).find('.options-txt').text();
        }
    });

    $('input[name=fixed_price]').val(parseInt($('input[name=fixed_price]').val() + '0000'));
    $('input[name=bid_start_price]').val(parseInt($('input[name=bid_start_price]').val() + '0000'));
    $('input[name=bid_price]').val(parseInt($('input[name=bid_price]').val() + '0000'));

    $('input[name=check_01_res]').val($('#check-01-div button.btn-type-02-active').text());
    $('input[name=check_02_res]').val($('#check-02-div button.btn-type-02-active').text());
    $('input[name=check_03_res]').val($('#check-03-div button.btn-type-02-active').text());
    $('input[name=check_04_res]').val($('#check-04-div button.btn-type-02-active').text());
    
    var type_str = '';
    if(sale_type == 'self') 
        type_str = '셀프판매';
    else if(sale_type == 'emergency')
        type_str = '긴급판매';
    else if(sale_type == 'direct')
        type_str = '머박다이렉트';
    else if(sale_type == 'turnkey')
        type_str = '턴키매각';

    var temp_list = JSON.parse(localStorage.getItem('sale_temp_list'));
    var temp_obj = {
        'type': sale_type,
        'type_str': type_str,
        'machine_cnt': machine_cnt,
        'kind_seq': kind_seq,
        'mnf_seq': mnf_seq,
        'model_seq': model_seq,
        'mnf_mode': mnf_mode,
        'model_mode': model_mode,
        'mnf_txt': mnf_txt,
        'model_txt': model_txt,
        'area_seq': area_seq,
        'serial_num': $('input[name=serial_num]').val(),
        'model_year_val': model_year_val,
        'model_year_txt': model_year_txt,
        'size': $('input[name=size]').val(),
        'weight': $('input[name=weight]').val(),
        'deliver_condition': $('input[name=deliver_condition]').val(),
        'controller': $('input[name=controller]').val(),
        'hope_price': $('input[name=hope_price]').val(),
        'method': $('input[name=method]').val(),
        'option': $('#option').val(),
        'options': options,
        'machine_add_list': $('#machine-add-list').html(),
        'factory': $('input[name=factory]').val(),
        'production': $('input[name=production]').val(),
        'quantity': $('input[name=quantity]').val(),
        'last_date': $('input[name=last_date]').val(),
        'creditor': $('input[name=creditor]').val(),
        'pur_price': $('input[name=pur_price]').val(),
        'remark': $('textarea[name=remark]').val(),
        'expect_date': $('input[name=expect_date]').val(),
        'check_01_res': $('input[name=check_01_res]').val(),
        'check_02_res': $('input[name=check_02_res]').val(),
        'check_03_res': $('input[name=check_03_res]').val(),
        'check_04_res': $('input[name=check_04_res]').val(),
        'check_01_det': $('input[name=check_01_det]').val(),
        'check_02_det': $('input[name=check_02_det]').val(),
        'save_time': getTimeStamp()
    };
    if(!temp_list) {
        temp_list = [];
        temp_list.push(temp_obj);
    } else {
        temp_list.push(temp_obj);
    }
    localStorage.setItem('sale_temp_list', JSON.stringify(temp_list));
    
    alert('작성하신 데이터가 저장되었습니다.\n임시 저장된 데이터는 마이페이지에서 재작성 하실 수 있습니다.');
}

function sale_load_data(temp_seq) { 
    var temp_list = JSON.parse(localStorage.getItem('sale_temp_list'));
    var temp_obj = temp_list[temp_seq];
    
    if(!temp_obj) return;

    machine_cnt = temp_obj.machine_cnt;

    $('#machine-add-list').html(temp_obj.machine_add_list);
    $.each($('select[name=sel_kind] option'), function(index, value) {
        if($(this).data('seq') == temp_obj.kind_seq) {
            $(this).prop('selected', true);
            return false;
        }
    });
    if(temp_obj.mnf_mode == 'input') {
        $('select[name=sel_mnf] option').eq(1).prop('selected', true);
        $('#input-mnf').removeClass('d-none');
        $('input[name=txt_mnf]').val(temp_obj.mnf_txt);
    } else {
        $.each($('select[name=sel_mnf] option'), function(index, value) {
            if($(this).data('seq') == temp_obj.mnf_seq) {
                $(this).prop('selected', true);
                return false;
            }
        });
    }
    setTimeout(function(){
        change_model_list();

        setTimeout(function(){
            if(temp_obj.model_mode == 'input') {
                $('select[name=sel_model] option').eq(1).prop('selected', true);
                $('#input-model').removeClass('d-none');
                $('input[name=txt_model]').val(temp_obj.model_txt);
            } else {
                $.each($('select[name=sel_model] option'), function(index, value) {
                    if($(this).data('seq') == temp_obj.model_seq) {
                        $(this).prop('selected', true);
                        return false;
                    }
                });
            }
            
            setTimeout(function(){
                var kind_name = '';
                var mnf_name = '';
                var model_name = '';
                if(!$('select[name=sel_kind] option:selected').data('seq') == false) {
                    kind_name = $('select[name=sel_kind] option:selected').text();
                }
                if(!$('select[name=sel_mnf] option:selected').data('seq') == false) {
                    mnf_name = $('select[name=sel_mnf] option:selected').text();
                }

                if(!$('select[name=sel_model] option:selected').data('seq') == false) {
                    model_name = $('select[name=sel_model] option:selected').text();
                }
                if(!(kind_name == '' && mnf_name == '' && model_name == '')) {
                    var data = {'kind_name': kind_name, 'mnf_name': mnf_name, 'model_name': model_name};
                    ajaxLowestModel(data);
                }
            }, 100);
        }, 200);
    }, 100);
    
    $.each($('select[name=sel_area] option'), function(index, value) {
        if($(this).data('seq') == temp_obj.area_seq) {
            $(this).prop('selected', true);
            return false;
        }
    });
    setTimeout(function(){
        $.each($('select[name=sel_year] option'), function(index, value) {
            if($(this).data('val') == temp_obj.model_year_val) {
                if($(this).data('val') == '0') {
                    $('#year-div').removeClass('d-none');
                    $('input[name=model_year]').val(temp_obj.model_year_txt);
                }
                $(this).prop('selected', true);
                return false;
            }
        });
    }, 1);
    $('#deliver-condition-div button')
    $.each($('#deliver-condition-div button'), function(index, value) {
        if($(this).text() == temp_obj.deliver_condition) {
            $('#deliver-condition-div button').removeClass('btn-type-02-active');
            $(this).addClass('btn-type-02-active');
            return false;
        }
    });
    $.each($('#method-div button'), function(index, value) {
        if($(this).text() == temp_obj.method) {
            $('#method-div button').removeClass('btn-type-02-active');
            $(this).addClass('btn-type-02-active');
            return false;
        }
    });
    if(temp_obj.options) {
        $.each(temp_obj.options.split(','), function(index, value) {
            if(value == '') 
                return false;
            var html = '';
            html += '<div class="options-div mr-2 mb-1">';
            html += '  <input type="hidden" name="option_name_arr[]" data-prefix="#' + machine_cnt + '_" value="' + value + '" class="machine_option machine_' + machine_cnt + '"/>';
            html += '  <pre class="options-txt">' + value + '</pre>';
            html += '  <i class="options-delete-btn"></i>';
            html += '</div>';
            $('#option-append').append(html);
        });
    }
    
    var size_arr = temp_obj.size;
    if(size_arr) {
        size_arr = size_arr.replaceAll(' ', '').replaceAll('x', '/').replaceAll('m', '/').split('/');
        $('input[name=size_01]').val(size_arr[0]);
        $('input[name=size_02]').val(size_arr[1]);
        $('input[name=size_03]').val(size_arr[2]);
    }
    var weight = temp_obj.weight;
    if(weight) {
        weight = weight.replace(' kg', '');
        $('input[name=weight]').val(weight);
    }
    $('input[name=serial_num]').val(temp_obj.serial_num);
    $('input[name=controller').val(temp_obj.controller);
    $('input[name=hope_price').val(temp_obj.hope_price);
    $('#option').val(temp_obj.option);
    
    $('input[name=factory').val(temp_obj.factory);
    $('input[name=production').val(temp_obj.production);
    $('input[name=quantity').val(temp_obj.quantity);
    $('input[name=last_date').val(temp_obj.last_date);
    $('input[name=creditor').val(temp_obj.creditor);
    $('input[name=expect_date').val(temp_obj.expect_date);
    $('input[name=pur_price').val(temp_obj.pur_price);
    $('textarea[name=remark').val(temp_obj.remark);
    $.each($('#check-01-div button'), function(index, value) {
        if($(this).text() == temp_obj.check_01_res) {
            $('#check-01-div button').removeClass('btn-type-02-active');
            $(this).addClass('btn-type-02-active');
            return false;
        }
    });
    $.each($('#check-02-div button'), function(index, value) {
        if($(this).text() == temp_obj.check_02_res) {
            $('#check-02-div button').removeClass('btn-type-02-active');
            $(this).addClass('btn-type-02-active');
            return false;
        }
    });
    $.each($('#check-03-div button'), function(index, value) {
        if($(this).text() == temp_obj.check_03_res) {
            $('#check-03-div button').removeClass('btn-type-02-active');
            $(this).addClass('btn-type-02-active');
            return false;
        }
    });
    $.each($('#check-04-div button'), function(index, value) {
        if($(this).text() == temp_obj.check_04_res) {
            $('#check-04-div button').removeClass('btn-type-02-active');
            $(this).addClass('btn-type-02-active');
            return false;
        }
    });
    $('input[name=check_01_res]').val(temp_obj.check_01_res);
    $('input[name=check_02_res]').val(temp_obj.check_02_res);
    $('input[name=check_03_res]').val(temp_obj.check_03_res);
    $('input[name=check_04_res]').val(temp_obj.check_04_res);
    $('input[name=check_01_det]').val(temp_obj.check_01_det);
    $('input[name=check_02_det]').val(temp_obj.check_02_det);

    temp_list.splice(temp_seq, 1);
    localStorage.setItem('sale_temp_list', JSON.stringify(temp_list));
}

function default_sale_load_data(model_year, mnf_seq, model_seq, path, mode) {
console.log('default 들어옴');
    image = path;
    reg_mode = mode;
    setTimeout(function(){
        change_model_list();

        $.each($('select[name=sel_year] option'), function(index, value) {
            if(index == 1) return;
            if($(this).data('val') == model_year) {
                $(this).prop('selected', true);
                return;
            }
        });
        $.each($('select[name=sel_mnf] option'), function(index, value) {
            if($(this).data('seq') == mnf_seq) {
                $(this).prop('selected', true);
                return;
            }
        });

        setTimeout(function(){
            $.each($('select[name=sel_model] option'), function(index, value) {
                if($(this).data('seq') == model_seq) {
                    $(this).prop('selected', true);
                    return;
                }
            });
            
            setTimeout(function(){
                var kind_name = '';
                var mnf_name = '';
                var model_name = '';
                if(!$('select[name=sel_kind] option:selected').data('seq') == false) {
                    kind_name = $('select[name=sel_kind] option:selected').text();
                }
                if(!$('select[name=sel_mnf] option:selected').data('seq') == false) {
                    mnf_name = $('select[name=sel_mnf] option:selected').text();
                }

                if(!$('select[name=sel_model] option:selected').data('seq') == false) {
                    model_name = $('select[name=sel_model] option:selected').text();
                }
                if(!(kind_name == '' && mnf_name == '' && model_name == '')) {
                    var data = {'kind_name': kind_name, 'mnf_name': mnf_name, 'model_name': model_name};
                    ajaxLowestModel(data);
                }
            }, 100);
        }, 200);
    }, 100);
}

function show_estimate_form(estimate_seq, mode) {
    window.open('/user/estimate_form/' + estimate_seq+'/' + mode + '?popup=y', "견적서 양식", "width=900, height=" + window.innerHeight + ", left=300, top=0"); 
}