$(document).ready(function(){
	$('.chk_one').change(function(){
		var is_checked = $(this).is(':checked');
		if(is_checked == true) {
			$('.chk_one').prop('checked', false);
			$(this).prop('checked', true);
		}
	});
});

$(document).on('change', 'select', function() {
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

function go_insert(type) {
	location.href = '/admin/sale/sale_regist?reg_mode=insert&type=' + type;
}

function go_modify(type) {
	var info_seq = $('.chk_one:checked').val();
	if(!info_seq) {
		alert('수정하실 기계를 선택해주세요.');
		return;
	}
	location.href = '/admin/sale/sale_regist?reg_mode=modify&type=' + type + '&seq=' + info_seq;
}

function go_delete_process() {
	var info_seq = $('.chk_one:checked').val();
	if(!info_seq) {
		alert('삭제하실 기계를 선택해주세요.');
		return;
	}
	if(confirm('삭제하신 데이터는 복구가 불가능합니다.\n정말 삭제하시겠습니까 ?')) {
		$('#frm_delete input[name=info_seq]').val(info_seq);
		$('#frm_delete').submit();
	}
}

function go_insert_find() {
	location.href = '/admin/sale/find_regist?reg_mode=insert';
}

function go_modify_find() {
	var find_seq = $('.chk_one:checked').val();
	if(!find_seq) {
		alert('수정하실 기계를 선택해주세요.');
		return;
	}
	location.href = '/admin/sale/find_regist?reg_mode=modify&seq=' + find_seq;
}

function go_delete_find_process() {
	var find_seq = $('.chk_one:checked').val();
	if(!find_seq) {
		alert('삭제하실 기계를 선택해주세요.');
		return;
	}
	if(confirm('삭제하신 데이터는 복구가 불가능합니다.\n정말 삭제하시겠습니까 ?')) {
		$('#frm_delete input[name=find_seq]').val(find_seq);
		$('#frm_delete').submit();
	}
}

function data_update() {
    $.ajax({
        type: 'post',
        url: '/load/data_update',
        dataType: 'json',
        success: function(data) {},
        error: function() {
            console.log('error');
        }
    });
}
data_update();

function go_select_userid(member_seq) {
	$.ajax({
        type: 'post',
        url: '/admin/sale/get_userdata',
        dataType: 'json',
        data: {'member_seq': member_seq},
        success: function(data) {
        	$('.txt_userid').text(data.userid);
        	$('input[name=userid]').val(data.userid);
        },
        error: function() {
            console.log('error');
        }
    });
}
function go_select_target_userid(member_seq) {
	$.ajax({
        type: 'post',
        url: '/admin/sale/get_userdata',
        dataType: 'json',
        data: {'member_seq': member_seq},
        success: function(data) {
        	$('.txt_target_userid').text(data.userid);
        	$('input[name=target_userid]').val(data.userid);
        },
        error: function() {
            console.log('error');
        }
    });
}

function show_service_regist_popup() {
	var info_seq = $('.chk_one:checked').val();
	if(!info_seq) {
		alert('이용하실 기계를 선택해주세요.');
		return;
	}
	$('#frm_service input[name=info_seq]').val(info_seq);
	openDialog('유료서비스 신청', "service_regist_layer", {"width":"240","height":"440"});
}

function show_service_cancel_popup() {
	var info_seq = $('.chk_one:checked').val();
	if(!info_seq) {
		alert('취소하실 기계를 선택해주세요.');
		return;
	}
	$('#frm_cancel_service input[name=info_seq]').val(info_seq);
	openDialog('유료서비스 취소', "service_cancel_layer", {"width":"240","height":"440"});
}


function go_service_process() {
	var service_list = $("input[name='chk_service[]']:checked");
	if(service_list.length == 0) {
		alert('이용하실 서비스를 선택해주세요.');
		return;
	}
	var message = '';
	var name = '';
	var price = 0;
	$.each(service_list, function(index, value) {
		name += index == 0 ? $(this).val() : ", " + $(this).val();
		
		price += $(this).data('price');
	});
	message = name + " 서비스를 판매자에게 결제요청 하시겠습니까 ?\n(총가격 : " + comma(price) + "원)";
	if(confirm(message)) {
		$('#frm_service').submit();
	}
}

function go_service_cancel_process() {
	var service_list = $("input[name='chk_service[]']:checked");
	if(service_list.length == 0) {
		alert('취소하실 서비스를 선택해주세요.');
		return;
	}
	var message = '';
	var name = '';
	$.each(service_list, function(index, value) {
		name += index == 0 ? $(this).val() : ", " + $(this).val();
	});
	message = name + " 서비스를 취소하시겠습니까 ?";
	if(confirm(message)) {
		$('#frm_cancel_service').submit();
	}
}

function show_service_info_popup() {
	var info_seq = $('.chk_one:checked').val();
	if(!info_seq) {
		alert('확인할 기계를 선택해주세요.');
		return;
	}
	$.ajax({
		type: 'post',
		url: '/admin/sale/get_service_info',
		dataType: 'json',
		data: {'info_seq': info_seq},
		success: function(data) {
			var html = '';
			var th_data = '';
			th_data += '<th width="60">번호</th>';
			th_data += '<th width="100">결제번호</th>';
			th_data += '<th width="140">결제일</th>';
			th_data += '<th width="100">결제자</th>';
			th_data += '<th width="260">결제내용</th>';
			th_data += '<th width="80">결제금액</th>';
			th_data += '<th width="100">결제수단</th>';
			th_data += '<th width="100">결제상태</th>';
			
			var td_data = '';
			if(data.ad_pay_info.length > 0) {
				$.each(data.ad_pay_info, function(index, value) {
					td_data += '<tr class="list-row" style="height: 40px;">';
					td_data += '<td align="center">' + (index+1) + '</td>';
					td_data += '<td align="center">' + value.pay_no + '</td>';
					td_data += '<td align="center">' + value.reg_date + '</td>';
					td_data += '<td align="center">' + value.pay_userid + '</td>';
					td_data += '<td align="center">' + value.pay_content + '</td>';
					td_data += '<td align="center">' + price_format(value.pay_price) + '원</td>';
					td_data += '<td align="center">' + value.pay_method + '</td>';
					td_data += '<td align="center">' + value.pay_state + '</td>';
					td_data += '</tr>';
				});
			} else {
				th_data = '<th>결제내역</th>';
				td_data += '<tr class="list-row">';
				td_data += '<td align="center">프리미엄광고 결제내역이 없습니다.</td>';
				td_data += '</tr>';
			}
			html = get_table_html(th_data, td_data);
			$('#tbl-ad-info').html(html);
			
			html = '';
			th_data = '';
			th_data += '<th width="60">번호</th>';
			th_data += '<th width="100">결제번호</th>';
			th_data += '<th width="140">결제일</th>';
			th_data += '<th width="100">결제자</th>';
			th_data += '<th width="260">결제내용</th>';
			th_data += '<th width="80">결제금액</th>';
			th_data += '<th width="100">결제수단</th>';
			th_data += '<th width="100">결제상태</th>';
			td_data = '';
			if(data.perform_pay_info.length > 0) {
				$.each(data.perform_pay_info, function(index, value) {
					td_data += '<tr class="list-row" style="height: 40px;">';
					td_data += '<td align="center">' + (index+1) + '</td>';
					td_data += '<td align="center">' + value.pay_no + '</td>';
					td_data += '<td align="center">' + value.reg_date + '</td>';
					td_data += '<td align="center">' + value.pay_userid + '</td>';
					td_data += '<td align="center">' + value.pay_content + '</td>';
					td_data += '<td align="center">' + price_format(value.pay_price) + '원</td>';
					td_data += '<td align="center">' + value.pay_method + '</td>';
					td_data += '<td align="center">' + value.pay_state + '</td>';
					td_data += '</tr>';
				});
			} else {
				th_data = '<th>결제내역</th>';
				td_data += '<tr class="list-row">';
				td_data += '<td align="center">성능검사 결제내역이 없습니다.</td>';
				td_data += '</tr>';
			}
			html = get_table_html(th_data, td_data);
			$('#tbl-perform-info').html(html);
			
			html = '';
			th_data = '';
			th_data += '<th width="60">번호</th>';
			th_data += '<th width="100">결제번호</th>';
			th_data += '<th width="140">결제일</th>';
			th_data += '<th width="100">결제자</th>';
			th_data += '<th width="260">결제내용</th>';
			th_data += '<th width="80">결제금액</th>';
			th_data += '<th width="100">결제수단</th>';
			th_data += '<th width="100">결제상태</th>';
			td_data = '';
			if(data.eval_pay_info.length > 0) {
				$.each(data.eval_pay_info, function(index, value) {
					td_data += '<tr class="list-row" style="height: 40px;">';
					td_data += '<td align="center">' + (index+1) + '</td>';
					td_data += '<td align="center">' + value.pay_no + '</td>';
					td_data += '<td align="center">' + value.reg_date + '</td>';
					td_data += '<td align="center">' + value.pay_userid + '</td>';
					td_data += '<td align="center">' + value.pay_content + '</td>';
					td_data += '<td align="center">' + price_format(value.pay_price) + '원</td>';
					td_data += '<td align="center">' + value.pay_method + '</td>';
					td_data += '<td align="center">' + value.pay_state + '</td>';
					td_data += '</tr>';
				});
			} else {
				th_data = '<th>결제내역</th>';
				td_data += '<tr class="list-row">';
				td_data += '<td align="center">기계평가 결제내역이 없습니다.</td>';
				td_data += '</tr>';
			}
			html = get_table_html(th_data, td_data);
			$('#tbl-eval-info').html(html);
			
			var using_service = data.ad_info.using_service;
			if(using_service == '')
				using_service = '미이용';
			$('#ad_setting_layer #using_service').text(data.ad_info.using_service);
			$('#service_info_layer #using_service').text(using_service);
			$('#service_info_layer #using_perform').text(data.using_perform);
			$('#service_info_layer #using_eval').text(data.using_eval);
			$('#ad_setting_layer input[name=start_date]').val(data.ad_info.start_date);
			$('#ad_setting_layer input[name=end_date]').val(data.ad_info.end_date);
					
			openDialog('유료서비스 정보', "service_info_layer", {"width": "1100","height":"600"});
		},
		error: function() {
			console.log('error');
		}
	});
}

function go_service_pay_check() {
	var info_seq = $('.chk_one:checked').val();
	$('#frm_service_pay input[name=info_seq]').val(info_seq);
	$.ajax({
		type: 'post',
		url: '/admin/sale/is_pay_check',
		dataType: 'json',
		data: {'info_seq': info_seq},
		success: function(data) {
			if(data == '입금대기') {
				if(confirm('입금대기 서비스들에 대한 결제확인 처리를 하시겠습니까 ?')) {
					$('#frm_service_pay').submit();
				}
			} else {
				alert('결제확인할 내역이 없습니다.');
			}
		},
		error: function() {
			console.log('error');
		}
	});
}

function show_ad_setting_layer() {
	var info_seq = $('.chk_one:checked').val();
	$('#frm_ad_setting input[name=info_seq]').val(info_seq);
	if($('#ad_setting_layer #using_service').text() == '') 
		alert('이용중인 서비스가 없습니다.');
	else
		openDialog('이용일 설정', "ad_setting_layer", {"width": "350","height":"260"});
}

function go_ad_setting() {
	if($('#frm_ad_setting input[name=start_date]').val() == '') {
		alert('시작일을 선택해주세요.');
		$('#frm_ad_setting input[name=start_date]').focus();
		return;
	} else if($('#frm_ad_setting input[name=end_date]').val() == '') {
		alert('종료일을 선택해주세요.');
		$('#frm_ad_setting input[name=end_date]').focus();
		return;
	}
	if(confirm('위의 날짜로 이용일을 설정하시겠습니까 ?')) {
		$('#frm_ad_setting').submit();
	}
}

function get_table_html(th_data, td_data) {
	var html = '';
	
	html += '<thead class="lth">';
	html += '<tr>';
	html += th_data;
	html += '</tr>';
	html += '</thead">';
	html += '<tbody class="ltb">';
	html += td_data;
	html += '</tbody">';
	
	return html;
}

function go_select_dealer(selectMemberArray) {
	if(confirm('선택된 ' + selectMemberArray.length + '명의 딜러회원에게 견적서 작성을 요청하시겠습니까 ?')) {
		var info_seq = $('.chk_one:checked').val();
		$.ajax({
			type: 'post',
			url: '/admin/sale/estimate_regist_process',
			dataType: 'json',
			data: {'info_seq': info_seq, 'dealer_list': selectMemberArray.join(',')},
			success: function(data) {
				if(data.result == true) {
					alert('견적서 신청이 완료되었습니다.\n작성된 견적서는 견적서 보기에서 확인하실 수 있습니다.');
				}
			},
			error: function() {
				console.log('error');
			}
		});
	}
}

function show_estimate_info_popup() {
	var info_seq = $('.chk_one:checked').val();
	if(!info_seq) {
		alert('견적서를 볼 기계를 선택해주세요.');
		return;
	}
	$.ajax({
		type: 'post',
		url: '/admin/sale/get_estimate_info',
		dataType: 'json',
		data: {'info_seq': info_seq},
		success: function(data) {
			var estimate_state = '';
			if(data.info.estimate_yn == 'n') {
				estimate_state = '견적서 미신청';
			} else if(data.info.estimate_yn == 'h') {
				estimate_state = '견적서 작성기간';
			} else if(data.info.estimate_yn == 'y') {
				estimate_state = '견적서 전송완료';
			}
			$('#estimate_info_popup #sales_no').text(data.info.sales_no);
			$('#estimate_info_popup #estimate_state').text(estimate_state);
			
			var html = '';
			var th_data = '';
			th_data += '<th width="60">번호</th>';
			th_data += '<th width="140">신청일</th>';
			th_data += '<th width="140">딜러 아이디</th>';
			th_data += '<th width="100">상태</th>';
			th_data += '<th width="100">판매자 선택여부</th>';
			th_data += '<th width="100">견적서 확인</th>';
			
			var td_data = '';
			if(data.list.length > 0) {
				$.each(data.list, function(index, value) {
					var select_nm = '';
					if(value.estimate_yn != 'y')
						select_nm = '-';
					else if(value.select_yn == 'y')
						select_nm = '선택'
					else if(value.select_yn == 'n')
						select_nm = '미선택';
					td_data += '<tr class="list-row" style="height: 40px;">';
					td_data += '<td align="center">' + (index+1) + '</td>';
					td_data += '<td align="center">' + value.reg_date + '</td>';
					td_data += '<td align="center">' + value.userid + '</td>';
					td_data += '<td align="center">' + value.state + '</td>';
					td_data += '<td align="center">' + select_nm + '</td>';
					if(value.state != '작성완료')
						td_data += '<td align="center">-</td>';
					else if(value.state == '작성완료')
						td_data += '<td align="center"><div><span class="btn small valign-middle"><input type="button" value="확인" onclick="show_estimate_form(\'' + value.estimate_seq + '\', \'view\')" /></span></div></td>';
					td_data += '</tr>';
				});
			} else {
				th_data = '<th>견적서 내역</th>';
				td_data += '<tr class="list-row">';
				td_data += '<td align="center">견적서 신청 내역이 없습니다.</td>';
				td_data += '</tr>';
			}
			html = get_table_html(th_data, td_data);
			$('#tbl-estimate-info').html(html);
			
			openDialog('견적서 보기', "estimate_info_popup", {"width": "800","height":"450"});
		},
		error: function() {
			console.log('error');
		}
	});
}

function show_estimate_form(estimate_seq, mode) {
    window.open('/admin/sale/estimate_form?seq=' + estimate_seq+'&mode=' + mode + '&popup=y', "견적서 양식", "width=900, height=" + window.innerHeight + ", left=300, top=0"); 
}

function go_select_estimate(selectMember) {
	if(confirm('선택된 딜러회원으로 견적서를 작성하시겠습니까 ?')) {
		var info_seq = $('.chk_one:checked').val();
		$.ajax({
			type: 'post',
			url: '/admin/sale/estimate_admin_regist',
			dataType: 'json',
			data: {'info_seq': info_seq, 'member_seq': selectMember},
			success: function(data) {
				if(data.result == false) {
					if(!confirm("이미 해당 기계에 대한 견적서가 작성되어있습니다. \n작성된 견적서를 삭제하고 새로 작성하시겠습니까 ?")) {
						return;
					}
				}
				show_estimate_form(data.estimate_seq, 'regist');
			},
			error: function() {
				console.log('error');
			}
		});
	}
}

function go_estimate_send() {
	var info_seq = $('.chk_one:checked').val();
	$.ajax({
		type: 'post',
		url: '/admin/sale/get_estimate_info',
		dataType: 'json',
		data: {'info_seq': info_seq},
		success: function(data) {
			var finish_cnt = 0;
			if(data.list.length > 0) {
				$.each(data.list, function(index, value) {
					if(value.state == '작성완료')
						finish_cnt ++;
				});
			}
			if(finish_cnt == 0) {
				alert('작성완료된 견적서가 없습니다.');
				return;
			} else {
				if(confirm('작성이 완료된 '+ finish_cnt + '개의 견적서를 판매자에게 전송하시겠습니까 ?')) {
					$('#frm_estimate_send input[name=info_seq]').val(info_seq);
					$('#frm_estimate_send').submit();
				}
			}
		},
		error: function() {
			console.log('error');
		}
	});
}

$(document).on('change', '.preview-div .input-file', function(e){
    var files = e.target.files;
    var filesArr = Array.prototype.slice.call(files);

    var reader = new FileReader();
    var preview = $(this).parents('.preview-div').find('.preview-back');

    filesArr.forEach(function(f) {
        if(!f.type.match('image.*')) {
            alert('이미지 파일이 아닙니다.');
            return;
        }

        reader.onload = function(e) {
           preview.css('background-image', 'url(\"' + e.target.result + '\")');
           $('.preview-div-active .preview-delete-btn').addClass('preview-delete-btn-active');
        }
       reader.readAsDataURL(f);
    });
});

function go_osc_insert() {
	location.href = '/admin/osc/osc_regist?reg_mode=insert';
}

function go_osc_modify() {
	var osc_seq = $('.chk_one:checked').val();
	if(!osc_seq) {
		alert('수정하실 외주를 선택해주세요.');
		return;
	}
	location.href = '/admin/osc/osc_regist?reg_mode=modify&seq=' + osc_seq;
}

function go_osc_delete() {
	var osc_seq = $('.chk_one:checked').val();
	if(!osc_seq) {
		alert('삭제하실 외주를 선택해주세요.');
		return;
	}
	if(confirm('삭제하신 데이터는 복구가 불가능합니다.\n정말 삭제하시겠습니까 ?')) {
		$('#frm_delete input[name=osc_seq]').val(osc_seq);
		$('#frm_delete').submit();
	}
}

function go_ptn_modify() {
	var partner_seq = $('.chk_one:checked').val();
	if(!partner_seq) {
		alert('수정하실 파트너를 선택해주세요.');
		return;
	}
	location.href = '/admin/osc/ptn_modify?seq=' + partner_seq;
}

function show_osc_apply_layer() {
	var osc_seq = $('.chk_one:checked').val();
	if(!osc_seq) {
		alert('확인할 외주를 선택해주세요.');
		return;
	}
	$.ajax({
		type: 'post',
		url: 'get_osc_apply_list',
		dataType: 'json',
		data: {osc_seq: osc_seq},
		success: function(data) {
			var th_data = '';
			var td_data = '';
			th_data += '<th width="60">번호</th>';
			th_data += '<th width="150">지원일</th>';
			th_data += '<th width="120">아이디</th>';
			th_data += '<th width="100">진행상태</th>';
			
			if(data.apply_cnt > 0) {
				$.each(data.apply_list, function(index, value) {
					var state = '';
					if(value.state == '0') 
						state = '대기';
					else if(value.state == '1') 
						state = '미팅';
					else if(value.state == '2') 
						state = '계약';
					else if(value.state == '3') 
						state = '완료';
					td_data += '<tr class="list-row">';
					td_data += '<td align="center">' + (index+1) + '</td>';
					td_data += '<td align="center">' + value.reg_date_2 + '</td>';
					td_data += '<td align="center"><a href="/mch/partner_info/' + value.partner_seq + '" target="_blank">' + value.userid + '</a></td>';
					td_data += '<td align="center">' + state + '</td>';
					td_data += '</tr>';
				});
			} else {
				th_data = '<th>지원현황</th>';
				td_data += '<tr class="list-row">';
				td_data += '<td align="center">지원자가 없습니다.</td>';
				td_data += '</tr>';
			}
			
			var html = get_table_html(th_data, td_data);
			$('#osc_apply_layer table').html(html);
			$('#osc_apply_layer #apply_cnt').html(data.apply_cnt);
			
			openDialog('지원현황', "osc_apply_layer", {"width": "600","height":"400"});
		}, 
		error: function() {
			console.log('error');
		}
	});
}

function show_ptn_finish_layer(partner_seq) {
	$.ajax({
		type: 'post',
		url: 'get_ptn_finish_list',
		dataType: 'json',
		data: {partner_seq: partner_seq},
		success: function(data) {
			var th_data = '';
			var td_data = '';
			th_data += '<th width="60">번호</th>';
			th_data += '<th width="150">완료일</th>';
			th_data += '<th width="160">외주명</th>';
			th_data += '<th width="100">계약금액</th>';
			
			if(data.finish_list.length > 0) {
				$.each(data.finish_list, function(index, value) {
					td_data += '<tr class="list-row">';
					td_data += '<td align="center">' + (index+1) + '</td>';
					td_data += '<td align="center">' + value.finish_date + '</td>';
					td_data += '<td align="center"><a href="/mch/osc_info/' + value.osc_seq + '" target="_blank">' + value.osc_name + '</a></td>';
					td_data += '<td align="center">' + price_format(value.budget) + '원</td>';
					td_data += '</tr>';
				});
			} else {
				th_data = '<th>실적</th>';
				td_data += '<tr class="list-row">';
				td_data += '<td align="center">실적 내역이 없습니다.</td>';
				td_data += '</tr>';
			}
			
			var html = get_table_html(th_data, td_data);
			$('#ptn_finish_layer table').html(html);
			
			openDialog('실적', "ptn_finish_layer", {"width": "600","height":"400"});
		}, 
		error: function() {
			console.log('error');
		}
	});
}

function show_ptn_grade_layer(partner_seq) {
	$.ajax({
		type: 'post',
		url: 'get_ptn_grade_list',
		dataType: 'json',
		data: {partner_seq: partner_seq},
		success: function(data) {
			var th_data = '';
			var td_data = '';
			th_data += '<th width="60">번호</th>';
			th_data += '<th width="150">평가일</th>';
			th_data += '<th width="100">평가자</th>';
			th_data += '<th width="220">평가내용</th>';
			th_data += '<th width="80">평점</th>';
			
			if(data.grade_list.length > 0) {
				$.each(data.grade_list, function(index, value) {
					td_data += '<tr class="list-row">';
					td_data += '<td align="center">' + (index+1) + '</td>';
					td_data += '<td align="center">' + value.reg_date + '</td>';
					td_data += '<td align="center"><a href="javascript:show_user_layer(\'' + value.userid + '\')">' + value.userid + '</a></td>';
					td_data += '<td align="center">' + value.content.replace(/(\n|\r\n)/g, '<br>') + '</td>';
					td_data += '<td align="center">' + value.grade + ' 점</td>';
					td_data += '</tr>';
				});
			} else {
				th_data = '<th>평가</th>';
				td_data += '<tr class="list-row">';
				td_data += '<td align="center">평가 내역이 없습니다.</td>';
				td_data += '</tr>';
			}
			
			var html = get_table_html(th_data, td_data);
			$('#ptn_grade_layer table').html(html);
			
			openDialog('평가', "ptn_grade_layer", {"width": "700","height":"400"});
		}, 
		error: function() {
			console.log('error');
		}
	});
}

function go_sale_excel_download(type) {
	var info_seq = $('.chk_one:checked').val();
	if(!info_seq) {
		alert('출력하실 기계를 선택해주세요.');
		return;
	}
	location.href = "/admin/sale/excel_download?type=" + type + "&seq=" + info_seq;
}
function go_osc_excel_download() {
	var osc_seq = $('.chk_one:checked').val();
	if(!osc_seq) {
		alert('출력하실 외주를 선택해주세요.');
		return;
	}
	location.href = "/admin/osc/osc_excel_download?seq=" + osc_seq;
}
function go_ptn_excel_download() {
	var ptn_seq = $('.chk_one:checked').val();
	if(!ptn_seq) {
		alert('출력하실 파트너를 선택해주세요.');
		return;
	}
	location.href = "/admin/osc/ptn_excel_download?seq=" + ptn_seq;
}

function go_osc_contract_move(po_seq) {
	if(confirm('계약 단계로 변경하시겠습니까 ?')) {
		$.ajax({
			type: 'post',
			url: '/admin/main/osc_contract_move',
			dataType: 'json',
			data: {'po_seq': po_seq},
			success: function(data) {
				if(data.result == true) {
					alert('변경이 완료되었습니다.');
					location.reload();
				} else {
					alert('일시적인 에러가 발생하였습니다.');
				}
			},
			error: function() {
				console.log('error');
			}
		});
	}
}

function go_prop_imdbuy_move(prop_seq) {
    if(confirm('즉시구매 단계로 변경하시겠습니까 ?')) {
		$.ajax({
			type: 'post',
			url: '/admin/main/prop_imdbuy_move',
			dataType: 'json',
			data: {'prop_seq': prop_seq},
			success: function(data) {
				if(data.result == true) {
					alert('변경이 완료되었습니다.');
					location.reload();
				} else {
					alert('일시적인 에러가 발생하였습니다.');
				}
			},
			error: function() {
				console.log('error');
			}
		});
	}
}

function send_machine_mail() {
	var chks = $('.member_chk:checked');
	if(chks.length == 0) {
		alert('회원을 선택해주세요.')
	}
	if(confirm('선택한 ' + chks.length + '명의 회원에게 메일을 전송하시겠습니까 ?')) {
		var member_list = '';
		$.each(chks, function(index, value) {
			var no = $(this).val();
			member_list += member_list == '' ? no : ',' + no;
		});
		$.ajax({
			type: 'post',
			url: '/admin/com/send_machinezone_mail',
			dataType: 'json',
			data: {member_list: member_list},
			success: function(data) {
				if(data.result == true) {
					alert('메일 전송이 완료되었습니다.');
					location.reload();
				} else {
					alert('메일 전송 중 에러가 발생했습니다.');
					location.reload();
				}
			},
			error: function() {
				console.log('error');
			}
		});
	}
}