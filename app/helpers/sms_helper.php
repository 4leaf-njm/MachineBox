<?
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    
    function send_common_sms($phone, $message) {
        $CI =& get_instance();
        $msg = "[머신박스] ".$message;
        send_sms($CI, $msg, $phone);
    }
    
	function sale_reg_sms($phone, $reg_type){
	    $CI =& get_instance();
		if	($phone){
		    if($reg_type == 'self') {
    		    $msg = "[머신박스] 감사합니다. 정상적으로 기계등록이 완료되었습니다. 빠른 판매가 되길 바랍니다.";
		    } else if($reg_type == 'emergency') {
		        $msg = "[머신박스] 감사합니다. 견적판매 신청이 완료되었습니다.";
		    } else if($reg_type == 'direct') {
		        $msg = "[머신박스] 감사합니다. 머박다이렉트 신청이 완료되었습니다.";
		    }
		    send_sms($CI, $msg, $phone);
		}
	}

	function sch_apply_sms($phone, $apply_type){
	    $CI =& get_instance();
	    if	($phone){
	        if($apply_type == 'proposal') {
	            $msg = "[머신박스] 가격제안 신청이 완료되었습니다.";
	        } else if($apply_type == 'imd_buy') {
	            $msg = "[머신박스] 즉시구매 신청이 완료되었습니다.";
	        } else if($apply_type == 'visit') {
	            $msg = "[머신박스] 현장방문 신청이 완료되었습니다.";
	        }
	        send_sms($CI, $msg, $phone);
	    }
	}
	
	function mch_reg_sms($phone, $reg_type){
	    $CI =& get_instance();
	    if	($phone){
	        if($reg_type == 'partner') {
	            $msg = "[머신박스] 파트너 등록이 완료되었습니다.";
	        } else if($reg_type == 'osc') {
	            $msg = "[머신박스] 외주 등록이 완료되었습니다.";
	        } 
	        send_sms($CI, $msg, $phone);
	    }
	}
	
	function sale_permit_sms($state, $phone, $message){
	    $CI =& get_instance();
	    if	($phone){
	        if($state == '승인') {
	            $msg = "[머신박스] ".$message;
	        } else if($state == '미승인') {
	            $msg = "[머신박스] ".$message;
	        } else if($state == '보류') {
	            $msg = "[머신박스] ".$message;
	        }
	        send_sms($CI, $msg, $phone);
	    }
	}
	
	function send_sms($CI, $msg, $phone){
	    require_once ROOTPATH."/app/libraries/sms.class.php";
	    $auth = config_load('master');
	    $sms_id = $CI->config_system['service']['sms_id'];
	    $sms_api_key = $auth['sms_auth'];
	    $gabiaSmsApi = new gabiaSmsApi($sms_id,$sms_api_key);
	    $gabiaSmsApi->sendSMS_Msg($msg, $phone);
	}
	
?>