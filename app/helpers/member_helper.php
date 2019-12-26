<?php

	/**
	* login/member/sns _process >>member session
	* @2014-01-21
	*/
	function create_member_session($data=array()){
		$CI =& get_instance();
		$data['rute'] = ($data['rute']!='f' && $data['sns_f'])?'facebook':$data['rute'];

		// 사업자 회원일 경우 업체명->이름
		if($data['business_seq']){
			$data['user_name'] = $data['bname'];
		}

		if(!$data['password_update_date']){
			if($data['lastlogin_date']){
				$data['password_update_date'] = $data['lastlogin_date'];
			}else{
				$data['password_update_date'] = $data['regist_date'];
			}
		}
		
		$CI->load->model('couponmodel');
		$CI->load->helper('coupon'); 
		$sc['member_seq']	= $data['member_seq']; 
		if( !empty($data['birthday']) && $data['birthday'] != '0000-00-00' ) {
			$data['thisyear_birthday'] = date("Y").substr($data['birthday'],4,6);
			if(checkdate(substr($data['thisyear_birthday'],5,2),substr($data['thisyear_birthday'],8,2),substr($data['thisyear_birthday'],0,4)) != true) {
				$data['thisyear_birthday'] = date("Y-m-d",strtotime('-1 day', strtotime($data['thisyear_birthday'])));
			}
			//한국나이
			$birthyear = date("Y", strtotime($data['birthday'])); //생년
			$nowyear = date("Y"); //현재년도
			$data['birthday_age'] = $nowyear-$birthyear+1; 
		}

		if ( !empty($data['anniversary']) ) {
			$data['thisyear_anniversary'] = date("Y").'-'.$data['anniversary'];//기념일(mm-dd) 추가
			if(checkdate(substr($data['thisyear_anniversary'],5,2),substr($data['thisyear_anniversary'],8,2),substr($data['thisyear_anniversary'],0,4)) != true) {
				$data['thisyear_anniversary'] = date("Y-m-d",strtotime('-1 day', strtotime($data['thisyear_anniversary'])));
			}
		}

		$mb_group_sort = mb_group_sort();//회원등급순서 재정렬
		//등급조정쿠폰의 등업된 경우에만 다운가능
		if ($data['grade_update_date'] != '0000-00-00 00:00:00') {
			$fm_member_group_logsql = "select * from fm_member_group_log where member_seq = '".$data['member_seq']."' order by regist_date desc limit 0,1";
			$fm_member_group_logquery = $CI->db->query($fm_member_group_logsql);  
			$fm_member_group_log =  $fm_member_group_logquery->row_array(); 
			if( ($mb_group_sort[$fm_member_group_log['prev_group_seq']] >= $mb_group_sort[$fm_member_group_log['chg_group_seq']]) || ($data['group_seq'] == 1) ) {
				$data['grade_update_date'] = '';
			}
		}else{
			$data['grade_update_date'] = substr($data['regist_date'],0,10);
		}

		if($data['birthday'] == '0000-00-00') $data['birthday'] ='';
		if($data['anniversary'] == '00-00') $data['anniversary'] =''; 
		$sc['year']			= date('Y',time());
		$sc['month']		= date('Y-m',time());
		$sc['today']	= date('Y-m-d',time());
		$couponpopupuse = config_load('couponpopupuse');
		unset($couponpopup);
		if( $couponpopupuse['birthday_popup_use'] == 'Y' ) $couponpopup[] = "birthday"; 
		if( $couponpopupuse['anniversary_popup_use'] == 'Y' ) $couponpopup[] = "anniversary"; 
		//if( $couponpopupuse['membergroup_popup_use'] == 'Y' ) $couponpopup[] = "membergroup"; 
		if( $couponpopupuse['memberGroup_popup_use'] == 'Y' ) $couponpopup[] = "memberGroup";  
		foreach($couponpopup as $coupontype) {
			if(!$coupontype)continue;
			unset($sc['coupon_type']);
			if( in_array($coupontype, array("memberGroup","membergroup")) ) {//배송비포함된 쿠폰
				$sc['coupon_type'][]		= $coupontype;
				$sc['coupon_type'][]		= $coupontype."_shipping";
			}else{
				$sc['coupon_type'][]		= $coupontype;
			}
			
			$coupondata = $CI->couponmodel->get_my_download($sc,$data,'totalcnt');
			$data['coupon_'.$coupontype.'_count'] = $coupondata['count'];
		}

		$member_data = array(
			'member_seq'			=> $data['member_seq'],
			'userid'				=> $data['userid'],
			'user_name'				=> $data['user_name'],
			'birthday'				=> $data['birthday'],
			'sex'					=> $data['sex'],
			'group_seq'				=> $data['group_seq'],
			'group_name'			=> $data['group_name'],
			'rute'					=> substr($data['rute'],0,1),
			'gnb_icon_view'			=> $data['gnb_icon_view'],
			'password_update_date'	=> $data['password_update_date'],
			'coupon_birthday_count'				=> $data['coupon_birthday_count'], 
			'coupon_anniversary_count'		=> $data['coupon_anniversary_count'], 
			'coupon_membergroup_count'		=> $data['coupon_memberGroup_count'],
			'member_type'			=> $data['mtype'],
		    'business_type'         => $data['btype']
		);
		
		$tmp = config_load('member');
		if(isset($tmp['sessLimit']) && $tmp['sessLimit']=='Y'){
			$limit = 60 * $tmp['sessLimitMin'];
			$CI->session->sess_expiration = $limit;
		}
		$CI->session->set_userdata(array('user'=>$member_data));
	}
	
	//session update
	function couponsave_member_session($couponData){
		$CI =& get_instance();
		$sess_user = ( $CI->session->userdata('user') )?$CI->session->userdata('user'):$_SESSION['user'];
		//쿠폰새창노출시 쿠폰건수 차감
		if( in_array($couponData['type'],$CI->couponmodel->couponpagetype['mypage']) && $CI->userInfo['coupon_'.$couponData['type'].'_count'] ) {
			$sess_user['coupon_'.$couponData['type'].'_count'] = ($sess_user['coupon_'.$couponData['type'].'_count']==1)?0:($sess_user['coupon_'.$couponData['type'].'_count']-1);
			$CI->session->set_userdata('user',$sess_user);
		}
	}

	function memberIconConf($num=null) {
		$memberIcondata = array('default01.png','default02.png','default03.png','default04.png','default05.png','default06.png','default07.png','default08.png','default09.png','default10.png','default11.png','default12.png','default13.png','default14.png');
		return ($num)?$memberIcondata[$num-1]:$memberIcondata;
	}

	/**
	* 회원등급 정렬순서 재정의 @2016-05-17
	**Array
	(
		[1] => 1
		[4] => 2
		[2] => 3
		[3] => 4
	)
	**/
	function mb_group_sort() {
		$CI =& get_instance(); 
		$sql = "select * from fm_member_group order by order_sum_price asc, order_sum_cnt asc, sale_price asc";
		$query = $CI->db->query($sql);
		if($query->num_rows() < 1) return;
		foreach ($query->result_array() as $row){$sort_num++;
			$group_sort[$row['group_seq']] = $sort_num;
		}
		return $group_sort;
	}
// END
/* End of file member_helper.php */
/* Location: ./app/helpers/member_helper.php */