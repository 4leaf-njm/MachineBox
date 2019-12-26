<?
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    
    function send_common_mail($send_email, $title, $subject) {
        $CI =& get_instance();
        $CI->load->library('email');
        $basicinfo	= ($CI->config_basic) ? $CI->config_basic	: config_load('basic');
        
        if	($send_email){
            $file_path	= "../../data/email/".get_lang(true)."/sale_reg.html";
            $CI->template->compile_dir = ROOTPATH."data/email/".get_lang(true)."/";
            $CI->template->assign(array('basic'=>$basicinfo, 'title'=>$title, 'message'=>$subject));
            $CI->template->define(array('tpl'=>$file_path));
            $body = $CI->template->fetch("tpl");
            
            $result = send_email($CI, $send_email, $subject, $body);
            return $result;
        }
        return false;
    }
    
	function sale_reg_mail($send_email, $reg_type){
	    $CI =& get_instance();
	    $CI->load->library('email');
	    $basicinfo	= ($CI->config_basic) ? $CI->config_basic	: config_load('basic');
	    
	    if($reg_type == 'self') {
	        $title = "판매 <b>등록</b> 완료";
	        $subject = "감사합니다. 정상적으로 기계등록이 완료되었습니다. 빠른 판매가 되길 바랍니다.";
	    } else if($reg_type == 'emergency') {
	        $title = "판매 <b>신청</b> 완료";
	        $subject = "감사합니다. 견적판매 신청이 완료되었습니다.";
	    } else if($reg_type == 'direct') {
	        $title = "판매 <b>신청</b> 완료";
	        $subject = "감사합니다. 머박다이렉트 신청이 완료되었습니다.";
	    } else if($reg_type == 'turnkey') {
	        $title = "매각 <b>신청</b> 완료";
	        $subject = "감사합니다. 턴키매각 신청이 완료되었습니다.";
	    }
		if	($send_email){
		    $file_path	= "../../data/email/".get_lang(true)."/sale_reg.html";
		    $CI->template->compile_dir = ROOTPATH."data/email/".get_lang(true)."/";
		    $CI->template->assign(array('basic'=>$basicinfo, 'title'=>$title, 'message'=>$subject));
		    $CI->template->define(array('tpl'=>$file_path));
		    $body = $CI->template->fetch("tpl");
		    
		    $result = send_email($CI, $send_email, $subject, $body);
		    return $result;
		}
		return false;
	}

	function sch_apply_mail($send_email, $apply_type){
	    $CI =& get_instance();
	    $CI->load->library('email');
	    $basicinfo	= ($CI->config_basic) ? $CI->config_basic	: config_load('basic');
	    
	    if($apply_type == 'proposal') {
	        $title = "가격제안 <b>신청</b> 완료";
	        $subject = "가격제안 신청이 완료되었습니다.";
	    } else if($apply_type == 'imd_buy') {
	        $title = "즉시구매 <b>신청</b> 완료";
	        $subject = "즉시구매 신청이 완료되었습니다.";
	    } else if($apply_type == 'visit') {
	        $title = "현장방문 <b>신청</b> 완료";
	        $subject = "현장방문 신청이 완료되었습니다.";
	    } 
	    if	($send_email){
	        $file_path	= "../../data/email/".get_lang(true)."/sale_reg.html";
	        $CI->template->compile_dir = ROOTPATH."data/email/".get_lang(true)."/";
	        $CI->template->assign(array('basic'=>$basicinfo, 'title'=>$title, 'message'=>$subject));
	        $CI->template->define(array('tpl'=>$file_path));
	        $body = $CI->template->fetch("tpl");
	        
	        $result = send_email($CI, $send_email, $subject, $body);
	        return $result;
	    }
	    return false;
	}
	
	function mch_reg_mail($send_email, $reg_type){
	    $CI =& get_instance();
	    $CI->load->library('email');
	    $basicinfo	= ($CI->config_basic) ? $CI->config_basic	: config_load('basic');
	    
	    if($reg_type == 'partner') {
	        $title = "파트너 <b>등록</b> 완료";
	        $subject = "파트너 등록이 완료되었습니다.";
	    } else if($reg_type == 'osc') {
	        $title = "외주 <b>등록</b> 완료";
	        $subject = "외주 등록이 완료되었습니다.";
	    } 
	    if	($send_email){
	        $file_path	= "../../data/email/".get_lang(true)."/sale_reg.html";
	        $CI->template->compile_dir = ROOTPATH."data/email/".get_lang(true)."/";
	        $CI->template->assign(array('basic'=>$basicinfo, 'title'=>$title, 'message'=>$subject));
	        $CI->template->define(array('tpl'=>$file_path));
	        $body = $CI->template->fetch("tpl");
	        
	        $result = send_email($CI, $send_email, $subject, $body);
	        return $result;
	    }
	    return false;
	}
	
	function sale_permit_mail($state, $send_email, $message){
	    $CI =& get_instance();
	    $CI->load->library('email');
	    $basicinfo	= ($CI->config_basic) ? $CI->config_basic	: config_load('basic');
	    
	    if($state == '승인') {
	        $title = "등록한 기계 <b>승인</b>";
	    } else if($state == '미승인') {
	        $title = "등록한 기계 <b>미승인</b>";
	    } else if($state == '보류') {
	        $title = "등록한 기계 <b>보류</b>";
	    } 
	    $subject = $message;
	    if	($send_email){
	        $file_path	= "../../data/email/".get_lang(true)."/sale_reg.html";
	        $CI->template->compile_dir = ROOTPATH."data/email/".get_lang(true)."/";
	        $CI->template->assign(array('basic'=>$basicinfo, 'title'=>$title, 'message'=>$subject));
	        $CI->template->define(array('tpl'=>$file_path));
	        $body = $CI->template->fetch("tpl");
	        
	        $result = send_email($CI, $send_email, $subject, $body);
	        return $result;
	    }
	    return false;
	}
	
    function send_machinezone_mail($send_email) {
        $CI =& get_instance();
        $CI->load->library('email');
        $basicinfo	= ($CI->config_basic) ? $CI->config_basic	: config_load('basic');
        
        if	($send_email){
            $file_path	= "../../data/email/".get_lang(true)."/machine_zone.html";
            $CI->template->compile_dir = ROOTPATH."data/email/".get_lang(true)."/";
            
            $query = "select min(x.sort_price) as min_price, x.* from fm_cm_machine_sales_info A, (select a.sort_price, b.kind_name, f.model_name, g.mnf_name, a.model_year, e.sales_date, c.area_name, d.path, e.type, a.info_seq ".
                 "from fm_cm_machine_sales_info a, fm_cm_machine_kind b, fm_cm_machine_area c, fm_cm_machine_sales_picture d, fm_cm_machine_sales e, fm_cm_machine_model f, fm_cm_machine_manufacturer g where a.kind_seq = b.kind_seq ".
                 "and a.area_seq = c.area_seq and a.info_seq = d.info_seq and a.sales_seq = e.sales_seq and a.model_seq = f.model_seq and a.mnf_seq = g.mnf_seq and d.sort = 2 and a.sort_price != 0 and a.sort_price is not null and ".
                 "e.type != 'direct' and a.state = '승인' and a.test_yn = 'n' order by a.sort_price asc LIMIT 18446744073709551615) as x group by kind_name order by min(x.sort_price) asc limit 6";
            $query = $CI->db->query($query);
            $result = $query->result_array();
            $idx = 0;
            foreach ($result as &$row) {
                $query2 = "select * from fm_cm_machine_sales_detail where info_seq = " . $row['info_seq'];
                $query2 = $CI->db->query($query2);
                $result2 = $query2->row_array();

                if (! empty($result2)) {
                    if ($result2['bid_yn'] == 'y') {
                        unset($result[$idx]);
                    } else {
                        $result[$idx] = array_merge($row, $result2);
                    }
                }
                $idx ++;
            }
            foreach ($result as &$row) {
                $query2 = "select hotmark_list from fm_cm_machine_sales_advertise where ad_name = '핫마크' and (date_format(now(), '%Y-%m-%d') between start_date and end_date) and info_seq = " . $row['info_seq'];
                $query2 = $CI->db->query($query2);
                $result2 = $query2->row_array();
                if (! empty($result2)) {
                    $row = array_merge($row, $result2);
                }
            }
            $zone_list_01 = $result;

            $query = "select * from fm_cm_machine_sales_info a, fm_cm_machine_kind b, fm_cm_machine_area c, fm_cm_machine_sales_picture d, fm_cm_machine_sales e, fm_cm_machine_model f, fm_cm_machine_manufacturer g ".
                "where a.kind_seq = b.kind_seq and a.area_seq = c.area_seq and a.info_seq = d.info_seq and a.sales_seq = e.sales_seq and a.model_seq = f.model_seq and a.mnf_seq = g.mnf_seq ".
                "and d.sort = 2 and a.sort_price != 0 and a.sort_price is not null and e.type != 'direct' and a.state = '승인' and a.test_yn = 'n' order by sales_date desc limit 6";
            $query = $CI->db->query($query);
            $result = $query->result_array();
            $idx = 0;
            foreach ($result as &$row) {
                $query2 = "select * from fm_cm_machine_sales_detail where info_seq = " . $row['info_seq'];
                $query2 = $CI->db->query($query2);
                $result2 = $query2->row_array();

                if (! empty($result2)) {
                    if ($result2['bid_yn'] == 'y') {
                        unset($result[$idx]);
                    } else {
                        $result[$idx] = array_merge($row, $result2);
                    }
                }
                $idx ++;
            }
            foreach ($result as &$row) {
                $query2 = "select hotmark_list from fm_cm_machine_sales_advertise where ad_name = '핫마크' and (date_format(now(), '%Y-%m-%d') between start_date and end_date) and info_seq = " . $row['info_seq'];
                $query2 = $CI->db->query($query2);
                $result2 = $query2->row_array();
                if (! empty($result2)) {
                    $row = array_merge($row, $result2);
                }
            }
            $zone_list_02 = $result;

            $query = "select * from fm_cm_machine_outsourcing a, fm_cm_machine_area b where a.area_seq = b.area_seq and permit_yn = 'y' order by reg_date desc limit 3";
            $query = $CI->db->query($query);
            $result = $query->result_array();

            foreach($result as &$row) {
                $osc_tech = $row['osc_tech'];
                if(!empty($osc_tech)) {
                    $tech_list = explode(',', $osc_tech);
                    $row['tech_list'] = $tech_list;
                }
                $query2 = "select count(*) as apply_cnt from fm_cm_machine_partner_osc where osc_seq = ".$row['osc_seq'];
                $query2 = $CI->db->query($query2);
                $result2 = $query2->row_array();
                $row['apply_cnt'] = $result2['apply_cnt'];
            }
            $zone_list_03 = $result;

            $query = "select * from fm_cm_machine_partner a, fm_cm_machine_area b where a.area_seq = b.area_seq ".
                     "order by (select COALESCE(convert(avg(grade), signed integer), 0) as grade from fm_cm_machine_partner_eval where partner_seq = a.partner_seq) desc limit 4";
            $query = $CI->db->query($query);
            $result = $query->result_array();

            foreach($result as &$row) {
                $query2 = "select COALESCE(convert(avg(grade), signed integer), 0) as grade, count(*) as eval_cnt from fm_cm_machine_partner_eval where partner_seq = ".$row['partner_seq'];
                $query2 = $CI->db->query($query2);
                $result2 = $query2->row_array();
                $row['grade'] = $result2['grade'];
                $row['eval_cnt'] = $result2['eval_cnt'];

                $query2 = "select count(*) as osc_cnt from fm_cm_machine_partner_osc where partner_seq = ".$row['partner_seq'];
                $query2 = $CI->db->query($query2);
                $result2 = $query2->row_array();
                $row['osc_cnt'] = $result2['osc_cnt'];

                $query2 = "select * from fm_cm_machine_partner_certificate where partner_seq = ".$row['partner_seq'];
                $query2 = $CI->db->query($query2);
                $cert_list = $query2->result_array();
                $row['cert_list'] = $cert_list;
            }
            $zone_list_04 = $result;

            $CI->template->assign(array('basic'=>$basicinfo, 'zone_list_01' => $zone_list_01, 'zone_list_02' => $zone_list_02, 'zone_list_03' => $zone_list_03, 'zone_list_04' => $zone_list_04));
            $CI->template->define(array('tpl'=>$file_path));
            $body = $CI->template->fetch("tpl");
            $subject = "머신박스존 업데이트 사항입니다.";
            $result = send_email_02($CI, $send_email, $subject, $body);
            return $result;
        }
        return false;
    }

	function send_email($CI, $send_email, $subject, $body) 
	{
	    $from_email = "help@emachinebox.com";
	    $CI->email->mailtype='html';
	    $CI->email->from($from_email, $from_email, "help@emachinebox.com");
	    $CI->email->to($send_email);
	    $CI->email->subject('머신박스 안내사항 입니다.');
	    $body = str_replace('\\','',http_src($body));
	    $CI->email->message($body);
	    $result = $CI->email->send();
	    $CI->email->clear();
	    return $result;
	}

    function send_email_02($CI, $send_email, $subject, $body) 
	{
	    $from_email = "help@emachinebox.com";
	    $CI->email->mailtype='html';
	    $CI->email->from($from_email, $from_email, "help@emachinebox.com");
	    $CI->email->to($send_email);
	    $CI->email->subject($subject);
	    $body = str_replace('\\','',http_src($body));
	    $CI->email->message($body);
	    $result = $CI->email->send();
	    $CI->email->clear();
	    return $result;
	}
?>