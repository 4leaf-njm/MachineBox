<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);
class user extends front_base {

    public function __construct() {
        parent::__construct();
        $this->load->library('validation');
        $this->load->model('membermodel');
        $this->load->helper(array('form', 'url', 'mail', 'sms'));
    }
    
    public function write_process() {
        
        $token = $this->input->get('access_token'); // 네이버 로그인 API호출로 받은 접근 토큰값
        $header = "Bearer ".$token; // Bearer 다음에 공백 추가
        $url = "https://openapi.naver.com/blog/writePost.json";
        $title = urlencode("네이버 블로그 api Test php");
        $contents = urlencode("네이버 블로그 api로 글을 블로그에 올려봅니다.");
        $postvars = "title=".$title."&contents=".$contents;
        $is_post = true;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, $is_post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $postvars);
        $headers[] = "Authorization: ".$header;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec ($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        echo "access_token: ".$token;
        echo "status_code:".$status_code;
        curl_close ($ch);
        if($status_code == 200) {
            echo $response;
        } else {
            echo "Error 내용:".$response;
        }
        
        //$this->session->set_flashdata('message', '글이 등록되었습니다.');
        //pageRedirect("/");
    }
    
	public function login()
	{
		$tpl = 'user/login.html';
		$skin = $this->skin;

		$this->template_path = $tpl;
		$this->template->assign(array("template_path"=>$this->template_path));
	
		$this->print_layout($skin.'/'.$tpl);
	}
	
	public function login_process()
	{
	    $this->load->model('ssl');
	    $this->ssl->decode();
	    
	    // return_url 에 http나 https가 있을 경우 외부 도메인으로 보낼 수 없도록 처리 by hed #24462
	    block_out_link_return_url();
	    
	    // 로그인 제한.
	    if($_COOKIE['wronglogin'] >= 5){
	        openDialogAlert(getAlert('mb250'),400,140,'parent','parent.location.reload();');
	        exit;
	    }
	    
	    ### Validation
	    //아이디
	    $this->validation->set_rules('userid', getAlert('mb201'),'trim|required|max_length[60]|xss_clean');
	    //비밀번호
	    $this->validation->set_rules('password', getAlert('mb202'),'trim|required|max_length[32]|xss_clean');
	    
	    if($this->validation->exec()===false){
	        $err = $this->validation->error_array;
	        $callback = "parent.setDefaultText();if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
	        openDialogAlert($err['value'],400,140,'parent',$callback);
	        exit;
	    }
	    
	    ### Query
	    $query = "select password(?) pass";
	    $query = $this->db->query($query,array($_POST['password']));
	    $data = $query->row_array();
	    
	    $member_config = config_load('member');
	    $passwordId = ($member_config['passwordid'])?$member_config['passwordid'] : "";
	    
	    $str_md5 = md5($_POST['password']);
	    $str_sha	=	hash('sha256',$_POST['password']);
	    $str_password = $data['pass'];
	    $str_oldpassword = $data['old_pass'];
	    $str_sha_md5 = hash('sha256',$str_md5);
	    $str_sha_password = hash('sha256',$data['pass']);
	    $str_sha_oldpassword = hash('sha256',$data['old_pass']);
	    $str_sha_newpassword = hash('sha512', md5($_POST['password']).$passwordId.$_POST['userid']);
	    
	    $query = "select A.*,B.business_seq,B.bname,C.group_name, D.label_title, D.label_value from fm_member A LEFT JOIN fm_member_business B ON A.member_seq = B.member_seq left join fm_member_group C on C.group_seq=A.group_seq left join fm_member_subinfo D on A.member_seq=D.member_seq where A.userid=? and (A.password=? or A.password=? or A.password=? or A.password=? or A.password=? or A.password=? or A.password=? or A.password=?)";
	    $query = $this->db->query($query,array($_POST['userid'],$str_md5,$str_sha,$str_password,$str_oldpassword,$str_sha_md5,$str_sha_password,$str_sha_oldpassword, $str_sha_newpassword));
	    $data = $query->result_array();
	    
	    if(!$data[0]['member_seq']){
	        $callback = "parent.setDefaultText();if(parent.document.getElementsByName('userid')[0]) parent.document.getElementsByName('userid')[0].focus();";
	        //일치하는 회원정보가 없습니다.
	        openDialogAlert(getAlert('mb203'),400,140,'parent',$callback);
	        
	        $wronglogin_cnt = ($_COOKIE['wronglogin']) ? $_COOKIE['wronglogin'] : 0;
	        setcookie('wronglogin', $wronglogin_cnt+1, time()+(60*5));	//5분동안 저장
	        exit;
	    }else{
	        setcookie('wronglogin', '', -1);		// 값을 비우고 휘발성으로 전환
	    }
	    
	    if($data[0]['status']=='hold'){
	        $callback = "parent.setDefaultText();if(parent.document.getElementsByName('userid')[0]) parent.document.getElementsByName('userid')[0].focus();";
	        if($data[0]['mtype'] == 'business' ) {
	            //<b>{$data[0]['bname']}</b>은(는) 아직 가입승인되지 않았습니다.
	            openDialogAlert(getAlert('mb204','<b>'.$data[0]['bname'].'</b>'),400,140,'parent',$callback);
	        } else {
	            //<b>{$data[0]['user_name']}</b>님은 아직 가입승인되지 않았습니다.
	            openDialogAlert(getAlert('mb205','<b>'.$data[0]['user_name'].'</b>'),400,140,'parent',$callback);
	        }
	        exit;
	    }
	    
	    ### LOG
	    $qry = "update fm_member set login_cnt = login_cnt+1, lastlogin_date = now(), login_addr = '".$_SERVER['REMOTE_ADDR']."' where member_seq = '{$params['member_seq']}'";
	    $result = $this->db->query($qry);
	    
	    ### SESSION
	    $this->create_member_session($data[0]);
	    
	    ### Main Page
	    $script = "parent.document.location='/main/index';";
	    echo "<script>".$script."</script>";
	}
	
	### 로그아웃
	public function logout() {
	    $unsetuserdata = array('user'=>'', 'mtype'=>'');
	    $this->session->unset_userdata($unsetuserdata);
	    
	    $_SESSION['user']			= '';
	    $_SESSION['mtype']			= '';
	    
	    echo "<script>alert('".getAlert('mb251')."');</script>";
	    pageRedirect('/main/index','','parent');
	}
	
	### 마이페이지 
	public function mypage() {
	    if(!$this->sessionCheck()) {
	        $this->session->set_flashdata('message', '로그인이 필요한 페이지입니다');
	        pageRedirect("/user/login");
	    }
	    $tpl = 'user/mypage.html';
	    $skin = $this->skin;
	    
	    $this->template_path = $tpl;
	    $this->template->assign(array("template_path"=>$this->template_path));
	    
	    $userData = $this->getUserData();
	    
	    $service_list = $this->get_using_service($userData['userid']);
	    $recent_sale_list = $this->get_recent_sale_list();
	    $sales_count = $this->get_sales_count($userData['userid']);
	    $prop_list = $this->get_proposal_list($userData['userid']);
	    $bid_list = $this->get_bid_list($userData['userid']);
	    $emergency_list = $this->get_emergency_list($userData['userid']);
	    
	    $recent_osc_list = $this->get_recent_osc_list();
	    $osc_count = $this->get_osc_count($userData['userid']);
	    
	    $this->template->assign('userInfo', $userData);
	    $this->template->assign('service_list', $service_list);
	    $this->template->assign('recent_sale_list', $recent_sale_list);
	    $this->template->assign('sales_count', $sales_count);
	    $this->template->assign('prop_list', $prop_list);
	    $this->template->assign('bid_list', $bid_list);
	    $this->template->assign('emergency_list', $emergency_list);
	    $this->template->assign('recent_osc_list', $recent_osc_list);
	    $this->template->assign('osc_count', $osc_count);
	    
	    $this->template->assign($this->get_my_new($userData['userid']));
	    $this->template->define('mypage_menu', $this->skin.'/user/mypage_menu.html');
	    
	    $this->print_layout($skin.'/'.$tpl);
	}
	
	public function my_buy_ing() {
	    if(!$this->sessionCheck()) {
	        $this->session->set_flashdata('message', '로그인이 필요한 페이지입니다');
	        pageRedirect("/user/login");
	    }
	    $tpl = 'user/my_buy_ing.html';
	    $skin = $this->skin;
	    
	    $this->template_path = $tpl;
	    $this->template->assign(array("template_path"=>$this->template_path));
	    
	    $userData = $this->getUserData();
	    
	    $prop_list = $this->get_proposal_list($userData['userid']);
	    $bid_list = $this->get_bid_list_02($userData['userid']);
	    $visit_list = $this->get_visit_list($userData['userid']);
	    $imdbuy_list = $this->get_imdbuy_list($userData['userid']);
	    
	    $this->template->assign('prop_list', $prop_list);
	    $this->template->assign('bid_list', $bid_list);
	    $this->template->assign('visit_list', $visit_list);
	    $this->template->assign('imdbuy_list', $imdbuy_list);
	    
	    $data = array(
	        'view_yn' => 'y'
	    );
	    $this->db->where('userid', $userData['userid']);
	    $this->db->update('fm_cm_machine_proposal', $data);
	   
	    $this->db->where('userid', $userData['userid']);
	    $this->db->update('fm_cm_machine_bid', $data);
	    
	    $this->db->where('userid', $userData['userid']);
	    $this->db->update('fm_cm_machine_visit', $data);
	    
	    $this->db->where('userid', $userData['userid']);
	    $this->db->update('fm_cm_machine_imdbuy', $data);
	    
	    $this->template->assign($this->get_my_new($userData['userid']));
	    $this->template->define('mypage_menu', $this->skin.'/user/mypage_menu.html');
	    
	    $this->print_layout($skin.'/'.$tpl);
	}
	
	public function my_buy_ing_more() {
	    if(!$this->sessionCheck()) {
	        $this->session->set_flashdata('message', '로그인이 필요한 페이지입니다');
	        pageRedirect("/user/login");
	    }
	    $tpl = 'user/my_buy_ing_more.html';
	    $skin = $this->skin;
	    
	    $this->template_path = $tpl;
	    $this->template->assign(array("template_path"=>$this->template_path));
	    
	    $userData = $this->getUserData();
	    
	    $prop_list = $this->get_proposal_list($userData['userid']);
	    $bid_list = $this->get_bid_list_02($userData['userid']);
	    
	    $this->template->assign('prop_list', $prop_list);
	    $this->template->assign('bid_list', $bid_list);
	    
	    $query = "select *, a.userid as sale_userid, c.userid as dealer_userid from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_estimate_dealer c ".
	   	    "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and c.state = '작성대기' and c.userid = '".$userData['userid']."' order by c.reg_date desc";
	    $query = $this->db->query($query);
	    $wait_list = $query->result_array();
	    $this->template->assign('wait_list', $wait_list);
	    
	    $query = "select *, a.userid as sale_userid, c.userid as dealer_userid from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_estimate_dealer c ".
	   	    "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and c.state != '작성대기' and c.userid = '".$userData['userid']."' order by c.reg_date desc";
	    $query = $this->db->query($query);
	    $finish_list = $query->result_array();
	    $this->template->assign('finish_list', $finish_list);
	    
	    $data = array(
	        'view_yn' => 'y'
	    );
	    $this->db->where('userid', $userData['userid']);
	    $this->db->update('fm_cm_machine_proposal', $data);
	    
	    $this->db->where('userid', $userData['userid']);
	    $this->db->update('fm_cm_machine_bid', $data);
	    
	    $this->db->where('userid', $userData['userid']);
	    $this->db->update('fm_cm_machine_estimate_dealer', $data);
	    
	    $this->template->assign($this->get_my_new($userData['userid']));
	    $this->template->define('mypage_menu', $this->skin.'/user/mypage_menu.html');
	    
	    $this->print_layout($skin.'/'.$tpl);
	}
	
	public function my_buy_finish() {
	    if(!$this->sessionCheck()) {
	        $this->session->set_flashdata('message', '로그인이 필요한 페이지입니다');
	        pageRedirect("/user/login");
	    }
	    $tpl = 'user/my_buy_finish.html';
	    $skin = $this->skin;
	    
	    $this->template_path = $tpl;
	    $this->template->assign(array("template_path"=>$this->template_path));
	    
	    $userData = $this->getUserData();
	    
	    $buy_list = $this->get_sale_list($userData['userid'], '', '', 'buy');
	    $bid_list_01 = $this->get_sale_list($userData['userid'], '입찰', '', '낙찰', 'buy');
	    $bid_list_02 = $this->get_sale_list($userData['userid'], '입찰', '', '유찰', 'buy');
	    
	    $this->template->assign('buy_list', $buy_list);
	    $this->template->assign('bid_list_01', $bid_list_01);
	    $this->template->assign('bid_list_02', $bid_list_02);
	    
	    $data = array(
	        'view_res_yn' => 'y'
	    );
	    $this->db->where('userid', $userData['userid'])
	             ->where_in('bid_yn', array('x', 'y'));
	    $this->db->update('fm_cm_machine_bid', $data);
	    
	    $this->template->assign($this->get_my_new($userData['userid']));
	    $this->template->define('mypage_menu', $this->skin.'/user/mypage_menu.html');
	    
	    $this->print_layout($skin.'/'.$tpl);
	}
	
	public function my_sale_eval($info_seq) {
	    if(!$this->sessionCheck()) {
	        $this->session->set_flashdata('message', '로그인이 필요한 페이지입니다');
	        pageRedirect("/user/login");
	    }
	    $tpl = 'user/my_sale_eval.html';
	    $skin = $this->skin;
	    
	    $this->template_path = $tpl;
	    $this->template->assign(array("skin_path"=>$this->skin,
	        "template_path"=>$this->template_path));
	    
	    $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_model c where a.sales_seq = b.sales_seq and b.model_seq = c.model_seq and info_seq = ".$info_seq;
	    $query = $this->db->query($query);
	    $info = $query->row_array();
	    
	    $this->template->assign('info', $info);
	    
	    $this->template->assign($this->get_my_new($userData['userid']));
	    $this->template->define('mypage_menu', $this->skin.'/user/mypage_menu.html');
	    
	    $this->print_layout($skin.'/'.$tpl);
	}
	
	public function my_sale_eval_process() {
	    $info_seq = $this->input->post('info_seq');
	    $grade_01 = $this->input->post('grade_01');
	    $grade_02 = $this->input->post('grade_02');
	    $grade_03 = $this->input->post('grade_03');
	    $grade_04 = $this->input->post('grade_04');
	    $grade_05 = $this->input->post('grade_05');
	    $grade = $this->input->post('grade');
	    $content = $this->input->post('content');
	    
	    $userData = $this->getUserData();
	    
	    $query = "select * from fm_cm_machine_sales_eval where info_seq = ".$info_seq." and userid = '".$userData['userid']."'";
	    $query = $this->db->query($query);
	    $result = $query->row_array();
	    if(!empty($result)) {
	        $this->session->set_flashdata('message', '이미 평가를 작성하셨습니다.');
	        pageRedirect('/user/my_buy_finish');
	        exit;
	    }
	    $data = array(
	        'userid' => $userData['userid'],
	        'info_seq' => $info_seq,
	        'grade_01' => $grade_01,
	        'grade_02' => $grade_02,
	        'grade_03' => $grade_03,
	        'grade_04' => $grade_04,
	        'grade_05' => $grade_05,
	        'grade' => $grade,
	        'content' => $content
	    );
	    $this->db->insert('fm_cm_machine_sales_eval', $data);
	    
	    $this->session->set_flashdata('message', '평가 작성이 완료되었습니다.');
	    pageRedirect('/user/my_buy_finish');
	}
	
	public function my_buy_recent() {
	    if(!$this->sessionCheck()) {
	        $this->session->set_flashdata('message', '로그인이 필요한 페이지입니다');
	        pageRedirect("/user/login");
	    }
	    $tpl = 'user/my_buy_recent.html';
	    $skin = $this->skin;
	    
	    $this->template_path = $tpl;
	    $this->template->assign(array("template_path"=>$this->template_path));
	    
	    $userData = $this->getUserData();
	    
	    $recent_sale_list = $this->get_recent_sale_list();
	    $idx = 0;
	    foreach($recent_sale_list as $row) {
	        $query = "select * from fm_cm_machine_sales_detail a where info_seq = ".$row['info_seq'];
	        $query = $this->db->query($query);
	        $result = $query->row_array();
	        if(!empty($result)) {
	            $recent_sale_list[$idx] = array_merge($row, $result);
	        }
	        $idx ++;
	    }
	    foreach($recent_sale_list as &$row) {
	        $query = "select * from fm_cm_machine_like a where info_seq = ".$row['info_seq'];
	        $query = $this->db->query($query);
	        $result = $query->row_array();
	        if(empty($result))
	            $row['like_cnt'] = 0;
            else
                $row['like_cnt'] = $result['like_cnt'];
	    }
	    
	    $this->template->assign('recent_sale_list', $recent_sale_list);
	    
	    $this->template->assign($this->get_my_new($userData['userid']));
	    $this->template->define('mypage_menu', $this->skin.'/user/mypage_menu.html');
	    
	    $this->print_layout($skin.'/'.$tpl);
	}
	
	public function my_sale_wait() {
	    if(!$this->sessionCheck()) {
	        $this->session->set_flashdata('message', '로그인이 필요한 페이지입니다');
	        pageRedirect("/user/login");
	    }
	    $tpl = 'user/my_sale_wait.html';
	    $skin = $this->skin;
	    
	    $this->template_path = $tpl;
	    $this->template->assign(array("template_path"=>$this->template_path));
	    
	    $userData = $this->getUserData();
	    
	    $direct_list = $this->get_sale_list($userData['userid'], '다이렉트', 'wait');
        $self_wait_list = $this->get_sale_list($userData['userid'], '셀프판매대기', 'wait');
	    $self_list = $this->get_sale_list($userData['userid'], '셀프판매', 'wait');
	    $bid_list = $this->get_sale_list($userData['userid'], '입찰', 'wait', '', 'sale');
	    
        foreach($self_list as &$row) {
            $query = "select * from fm_cm_machine_pay where pay_type in('프리미엄광고', '성능검사', '기계평가') and pay_state = '입금대기' and pay_userid = '".$userData['userid']."' and target_seq = ".$row['info_seq'];
            $query = $this->db->query($query);
            $result = $query->result_array();
            $pay_price = 0;
            foreach($result as $row2) {
                $pay_price += $row2['pay_price'];
            }
            $row['pay_price'] = $pay_price;
        }
        
	    $this->template->assign('direct_list', $direct_list);
	    $this->template->assign('self_wait_list', $self_wait_list);
	    $this->template->assign('self_list', $self_list);
	    $this->template->assign('bid_list', $bid_list);
	    
	    $this->template->assign($this->get_my_new($userData['userid']));
	    $this->template->define('mypage_menu', $this->skin.'/user/mypage_menu.html');
	    
	    $this->print_layout($skin.'/'.$tpl);
	}
	
	public function my_sale_reply() {
	    if(!$this->sessionCheck()) {
	        $this->session->set_flashdata('message', '로그인이 필요한 페이지입니다');
	        pageRedirect("/user/login");
	    }
	    $tpl = 'user/my_sale_reply.html';
	    $skin = $this->skin;
	    
	    $this->template_path = $tpl;
	    $this->template->assign(array("template_path"=>$this->template_path));
	    
	    $userData = $this->getUserData();
	    
	    $prop_map = $this->get_proposal_list_for_sale($userData['userid']);
	    
	    $this->template->assign('prop_list', $prop_map['prop_list']);
	    $this->template->assign('prop_cnt', $prop_map['prop_cnt']);
	    
	    $this->template->assign($this->get_my_new($userData['userid']));
	    $this->template->define('mypage_menu', $this->skin.'/user/mypage_menu.html');
	    
	    $this->print_layout($skin.'/'.$tpl);
	}
	
	public function my_sale_ing() {
	    if(!$this->sessionCheck()) {
	        $this->session->set_flashdata('message', '로그인이 필요한 페이지입니다');
	        pageRedirect("/user/login");
	    }
	    $tpl = 'user/my_sale_ing.html';
	    $skin = $this->skin;
	    
	    $this->template_path = $tpl;
	    $this->template->assign(array("template_path"=>$this->template_path));
	    
	    $userData = $this->getUserData();
	    
	    $fixed_list = $this->get_sale_list($userData['userid'], '고정가격', 'ing');
	    $bid_list = $this->get_sale_list($userData['userid'], '입찰', 'ing', '', 'sale');
	    $direct_list = $this->get_sale_list($userData['userid'], '다이렉트', 'ing');
		$visit_list = $this->get_visit_list_for_sale($userData['userid']);
	    $imdbuy_list = $this->get_imdbuy_list_for_sale($userData['userid']);
	    
	    $this->template->assign('fixed_list', $fixed_list);
	    $this->template->assign('bid_list', $bid_list);
	    $this->template->assign('direct_list', $direct_list);
	    $this->template->assign('visit_list', $visit_list);
	    $this->template->assign('imdbuy_list', $imdbuy_list);
	    
	    $query = "update fm_cm_machine_sales a, fm_cm_machine_sales_info b set view_yn = 'y' where a.sales_seq = b.sales_seq and type != 'emergency' and a.userid = '".$userData['userid']."'";
	    $this->db->query($query);
	    
	    $this->template->assign($this->get_my_new($userData['userid']));
	    $this->template->define('mypage_menu', $this->skin.'/user/mypage_menu.html');
	    
	    $this->print_layout($skin.'/'.$tpl);
	}
	
	public function my_sale_finish() {
	    if(!$this->sessionCheck()) {
	        $this->session->set_flashdata('message', '로그인이 필요한 페이지입니다');
	        pageRedirect("/user/login");
	    }
	    $tpl = 'user/my_sale_finish.html';
	    $skin = $this->skin;
	    
	    $this->template_path = $tpl;
	    $this->template->assign(array("template_path"=>$this->template_path));
	    
	    $userData = $this->getUserData();
	    
	    $fixed_list = $this->get_sale_list($userData['userid'], '고정가격', 'finish');
	    $bid_list = $this->get_sale_list($userData['userid'], '입찰', 'finish', '', 'sale');
	    $direct_list = $this->get_sale_list($userData['userid'], '다이렉트', 'finish');
	    
	    $this->template->assign('fixed_list', $fixed_list);
	    $this->template->assign('bid_list', $bid_list);
	    $this->template->assign('direct_list', $direct_list);
	    
	    $this->template->assign($this->get_my_new($userData['userid']));
	    $this->template->define('mypage_menu', $this->skin.'/user/mypage_menu.html');
	    
	    $this->print_layout($skin.'/'.$tpl);
	}
	
	public function my_sale_emergency() {
	    if(!$this->sessionCheck()) {
	        $this->session->set_flashdata('message', '로그인이 필요한 페이지입니다');
	        pageRedirect("/user/login");
	    }
	    $tpl = 'user/my_sale_emergency.html';
	    $skin = $this->skin;
	    
	    $this->template_path = $tpl;
	    $this->template->assign(array("template_path"=>$this->template_path));
	    
	    $userData = $this->getUserData();
	    
	    $wait_list = $this->get_sale_list($userData['userid'], '긴급판매', 'wait');
	    $ing_list = $this->get_sale_list($userData['userid'], '긴급판매', 'ing');
	    $finish_list = $this->get_sale_list($userData['userid'], '긴급판매', 'finish');
	    foreach($wait_list as &$row) {
	        $query = "select * from fm_cm_machine_estimate_dealer where info_seq = ".$row['info_seq']." and state = '작성완료'";
	        $query = $this->db->query($query);
	        $result = $query->result_array();
	        $row['dealer_list'] = $result;
	    }
	    $this->template->assign('wait_list', $wait_list);
	    $this->template->assign('ing_list', $ing_list);
	    $this->template->assign('finish_list', $finish_list);
	    
	    $query = "update fm_cm_machine_sales a, fm_cm_machine_sales_info b set view_yn = 'y' where a.sales_seq = b.sales_seq and type = 'emergency' and a.userid = '".$userData['userid']."'";
	    $this->db->query($query);
	    
	    $this->template->assign($this->get_my_new($userData['userid']));
	    $this->template->define('mypage_menu', $this->skin.'/user/mypage_menu.html');
	    
	    $this->print_layout($skin.'/'.$tpl);
	}
	
	public function my_osc_wait() {
	    if(!$this->sessionCheck()) {
	        $this->session->set_flashdata('message', '로그인이 필요한 페이지입니다');
	        pageRedirect("/user/login");
	    }
	    $tpl = 'user/my_osc_wait.html';
	    $skin = $this->skin;
	    
	    $this->template_path = $tpl;
	    $this->template->assign(array("template_path"=>$this->template_path));
	    
	    $userData = $this->getUserData();

	    $osc_list = $this->get_osc_list($userData['userid'], 'wait');
	    
	    $this->template->assign('osc_list', $osc_list['osc_list']);
	    
	    $data = array(
	        'view_yn' => 'y'
	    );
	    $this->db->where('userid', $userData['userid'])
	             ->where('permit_yn', 'n');
	    $this->db->update('fm_cm_machine_outsourcing', $data);
	    
	    $this->template->assign($this->get_my_new($userData['userid']));
	    $this->template->define('mypage_menu', $this->skin.'/user/mypage_menu.html');
	    
	    $this->print_layout($skin.'/'.$tpl);
	}
	
	public function my_osc_ing() {
	    if(!$this->sessionCheck()) {
	        $this->session->set_flashdata('message', '로그인이 필요한 페이지입니다');
	        pageRedirect("/user/login");
	    }
	    $tpl = 'user/my_osc_ing.html';
	    $skin = $this->skin;
	    
	    $this->template_path = $tpl;
	    $this->template->assign(array("template_path"=>$this->template_path));
	    
	    $userData = $this->getUserData();
	    
	    $osc_list = $this->get_osc_list($userData['userid'], 'ing');
	    
	    $this->template->assign('osc_list', $osc_list['osc_list']);
	    
	    $data = array(
	        'view_yn' => 'y'
	    );
	    $this->db->where('userid', $userData['userid'])
	             ->where('permit_yn', 'y')
	             ->where('state', '1')
                 ->where('finish_yn', 'n');
	    $this->db->update('fm_cm_machine_outsourcing', $data);
	    
	    $this->template->assign($this->get_my_new($userData['userid']));
	    $this->template->define('mypage_menu', $this->skin.'/user/mypage_menu.html');
	    
	    $this->print_layout($skin.'/'.$tpl);
	}
	
	public function my_osc_ing_more() {
	    if(!$this->sessionCheck()) {
	        $this->session->set_flashdata('message', '로그인이 필요한 페이지입니다');
	        pageRedirect("/user/login");
	    }
	    $tpl = 'user/my_osc_ing_more.html';
	    $skin = $this->skin;
	    
	    $this->template_path = $tpl;
	    $this->template->assign(array("template_path"=>$this->template_path));
	    
	    $userData = $this->getUserData();
	    
	    $osc_list_01 = $this->get_osc_list($userData['userid'], 'ing');
	    $osc_list_02 = $this->get_osc_list($userData['userid'], 'end');
	    
	    $this->template->assign('osc_list_01', $osc_list_01['osc_list']);
	    $this->template->assign('osc_list_02', $osc_list_02['osc_list']);
	    
	    $data = array(
	        'view_yn' => 'y'
	    );
	    $this->db->where('userid', $userData['userid'])
	    ->where('permit_yn', 'y')
	    ->where('state', '1');
	    $this->db->update('fm_cm_machine_outsourcing', $data);
	    
	    $this->db->where('userid', $userData['userid'])
	    ->where('permit_yn', 'y')
	    ->where_in('state', array('1', '2'))
	    ->where('finish_yn', 'n');
	    $this->db->update('fm_cm_machine_outsourcing', $data);
	    
	    $this->template->assign($this->get_my_new($userData['userid']));
	    $this->template->define('mypage_menu', $this->skin.'/user/mypage_menu.html');
	    
	    $this->print_layout($skin.'/'.$tpl);
	}
	
	public function my_osc_end() {
	    if(!$this->sessionCheck()) {
	        $this->session->set_flashdata('message', '로그인이 필요한 페이지입니다');
	        pageRedirect("/user/login");
	    }
	    $tpl = 'user/my_osc_end.html';
	    $skin = $this->skin;
	    
	    $this->template_path = $tpl;
	    $this->template->assign(array("template_path"=>$this->template_path));
	    
	    $userData = $this->getUserData();
	    
	    $osc_list = $this->get_osc_list($userData['userid'], 'end');
	    
	    $this->template->assign('osc_list', $osc_list['osc_list']);
	    
	    $data = array(
	        'view_yn' => 'y'
	    );
	    $this->db->where('userid', $userData['userid'])
	    ->where('permit_yn', 'y')
	    ->where('state', '2')
	    ->where('finish_yn', 'n');
	    $this->db->update('fm_cm_machine_outsourcing', $data);
	    
	    $this->template->assign($this->get_my_new($userData['userid']));
	    $this->template->define('mypage_menu', $this->skin.'/user/mypage_menu.html');
	    
	    $this->print_layout($skin.'/'.$tpl);
	}
	
	public function my_osc_finish() {
	    if(!$this->sessionCheck()) {
	        $this->session->set_flashdata('message', '로그인이 필요한 페이지입니다');
	        pageRedirect("/user/login");
	    }
	    $tpl = 'user/my_osc_finish.html';
	    $skin = $this->skin;
	    
	    $this->template_path = $tpl;
	    $this->template->assign(array("template_path"=>$this->template_path));
	    
	    $userData = $this->getUserData();
	    
	    $osc_list = $this->get_osc_list($userData['userid'], 'finish');
	    
	    $this->template->assign('osc_list', $osc_list['osc_list']);
	    $this->template->assign('partner_info', $osc_list['partner_info']);
	    
	    $data = array(
	        'view_yn' => 'y'
	    );
	    $this->db->where('userid', $userData['userid'])
	             ->where('finish_yn', 'y');
	    $this->db->update('fm_cm_machine_outsourcing', $data);
	    
	    $this->template->assign($this->get_my_new($userData['userid']));
	    $this->template->define('mypage_menu', $this->skin.'/user/mypage_menu.html');
	    
	    $this->print_layout($skin.'/'.$tpl);
	}
	
	public function osc_meeting_process() {
	    $po_seq = $this->input->post('po_seq');
	    $userid = $this->input->post('userid');
	    $meet_state = $this->input->post('meet_state');
	    
	    if($meet_state == 1) {
	        $query = "select * from fm_cm_machine_partner_osc where po_seq = ".$po_seq;
	        $query = $this->db->query($query);
	        $result = $query->row_array();
	        if($result['meet_state'] != 0) {
	            $callback = "parent.location.reload()";
	            openDialogAlert('이미 미팅신청을 하셨습니다.',400,140,'parent',$callback);
	            exit;
	        }
	        $data = array(
	            'meet_state' => $meet_state,
	            'meet_date' => date('Y-m-d H:i:s'),
	            'admin_meet_view_yn' => 'n'
	        );
	    } else if($meet_state == 2) {
	        $data = array(
	            'meet_state' => $meet_state,
	            'meet_permit_date' => date('Y-m-d H:i:s'),
                'state' => 1,
	            'admin_meet_view_yn' => 'n'
	        );
	    } else if($meet_state == 3) {
	        $data = array(
	            'meet_state' => $meet_state,
	            'admin_meet_view_yn' => 'n'
	        );
	    }
	    $this->db->where('po_seq', $po_seq);
	    $this->db->update('fm_cm_machine_partner_osc', $data);
        
	    $userData = $this->getUserDataById($userid);
	    
	    if($meet_state == 1) {
    	    $title = "지원 외주 <b>미팅 신청</b>";
    	    $mail_message = "지원하신 외주에서 미팅신청이 들어왔습니다.";
    	    $sms_message = "지원하신 외주에서 미팅신청이 들어왔습니다.\n자세한 사항은 마이페이지를 참고해주세요.";
    	    $callback_message = "미팅신청이 완료되었습니다.";
	    } else if ($meet_state == 2) {
	        $title = "미팅신청 <b>승인</b>";
	        $mail_message = "파트너가 미팅 신청을 받았습니다.";
	        $sms_message = "파트너가 미팅 신청을 받았습니다. \n자세한 사항은 마이페이지를 참고해주세요.";
	        $callback_message = "승인 되었습니다.";
	    } else if ($meet_state == 3) {
	        $title = "지원 외주 수주사로 <b>선택</b>";
	        $mail_message = "지원하신 외주에서 수주사로 최종 선택되었습니다.";
	        $sms_message = "지원하신 외주에서 수주사로 최종 선택되었습니다. \n자세한 사항은 마이페이지를 참고해주세요.";
	        $callback_message = "최종선택이 완료되었습니다.";
	    }
	    $this->send_common_mail($userData['email'], $title, $mail_message);
	    $this->send_common_sms($userData['cellphone'], $sms_message);
	    
	    $callback = "parent.location.reload()";
	    openDialogAlert($callback_message,400,140,'parent',$callback);
	}
	
	public function my_ptn_recent() {
	    if(!$this->sessionCheck()) {
	        $this->session->set_flashdata('message', '로그인이 필요한 페이지입니다');
	        pageRedirect("/user/login");
	    }
	    $tpl = 'user/my_ptn_recent.html';
	    $skin = $this->skin;
	    
	    $this->template_path = $tpl;
	    $this->template->assign(array("template_path"=>$this->template_path));
	    
	    $recent_list = $this->get_recent_osc_list();
	    
	    $this->template->assign('recent_list', $recent_list);
	    
	    $this->template->assign($this->get_my_new($userData['userid']));
	    $this->template->define('mypage_menu', $this->skin.'/user/mypage_menu.html');
	    
	    $this->print_layout($skin.'/'.$tpl);
	}
	
	public function my_ptn_wait() {
	    if(!$this->sessionCheck()) {
	        $this->session->set_flashdata('message', '로그인이 필요한 페이지입니다');
	        pageRedirect("/user/login");
	    }
	    $tpl = 'user/my_ptn_wait.html';
	    $skin = $this->skin;
	    
	    $this->template_path = $tpl;
	    $this->template->assign(array("template_path"=>$this->template_path));
	    
	    $userData = $this->getUserData();
	    
	    $ptn_list = $this->get_ptn_list($userData['userid'], 'wait');
	    
	    $this->template->assign('ptn_list', $ptn_list);
	    
	    $query = "update fm_cm_machine_partner_osc a, fm_cm_machine_partner b set a.view_yn = 'y' where a.partner_seq = b.partner_seq and a.state = '0' and b.userid = '".$userData['userid']."'";
	    $query = $this->db->query($query);
	    
	    $this->template->assign($this->get_my_new($userData['userid']));
	    $this->template->define('mypage_menu', $this->skin.'/user/mypage_menu.html');
	    
	    $this->print_layout($skin.'/'.$tpl);
	}
	
	public function my_ptn_ing() {
	    if(!$this->sessionCheck()) {
	        $this->session->set_flashdata('message', '로그인이 필요한 페이지입니다');
	        pageRedirect("/user/login");
	    }
	    $tpl = 'user/my_ptn_ing.html';
	    $skin = $this->skin;
	    
	    $this->template_path = $tpl;
	    $this->template->assign(array("template_path"=>$this->template_path));
	    
	    $userData = $this->getUserData();
	    
	    $ptn_list = $this->get_ptn_list($userData['userid'], 'ing');
	    
	    $this->template->assign('ptn_list', $ptn_list);
	    
	    $query = "update fm_cm_machine_partner_osc a, fm_cm_machine_partner b set a.view_yn = 'y' where a.partner_seq = b.partner_seq and a.state in('1', '2') and b.userid = '".$userData['userid']."'";
	    $query = $this->db->query($query);
	    
	    $this->template->assign($this->get_my_new($userData['userid']));
	    $this->template->define('mypage_menu', $this->skin.'/user/mypage_menu.html');
	    
	    $this->print_layout($skin.'/'.$tpl);
	}
	
	public function my_ptn_ing_more() {
	    if(!$this->sessionCheck()) {
	        $this->session->set_flashdata('message', '로그인이 필요한 페이지입니다');
	        pageRedirect("/user/login");
	    }
	    $tpl = 'user/my_ptn_ing_more.html';
	    $skin = $this->skin;
	    
	    $this->template_path = $tpl;
	    $this->template->assign(array("template_path"=>$this->template_path));
	    
	    $userData = $this->getUserData();
	    
	    $ptn_list_01 = $this->get_ptn_list($userData['userid'], 'wait');
	    $ptn_list_02 = $this->get_ptn_list($userData['userid'], 'ing');
	    
	    $query = "update fm_cm_machine_partner_osc a, fm_cm_machine_partner b set a.view_yn = 'y' where a.partner_seq = b.partner_seq and a.state = '0' and b.userid = '".$userData['userid']."'";
	    $query = $this->db->query($query);
	    
	    $query = "update fm_cm_machine_partner_osc a, fm_cm_machine_partner b set a.view_yn = 'y' where a.partner_seq = b.partner_seq and a.state in('1', '2') and b.userid = '".$userData['userid']."'";
	    $query = $this->db->query($query);
	    
	    $this->template->assign('ptn_list_01', $ptn_list_01);
	    $this->template->assign('ptn_list_02', $ptn_list_02);
	    
	    $this->template->assign($this->get_my_new($userData['userid']));
	    $this->template->define('mypage_menu', $this->skin.'/user/mypage_menu.html');
	    
	    $this->print_layout($skin.'/'.$tpl);
	}
	
	public function my_ptn_finish() {
	    if(!$this->sessionCheck()) {
	        $this->session->set_flashdata('message', '로그인이 필요한 페이지입니다');
	        pageRedirect("/user/login");
	    }
	    $tpl = 'user/my_ptn_finish.html';
	    $skin = $this->skin;
	    
	    $this->template_path = $tpl;
	    $this->template->assign(array("template_path"=>$this->template_path));
	    
	    $userData = $this->getUserData();
	    
	    $ptn_list = $this->get_ptn_list($userData['userid'], 'finish');
	    
	    $this->template->assign('ptn_list', $ptn_list);
	    
	    $query = "update fm_cm_machine_partner_osc a, fm_cm_machine_partner b set a.view_yn = 'y' where a.partner_seq = b.partner_seq and a.state = '3' and b.userid = '".$userData['userid']."'";
	    $query = $this->db->query($query);
	    
	    $this->template->assign($this->get_my_new($userData['userid']));
	    $this->template->define('mypage_menu', $this->skin.'/user/mypage_menu.html');
	    
	    $this->print_layout($skin.'/'.$tpl);
	}
	
	public function my_info_modify($change) {
	    if(!$this->sessionCheck()) {
	        $this->session->set_flashdata('message', '로그인이 필요한 페이지입니다');
	        pageRedirect("/user/login");
	    }
	    $tpl = 'user/my_info_modify.html';
	    $skin = $this->skin;
	    
	    $this->template_path = $tpl;
	    $this->template->assign(array("template_path"=>$this->template_path));
	    
	    ###
	    $email = code_load('email');
	    $joinform = ($this->joinform)?$this->joinform:config_load('joinform');
	    $memberapproval = config_load('member');
	    
	    for ($m=1;$m<=12;$m++){	$m_arr[] = str_pad($m, 2, '0', STR_PAD_LEFT); }
	    for ($d=1;$d<=31;$d++){	$d_arr[] = str_pad($d, 2, '0', STR_PAD_LEFT); }
	    $this->template->assign('m_arr',$m_arr);
	    $this->template->assign('d_arr',$d_arr);
	    
        $this->mdata['mtype'] = $mtype;
        if($memberapproval) $this->template->assign('memberapproval',$memberapproval);
        if($email) $this->template->assign('email_arr',$email);
        if($this->mdata['birthday'] == '0000-00-00') $this->mdata['birthday'] ='';
        if($this->mdata) $this->template->assign($this->mdata);
	        
        $member = config_load('member');
        $member['agreement'] = str_replace("{shopName}",$arrBasic['shopName'],$member['agreement']);
        $member['privacy'] = str_replace("{shopName}",$arrBasic['shopName'],$member['privacy']);
        $member['privacy'] = str_replace("{domain}",$arrBasic['domain'],$member['privacy']);
        
        //개인정보 관련 문구개선 @2016-09-06 ysm
        $member['privacy'] = str_replace("{책임자명}",$arrBasic['member_info_manager'],$member['privacy']);
        $member['privacy'] = str_replace("{책임자담당부서}",$arrBasic['member_info_part'],$member['privacy']);
        $member['privacy'] = str_replace("{책임자직급}",$arrBasic['member_info_rank'],$member['privacy']);
        $member['privacy'] = str_replace("{책임자연락처}",$arrBasic['member_info_tel'],$member['privacy']);
        $member['privacy'] = str_replace("{책임자이메일}",$arrBasic['member_info_email'],$member['privacy']);
        
        //개인정보 수집-이용
        $member['policy'] = str_replace("{domain}",$arrBasic['domain'],str_replace("{shopName}",$arrBasic['shopName'],$member['policy']));
        $this->template->assign($member);
	        
        if($joinform) $this->template->assign('joinform',$joinform);
        
        $userData = $this->getUserData();
        $mtype = 'member';
        if($change == 'change') {
            $mtype = 'business';
            $userData['bphone'] = $userData['phone'];
            $userData['bcellphone'] = $userData['cellphone'];
        } else if($userData['business_seq']){
            $mtype = 'business';
        }
        $userData['mtype'] = $mtype;
        
        //가입 추가 정보 리스트
        $msubdata = '';
        $qry = "select * from fm_joinform where used='Y' order by sort_seq";
        $query = $this->db->query($qry);
        $form_arr = $query -> result_array();
        foreach ($form_arr as $k => $data){
            $data['label_view'] = $this -> membermodel-> get_labelitem_type($data,$msubdata);
            $sub_form[] = $data;
        }
        $query = "select label_value as gubun from fm_member_subinfo where label_title = '회원구분' and member_seq = ".$userData['member_seq'];
        $query = $this->db->query($query);
        $userData['gubun'] = $query->row()->gubun;
        
        $this->template->assign($this->get_my_new($userData['userid']));
        
        $this->template->assign($userData);
        $this->template->assign('change', $change);
        $this->template->assign('form_sub',$sub_form);
        $this->template->define(array('form_member'=>$this->skin.'/member/register_form.html'));
        $this->template->define('mypage_menu', $this->skin.'/user/mypage_menu.html');
        
        $this->print_layout($skin.'/'.$tpl);
	}
	
	public function my_pwd_modify($step="step_01") {
	    if(!$this->sessionCheck()) {
	        $this->session->set_flashdata('message', '로그인이 필요한 페이지입니다');
	        pageRedirect("/user/login");
	    }
	    $tpl = 'user/my_pwd_modify.html';
	    $skin = $this->skin;
	    
	    $this->template_path = $tpl;
	    $this->template->assign(array("template_path"=>$this->template_path));
	    
	    $userData = $this->getUserData();
	    
	    $this->template->assign('step', $step);
	    
	    $this->template->assign($this->get_my_new($userData['userid']));
	    $this->template->define('mypage_menu', $this->skin.'/user/mypage_menu.html');
	    
	    $this->print_layout($skin.'/'.$tpl);
	}
	
	public function my_pwd_modify_process() {
	    $step = $this->input->post('step');
	    $old_password = $this->input->post('old_password');
	    $new_password = $this->input->post('new_password');
	}
	
	public function my_qna_write() {
	    if(!$this->sessionCheck()) {
	        $this->session->set_flashdata('message', '로그인이 필요한 페이지입니다');
	        pageRedirect("/user/login");
	    }
	    $tpl = 'user/my_qna_write.html';
	    $skin = $this->skin;
	    
	    $this->template_path = $tpl;
	    $this->template->assign(array("template_path"=>$this->template_path));
	    
	    $userData = $this->getUserData();
	    
	    $this->template->define('mypage_menu', $this->skin.'/user/mypage_menu.html');
	    
	    $this->print_layout($skin.'/'.$tpl);
	}
	
	public function my_qna_write_process() {
	    $title = $this->input->post('title');
	    $content = $this->input->post('content');
	    
	    if(empty($title)) {
	        openDialogAlert("문의 제목을 작성해주세요.",400,140,'parent',$callback);
	        exit;
	    }
	    if(empty($content)) {
	        openDialogAlert("문의 내용을 작성해주세요.",400,140,'parent',$callback);
	        exit;
	    }
	    
	    $userData = $this->getUserData();
	    $data = array(
	        'userid' => $userData['userid'],
	        'title' => $title,
	        'content' => $content
	    );
	    $this->db->insert('fm_cm_machine_my_qna', $data);

	    $callback = "parent.location.href = '/user/my_qna_list'";
	    openDialogAlert("작성하신 내용이 관리자에게 전달 되었습니다.",400,140,'parent',$callback);
	}
	
	public function my_qna_list() {
	    if(!$this->sessionCheck()) {
	        $this->session->set_flashdata('message', '로그인이 필요한 페이지입니다');
	        pageRedirect("/user/login");
	    }
	    $tpl = 'user/my_qna_list.html';
	    $skin = $this->skin;
	    
	    $this->template_path = $tpl;
	    $this->template->assign(array("template_path"=>$this->template_path));
	    
	    $userData = $this->getUserData();
	    $query = "select * from fm_cm_machine_my_qna where userid = '".$userData['userid']."' order by reg_date desc";
	    $query = $this->db->query($query);
	    $qna_list = $query->result_array();
	    
	    $query = "select count(*) as cnt from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_question c ".
	   	    "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and c.userid = '".$userData['userid']."' order by c.reg_date desc";
	    $query = $this->db->query($query);
	    $sale_qna_cnt = $query->row_array()['cnt'];
	    
	    $query = "select *, a.userid as sale_userid, c.userid as qna_userid from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_question c ".
	   	    "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and c.userid = '".$userData['userid']."' group by c.info_seq order by c.reg_date desc";
	    $query = $this->db->query($query);
	    $sale_qna_list = $query->result_array();
	    
	    foreach($sale_qna_list as &$row) {
	        $query = "select *, a.userid as sale_userid, c.userid as qna_userid from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_question c ".
	   	        "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and c.info_seq = ".$row['info_seq']." and c.userid = '".$userData['userid']."' order by c.reg_date desc";
	        $query = $this->db->query($query);
	        $list = $query->result_array();
	        $row['list'] = $list;
	    }
	    
	    foreach($sale_qna_list as $row2) {
	        foreach($row2['list'] as $row3) {
    	        $data = array(
    	            'view_yn' => 'y'
    	        );
    	        if($row3['res_yn'] == 'y')
    	            $data['res_view_yn'] = 'y';
    	        $this->db->where('qna_seq', $row3['qna_seq']);
    	        $this->db->update('fm_cm_machine_question', $data);
	        }
	    }
	    foreach($qna_list as $row) {
	        $data = array(
	            'view_yn' => 'y'
	        );
	        if(!empty($row['reply']))
	            $data['res_view_yn'] = 'y';
            $this->db->where('qna_seq', $row['qna_seq']);
            $this->db->update('fm_cm_machine_my_qna', $data);
	    }
	    
	    $qna_map = $this->get_qna_list_for_sale($userData['userid']);
	    $this->template->assign('qna_rec_list', $qna_map['qna_list']);
	    $this->template->assign('qna_rec_cnt', $qna_map['qna_cnt']);
	    
	    $this->template->assign('qna_list', $qna_list);
	    $this->template->assign('sale_qna_list', $sale_qna_list);
	    $this->template->assign('sale_qna_cnt', $sale_qna_cnt);
	    
	    $this->template->assign($this->get_my_new($userData['userid']));
	    $this->template->define('mypage_menu', $this->skin.'/user/mypage_menu.html');
	    
	    $this->print_layout($skin.'/'.$tpl);
	}
	
	public function my_pay_wait() {
	    if(!$this->sessionCheck()) {
	        $this->session->set_flashdata('message', '로그인이 필요한 페이지입니다');
	        pageRedirect("/user/login");
	    }
	    $tpl = 'user/my_pay_wait.html';
	    $skin = $this->skin;
	    
	    $this->template_path = $tpl;
	    $this->template->assign(array("template_path"=>$this->template_path));
	    
	    $this->template->assign($this->get_my_new($userData['userid']));
	    $this->template->define('mypage_menu', $this->skin.'/user/mypage_menu.html');
	    
	    $this->print_layout($skin.'/'.$tpl);
	}
	
	public function my_pay_list() {
	    if(!$this->sessionCheck()) {
	        $this->session->set_flashdata('message', '로그인이 필요한 페이지입니다');
	        pageRedirect("/user/login");
	    }
	    $tpl = 'user/my_pay_list.html';
	    $skin = $this->skin;
	    
	    $this->template_path = $tpl;
	    $this->template->assign(array("template_path"=>$this->template_path));
	    
	    $pay_type_list = $this->input->post('pay_type_list');
	    $date_s = $this->input->post('date_s');
	    $date_f = $this->input->post('date_f');
	    
	    $pay_type_list = empty($pay_type_list) ? "" : $pay_type_list;
	    $date_s = empty($date_s) ? "" : $date_s;
	    $date_f = empty($date_s) ? "" : $date_f;
	    
	    if($pay_type_list != "") {
	        if(strpos($pay_type_list, '기타') !== false) {
	            $split = explode(", ", str_replace("'", "", $pay_type_list));
	            $list = "현장미팅, 머박다이렉트, 프리미엄광고, 기계평가, 성능검사, 비교견적, 외주, 배송대행";
	            foreach($split as $row) {
	                if(strpos($list, $row) !== false) {
	                    $list = str_replace($row, "", $list);
	                }
	            }
                $list = explode(", ", $list);
	            $not_pay_type_list = "";
                foreach($list as $row) {
                    if($row != "")
                        $not_pay_type_list .= $not_pay_type_list == "" ? "'".$row."'" : ", '".$row."'";
                }
	            $where_query .= "and pay_type not in(".$not_pay_type_list.") ";
	        } else {
    	        $where_query .= "and pay_type in(".$pay_type_list.") ";
	        }
	    }
	    
        if($date_s == '' && $date_f == '') {
            $where_query .= "";
        } else if($date_s != '' && $date_f == '') {
            $where_query .= "and date_format(reg_date, '%Y-%m-%d') >= '".$date_s."' ";
        } else if($date_s == '' && $date_f != '') {
            $where_query .= "and date_format(reg_date, '%Y-%m-%d') <= '".$date_f."' ";
        } else if($date_s != '' && $date_f != '') {
            $where_query .= "and date_format(reg_date, '%Y-%m-%d') between '".$date_s."' and '".$date_f."' ";
        }
        $userData = $this->getUserData();
	    $query = "select * from fm_cm_machine_pay where pay_state = '결제확인' and pay_userid = '".$userData['userid']."' ".$where_query."order by reg_date desc";
	    $query = $this->db->query($query);
	    $pay_list = $query->result_array();
	    
	    $query = "select * from fm_cm_machine_pay where pay_state != '결제확인' and pay_userid = '".$userData['userid']."' ".$where_query."order by reg_date desc";
	    $query = $this->db->query($query);
	    $pay_wait_list = $query->result_array();
	    
	    $this->template->assign($this->get_my_new($userData['userid']));
	    
	    $this->template->assign('pay_list', $pay_list);
	    $this->template->assign('pay_wait_list', $pay_wait_list);
	    $this->template->assign(array('pay_type_list' => $pay_type_list, 'date_s' => $date_s, 'date_f' => $date_f));
	    $this->template->define('mypage_menu', $this->skin.'/user/mypage_menu.html');
	    
	    $this->print_layout($skin.'/'.$tpl);
	}
	
	public function my_using_service() {
	    if(!$this->sessionCheck()) {
	        $this->session->set_flashdata('message', '로그인이 필요한 페이지입니다');
	        pageRedirect("/user/login");
	    }
	    $tpl = 'user/my_using_service.html';
	    $skin = $this->skin;
	    
	    $this->template_path = $tpl;
	    $this->template->assign(array("template_path"=>$this->template_path));
	    
	    $userData = $this->getUserData();
	    
	    $list = $this->get_using_service2($userData['userid']);
	    
	    $this->template->assign('list', $list);
	    
	    $this->template->assign($this->get_my_new($userData['userid']));
	    $this->template->define('mypage_menu', $this->skin.'/user/mypage_menu.html');
	    
	    $this->print_layout($skin.'/'.$tpl);
	}

	public function update_service_process() {
	    $ad_seq = $this->input->post('ad_seq');
	    $remaining = $this->input->post('remaining');
	    
	    $data = array(
	        'remaining' => (int)$remaining - 1,
	        'update_time' => date('Y-m-d H:i:s')
	    );
	    $this->db->where('ad_seq', $ad_seq);
	    $this->db->update('fm_cm_machine_sales_advertise', $data);
	    
	    $this->session->set_flashdata('message', '자동업데이트가 사용되었습니다.');
	    pageRedirect("/user/my_using_service");
	}
	
	public function hotmark_service_process() {
	   $ad_seq = $this->input->post('ad_seq');    
	   $hotmark_list = $this->input->post('hotmark_list');
	   
	   $data = array(
	       'hotmark_list' => $hotmark_list
	   );
	   $this->db->where('ad_seq', $ad_seq);
	   $this->db->update('fm_cm_machine_sales_advertise', $data);
	   
	   $this->session->set_flashdata('message', '핫마크 설정이 완료되었습니다.');
	   pageRedirect("/user/my_using_service");
	}

	public function service($info_seq, $sale_type) {
		if(!$this->sessionCheck()) {
	        $this->session->set_flashdata('message', '로그인이 필요한 페이지입니다');
	        pageRedirect("/user/login");
	    }
	    $tpl = 'user/service.html';
	    $skin = $this->skin;
	    
	    $this->template_path = $tpl;
	    $this->template->assign(array("template_path"=>$this->template_path));
	    
	    $userData = $this->getUserData();
	    
        $sale_info = $this->get_detail_query($sale_type, $info_seq);
        $this->template->assign($sale_info['info_list']);
        $this->template->assign($sale_info);

	    $this->template->assign($this->get_my_new($userData['userid']));
	    $this->template->define('mypage_menu', $this->skin.'/user/mypage_menu.html');
	    
	    $this->print_layout($skin.'/'.$tpl);
	}

	public function service_process() {
		$info_seq = $this->input->post('info_seq');
		$total_price = $this->input->post('total_price');
		$ad_name_arr = $this->input->post('ad_name');
        $ad_price_arr = $this->input->post('ad_price');
        $perform_check_yn = $this->input->post('perform_check_yn');
        $online_eval_yn = $this->input->post('online_eval_yn');
        $online_eval_option = $this->input->post('online_eval_option');
        
        $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_model c where a.sales_seq = b.sales_seq and b.model_seq = c.model_seq and b.info_seq = ".$info_seq;
        $query = $this->db->query($query);
        $prev_data = $query->row_array();
        
        $userData = $this->getUserData();
        
        if($prev_data['total_price'] < $total_price) {
            $is_pay = true;
            $state = '입금대기';
            $diff_price = $total_price - $prev_data['total_price'];
        } else {
            $is_pay = false;
            $state = $prev_data['state'];
        }
        $sales_data = array(
            'total_price' => $total_price,
            'pay_method' => '무통장 입금'
        );
        
        $info_data = array(
            'state' => $state
        );
        
        $pay_content = "";
        $pay_price = 0;
        $this->db->where('info_seq', $info_seq);
        $this->db->delete('fm_cm_machine_sales_advertise');
        for($i=0; $i<count($ad_name_arr); $i++) {
            $query = "select * from fm_cm_machine_sales_advertise where info_seq = ".$info_seq." and ad_name = '".$ad_name_arr[$i]."'";
            $query = $this->db->query($query);
            $result = $query->row_array();
            if(empty($result)) {
                $ad_data = array();
                $ad_data['info_seq'] = $info_seq;
                $ad_data['ad_name'] = $ad_name_arr[$i];
                $ad_data['price'] = $ad_price_arr[$i];
                $ad_data['start_date'] = date('Y-m-d');
                $ad_data['end_date'] = date('Y-m-d', strtotime('+30 days'));
                if($ad_name_arr[$i] == "자동 업데이트")
                    $ad_data['remaining'] = 10;
                $this->db->insert('fm_cm_machine_sales_advertise', $ad_data);
                
                $pay_content .= $pay_content == "" ? $ad_name_arr[$i] : ", ".$ad_name_arr[$i];
            }
        }
        
        $pay_data_list = array();
        if($perform_check_yn == 'y') {
            $query = "select * from fm_cm_machine_perform where info_seq = ".$info_seq;
            $query = $this->db->query($query);
            $result = $query->row_array();
            if(empty($result)) {
                $perform_data = array();
                $perform_data['info_seq'] = $info_seq;
                $this->db->insert('fm_cm_machine_perform', $perform_data);
                
                $pay_state = '입금대기';
                
                $pay_data = array();
                $pay_data['pay_userid'] = $userData['userid'];
                $pay_data['pay_content'] = '오프라인 성능검사 결제 (서비스변경 차액 결제)';
                $pay_data['pay_price'] = $diff_price;
                $pay_data['pay_method'] = '무통장입금';
                $pay_data['pay_state'] = $pay_state;
                $pay_data['pay_type'] = '성능검사';
                $pay_data_list[] = $pay_data;
            }
        } else {
            $this->db->where('info_seq', $info_seq);
            $this->db->delete('fm_cm_machine_perform');
        }
        if($online_eval_yn == 'y') {
            $query = "select * from fm_cm_machine_online_eval where info_seq = ".$info_seq;
            $query = $this->db->query($query);
            $result = $query->row_array();
            if(empty($result)) {
                $eval_data = array();
                $eval_data['info_seq'] = $info_seq;
                $eval_data['eval_name'] = $online_eval_option;
                $this->db->insert('fm_cm_machine_online_eval', $eval_data);

                $pay_state = '입금대기';
                
                $pay_data = array();
                $pay_data['pay_userid'] = $userData['userid'];
                $pay_data['pay_content'] = $online_eval_option.' 결제 (서비스변경 차액 결제)';
                $pay_data['pay_price'] = $diff_price;
                $pay_data['pay_method'] = '무통장입금';
                $pay_data['pay_state'] = $pay_state;
                $pay_data['pay_type'] = '기계평가';
                $pay_data_list[] = $pay_data;
            }
        } else {
            $this->db->where('info_seq', $info_seq);
            $this->db->delete('fm_cm_machine_online_eval');
        }
        
        if($pay_content != "") {
            $pay_state = '입금대기';
            
            $pay_data = array();
            $pay_data['pay_userid'] = $userData['userid'];
            $pay_data['pay_content'] = '프리미엄광고 ('.$pay_content.') 결제 (서비스변경 차액 결제)';
            $pay_data['pay_price'] = $diff_price;
            $pay_data['pay_method'] ='무통장입금';
            $pay_data['pay_state'] = $pay_state;
            $pay_data['pay_type'] = '프리미엄광고';
            $pay_data_list[] = $pay_data;
        }

        if($is_pay == true) {
            $this->db->where('sales_seq', $prev_data['sales_seq']);
            $this->db->update('fm_cm_machine_sales', $sales_data);
            $this->db->where('info_seq', $info_seq);
            $this->db->update('fm_cm_machine_sales_info', $info_data);
            
            foreach($pay_data_list as &$pay_data) {
                $pay_data['pay_no'] = $this->get_pay_no();
                $pay_data['target_seq'] = $info_seq;
                $this->db->insert('fm_cm_machine_pay', $pay_data);
            }
            
            $title = "유료서비스 변경<b>결제 안내</b>";
            $message = "※ 유료서비스 변경 결제안내\r\n판매자 " . $userData['userid'] . '님이 등록하신 '.$prev_data['model_name']."(" . $prev_data['sales_no'] . ")의 유료서비스가 변경되어 차액에 대한 결제가 필요합니다.\r\n- 입금안내 : 에스디네트웍스(신동훈), 농협은행, 계좌번호 302-1371-4082-81, 결제금액 ".number_format($diff_price)."원";
            
            $this->send_common_sms($userData['cellphone'], $message);
            $this->send_common_mail($userData['email'], $title, $message);

            $text = "유료서비스 신청이 완료되었습니다.\\n서비스 변경에 대한 차액 결제가 필요합니다. 결제 안내문자를 확인해주세요.";
        } else {
            $text = '유료서비스 신청이 완료되었습니다.';
        }
        pageRedirect("/user/my_using_service");
        $this->session->set_flashdata('message', $text);
	}
	
	public function my_mch_service() {
	    if(!$this->sessionCheck()) {
	        $this->session->set_flashdata('message', '로그인이 필요한 페이지입니다');
	        pageRedirect("/user/login");
	    }
	    $tpl = 'user/my_mch_service.html';
	    $skin = $this->skin;
	    
	    $this->template_path = $tpl;
	    $this->template->assign(array("template_path"=>$this->template_path));
	    
	    $userData = $this->getUserData();
	    
	    $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_kind c, fm_cm_machine_manufacturer d, fm_cm_machine_model e ".
	             "where a.sales_seq = b.sales_seq and b.kind_seq = c.kind_seq and b.mnf_seq = d.mnf_seq and b.model_seq = e.model_seq and state = '승인' and a.userid = '".$userData['userid']."' ".
	             "order by sales_no asc";
	    $query = $this->db->query($query);
	    $result = $query->result_array();
	    $this->template->assign('sales_list', $result);
	    
	    $query = "select * from fm_cm_machine_delivery where userid = '".$userData['userid']."' order by reg_date desc";
	    $query = $this->db->query($query);
	    $result = $query->result_array();
	    $this->template->assign('deliv_list', $result);
	    
	    $this->template->assign($this->get_my_new($userData['userid']));
	    $this->template->define('mypage_menu', $this->skin.'/user/mypage_menu.html');
	    
	    $this->print_layout($skin.'/'.$tpl);
	}
	
	public function my_mch_service_process() {
	    $info_seq = $this->input->post('info_seq');
	    $sales_no = $this->input->post('sales_no');
	    $kind_name = $this->input->post('kind_name');
	    $model_name = $this->input->post('model_name');
	    $mnf_name = $this->input->post('mnf_name');
	    $size = $this->input->post('size');
	    $weight = $this->input->post('weight');
	    $start_addr = $this->input->post('start_addr');
	    $end_addr = $this->input->post('end_addr');
	    $duration = $this->input->post('duration');
	    $service_list = $this->input->post('service_list');
	    $deliv_content = $this->input->post('deliv_content');
	    
	    $userData = $this->getUserData();
	    
	    $data = array(
	        'userid' => $userData['userid'],
	        'sales_no' => $sales_no,
	        'kind_name' => $kind_name,
	        'model_name' => $model_name,
	        'mnf_name' => $mnf_name,
	        'size' => $size,
	        'weight' => $weight,
	        'start_addr' => $start_addr,
	        'end_addr' => $end_addr,
	        'duration' => $duration,
	        'service_list' => $service_list,
	        'deliv_content' => $deliv_content,
	        'pay_price' => 0,
	        'pay_state' => '승인대기',
	        'deliv_state' => '작업시작전',
	        'info_seq' => $info_seq
	    );
	    $this->db->insert('fm_cm_machine_delivery', $data);
	    
	    $callback = "parent.location.reload()";
	    openDialogAlert('신청이 완료되었습니다. 관리자 승인을 기다려주세요.',400,140,'parent',$callback);
	}
	
	public function get_kind_type() {
	    header("Content-Type: application/json");
	    
	    $query = "select * from fm_cm_machine_kind group by kind_no";
	    $query = $this->db->query($query);
	    $result = $query->result_array();
	    
	    $kind_type = array();
	    foreach($result as $row) {
	        $kind_type[] = $row['kind_type'];
	    }
	    echo json_encode(array('kind_type' => $kind_type));
	}
	
	public function get_my_sale() {
		header("Content-Type: application/json");

		$userData = $this->getUserData();
		$query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_kind c,".
                "fm_cm_machine_manufacturer d, fm_cm_machine_model e, fm_cm_machine_area f ".
                "where a.sales_seq = b.sales_seq and b.kind_seq = c.kind_seq and b.mnf_seq = d.mnf_seq ".
                "and b.model_seq = e.model_seq and b.area_seq = f.area_seq and a.userid = '".$userData['userid']."' order by b.sales_no asc";
        $query = $this->db->query($query);
        $list = $query->result_array();
        
        echo json_encode(array('list' => $list));
	}

	public function is_main_dealer() {
	    header("Content-Type: application/json");
	    $userData = $this->getUserData();
	    
	    $query = "select * from fm_member_business where member_seq = ".$userData['member_seq'];
	    $query = $this->db->query($query);
	    $result = $query->row_array();
	    echo json_encode($result['main_dealer_yn']);
	}
	
	public function my_sale_estimate() {
	    if(!$this->sessionCheck()) {
	        $this->session->set_flashdata('message', '로그인이 필요한 페이지입니다');
	        pageRedirect("/user/login");
	    }
	    $tpl = 'user/my_sale_estimate.html';
	    $skin = $this->skin;
	    
	    $this->template_path = $tpl;
	    $this->template->assign(array("template_path"=>$this->template_path));
	    
	    $userData = $this->getUserData();
	    
	    $query = "select *, a.userid as sale_userid, c.userid as dealer_userid from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_estimate_dealer c ".
	             "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and c.state = '작성대기' and c.userid = '".$userData['userid']."' order by c.reg_date desc";
	    $query = $this->db->query($query);
	    $wait_list = $query->result_array();
	    $this->template->assign('wait_list', $wait_list);
	    
	    $query = "select *, a.userid as sale_userid, c.userid as dealer_userid from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_estimate_dealer c ".
	   	    "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and c.state != '작성대기' and c.userid = '".$userData['userid']."' order by c.reg_date desc";
	    $query = $this->db->query($query);
	    $finish_list = $query->result_array();
	    $this->template->assign('finish_list', $finish_list);
	    
	    $data = array(
	        'view_yn' => 'y'
	    );
	    $this->db->where('userid', $userData['userid']);
	    $this->db->update('fm_cm_machine_estimate_dealer', $data);
	    
	    $this->template->assign($this->get_my_new($userData['userid']));
	    $this->template->define('mypage_menu', $this->skin.'/user/mypage_menu.html');
	    
	    $this->print_layout($skin.'/'.$tpl);
	}
	
	public function estimate_form($estimate_seq, $mode, $is_admin) {
	    $tpl = 'user/estimate_form.html';
	    $skin = $this->skin;
	    
	    $this->template_path = $tpl;
	    $this->template->assign(array("template_path"=>$this->template_path));
	    
	    $userData = $this->getUserData();
	    
	    if($mode == 'regist') {
	        $query = "select *, a.userid as sale_userid, c.userid as buy_userid from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_estimate_dealer c, fm_cm_machine_kind d, fm_cm_machine_model e, fm_cm_machine_manufacturer f ".
	   	        "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and b.kind_seq = d.kind_seq and b.model_seq = e.model_seq and b.mnf_seq = f.mnf_seq and estimate_seq = ".$estimate_seq;
	        $query = $this->db->query($query);
	        $model_info = $query->row_array();
	        
	        $query = "select * from fm_cm_machine_sales_option where info_seq = ".$model_info['info_seq'];
	        $query = $this->db->query($query);
	        $result = $query->result_array();
	        $option_list = "";
	        foreach($result as $row) {
	            $option_list .= $option_list == "" ? $row['option_name'] : ", ".$row['option_name'];
	        }
	        $model_info['option_list'] = $option_list;
	        
	        $sale_info = $this->getUserDataById($model_info['sale_userid']);
	        $buy_info = $this->getUserDataById($model_info['buy_userid']);
	    } else if ($mode == 'view') {
	        $query = "select * from fm_cm_machine_estimate_form where estimate_seq = ".$estimate_seq;
	        $query = $this->db->query($query);
	        $result = $query->row_array();
	        
	        $model_info = array(
	            'estimate_seq' => $result['estimate_seq'],
	            'kind_type' => $result['kind_type'],
	            'kind_name' => $result['kind_name'],
	            'mnf_name' => $result['mnf_name'],
	            'model_name' => $result['model_name'],
	            'model_year' => $result['model_year'],
	            'serial_num' => $result['serial_num'],
	            'option_list' => $result['option_list']
	        );
	        $sale_info = array(
	            'userid' => $result['sale_userid'],
	            'baddress' => $result['sale_baddress'],
	            'bno' => $result['sale_bno'],
	            'bcellphone' => $result['sale_phone'],
	        );
	        $buy_info = array(
	            'userid' => $result['buy_userid'],
	            'baddress' => $result['buy_baddress'],
	            'bno' => $result['buy_bno'],
	            'bcellphone' => $result['buy_phone'],
	        );
	        $estimate_info = array(
	            'estimate_price' => $result['estimate_price'],
	            'reg_date' => $result['reg_date']
	        );
	    }
	    $this->template->assign('model_info', $model_info);
	    $this->template->assign('sale_info', $sale_info);
	    $this->template->assign('buy_info', $buy_info);
	    $this->template->assign('estimate_info', $estimate_info);
	    
	    $this->template->assign('mode', $mode);
	    $this->template->assign('is_popup', 'true');
	    if(empty($is_admin))
    	    $this->template->assign('is_admin', 'y');
	        
	    $this->print_layout($skin.'/'.$tpl);
	}
	
	public function estimate_form_regist() {
	    $sale_userid = $this->input->post('sale_userid');
	    $sale_baddress = $this->input->post('sale_baddress');
	    $sale_bno = $this->input->post('sale_bno');
	    $sale_phone = $this->input->post('sale_phone');
	    $buy_userid = $this->input->post('buy_userid');
	    $buy_baddress = $this->input->post('buy_baddress');
	    $buy_bno = $this->input->post('buy_bno');
	    $buy_phone = $this->input->post('buy_phone');
	    $kind_type = $this->input->post('kind_type');
	    $kind_name = $this->input->post('kind_name');
	    $mnf_name = $this->input->post('mnf_name');
	    $model_name = $this->input->post('model_name');
	    $model_year = $this->input->post('model_year');
	    $serial_num = $this->input->post('serial_num');
	    $option_list = $this->input->post('option_list');
	    $estimate_price = $this->input->post('estimate_price');
	    
	    $estimate_seq = $this->input->post('estimate_seq');
	    
	    $this->db->where('estimate_seq', $estimate_seq);
	    $this->db->delete('fm_cm_machine_estimate_form');
	    $data = array(
	        'estimate_seq' => $estimate_seq,
	        'sale_userid' => $sale_userid,
	        'sale_baddress' => $sale_baddress,
	        'sale_bno' => $sale_bno,
	        'sale_phone' => $sale_phone,
	        'buy_userid' => $buy_userid,
	        'buy_baddress' => $buy_baddress,
	        'buy_bno' => $buy_bno,
	        'buy_phone' => $buy_phone,
	        'kind_type' => $kind_type,
	        'kind_name' => $kind_name,
	        'mnf_name' => $mnf_name,
	        'model_name' => $model_name,
	        'model_year' => $model_year,
	        'serial_num' => $serial_num,
	        'option_list' => $option_list,
	        'estimate_price' => $estimate_price
	    );
	    $this->db->insert('fm_cm_machine_estimate_form', $data);
	    
	    $data = array(
	        'state' => '작성완료' 
	    );
	    $this->db->where('estimate_seq', $estimate_seq);
	    $this->db->update('fm_cm_machine_estimate_dealer', $data);
	    
	    $callback = "parent.close_estimate_form()";
	    openDialogAlert('제출이 완료되었습니다.',400,140,'parent',$callback);
	}
	
	public function estimate_cancel() {
	   $estimate_seq = $this->input->post('estimate_seq');
	   
	   $data = array(
	       'state' => '입찰거절'
	   );
	   $this->db->where('estimate_seq', $estimate_seq);
	   $this->db->update('fm_cm_machine_estimate_dealer', $data);
	   
	   $callback = "parent.location.reload()";
	   openDialogAlert('처리 되었습니다.',400,140,'parent',$callback);
	}
	
	public function sale_finish_process() {
	    $type = $this->input->post('type');    
	    $info_seq = $this->input->post('info_seq');    
	    $sales_method = $this->input->post('sales_method');    
	    $sales_price = $this->input->post('sales_price');    
	    $sales_cancel_reason = $this->input->post('sales_cancel_reason');    
	    
	    if($type == 'finish') {
	        $data = array(
	            'sales_yn' => 'y',
	            'sales_method' => $sales_method,
	            'sales_price' => $sales_price,
	            'sales_finish_date' => date('Y-m-d H:i:s')
	        );
	    } else if($type == 'cancel') {
	        $data = array(
	            'state' => '등록취소',
	            'sales_cancel_reason' => $sales_cancel_reason,
	            'sales_cancel_date' => date('Y-m-d H:i:s')
	        );
	    }
	    $this->db->where('info_seq', $info_seq);
	    $this->db->update('fm_cm_machine_sales_info', $data);
	    
	    $callback = "parent.location.reload()";
	    openDialogAlert('정상적으로 처리 되었습니다.',400,140,'parent',$callback);
	}
	
	private function get_service_list($userid, $service) {
	    $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_sales_advertise c where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and b.state = '승인' and b.sales_yn = 'n' and userid = '".$userid."' and ad_name = '".$service."' order by sales_date desc";
	    $query = $this->db->query($query);
	    $result = $query->result_array();
	    return $result;
	}
	
	private function get_proposal_list($userid) {
	    $query = "select *, c.userid as sale_userid from fm_cm_machine_proposal a, fm_cm_machine_sales_info b, fm_cm_machine_sales c where a.info_seq = b.info_seq and b.sales_seq = c.sales_seq and a.userid = '".$userid."' order by reg_date desc";
	    $query = $this->db->query($query);
	    $result = $query->result_array();
	    return $result;
	}
	
	private function get_proposal_list_for_sale($userid) {
	    $resultMap = array();
	    $query = "select count(*) as cnt from fm_cm_machine_proposal a, fm_cm_machine_sales_info b, fm_cm_machine_sales c where a.info_seq = b.info_seq and b.sales_seq = c.sales_seq and a.admin_yn = 'y' and c.userid = '".$userid."' order by reg_date desc";
	    $query = $this->db->query($query);
	    $result = $query->row_array();
	    $resultMap['prop_cnt'] = $result['cnt'];
	    
	    
	    $query = "select *, a.userid as buy_userid, c.userid as sale_userid from fm_cm_machine_proposal a, fm_cm_machine_sales_info b, fm_cm_machine_sales c where a.info_seq = b.info_seq and b.sales_seq = c.sales_seq and c.userid = '".$userid."' group by a.info_seq order by reg_date desc";
	    $query = $this->db->query($query);
	    $result = $query->result_array();
	    
	    foreach($result as &$row) {
	        $query = "select *, a.userid as buy_userid, c.userid as sale_userid from fm_cm_machine_proposal a, fm_cm_machine_sales_info b, fm_cm_machine_sales c where a.info_seq = b.info_seq and b.sales_seq = c.sales_seq and a.info_seq = ".$row['info_seq']." and  c.userid = '".$userid."' order by reg_date desc";
	        $query = $this->db->query($query);
	        $list = $query->result_array();
	        $row['list'] = $list;
	    }
	    $resultMap['prop_list'] = $result;
	    return $resultMap;
	}
	
	private function get_bid_list($userid) {
	    $query = "select * from fm_cm_machine_bid where userid = '".$userid."' order by reg_date desc";
	    $query = $this->db->query($query);
	    $result = $query->result_array();
	    return $result;
	}
	
	private function get_bid_list_02($userid) {
	    $query = "select *, d.bid_yn as bid_result_yn from fm_cm_machine_bid a, fm_cm_machine_sales_info b, fm_cm_machine_sales c, fm_cm_machine_sales_detail d where a.info_seq = b.info_seq and b.sales_seq = c.sales_seq and b.info_seq = d.info_seq and d.bid_yn = 'n' and a.userid = '".$userid."' group by a.info_seq order by reg_date desc";
	    $query = $this->db->query($query);
	    $bid_list = $query->result_array();
	    
	    foreach($bid_list as &$row) {
	        $query = "select *, a.bid_price as my_bid_price from fm_cm_machine_bid a, fm_cm_machine_sales_info b, fm_cm_machine_sales c, fm_cm_machine_sales_detail d where a.info_seq = b.info_seq and b.sales_seq = c.sales_seq and b.info_seq = d.info_seq and a.userid = '".$userid."' and a.info_seq = ".$row['info_seq']." order by reg_date asc";
	        $query = $this->db->query($query);
	        $info_list = $query->result_array();
	        $row['info_list'] = $info_list;
	        $info_seq = $row['info_seq'];
	    
    	    $query = "select *, UNIX_TIMESTAMP(now()) as now_date, UNIX_TIMESTAMP(date_add(sales_date, interval +bid_duration day)) as bid_date ".
    	   	    "from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_sales_detail c ".
    	   	    "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and b.info_seq = ".$info_seq;
    	    $query = $this->db->query($query);
    	    $result = $query->row_array();
    	    
    	    $now_date = $result['now_date'];
    	    $bid_date = $result['bid_date'];
    	    
    	    $date1 = $bid_date;
    	    $date2 = $now_date;
    	    
    	    $data = array();
    	    $data['restTime'] = $date1 - $date2;
    	    $data['isEnd'] = false;
    	    if($date1 - $date2 <= 0) {
    	        $data['isEnd'] = true;
    	    }
	        $row['restTime'] = $data['restTime'];
	        $row['isEnd'] = $data['isEnd'];
	    }
	    return $bid_list;
	}
	
	private function get_qna_list_for_sale($userid) {
	    $resultMap = array();
	    $query = "select count(*) as cnt from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_question c ".
	             "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and send_yn = 'y' and a.userid = '".$userid."' order by c.reg_date desc";
	    $query = $this->db->query($query);
	    $result = $query->row_array();
	    $resultMap['qna_cnt'] = $result['cnt'];
	    
	    $query = "select *, a.userid as sale_userid, c.userid as qna_userid from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_question c ".
	   	    "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and send_yn = 'y' and a.userid = '".$userid."' group by c.info_seq order by c.reg_date desc";
	    $query = $this->db->query($query);
	    $result = $query->result_array();
	    foreach($result as &$row) {
	        $query = "select *, a.userid as sale_userid, c.userid as qna_userid from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_question c ".
	   	        "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and send_yn = 'y' and c.info_seq = ".$row['info_seq']." and a.userid = '".$userid."' order by c.reg_date desc";
	        $query = $this->db->query($query);
	        $list = $query->result_array();
	        $row['list'] = $list;
	    }
	    $resultMap['qna_list'] = $result;
	    return $resultMap;
	}
	
	private function get_visit_list($userid) {
	    $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_visit c ".
	             "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and c.state not in('4', '5') and c.userid = '".$userid."' order by c.reg_date desc";
	    $query = $this->db->query($query);
	    $result = $query->result_array();
	    foreach($result as &$row) {
	        $query = "select * from fm_cm_machine_visit_detail where visit_seq = ".$row['visit_seq'];
	        $query = $this->db->query($query);
	        $det_list = $query->result_array();
	        foreach($det_list as &$row2) {
	            $week = array("일요일" , "월요일"  , "화요일" , "수요일" , "목요일" , "금요일" ,"토요일");
	            $weekday = $week[date('w', strtotime($row2['hope_date']))];
	            $hope_time = explode(':', $row2['hope_time']);
	            $full_date = date('Y년 m월 d일', strtotime($row2['hope_date']))." ".$weekday." ".$hope_time[0].'시 '.$hope_time[1].'분';
	            $row2['full_date'] = $full_date;
	        }
	        $row['det_list'] = $det_list;
	    }
	    return $result;
	}
	
	private function get_visit_list_for_sale($userid) {
	    $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_visit c ".
	             "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and c.state not in('4', '5') and a.userid = '".$userid."' order by c.reg_date desc";
	    $query = $this->db->query($query);
	    $result = $query->result_array();
	    foreach($result as &$row) {
	        $query = "select * from fm_cm_machine_visit_detail where visit_seq = ".$row['visit_seq'];
	        $query = $this->db->query($query);
	        $det_list = $query->result_array();
	        foreach($det_list as &$row2) {
	            $week = array("일요일" , "월요일"  , "화요일" , "수요일" , "목요일" , "금요일" ,"토요일");
	            $weekday = $week[date('w', strtotime($row2['hope_date']))];
	            $hope_time = explode(':', $row2['hope_time']);
	            $full_date = date('Y년 m월 d일', strtotime($row2['hope_date']))." ".$weekday." ".$hope_time[0].'시 '.$hope_time[1].'분';
	            $row2['full_date'] = $full_date;
	        }
	        $row['det_list'] = $det_list;
	    }
	    return $result;
	}
	
	private function get_imdbuy_list($userid) {
	    $query = "select *, a.userid as sale_userid, d.userid as buy_userid from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_model c, fm_cm_machine_imdbuy d ".
	   	    "where a.sales_seq = b.sales_seq and b.model_seq = c.model_seq and b.info_seq = d.info_seq and d.userid = '".$userid."'";
	    $query = $this->db->query($query);
	    $result = $query->result_array();
	    
	    return $result;
	}
	
	private function get_imdbuy_list_for_sale($userid) {
	    $query = "select *, a.userid as sale_userid, d.userid as buy_userid from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_model c, fm_cm_machine_imdbuy d ".
	   	    "where a.sales_seq = b.sales_seq and b.model_seq = c.model_seq and b.info_seq = d.info_seq and d.permit_yn = 'y' and a.userid = '".$userid."'";
	    $query = $this->db->query($query);
	    $result = $query->result_array();
	    
	    return $result;
	}
	
	private function get_emergency_list($userid) {
	    $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b where a.sales_seq = b.sales_seq and type = 'emergency' and userid = '".$userid."' order by sales_date desc limit 1";
	    $query = $this->db->query($query);
	    $result = $query->row_array();
	    
	    if(!empty($result)) {
    	    $query = "select * from fm_cm_machine_estimate_dealer where info_seq = ".$result['info_seq']." and select_yn = 'y'";
    	    $query = $this->db->query($query);
    	    $result2 = $query->row_array();
    	    if(!empty($result2)) {
    	        $result['select_yn'] = 'y';
    	    }
	    }
	    return $result;
	}
	
	private function get_sales_count($userid) {
	    $result_array = array();
	    $query = "select count(*) as fixed_cnt from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_sales_detail c where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and b.state = '승인' and wait_yn = 'n' and sales_yn = 'n' and b.sort_price is not null and b.sort_price != 0 and method = '고정가격판매' and userid = '".$userid."'";
	    $query = $this->db->query($query);
	    $result = $query->row_array();
	    $result_array['fixed_cnt'] = $result['fixed_cnt'];
	    
	    $query = "select count(*) as bid_cnt from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_sales_detail c where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and b.state = '승인' and wait_yn = 'n' and sales_yn = 'n' and b.sort_price is not null and b.sort_price != 0 and method = '입찰' and bid_yn = 'n' and userid = '".$userid."'";
	    $query = $this->db->query($query);
	    $result = $query->row_array();
	    $result_array['bid_cnt'] = $result['bid_cnt'];
	    
	    $query = "select count(*) as direct_cnt from fm_cm_machine_sales a, fm_cm_machine_sales_info b where a.sales_seq = b.sales_seq and b.state = '승인' and wait_yn = 'n' and sales_yn = 'n' and b.sort_price is not null and b.sort_price != 0 and type = 'direct' and userid = '".$userid."'";
	    $query = $this->db->query($query);
	    $result = $query->row_array();
	    $result_array['direct_cnt'] = $result['direct_cnt'];
		
		$direct_list = $this->get_sale_list($userData['userid'], '다이렉트', 'wait');
        $self_wait_list = $this->get_sale_list($userData['userid'], '셀프판매대기', 'wait');
	    $self_list = $this->get_sale_list($userData['userid'], '셀프판매', 'wait');
		$bid_list = $this->get_sale_list($userData['userid'], '입찰', 'wait', '', 'sale');
		
		$result_array['wait_cnt'] = count($direct_list) + count($self_wait_list) + count($self_list) + count($bid_list);
		
        $query = "select count(*) as cancel_cnt from fm_cm_machine_sales a, fm_cm_machine_sales_info b where a.sales_seq = b.sales_seq and b.state = '등록취소'  and b.sort_price is not null and b.sort_price != 0 and userid = '".$userid."'";
	    $query = $this->db->query($query);
	    $result = $query->row_array();
	    $result_array['cancel_cnt'] = $result['cancel_cnt'];
        
	    $query = "select count(*) as complete_cnt from fm_cm_machine_sales a, fm_cm_machine_sales_info b where a.sales_seq = b.sales_seq and b.state = '승인' and sales_yn = 'y' and b.sort_price is not null and b.sort_price != 0 and userid = '".$userid."'";
	    $query = $this->db->query($query);
	    $result = $query->row_array();
	    $result_array['complete_cnt'] = $result['complete_cnt'];
	    
	    $query = "select count(*) as bid_buy_ing_cnt from (select count(*) from fm_cm_machine_bid a, fm_cm_machine_sales_info b, fm_cm_machine_sales c, fm_cm_machine_sales_detail d ".
	             "where a.info_seq = b.info_seq and b.sales_seq = c.sales_seq and b.info_seq = d.info_seq and d.bid_yn = 'n' and a.userid = '".$userid."' group by a.info_seq) as cnt";
	    $query = $this->db->query($query);
	    $result = $query->row_array();
	    $result_array['bid_buy_ing_cnt'] = $result['bid_buy_ing_cnt'];
	    
	    $query = "select count(*) as estimate_cnt from fm_cm_machine_estimate_dealer where userid = '".$userid."'";
	    $query = $this->db->query($query);
	    $result = $query->row_array();
	    $result_array['estimate_cnt'] = $result['estimate_cnt'];
	    
	    $query = "select count(*) as fix_buy_finish_cnt from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_sales_detail c, fm_cm_machine_buy d ".
	   	        "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and b.info_seq = d.info_seq and c.method = '고정가격판매' and d.userid = '".$userid."' ";
	    $query = $this->db->query($query);
	    $result = $query->row_array();
	    $result_array['fix_buy_finish_cnt'] = $result['fix_buy_finish_cnt'];
	    
	    $query = "select count(cnt.bid_seq) as bid_buy_finish_cnt_01 from ".
	             "(select bid_seq from fm_cm_machine_sales_info a, fm_cm_machine_sales_detail b, ".
	             "fm_cm_machine_bid c where a.info_seq = b.info_seq and a.info_seq = c.info_seq ".
	             "and b.bid_yn = 'n' and c.userid = '".$userid."' group by c.info_seq) as cnt ";
	    $query = $this->db->query($query);
	    $result = $query->row_array();
	    $result_array['bid_buy_finish_cnt_01'] = $result['bid_buy_finish_cnt_01'];
	    
	    $query = "select count(cnt.bid_seq) as bid_buy_finish_cnt_02 from ".
    	   	     "(select bid_seq from fm_cm_machine_sales_info a, fm_cm_machine_sales_detail b, ".
    	   	     "fm_cm_machine_bid c where a.info_seq = b.info_seq and a.info_seq = c.info_seq ".
    	   	     "and b.bid_yn = 'y' and c.bid_yn = 'y' and c.userid = '".$userid."' group by c.info_seq) as cnt ";
   	    $query = $this->db->query($query);
   	    $result = $query->row_array();
   	    $result_array['bid_buy_finish_cnt_02'] = $result['bid_buy_finish_cnt_02'];
	
   	    $query = "select c.info_seq as info_seq from fm_cm_machine_sales_info a, fm_cm_machine_sales_detail b, ".
       	   	     "fm_cm_machine_bid c where a.info_seq = b.info_seq and a.info_seq = c.info_seq ".
       	   	     "and b.bid_yn = 'y' and c.bid_yn = 'y' and c.userid = '".$userid."'";
   	    $query = $this->db->query($query);
   	    $result = $query->result_array();
   	    $list = "";
   	    foreach($result as $row) {
   	        $list .= $list == "" ? $row['info_seq'] : ", ".$row['info_seq'];
   	    }
   	    if(count($result) > 0)
   	        $where_query = "and c.info_seq not in(".$list.") ";
   	    $query = "select count(cnt.bid_seq) as bid_buy_finish_cnt_03 from ".
       	   	     "(select bid_seq from fm_cm_machine_sales_info a, fm_cm_machine_sales_detail b, ".
       	   	     "fm_cm_machine_bid c where a.info_seq = b.info_seq and a.info_seq = c.info_seq ".
       	   	     "and b.bid_yn = 'y' and c.bid_yn = 'x'  and c.userid = '".$userid."' ".$where_query." group by c.info_seq) as cnt ";
   	    $query = $this->db->query($query);
   	    $result = $query->row_array();
   	    $result_array['bid_buy_finish_cnt_03'] = $result['bid_buy_finish_cnt_03'];
   	   	    
	    return $result_array;
	}
	
	private function get_osc_count($userid) {
	    $result_array = array();
	    $query = "select count(*) as osc_wait_cnt from fm_cm_machine_outsourcing where permit_yn = 'n' and userid = '".$userid."'";
	    $query = $this->db->query($query);
	    $result = $query->row_array();
	    $result_array['osc_wait_cnt'] = $result['osc_wait_cnt'];
	    
	    $query = "select count(*) as osc_reg_cnt from fm_cm_machine_outsourcing where permit_yn = 'y' and state = 1 and finish_yn = 'n' and userid = '".$userid."'";
	    $query = $this->db->query($query);
	    $result = $query->row_array();
	    $result_array['osc_reg_cnt'] = $result['osc_reg_cnt'];
	    
	    $query = "select count(*) as osc_ing_cnt from fm_cm_machine_outsourcing where permit_yn = 'y' and state = 2 and finish_yn = 'n' and userid = '".$userid."'";
	    $query = $this->db->query($query);
	    $result = $query->row_array();
	    $result_array['osc_ing_cnt'] = $result['osc_ing_cnt'];
	    
	    $query = "select count(*) as osc_finish_cnt from fm_cm_machine_outsourcing where finish_yn = 'y' and userid = '".$userid."'";
	    $query = $this->db->query($query);
	    $result = $query->row_array();
	    $result_array['osc_finish_cnt'] = $result['osc_finish_cnt'];
	    
	    $query = "select count(*) as ptn_wait_cnt from fm_cm_machine_partner a, fm_cm_machine_partner_osc b where a.partner_seq = b.partner_seq and state in (0, 1) and userid = '".$userid."'";
	    $query = $this->db->query($query);
	    $result = $query->row_array();
	    $result_array['ptn_wait_cnt'] = $result['ptn_wait_cnt'];
	    
	    $query = "select count(*) as ptn_ing_cnt from fm_cm_machine_partner a, fm_cm_machine_partner_osc b where a.partner_seq = b.partner_seq and state = 2 and userid = '".$userid."'";
	    $query = $this->db->query($query);
	    $result = $query->row_array();
	    $result_array['ptn_ing_cnt'] = $result['ptn_ing_cnt'];
	    
	    $query = "select count(*) as ptn_finish_cnt from fm_cm_machine_partner a, fm_cm_machine_partner_osc b where a.partner_seq = b.partner_seq and state = 3 and userid = '".$userid."'";
	    $query = $this->db->query($query);
	    $result = $query->row_array();
	    $result_array['ptn_finish_cnt'] = $result['ptn_finish_cnt'];
	    
	    return $result_array;
	}
	
	private function get_recent_sale_list() {
	    $recent_cookies = explode(",", $_COOKIE['recent_sale_cookie']);
	    $recent_list = array();
	    if(!empty($recent_cookies)) {
    	    foreach($recent_cookies as $row) {
    	        if(!empty($row)) {
        	        $query = "select * from fm_cm_machine_sales g, fm_cm_machine_sales_info a, fm_cm_machine_kind b, fm_cm_machine_manufacturer c, fm_cm_machine_model d, fm_cm_machine_area e, fm_cm_machine_sales_picture f ".
            	   	         "where g.sales_seq = a.sales_seq and a.kind_seq = b.kind_seq and a.mnf_seq = c.mnf_seq and a.model_seq = d.model_seq and a.area_seq = e.area_seq and a.info_seq = f.info_seq ".
            	   	         "and f.sort = 2 and a.info_seq = ".$row;
        	        $query = $this->db->query($query);
        	        $result = $query->row_array();
        	        if(!empty($result))
            	        $recent_list[] = $result;
    	        }
    	    }
	    }
	    return $recent_list;
	}
	
	private function get_sale_list($userid, $type, $state, $sub, $page_type) {
	    if($type == '다이렉트') {
	        $where_query = "and type = 'direct' ";
	    } else if ($type == '고정가격') {
	        $where_query = "and type = 'self' ";
	        $detail_query = "and method = '고정가격판매' ";
	    } else if ($type == '입찰') {
	        $where_query = "and type = 'self' ";
	        $detail_query = "and method = '입찰' ";
	    } else if ($type == '긴급판매') {
	        $where_query = "and type = 'emergency' ";
	    } else if ($type == '셀프판매' || $type == '셀프판매대기') {
	        $where_query = "and type = 'self' ";
	    } 
	    if($type == '셀프판매') {
			$where_query .= "and b.state = '입금대기' and sales_yn = 'n' and wait_yn = 'n' ";	
	    } else if($type == '셀프판매대기') {
	        $where_query .= "and b.state not in('입금대기', '등록취소') and sales_yn = 'n' and wait_yn = 'y' ";
	    } else if($state == 'wait') {
	        if($type == '입찰') {
	            if($page_type == 'sale')
	                $where_query .= "and b.state not in('입금대기', '등록취소') and sales_yn = 'n' and wait_yn = 'n' ";
	            $detail_query .= "and bid_yn = 'y' ";
	        }
	        else
    	        $where_query .= "and b.state != '승인' and (b.sort_price is null or b.sort_price = 0) and sales_yn = 'n' and wait_yn = 'n' ";
	    } else if ($state == 'ing') {
	        if($type == '입찰') {
	            if($page_type == 'sale')
	                $where_query .= "and b.state = '승인' and sales_yn = 'n' ";
    	        $where_query .= "and b.sort_price is not null and b.sort_price != 0 and wait_yn = 'n' ";
	            $detail_query .= "and bid_yn = 'n' ";
	        } else {
    	        $where_query .= "and b.state = '승인' and sales_yn = 'n' and wait_yn = 'n' and b.sort_price is not null and b.sort_price != 0 ";
	        }
	    } else if ($state == 'finish') {
	        if($type == '입찰') {
	            if($page_type == 'sale')
	                $where_query .= "and b.state = '승인' and sales_yn = 'y' ";
    	        $where_query .= "and b.sort_price is not null and b.sort_price != 0 and wait_yn = 'n' ";
	        } else {
    	        $where_query .= "and b.state = '승인' and sales_yn = 'y' and wait_yn = 'n' and b.sort_price is not null and b.sort_price != 0 ";
	        }
	    } else {
	        $where_query .= "and b.state = '승인' and b.sort_price is not null and b.sort_price != 0 and wait_yn = 'n' ";
	    }
	    if(!empty($sub)) {
	        if($sub == 'buy') {
    	        $buy_from_query = ", fm_cm_machine_buy f";
    	        $buy_where_query = "and b.info_seq = f.info_seq and f.userid = '".$userid."' ";
    	        $buy_order_query = "buy_date desc, ";
	        }
	    } else {
	        $where_query .= "and a.userid = '".$userid."' ";
	    }
	    $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_kind c, fm_cm_machine_sales_picture d, fm_cm_machine_area e, fm_cm_machine_model g, fm_cm_machine_manufacturer h".$buy_from_query." ".
	   	    "where a.sales_seq = b.sales_seq and b.kind_seq = c.kind_seq and b.info_seq = d.info_seq and b.area_seq = e.area_seq and b.model_seq = g.model_seq and b.mnf_seq = h.mnf_seq ".
	   	    "and d.sort = 2 ".$where_query.$buy_where_query." order by ".$buy_order_query."sales_date desc";
	    $query = $this->db->query($query);
	    $result = $query->result_array();
	    $idx = 0;
	    foreach($result as $row) {
	        if($row['type'] == 'self') {
    	        $query2 = "select * from fm_cm_machine_sales_detail a where info_seq = ".$row['info_seq']." ".$detail_query;
    	        $query2 = $this->db->query($query2);
    	        $result2 = $query2->result_array();
    	        if(empty($result2)) {
    	            unset($result[$idx]);
    	        } else {
    	            $result[$idx] = array_merge($row, $result2[0]);
    	        }
    	        $idx ++;
	        }
	    }
	    $result = array_values($result);
	    if($sub == '낙찰') {
	        $idx = 0;
	        foreach($result as &$row) {
	            if($row['method'] == '입찰') {
	                $query2 = "select * from fm_cm_machine_bid where bid_yn = 'y' and userid = '".$userid."'";
	                $query2 = $this->db->query($query2);
	                $result2 = $query2->result_array();
	                $seq_list = array();
	                foreach($result2 as $row2) {
	                    $seq_list[] = $row2['info_seq'];
	                }
	                if(!in_array($row['info_seq'], $seq_list)) {
	                    unset($result[$idx]);
	                }
	            } else {
	                unset($result[$idx]);
	            }
	            $idx ++;
	        }
	    } else if ($sub == '유찰') {
	        $idx = 0;
	        foreach($result as &$row) {
	            if($row['method'] == '입찰') {
	                $query2 = "select * from fm_cm_machine_bid where bid_yn = 'y' and userid = '".$userid."'";
	                $query2 = $this->db->query($query2);
	                $result2 = $query2->result_array();
	                $seq_list = "";
	                foreach($result2 as $row2) {
	                    $seq_list .= $seq_list == "" ? $row2['info_seq'] : ", ".$row2['info_seq'];
	                }
	                if($seq_list != "") {
	                    $bid_where_query = "and info_seq not in(".$seq_list.")";
	                }
	                $query2 = "select * from fm_cm_machine_bid where bid_yn = 'x' and userid = '".$userid."' ".$bid_where_query." group by info_seq";
	                $query2 = $this->db->query($query2);
	                $result2 = $query2->result_array();
	                $seq_list = array();
	                foreach($result2 as $row2) {
	                    $seq_list[] = $row2['info_seq'];
	                }
	                if(!in_array($row['info_seq'], $seq_list)) {
	                    unset($result[$idx]);
	                }
	            } else {
	                unset($result[$idx]);
	            }
	            $idx ++;
	        }
	    }
	    foreach($result as &$row) {
	        $query3 = "select * from fm_cm_machine_like a where info_seq = ".$row['info_seq'];
	        $query3 = $this->db->query($query3);
	        $result3 = $query3->row_array();
	        if(empty($result3))
	            $row['like_cnt'] = 0;
            else
                $row['like_cnt'] = $result3['like_cnt'];
	    }
	    foreach($result as &$row) {
	        $query3 = "select * from fm_cm_machine_partner where userid = '".$row['userid']."'";
	        $query3 = $this->db->query($query3);
	        $result3 = $query3->row_array();
	        if(empty($result3)) {
	            $query4 = "select * from fm_member where userid = '".$row['userid']."'";
	            $query4 = $this->db->query($query4);
	            $result4 = $query4->row_array();
	            $row['partner_profile_path'] = "/data/uploads/common/no-image.png";
	            $row['partner_reg_date'] = $result4['regist_date'];
	        } else {
	            $row['partner_profile_path'] = $result3['profile_path'];
	            $row['partner_reg_date'] = $result3['reg_date'];
	        }
	    }
	    
	    foreach($result as &$row) {
	        $query = "select COALESCE(convert(avg(grade), signed integer), 0) as grade, count(*) as grade_cnt from fm_cm_machine_sales_eval a, fm_cm_machine_sales b, fm_cm_machine_sales_info c where a.info_seq = c.info_seq and b.sales_seq = c.sales_seq and b.userid = '".$row['userid']."'";
	        $query = $this->db->query($query);
	        $row['grade'] = $query->row()->grade;
	        $row['grade_cnt'] = $query->row()->grade_cnt;
	        
	        $query = "select count(*) as sale_ing_cnt from fm_cm_machine_sales a, fm_cm_machine_sales_info b where a.sales_seq = b.sales_seq and b.state = '승인' and b.sales_yn = 'n' and a.userid = '" . $row['userid'] . "'";
	        $query = $this->db->query($query);
	        $row['sale_ing_cnt'] = $query->row()->sale_ing_cnt;
	        
	        $query = "select count(*) as sale_finish_cnt from fm_cm_machine_sales a, fm_cm_machine_sales_info b where a.sales_seq = b.sales_seq and b.state = '승인' and b.sales_yn = 'y' and a.userid = '" . $row['userid'] . "'";
	        $query = $this->db->query($query);
	        $row['sale_finish_cnt'] = $query->row()->sale_finish_cnt;
	    }
	    return $result;
	}
	
	private function get_recent_osc_list() {
	    $recent_cookies = explode(",", $_COOKIE['recent_osc_cookie']);
	    $recent_list = array();
	    if(!empty($recent_cookies)) {
	        foreach($recent_cookies as $row) {
	            if(!empty($row)) {
	                $query = "select * from fm_cm_machine_outsourcing a, fm_cm_machine_area b where a.area_seq = b.area_seq and osc_seq = ".$row;
	                $query = $this->db->query($query);
	                $result = $query->row_array();
	                
	                $osc_tech = $result['osc_tech'];
                    $tech_list = explode(',', $osc_tech);
                    $result['tech_list'] = $tech_list;
                    
                    $query2 = "select count(*) as apply_cnt from fm_cm_machine_partner_osc where admin_yn = 'y' and osc_seq = ".$row;
                    $query2 = $this->db->query($query2);
                    $result2 = $query2->row_array();
                    $result['apply_cnt'] = $result2['apply_cnt'];
	                $recent_list[] = $result;
	            }
	        }
	    }
	    return $recent_list;
	}
	
	private function get_osc_list($userid, $state) {
	    if($state == 'wait') {
	        $where_query = "and permit_yn = 'n'";
	    } else if ($state == 'ing') {
	        $where_query = "and permit_yn = 'y' and state = 1";
	    } else if ($state == 'end') {
	        $where_query = "and permit_yn = 'y' and state = 2 and finish_yn = 'n'";
	    } else if ($state == 'finish') {
	        $where_query = "and finish_yn = 'y'";
	    }
	    $query = "select * from fm_cm_machine_outsourcing a, fm_cm_machine_area b where a.area_seq = b.area_seq and a.userid = '".$userid."' ".$where_query." order by reg_date desc";
   	    $query = $this->db->query($query);
   	    $result = $query->result_array();
   	    
   	    foreach($result as &$row) {
   	        $osc_tech = $row['osc_tech'];
   	        $tech_list = explode(',', $osc_tech);
   	        $row['tech_list'] = $tech_list;
   	        
   	        $query2 = "select count(*) as apply_cnt from fm_cm_machine_partner_osc where admin_yn = 'y' and osc_seq = ".$row['osc_seq'];
   	        $query2 = $this->db->query($query2);
   	        $result2 = $query2->row_array();
   	        $row['apply_cnt'] = $result2['apply_cnt'];
   	        
   	        $query = "select * from fm_cm_machine_partner a, fm_cm_machine_area b, fm_cm_machine_partner_osc c where a.area_seq = b.area_seq and a.partner_seq = c.partner_seq and c.admin_yn = 'y' and c.osc_seq = ".$row['osc_seq'];
   	        $query = $this->db->query($query);
   	        $apply_list = $query->result_array();
   	        
   	        foreach($apply_list as &$row2) {
   	            $query2 = "select COALESCE(convert(avg(grade), signed integer), 0) as grade, count(*) as eval_cnt from fm_cm_machine_partner_eval where partner_seq = ".$row2['partner_seq'];
   	            $query2 = $this->db->query($query2);
   	            $result2 = $query2->row_array();
   	            $row2['grade'] = $result2['grade'];
   	            $row2['eval_cnt'] = $result2['eval_cnt'];
   	            
   	            $query2 = "select count(*) as osc_cnt from fm_cm_machine_partner_osc where partner_seq = ".$row2['partner_seq'];
   	            $query2 = $this->db->query($query2);
   	            $result2 = $query2->row_array();
   	            $row2['osc_cnt'] = $result2['osc_cnt'];
   	            
   	            $query2 = "select * from fm_cm_machine_partner_certificate where partner_seq = ".$row2['partner_seq'];
   	            $query2 = $this->db->query($query2);
   	            $cert_list = $query2->result_array();
   	            $row2['cert_list'] = $cert_list;
   	        }
   	        $row['apply_list'] = $apply_list;
   	        
   	        if($state == 'finish') {
   	            $query = "select * from fm_cm_machine_partner a, fm_cm_machine_partner_osc b where a.partner_seq = b.partner_seq and state = 3 and b.osc_seq = ".$row['osc_seq']." limit 1";
   	            $query = $this->db->query($query);
   	            $partner_info = $query->row_array();
   	            $resultMap['partner_info'] = $partner_info;
   	        }
   	    }
   	    $resultMap['osc_list'] = $result;
   	    return $resultMap;
	}
	
	private function get_ptn_list($userid, $state) {
	    if($state == 'wait') {
	        $where_query = "and d.state in (0, 1)";
	    } else if ($state == 'ing') {
	        $where_query = "and d.state = 2";
	    }  else if ($state == 'finish') {
	        $where_query = "and d.state = 3";
	    }
	    $query = "select *, a.userid as osc_userid, c.userid as ptn_userid, a.state as osc_state, d.state as ptn_state from fm_cm_machine_outsourcing a, fm_cm_machine_area b, fm_cm_machine_partner c, fm_cm_machine_partner_osc d where a.area_seq = b.area_seq and a.osc_seq = d.osc_seq and c.partner_seq = d.partner_seq and c.userid = '".$userid."' ".$where_query." order by d.reg_date desc";
	    $query = $this->db->query($query);
	    $result = $query->result_array();
	    
	    foreach($result as &$row) {
	        $osc_tech = $row['osc_tech'];
	        $tech_list = explode(',', $osc_tech);
	        $row['tech_list'] = $tech_list;
	        
	        $query2 = "select count(*) as apply_cnt from fm_cm_machine_partner_osc where admin_yn = 'y' and osc_seq = ".$row['osc_seq'];
	        $query2 = $this->db->query($query2);
	        $result2 = $query2->row_array();
	        $row['apply_cnt'] = $result2['apply_cnt'];
	        
	        $query = "select * from fm_cm_machine_partner a, fm_cm_machine_area b, fm_cm_machine_partner_osc c where a.area_seq = b.area_seq and a.partner_seq = c.partner_seq and c.osc_seq = ".$row['osc_seq'];
	        $query = $this->db->query($query);
	        $apply_list = $query->result_array();
	        
	        foreach($apply_list as &$row2) {
	            $query2 = "select COALESCE(convert(avg(grade), signed integer), 0) as grade, count(*) as eval_cnt from fm_cm_machine_partner_eval where partner_seq = ".$row2['partner_seq'];
	            $query2 = $this->db->query($query2);
	            $result2 = $query2->row_array();
	            $row2['grade'] = $result2['grade'];
	            $row2['eval_cnt'] = $result2['eval_cnt'];
	            
	            $query2 = "select count(*) as osc_cnt from fm_cm_machine_partner_osc where partner_seq = ".$row2['partner_seq'];
	            $query2 = $this->db->query($query2);
	            $result2 = $query2->row_array();
	            $row2['osc_cnt'] = $result2['osc_cnt'];
	            
	            $query2 = "select * from fm_cm_machine_partner_certificate where partner_seq = ".$row2['partner_seq'];
	            $query2 = $this->db->query($query2);
	            $cert_list = $query2->result_array();
	            $row2['cert_list'] = $cert_list;
	        }
	        $row['apply_list'] = $apply_list;
	    }
	    return $result;
	}
	
	private function get_using_service($userid) {
	    $highlight_list = $this->get_service_list($userid, '하이라이트');
	    $dealer_list = $this->get_service_list($userid, '딜러존');
	    $update_list = $this->get_service_list($userid, '자동 업데이트');
	    $hotmark_list = $this->get_service_list($userid, '핫마크');
	    
	    $service_list = array();
	    if(count($highlight_list) > 0) {
	        $service_list['하이라이트'] = $highlight_list;
	    }
	    if(count($dealer_list) > 0) {
	        $service_list['딜러존'] = $dealer_list;
	    }
	    if(count($update_list) > 0) {
	        $service_list['자동 업데이트'] = $update_list;
	    }
	    if(count($hotmark_list) > 0) {
	        $service_list['핫마크'] = $hotmark_list;
	    }
	    return $service_list;
	}
	
	private function get_using_service2($userid) {
	    $service_seq = array();
	    $query = "select * from fm_cm_machine_sales_advertise";
	    $query = $this->db->query($query);
	    $result = $query->result_array();
	    foreach($result as $row) {
	        if(!in_array($row['info_seq'], $service_seq))
	            $service_seq[] = $row['info_seq'];
	    }
	    $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b ". 
	             "where a.sales_seq = b.sales_seq and b.state = '승인' and b.sales_yn = 'n' and info_seq in(".join(", ", $service_seq).") and userid = '".$userid."' ".
	             "order by sales_date desc";
	    $query = $this->db->query($query);
	    $result = $query->result_array();
	    
	    foreach($result as &$row) {
	        $query = "select * from fm_cm_machine_sales_advertise where info_seq = ".$row['info_seq'];
	        $query = $this->db->query($query);
	        $service_list = $query->result_array();
	        $row['service_list'] = $service_list;
	    }
	    return $result;
	}
	
	private function get_my_new($userid) {
	    $resultMap = array();
	    
	    $query = "select * from fm_cm_machine_proposal a, fm_cm_machine_sales_info b, fm_cm_machine_sales c where a.info_seq = b.info_seq and b.sales_seq = c.sales_seq and a.permit_yn = 'c' and a.counter_permit_yn = 'h' and a.userid = '".$userid."'";
	    $query = $this->db->query($query);
	    $result = $query->result_array();
	    if(count($result) > 0) {
	        $resultMap['new_buy_ing'] = 'true';
	        $resultMap['new_buy_ing_prop'] = 'true';
	    }
	    $query = "select * from fm_cm_machine_proposal a, fm_cm_machine_sales_info b, fm_cm_machine_sales c where a.info_seq = b.info_seq and b.sales_seq = c.sales_seq and a.view_yn = 'n' and a.userid = '".$userid."'";
	    $query = $this->db->query($query);
	    $result = $query->result_array();
	    if(count($result) > 0) {
	        $resultMap['new_buy_ing'] = 'true';
	        $resultMap['new_buy_ing_prop'] = 'true';
	    }
	    $query = "select * from fm_cm_machine_bid a, fm_cm_machine_sales_info b, fm_cm_machine_sales c where a.info_seq = b.info_seq and b.sales_seq = c.sales_seq and a.bid_yn = 'n' and a.view_yn = 'n' and a.userid = '".$userid."'";
	    $query = $this->db->query($query);
	    $result = $query->result_array();
	    if(count($result) > 0) {
	        $resultMap['new_buy_ing'] = 'true';
	        $resultMap['new_buy_ing_bid'] = 'true';
	    }
	    $query = "select * from fm_cm_machine_visit a, fm_cm_machine_sales_info b, fm_cm_machine_sales c where a.info_seq = b.info_seq and b.sales_seq = c.sales_seq and a.view_yn = 'n' and a.userid = '".$userid."'";
	    $query = $this->db->query($query);
	    $result = $query->result_array();
	    if(count($result) > 0) {
	        $resultMap['new_buy_ing'] = 'true';
	        $resultMap['new_buy_ing_visit'] = 'true';
	    }
	    $query = "select * from fm_cm_machine_imdbuy a, fm_cm_machine_sales_info b, fm_cm_machine_sales c where a.info_seq = b.info_seq and b.sales_seq = c.sales_seq and a.view_yn = 'n' and a.userid = '".$userid."'";
	    $query = $this->db->query($query);
	    $result = $query->result_array();
	    if(count($result) > 0) {
	        $resultMap['new_buy_ing'] = 'true';
	    }
	    $query = "select * from fm_cm_machine_estimate_dealer a, fm_cm_machine_sales_info b, fm_cm_machine_sales c where a.info_seq = b.info_seq and b.sales_seq = c.sales_seq and a.view_yn = 'n' and a.userid = '".$userid."'";
	    $query = $this->db->query($query);
	    $result = $query->result_array();
	    if(count($result) > 0) {
	        $resultMap['new_sale_estimate'] = 'true';
	    }
	    $query = "select * from fm_cm_machine_bid a, fm_cm_machine_sales_info b, fm_cm_machine_sales c where a.info_seq = b.info_seq and b.sales_seq = c.sales_seq and a.bid_yn = 'x' and a.view_res_yn = 'n' and a.userid = '".$userid."'";
	    $query = $this->db->query($query);
	    $result = $query->result_array();
	    if(count($result) > 0) {
	        $resultMap['new_buy_finish'] = 'true';
	        $resultMap['new_buy_finish_bid_x'] = 'true';
	    }  
	    $query = "select * from fm_cm_machine_bid a, fm_cm_machine_sales_info b, fm_cm_machine_sales c where a.info_seq = b.info_seq and b.sales_seq = c.sales_seq and a.bid_yn = 'y' and a.view_res_yn = 'n' and a.userid = '".$userid."'";
	    $query = $this->db->query($query);
	    $result = $query->result_array();
	    if(count($result) > 0) {
	        $resultMap['new_buy_finish'] = 'true';
	        $resultMap['new_buy_finish_bid_y'] = 'true';
	    }  
	    $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_sales_detail c where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and b.state = '승인' and sales_yn = 'n' and b.sort_price is not null and b.sort_price != 0 and method = '고정가격판매' and view_yn = 'n' and userid = '".$userid."'";
	    $query = $this->db->query($query);
	    $result = $query->result_array();
	    if(count($result) > 0) {
	        $resultMap['new_sale_ing'] = 'true';
	        $resultMap['new_sale_ing_fixed'] = 'true';
	    }  
	    $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_sales_detail c where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and b.state = '승인' and sales_yn = 'n' and b.sort_price is not null and b.sort_price != 0 and method = '입찰' and bid_yn = 'n' and view_yn = 'n' and userid = '".$userid."'";
	    $query = $this->db->query($query);
	    $result = $query->result_array();
	    if(count($result) > 0) {
	        $resultMap['new_sale_ing'] = 'true';
	        $resultMap['new_sale_ing_bid'] = 'true';
	    }  
	    $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b where a.sales_seq = b.sales_seq and b.state = '승인' and sales_yn = 'n' and b.sort_price is not null and b.sort_price != 0 and type = 'direct' and view_yn = 'n' and userid = '".$userid."'";
	    $query = $this->db->query($query);
	    $result = $query->result_array();
	    if(count($result) > 0) {
	        $resultMap['new_sale_ing'] = 'true';
	        $resultMap['new_sale_ing_direct'] = 'true';
	    }  
	    $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b where a.sales_seq = b.sales_seq and type = 'emergency' and view_yn = 'n' and userid = '".$userid."'";
	    $query = $this->db->query($query);
	    $result = $query->result_array();
	    if(count($result) > 0) {
	        $resultMap['new_sale_emergency'] = 'true';
	    }  
        $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_proposal c ".
            "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and c.admin_yn = 'y' and c.permit_yn = 'h' and a.userid = '".$userid."'";
        $query = $this->db->query($query);
        $result = $query->result_array();
        if(count($result) > 0)
            $resultMap['new_sale_reply'] = 'true';
        $query = "select * from fm_cm_machine_outsourcing where permit_yn = 'n' and view_yn = 'n' and userid = '".$userid."'";
        $query = $this->db->query($query);
        $result = $query->result_array();
        if(count($result) > 0)
            $resultMap['new_osc_wait'] = 'true';
        $query = "select * from fm_cm_machine_outsourcing where permit_yn = 'y' and state = 1 and view_yn = 'n' and finish_yn = 'n' and userid = '".$userid."'";
        $query = $this->db->query($query);
        $result = $query->result_array();
        if(count($result) > 0)
            $resultMap['new_osc_ing'] = 'true';
        /*
        $query = "select * from fm_cm_machine_outsourcing a, fm_cm_machine_partner_osc b where a.osc_seq = b.osc_seq and a.permit_yn = 'y' and a.state = 1 and b.state in('0', '1') and b.meet_state = '0' and a.finish_yn = 'n' and b.admin_yn = 'y' and a.userid = '".$userid."'";
        $query = $this->db->query($query);
        $result = $query->result_array();
        if(count($result) > 0)
            $resultMap['new_osc_ing'] = 'true';
        */
        $query = "select * from fm_cm_machine_outsourcing where permit_yn = 'y' and state = 2 and finish_yn = 'n' and view_yn = 'n' and userid = '".$userid."'";
        $query = $this->db->query($query);
        $result = $query->result_array();
        if(count($result) > 0)
            $resultMap['new_osc_end'] = 'true';
        /*
        $query = "select * from fm_cm_machine_outsourcing a, fm_cm_machine_partner_osc b where a.osc_seq = b.osc_seq and a.permit_yn = 'y' and a.state = 2 and b.state in('0', '1') and b.meet_state = '0' and a.finish_yn = 'n' and b.admin_yn = 'y' and a.userid = '".$userid."'";
        $query = $this->db->query($query);
        $result = $query->result_array();
        if(count($result) > 0)
            $resultMap['new_osc_end'] = 'true';
        */
        $query = "select * from fm_cm_machine_outsourcing where finish_yn = 'y' and view_yn = 'n' and userid = '".$userid."'";
        $query = $this->db->query($query);
        $result = $query->result_array();
        if(count($result) > 0)
            $resultMap['new_osc_finish'] = 'true';
        $query = "select * from fm_cm_machine_partner_osc a, fm_cm_machine_partner b where a.partner_seq = b.partner_seq and a.state in(0, 1) and a.view_yn = 'n' and b.userid = '".$userid."'";
        $query = $this->db->query($query);
        $result = $query->result_array();
        if(count($result) > 0)
            $resultMap['new_ptn_wait'] = 'true';
        $query = "select * from fm_cm_machine_partner_osc a, fm_cm_machine_partner b where a.partner_seq = b.partner_seq and a.state in(0, 1) and a.meet_state = 1 and b.userid = '".$userid."'";
        $query = $this->db->query($query);
        $result = $query->result_array();
        if(count($result) > 0)
            $resultMap['new_ptn_wait'] = 'true';
        $query = "select * from fm_cm_machine_partner_osc a, fm_cm_machine_partner b where a.partner_seq = b.partner_seq and a.state = 2 and a.view_yn = 'n' and b.userid = '".$userid."'";
        $query = $this->db->query($query);
        $result = $query->result_array();
        if(count($result) > 0)
            $resultMap['new_ptn_ing'] = 'true';
        $query = "select * from fm_cm_machine_partner_osc a, fm_cm_machine_partner b where a.partner_seq = b.partner_seq and a.state = 3 and a.view_yn = 'n' and b.userid = '".$userid."'";
        $query = $this->db->query($query);
        $result = $query->result_array();
        if(count($result) > 0)
            $resultMap['new_ptn_finish'] = 'true';
            
        $query = "select * from fm_cm_machine_question a, fm_cm_machine_sales_info b, fm_cm_machine_sales c where a.info_seq = b.info_seq and b.sales_seq = c.sales_seq and a.view_yn = 'n' and a.userid = '".$userid."'";
        $query = $this->db->query($query);
        $result = $query->result_array();
        if(count($result) > 0)
            $resultMap['new_qna_list'] = 'true';
            
        $query = "select * from fm_cm_machine_question a, fm_cm_machine_sales_info b, fm_cm_machine_sales c where a.info_seq = b.info_seq and b.sales_seq = c.sales_seq and res_view_yn = 'n' and res_yn = 'y' and a.userid = '".$userid."'";
        $query = $this->db->query($query);
        $result = $query->result_array();
        if(count($result) > 0)
            $resultMap['new_qna_list'] = 'true';
        
        $query = "select * from fm_cm_machine_my_qna where view_yn = 'n' and userid = '".$userid."'";
        $query = $this->db->query($query);
        $result = $query->result_array();
        if(count($result) > 0)
            $resultMap['new_qna_list'] = 'true';
        
        $query = "select * from fm_cm_machine_my_qna where res_view_yn = 'n' and reply is not null and userid = '".$userid."'";
        $query = $this->db->query($query);
        $result = $query->result_array();
        if(count($result) > 0)
            $resultMap['new_qna_list'] = 'true';
        $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_question c ".
            "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and send_yn = 'y' and c.res_content is null and a.userid = '".$userid."'";
        $query = $this->db->query($query);
        $result = $query->result_array();
        if(count($result) > 0)
            $resultMap['new_qna_list'] = 'true';
        return $resultMap;
	}
	
	private function get_detail_query($type, $info_seq) {
        $resultMap = array();
        
        if($type == 'self') {
            $from_query = ", fm_cm_machine_sales_detail f";
            $where_query = "and a.info_seq = f.info_seq";
        } else {
            $from_query = "";
            $where_query = "";
        }
        $query = "select * from fm_cm_machine_sales g, fm_cm_machine_sales_info a, fm_cm_machine_kind b, fm_cm_machine_manufacturer c, fm_cm_machine_model d, fm_cm_machine_area e".$from_query." ".
            "where g.sales_seq = a.sales_seq and a.kind_seq = b.kind_seq and a.mnf_seq = c.mnf_seq and a.model_seq = d.model_seq and a.area_seq = e.area_seq ".
            "and a.info_seq = ".$info_seq." ".$where_query;
        $query = $this->db->query($query);
        $result = $query->result_array();
        $sales_seq = $result[0]['sales_seq'];
        
        $resultMap['info_list'] = $result[0];
        
        $query = "select * from fm_cm_machine_sales_info a, fm_cm_machine_sales_picture b ".
            "where a.info_seq = b.info_seq and a.info_seq = ".$info_seq." order by sort asc";
        $query = $this->db->query($query);
        $result = $query->result_array();
        $resultMap['picture_list'] = $result;
        
        $query = "select * from fm_cm_machine_sales_info a, fm_cm_machine_sales_option b ".
            "where a.info_seq = b.info_seq and a.info_seq = ".$info_seq." order by option_seq asc";
        $query = $this->db->query($query);
        $result = $query->result_array();
        $resultMap['option_list'] = $result;
        
        $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b left outer join ".
            "fm_cm_machine_perform c on b.info_seq = c.info_seq where a.sales_seq = b.sales_seq and b.info_seq = ".$info_seq;
        $query = $this->db->query($query);
        $result = $query->row_array();
        $resultMap['perform'] = $result;

        $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b left outer join ".
            "fm_cm_machine_online_eval c on b.info_seq = c.info_seq where a.sales_seq = b.sales_seq and b.info_seq = ".$info_seq;
        $query = $this->db->query($query);
        $result = $query->row_array();
        $resultMap['eval'] = $result;
        
        $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b left outer join ".
            "fm_cm_machine_sales_advertise c on b.info_seq = c.info_seq where a.sales_seq = b.sales_seq and b.info_seq = ".$info_seq;
        $query = $this->db->query($query);
        $result = $query->result_array();
        $resultMap['ad_list'] = $result;

        $query = "select * from fm_cm_machine_sales_check where sales_seq = ".$sales_seq;
        $query = $this->db->query($query);
        $result = $query->row_array();
        $resultMap['check_list'] = $result;
        
        return $resultMap;
    }

	### 세션 생성
	private function create_member_session($data=array()){
	    $this->load->helper('member');
	    create_member_session($data);
	}
	
	private function sessionCheck() {
	    $this->userInfo = $this->session->userdata('user');
	    if(!$this->userInfo['member_seq']) {
	        return false;
	    } else {
	        return true;
	    }
	}
	
	private function getUserData() {
	    $this->userInfo = $this->session->userdata('user');
	    $userData = $this->membermodel->get_member_data($this->userInfo['member_seq']);
	    $query = "select label_value as gubun from fm_member_subinfo where label_title = '회원구분' and member_seq = ".$this->userInfo['member_seq'];
	    $query = $this->db->query($query);
	    if($userData['mtype'] == 'member') 
    	    $userData['gubun'] = "일반회원";
	    else
	       $userData['gubun'] = $query->row()->gubun;
	    return $userData; 
	}
	
	private function getUserDataById($userid) {
	    $query = "select * from fm_member where userid='".$userid."'";
	    $query = $this->db->query($query);
	    $result = $query->row_array();
	    return $this->membermodel->get_member_data($result['member_seq']);
	}
	
	private function send_common_mail($email, $title, $message) {
	    if($email) {
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
}