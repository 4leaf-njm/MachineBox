<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class batch extends admin_base {

	public function __construct() {
		parent::__construct();
		$this->load->helper('member');
		$this->template->assign('mname',$this->managerInfo['mname']);
		$this->template->define('member_search',$this->skin.'/member/member_search.html');

		// 보안키 입력창
		$member_download_info = $this->skin.'/member/member_download_info.html';
		$this->template->define(array("member_download_info"=>$member_download_info));

	}

	public function index()
	{
		redirect("/admin/member/catalog");
	}

	### 회원리스트
	public function member_catalog()
	{
		$this->load->model('snsmember');
		$this->load->model('membermodel');
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();
		
		if($_GET['keyword'] == "이름, 아이디, 이메일, 전화번호, 핸드폰(뒷자리4), 주소, 닉네임"){
			$_GET['keyword'] = "";
		}
		
		// 개인 정보 조회 로그
		// $type,$manager_seq,$type_seq
		$this->load->model('logPersonalInformation');
		$this->logPersonalInformation->insert('memberlist',$this->managerInfo['manager_seq'],'');

		for ($m=1;$m<=12;$m++){	$m_arr[] = str_pad($m, 2, '0', STR_PAD_LEFT); }
		for ($d=1;$d<=31;$d++){	$d_arr[] = str_pad($d, 2, '0', STR_PAD_LEFT); }
		$this->template->assign('m_arr',$m_arr);
		$this->template->assign('d_arr',$d_arr);

		#### AUTH
		$auth_act		= $this->authmodel->manager_limit_act('member_act');
		if(isset($auth_act)) $this->template->assign('auth_act',$auth_act);
		$auth_promotion = $this->authmodel->manager_limit_act('member_promotion');
		if(isset($auth_promotion)) $this->template->assign('auth_promotion',$auth_promotion);
		$auth_send	= $this->authmodel->manager_limit_act('member_send');
		if(isset($auth_send)) $this->template->assign('auth_send',$auth_send);

		// 회원정보다운로드 체크
		if ($this->managerInfo['manager_yn']=='Y') {
			$this->load->model('managermodel');
			$mg_info =$this->managermodel->get_manager($this->managerInfo['manager_seq']);
			$mg_auth_arr = explode("||",$mg_info['manager_auth']);
			$mg_auth = array();

			$auth_member_down = false;
			foreach($mg_auth_arr as $k){
				$tmp_arr = explode("=",$k);
				$mg_auth[$tmp_arr[0]] = $tmp_arr[1];
			}

			if ($mg_auth['member_download']=='Y') {
				$auth_member_down = true;
			}
		} else {
			$auth_member_down	= $this->authmodel->manager_limit_act('member_download');
		}
		if(isset($auth_member_down)) $this->template->assign('auth_member_down',$auth_member_down);

		###
		if($_GET['header_search_keyword']) $_GET['keyword'] = $_GET['header_search_keyword'];

		### GROUP
		$group_arr = $this->membermodel->find_group_list();

		### SEARCH
		//print_r($_POST);
		$sc = $_GET;
		$sc['orderby']			= (isset($_GET['orderby'])) ?	$_GET['orderby']:'A.member_seq';
		$sc['sort']				= (isset($_GET['sort'])) ?		$_GET['sort']:'desc';
		$sc['page']				= (isset($_GET['page'])) ?		intval($_GET['page']):0;
		$sc['perpage']			= (isset($_GET['perpage'])) ?	intval($_GET['perpage']):50;

		$sc['pageType'] = "search";

		// 판매환경
		if( $_GET['sitetype'] ){
			$sc['sitetype'] = implode('\',\'',$_GET['sitetype']);
		}

		// 가입양식	if( $_GET['rute'] )$sc['rute'] = implode('\',\'',$_GET['rute']);
 		if( $_GET['snsrute'] ) {
			foreach($_GET['snsrute'] as $key=>$val){$sc[$val] = 1;}
		}
        
		$sc['page'] = '0';
		$sc['perpage'] = '1000000000000';
		
		if($_GET['callPage'] == 'dealer' || $_GET['callPage'] == 'estimate')
		    $sc['main_dealer'] = 'y';
		### MEMBER
		//$data = $this->membermodel->admin_member_list($sc); //kmj
		$data = $this->membermodel->admin_member_list_spout($sc);
		if(count($data['grade_cnt']) > 1){
			$member_grade_seq	= "";
			$member_grade_name	= "";
		}else{
			$member_grade_seq	= $data['grade_cnt'][0]['group_seq'];
			$member_grade_name	= $data['grade_cnt'][0]['group_name'];
		}

		$this->template->assign(array('member_grade_seq'=>$member_grade_seq,'member_grade_name'=>$member_grade_name));

		### PAGE & DATA
		$sc['searchcount']	 = $data['count'];
		$sc['total_page']	 = ceil($sc['searchcount']	 / $sc['perpage']);
		$cntquery = $this->db->query("select count(*) as cnt from fm_member where status in ('done','hold','dormancy')");
		$cntrow = $cntquery->result_array();
		$sc['totalcount'] = $cntrow[0]['cnt'];

		$idx = 0;
		$this->load->model('Goodsreview','Boardmodel');//리뷰건
		foreach($data['result'] as $datarow){
			$idx++;
			$datarow['number']	= $sc['searchcount']	 - ( ($sc['page'] -1 ) * 1 + $idx + 1) + 1;

			$adddata = $this->membermodel->get_member_seq_only($datarow['member_seq']);
			$datarow['email'] = $adddata['email'];
			$datarow['phone'] = $adddata['phone'];
			$datarow['cellphone'] = $adddata['cellphone'];
			$datarow['group_name'] = $adddata['group_name'];

			$datarow['type']	= $datarow['business_seq'] ? '기업' : '개인';
            
			if($datarow['business_seq']){
				$datarow['user_name'] = $datarow['bname'];
				$datarow['cellphone'] = $datarow['bcellphone'];
				$datarow['phone'] = $datarow['bphone'];
				
				$query = "select label_value as gubun from fm_member_subinfo where label_title = '회원구분' and member_seq = ".$datarow['member_seq'];
				$query = $this->db->query($query);
				$datarow['gubun'] = $query->row()->gubun == '기업회원' ? '기업' : '딜러';
			} else {
			    $datarow['gubun'] = '개인';
			}
			###
			//$temp_arr	= $this->membermodel->get_order_count($datarow['member_seq']);
			//$datarow['member_order_cnt']	= $temp_arr['cnt'];

			//리뷰건
			$sc['whereis'] = ' and mseq='.$datarow['member_seq'];
			$sc['select'] = ' count(gid) as cnt ';
			$gdreviewquery = $this->Boardmodel->get_data($sc);
			$datarow['gdreview_sum'] = $gdreviewquery['cnt'];

			if($datarow['rute'] != "none" ) {
				$snsmbsc['select'] = ' * ';
				$snsmbsc['whereis'] = ' and member_seq = \''.$datarow['member_seq'].'\' ';
				$snslist = $this->snsmember->snsmb_list($snsmbsc);
				if($snslist['result'][0]) $datarow['snslist'] = $snslist['result'];
			}

			/****/

			$dataloop[] = $datarow;
		}

		## 유입경로 그룹
		$this->load->model('statsmodel');
		$referer_list	= $this->statsmodel->get_referer_grouplist();
		$this->template->assign('referer_list',$referer_list);

		
		###
		if(isset($data)) $this->template->assign('loop',$dataloop);
		
		$paginlay = pagingtag($sc['searchcount'], $sc['perpage'], 'javascript:searchPaging(\'', getLinkFilter('',array_keys($sc)).'\');' );
		
		$paginlay = str_replace("&amp;", "&", $paginlay);
		$paginlay = str_replace("?", "&", $paginlay); //검색 안돼서 추가 18.04.03 kmj
		
		if(empty($paginlay))$paginlay = '<p><a class="on red">1</a><p>';

		//가입환경
		$sitetypeloop = sitetype($_GET['sitetype'], 'image', 'array');
		$this->template->assign('sitetypeloop',$sitetypeloop);

		//가입양식
		$ruteloop = memberrute($_GET['rute'], 'image', 'array');
		$this->template->assign('ruteloop',$ruteloop);

		$this->template->assign('pagin',$paginlay);
		$this->template->assign('group_arr',$group_arr);
		$this->template->assign('perpage',$sc['perpage']);
		$this->template->assign('dormancy_count',$data['dormancy_count']);

		$this->template->assign('sc',$sc);

		$this->template->assign('callPage',$_GET['callPage']);
		$this->template->assign('pageType',"search");
		$this->template->assign('loadType',"layer");
		$this->template->assign('query_string',get_query_string());

		$this->template->define('member_list',$this->skin.'/member/member_list.html');
		$this->template->define('member_search',$this->skin.'/member/member_search.html');
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	
	//이메일 수동 발송
	public function email_form()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		$auth_send	= $this->authmodel->manager_limit_act('member_send');
		
		if(!$auth_send){
			echo "<script>alert('권한이 없습니다.'); self.close();</script>";
			exit;
		}


		// 회원정보다운로드 체크
		if ($this->managerInfo['manager_yn']=='Y') {
			$auth_member_down = true;
		} else {
			$auth_member_down	= $this->authmodel->manager_limit_act('member_download');
		}
		if(isset($auth_member_down)) $this->template->assign('auth_member_down',$auth_member_down);
		

		$basic = ($this->config_basic)?$this->config_basic:config_load('basic');

		###
		$query = $this->db->query("select * from fm_log_email order by seq desc limit 10");
		$emailData = $query->result_array();

		###
		$this->load->model('usedmodel');
		$email_chk = $this->usedmodel->hosting_check();
		$this->template->assign('email_chk',$email_chk);
		$this->template->assign('verify',$this->config_system['shopSno']);

		###
		//$mInfo['total'] = get_rows('fm_member',array('status !='=>'withdrawal'));
		
		$this->template->assign(array('mail_count'=>master_mail_count(),'email'=>$basic['companyEmail']));
		$this->template->assign('loop',$emailData);
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	//SMS 대량 발송
	public function sms(){
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		// smsid, key 가 없을때 블락처리 :: 2018-09-12 pjw
		$auth			= config_load('master');
		$sms_id			= $this->config_system['service']['sms_id'];
		$sms_api_key	= $auth['sms_auth'];
		if($sms_id == ''){
			echo "<script>alert('SMS 아이디가 없습니다.'); document.location.replace('/admin/member/sms');</script>";
			exit;
		}else if($sms_api_key == ''){
			echo "<script>alert('등록 된 SMS 인증키가 없습니다.'); document.location.replace('/admin/member/sms');</script>";
			exit;
		}

		$auth_send	= $this->authmodel->manager_limit_act('member_send');
		if(!$auth_send){
			echo "<script>alert('권한이 없습니다.'); history.back();</script>";
			exit;
		}

		$send_phone = getSmsSendInfo();
		if(isset($send_phone)) $this->template->assign('send_phone',$send_phone);
		
		// 회원정보다운로드 체크
		if ($this->managerInfo['manager_yn']=='Y') {
			$auth_member_down = true;
		} else {
			$auth_member_down	= $this->authmodel->manager_limit_act('member_download');
		}
		if(isset($auth_member_down)) $this->template->assign('auth_member_down',$auth_member_down);


		if(isset($auth_send)) $this->template->assign('auth_send',$auth_send);
		$auth_promotion = $this->authmodel->manager_limit_act('member_promotion');
		if(isset($auth_promotion)) $this->template->assign('auth_promotion',$auth_promotion);

		$this->load->model("smsmodel");
		$return = $this->smsmodel->sms_auth_check();
		
		if($return['code'] != "200"){
			if($return['code'] == "203"){
				echo "<script>alert('인증 시간이 만료되었습니다. 다시 인증해 주십시오.'); document.location.replace('/admin/batch/sms_hp_auth');</script>";
				exit;
			}else{
				echo "<script>alert('".$return['msg']."'); </script>";
				exit;
			}
		}	
		
		$this->load->model("membermodel");

		$sc = $_GET;
		$sc['orderby']			= (isset($_GET['orderby'])) ?	$_GET['orderby']:'A.member_seq';
		$sc['sort']				= (isset($_GET['sort'])) ?		$_GET['sort']:'desc';
		$sc['page']				= (isset($_GET['page'])) ?		intval($_GET['page']):0;
		$sc['perpage']			= (isset($_GET['perpage'])) ?	intval($_GET['perpage']):10;

		// 판매환경
		if( $_GET['sitetype'] ){
			$sc['sitetype'] = implode('\',\'',$_GET['sitetype']);
		}

		// 가입양식	if( $_GET['rute'] )$sc['rute'] = implode('\',\'',$_GET['rute']);
 		if( $_GET['snsrute'] ) {
			foreach($_GET['snsrute'] as $key=>$val){$sc[$val] = 1;}
		}

		$data = $this->membermodel->admin_member_list($sc);

		
		$this->template->assign('send_count',$data['count']);

		$specialArr	= array('＃', '＆', '＊', '＠', '§', '※', '☆', '★', '○', '●', '◎', '◇', '◆', '□', '■', '△', '▲', '▽', '▼', '→', '←', '↑', '↓', '↔', '〓', '◁', '◀', '▷', '▶', '♤', '♠', '♡', '♥', '♧', '♣', '⊙', '◈', '▣', '◐', '◑', '▒', '▤', '▥', '▨', '▧', '▦', '▩', '♨', '☏', '☎', '☜', '☞', '¶', '†', '‡', '↕', '↗', '↙', '↖', '↘', '♭', '♩', '♪', '♬', '㉿', '㈜', '№', '㏇', '™', '㏂', '㏘', '℡', '?', 'ª', 'º');
		$sms_id = $this->config_system['service']['sms_id'];
		$limit	= commonCountSMS();
		$sms_chk = $sms_id;

		$this->template->assign('count',$limit);
		$this->template->assign($this->session->userdata('token'));
		$this->template->assign(array('mInfo'=>$mInfo,'number'=>($send_num)? $send_num : $basic['companyPhone']));
		$this->template->assign(array('sms_loop'=>$sms_data,'sms_total'=>$sms_total,'sms_cont'=>$specialArr,'chk'=>$sms_chk));

		$this->template->assign('query_string',get_query_string());
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");

	}

	public function emoney_form()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();
		
		$auth_promotion = $this->authmodel->manager_limit_act('member_promotion');
		
		if(!$auth_promotion){
			echo "<script>alert('권한이 없습니다.'); self.close();</script>";
			exit;
		}

		// 회원정보다운로드 체크
		if ($this->managerInfo['manager_yn']=='Y') {
			$auth_member_down = true;
		} else {
			$auth_member_down	= $this->authmodel->manager_limit_act('member_download');
		}
		if(isset($auth_member_down)) $this->template->assign('auth_member_down',$auth_member_down);
		
		###
		//$mInfo['total'] = get_rows('fm_member',array('status !='=>'withdrawal'));
		//$this->template->assign('mInfo',$mInfo);

		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function point_form()
	{
		serviceLimit('H_FR','process');

		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();
		
		$auth_promotion = $this->authmodel->manager_limit_act('member_promotion');
		
		if(!$auth_promotion){
			echo "<script>alert('권한이 없습니다.'); self.close();</script>";
			exit;
		}

		// 회원정보다운로드 체크
		if ($this->managerInfo['manager_yn']=='Y') {
			$auth_member_down = true;
		} else {
			$auth_member_down	= $this->authmodel->manager_limit_act('member_download');
		}
		if(isset($auth_member_down)) $this->template->assign('auth_member_down',$auth_member_down);

		
		###
		//$mInfo['total'] = get_rows('fm_member',array('status !='=>'withdrawal'));
		//$this->template->assign('mInfo',$mInfo);

		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function sms_form()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		$auth_send	= $this->authmodel->manager_limit_act('member_send');
		if(!$auth_send){
			echo "<script>alert('권한이 없습니다.'); self.close();</script>";
			exit;
		}

		$this->load->model("smsmodel");
		$sms_result	= $this->smsmodel->smsAuth_chk();
		$smsAuth	= $sms_result['auth'];
		if($sms_result['msg']){
			echo "<script>alert('" . $sms_result['msg'] . "');</script>";
		}

		if($smsAuth){
			$send_phone = getSmsSendInfo();
			if(isset($send_phone)) $this->template->assign('send_phone',$send_phone);

			$table = !empty($_GET['table']) ? $_GET['table'] : 'fm_member';
			$this->template->assign('table',$table);

			// 회원정보다운로드 체크
			if ($this->managerInfo['manager_yn']=='Y') {
				$auth_member_down = true;
			} else {
				$auth_member_down	= $this->authmodel->manager_limit_act('member_download');
			}
			if(isset($auth_member_down)) $this->template->assign('auth_member_down',$auth_member_down);

			###
			if($table=='fm_goods_restock_notify'){
				$mInfo['total'] = get_rows('fm_goods_restock_notify',array('notify_status'=>'none'));
				$action = "../goods_process/restock_notify_send_sms";
				$this->template->assign('action',$action);

				$this->template->assign('send_message',"[{$this->config_basic['shopName']}] 고객님께서 알림요청하신 상품({상품고유값},{상품명})이 재입고되었습니다.");
			}else{
				//$mInfo['total'] = get_rows('fm_member',array('status !='=>'withdrawal'));
				$action = "../member_process/send_sms";
				$this->template->assign('action',$action);
			}
			$specialArr	= array('＃', '＆', '＊', '＠', '§', '※', '☆', '★', '○', '●', '◎', '◇', '◆', '□', '■', '△', '▲', '▽', '▼', '→', '←', '↑', '↓', '↔', '〓', '◁', '◀', '▷', '▶', '♤', '♠', '♡', '♥', '♧', '♣', '⊙', '◈', '▣', '◐', '◑', '▒', '▤', '▥', '▨', '▧', '▦', '▩', '♨', '☏', '☎', '☜', '☞', '¶', '†', '‡', '↕', '↗', '↙', '↖', '↘', '♭', '♩', '♪', '♬', '㉿', '㈜', '№', '㏇', '™', '㏂', '㏘', '℡', '?', 'ª', 'º');
			$basic = ($this->config_basic)?$this->config_basic:config_load('basic');

			$sms_info = config_load('sms_info','send_num');
			if($sms_info['send_num']) $send_num = $sms_info['send_num'];

			###
			$sql = "select count(seq) as total, category from fm_sms_album group by category";
			$query = $this->db->query($sql);
			$sms_data = $query->result_array();
			$sms_total = get_rows('fm_sms_album');
			array_push($sms_data,array('total'=>$sms_total,'category'=>'전체보기'));
			rsort($sms_data);

			$sms_id = $this->config_system['service']['sms_id'];
			$limit	= commonCountSMS();
			$sms_chk = $sms_id;
		}

		$this->template->assign('sms_auth',$smsAuth);
		$this->template->assign('count',$limit);
		$this->template->assign(array('mInfo'=>$mInfo,'number'=>($send_num)? $send_num : $basic['companyPhone']));
		$this->template->assign(array('sms_loop'=>$sms_data,'sms_total'=>$sms_total,'sms_cont'=>$specialArr,'chk'=>$sms_chk));
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function sms_hp_auth(){
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		###
		//$mInfo['total'] = get_rows('fm_member',array('status !='=>'withdrawal'));
		//$this->template->assign('mInfo',$mInfo);

		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}


	public function getSmsCategory(){
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();
		
		###
		$sql = "select count(seq) as total, category from fm_sms_album group by category";
		$query = $this->db->query($sql);
		$sms_data = $query->result_array();
		$sms_total = get_rows('fm_sms_album');
		array_push($sms_data,array('total'=>$sms_total,'category'=>'전체보기'));
		rsort($sms_data);
		$this->template->assign(array('sms_loop'=>$sms_data));

		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");

	}


	public function getSmsSelectCategory(){
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();
		
		###
		$sql = "select count(seq) as total, category from fm_sms_album group by category";
		$query = $this->db->query($sql);
		$sms_data = $query->result_array();
		$sms_total = get_rows('fm_sms_album');
		array_push($sms_data,array('total'=>$sms_total,'category'=>'전체보기'));
		rsort($sms_data);
		$this->template->assign(array('sms_loop'=>$sms_data));

		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");

	}

	
	public function restock_notify_sms(){
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		$auth_send	= $this->authmodel->manager_limit_act('member_send');
		if(!$auth_send){
			echo "<script>alert('권한이 없습니다.'); self.close();</script>";
			exit;
		}

		$send_phone = getSmsSendInfo();
		if(isset($send_phone)) $this->template->assign('send_phone',$send_phone);

		// 회원정보다운로드 체크
		if ($this->managerInfo['manager_yn']=='Y') {
			$auth_member_down = true;
		} else {
			$auth_member_down	= $this->authmodel->manager_limit_act('member_download');
		}
		if(isset($auth_member_down)) $this->template->assign('auth_member_down',$auth_member_down);

		###
		$mInfo['total'] = get_rows('fm_goods_restock_notify',array('notify_status'=>'none'));
		$action = "../goods_process/restock_notify_send_sms";
		$this->template->assign('action',$action);

		$this->template->assign('send_message',"[{$this->config_basic['shopName']}] 고객님께서 알림요청하신 상품({상품고유값},{상품명},{옵션})이 재입고되었습니다.");

		$specialArr	= array('＃', '＆', '＊', '＠', '§', '※', '☆', '★', '○', '●', '◎', '◇', '◆', '□', '■', '△', '▲', '▽', '▼', '→', '←', '↑', '↓', '↔', '〓', '◁', '◀', '▷', '▶', '♤', '♠', '♡', '♥', '♧', '♣', '⊙', '◈', '▣', '◐', '◑', '▒', '▤', '▥', '▨', '▧', '▦', '▩', '♨', '☏', '☎', '☜', '☞', '¶', '†', '‡', '↕', '↗', '↙', '↖', '↘', '♭', '♩', '♪', '♬', '㉿', '㈜', '№', '㏇', '™', '㏂', '㏘', '℡', '?', 'ª', 'º');
		$basic = ($this->config_basic)?$this->config_basic:config_load('basic');

		$sms_info = config_load('sms_info','send_num');
		if($sms_info['send_num']) $send_num = $sms_info['send_num'];

		###
		$sql = "select count(seq) as total, category from fm_sms_album group by category";
		$query = $this->db->query($sql);
		$sms_data = $query->result_array();
		$sms_total = get_rows('fm_sms_album');
		array_push($sms_data,array('total'=>$sms_total,'category'=>'전체보기'));
		rsort($sms_data);

		$sms_id = $this->config_system['service']['sms_id'];
		$limit	= commonCountSMS();
		$sms_chk = $sms_id;

		$this->template->assign('count',$limit);
		$this->template->assign(array('mInfo'=>$mInfo,'number'=>($send_num)? $send_num : $basic['companyPhone']));
		$this->template->assign(array('sms_loop'=>$sms_data,'sms_total'=>$sms_total,'sms_cont'=>$specialArr,'chk'=>$sms_chk));
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}




	function process_test(){
		echo "<table id='processTable' width='".$percent."%'>
			<tr>
				<td height='30' bgcolor='#ff0000'></td>
			</tr>
		</table>
		";

		$totalCount = 100000;

		$sendCount=0;
		ob_start();
		for($i=0; $i<$totalCount; $i++){
			$sendCount++;
			$percent = $sendCount / $totalCount * 100;
			echo "<script>document.all.processTable.style.width='".$percent."%';</script>";
			ob_flush(); 
			ob_clean();
		}
		ob_end_clean(); 
		exit;
	}

	public function restock_notify_count()
	{
		set_time_limit(1);
		$query	= base64_decode($_GET['param']);
		$arr	= explode('from',$query);
		unset($arr[0]);
		$query = "SELECT count(*) as cnt FROM (SELECT K.*, (SELECT E.category_code FROM fm_category_link E WHERE E.goods_seq = K.goods_seq AND E.link = '1' limit 1) as category_code, (SELECT F.category_code FROM fm_brand_link F WHERE F.goods_seq = K.goods_seq AND F.link = '1' limit 1) as brand_code FROM
			(select
				A.restock_notify_seq,
				A.member_seq,
				A.goods_seq,
				A.notify_status,
				A.notify_date,
				A.regist_date,
				AES_DECRYPT(UNHEX(A.cellphone), '{$key}') as cellphone,
				CASE WHEN A.notify_status = 'none' THEN '미통보'
					WHEN A.notify_status = 'complete' THEN '통보'
					END AS goods_status_text,
				B.consumer_price, B.price,
				C.stock,
				C.badstock,
				C.reservation15,
				C.reservation25,
				D.userid,
				D.user_name,
				AES_DECRYPT(UNHEX(D.email), '{$key}') as email,
				E.group_name,
				F.goods_name,
				F.goods_code,
				F.cancel_type,
				F.goods_status,
				F.goods_view,
				F.tax,
				D.rute as mbinfo_rute,
				D.user_name as mbinfo_user_name,
				G.business_seq as mbinfo_business_seq,
				G.bname as mbinfo_bname,
				H.restock_option_seq,
				H.title1,
				H.option1,
				H.title2,
				H.option2,
				H.title3,
				H.option3,
				H.title4,
				H.option4,
				H.title5,
				H.option5 from ".implode('from', $arr);

		$query = preg_replace('/limit [0-9]{1,} , [0-9]{1,}$/Ui', '', $query);

		$query	= $this->db->query($query);
		$data = $query->row_array();
		echo json_encode($data);
	}

}