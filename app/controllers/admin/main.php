<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class main extends admin_base {

	public function __construct() {
		parent::__construct();

		$this->load->model('membermodel');
		$this->load->helper(array('form', 'url', 'mail', 'sms'));
		
		$this->cach_file_path	= $_SERVER['DOCUMENT_ROOT'] . '/data/cach/';
		$this->cach_file_url		= '../../data/cach/';
		$this->cach_file_name	= 'admin_main_index.html';

		// 운영자별 페이지 생성
		$this->cach_stat_file	= 'admin_main_stats_'.$this->managerInfo['manager_id'].'.html';
	}

	public function main_index()
	{
		redirect("/admin/main/index");
	}
    
	public function get_state_list() {
	    header("Content-Type: application/json");
	    
	    $type = $this->input->post('type');
	    $date_s = $this->input->post('date_s');
	    $date_f = $this->input->post('date_f');
	    
	    $count_obj = array();
	    if($type == '등록기계') {
	        $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_kind c, fm_cm_machine_manufacturer d, fm_cm_machine_model e ".
	   	        "where a.sales_seq = b.sales_seq and b.kind_seq = c.kind_seq and b.mnf_seq = d.mnf_seq and b.model_seq = e.model_seq and state = '승인' and sales_yn = 'n' ".$this->get_date_where_query('sales_date', $date_s, $date_f).
	            "order by sales_date desc";
	        $query = $this->db->query($query);
	        $list = $query->result_array();
	        
	        $total_price = 0;
	        $self_total_count = 0;
	        $self_total_price = 0;
	        $direct_total_count = 0;
	        $direct_total_price = 0;
	        $emergency_total_count = 0;
	        $emergency_total_price = 0;
	        $turnkey_total_count = 0;
	        $turnkey_total_price = 0;
	        
	        foreach($list as $row) {
	            $total_price += $row['sort_price'];
	            if($row['type'] == 'self') {
	                $self_total_count ++;
	                $self_total_price += $row['sort_price'];
	            } else if($row['type'] == 'direct') {
	                $direct_total_count ++;
	                $direct_total_price += $row['sort_price'];
	            } else if($row['type'] == 'emergency') {
	                $emergency_total_count ++;
	                $emergency_total_price += $row['sort_price'];
	            } else if($row['type'] == 'turnkey') {
	                $turnkey_total_count ++;
	                $turnkey_total_price += $row['sort_price'];
	            } 
	        }
	        $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_kind c, fm_cm_machine_manufacturer d, fm_cm_machine_model e ".
	   	        "where a.sales_seq = b.sales_seq and b.kind_seq = c.kind_seq and b.mnf_seq = d.mnf_seq and b.model_seq = e.model_seq and state = '승인' and sales_yn = 'n' ".$this->get_date_where_query('sales_date', $date_s, $date_f).
	   	        "group by b.kind_seq order by b.kind_seq asc";
	        $query = $this->db->query($query);
	        $result = $query->result_array();
	        $count_list = array();
	        $head_list = array();
	        $body_list = array();
	        foreach($result as $row) {
	            $query = "select count(*) as cnt from fm_cm_machine_sales a, fm_cm_machine_sales_info b where a.sales_seq = b.sales_seq and state = '승인' and sales_yn = 'n' ".$this->get_date_where_query('sales_date', $date_s, $date_f)."and kind_seq = ".$row['kind_seq'];
	            $query = $this->db->query($query);
	            $result = $query->row_array();
	            $head_list[] = $row['kind_name'];
	            $body_list[] = $result['cnt'];
	        }
	        $count_list['head_list'] = $head_list;
	        $count_list['body_list'] = $body_list;
	        $count_obj['total_count'] = count($list);
	        $count_obj['total_price'] = $total_price;
	        $count_obj['self_total_count'] = $self_total_count;
	        $count_obj['self_total_price'] = $self_total_price;
	        $count_obj['direct_total_count'] = $direct_total_count;
	        $count_obj['direct_total_price'] = $direct_total_price;
	        $count_obj['emergency_total_count'] = $emergency_total_count;
	        $count_obj['emergency_total_price'] = $emergency_total_price;
	        $count_obj['turnkey_total_count'] = $turnkey_total_count;
	        $count_obj['turnkey_total_price'] = $turnkey_total_price;
	    } else if ($type == '판매기계') {
	        $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_kind c, fm_cm_machine_manufacturer d, fm_cm_machine_model e ".
	   	        "where a.sales_seq = b.sales_seq and b.kind_seq = c.kind_seq and b.mnf_seq = d.mnf_seq and b.model_seq = e.model_seq and sales_yn = 'y' ".$this->get_date_where_query('sales_finish_date', $date_s, $date_f).
	   	        "order by sales_date desc";
	        $query = $this->db->query($query);
	        $list = $query->result_array();
	        $total_price = 0;
	        $self_total_count = 0;
	        $self_total_price = 0;
	        $direct_total_count = 0;
	        $direct_total_price = 0;
	        $emergency_total_count = 0;
	        $emergency_total_price = 0;
	        $turnkey_total_count = 0;
	        $turnkey_total_price = 0;
	        
	        foreach($list as $row) {
	            $total_price += $row['sales_price'];
	            if($row['type'] == 'self') {
	                $self_total_count ++;
	                $self_total_price += $row['sales_price'];
	            } else if($row['type'] == 'direct') {
	                $direct_total_count ++;
	                $direct_total_price += $row['sales_price'];
	            } else if($row['type'] == 'emergency') {
	                $emergency_total_count ++;
	                $emergency_total_price += $row['sales_price'];
	            } else if($row['type'] == 'turnkey') {
	                $turnkey_total_count ++;
	                $turnkey_total_price += $row['sort_price'];
	            } 
	        }
	        $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_kind c, fm_cm_machine_manufacturer d, fm_cm_machine_model e ".
	   	        "where a.sales_seq = b.sales_seq and b.kind_seq = c.kind_seq and b.mnf_seq = d.mnf_seq and b.model_seq = e.model_seq and sales_yn = 'y' ".$this->get_date_where_query('sales_finish_date', $date_s, $date_f).
	   	        "group by b.kind_seq order by b.kind_seq asc";
	        $query = $this->db->query($query);
	        $result = $query->result_array();
	        $count_list = array();
	        $head_list = array();
	        $body_list = array();
	        foreach($result as $row) {
	            $query = "select count(*) as cnt from fm_cm_machine_sales a, fm_cm_machine_sales_info b where a.sales_seq = b.sales_seq and sales_yn = 'y' ".$this->get_date_where_query('sales_finish_date', $date_s, $date_f)."and kind_seq = ".$row['kind_seq'];
	            $query = $this->db->query($query);
	            $result = $query->row_array();
	            $head_list[] = $row['kind_name'];
	            $body_list[] = $result['cnt'];
	        }
	        $count_list['head_list'] = $head_list;
	        $count_list['body_list'] = $body_list;
	        $count_obj['total_count'] = count($list);
	        $count_obj['total_price'] = $total_price;
	        $count_obj['self_total_count'] = $self_total_count;
	        $count_obj['self_total_price'] = $self_total_price;
	        $count_obj['direct_total_count'] = $direct_total_count;
	        $count_obj['direct_total_price'] = $direct_total_price;
	        $count_obj['emergency_total_count'] = $emergency_total_count;
	        $count_obj['emergency_total_price'] = $emergency_total_price;
	        $count_obj['turnkey_total_count'] = $turnkey_total_count;
	        $count_obj['turnkey_total_price'] = $turnkey_total_price;
	    } else if ($type == '현장미팅') {
	        $query = "select *, a.userid as sale_userid, c.userid as buy_userid, b.state as sale_state, c.state as visit_state from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_visit c ".
	   	        "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and b.state != '등록취소' ".$this->get_date_where_query('reg_date', $date_s, $date_f).
	   	        "order by reg_date desc";
	        $query = $this->db->query($query);
	        $list = $query->result_array();
	        
	        foreach($list as &$row) {
	            $query = "select * from fm_cm_machine_visit_detail where select_yn = 'y' and visit_seq = ".$row['visit_seq'];
	            $query = $this->db->query($query);
	            $result = $query->row_array();
	            if(!empty($result)) {
    	            $row['hope_date'] = $result['hope_date']." ".$result['hope_time'];
	            }
	        }
	        $count_obj['total_count'] = count($list);
	    } else if($type == '프리미엄광고') {
	        $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_sales_advertise c where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and b.state != '등록취소' ".$this->get_date_where_query('c.reg_date', $date_s, $date_f).
	        "group by b.info_seq order by sales_date desc";
	        $query = $this->db->query($query);
	        $list = $query->result_array();
	        
	        $count_list = array();
	        $head_list = array('하이라이트', '딜러존', '자동 업데이트', '핫마크');
	        $body_list = array('0', '0', '0', '0');
	        $total_count = 0;
	        $total_price = 0;
	        foreach($list as &$row) {
	            $query = "select * from fm_cm_machine_sales_advertise where info_seq = ".$row['info_seq'];
	            $query = $this->db->query($query);
	            $result = $query->result_array();
	            $ad_list = "";
	            foreach($result as $row2) {
	                $ad_list .= $ad_list == "" ? $row2['ad_name'] : ", ".$row2['ad_name'];
	                for($idx=0; $idx<count($head_list); $idx++) {
	                    if($head_list[$idx] == $row2['ad_name'])
	                        $body_list[$idx] = (int)$body_list[$idx] + 1;
	                }
	                $total_count ++;
	                $total_price += $row2['price'];
	            }
	            $row['ad_list'] = $ad_list;
	        }
	        $count_list['head_list'] = $head_list;
	        $count_list['body_list'] = $body_list;
	        $count_obj['total_count'] = $total_count;
	        $count_obj['total_price'] = $total_price;
	    } else if($type == '성능평가') {
	        $count_list = array();
	        $head_list = array('성능검사', '기계평가3', '기계평가5');
	        $body_list = array('0', '0', '0');
	        $total_count = 0;
	        $total_price = 0;
	        $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_perform c where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and b.state != '등록취소' ".$this->get_date_where_query('c.reg_date', $date_s, $date_f).
	           "order by sales_date desc";
	        $query = $this->db->query($query);
	        $perform_list = $query->result_array();
	        
	        $total_count += count($perform_list);
	        $body_list[0] = count($perform_list);
	        
	        foreach($perform_list as &$row) {
	           $row['service_name'] = '성능검사';   
	           $total_price += '150000';
	        }
	        $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_online_eval c where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and b.state != '등록취소' ".$this->get_date_where_query('c.reg_date', $date_s, $date_f).
	           "order by sales_date desc";
	        $query = $this->db->query($query);
	        $eval_list = $query->result_array();
	        
	        $total_count += count($eval_list);
	        foreach($eval_list as &$row) {
	            if($row['eval_name'] == '온라인 기계평가 3') {
	               $body_list[1] = (int)$body_list[1] + 1;
	               $row['service_name'] = '온라인 기계평가 3';   
	               $total_price += '30000';
	            }
	            if($row['eval_name'] == '온라인 기계평가 5') {
                   $body_list[2] = (int)$body_list[2] + 1;
                   $row['service_name'] = '온라인 기계평가 5';   
                   $total_price += '50000';
	            }
	        }
	        $list = array_merge($perform_list, $eval_list);
	        
	        $count_list['head_list'] = $head_list;
	        $count_list['body_list'] = $body_list;
	        $count_obj['total_count'] = $total_count;
	        $count_obj['total_price'] = $total_price;
	    } else if($type == '배송대행') {
	        $query = "select * from fm_cm_machine_delivery where 1=1 ".$this->get_date_where_query('reg_date', $date_s, $date_f).
	   	        "order by reg_date desc";
	        $query = $this->db->query($query);
	        $list = $query->result_array();
	        
	        $total_price = 0;
	        
	        $count_list = array();
	        $head_list = array('상차', '하차', '배송', '해체/설치', '보관', '기계수리', '전기수리', '기타');
	        $body_list = array('0', '0', '0', '0', '0', '0', '0', '0');
	        foreach($list as $row) {
	            $service_list = explode(", ", $row['service_list']);
	            foreach($service_list as $value) {
	                if(strpos($value, "기타") !== false)
	                    $value = '기타';
    	            for($idx=0; $idx<count($head_list); $idx++) {
    	                if($head_list[$idx] == $value)
    	                    $body_list[$idx] = (int)$body_list[$idx] + 1;
    	            }
	            }
	            $total_price += $row['pay_price'];
	        }
	        $count_list['head_list'] = $head_list;
	        $count_list['body_list'] = $body_list;
	        $count_obj['total_count'] = count($list);
	        $count_obj['total_price'] = $total_price;
	    } else if ($type == '외주신청') {
	        $query = "select * from fm_cm_machine_outsourcing where finish_yn = 'n' ".$this->get_date_where_query('reg_date', $date_s, $date_f).
	           "order by reg_date desc";
	        $query = $this->db->query($query);
	        $list = $query->result_array();
	        
	        $total_price = 0;
	        foreach($list as &$row) {
	            $total_price += $row['budget'];
	            
	            $query2 = "select count(*) as apply_cnt from fm_cm_machine_partner_osc where osc_seq = ".$row['osc_seq'];
	            $query2 = $this->db->query($query2);
	            $result = $query2->row_array();
	            $row['apply_cnt'] = $result['apply_cnt'];
	            
	            $query2 = "select * from fm_cm_machine_partner_osc a, fm_cm_machine_partner b where a.partner_seq = b.partner_seq and osc_seq = ".$row['osc_seq'];
	            $query2 = $this->db->query($query2);
	            $result = $query2->result_array();
	            $row['apply_list'] = $result;
	        }
	        $count_obj['total_count'] = count($list);
	        $count_obj['total_price'] = $total_price;
	    } else if ($type == '수주신청') {
	        $query = "select *, b.reg_date as date, b.state as partner_state, c.state as osc_state, a.userid as partner_id, c.userid as osc_id from fm_cm_machine_partner a, fm_cm_machine_partner_osc b, fm_cm_machine_outsourcing c where a.partner_seq = b.partner_seq and b.osc_seq = c.osc_seq ".$this->get_date_where_query('b.reg_date', $date_s, $date_f).
	        "order by b.reg_date desc";
	        $query = $this->db->query($query);
	        $list = $query->result_array();
	        
	        $count_obj['total_count'] = count($list);
	    } else if ($type == '외주완료') {
	        $query = "select * from fm_cm_machine_outsourcing where finish_yn = 'y' ".$this->get_date_where_query('finish_date', $date_s, $date_f).
	        "order by reg_date desc";
	        $query = $this->db->query($query);
	        $list = $query->result_array();
	        
	        $total_price = 0;
	        foreach($list as &$row) {
	            $total_price += $row['budget'];
	            
	            $query2 = "select count(*) as apply_cnt from fm_cm_machine_partner_osc where osc_seq = ".$row['osc_seq'];
	            $query2 = $this->db->query($query2);
	            $result = $query2->row_array();
	            $row['apply_cnt'] = $result['apply_cnt'];
	            
	            $query2 = "select * from fm_cm_machine_partner_osc a, fm_cm_machine_partner b where a.partner_seq = b.partner_seq and osc_seq = ".$row['osc_seq'];
	            $query2 = $this->db->query($query2);
	            $result = $query2->result_array();
	            $row['apply_list'] = $result;
	        }
	        $count_obj['total_count'] = count($list);
	        $count_obj['total_price'] = $total_price;
	    } else if ($type == '회원') {
	        $key = get_shop_key();
	        
	        $sqlSelectClause = "
				select
					A.member_seq,A.userid,A.user_name,A.nickname,A.mailing,A.sms,A.emoney,A.point,A.cash,A.regist_date,A.lastlogin_date,A.review_cnt,A.login_cnt,A.birthday,A.zipcode,A.address_street,A.address_type,A.address,A.address_detail,A.sns_f,A.anniversary,A.recommend,A.sex,A.mtype,
					AES_DECRYPT(UNHEX(A.email), '{$key}') as email,
					AES_DECRYPT(UNHEX(A.phone), '{$key}') as phone,
					AES_DECRYPT(UNHEX(A.cellphone), '{$key}') as cellphone,
					CASE WHEN A.status = 'done' THEN '승인'
						 WHEN A.status = 'hold' THEN '미승인'
						 WHEN A.status = 'withdrawal' THEN '탈퇴'
						 WHEN A.status = 'dormancy' THEN '휴면'
					ELSE '' END AS status_nm, A.mall_t_check,
					B.bname, B.bphone, B.bcellphone, B.business_seq, B.baddress_type, B.baddress, B.baddress_detail,
					B.bzipcode, B.bceo, B.bno, B.bitem,
					B.bstatus, B.bperson, B.bpart,
					A.member_order_cnt,A.member_order_price,A.member_recommend_cnt ,A.member_invite_cnt,
					A.referer, A.referer_domain,
					IF(C.referer_group_no>0, C.referer_group_name, IF(LENGTH(A.referer)>0,'기타','직접입력')) as referer_name,
					A.group_seq,D.group_name,
					A.rute,
					A.sns_change,
					A.blacklist,
					CASE WHEN length(A.sns_n) >= '10'
						THEN concat(left(A.sns_n, 10 - 1),'*n')
						ELSE concat(left(A.sns_n, length(A.sns_n) - 1),'*n')
					END AS conv_sns_n
			";
	        $sqlFromClause = "
    			from
    				fm_member A
    				LEFT JOIN fm_member_business B ON A.member_seq = B.member_seq
    				LEFT JOIN fm_referer_group C ON A.referer_domain = C.referer_group_url
    				LEFT JOIN fm_member_group D ON A.group_seq = D.group_seq
    		";
	        $sqlWhereClause = "
    			where A.status in ('done','hold','dormancy') ";
	        $sqlOrderClause = "order by A.regist_date desc";

	        $query = "
    			{$sqlSelectClause}
    			{$sqlFromClause}
    			{$sqlWhereClause}
    			{$sqlOrderClause}
    		";
			$query = $this->db->query($query);
			$list = $query->result_array();
			$count_obj['total_count'] = count($list);
			
	        $sqlWhereClause .= $this->get_date_where_query('A.regist_date', $date_s, $date_f)." ";
	        $query = "
    			{$sqlSelectClause}
    			{$sqlFromClause}
    			{$sqlWhereClause}
    			{$sqlOrderClause}
    		";
	        $query = $this->db->query($query);
	        $list = $query->result_array();
	        
	        foreach($list as &$row) {
	            $query = "select * from fm_member_business where member_seq = ".$row['member_seq'];
	            $query = $this->db->query($query);
	            $result = $query->row_array();
	            $row['business_seq'] = $result['business_seq'];
	            $row['bpermit_yn'] = $result['bpermit_yn'];
	            $row['bcard_path'] = $result['bcard_path'];
	            
	            if($row['mtype'] == 'business'){
	                $row['type'] = '기업';
	            
    	            $query = "select label_value as gubun from fm_member_subinfo where label_title = '회원구분' and member_seq = ".$row['member_seq'];
    	            $query = $this->db->query($query);
    	            $row['gubun'] = $query->row()->gubun == '기업회원' ? '기업' : '딜러';
	            } else {
	                $row['type'] = '개인';
	                $row['gubun'] = '개인';
	            }
	        }
	        $count_obj['regist_count'] = count($list);
	        
	        $query2 = "select * from fm_stats_visitor_count where count_type = 'visit' ".$this->get_date_where_query('stats_date', $date_s, $date_f);
	        $query2 = $this->db->query($query2);
	        $visit_list = $query2->result_array();
	        $visit_count = 0;
	        foreach($visit_list as $visit) {
	            $visit_count += $visit['count_sum'];
	        }
	        $count_obj['visit_count'] = $visit_count;
	    }
	    echo json_encode(array('type' => $type, 'date_s' => $date_s, 'date_f' => $date_f, 'count_obj' => $count_obj, 'count_list' => $count_list, 'list' => $list));
	}
	
	public function get_main_list() {
	    header("Content-Type: application/json");
	    
	    $title = $this->input->post('title');
	    $type = $this->input->post('type');
	    
	    if($title == '신청승인') {
	        if($type == '판매등록') {
	            $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_kind c,".
	   	            "fm_cm_machine_manufacturer d, fm_cm_machine_model e, fm_cm_machine_area f ".
	   	            "where a.sales_seq = b.sales_seq and b.kind_seq = c.kind_seq and b.mnf_seq = d.mnf_seq ".
	   	            "and b.model_seq = e.model_seq and b.area_seq = f.area_seq and (state in('미승인', '보류') or wait_yn = 'y') ".
	   	            "order by sales_date desc, b.info_seq asc";
	            $query = $this->db->query($query);
	            $list = $query->result_array();
	            
	            foreach($list as &$row) {
	                $query2 = "select * from fm_cm_machine_sales_picture where info_seq = ".$row['info_seq']." ".
	   	                "order by sort asc";
	                $query2 = $this->db->query($query2);
	                $result = $query2->result_array();
	                $row['picture_list'] = $result;
	                
	                $query2 = "select * from fm_cm_machine_sales_check where sales_seq = ".$row['sales_seq'];
	                $query2 = $this->db->query($query2);
	                $result = $query2->row_array();
	                $row['check_list'] = $result;
	            }
	            $data = array(
	                'admin_view_yn' => 'y'
	            );
                $this->db->where_in('state', array('미승인', '보류'))
                         ->or_where('wait_yn', 'y');
	            $this->db->update('fm_cm_machine_sales_info', $data);
	        } else if ($type == '현장미팅') {
	            $query = "select *, a.userid as sale_userid, c.userid as buy_userid, b.state as sale_state, c.state as visit_state from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_visit c ".
	   	             "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and c.admin_yn = 'n' and b.state != '등록취소' ".
	   	             "order by c.reg_date desc";
	            $query = $this->db->query($query);
	            $list = $query->result_array();
	            
	            foreach($list as &$row) {
	                $query = "select * from fm_cm_machine_visit_detail where visit_seq = ".$row['visit_seq'];
	                $query = $this->db->query($query);
					$result = $query->result_array();
					$hope_date = '';
					foreach($result as $row2) {
						$hope_date .= $row2['hope_date']." ".$row2['hope_time']."<br/>";
					}
					$row['hope_date'] = $hope_date;
	            }
	            
                $data = array(
                    'admin_view_yn' => 'y'
                );
                $this->db->where('admin_yn', 'n');
                $this->db->update('fm_cm_machine_visit', $data);
	        } else if ($type == '가격제안') {
	            $query = "select *, a.userid as sale_userid, c.userid as buy_userid from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_proposal c ".
	   	            "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and c.admin_yn = 'n' and b.state != '등록취소' ".
	   	            "order by c.reg_date desc";
	            $query = $this->db->query($query);
	            $list = $query->result_array();
	            foreach($list as &$row) {
	                $query = "select count(*) as cnt from fm_cm_machine_proposal where info_seq = ".$row['info_seq'];
	                $query = $this->db->query($query);
	                $total_count = $query->row_array()['cnt'];
	                
	                $query = "select count(*) as cnt from fm_cm_machine_proposal where (permit_yn = 'y' or (permit_yn = 'c' and counter_permit_yn = 'y')) and info_seq = ".$row['info_seq'];
	                $query = $this->db->query($query);
	                $permit_count = $query->row_array()['cnt'];
	                
	                $percent = $permit_count / $total_count * 100;
	                $row['permit_percent'] = (int)$percent."%";
	            }
	            $data = array(
	                'admin_view_yn' => 'y'
	            );
	            $this->db->where('admin_yn', 'n');
	            $this->db->update('fm_cm_machine_proposal', $data);
	        } else if ($type == '즉시구매') {
	            $query = "select *, a.userid as sale_userid, c.userid as buy_userid from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_imdbuy c ".
	   	            "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and c.permit_yn = 'n' and b.state != '등록취소' ".
	   	            "order by c.reg_date desc";
	            $query = $this->db->query($query);
	            $list = $query->result_array();
	            
	            $data = array(
	                'admin_view_yn' => 'y'
	            );
                $this->db->where('permit_yn', 'n');
	            $this->db->update('fm_cm_machine_imdbuy', $data);
	        } else if ($type == '낙찰내역') {
	            $query = "select *, a.userid as sale_userid, d.userid as buy_userid from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_sales_detail c, fm_cm_machine_bid d ".
	   	            "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and b.info_seq = d.info_seq and d.bid_yn = 'y' and d.admin_yn = 'n' and b.state != '등록취소' ".
	   	            "order by d.reg_date desc";
	            $query = $this->db->query($query);
	            $list = $query->result_array();
	            
	            $data = array(
	                'admin_view_yn' => 'y'
	            );
                $this->db->where('bid_yn', 'y')
                        ->where('admin_yn', 'n');
	            $this->db->update('fm_cm_machine_bid', $data);
	        } else if ($type == '계약') {
	            $query = "select *, reg_date as cont_reg_date from fm_cm_machine_contract where write_yn = 'n' order by reg_date desc";
	            $query = $this->db->query($query);
	            $list = $query->result_array();
	            foreach($list as &$row) {
                    if($row['cont_type'] == '판매') {
                        $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b where a.sales_seq = b.sales_seq and b.info_seq = ".$row['target_seq'];
	                    $query = $this->db->query($query);
	                    $result = $query->row_array();
                        $row = array_merge($row, $result);
                    } else if ($row['cont_type'] == '외주') {
                        $query = "select * from fm_cm_machine_outsourcing a, fm_cm_machine_partner_osc b where a.osc_seq = b.osc_seq and b.po_seq = ".$row['target_seq'];
	                    $query = $this->db->query($query);
	                    $result = $query->row_array();
                        $row = array_merge($row, $result);
                    }
                }
	            $data = array(
	                'admin_view_yn' => 'y'
	            );
                $this->db->where('write_yn', 'n');
	            $this->db->update('fm_cm_machine_contract', $data);
	        } else if ($type == '문의') {
	            $query = "select *, a.userid as sale_userid, c.userid as buy_userid from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_question c ".
	   	            "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and c.send_yn = 'n' and b.state != '등록취소' ".
	   	            "order by c.reg_date desc";
	            $query = $this->db->query($query);
	            $list = $query->result_array();
	            
	            $data = array(
	                'admin_view_yn' => 'y'
	            );
                $this->db->where('send_yn', 'n');
	            $this->db->update('fm_cm_machine_question', $data);
	        } else if ($type == '외주신청') {
	            $query = "select * from fm_cm_machine_outsourcing ".
	   	            "where permit_yn = 'n' ".
	   	            "order by reg_date desc";
	            $query = $this->db->query($query);
	            $list = $query->result_array();

	            foreach($list as &$row) {
	                $query2 = "select count(*) as apply_cnt from fm_cm_machine_partner_osc where osc_seq = ".$row['osc_seq'];
	                $query2 = $this->db->query($query2);
	                $result = $query2->row_array();
	                $row['apply_cnt'] = $result['apply_cnt'];
	                
	                $query2 = "select * from fm_cm_machine_partner_osc a, fm_cm_machine_partner b where a.partner_seq = b.partner_seq and osc_seq = ".$row['osc_seq'];
	                $query2 = $this->db->query($query2);
	                $result = $query2->result_array();
	                $row['apply_list'] = $result;
	            }
	            
	            $data = array(
	                'admin_view_yn' => 'y'
	            );
                $this->db->where('permit_yn', 'n');
	            $this->db->update('fm_cm_machine_outsourcing', $data);
	        } else if ($type == '수주신청') {
	            $query = "select *, b.reg_date as apply_date, a.userid as ptn_userid, c.userid as osc_userid from fm_cm_machine_partner a, fm_cm_machine_partner_osc b, fm_cm_machine_outsourcing c ".
	   	            "where a.partner_seq = b.partner_seq and b.osc_seq = c.osc_seq and meet_state = 0 and admin_yn = 'n' ".
	   	            "order by b.reg_date desc";
	            $query = $this->db->query($query);
	            $list = $query->result_array();
	            
	            $data = array(
	                'admin_ptn_view_yn' => 'y'
	            );
                $this->db->where('admin_yn', 'n')
                         ->where('meet_state', '0');
	            $this->db->update('fm_cm_machine_partner_osc', $data);
	        } else if ($type == '외주미팅신청') {
	            $query = "select *, a.userid as ptn_userid, c.userid as osc_userid from fm_cm_machine_partner a, fm_cm_machine_partner_osc b, fm_cm_machine_outsourcing c ".
	   	            "where a.partner_seq = b.partner_seq and b.osc_seq = c.osc_seq and b.state in ('0', '1') and b.meet_state != '0' and meet_admin_yn = 'n' ".
	   	            "order by b.meet_date desc";
	            $query = $this->db->query($query);
	            $list = $query->result_array();
	            
	            foreach($list as &$row) {
	                $query2 = "select count(*) as apply_cnt from fm_cm_machine_partner_osc where osc_seq = ".$row['osc_seq'];
	                $query2 = $this->db->query($query2);
	                $result = $query2->row_array();
	                $row['apply_cnt'] = $result['apply_cnt'];
	                
	                $query2 = "select * from fm_cm_machine_partner_osc a, fm_cm_machine_partner b where a.partner_seq = b.partner_seq and osc_seq = ".$row['osc_seq'];
	                $query2 = $this->db->query($query2);
	                $result = $query2->result_array();
	                $row['apply_list'] = $result;
	            }
	            
	            $data = array(
	                'admin_meet_view_yn' => 'y'
	            );
	            $this->db->where('meet_admin_yn', 'n')
                         ->where_in('state', array('0', '1'))
                         ->where('meet_state !=', '0');
	            $this->db->update('fm_cm_machine_partner_osc', $data);
	        } else if ($type == '배송대행') {
	            $query = "select * from fm_cm_machine_delivery where pay_state = '승인대기' order by reg_date desc";
	            $query = $this->db->query($query);
	            $list = $query->result_array();
	            
	            $data = array(
	                'admin_view_yn' => 'y'
	            );
                $this->db->where('pay_state', '승인대기');
	            $this->db->update('fm_cm_machine_delivery', $data);
	        } else if ($type == '회원') {
				$key = get_shop_key();
	    
				$sqlSelectClause = "
						select
							A.member_seq,A.userid,A.user_name,A.nickname,A.mailing,A.sms,A.emoney,A.point,A.cash,A.regist_date,A.lastlogin_date,A.review_cnt,A.login_cnt,A.birthday,A.zipcode,A.address_street,A.address_type,A.address,A.address_detail,A.sns_f,A.anniversary,A.recommend,A.sex,A.mtype,
							AES_DECRYPT(UNHEX(A.email), '{$key}') as email,
							AES_DECRYPT(UNHEX(A.phone), '{$key}') as phone,
							AES_DECRYPT(UNHEX(A.cellphone), '{$key}') as cellphone,
							CASE WHEN A.status = 'done' THEN '승인'
								WHEN A.status = 'hold' THEN '미승인'
								WHEN A.status = 'withdrawal' THEN '탈퇴'
								WHEN A.status = 'dormancy' THEN '휴면'
							ELSE '' END AS status_nm, A.mall_t_check,
							B.bname, B.bphone, B.bcellphone, B.business_seq, B.baddress_type, B.baddress, B.baddress_detail,
							B.bzipcode, B.bceo, B.bno, B.bitem,
							B.bstatus, B.bperson, B.bpart,
							B.bpermit_yn, B.main_dealer_yn, B.bcard_path,
							A.member_order_cnt,A.member_order_price,A.member_recommend_cnt ,A.member_invite_cnt,
							A.referer, A.referer_domain,
							IF(C.referer_group_no>0, C.referer_group_name, IF(LENGTH(A.referer)>0,'기타','직접입력')) as referer_name,
							A.group_seq,D.group_name,
							A.rute,
							A.sns_change,
							A.blacklist,
							CASE WHEN length(A.sns_n) >= '10'
								THEN concat(left(A.sns_n, 10 - 1),'*n')
								ELSE concat(left(A.sns_n, length(A.sns_n) - 1),'*n')
							END AS conv_sns_n
					";
				$sqlFromClause = "
						from
							fm_member A
							LEFT JOIN fm_member_business B ON A.member_seq = B.member_seq
							LEFT JOIN fm_referer_group C ON A.referer_domain = C.referer_group_url
							LEFT JOIN fm_member_group D ON A.group_seq = D.group_seq
					";
				$sqlWhereClause = "
						where A.status in ('done','hold','dormancy') and (B.bpermit_yn = 'n' or B.main_dealer_yn = 'h') ";
				$sqlOrderClause = "order by A.regist_date desc";
				
				$query = "
					{$sqlSelectClause}
					{$sqlFromClause}
					{$sqlWhereClause}
					{$sqlOrderClause}
				";
				$query = $this->db->query($query);
				$list = $query->result_array();
				
				foreach($list as &$row) {
					if($row['mtype'] == 'business'){
						$row['type'] = '기업';
						
						$query = "select label_value as gubun from fm_member_subinfo where label_title = '회원구분' and member_seq = ".$row['member_seq'];
						$query = $this->db->query($query);
						$row['gubun'] = $query->row()->gubun == '기업회원' ? '기업' : '딜러';
					} else {
						$row['type'] = '개인';
						$row['gubun'] = '개인';
					}
				}

				$data = array(
	                'admin_view_yn' => 'y'
	            );
				$this->db->where('bpermit_yn', 'n')
				         ->or_where('main_dealer_yn', 'h');
	            $this->db->update('fm_member_business', $data);
			}
	    } else if ($title == '진행현황') {
	        if($type == '판매등록') {
	            $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_kind c,".
	   	            "fm_cm_machine_manufacturer d, fm_cm_machine_model e, fm_cm_machine_area f ".
	   	            "where a.sales_seq = b.sales_seq and b.kind_seq = c.kind_seq and b.mnf_seq = d.mnf_seq ".
	   	            "and b.model_seq = e.model_seq and b.area_seq = f.area_seq and sales_yn = 'n' and state in('승인', '입금대기', '계약대기') and wait_yn = 'n' ".
	   	            "order by state_date desc, b.info_seq asc";
	            $query = $this->db->query($query);
	            $list = $query->result_array();
	            
	            foreach($list as &$row) {
	                $query2 = "select * from fm_cm_machine_sales_picture where info_seq = ".$row['info_seq']." ".
	   	                "order by sort asc";
	                $query2 = $this->db->query($query2);
	                $result = $query2->result_array();
	                $row['picture_list'] = $result;
	                
	                $query2 = "select * from fm_cm_machine_sales_check where sales_seq = ".$row['sales_seq'];
	                $query2 = $this->db->query($query2);
	                $result = $query2->row_array();
	                $row['check_list'] = $result;
	                
	                $row['reg_rest_time'] = $this->getRestTime(date('Y-m-d H:i:s', strtotime('+30 days', strtotime($row['sales_date'])))).' 남음';
	            }
                $data = array(
	                'admin_view_yn' => 'y'
	            );
                $this->db->where_in('state', array('승인', '입금대기', '계약대기'))
                -> where('sales_yn', 'n')
                -> where('wait_yn', 'n');
	            $this->db->update('fm_cm_machine_sales_info', $data);
	        } else if ($type == '현장미팅') {
	            $query = "select *, a.userid as sale_userid, c.userid as buy_userid, b.state as sale_state, c.state as visit_state from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_visit c ".
	   	            "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and c.admin_yn = 'y' and b.state != '등록취소' ".
	   	            "order by c.reg_date desc";
	            $query = $this->db->query($query);
	            $list = $query->result_array();
	            
	            foreach($list as &$row) {
	                $query = "select * from fm_cm_machine_visit_detail where visit_seq = ".$row['visit_seq'];
	                $query = $this->db->query($query);
	                $result = $query->result_array();
	               
					$hope_date = '';
					foreach($result as $row2) {
						if($row2['select_yn'] == 'y') {
							$hope_date = $row2['hope_date']." ".$row2['hope_time'];
							break;
						} else {
							$hope_date .= $row2['hope_date']." ".$row2['hope_time']."<br/>";
						}
					}
					$row['hope_date'] = $hope_date;
				}

	            $data = array(
	                'admin_view_yn' => 'y'
	            );
	            $this->db->where('admin_yn', 'y');
	            $this->db->update('fm_cm_machine_visit', $data);
	        } else if ($type == '가격제안') {
	            $query = "select *, a.userid as sale_userid, c.userid as buy_userid from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_proposal c ".
	   	            "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and c.admin_yn = 'y' and c.check_yn = 'n' and b.state != '등록취소' ".
	   	            "order by c.reg_date desc";
	            $query = $this->db->query($query);
	            $list = $query->result_array();
	            foreach($list as &$row) {
	                $query = "select count(*) as cnt from fm_cm_machine_proposal where info_seq = ".$row['info_seq'];
	                $query = $this->db->query($query);
	                $total_count = $query->row_array()['cnt'];
	                
	                $query = "select count(*) as cnt from fm_cm_machine_proposal where (permit_yn = 'y' or (permit_yn = 'c' and counter_permit_yn = 'y')) and info_seq = ".$row['info_seq'];
	                $query = $this->db->query($query);
	                $permit_count = $query->row_array()['cnt'];
	                
	                $percent = $permit_count / $total_count * 100;
	                $row['permit_percent'] = (int)$percent."%";
	            }
	            $data = array(
	                'admin_view_yn' => 'y'
	            );
	            $this->db->where('admin_yn', 'y');
	            $this->db->update('fm_cm_machine_proposal', $data);
	        } else if ($type == '즉시구매') {
	            $query = "select *, a.userid as sale_userid, c.userid as buy_userid from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_imdbuy c ".
	   	            "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and c.permit_yn = 'y' and c.check_yn = 'n' and b.state != '등록취소' ".
	   	            "order by c.reg_date desc";
	            $query = $this->db->query($query);
	            $list = $query->result_array();
                
                $data = array(
	                'admin_view_yn' => 'y'
	            );
                $this->db->where('permit_yn', 'y');
	            $this->db->update('fm_cm_machine_imdbuy', $data);
	        } else if ($type == '낙찰내역') {
	            $query = "select *, a.userid as sale_userid, d.userid as buy_userid, c.bid_price as bid_price_det, d.bid_price as bid_price_bid    from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_sales_detail c, fm_cm_machine_bid d ".
	   	            "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and b.info_seq = d.info_seq and d.bid_yn = 'y' and d.admin_yn = 'y' and d.check_yn = 'n' and b.state != '등록취소' ".
	   	            "order by d.reg_date desc";
	            $query = $this->db->query($query);
	            $list = $query->result_array();
                
                $data = array(
	                'admin_view_yn' => 'y'
	            );
                $this->db->where('bid_yn', 'y')
                        ->where('admin_yn', 'y');
	            $this->db->update('fm_cm_machine_bid', $data); 
	        } else if ($type == '계약') {
	            $query = "select *, reg_date as cont_reg_date from fm_cm_machine_contract where write_yn = 'y' and finish_yn = 'n' order by reg_date desc";
	            $query = $this->db->query($query);
	            $list = $query->result_array();
	            foreach($list as &$row) {
                    if($row['cont_type'] == '판매') {
                        $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b where a.sales_seq = b.sales_seq and b.info_seq = ".$row['target_seq'];
                        $res .= "query:  ".$query. "  ";
	                    $query = $this->db->query($query);
                        $result = $query->row_array();
                        $row = array_merge($row, $result);
                    } else if ($row['cont_type'] == '외주') {
                        $query = "select * from fm_cm_machine_outsourcing a, fm_cm_machine_partner_osc b where a.osc_seq = b.osc_seq and b.po_seq = ".$row['target_seq'];
	                    $query = $this->db->query($query);
	                    $result = $query->row_array();
                        $row = array_merge($row, $result);
                    }
                }
	            $data = array(
	                'admin_view_yn' => 'y'
	            );
                $this->db->where('write_yn', 'y');
	            $this->db->update('fm_cm_machine_contract', $data);
	        } else if ($type == '문의') {
	            $query = "select *, a.userid as sale_userid, c.userid as buy_userid from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_question c ".
	   	            "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and c.send_yn = 'y' and b.state != '등록취소' ".
	   	            "order by c.reg_date desc";
	            $query = $this->db->query($query);
	            $list = $query->result_array();
                
                $data = array(
	                'admin_view_yn' => 'y'
	            );
                $this->db->where('send_yn', 'y');
	            $this->db->update('fm_cm_machine_question', $data); 
	        } else if ($type == '외주') {
	            $query = "select * from fm_cm_machine_outsourcing ".
	   	            "where permit_yn = 'y' ".
	   	            "order by reg_date desc";
	            $query = $this->db->query($query);
	            $list = $query->result_array();
	            
	            foreach($list as &$row) {
	                $query2 = "select count(*) as apply_cnt from fm_cm_machine_partner_osc where osc_seq = ".$row['osc_seq'];
	                $query2 = $this->db->query($query2);
	                $result = $query2->row_array();
	                $row['apply_cnt'] = $result['apply_cnt'];
	                
	                $query2 = "select * from fm_cm_machine_partner_osc a, fm_cm_machine_partner b where a.partner_seq = b.partner_seq and osc_seq = ".$row['osc_seq'];
	                $query2 = $this->db->query($query2);
	                $result = $query2->result_array();
	                $row['apply_list'] = $result;
	            }
                $data = array(
	                'admin_view_yn' => 'y'
	            );
                $this->db->where('permit_yn', 'y')
                         ->where('finish_yn', 'n');
	            $this->db->update('fm_cm_machine_outsourcing', $data); 
	        } else if ($type == '수주') {
	            $query = "select *, b.reg_date as apply_date, a.userid as ptn_userid, c.userid as osc_userid from fm_cm_machine_partner a, fm_cm_machine_partner_osc b, fm_cm_machine_outsourcing c ".
	   	            "where a.partner_seq = b.partner_seq and b.osc_seq = c.osc_seq and admin_yn = 'y' ".
	   	            "order by b.reg_date desc";
	            $query = $this->db->query($query);
	            $list = $query->result_array();
                
                $data = array(
	                'admin_ptn_view_yn' => 'y'
	            );
                $this->db->where('admin_yn', 'y');
	            $this->db->update('fm_cm_machine_partner_osc', $data);
	        } else if ($type == '외주미팅') {
	            $query = "select *, a.userid as ptn_userid, c.userid as osc_userid from fm_cm_machine_partner a, fm_cm_machine_partner_osc b, fm_cm_machine_outsourcing c ".
	   	            "where a.partner_seq = b.partner_seq and b.osc_seq = c.osc_seq and b.state in('0', '1') and b.meet_state != '0' and meet_admin_yn = 'y' ".
	   	            "order by b.meet_date desc";
	            $query = $this->db->query($query);
	            $list = $query->result_array();
	            
	            foreach($list as &$row) {
	                $query2 = "select count(*) as apply_cnt from fm_cm_machine_partner_osc where osc_seq = ".$row['osc_seq'];
	                $query2 = $this->db->query($query2);
	                $result = $query2->row_array();
	                $row['apply_cnt'] = $result['apply_cnt'];
	                
	                $query2 = "select * from fm_cm_machine_partner_osc a, fm_cm_machine_partner b where a.partner_seq = b.partner_seq and osc_seq = ".$row['osc_seq'];
	                $query2 = $this->db->query($query2);
	                $result = $query2->result_array();
	                $row['apply_list'] = $result;
	            }
	            $data = array(
	                'admin_meet_view_yn' => 'y'
	            );
	            $this->db->where('meet_admin_yn', 'y')
                         ->where_in('state', array('0', '1'))
                         ->where('meet_state !=', '0');
	            $this->db->update('fm_cm_machine_partner_osc', $data);
	        } else if ($type == '배송대행') {
	            $query = "select * from fm_cm_machine_delivery where pay_state != '승인대기' order by reg_date desc";
	            $query = $this->db->query($query);
	            $list = $query->result_array();
                
                $data = array(
	                'admin_view_yn' => 'y'
	            );
                $this->db->where('pay_state !=', '승인대기');
	            $this->db->update('fm_cm_machine_delivery', $data);
	        } 
	    } else if ($title == '결제확인') {
	        /*
	        $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b ".
	   	        "where a.sales_seq = b.sales_seq and state = '입금대기' ".
	   	        "order by sales_date desc, b.info_seq asc";
	        $query = $this->db->query($query);
	        $list = $query->result_array();
	        
	        foreach($list as &$row) {
	            $query = "select * from fm_cm_machine_sales_advertise where sales_seq = ".$row['sales_seq'];
	            $query = $this->db->query($query);
	            $result = $query->result_array();
	            
	            $pay_list = "";
	            $ad_list = "";
	            foreach($result as $row2) {
	                $ad_list .= $ad_list == "" ? $row2['ad_name'] : ", ".$row2['ad_name'];
	            }
	            if($ad_list != "") {
	                $ad_list = "프리미엄광고 (".$ad_list.")";
	                $pay_list .= $ad_list;
	            }
	            
	            $query = "select * from fm_cm_machine_perform where sales_seq = ".$row['sales_seq'];
	            $query = $this->db->query($query);
	            $result = $query->row_array();
	            if(!empty($result)) {
	                $pay_list .= ", 성능검사";
	            }
	            
	            if($row['online_eval_yn'] == 'y') {
	                $pay_list .= ", ".$row['online_eval_option'];
	            }
	            $row['pay_list'] = $pay_list;
	        }
	        */
	        $pay_type = $type == '셀프판매 & 현장미팅' ? '현장미팅' : $type;
	        if($type == '셀프판매 & 현장미팅') {
	            $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_pay c ".
	   	            "where a.sales_seq = b.sales_seq and c.target_seq = b.info_seq and b.state != '등록취소' and c.pay_type = '현장미팅' ".
	   	            "order by c.reg_date desc";
	            $query = $this->db->query($query);
	            $list = $query->result_array();
	        } else if($type == '머박다이렉트') {
	            $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_pay c ".
	                  "where a.sales_seq = b.sales_seq and b.info_seq = c.target_seq and b.state != '등록취소' and c.pay_type = '머박다이렉트' ".
	                  "order by c.reg_date desc";
	            $query = $this->db->query($query);
	            $list = $query->result_array();
	        } else if ($type == '비교견적') {
	            $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_pay c ".
	   	            "where a.sales_seq = b.sales_seq and b.info_seq = c.target_seq and b.state != '등록취소' and c.pay_type = '비교견적' ".
	   	            "order by c.reg_date desc";
	            $query = $this->db->query($query);
	            $list = $query->result_array();
	        } else if ($type == '프리미엄광고') {
	            $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_pay c ".
	   	            "where a.sales_seq = b.sales_seq and b.info_seq = c.target_seq and b.state != '등록취소' and c.pay_type = '프리미엄광고' ".
	   	            "order by c.reg_date desc";
	            $query = $this->db->query($query);
	            $list = $query->result_array();
	        } else if ($type == '기계평가') {
	            $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_pay c ".
	   	            "where a.sales_seq = b.sales_seq and b.info_seq = c.target_seq and b.state != '등록취소' and c.pay_type = '기계평가' ".
	   	            "order by c.reg_date desc";
	            $query = $this->db->query($query);
	            $list = $query->result_array();
	        } else if ($type == '성능검사') {
	            $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_pay c ".
	   	            "where a.sales_seq = b.sales_seq and b.info_seq = c.target_seq and b.state != '등록취소' and c.pay_type = '성능검사' ".
	   	            "order by c.reg_date desc";
	            $query = $this->db->query($query);
	            $list = $query->result_array();
	        } else if ($type == '배송대행') {
	            $query = "select * from fm_cm_machine_pay where pay_type = '배송대행' and pay_state != '승인대기' order by reg_date desc";
	            $query = $this->db->query($query);
	            $list = $query->result_array();
	        } else if ($type == '외주') {
	            $query = "select * from fm_cm_machine_outsourcing a, fm_cm_machine_pay b ".
	   	            "where b.target_seq = a.osc_seq and b.pay_type = '외주' ".
	   	            "order by b.reg_date desc";
	            $query = $this->db->query($query);
	            $list = $query->result_array();
	        } else if ($type == '대금보호') {
	            $query = "select * from fm_cm_machine_pay ".
	   	            "where pay_type = '대금보호' ".
	   	            "order by reg_date desc";
	            $query = $this->db->query($query);
	            $list = $query->result_array();
	        } else if($type == '판매완료') {
	            $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_kind c,".
	   	            "fm_cm_machine_manufacturer d, fm_cm_machine_model e, fm_cm_machine_area f ".
	   	            "where a.sales_seq = b.sales_seq and b.kind_seq = c.kind_seq and b.mnf_seq = d.mnf_seq ".
	   	            "and b.model_seq = e.model_seq and b.area_seq = f.area_seq and sales_yn = 'y' ".
	   	            "order by sales_finish_date desc, b.info_seq asc";
	            $query = $this->db->query($query);
	            $list = $query->result_array();
	            
	            foreach($list as &$row) {
	                $query2 = "select * from fm_cm_machine_sales_picture where info_seq = ".$row['info_seq']." ".
	   	                "order by sort asc";
	                $query2 = $this->db->query($query2);
	                $result = $query2->result_array();
	                $row['picture_list'] = $result;
	                
	                $query2 = "select * from fm_cm_machine_sales_check where sales_seq = ".$row['sales_seq'];
	                $query2 = $this->db->query($query2);
	                $result = $query2->row_array();
	                $row['check_list'] = $result;
	            }
                $data = array(
                    'admin_view_yn' => 'y'
                );
                $this->db->where('sales_yn', 'y');
                $this->db->update('fm_cm_machine_sales_info', $data);
	        } else if ($type == '외주완료') {
	            $query = "select *, a.userid as ptn_userid, c.userid as osc_userid from fm_cm_machine_partner a, fm_cm_machine_partner_osc b, fm_cm_machine_outsourcing c ".
	   	            "where a.partner_seq = b.partner_seq and b.osc_seq = c.osc_seq and b.state = '3' and meet_admin_yn = 'y' and c.finish_yn = 'y' ".
	   	            "order by c.finish_date desc";
	            $query = $this->db->query($query);
	            $list = $query->result_array();
	            
	            foreach($list as &$row) {
	                $query2 = "select count(*) as apply_cnt from fm_cm_machine_partner_osc where osc_seq = ".$row['osc_seq'];
	                $query2 = $this->db->query($query2);
	                $result = $query2->row_array();
	                $row['apply_cnt'] = $result['apply_cnt'];
	                
	                $query2 = "select * from fm_cm_machine_partner_osc a, fm_cm_machine_partner b where a.partner_seq = b.partner_seq and osc_seq = ".$row['osc_seq'];
	                $query2 = $this->db->query($query2);
	                $result = $query2->result_array();
	                $row['apply_list'] = $result;
	            }
                $data = array(
                    'admin_view_yn' => 'y'
                );
                $this->db->where('finish_yn', 'y');
                $this->db->update('fm_cm_machine_outsourcing', $data);
	        } 
	        $data = array(
	            'admin_view_yn' => 'y'
	        );
	        $this->db->where('pay_type', $pay_type);
	        $this->db->update('fm_cm_machine_pay', $data);
	    }
	    echo json_encode(array('title' => $title, 'type' => $type, 'list' => $list, 'res' => $res));
	}
	    
	public function sale_permit_process() {
	    $info_seq = $this->input->post('info_seq');
	    $real_price = $this->input->post('real_price');
	    
	    $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_model c where a.sales_seq = b.sales_seq and b.model_seq and c.model_seq and b.info_seq = ".$info_seq;
	    $query = $this->db->query($query);
	    $result = $query->row_array();
	    
	    $data = array(
	        'state' => '승인',
	        'state_date' => date('Y-m-d H:i:s')
	    );
	    $this->db->where('info_seq', $info_seq);
	    $this->db->update('fm_cm_machine_sales_info', $data);
	    
	    if($result['type'] == 'emergency' || $result['type'] == 'direct') {
            $data = array(
                'real_price' => $real_price,
                'sort_price' => $real_price
            );
            $this->db->where('info_seq', $info_seq);
            $this->db->update('fm_cm_machine_sales_info', $data);
	    }
	    
	    $userData = $this->getUserData($result['userid']);
	    $title = "등록하신 기계 <b>승인</b>";
	    $message = "※ 등록기계 승인안내\r\n판매자 " . $userData['userid'] . '님이 등록하신 '.$result['model_name']."(" . $result['sales_no'] . ")이(가) 승인되었습니다.";
	    $this->send_common_mail($userData['email'], $title, $message);
	    $this->send_common_sms($userData['cellphone'], $message);
	    
	    $callback = "parent.location.reload()";
	    openDialogAlert('승인 되었습니다.',400,200,'parent',$callback);
	}
	
	public function common_permit_process() {
	    $type = $this->input->post('type');
	    
	    if($type == '현장미팅') {
    	    $visit_seq = $this->input->post('visit_seq');
            
            $query = "select *, a.userid as sale_userid, d.userid as buy_userid from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_model c, fm_cm_machine_visit d " . "where a.sales_seq = b.sales_seq and b.model_seq = c.model_seq and b.info_seq = d.info_seq and d.visit_seq = " . $visit_seq;
            $query = $this->db->query($query);
            $result = $query->row_array();
            
            $saleUser = $this->getUserDataById($result['sale_userid']);
            
            $title = "현장방문 <b>신청</b>";
            $message = "구매 예정자로부터 등록하신 " . $result['model_name'] . "(" . $result['sales_no'] . ")의 매입 현장방문 신청이 들어왔습니다. 미팅 진행을 하시겠습니까 ? \r\n※ 바로가기 URL: https://emachinebox.com/sch/visit_rcv/". $visit_seq;
            $this->send_common_mail($saleUser['email'], $title, $message);
            $this->send_common_sms($saleUser['cellphone'], $message);
            
	        $data = array(
	            'admin_yn' => 'y',
	            'admin_date' => date('Y-m-d H:i:s'),
                'admin_view_yn' => 'n'
	        );
	        $this->db->where('visit_seq', $visit_seq);
	        $this->db->update('fm_cm_machine_visit', $data);
	    } else if($type == '가격제안') {
	        $prop_seq = $this->input->post('prop_seq');
	        $data = array(
	            'admin_yn' => 'y',
                'admin_view_yn' => 'n'
	        );
	        $this->db->where('prop_seq', $prop_seq);
	        $this->db->update('fm_cm_machine_proposal', $data);
            
            $query = "select *, a.userid as sale_userid, c.userid as buy_userid from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_proposal c ".
            "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and c.prop_seq = ".$prop_seq;
            $query = $this->db->query($query);
            $result = $query->row_array();
            $userData = $this->getUserDataById($result['sale_userid']);

            $title = "가격제안 <b>받음</b>";
            $mail_message = "판매 중인 기계에 가격제안이 들어왔습니다.";
            $sms_message = "판매 중인 기계에 가격제안이 들어왔습니다. 자세한 사항은 마이페이지를 참고해주세요.";

            $this->send_common_mail($userData['email'], $title, $mail_message);
            $this->send_common_sms($userData['cellphone'], $sms_message);
	    } else if($type == '즉시구매') {
	        $buy_seq = $this->input->post('buy_seq');
	        $data = array(
	            'permit_yn' => 'y',
                'admin_view_yn' => 'n'
	        );
	        $this->db->where('buy_seq', $buy_seq);
	        $this->db->update('fm_cm_machine_imdbuy', $data);
	        
	        $query = "select *, a.userid as sale_userid, d.userid as buy_userid from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_model c, fm_cm_machine_imdbuy d ".
	   	        "where a.sales_seq = b.sales_seq and b.model_seq = c.model_seq and b.info_seq = d.info_seq and d.buy_seq = " . $buy_seq;
	        $query = $this->db->query($query);
	        $result = $query->row_array();
	        
	        $saleUser = $this->getUserData($result['sale_userid']);
	        $buyUser = $this->getUserData($result['buy_userid']);
	        
			if($result['visit_pay_yn'] != 'y') {
				$pay_message = "\r\n판매자님은 이용수수료 결제가 안되어 이용수수료 결제가 필요합니다.\r\n※ 이용수수료 결제하기 URL : https://emachinebox.com/sch/pay/buy/".$result['info_seq'];
			}
	        $title = "즉시구매 <b>신청 안내</b>";
	        $message = "※ 즉시구매 신청 안내\r\n판매자 " . $saleUser['userid'] . '님이 등록하신 '.$result['model_name']."(" . $result['sales_no'] . ")에 즉시구매 신청이 들어왔습니다.".$pay_message;
	        
	        $this->send_common_mail($saleUser['email'], $title, $message);
	        $this->send_common_sms($saleUser['cellphone'], $message);
	        
	        /*
	        $title = "즉시구매 <b>승인 안내</b>";
	        $message = "※ 즉시구매 승인 안내\r\n구매자 " . $buyUser['userid'] . '님의 '.$result['model_name']."(" . $result['sales_no'] . ")에 대한 즉시구매 신청이 승인되었습니다.";
	        
	        $this->send_common_mail($buyUser['email'], $title, $message);
	        $this->send_common_sms($buyUser['cellphone'], $message);
	        */
	    } else if($type == '낙찰내역') {
	        $bid_seq = $this->input->post('bid_seq');
	        $data = array(
	            'admin_yn' => 'y',
                'admin_view_yn' => 'n'
	        );
	        $this->db->where('bid_seq', $bid_seq);
	        $this->db->update('fm_cm_machine_bid', $data);
	    } else if($type == '문의') {
	        $qna_type = $this->input->post('qna_type');
	        $qna_seq = $this->input->post('qna_seq');
	        $title = $this->input->post('title');
	        $content = $this->input->post('content');
	        $res_content = $this->input->post('res_content');
	        
	        $query = "select *, a.userid as sale_userid, d.userid as buy_userid from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_model c, fm_cm_machine_question d ".
	   	        "where a.sales_seq = b.sales_seq and b.model_seq = c.model_seq and b.info_seq = d.info_seq and d.qna_seq = " . $qna_seq;
	        $query = $this->db->query($query);
	        $result = $query->row_array();
	        
	        $saleUser = $this->getUserData($result['sale_userid']);
	        $buyUser = $this->getUserData($result['buy_userid']);
	        
	        if($qna_type == 'send') {
	            $data = array(
	                'title' => $title,
	                'content' => $content,
	                'send_yn' => 'y',
                    'admin_view_yn' => 'n'
	            );
	            $this->db->where('qna_seq', $qna_seq);
	            $this->db->update('fm_cm_machine_question', $data);
	            
	            $email_title = "등록기계 <b>문의</b>";
	            $message = "※ 등록기계 문의안내\r\n판매자 " . $saleUser['userid'] . '님이 등록하신 '.$result['model_name']."(" . $result['sales_no'] . ")에 문의가 들어왔습니다.\r\n 제목: ".$title."\r\n 내용: ".$content;
	            
	            $this->send_common_mail($saleUser['email'], $email_title, $message);
	            $this->send_common_sms($saleUser['cellphone'], $message);
	            
	            $callback = "parent.location.reload()";
	            openDialogAlert('전송 되었습니다.',400,200,'parent',$callback);
	            exit;
	        } else if ($qna_type == 'reply') {
	            $data = array(
	                'res_content' => $res_content,
	                'res_yn' => 'y',
	                'res_date' => date('Y-m-d H:i:s')
	            );
	            $this->db->where('qna_seq', $qna_seq);
	            $this->db->update('fm_cm_machine_question', $data);
	            
	            $email_title = "문의 <b>답변</b>";
	            $message = "※ 문의 답변안내\r\n구매자 " . $buyUser['userid'] . '님이 '.$result['model_name']."(" . $result['sales_no'] . ")에 문의하신 내용에 대한 답변이 왔습니다.\r\n내용: ".$res_content;
	            
	            $this->send_common_mail($buyUser['email'], $email_title, $message);
	            $this->send_common_sms($buyUser['cellphone'], $message);
	            
	            $callback = "parent.location.reload()";
	            openDialogAlert('답변 되었습니다.',400,200,'parent',$callback);
	            exit;
	        }
	    } else if($type == '외주신청') {
	        $osc_seq = $this->input->post('osc_seq');
	        $data = array(
	            'permit_yn' => 'y',
	            'permit_date' => date('Y-m-d H:i:s'),
                'admin_view_yn' => 'n'
	        );
	        $this->db->where('osc_seq', $osc_seq);
	        $this->db->update('fm_cm_machine_outsourcing', $data);
	    } else if($type == '수주신청') {
	        $po_seq = $this->input->post('po_seq');
	        $data = array(
	            'admin_yn' => 'y',
                'admin_ptn_view_yn' => 'n'
	        );
	        $this->db->where('po_seq', $po_seq);
	        $this->db->update('fm_cm_machine_partner_osc', $data);
	    } else if($type == '외주미팅신청') {
	        $po_seq = $this->input->post('po_seq');
	        $data = array(
	            'meet_admin_yn' => 'y',
	            'meet_admin_date' => date('Y-m-d H:i:s'),
                'admin_meet_view_yn' => 'n'
	        );
	        $this->db->where('po_seq', $po_seq);
	        $this->db->update('fm_cm_machine_partner_osc', $data);
	    } else if($type == '배송대행') {
	        $deliv_seq = $this->input->post('deliv_seq');
            $deliv_company = $this->input->post('deliv_company');
	        $pay_price = $this->input->post('pay_price');
	        $data = array(
                'deliv_company' => $deliv_company,
	            'pay_price' => $pay_price,
	            'pay_state' => '입금대기',
                'admin_view_yn' => 'n'
	        );
	        $this->db->where('deliv_seq', $deliv_seq);
	        $this->db->update('fm_cm_machine_delivery', $data);
	        
	        $query = "select * from fm_cm_machine_delivery where deliv_seq = ".$deliv_seq;
	        $query = $this->db->query($query);
	        $result = $query->row_array();
	        
            $pay_data = array();
            $pay_data['pay_userid'] = $result['userid'];
            $pay_data['pay_content'] = '배송대행 ('.$result['service_list'].') 결제';
            $pay_data['pay_price'] = $pay_price;
            $pay_data['pay_method'] = '무통장 입금';
            $pay_data['pay_state'] = '입금대기';
            $pay_data['pay_type'] = '배송대행';
            $pay_data['pay_no'] = $this->get_pay_no();
            $pay_data['target_seq'] = $result['deliv_seq'];
            $this->db->insert('fm_cm_machine_pay', $pay_data);
            
            $saleUser = $this->getUserData($result['userid']);
            $title = "배송대행 <b>신청승인</b>";
            $message = "※ 배송대행 신청승인 안내\r\n판매자 " . $saleUser['userid'] . '님이 신청하신 '.$result['model_name']."(" . $result['sales_no'] . ")에 대한 배송대행 신청이 승인되었습니다. \r\n아래의 계좌로 입금해주시면 서비스를 이용하실 수 있습니다.\r\n농협은행, 에스디네트웍스(신동훈), 계좌번호 302-1371-4082-81\r\n결제금액 : ".number_format($pay_price)."원";
            
            $this->send_common_mail($saleUser['email'], $title, $message);
            $this->send_common_sms($saleUser['cellphone'], $message);
	    }  else if($type == '결제확인') {
	        $pay_type = $this->input->post('pay_type');
	        $pay_seq = $this->input->post('pay_seq');
	        $data = array(
	            'pay_state' => '결제확인'
	        );
	        $this->db->where('pay_seq', $pay_seq);
	        $this->db->update('fm_cm_machine_pay', $data);
	        
	        if($pay_type == '배송대행') {
	            $query = "select * from fm_cm_machine_pay a, fm_cm_machine_delivery b where a.target_seq = b.deliv_seq and a.pay_seq = ".$pay_seq;
	            $query = $this->db->query($query);
	            $result = $query->row_array();
	            
	            $data = array(
	                'pay_state' => '입금완료'
	            );
	            $this->db->where('deliv_seq', $result['deliv_seq']);
	            $this->db->update('fm_cm_machine_delivery', $data);
	            
	            $saleUser = $this->getUserData($result['userid']);
	            $title = $pay_type." <b>입금확인</b>";
	            $message = "※ ".$pay_type." 입금확인 안내\r\n판매자 " . $saleUser['userid'] . '님이 신청하신 '.$result['model_name']."에 대한 ".$pay_type." 결제의 입금확인이 되었습니다.\r\n결제 금액 : ".number_format($result['pay_price'])."원";
	            
	            $this->send_common_mail($saleUser['email'], $title, $message);
	            $this->send_common_sms($saleUser['cellphone'], $message);
	        } else if($pay_type == '프리미엄광고' || $pay_type == '기계평가' || $pay_type == '성능검사') {
	            $query = "select * from fm_cm_machine_pay where pay_seq = ".$pay_seq;
	            $query = $this->db->query($query);
	            $result = $query->row_array();
	            
	            $target_seq = $result['target_seq'];
	            $pay_price = $result['pay_price'];
	            $query = "select * from fm_cm_machine_pay where pay_type in('프리미엄광고', '성능검사', '기계평가') and pay_state = '입금대기' and target_seq = ".$target_seq;
	            $query = $this->db->query($query);
	            $result = $query->row_array();
	            
	            $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_model c where a.sales_seq = b.sales_seq and b.model_seq = c.model_seq and b.info_seq = ".$target_seq;
	            $query = $this->db->query($query);
	            $result2 = $query->row_array();
	            if(empty($result)) {
	                $data = array(
	                    'state' => '승인',
	                    'state_date' => date('Y-m-d H:i:s')
	                );
	                $this->db->where('info_seq', $result2['info_seq']);
	                $this->db->update('fm_cm_machine_sales_info', $data);
	            }
	            $saleUser = $this->getUserData($result2['userid']);
                $title = $pay_type." <b>입금확인</b>";
                $message = "※ ".$pay_type." 입금확인 안내\r\n판매자 " . $saleUser['userid'] . '님이 등록하신 '.$result2['model_name']."(" . $result2['sales_no'] . ")에 대한 ".$pay_type." 결제의 입금확인이 되었습니다.\r\n결제 금액 : ".number_format($pay_price)."원";
                
                $this->send_common_mail($saleUser['email'], $title, $message);
                $this->send_common_sms($saleUser['cellphone'], $message);
	        }
	        $callback = "parent.location.reload()";
	        openDialogAlert('결제확인 되었습니다.',400,200,'parent',$callback);
	        exit;
	    } else if($type == '셀프판매') {
	        $info_seq = $this->input->post('info_seq');
	        $data = array(
	            'state' => '승인',
	            'state_date' => date('Y-m-d H:i:s')
	        );
	        $this->db->where('info_seq', $info_seq);
	        $this->db->update('fm_cm_machine_sales_info', $data);
	        
	        $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_model c where a.sales_seq = b.sales_seq and b.model_seq = c.model_seq and b.info_seq = ".$info_seq;
	        $query = $this->db->query($query);
	        $result = $query->row_array();
	        
	        $saleUser = $this->getUserData($result['userid']);
	        $title = "셀프판매 <b>입금확인</b>";
	        $message = "※ 셀프판매 입금확인 안내\r\n판매자 " . $saleUser['userid'] . '님이 등록하신 '.$result['model_name']."(" . $result['sales_no'] . ")에 대한 입금확인이 되었습니다.";
	        
	        $this->send_common_mail($saleUser['email'], $title, $message);
	        $this->send_common_sms($saleUser['cellphone'], $message);
	        
	        $callback = "parent.location.reload()";
	        openDialogAlert('결제확인 되었습니다.',400,200,'parent',$callback);
	        exit;
	    }
	    $callback = "parent.location.reload()";
	    openDialogAlert('승인 되었습니다.',400,200,'parent',$callback);
	}
	
	public function change_deliv_process() {
	   $deliv_seq = $this->input->post("deliv_seq");    
	   $deliv_state = $this->input->post("deliv_state");  
	   
	   $data = array(
	       'deliv_state' => $deliv_state
	   );
	   $this->db->where('deliv_seq', $deliv_seq);
	   $this->db->update('fm_cm_machine_delivery', $data);
	   
	   $callback = "parent.location.reload()";
	   openDialogAlert('변경 되었습니다.',400,200,'parent',$callback);
	}
	
	public function get_process_data() {
	    header("Content-Type: application/json");
	    
	    $type = $this->input->post('type');
	    $seq = $this->input->post('seq');

	    $timeline = array();
	    if($type == 'info') {
	        $query = "select *, a.userid as sale_userid from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_kind c, fm_cm_machine_manufacturer d, fm_cm_machine_model e ".
	   	             "where a.sales_seq = b.sales_seq and b.kind_seq = c.kind_seq and b.mnf_seq = d.mnf_seq and b.model_seq = e.model_seq and b.info_seq = ".$seq;
	        $query = $this->db->query($query);
	        $info = $query->row_array();
	        
	        $query = "select * from fm_cm_machine_sales_option where info_seq = ".$info['info_seq'];
	        $query = $this->db->query($query);
	        $option = $query->result_array();
	        $option_list = "";
	        foreach($option as $row) {
	            $option_list .= $option_list == "" ? $row['option_name'] : ", ".$row['option_name'];
	        }
	        if($option_list == "")
	            $option_list = "없음";
	        $info['option_list'] = $option_list;
	        
	        $timeline["판매자"] = array_fill(0, 7, "-");
	        $timeline["머신박스"] = array_fill(0, 7, "-");
	        $timeline["구매자"] = array_fill(0, 7, "-");
	        
	        $step = "등록";
	        $timeline['판매자'][0] = date('Y년 m월 d일 H:i:s', strtotime($info['sales_date']));
	        $info['buy_userid'] = '없음';
	        
	        if(!empty($info['state_date'])) {
	            $step = "광고";
	            $timeline['머신박스'][1] = date('Y년 m월 d일 H:i:s', strtotime($info['state_date']));
	        }
	        
	        $query = "select * from fm_cm_machine_visit where state != 5 and info_seq = ".$info['info_seq']. " order by state desc, reg_date desc limit 1";
	        $query = $this->db->query($query);
	        $visit = $query->row_array();
	        if(!empty($visit)) {
	            $step = "현장미팅";
	            if(!empty($visit['select_date'])) $timeline['판매자'][2] = date('Y년 m월 d일 H:i:s', strtotime($visit['select_date']));
	            if(!empty($visit['admin_date'])) $timeline['머신박스'][2] = date('Y년 m월 d일 H:i:s', strtotime($visit['admin_date']));
	            $timeline['구매자'][2] = date('Y년 m월 d일 H:i:s', strtotime($visit['reg_date']));
	            $info['buy_userid'] = $visit['userid'];
	        }
	        
	        if($info['sales_yn'] == 'y') {
	            $step = "완료";
	            $timeline['판매자'][6] = date('Y년 m월 d일 H:i:s', strtotime($info['sales_finish_date']));
	            $timeline['머신박스'][6] = date('Y년 m월 d일 H:i:s', strtotime($info['sales_finish_date']));
	            $timeline['구매자'][6] = date('Y년 m월 d일 H:i:s', strtotime($info['sales_finish_date']));
	        } 
	    } else if ($type == 'osc') {
	        $query = "select *, a.userid as osc_userid from fm_cm_machine_outsourcing a, fm_cm_machine_category b, fm_cm_machine_area c ".
	                 "where a.cate_seq = b.cate_seq and a.area_seq = c.area_seq and osc_seq = ".$seq;
	        $query = $this->db->query($query);
	        $info = $query->row_array();
	        
	        $timeline["외주사"] = array_fill(0, 7, "-");
	        $timeline["머신박스"] = array_fill(0, 7, "-");
	        $timeline["수주사"] = array_fill(0, 7, "-");
	        $info['ptn_userid'] = '없음';
	        
	        $step = "등록";
	        $timeline['외주사'][0] = date('Y년 m월 d일 H:i:s', strtotime($info['reg_date']));
	        
	        if(!empty($info['permit_date'])) {
	            $step = "광고";
	            $timeline['머신박스'][1] = date('Y년 m월 d일 H:i:s', strtotime($info['permit_date']));
	        }
	        
	        $query = "select * from fm_cm_machine_partner_osc where meet_state in ('1', '2') and osc_seq = ".$info['osc_seq']. " order by meet_state desc, state desc, reg_date desc limit 1";
	        $query = $this->db->query($query);
	        $meet = $query->row_array();
	        if(!empty($meet)) {
	            $step = "미팅";
	            if(!empty($meet['meet_date'])) $timeline['외주사'][2] = $meet['meet_date'];
	            if(!empty($meet['meet_admin_date'])) $timeline['머신박스'][2] = $meet['meet_admin_date'];
	            if(!empty($meet['meet_permit_date']))$timeline['수주사'][2] = $meet['meet_permit_date'];
	            $info['ptn_userid'] = $meet['userid'];
	        }
	        
	        if($info['finish_yn'] == 'y') {
	            $step = "완료";
	            $timeline['외주사'][6] = date('Y년 m월 d일 H:i:s', strtotime($info['finish_date']));
	            $timeline['머신박스'][6] = date('Y년 m월 d일 H:i:s', strtotime($info['finish_date']));
	            $timeline['수주사'][6] = date('Y년 m월 d일 H:i:s', strtotime($info['finish_date']));
	        }
	    }
	    echo json_encode(array('type' => $type, 'seq' => $seq, 'info' => $info, 'step' => $step, 'timeline' => $timeline));
	}
	
	public function get_sales_list_search() {
	    header("Content-Type: application/json");
	    
	    $keyword = $this->input->post('keyword');
	    
	    $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_kind c,".
	   	    "fm_cm_machine_manufacturer d, fm_cm_machine_model e, fm_cm_machine_area f ".
	   	    "where a.sales_seq = b.sales_seq and b.kind_seq = c.kind_seq and b.mnf_seq = d.mnf_seq ".
	   	    "and b.model_seq = e.model_seq and b.area_seq = f.area_seq and b.state != '등록취소' and (sales_no like CONCAT(CONCAT('%', '".$keyword."'), '%') or userid like CONCAT(CONCAT('%', '".$keyword."'), '%')) ".
	   	    "order by sales_date desc, b.info_seq asc";
	    $query = $this->db->query($query);
	    $list = $query->result_array();
	    
	    foreach($list as &$row) {
	        $query2 = "select * from fm_cm_machine_sales_picture where info_seq = ".$row['info_seq']." ".
	   	        "order by sort asc";
	        $query2 = $this->db->query($query2);
	        $result = $query2->result_array();
	        $row['picture_list'] = $result;
	        
	        $query2 = "select * from fm_cm_machine_sales_check where sales_seq = ".$row['sales_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['check_list'] = $result;
	        
	        $row['reg_rest_time'] = $this->getRestTime(date('Y-m-d H:i:s', strtotime('+30 days', strtotime($row['sales_date'])))).' 남음';
	    }
	    
	    echo json_encode(array('list' => $list));
	}
	
	public function osc_contract_move() {
	    header("Content-Type: application/json");
	    
	    $po_seq = $this->input->post('po_seq');
	    
	    $result = false;
	    $data = array(
	        'state' => '2'
	    );
	    $this->db->where('po_seq', $po_seq);
	    $this->db->update('fm_cm_machine_partner_osc', $data);
	   
	    $query = "select * from fm_cm_machine_partner_osc where po_seq = ".$po_seq;
		$query = $this->db->query($query);
		$res = $query->row_array();
		
		$data = array(
	        'contract_yn' => 'y'
	    );
	    $this->db->where('osc_seq', $res['osc_seq']);
	    $this->db->update('fm_cm_machine_outsourcing', $data);
		
	    $data = array(
	        'cont_type' => '외주',
	        'target_seq' => $po_seq,
	    );
	    $this->db->insert('fm_cm_machine_contract', $data);
	    $result = true;
	    
	    echo json_encode(array('result' => $result));
	}
	
    public function prop_imdbuy_move() {
	    header("Content-Type: application/json");
	    
	    $prop_seq = $this->input->post('prop_seq');
	    
        $query = "select * from fm_cm_machine_proposal where prop_seq = ".$prop_seq;
        $query = $this->db->query($query);
        $info = $query->row_array();
        
	    $result = false;
	    $data = array(
	        'check_yn' => 'y'
	    );
	    $this->db->where('prop_seq', $prop_seq);
	    $this->db->update('fm_cm_machine_proposal', $data);
	    
        if ($info['permit_yn'] == 'y') {
            $buy_data = array(
                'info_seq' => $info['info_seq'],
                'userid' => $info['userid'],
                'buy_price' => $info['prop_price'],
                'hope_date' => '-',
                'hope_time' => '-',
                'deliver_service' => '-'
            );
            $this->db->insert('fm_cm_machine_imdbuy', $buy_data);
            $result = true;
        } else if ($info['counter_permit_yn'] == 'y') {
            $buy_data = array(
                'info_seq' => $info['info_seq'],
                'userid' => $info['userid'],
                'buy_price' => $info['counter_price'],
                'hope_date' => '-',
                'hope_time' => '-',
                'deliver_service' => '-'
            );
            $this->db->insert('fm_cm_machine_imdbuy', $buy_data);
            $result = true;
        }
	    echo json_encode(array('result' => $result));
	}
    
    public function imdbuy_contract_move() {
        $buy_seq = $this->input->post('buy_seq');
        $buy_price = $this->input->post('buy_price');
        
        $query = "select * from fm_cm_machine_imdbuy where buy_seq = ".$buy_seq;
        $query = $this->db->query($query);
        $result = $query->row_array();
        
		$data = array(
			'state' => '계약대기'
		);
		$this->db->where('info_seq', $result['info_seq']);
	    $this->db->update('fm_cm_machine_sales_info', $data);
        $data = array(
            'buy_price' => $buy_price,
            'check_yn' => 'y'
        );
        $this->db->where('buy_seq', $buy_seq);
        $this->db->update('fm_cm_machine_imdbuy', $data);
        
        $data = array(
	        'cont_type' => '판매',
	        'target_seq' => $result['info_seq'],
	    );
	    $this->db->insert('fm_cm_machine_contract', $data);
        
        $callback = "parent.location.reload()";
	   openDialogAlert('처리 되었습니다.',400,200,'parent',$callback);
    }
    
    public function bid_contract_move() {
	    header("Content-Type: application/json");
	    
	    $bid_seq = $this->input->post('bid_seq');
	    $bid_price = $this->input->post('bid_price');
        
        $query = "select * from fm_cm_machine_bid where bid_seq = ".$bid_seq;
        $query = $this->db->query($query);
        $info = $query->row_array();
        
	    $result = false;
		$data = array(
			'state' => '계약대기'
		);
		$this->db->where('info_seq', $info['info_seq']);
	    $this->db->update('fm_cm_machine_sales_info', $data);
	    $data = array(
	        'check_yn' => 'y'
	    );
	    $this->db->where('bid_seq', $bid_seq);
	    $this->db->update('fm_cm_machine_bid', $data);
	    
	    $data = array(
	        'cont_type' => '판매',
	        'target_seq' => $info['info_seq']
	    );
	    $this->db->insert('fm_cm_machine_contract', $data);
	    $result = true;
	    
	    echo json_encode(array('result' => $result));
	}
                                    
    public function contract_write_process() {
        header("Content-Type: application/json");
        
        $cont_seq = $this->input->post('cont_seq');
        
        $result = false;
        $data = array(
            'write_yn' => 'y',
            'write_date' => date('Y-m-d H:i:s'),
            'admin_view_yn' => 'n'
        );
        $this->db->where('cont_seq', $cont_seq);
        $this->db->update('fm_cm_machine_contract', $data);
        $result = true;
	    
	    echo json_encode(array('result' => $result));
    }
                                           
    public function process_finish() {
        header("Content-Type: application/json");
        
        $cont_seq = $this->input->post('cont_seq');
        $result = false;
        $data = array(
            'finish_yn' => 'y'
        );
        $this->db->where('cont_seq', $cont_seq);
        $this->db->update('fm_cm_machine_contract', $data);
        
        $query = "select * from fm_cm_machine_contract where cont_seq = ".$cont_seq;
        $query = $this->db->query($query);
        $info = $query->row_array();
        if($info['cont_type'] == '판매') {
            $data = array(
                'sales_yn' => 'y',
                'sales_finish_date' => date('Y-m-d H:i:s'),
                'admin_view_yn' => 'n',
				'state' => '승인'
            );
            $this->db->where('info_seq', $info['target_seq']);
            $this->db->update('fm_cm_machine_sales_info', $data);
        } else if($info['cont_type'] == '외주') {
            $data = array(
                'state' => '3',
                'finish_date' => date('Y-m-d H:i:s')
            );
            $this->db->where('po_seq', $info['target_seq']);
            $this->db->update('fm_cm_machine_partner_osc', $data);
            
            $query = "select * from fm_cm_machine_partner_osc where po_seq = ".$info['target_seq'];
            $query = $this->db->query($query);
            $res = $query->row_array();
            
            $data = array(
                'finish_yn' => 'y',
                'finish_date' => date('Y-m-d H:i:s'),
                'admin_view_yn' => 'n',
				'contract_yn' => 'n'
            );
            $this->db->where('osc_seq', $res['osc_seq']);
            $this->db->update('fm_cm_machine_outsourcing', $data);
        }
        $result = true;
	    
	    echo json_encode(array('result' => $result));
    }
	
	public function contract_cancel() {
	    header("Content-Type: application/json");
        
        $cont_seq = $this->input->post('cont_seq');
        
		$query = "select * from fm_cm_machine_contract where cont_seq = ".$cont_seq;
        $query = $this->db->query($query);
        $info = $query->row_array();
		
	    $result = false;
		if($info['cont_type'] == '판매') {
			$data = array(
				'state' => '승인',
			    'admin_view_yn' => 'n'
			);
			$this->db->where('info_seq', $info['target_seq']);
			$this->db->update('fm_cm_machine_sales_info', $data);
		} else if ($info['cont_type'] == '외주'){
			$query = "select * from fm_cm_machine_partner_osc where po_seq = ".$info['target_seq'];
            $query = $this->db->query($query);
            $res = $query->row_array();
			
			$data = array(
				'contract_yn' => 'n',
			    'admin_view_yn' => 'n'
			);
			$this->db->where('osc_seq', $res['osc_seq']);
			$this->db->update('fm_cm_machine_outsourcing', $data);
		}
		$this->db->where('cont_seq', $cont_seq);
		$this->db->delete('fm_cm_machine_contract');
	    $result = true;
	    
	    echo json_encode(array('result' => $result));
	}

	public function permit_member() {
        header("Content-Type: application/json");
        
        $member_seq = $this->input->post('member_seq');
        $result = false;
        $data = array(
            'bpermit_yn' => 'y'
        );
        $this->db->where('member_seq', $member_seq);
        $this->db->update('fm_member_business', $data);
        
        $result = true;
	    
	    echo json_encode(array('result' => $result));
    }
                            
	private function get_main_count() {
	   $resultMap = array();
	   
       $query = "select count(*) as cnt from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_kind c, fm_cm_machine_manufacturer d, fm_cm_machine_model e ".
   	       "where a.sales_seq = b.sales_seq and b.kind_seq = c.kind_seq and b.mnf_seq = d.mnf_seq and b.model_seq = e.model_seq and state in('승인', '입금대기', '계약대기') and wait_yn = 'n' and sales_yn = 'n'";
       $query = $this->db->query($query);
       $resultMap['state_count_01'] = $query->row_array()['cnt'];
	       
       $query = "select count(*) as cnt from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_kind c, fm_cm_machine_manufacturer d, fm_cm_machine_model e ".
   	       "where a.sales_seq = b.sales_seq and b.kind_seq = c.kind_seq and b.mnf_seq = d.mnf_seq and b.model_seq = e.model_seq and sales_yn = 'y' and b.state != '등록취소'";
       $query = $this->db->query($query);
       $resultMap['state_count_02'] = $query->row_array()['cnt'];
	       
       $query = "select count(*) as cnt from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_visit c ".
   	       "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and b.state != '등록취소'";
       $query = $this->db->query($query);
       $resultMap['state_count_03'] = $query->row_array()['cnt'];
	  
       $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_sales_advertise c where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and b.state != '등록취소' ".
          "group by b.info_seq order by sales_date desc";
       $query = $this->db->query($query);
       $list = $query->result_array();
       
       $total_count = 0;
       foreach($list as &$row) {
           $query = "select * from fm_cm_machine_sales_advertise where info_seq = ".$row['info_seq'];
           $query = $this->db->query($query);
           $result = $query->result_array();
           foreach($result as $row2) {
               $total_count ++;
           }
       }
       $resultMap['state_count_04'] = $total_count;
       
       $total_count = 0;
       $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_perform c where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and b.state != '등록취소'";
       $query = $this->db->query($query);
       $perform_list = $query->result_array();
       $total_count += count($perform_list);
       
       $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_online_eval c where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and b.state != '등록취소'";
       $query = $this->db->query($query);
       $eval_list = $query->result_array();
       $total_count += count($eval_list);
       $resultMap['state_count_05'] = $total_count;
       
       $query = "select count(*) as cnt from fm_cm_machine_delivery";
       $query = $this->db->query($query);
       $resultMap['state_count_06'] = $query->row_array()['cnt'];
	      
       $query = "select count(*) as cnt from fm_cm_machine_outsourcing where finish_yn = 'n'";
       $query = $this->db->query($query);
       $resultMap['state_count_07'] = $query->row_array()['cnt'];
	       
       $query = "select count(*) as cnt from fm_cm_machine_partner a, fm_cm_machine_partner_osc b, fm_cm_machine_outsourcing c where a.partner_seq = b.partner_seq and b.osc_seq = c.osc_seq";
       $query = $this->db->query($query);
       $resultMap['state_count_08'] = $query->row_array()['cnt'];
	       
       $query = "select count(*) as cnt from fm_cm_machine_outsourcing where finish_yn = 'y'";
       $query = $this->db->query($query);
       $resultMap['state_count_09'] = $query->row_array()['cnt'];
	       
       $key = get_shop_key();
       $sqlSelectClause = "
			select
				A.member_seq,A.userid,A.user_name,A.nickname,A.mailing,A.sms,A.emoney,A.point,A.cash,A.regist_date,A.lastlogin_date,A.review_cnt,A.login_cnt,A.birthday,A.zipcode,A.address_street,A.address_type,A.address,A.address_detail,A.sns_f,A.anniversary,A.recommend,A.sex,A.mtype,
				AES_DECRYPT(UNHEX(A.email), '{$key}') as email,
				AES_DECRYPT(UNHEX(A.phone), '{$key}') as phone,
				AES_DECRYPT(UNHEX(A.cellphone), '{$key}') as cellphone,
				CASE WHEN A.status = 'done' THEN '승인'
					 WHEN A.status = 'hold' THEN '미승인'
					 WHEN A.status = 'withdrawal' THEN '탈퇴'
					 WHEN A.status = 'dormancy' THEN '휴면'
				ELSE '' END AS status_nm, A.mall_t_check,
				B.bname, B.bphone, B.bcellphone, B.business_seq, B.baddress_type, B.baddress, B.baddress_detail,
				B.bzipcode, B.bceo, B.bno, B.bitem,
				B.bstatus, B.bperson, B.bpart,
				A.member_order_cnt,A.member_order_price,A.member_recommend_cnt ,A.member_invite_cnt,
				A.referer, A.referer_domain,
				IF(C.referer_group_no>0, C.referer_group_name, IF(LENGTH(A.referer)>0,'기타','직접입력')) as referer_name,
				A.group_seq,D.group_name,
				A.rute,
				A.sns_change,
				A.blacklist,
				CASE WHEN length(A.sns_n) >= '10'
					THEN concat(left(A.sns_n, 10 - 1),'*n')
					ELSE concat(left(A.sns_n, length(A.sns_n) - 1),'*n')
				END AS conv_sns_n
		";
       $sqlFromClause = "
			from
				fm_member A
				LEFT JOIN fm_member_business B ON A.member_seq = B.member_seq
				LEFT JOIN fm_referer_group C ON A.referer_domain = C.referer_group_url
				LEFT JOIN fm_member_group D ON A.group_seq = D.group_seq
		";
       $sqlWhereClause = "
			where A.status in ('done','hold','dormancy') ";
       $sqlOrderClause = "order by A.regist_date desc";
       
       $query = "
			{$sqlSelectClause}
			{$sqlFromClause}
			{$sqlWhereClause}
			{$sqlOrderClause}
		";
		$query = $this->db->query($query);
		$list = $query->result_array();
		$resultMap['state_count_10'] = count($list);
			
        $query = "select count(*) as cnt from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_kind c,".
  		        "fm_cm_machine_manufacturer d, fm_cm_machine_model e, fm_cm_machine_area f ".
  		        "where a.sales_seq = b.sales_seq and b.kind_seq = c.kind_seq and b.mnf_seq = d.mnf_seq ".
  		        "and b.model_seq = e.model_seq and b.area_seq = f.area_seq and (state in('미승인', '보류') or wait_yn = 'y') and b.sales_yn = 'n'";
        $query = $this->db->query($query);
        $resultMap['permit_count_01'] = $query->row_array()['cnt'];
		        
		       
        $query = "select count(*) as cnt from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_visit c ".
  		        "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and c.admin_yn = 'n' and b.state != '등록취소'";
        $query = $this->db->query($query);
        $resultMap['permit_count_02'] = $query->row_array()['cnt'];
		   
        $query = "select count(*) as cnt from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_proposal c ".
  		        "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and c.admin_yn = 'n' and b.state != '등록취소'";
        $query = $this->db->query($query);
        $resultMap['permit_count_03'] = $query->row_array()['cnt'];
		        
        $query = "select count(*) as cnt from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_imdbuy c ".
  		        "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and c.permit_yn = 'n' and b.state != '등록취소'";
        $query = $this->db->query($query);
        $resultMap['permit_count_04'] = $query->row_array()['cnt'];
        
        $query = "select count(*) as cnt from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_sales_detail c, fm_cm_machine_bid d ".
  		        "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and b.info_seq = d.info_seq and d.bid_yn = 'y' and d.admin_yn = 'n' and b.state != '등록취소'";
        $query = $this->db->query($query);
        $resultMap['permit_count_05'] = $query->row_array()['cnt'];
		        
        $query = "select count(*) as cnt from fm_cm_machine_contract where write_yn = 'n'";
	    $query = $this->db->query($query);
	    $resultMap['permit_count_06'] = $query->row_array()['cnt'];
        
        $query = "select count(*) as cnt from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_question c ".
  		        "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and c.send_yn = 'n' and b.state != '등록취소'";
        $query = $this->db->query($query);
        $resultMap['permit_count_07'] = $query->row_array()['cnt'];
        
        $query = "select count(*) as cnt from fm_cm_machine_outsourcing where permit_yn = 'n'";
        $query = $this->db->query($query);
        $resultMap['permit_count_08'] = $query->row_array()['cnt'];
	
        $query = "select count(*) as cnt from fm_cm_machine_partner a, fm_cm_machine_partner_osc b, fm_cm_machine_outsourcing c ".
  		        "where a.partner_seq = b.partner_seq and b.osc_seq = c.osc_seq and meet_state = 0 and admin_yn = 'n'";
        $query = $this->db->query($query);
        $resultMap['permit_count_09'] = $query->row_array()['cnt'];
		 
        $query = "select count(*) as cnt from fm_cm_machine_partner a, fm_cm_machine_partner_osc b, fm_cm_machine_outsourcing c ".
  		        "where a.partner_seq = b.partner_seq and b.osc_seq = c.osc_seq and b.state in ('0', '1') and b.meet_state != '0' and meet_admin_yn = 'n'";
        $query = $this->db->query($query);
        $resultMap['permit_count_10'] = $query->row_array()['cnt'];
		        
        $query = "select count(*) as cnt from fm_cm_machine_delivery where pay_state = '승인대기'";
        $query = $this->db->query($query);
        $resultMap['permit_count_11'] = $query->row_array()['cnt'];
		
		$query = "select count(*) as cnt from fm_member a, fm_member_business b where a.member_seq = b.member_seq and a.status in ('done', 'hold', 'dormancy') and (bpermit_yn = 'n' or main_dealer_yn = 'h')"; 
	    $query = $this->db->query($query);
		$resultMap['permit_count_12'] = $query->row_array()['cnt'];

        $query = "select count(*) as cnt from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_kind c,".
  		        "fm_cm_machine_manufacturer d, fm_cm_machine_model e, fm_cm_machine_area f ".
  		        "where a.sales_seq = b.sales_seq and b.kind_seq = c.kind_seq and b.mnf_seq = d.mnf_seq ".
  		        "and b.model_seq = e.model_seq and b.area_seq = f.area_seq and sales_yn = 'n' and state in('승인', '입금대기', '계약대기') and wait_yn = 'n'  and b.sales_yn = 'n'";
        $query = $this->db->query($query);
        $resultMap['progress_count_01'] = $query->row_array()['cnt'];
		        
        $query = "select count(*) as cnt from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_visit c ".
  		        "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and c.admin_yn = 'y' and b.state != '등록취소'";
        $query = $this->db->query($query);
        $resultMap['progress_count_02'] = $query->row_array()['cnt'];
		
        $query = "select count(*) as cnt from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_proposal c ".
  		        "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and c.admin_yn = 'y' and c.check_yn = 'n' and b.state != '등록취소'";
        $query = $this->db->query($query);
        $resultMap['progress_count_03'] = $query->row_array()['cnt'];
        
        $query = "select count(*) as cnt from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_imdbuy c ".
  		        "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and c.permit_yn = 'y' and c.check_yn = 'n' and b.state != '등록취소'";
        $query = $this->db->query($query);
        $resultMap['progress_count_04'] = $query->row_array()['cnt'];
		   
        $query = "select count(*) as cnt from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_sales_detail c, fm_cm_machine_bid d ".
  		        "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and b.info_seq = d.info_seq and d.bid_yn = 'y' and d.check_yn = 'n' and d.admin_yn = 'y' and b.state != '등록취소'";
        $query = $this->db->query($query);
        $resultMap['progress_count_05'] = $query->row_array()['cnt'];
        
        $query = "select count(*) as cnt from fm_cm_machine_contract where write_yn = 'y' and finish_yn = 'n'";
	    $query = $this->db->query($query);
	    $resultMap['progress_count_06'] = $query->row_array()['cnt'];
        
        $query = "select count(*) as cnt from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_question c ".
  		        "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and c.send_yn = 'y' and b.state != '등록취소'";
        $query = $this->db->query($query);
        $resultMap['progress_count_07'] = $query->row_array()['cnt'];
        
        $query = "select count(*) as cnt from fm_cm_machine_outsourcing where permit_yn = 'y'";
        $query = $this->db->query($query);
        $resultMap['progress_count_08'] = $query->row_array()['cnt'];
		
        $query = "select count(*) as cnt from fm_cm_machine_partner a, fm_cm_machine_partner_osc b, fm_cm_machine_outsourcing c ".
  		        "where a.partner_seq = b.partner_seq and b.osc_seq = c.osc_seq and admin_yn = 'y'";
        $query = $this->db->query($query);
        $resultMap['progress_count_09'] = $query->row_array()['cnt'];
		   
        $query = "select count(*) as cnt from fm_cm_machine_partner a, fm_cm_machine_partner_osc b, fm_cm_machine_outsourcing c ".
  		        "where a.partner_seq = b.partner_seq and b.osc_seq = c.osc_seq and b.state in('0', '1') and b.meet_state != '0' and meet_admin_yn = 'y'";
        $query = $this->db->query($query);
        $resultMap['progress_count_10'] = $query->row_array()['cnt'];
		  
        $query = "select count(*) as cnt from fm_cm_machine_delivery where pay_state != '승인대기'";
        $query = $this->db->query($query);
        $resultMap['progress_count_11'] = $query->row_array()['cnt'];
        
        $query = "select count(*) as cnt from fm_cm_machine_partner a, fm_cm_machine_partner_osc b, fm_cm_machine_outsourcing c ".
  		        "where a.partner_seq = b.partner_seq and b.osc_seq = c.osc_seq and b.state = '3' and meet_admin_yn = 'y'";
        $query = $this->db->query($query);
        $resultMap['progress_count_12'] = $query->row_array()['cnt'];
		
        $query = "select count(*) as cnt from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_pay c ".
            "where a.sales_seq = b.sales_seq and c.target_seq = b.info_seq and c.pay_type = '현장미팅' and c.pay_state = '입금대기' and b.state != '등록취소'";
        $query = $this->db->query($query);
        $resultMap['pay_count_01'] = $query->row_array()['cnt'];
        
        $query = "select count(*) as cnt from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_pay c ".
  		        "where a.sales_seq = b.sales_seq and b.info_seq = c.target_seq and c.pay_type = '머박다이렉트' and c.pay_state = '입금대기' and b.state != '등록취소'";
        $query = $this->db->query($query);
        $resultMap['pay_count_02'] = $query->row_array()['cnt'];
		    
        $query = "select count(*) as cnt from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_pay c ".
  		        "where a.sales_seq = b.sales_seq and b.info_seq = c.target_seq and c.pay_type = '비교견적' and c.pay_state = '입금대기' and b.state != '등록취소'";
        $query = $this->db->query($query);
        $resultMap['pay_count_03'] = $query->row_array()['cnt'];
        
        $query = "select count(*) as cnt from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_pay c ".
  		        "where a.sales_seq = b.sales_seq and b.info_seq = c.target_seq and c.pay_type = '프리미엄광고' and c.pay_state = '입금대기' and b.state != '등록취소'";
        $query = $this->db->query($query);
        $resultMap['pay_count_04'] = $query->row_array()['cnt'];
		  
        $query = "select count(*) as cnt from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_pay c ".
  		        "where a.sales_seq = b.sales_seq and b.info_seq = c.target_seq and c.pay_type = '기계평가' and c.pay_state = '입금대기' and b.state != '등록취소'";
        $query = $this->db->query($query);
        $resultMap['pay_count_05'] = $query->row_array()['cnt'];
		  
        $query = "select count(*) as cnt from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_pay c ".
  		        "where a.sales_seq = b.sales_seq and b.info_seq = c.target_seq and c.pay_type = '성능검사' and c.pay_state = '입금대기' and b.state != '등록취소'";
        $query = $this->db->query($query);
        $resultMap['pay_count_06'] = $query->row_array()['cnt'];
        
        $query = "select count(*) as cnt from fm_cm_machine_pay where pay_type = '배송대행' and pay_state = '입금대기'";
        $query = $this->db->query($query);
        $resultMap['pay_count_07'] = $query->row_array()['cnt'];
		    
        $query = "select count(*) as cnt from fm_cm_machine_outsourcing a, fm_cm_machine_pay b ".
  		        "where b.target_seq = a.osc_seq and b.pay_type = '외주' and b.pay_state = '입금대기'";
        $query = $this->db->query($query);
        $resultMap['pay_count_08'] = $query->row_array()['cnt'];
		    
        $query = "select count(*) as cnt from fm_cm_machine_pay where pay_type = '대금보호' and pay_state = '입금대기'";
        $query = $this->db->query($query);
        $resultMap['pay_count_09'] = $query->row_array()['cnt'];
	
        $query = "select count(*) as cnt from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_kind c,".
            "fm_cm_machine_manufacturer d, fm_cm_machine_model e, fm_cm_machine_area f ".
            "where a.sales_seq = b.sales_seq and b.kind_seq = c.kind_seq and b.mnf_seq = d.mnf_seq ".
            "and b.model_seq = e.model_seq and b.area_seq = f.area_seq and sales_yn = 'y'";
        $query = $this->db->query($query);
        $resultMap['pay_count_10'] = $query->row_array()['cnt'];
	            
        $query = "select count(*) as cnt from fm_cm_machine_partner a, fm_cm_machine_partner_osc b, fm_cm_machine_outsourcing c ".
            "where a.partner_seq = b.partner_seq and b.osc_seq = c.osc_seq and b.state = '3' and meet_admin_yn = 'y' and c.finish_yn = 'y' ";
        $query = $this->db->query($query);
        $resultMap['pay_count_11'] = $query->row_array()['cnt'];
	    return $resultMap;
	}
	
	public function ajax_user_data() {
	    header("Content-Type: application/json");
	    
	    $userid = $this->input->post('userid');
	    
	    $key = get_shop_key();
	    
	    $sqlSelectClause = "
				select
					A.member_seq,A.userid,A.user_name,A.nickname,A.mailing,A.sms,A.emoney,A.point,A.cash,A.regist_date,A.lastlogin_date,A.review_cnt,A.login_cnt,A.birthday,A.zipcode,A.address_street,A.address_type,A.address,A.address_detail,A.sns_f,A.anniversary,A.recommend,A.sex,A.mtype,
					AES_DECRYPT(UNHEX(A.email), '{$key}') as email,
					AES_DECRYPT(UNHEX(A.phone), '{$key}') as phone,
					AES_DECRYPT(UNHEX(A.cellphone), '{$key}') as cellphone,
					CASE WHEN A.status = 'done' THEN '승인'
						 WHEN A.status = 'hold' THEN '미승인'
						 WHEN A.status = 'withdrawal' THEN '탈퇴'
						 WHEN A.status = 'dormancy' THEN '휴면'
					ELSE '' END AS status_nm, A.mall_t_check,
					B.bname, B.bphone, B.bcellphone, B.business_seq, B.baddress_type, B.baddress, B.baddress_detail,
					B.bzipcode, B.bceo, B.bno, B.bitem,
					B.bstatus, B.bperson, B.bpart,
					A.member_order_cnt,A.member_order_price,A.member_recommend_cnt ,A.member_invite_cnt,
					A.referer, A.referer_domain,
					IF(C.referer_group_no>0, C.referer_group_name, IF(LENGTH(A.referer)>0,'기타','직접입력')) as referer_name,
					A.group_seq,D.group_name,
					A.rute,
					A.sns_change,
					A.blacklist,
					CASE WHEN length(A.sns_n) >= '10'
						THEN concat(left(A.sns_n, 10 - 1),'*n')
						ELSE concat(left(A.sns_n, length(A.sns_n) - 1),'*n')
					END AS conv_sns_n
			";
	    $sqlFromClause = "
    			from
    				fm_member A
    				LEFT JOIN fm_member_business B ON A.member_seq = B.member_seq
    				LEFT JOIN fm_referer_group C ON A.referer_domain = C.referer_group_url
    				LEFT JOIN fm_member_group D ON A.group_seq = D.group_seq
    		";
	    $sqlWhereClause = "
    			where A.status in ('done','hold','dormancy') ";
	    $sqlOrderClause = "order by A.regist_date desc";
	    
	    $sqlWhereClause .= "and A.userid = '".$userid."' ";
	    $query = "
			{$sqlSelectClause}
			{$sqlFromClause}
			{$sqlWhereClause}
			{$sqlOrderClause}
		";
		$query = $this->db->query($query);
		$data = $query->row_array();
			
		$query = "select * from fm_member_business where member_seq = ".$data['member_seq'];
	    $query = $this->db->query($query);
	    $result = $query->row_array();
	    $data['business_seq'] = $result['business_seq'];
	    $data['bpermit_yn'] = $result['bpermit_yn'];
	    $data['bcard_path'] = $result['bcard_path'];
	    
	    if($data['mtype'] == 'business'){
	        $data['type'] = '기업';
	        
	        $query = "select label_value as gubun from fm_member_subinfo where label_title = '회원구분' and member_seq = ".$data['member_seq'];
	        $query = $this->db->query($query);
	        $data['gubun'] = $query->row()->gubun == '기업회원' ? '기업' : '딜러';
	    } else {
	        $data['type'] = '개인';
	        $data['gubun'] = '개인';
	    }
		echo json_encode($data);
	}
	
	private function get_main_new() {
	    $resultMap = array();
	    
	    $query = "select count(*) as cnt from fm_cm_machine_sales_info where admin_view_yn = 'n' and (state in('미승인', '보류') or wait_yn = 'y')";
	    $query = $this->db->query($query);
	    $resultMap['permit_new_01'] = $query->row_array()['cnt'] > 0 ? 'y' : 'n';
	    
	    $query = "select count(*) as cnt from fm_cm_machine_visit where admin_yn = 'n' and admin_view_yn = 'n'";
	    $query = $this->db->query($query);
	    $resultMap['permit_new_02'] = $query->row_array()['cnt'] > 0 ? 'y' : 'n';
	    
	    $query = "select count(*) as cnt from fm_cm_machine_proposal where admin_yn = 'n' and admin_view_yn = 'n'";
	    $query = $this->db->query($query);
	    $resultMap['permit_new_03'] = $query->row_array()['cnt'] > 0 ? 'y' : 'n';
	    
	    $query = "select count(*) as cnt from fm_cm_machine_imdbuy where permit_yn = 'n' and admin_view_yn = 'n'";
	    $query = $this->db->query($query);
	    $resultMap['permit_new_04'] = $query->row_array()['cnt'] > 0 ? 'y' : 'n';
	    
	    $query = "select count(*) as cnt from fm_cm_machine_bid where admin_view_yn = 'n' and bid_yn = 'y' and admin_yn = 'n'";
	    $query = $this->db->query($query);
	    $resultMap['permit_new_05'] = $query->row_array()['cnt'] > 0 ? 'y' : 'n';
	    
	    $query = "select count(*) as cnt from fm_cm_machine_contract where admin_view_yn = 'n' and write_yn = 'n'";
	    $query = $this->db->query($query);
	    $resultMap['permit_new_06'] = $query->row_array()['cnt'] > 0 ? 'y' : 'n';
	    
	    $query = "select count(*) as cnt from fm_cm_machine_question where send_yn = 'n' and admin_view_yn = 'n'";
	    $query = $this->db->query($query);
	    $resultMap['permit_new_07'] = $query->row_array()['cnt'] > 0 ? 'y' : 'n';
	    
	    $query = "select count(*) as cnt from fm_cm_machine_outsourcing where permit_yn = 'n' and admin_view_yn = 'n'";
	    $query = $this->db->query($query);
	    $resultMap['permit_new_08'] = $query->row_array()['cnt'] > 0 ? 'y' : 'n';
	    
	    $query = "select count(*) as cnt from fm_cm_machine_partner_osc where meet_state = 0 and admin_yn = 'n' and admin_ptn_view_yn = 'n'";
	    $query = $this->db->query($query);
	    $resultMap['permit_new_09'] = $query->row_array()['cnt'] > 0 ? 'y' : 'n';
	    
	    $query = "select count(*) as cnt from fm_cm_machine_partner_osc where state in ('0', '1') and meet_state != '0' and meet_admin_yn = 'n' and admin_meet_view_yn = 'n'";
	    $query = $this->db->query($query);
	    $resultMap['permit_new_10'] = $query->row_array()['cnt'] > 0 ? 'y' : 'n';
	    
	    $query = "select count(*) as cnt from fm_cm_machine_delivery where pay_state = '승인대기' and admin_view_yn = 'n'";
	    $query = $this->db->query($query);
	    $resultMap['permit_new_11'] = $query->row_array()['cnt'] > 0 ? 'y' : 'n';
		
		$query = "select count(*) as cnt from fm_member a, fm_member_business b where a.member_seq = b.member_seq and a.status in ('done', 'hold', 'dormancy') and (bpermit_yn = 'n' or main_dealer_yn = 'h')  and admin_view_yn = 'n'";
	    $query = $this->db->query($query);
		$resultMap['permit_new_12'] = $query->row_array()['cnt'] > 0 ? 'y' : 'n';
		
        $query = "select count(*) as cnt from fm_cm_machine_sales_info where admin_view_yn = 'n' and sales_yn = 'n' and state in('승인', '입금대기', '계약대기') and wait_yn = 'n'";
	    $query = $this->db->query($query);
	    $resultMap['progress_new_01'] = $query->row_array()['cnt'] > 0 ? 'y' : 'n';
        
	    $query = "select count(*) as cnt from fm_cm_machine_visit where admin_yn = 'y' and admin_view_yn = 'n'";
	    $query = $this->db->query($query);
	    $resultMap['progress_new_02'] = $query->row_array()['cnt'] > 0 ? 'y' : 'n';
	    
	    $query = "select count(*) as cnt from fm_cm_machine_proposal where admin_yn = 'y' and admin_view_yn = 'n' and check_yn = 'n'";
	    $query = $this->db->query($query);
	    $resultMap['progress_new_03'] = $query->row_array()['cnt'] > 0 ? 'y' : 'n';
        
        $query = "select count(*) as cnt from fm_cm_machine_imdbuy where permit_yn = 'y' and check_yn = 'n' and admin_view_yn = 'n'";
	    $query = $this->db->query($query);
	    $resultMap['progress_new_04'] = $query->row_array()['cnt'] > 0 ? 'y' : 'n';
	    
        $query = "select count(*) as cnt from fm_cm_machine_bid where admin_view_yn = 'n' and bid_yn = 'y' and admin_yn = 'y'";
	    $query = $this->db->query($query);
	    $resultMap['progress_new_05'] = $query->row_array()['cnt'] > 0 ? 'y' : 'n';
        
        $query = "select count(*) as cnt from fm_cm_machine_contract where admin_view_yn = 'n' and write_yn = 'y' and finish_yn = 'n'";
	    $query = $this->db->query($query);
	    $resultMap['progress_new_06'] = $query->row_array()['cnt'] > 0 ? 'y' : 'n';
        
	    $query = "select count(*) as cnt from fm_cm_machine_question where res_content is not null and res_yn = 'n' and send_yn = 'y'";
	    $query = $this->db->query($query);
	    $resultMap['progress_new_07'] = $query->row_array()['cnt'] > 0 ? 'y' : 'n';
	    
        $query = "select count(*) as cnt from fm_cm_machine_outsourcing where permit_yn = 'y' and finish_yn = 'n' and admin_view_yn = 'n'";
	    $query = $this->db->query($query);
	    $resultMap['progress_new_08'] = $query->row_array()['cnt'] > 0 ? 'y' : 'n';
	    
	    $query = "select count(*) as cnt from fm_cm_machine_partner_osc where meet_state = 0 and admin_yn = 'y' and admin_ptn_view_yn = 'n'";
	    $query = $this->db->query($query);
	    $resultMap['progress_new_09'] = $query->row_array()['cnt'] > 0 ? 'y' : 'n';
	    
	    $query = "select count(*) as cnt from fm_cm_machine_partner_osc where state in ('0', '1') and meet_state != '0' and meet_admin_yn = 'y' and admin_meet_view_yn = 'n'";
	    $query = $this->db->query($query);
	    $resultMap['progress_new_10'] = $query->row_array()['cnt'] > 0 ? 'y' : 'n';
	    
	    $query = "select count(*) as cnt from fm_cm_machine_delivery where pay_state != '승인대기' and admin_view_yn = 'n'";
	    $query = $this->db->query($query);
	    $resultMap['progress_new_11'] = $query->row_array()['cnt'] > 0 ? 'y' : 'n';
        
	    $query = "select count(*) as cnt from fm_cm_machine_pay where admin_view_yn = 'n' and pay_type = '현장미팅'";
	    $query = $this->db->query($query);
	    $resultMap['pay_new_01'] = $query->row_array()['cnt'] > 0 ? 'y' : 'n';
	    
	    $query = "select count(*) as cnt from fm_cm_machine_pay where admin_view_yn = 'n' and pay_type = '머박다이렉트'";
	    $query = $this->db->query($query);
	    $resultMap['pay_new_02'] = $query->row_array()['cnt'] > 0 ? 'y' : 'n';
	    
	    $query = "select count(*) as cnt from fm_cm_machine_pay where admin_view_yn = 'n' and pay_type = '비교견적'";
	    $query = $this->db->query($query);
	    $resultMap['pay_new_03'] = $query->row_array()['cnt'] > 0 ? 'y' : 'n';
	    
	    $query = "select count(*) as cnt from fm_cm_machine_pay where admin_view_yn = 'n' and pay_type = '프리미엄광고'";
	    $query = $this->db->query($query);
	    $resultMap['pay_new_04'] = $query->row_array()['cnt'] > 0 ? 'y' : 'n';
	    
	    $query = "select count(*) as cnt from fm_cm_machine_pay where admin_view_yn = 'n' and pay_type = '기계평가'";
	    $query = $this->db->query($query);
	    $resultMap['pay_new_05'] = $query->row_array()['cnt'] > 0 ? 'y' : 'n';
	    
	    $query = "select count(*) as cnt from fm_cm_machine_pay where admin_view_yn = 'n' and pay_type = '성능검사'";
	    $query = $this->db->query($query);
	    $resultMap['pay_new_06'] = $query->row_array()['cnt'] > 0 ? 'y' : 'n';
	    
	    $query = "select count(*) as cnt from fm_cm_machine_pay where admin_view_yn = 'n' and pay_type = '배송대행'";
	    $query = $this->db->query($query);
	    $resultMap['pay_new_07'] = $query->row_array()['cnt'] > 0 ? 'y' : 'n';
	    
	    $query = "select count(*) as cnt from fm_cm_machine_pay where admin_view_yn = 'n' and pay_type = '외주'";
	    $query = $this->db->query($query);
	    $resultMap['pay_new_08'] = $query->row_array()['cnt'] > 0 ? 'y' : 'n';
	    
	    $query = "select count(*) as cnt from fm_cm_machine_pay where admin_view_yn = 'n' and pay_type = '대금보호'";
	    $query = $this->db->query($query);
	    $resultMap['pay_new_09'] = $query->row_array()['cnt'] > 0 ? 'y' : 'n';
	    
        $query = "select count(*) as cnt from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_kind c,".
            "fm_cm_machine_manufacturer d, fm_cm_machine_model e, fm_cm_machine_area f ".
            "where a.sales_seq = b.sales_seq and b.kind_seq = c.kind_seq and b.mnf_seq = d.mnf_seq ".
            "and b.model_seq = e.model_seq and b.area_seq = f.area_seq and sales_yn = 'y' and b.admin_view_yn = 'n'";
        $query = $this->db->query($query);
        $resultMap['pay_new_10'] = $query->row_array()['cnt'] > 0 ? 'y' : 'n';
	            
        $query = "select count(*) as cnt from fm_cm_machine_partner a, fm_cm_machine_partner_osc b, fm_cm_machine_outsourcing c ".
            "where a.partner_seq = b.partner_seq and b.osc_seq = c.osc_seq and b.state = '3' and meet_admin_yn = 'y' and c.finish_yn = 'y' and c.admin_view_yn = 'n'";
        $query = $this->db->query($query);
        $resultMap['pay_new_11'] = $query->row_array()['cnt'] > 0 ? 'y' : 'n';
        
	    return $resultMap;
	}
	
	private function get_date_where_query($column, $date_s, $date_f) {
	    if($date_s == '' && $date_f == '') {
	        $where_query = "";
	    } else if($date_s != '' && $date_f == '') {
	        $where_query = "and date_format(".$column.", '%Y-%m-%d') >= '".$date_s."' ";
	    } else if($date_s == '' && $date_f != '') {
	        $where_query = "and date_format(".$column.", '%Y-%m-%d') <= '".$date_f."' ";
	    } else if($date_s != '' && $date_f != '') {
	        $where_query = "and date_format(".$column.", '%Y-%m-%d') between '".$date_s."' and '".$date_f."' ";
	    } 
	    return $where_query;
	}
	
	private function getRestTime($date)
	{
	    $now_date = strtotime(date('Y-m-d H:i:s'));
	    
	    $date1 = strtotime($date);
	    $date2 = $now_date;
	    
	    $restTime = $date1 - $date2;
	    
	    $day = floor($restTime / (60*60*24));
	    $hour = floor(($restTime-($day*60*60*24))/(60*60));
	    $minute = floor(($restTime-($day*60*60*24)-($hour*60*60))/(60));
	    $second = $restTime - ($day*60*60*24) - ($hour*60*60) - ($minute*60);
	    
	    if($day < 0) {
	        $day = 0;
	        $hour = 0;
	        $minute = 0;
	        $second = 0;
	    }
	    $day = $day < 10 ? '0'.$day : $day;
	    $hour = $hour < 10 ? '0'.$hour : $hour;
	    $minute = $minute < 10 ? '0'.$minute : $minute;
	    $second = $second < 10 ? '0'.$second : $second;
	    
	    return $day."일 ".$hour.":".$minute.":".$second;
	}
	
	private function getUserData($userid) {
	    $query = "select * from fm_member where userid='".$userid."'";
	    $query = $this->db->query($query);
	    $result = $query->row_array();
	    return $this->membermodel->get_member_data($result['member_seq']);
	}
	
	private function send_common_mail($email, $title, $message)
	{
	    if ($email) {
	        send_common_mail($email, $title, $message);
	    }
	}
	
	private function send_common_sms($phone, $message) {
	    if($phone) {
	        send_common_sms($phone, $message);
	    }
	}
	
	private function get_pay_no() {
	    $query = "select * from fm_cm_machine_pay where date_format(reg_date, '%Y-%m-%d') = date_format(now(), '%Y-%m-%d') order by pay_seq desc limit 1";
	    $query = $this->db->query($query);
	    $result = $query->row_array();
	    if(empty($result))
	        $pay_no = 1;
	        else
	            $pay_no = (int)substr($result['pay_no'], -4) + 1;
	            $date = date('ymd');
	            $pay_no = sprintf('%04d', $pay_no);
	            return $date.$pay_no;
	}
	
	private function getUserDataById($userid) {
	    $query = "select * from fm_member where userid='".$userid."'";
	    $query = $this->db->query($query);
	    $result = $query->row_array();
	    return $this->membermodel->get_member_data($result['member_seq']);
	}
	
	// 메인화면
	public function index()
	{
		$this->load->model('usedmodel');
		$this->template->assign("config_basic",$this->config_basic);

		# 네이버페이 1.0 사용자들에게만 공지팝업 띄우기 옵션 추가 @2016-08-01 pjm
		$cfg_naverpay = config_load('navercheckout');
		if(!trim($cfg_naverpay['version']) && in_array($cfg_naverpay['use'],array('y','test'))) $cfg_naverpay['version'] = "1.0";

		/* MY 서비스 바 */
		$params['cfg_naverpay'] = $cfg_naverpay;
		$this->get_main_myservice_bar($params);

		/* 진행중인 이벤트 */
		$params = array();
		$this->load->model('eventmodel');
		$field_str						= "count(*) cnt";
		$params['start_date<=']	= date('Y-m-d H:i:s');
		$params['end_date>=']		= date('Y-m-d H:i:s');
		$data								= $this->eventmodel->get($params, $field_str)->row_array();
		$eventCount					= $data['cnt'];

		/* 진행중인 사은품 이벤트 */
		$params = array();
		$this->load->model('giftmodel');
		$field_str						= "count(*) cnt";
		$params['start_date<=']	= date('Y-m-d H:i:s');
		$params['end_date>=']		= date('Y-m-d H:i:s');
		$data								= $this->giftmodel->get($params, $field_str)->row_array();
		$giftCount						= $data['cnt'];

		/* 매출증빙 */
		$params = array();
		$this->load->model('salesmodel');
		$params['tstep'][]				= '1';
		$params['action_mode']	= 'count';
		$data					= $this->salesmodel->sales_list($params)->row_array();
		$saleCount			= $data['cnt'];

		/* 서비스기간 남은일수 */
		$expireDayTime		= strtotime($this->config_system['service']['expire_date']);
		$todayTime				= strtotime(date('Y-m-d'));
		$expireDay				= date("Y년 m월 d일", $expireDayTime);
		$remainExpireDay	= round( ($expireDayTime-$todayTime)/(3600*24) );

		/* 최대 용량 */
		$maxDiskSpace		= $this->usedmodel->get_disk_space_format($this->config_system['service']['disk_space']);
		$maxDiskSpace		= str_replace('MB','',$maxDiskSpace);
		if($maxDiskSpace > 1000){
			$maxDiskSpace		= round($maxDiskSpace/1000*100)/100;
			$maxDiskSpace		.= 'GB';
		}else{
			$maxDiskSpace		.= 'MB';
		}

		/* 사용 용량 */
		$usedDiskSpace = $this->usedmodel->get_disk_space_format($this->config_system['usedDiskSpace']);
		$result = $this->usedmodel->used_limit_check();

		/* 디스크 사용율 */
		$usedSpacePercent = $this->usedmodel->get_used_space_percent();

		/* 트래픽제한 */
		$trafficLimit = $this->config_system['service']['traffic'];

		/* 통계 */
		$caching_time	= $this->chk_stats_caching();

		/* 출고예약량 */
		$cfg_reservation = config_load('reservation');
		$this->template->assign(array(
			'expireDay'					=> $expireDay,
			'remainExpireDay'		=> $remainExpireDay,
			'maxDiskSpace'			=> $maxDiskSpace,
			'maxDiskSpaceGiga'		=> $maxDiskSpaceGiga,
			'usedDiskSpace'			=> $usedDiskSpace,
			'usedSpacePercent'		=> $usedSpacePercent,
			'trafficLimit'					=> $trafficLimit,
			'cfg_reservation'			=> $cfg_reservation,
			'eventCount'				=> $eventCount,
			'giftCount'					=> $giftCount,
			'saleCount'					=> $saleCount,
			'main'						=> true
		));

		$this->admin_menu();
		$this->tempate_modules();

		// 트위터 기본앱 관련 공지 #19795 2018-06-27 hed
		// 페이스북 공지를 그대로 활용
		if($this->arrSns['key_t'] == "ifHWJYpPA2ZGYDrdc5wQ" && $this->arrSns['use_t'] == "1" && date('Ymd') <= '20180713'){
			$facebook_notice['content']	= readurl(get_connet_protocol()."interface.firstmall.kr/firstmall_plus/request.php?cmd=getGabiaPannel&code=twitter_notice");
			$facebook_notice['title']	= '트위터 기본앱 서비스 중단 안내';
			$facebook_notice['width']	= 800;
			$facebook_notice['height']	= 800;
		} else {
			$facebook_notice = 0;
		}

		$this->template->assign($this->get_main_count());
		$this->template->assign($this->get_main_new());
		$this->template->assign('facebook_notice', $facebook_notice);
		$this->template->assign('npayver',"npay".$cfg_naverpay['version']);
		$this->template->assign('last_reload',$caching_time);
		$this->template->assign(array('cfg_reservation',$cfg_reservation));
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	// 2013-10-25 lwh 트래픽 데이터 호출
	public function get_traffic_data($domain){
		if	($this->config_system['service']['hosting_code'] == 'F_SH_X'){
			$decode_arr['u']['limits']	= '0KB';
			$decode_arr['u']['usages']	= '0KB';
			$decode_arr['u']['state']	= '0';
		}else{
			if( !serviceLimit('H_EXAD') ) {//무료/임대
				$decode_arr['u'][]	= 'FR';
			}else{
				$this->load->helper('readurl');
				$requestUrl = "http://traffic.firstmall.kr/traffic.php";
				$json_traffic = readurl($requestUrl,array('domain' => $domain));
				$decode_arr = json_decode($json_traffic,true);
			}
		}

		return $decode_arr;
	}
	// 2013-10-25 lwh 트래픽 재 데이터 호출
	public function re_traffic_data(){
		if	($this->config_system['service']['hosting_code'] == 'F_SH_X'){
			$decode_arr['u']['limits']	= '0KB';
			$decode_arr['u']['usages']	= '0KB';
			$decode_arr['u']['state']	= '0';
		}else{
			if( !serviceLimit('H_EXAD')) {//무료/임대
				$decode_arr['u'][]	= 'FR';
			}else{
				$this->load->helper('readurl');
				$requestUrl = "http://traffic.firstmall.kr/traffic.php";
				$json_traffic = readurl($requestUrl,array('domain' => $_GET['domain']));
				$decode_arr = json_decode($json_traffic,true);
			}
		}

		echo implode($decode_arr['u'],"|");
	}

	public function get_main_myservice_bar($params)
	{
		//############ 서비스 설정 ##########//
		/*
			* servicetxt
			0 = 신청이미지 <img src='../skin/default/images/main/btn_s_app.gif' />
			1 = 사용중
			2 = 사용(무료)
			3 = 사용(유료)
			4 = 발행안함
			5 = CSS 사용중
			6 = 미사용
			7 = 설정

			그외 = servicetxt 그대로 표현
		*/

		$servicecnt = 0;

		/* 카카오 알림톡 */
		$this->load->model('kakaotalkmodel');
		$config_kakaotalk = $this->kakaotalkmodel->get_service();
		$serviceuse = "<span class='servicetxt_kko'></span>";
		$servicecnt++;
		$serviceHtml[$servicecnt]['name'] = "알림톡";
		$serviceHtml[$servicecnt]['link'] = "../member/kakaotalk_charge";
		$serviceHtml[$servicecnt]['servicetxt'] = $serviceuse;

		/* 문자 서비스 */
		$serviceuse = "<span class='servicetxt_sms'></span>";
		$servicecnt++;
		$serviceHtml[$servicecnt]['name'] = "문자충전";
		$serviceHtml[$servicecnt]['link'] = "../member/sms_charge";
		$serviceHtml[$servicecnt]['servicetxt'] = $serviceuse;

		/* 빅데이터 사용여부 */
		/*
		$serviceuse			= '미사용';
		$this->load->model('bigdatamodel');
		$kinds				= $this->bigdatamodel->get_kind_array();
		foreach($kinds as $kind => $text){
			$cfg_bigdata[$kind]		= config_load('bigdata_'. $kind);
			if($cfg_bigdata[$kind]['use_view_m']=='y' || $cfg_bigdata[$kind]['use_view_p']=='y'){
				$serviceuse			= '사용';
				break;
			}
		}
		$servicecnt++;
		$serviceHtml[$servicecnt]['name'] = "빅데이터";
		$serviceHtml[$servicecnt]['link'] = '../bigdata/catalog';
		$serviceHtml[$servicecnt]['servicetxt'] = $serviceuse;
		*/

		/* 자동입금확인 무통장자동확인 사용여부 */
		$autodeposit_edate	= $this->config_system['autodeposit_edate'];
		$autodeposit_count	= $this->config_system['autodeposit_count'];
		$linkUrl			= '../setting/bank';
		$serviceuse			= '미사용';
		if($this->config_system['service']['hosting_code'] == 'F_SH_X'){
			$serviceuse = "외부호스팅";
		}elseif	($autodeposit_count){
			$addService['bankda']		= (!empty($autodeposit_edate)) ? 1 : 0;
			$addService['bankda_day']	= -1;
			if(!empty($autodeposit_edate))
			{
				$addService['bankda_day'] = (strtotime($autodeposit_edate)-strtotime(date('Y-m-d')))/86400;
			}
			if($addService['bankda'])
			{
				$linkUrl = "../order/autodeposit";
				$serviceuse			= '사용';
			}
		}
		$servicecnt++;
		$serviceHtml[$servicecnt]['name'] = "자동입금";
		$serviceHtml[$servicecnt]['link'] = $linkUrl;
		$serviceHtml[$servicecnt]['servicetxt'] = $serviceuse;

		/* 구글통계 사용여부 */
		if($this->ga_auth['ga_id'] && $this->ga_auth['ga_visit'] == "Y"){
			$serviceuse			= '사용';
		}else{
			$serviceuse			= '미사용';
		}
		$servicecnt++;
		$serviceHtml[$servicecnt]['name'] = "구글통계";
		$serviceHtml[$servicecnt]['link'] = '../statistic_ga';
		$serviceHtml[$servicecnt]['servicetxt'] = $serviceuse;

		/* 실명인증 사용여부 */
		$serviceuse = '미사용';
		$realname = config_load('realname');
		$addService['realphone'] = ($realname['useRealnamephone']=="Y") ? 1 : 0;
		if($addService['realphone']){ // 휴대폰 인증
			$serviceuse = '사용';
		}
		$addService['ipin']	= $realname['useIpin']=='N' ? 0 : 1;
		if($addService['ipin']){ // ipin
			$serviceuse = '사용';
		}
		$addService['realname']	= $realname['useRealname']=='N' ? 0 : 1;
		if($addService['realname']){ // 안심체크
			$serviceuse = '사용';
		}
		$servicecnt++;
		$serviceHtml[$servicecnt]['name'] = "실명인증";
		$serviceHtml[$servicecnt]['link'] = "../setting/member?gb=realname";
		$serviceHtml[$servicecnt]['servicetxt'] = $serviceuse;

		/* 웹폰트 사용여부 */
		if($this->config_system['font_service_check_date'] < date('Y-m-d') ){
			$this->load->helper('readurl');
			$requestUrl = get_connet_protocol()."font.firstmall.kr/engine/font_list.php";
			$jsonfont = readurl($requestUrl,array('shop_no' => $this->config_system['shopSno']));
			$decode_arr = json_decode($jsonfont,true);
			$addService['font'] = 0;
			$addService['font_day'] = -1;
			if($decode_arr){
				$addService['font'] = 1;
				$font_day = $decode_arr[0]['end_date'];
				foreach($decode_arr as $key => $value){
					if($font_day > $value['end_date']) $font_day = $value['end_date'];
				}
				$addService['font_day'] = (strtotime($font_day)-strtotime(date('Y-m-d',time())))/86400;
				config_save('system',array('font_service_use'=>'y'));
			}else{
				config_save('system',array('font_service_use'=>'n'));
			}
			config_save('system',array('font_service_check_date'=>date('Y-m-d')));
		}
		$serviceuse = '미사용';
		if($this->config_system['font_service_use']=='y'){
			$serviceuse = '사용';
		}
		$servicecnt++;
		$serviceHtml[$servicecnt]['name'] = "웹폰트";
		$serviceHtml[$servicecnt]['link'] = "../design/font";
		$serviceHtml[$servicecnt]['servicetxt'] = $serviceuse;

		/* 통합결제 */
		$serviceuse = '미사용';
		$addService['pg'] = $this->config_system['pgCompany'];
		if($addService['pg']){
			$serviceuse = '사용';
		}
		$servicecnt++;
		$serviceHtml[$servicecnt]['name'] = "전자결제";
		$serviceHtml[$servicecnt]['link'] = "../setting/pg";
		$serviceHtml[$servicecnt]['servicetxt'] = $serviceuse;

		/* 보안서버 사용여부 */
		$addService['ssl_day'] = -1;
		$servicetxt = '미설치';
		if($this->config_system['ssl_multi_domain']){
			$servicetxt = '설치';
		}
		$servicecnt++;
		$serviceHtml[$servicecnt]['name'] = "보안인증서";
		$serviceHtml[$servicecnt]['link'] = "../setting/protect";
		$serviceHtml[$servicecnt]['servicetxt'] = $servicetxt;

		/* 카카오페이 */
		// 구/신버전 하나라도 사용중인지 체크하여 사용여부 노출 2018-04-19
		$servicetxt = '미사용';
		$addService['kakaopay']		= $this->config_system['not_use_kakao'];
		$addService['daumkakaopay'] = $this->config_system['not_use_daumkakaopay'];
		if($addService['kakaopay']=='n' || $addService['daumkakaopay']=='n'){
			$servicetxt = '사용';
		}
		$servicecnt++;
		$serviceHtml[$servicecnt]['name'] = "카카오페이";
		$serviceHtml[$servicecnt]['link'] = "../setting/pg";
		$serviceHtml[$servicecnt]['servicetxt'] = $servicetxt;

		/* 페이코 :: 2018-09-27 */
		$servicetxt = '미사용';
		$addService['payco']		= $this->config_system['not_use_payco'];
		if($addService['payco']=='n'){
			$servicetxt = '사용';
		}
		$servicecnt++;
		$serviceHtml[$servicecnt]['name'] = "페이코";
		$serviceHtml[$servicecnt]['link'] = "../setting/pg";
		$serviceHtml[$servicecnt]['servicetxt'] = $servicetxt;

		/* 대량메일 잔여수 */
		$servicetxt = '미사용';
		$email_mass = config_load('email_mass');
		$addService['bulkmail'] = ($email_mass['name']) ? 1 : 0;
		if($addService['bulkmail']){
			$servicetxt = '사용';
		}
		$servicecnt++;
		$serviceHtml[$servicecnt]['name'] = "대량메일";
		$serviceHtml[$servicecnt]['link'] = "../member/amail_send";
		$serviceHtml[$servicecnt]['servicetxt'] = $servicetxt;

		/* 리얼패킹 */
		$servicetxt		= '미사용';
		$real_config	= config_load('realpacking');
		if($real_config['use_service'] == 'Y'){
			$servicetxt = '사용';
		}
		$servicecnt++;
		$serviceHtml[$servicecnt]['name'] = "리얼패킹";
		$serviceHtml[$servicecnt]['link'] = "../setting/video";
		$serviceHtml[$servicecnt]['servicetxt'] = $servicetxt;

		/* 리마인드 */
		/*
		$serviceuse = '미사용';
		$remind = config_load('personal_use');
		if(in_array('y',$remind))		$remind_use = 'Y';
		else							$remind_use = 'N';
		$addService['personal_coupon_user_yn']	= ($remind_use=='N') ? 0 : 1;
		if($addService['personal_coupon_user_yn']){
			$serviceuse = '사용';
		}
		$servicecnt++;
		$serviceHtml[$servicecnt]['name'] = "리마인드";
		$serviceHtml[$servicecnt]['link'] = "../member/curation";
		$serviceHtml[$servicecnt]['servicetxt'] = $serviceuse;
		*/

		/* 택배업무 자동화 - 택배자동 */
		$serviceuse = '미사용';
		$this->load->model('invoiceapimodel');
		$invoice = $this->invoiceapimodel->get_invoice_setting();
		## 굿스플로 서비스 체크 @nsg 2015-10-20
		$this->load->model('goodsflowmodel');
		$goodsflow = $this->goodsflowmodel->get_goodsflow_setting();
		## 우체국자동화서비스 체크 :: 2016-04-12 lwh
		$this->load->model('epostmodel');
		$epost = $this->epostmodel->get_epost_requestkey();
		if($invoice['hlc']['use'] || ($goodsflow['gf_use']=='Y'&&$goodsflow['goodsflow_step']=='1') || ($epost['epost_use']=='Y'&&$epost['status']=='9') ){
			$serviceuse = '사용';
		}
		$servicecnt++;
		$serviceHtml[$servicecnt]['name'] = "택배자동";
		$serviceHtml[$servicecnt]['link'] = "../setting/delivery_company";
		$serviceHtml[$servicecnt]['servicetxt'] = $serviceuse;

		/* 네이버톡톡 사용여부 */
		$serviceuse = '미사용';
		$cfg_snssocial = config_load('snssocial');
		if( $cfg_snssocial['ntalk_connect'] == 'Y' ){
			$serviceuse = '사용';
		}
		$servicecnt++;
		$serviceHtml[$servicecnt]['name'] = "네이버톡톡";
		$serviceHtml[$servicecnt]['link'] = "../setting/snsconf";
		$serviceHtml[$servicecnt]['servicetxt'] = $serviceuse;

		/* 속도(캐시) 사용여부 */
		$serviceuse = '미사용';
		if($this->config_system['display_cach']!='OFF'){
			$serviceuse = '사용';
		}
		$servicecnt++;
		$serviceHtml[$servicecnt]['name'] = "속도(캐시)";
		$serviceHtml[$servicecnt]['link'] = '../setting/protect';
		$serviceHtml[$servicecnt]['servicetxt'] = $serviceuse;

		/* 네이버페이 */
		$serviceuse = '미사용';
		if($params['cfg_naverpay']['use'] == 'y') $serviceuse = '사용';
		$servicecnt++;
		$serviceHtml[$servicecnt]['name'] = "네이버페이";
		$serviceHtml[$servicecnt]['link'] = '../marketing/marketplace_url';
		$serviceHtml[$servicecnt]['servicetxt'] = $serviceuse;

		/* 하이웍스 */
		$serviceuse = '미사용';
		if($this->config_system['webmail_admin_id'] && $this->config_system['webmail_domain'] && $this->config_system['webmail_key']) $serviceuse = '사용';
		$servicecnt++;
		$serviceHtml[$servicecnt]['name'] = "하이웍스";
		$serviceHtml[$servicecnt]['link'] = '../setting/sale';
		$serviceHtml[$servicecnt]['servicetxt'] = $serviceuse;

		/* 네이버쇼핑 */
		$naver_use			= $this->config_basic['naver_use']; # EP 2.0
		if( $naver_use != 'Y' && $this->config_basic['naver_third_use'] ) $naver_use = $this->config_basic['naver_third_use'];
		$serviceuse = '미사용';
		if($naver_use=='Y') $serviceuse = '사용';
		$servicecnt++;
		$serviceHtml[$servicecnt]['name'] = "네이버쇼핑";
		$serviceHtml[$servicecnt]['link'] = '../marketing/marketplace_url';
		$serviceHtml[$servicecnt]['servicetxt'] = $serviceuse;

		/* 다음쇼핑 */
		$daum_use			= $this->config_basic['daum_use'];
		$serviceuse = '미사용';
		if( $daum_use =='Y' ) $serviceuse = '사용';
		$servicecnt++;
		$serviceHtml[$servicecnt]['name'] = "다음쇼핑";
		$serviceHtml[$servicecnt]['link'] = '../marketing/marketplace_url';
		$serviceHtml[$servicecnt]['servicetxt'] = $serviceuse;

		unset($params);
		$params['shopSno']	= $this->config_system['shopSno'];
		$call_url			= 'http://userapp.firstmall.kr/getmobileapprelease';
		$read_data			= readurl($call_url,$params);
		$service_res		= json_decode($read_data,true);

		/* 쇼핑몰앱 android */
		$serviceuse = '미사용';
		if($service_res['ANDROID']['data']['status'] == '80')  $serviceuse = '사용';
		$servicecnt++;
		$serviceHtml[$servicecnt]['name'] = "Android 앱";
		$serviceHtml[$servicecnt]['link'] = '/admin/mobile_app/setting';
		$serviceHtml[$servicecnt]['servicetxt'] = $serviceuse;

		/* 쇼핑몰앱 ios */
		$daum_use			= $this->config_basic['daum_use'];
		$serviceuse = '미사용';
		if($service_res['IOS']['data']['status'] == '80')  $serviceuse = '사용';
		$servicecnt++;
		$serviceHtml[$servicecnt]['name'] = "iOS 앱";
		$serviceHtml[$servicecnt]['link'] = '/admin/mobile_app/setting';
		$serviceHtml[$servicecnt]['servicetxt'] = $serviceuse;

		$this->template->assign('addService', $addService);
		$this->template->assign('serviceHtml', $serviceHtml);
	}

	/* 공지사항 영역 Define */
	public function json_main_news_area(){
		$this->load->helper('text');
		$this->load->library('SofeeXmlParser');
		$channel	= $_POST['channel'];
		$today	= date("Y-m-d",time());
		switch($channel){
			case "notice" :
				$rss_url	= get_connet_protocol()."firstmall.kr/ec_hosting/rss/_notice/rss.php?channel=notice&solution=firstmall_plus&limit=4";
				break;
			case "upgrade" :
				$rss_url	= get_connet_protocol()."firstmall.kr/ec_hosting/rss/_notice/rss.php?channel=upgrade&solution=firstmall_plus&shopSno={$this->config_system['shopSno']}&service_type=".SERVICE_CODE."_GL&limit=4";
				break;
			case "upgrade_news" :
				$rss_url	= get_connet_protocol()."firstmall.kr/ec_hosting/rss/_notice/rss.php?channel=upgrade_news&solution=firstmall_plus&limit=4";
				break;
			case "education" :
				$rss_url	= get_connet_protocol()."firstmall.kr/ec_hosting/rss/_notice/rss.php?channel=education&solution=firstmall_plus&shopSno={$this->config_system['shopSno']}&service_type=".SERVICE_CODE."&limit=4";
				break;
		}
		$xmlParser = new SofeeXmlParser();
		$xmlParser->parseFile($rss_url);
		$tree = $xmlParser->getTree();
		$mainNewsNoticeList = $tree['rss']['channel']['item'];
		foreach($mainNewsNoticeList as $k => $data)
		{
			$data['pubDateStatus']			= 'ing';
			$data['pubDateStatusMsg']	= '접수';
			if( $today >= $data['pubDate']['value'] )
			{
				$data['pubDateStatus']			= 'end';
				$data['pubDateStatusMsg']	= '마감';
			}
			$data['pubDate']['value']	= str_replace('-','.',substr($data['pubDate']['value'],5));
			$data['title']['value']		= strip_tags($data['title']['value']);
			if(!isset($data['link'])) $data['link']['value'] = '';
			$mainNewsNoticeList[$k]	= $data;

		}
		echo json_encode($mainNewsNoticeList);
	}

	/* 관리자메모 영역 Define */
	public function _define_admin_memo_area(){
		$this->template->define(array('admin_memo_area'=>$this->skin."/main/_admin_memo_area.html"));
	}

	// 카카오 알림톡 건수 조회
	public function get_kt_info(){
		$this->load->model('kakaotalkmodel');
		$config_kakaotalk = $this->kakaotalkmodel->get_service();
		$data['getType']	= 'C';
		$data['year']		= date('Y');
		$kakaotalk_info		= $this->kakaotalkmodel->get_charge_log($data);

		echo json_encode($kakaotalk_info);
	}

	public function get_sms_info(){
		/* SMS 건수 */
		include_once $_SERVER['DOCUMENT_ROOT']."/app/libraries/sms.class.php";
		$auth = config_load('master');
		$sms_id = $this->config_system['service']['sms_id'];
		$sms_api_key = $auth['sms_auth'];

		//$sms_send	= new SMS_SEND();
		$gabiaSmsApi = new gabiaSmsApi($sms_id,$sms_api_key);

		$params	= "sms_id=" . $sms_id . '&sms_pw=' . md5($sms_id);
		$params = makeEncriptParam($params);
		$limit	= $gabiaSmsApi->getSmsCount();
		$sms_chk = $sms_id;

		$int_sms = (int) $limit;

		if($int_sms<50){
			$popicon = "charge";
		}

		$return = array();
		if ($popicon=="charge") {
			$return['html'] = sprintf("<div class='myservice_area'><div class='myservice_%s'><img src='/admin/skin/default/images/main/icon_%s.gif' /></div>",$popicon,$popicon);
		} else {
			$return['html'] = "<div class='myservice_area'></div>";
		}

		$return['txt_cnt'] = $int_sms."통";

		echo json_encode($return);
	}


	/* 게시판마일리지지급 Define */
	public function _define_board_emoney_form(){
		$reserves = ($this->reserves)?$this->reserves:config_load('reserve');
		$this->template->assign('reserve_goods_review',$reserves['reserve_goods_review']);
		$this->template->define(array('emoneyform'=>$this->skin.'/board/_emoney.html'));
	}

	public function login(){
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	/* 메인 페이지 통계 캐쉬 생성 시간 체크 */
	public function chk_stats_caching()
	{
		$cache_file_path	= $this->cach_file_path . $this->cach_stat_file;
		//if (!file_exists($cache_file_path) || strtotime('-4 hour') > filemtime($cache_file_path))
		{
			$this->main_stats_caching();
		}

		return filemtime($cache_file_path);
	}

	/* 메인 페이지 통계 캐쉬 처리 */
	public function main_stats_caching()
	{
		ob_start();
		$this->load->model('usedmodel');
		$result = $this->usedmodel->used_service_check('advanced_statistic');
		if(!$result['type']){
			$this->advanced_statistic_limit	= 'y';
		}
		$this->load->model('statsmodel');
		$result	= $this->statsmodel->get_main_statistic_json();
		echo json_encode($result);

		$cach_stats	= ob_get_contents();
		ob_end_clean();

		$cache_file_path	= $this->cach_file_path . $this->cach_stat_file;

		$file_obj	= fopen($cache_file_path, 'w+');
		if	(!$file_obj){
			$dir_name	= dirname($cache_file_path);
			if( !is_dir($dir_name) )	@mkdir($dir_name);
			@chmod($dir_name,0777);
			$file_obj	= fopen($cache_file_path, 'w+');
		}

		fwrite($file_obj, $cach_stats);
		fclose($file_obj);
	}

	/* 메인 페이지 통계 캐쉬 제거 */
	public function main_stats_cach_delete()
	{
		// 운영자별 페이지 생성 체크
		$cache_file_path	= $this->cach_file_path . $this->cach_stat_file;
		if	(file_exists($cache_file_path)){
			@unlink($cache_file_path);
		}
		$cache_file_path	= ROOTPATH . 'data/cach/action_alert.html';
		if	(file_exists($cache_file_path)){
			@unlink($cache_file_path);
		}
		echo json_encode(array('result'=>'OK'));
	}

	public function json_main_stats()
	{
		$filePath	= $this->cach_file_path . $this->cach_stat_file;
		if(file_exists($filePath)) {
			$handle			= fopen($filePath, "r");
			$fileContents	= fread($handle, filesize($filePath));
			fclose($handle);

			if($_GET['mode'] == 'debug'){
				debug(json_decode($fileContents));
				exit;
			}

			if(!empty($fileContents)) {
				echo $fileContents;
			}
		}
	}

	public function popup_change_pass()
	{
		$this->template->define(array('tpl'=>$this->skin."/main/popup_change_pass.html"));
		$this->template->print_("tpl");
	}

	// 부가서비스 이슈 알림
	public function get_notify_info(){

		$this->load->model('usedmodel');

		$return = array();

		/* 페이스북 좋아요 연결방식 변경권유 */
		$snssocial = ($this->arrSns)?$this->arrSns:config_load('snssocial');
		if( $snssocial['fb_like_box_type']=='OP' && ( !($snssocial['key_f'] != '455616624457601' && $snssocial['facebook_publish_actions']) || $snssocial['key_f'] == '455616624457601' ) ) {//전용앱중에서 오픈그라피제공앱은 제외@2015-07-14
			$return['fb_like_box_type'] = $snssocial['fb_like_box_type'];
		}

		$cfg_addservie_notify = config_load('addservie_notify');

		/* SMS 건수 */
		$sms = commonCountSMS();
		if($cfg_addservie_notify['sms_primary_complete']!='Y' && 31 <= $sms && $sms <= 50){
			$return['remain_sms'] = (int)preg_replace("/[^0-9]/",'',$sms);
			config_save('addservie_notify',array('sms_primary_complete'=>'Y'));
		}
		if($cfg_addservie_notify['sms_finally_complete']!='Y' && 1 <= $sms && $sms <= 30){
			$return['remain_sms'] = (int)preg_replace("/[^0-9]/",'',$sms);
			config_save('addservie_notify',array('sms_finally_complete'=>'Y'));
		}

		/* SMS 발신 번호 등록*/
		if($sms){
			$send_sms_phone = getSmsSendInfo();
			if(!$send_sms_phone){
				$return['send_sms'] = true;
			}
		}

		/* 자동입금확인 */
		$edate = $this->config_system['autodeposit_count'];
		$remain = round((strtotime($edate)-time()) / (3600*24));
		if($cfg_addservie_notify['autodeposit_primary_complete']!='Y' && 11 <= $remain && $remain <= 20){
			$return['remain_autodeposit'] = $this->config_system['autodeposit_edate'];
			config_save('addservie_notify',array('autodeposit_primary_complete'=>'Y'));
		}
		if($cfg_addservie_notify['autodeposit_finally_complete']!='Y' && 1 <= $remain && $remain <= 10){
			$return['remain_autodeposit'] = $this->config_system['autodeposit_edate'];
			config_save('addservie_notify',array('autodeposit_finally_complete'=>'Y'));
		}

		/* 굿스플로 */
		$goodsflow = $this->usedmodel->used_get_service_info('view');
		if($cfg_addservie_notify['goodsflow_primary_complete']!='Y' && 31 <= $goodsflow && $goodsflow <= 50){
			$return['remain_goodsflow'] = (int)preg_replace("/[^0-9]/",'',$goodsflow);
			config_save('addservie_notify',array('goodsflow_primary_complete'=>'Y'));
		}
		if($cfg_addservie_notify['goodsflow_finally_complete']!='Y' && 1 <= $goodsflow && $goodsflow <= 30){
			$return['remain_goodsflow'] = (int)preg_replace("/[^0-9]/",'',$goodsflow);
			config_save('addservie_notify',array('goodsflow_finally_complete'=>'Y'));
		}

		if(!preg_match("/^F_SH_/",$this->config_system['service']['hosting_code'])){

			/*lek 오늘하루그만보기 추가*/
			$cookie = get_cookie('isChk');
			$disk_percent	= $this->usedmodel->get_used_space_percent();
			if($disk_percent >= 90 && empty($cookie))	$return['space_percent']	= $disk_percent;
		}

		/* 우체국택배 */
		if($cfg_addservie_notify['epost_message_complete']=='N'){
			config_save('addservie_notify',array('epost_message_complete'=>''));
			$return['epost_complete'] = 'Y';
		}
		
		// 인증후에서 수신 인증서가 없을 경우, 향후 수동 설치된 인증서 여부를 파악하기 위해서는 기존 ssl정보를 추적해야함 by hed
		$this->load->library('ssllib');
		$sslEnv = $this->ssllib->getSslEnvironment();
		if(empty($sslEnv['data'])){
			$return['ssl_notify'] = 'Y';
		}

		if($return){
			$this->template->assign("return",$return);
			$this->template->define(array('tpl'=>$this->skin."/main/_main_notify_popup.html"));
			$return['html'] = $this->template->fetch("tpl");
		}else{
			$return['html'] = '';
		}

		echo json_encode($return);
	}

	//데모 기능제한 팝업
	public function main_demo()
	{
		$this->tempate_modules();
		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	//무료몰 기능제한 팝업
	public function main_free()
	{
		$this->tempate_modules();
		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}
}

/* End of file main.php */
/* Location: ./app/controllers/admin/main.php */
