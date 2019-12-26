<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);

class member_process extends front_base {

	public function __construct() {
		parent::__construct();
		$this->load->library('validation');
		$this->load->library('snssocial');
        $this->load->model('membermodel');
        $this->load->model('appmembermodel');
		$this->load->helper('member');

		//스킨패치 하지 않은 사용자를 위해 우편번호 합치기
		if($_POST['zipcode'][1]){
			$_POST['zipcode'][0] = @implode('',$_POST['zipcode']);
			unset($_POST['zipcode'][1]);
		}
		if($_POST['bzipcode'][1]){
			$_POST['bzipcode'][0] = @implode('',$_POST['bzipcode']);
			unset($_POST['bzipcode'][1]);
		}
	}


	###
	public function register(){
		if($_POST['agree']!='Y' || $_POST['agree2']!='Y'){
			$key	= $_POST['agree']!='Y' ? "agree" : "agree2";
			$name	= $_POST['agree']!='Y' ? "이용약관에 동의하셔야합니다." : "개인정보처리방침에 동의하셔야합니다.";
			$callback = "if(parent.document.getElementsByName('{$key}')[0]) parent.document.getElementsByName('{$key}')[0].focus();";
			openDialogAlert($name,400,140,'parent',$callback);
			exit;
		}
		$url = isset($_POST['join_type']) ? '/member/register?join_type='.$_POST['join_type'] : '../member/register';
		pageRedirect($url,'','parent');
	}

	###
	public function id_chk($chk_key = null){

		//#####  2018.02.05 gcs ksm : RSA 17.11.30~ 패치
		// https 보안강화를 위해 아이디 체크 기능 변경 front 영역은 common.js에서 호출
		if($chk_key == null ){
			$this->load->model('ssl');
			$this->ssl->decode();
		}
		//#####  2018.02.05 gcs ksm : RSA 17.11.30~ 패치

		$conf = config_load('joinform');

		if ( $conf['email_userid'] == 'Y' ) {
			if($_POST['userid'] == '@' ) $_POST['userid'] = '';
		}

		$userid = $_POST['userid'];
		if(!$userid) die();
		$userid = strtolower($userid);

		if ( $conf['email_userid'] == 'Y' ) {
			//이메일
			$this->validation->set_rules('userid', getAlert('mb051'),'trim|required|valid_email|xss_clean');
			if($this->validation->exec()===false){
				$err = $this->validation->error_array;
				//유효하지 않는 이메일 형식입니다.
				$text = getAlert('mb052');//$err['value'];
				$result = array("return_result" => $text, "userid" => $_POST['userid'], "return" => false, "returns" => false);

				if($chk_key){
					return $result;
				}else{
					//#####  2018.02.05 gcs ksm : RSA 17.11.30~ 패치
					if($_POST['sslEncodedString'] || ($_POST['jCryption'] && $_POST['encryptionKey'])){
						echo "<script>parent.callbackIdChk('".json_encode($result)."');</script>";
					}else{
						echo json_encode($result);
					}
					//#####  2018.02.05 gcs ksm : RSA 17.11.30~ 패치


					//echo json_encode($result);
				}
			}
		}

		###
		$count = get_rows('fm_member',array('userid'=>$userid));

		###
		$disabled_userid = explode(",",$conf['disabled_userid']);

		$return = true;
		if ( $conf['email_userid'] == 'Y' ) {
			$text = "OK";
			if(in_array($userid, $disabled_userid)) {
				//금지 이메일 입니다.
				$text = getAlert('mb053');
				$return = false;
			}else if($count > 0){
				//이미 등록된 이메일 입니다.
				$text = getAlert('mb054');
				$return = 'duplicate';
			}
		}else{
			//사용할 수 있는 아이디 입니다.
			$text = getAlert('mb055');
			if(strlen($userid)<6 || strlen($userid)>20){
				//아이디 글자 제한 수를 맞춰주세요.
				$text = getAlert('mb056');
				$return = false;
			}else if(preg_match("/[^a-z0-9\-_]/i", $userid)) {
				//사용할 수 없는 아이디 입니다.
				$text = getAlert('mb057');
				$return = false;
			}else if(in_array($userid, $disabled_userid)) {
				//금지 아이디 입니다.
				$text = getAlert('mb058');
				$return = false;
			}else if($count > 0){
				//이미 사용중인 아이디 입니다.
				$text = getAlert('mb059');
				$return = false;
			}
		}
		$result = array("return_result" => $text, "userid" => $userid, "return" => $return, "returns" => $return);

		if($chk_key){
			return $result;
		}else{
			//#####  2018.02.05 gcs ksm : RSA 17.11.30~ 패치
			if($_POST['sslEncodedString'] || ($_POST['jCryption'] && $_POST['encryptionKey'])){
				echo "<script>parent.callbackIdChk('".json_encode($result)."');</script>";
			}else{
				echo json_encode($result);
			}
			//#####  2018.02.05 gcs ksm : RSA 17.11.30~ 패치

			//echo json_encode($result);
		}
	}

	### 비밀번호 유효성체크
	public function pw_chk($chk_key = null){

		$conf = config_load('joinform');

		$password = $_POST['password'];
		if(!$password) die();
		//비밀번호
		$this->validation->set_rules('password', getAlert('mb060'),'trim|required|min_length[6]|max_length[32]|xss_clean');
		if($this->validation->exec()===false){
			//유효하지 않은 비밀번호 형식입니다.
			$text = getAlert('mb061');
			$result = array("return_result" => $text, "password" => $password, "return" => false, "returns" => false);

			if($chk_key){
				return $result;
			}else{
				echo json_encode($result);
			}
		}

		###
		$return = true;
		$text = "OK";
		$mix_check = 0;
		//소문자영문체크
		if(preg_match("/[a-z]/",$_POST['password'])){
			$mix_check += 1;
		}

		//대문자영문체크
		if(preg_match("/[A-Z]/",$_POST['password'])){
			$mix_check += 1;
		}

		//숫자체크
		if(preg_match("/[0-9]/",$_POST['password'])){
			$mix_check += 1;
		}

		//특수문자체크
		if(preg_match("/[!#$%^&*()?+=\/]/",$_POST['password'])){
			$mix_check += 1;
		}
		if(strlen($password)<6 || strlen($password)>20){
			//비밀번호 글자 제한 수를 맞춰주세요.
			$text = getAlert('mb062');
			$return = false;
		}

		if($mix_check < 2){
			//비밀번호는 6~20자 영문 대소문자, 숫자, 특수문자 중<br> 2가지 이상 조합이어야 합니다.
			$text = getAlert('mb037');
			$return = false;
		}
		$result = array("return_result" => $text, "password" => $password, "return" => $return, "returns" => $return);

		if($chk_key){
			return $result;
		}else{
			echo json_encode($result);
		}
	}

	###
	public function bno_chk($chk_key = null){
		$joinform = config_load('joinform');

		$bno = trim($_POST['bno']);
		$count = get_rows('fm_member_business',array('bno'=>$bno));
		$bno = str_replace('-','',$bno);

		$text = "";
		$return = true;
		if($joinform['bno_use']=='Y' && ($joinform['bno_required']=='Y' || $_POST['bno']) ) {//사용중이면서 필수 또는 입력된 경우에만
			$bcheck = $this->membermodel->bizno_check($bno);
			if( $bcheck === false ) {
				//올바르지 않은 사업자등록번호 입니다.
				$text = getAlert('mb064');
				$return = false;
			}

			if($count > 0){
				//이미 가입된 사업자등록번호 입니다.
				$text = getAlert('mb065');
				$return = false;
			}
		}
		$result = array("return_result" => $text, "bno" => $bno, "return" => $return, "returns" => $return);

		if($chk_key){
			return $result;
		}else{
			echo json_encode($result);
		}
	}



	###
	public function register_ok(){
		$this->load->model('ssl');
		$this->ssl->decode();

		$joinform				= config_load('joinform');

		$label_pr				= $_POST['label'];
		$label_sub_pr			= $_POST['labelsub'];
		$label_required			= $_POST['required'];
		$label_required_title	= $_POST['required_title'];
		$_POST['userid']		= strtolower($_POST['userid']);

		## 회원가입 버튼 재 노출
		$callback_default = "parent.document.getElementById('btn_register').style.display='block';";

		### Validation
		if ( $joinform['email_userid'] == 'Y' ) {
			//아이디
			$this->validation->set_rules('userid', getAlert('mb010'),'trim|required|valid_email|xss_clean');
			//아이디확인
			$this->validation->set_rules('re_userid', getAlert('mb011'),'trim|required|valid_email|xss_clean');
		}else{
			//아이디
			$this->validation->set_rules('userid', getAlert('mb010'),'trim|required|min_length[6]|max_length[20]|xss_clean');
		}

		//비밀번호
		$this->validation->set_rules('password', getAlert('mb012'),'trim|required|min_length[6]|max_length[32]|xss_clean');
		//비밀번호확인
		$this->validation->set_rules('re_password', getAlert('mb013'),'trim|required|min_length[6]|max_length[32]|xss_clean');

		### COMMON
		if(!empty($_POST['anniversary'][0]) && !empty($_POST['anniversary'][1]))
			$_POST['anniversary'] = implode("-",$_POST['anniversary']);
		else
			$_POST['anniversary'] = '';

		### COMMON
		if(isset($_POST['email'])) $_POST['email'] = implode("@",$_POST['email']);
		if($_POST['email'] == '@' ) $_POST['email'] = '';

		### COMMON
		if ( $joinform['email_userid'] == 'Y' ) {//&& !$_POST['email']
			$_POST['email'] = $_POST['userid'];
		}

		if( is_array($_POST['births']) ) {
			if( $_POST['births'][0] && $_POST['births'][1] && $_POST['births'][2]) {
				$_POST['birthday'] =  $_POST['births'][0].'-'.str_pad($_POST['births'][1],2 ,"0", STR_PAD_LEFT).'-'.str_pad($_POST['births'][2],2 ,"0", STR_PAD_LEFT);
			}
		}else{
			if($_POST['births']){
				$_POST['birthday'] = $_POST['births'];
			}else{
				$_POST['birthday'] = $_POST['birthday'] ? $_POST['birthday'] : '';
			}
		}

		if($joinform['recommend_use']=='Y'){
			//추천인
			if($joinform['recommend_required']=='Y') $this->validation->set_rules('recommend', getAlert('mb014'),'trim|required|max_length[100]|xss_clean');
			else $this->validation->set_rules('recommend', getAlert('mb014'),'trim|max_length[100]|xss_clean');

		}

		if(!isset($_POST['new_zipcode']) && $_POST['zipcode']){
			$_POST['new_zipcode']	= implode('',$_POST['zipcode']);
			unset($_POST['zipcode']);
		}

		### MEMBER
		if(isset($_POST['mtype']) && $_POST['mtype']=='member'){

			if($joinform['email_use']=='Y'){
				if($joinform['email_required']=='Y') {
					//이메일
					$this->validation->set_rules('email', getAlert('mb015'),'trim|required|max_length[64]|valid_email|xss_clean');
				}elseif( !empty($_POST['email'])) {
					$this->validation->set_rules('email', getAlert('mb015'),'trim|max_length[64]|valid_email|xss_clean');
				}
			}

			if($joinform['user_name_use']=='Y'){
				//이름
				if($joinform['user_name_required']=='Y') $this->validation->set_rules('user_name', getAlert('mb016'),'trim|required|max_length[32]|xss_clean');
				else $this->validation->set_rules('user_name', getAlert('mb016'),'trim|max_length[32]|xss_clean');
			}
			if($joinform['phone_use']=='Y'){
				//연락처
				if($joinform['phone_required']=='Y') $this->validation->set_rules('phone[]', getAlert('mb017'),'trim|required|max_length[4]|numeric|xss_clean');
				else $this->validation->set_rules('phone[]', getAlert('mb017'),'trim|max_length[4]|xss_clean');
			}
			if($joinform['cellphone_use']=='Y'){
				//휴대폰번호
				if($joinform['cellphone_required']=='Y') $this->validation->set_rules('cellphone[]', getAlert('mb018'),'trim|required|max_length[4]|numeric|xss_clean');
				else  $this->validation->set_rules('cellphone[]', getAlert('mb018'),'trim|max_length[4]|xss_clean');
			}
			if($joinform['address_use']=='Y'){
				if($joinform['address_required']=='Y'){
					if(isset($_POST['new_zipcode'])){
						//우편번호
						$this->validation->set_rules('new_zipcode', getAlert('mb019'),'trim|required|max_length[7]|xss_clean');
					}else{
						$this->validation->set_rules('zipcode[]', getAlert('mb019'),'trim|required|max_length[7]|xss_clean');
					}
					//주소
					$this->validation->set_rules('address', getAlert('mb020'),'trim|required|max_length[100]|xss_clean');
					//상세 주소
					$this->validation->set_rules('address_detail', getAlert('mb021'),'trim|max_length[100]|xss_clean');
				}
			}
			if($joinform['birthday_use']=='Y'){
				//생일
				if($joinform['birthday_required']=='Y') $this->validation->set_rules('birthday', getAlert('mb022'),'trim|required|max_length[10]|xss_clean');
				else  $this->validation->set_rules('birthday', getAlert('mb022'),'trim|max_length[10]|xss_clean');
			}
			if($joinform['anniversary_use']=='Y'){
				//기념일
				if($joinform['anniversary_required']=='Y') $this->validation->set_rules('anniversary', getAlert('mb023'),'trim|required|max_length[5]|xss_clean');
				else  $this->validation->set_rules('anniversary', getAlert('mb023'),'trim|max_length[5]|xss_clean');
			}
			if($joinform['nickname_use']=='Y'){
				//닉네임
				if($joinform['nickname_required']=='Y') $this->validation->set_rules('nickname', getAlert('mb024'),'trim|required|max_length[10]|xss_clean');
				else  $this->validation->set_rules('nickname', getAlert('mb024'),'trim|max_length[10]|xss_clean');
			}
			if($joinform['sex_use']=='Y'){
				//성별
				if($joinform['sex_required']=='Y') $this->validation->set_rules('sex', getAlert('mb025'),'trim|required|max_length[6]|xss_clean');
				else  $this->validation->set_rules('sex', getAlert('mb025'),'trim|max_length[6]|xss_clean');
			}
		}

		### BUSINESS
		if(isset($_POST['mtype']) && $_POST['mtype']=='business'){
			if($joinform['bemail_use']=='Y'){
				if($joinform['bemail_required']=='Y') {
					//이메일
					$this->validation->set_rules('email', getAlert('mb015'),'trim|required|max_length[64]|valid_email|xss_clean');
				}elseif( !empty($_POST['email']) ) {
					$this->validation->set_rules('email', getAlert('mb015'),'trim|max_length[64]|valid_email|xss_clean');
				}
			}


			if($joinform['bname_use']=='Y'){
				//업체명
				if($joinform['bname_required']=='Y') $this->validation->set_rules('bname', getAlert('mb026'),'trim|required|max_length[32]|xss_clean');
				else  $this->validation->set_rules('bname', getAlert('mb026'),'trim|max_length[32]|xss_clean');
			}
			if($joinform['bceo_use']=='Y'){
				//대표자명
				if($joinform['bceo_required']=='Y') $this->validation->set_rules('bceo', getAlert('mb027'),'trim|required|max_length[32]|xss_clean');
				else  $this->validation->set_rules('bceo', getAlert('mb027'),'trim|max_length[32]|xss_clean');
			}
			if($joinform['bno_use']=='Y'){
				//사업자 등록번호
				if($joinform['bno_required']=='Y') $this->validation->set_rules('bno', getAlert('mb028'),'trim|required|max_length[12]|xss_clean');
				else  $this->validation->set_rules('bno', getAlert('mb028'),'trim|max_length[12]|xss_clean');
			}
			if($joinform['bitem_use']=='Y'){
				if($joinform['bitem_required']=='Y') {
					//업태
					$this->validation->set_rules('bitem', getAlert('mb029'),'trim|required|max_length[40]|xss_clean');
					//종목
					$this->validation->set_rules('bstatus', getAlert('mb030'),'trim|required|max_length[40]|xss_clean');
				}
				else{
					//업태
					$this->validation->set_rules('bitem', getAlert('mb029'),'trim|max_length[40]|xss_clean');
					//종목
					$this->validation->set_rules('bstatus', getAlert('mb030'),'trim|max_length[40]|xss_clean');
				}
			}

			if(!isset($_POST['new_bzipcode']) && $_POST['bzipcode']){
				$_POST['new_bzipcode']	= implode('',$_POST['bzipcode']);
				unset($_POST['bzipcode']);
			}

			if($joinform['badress_use']=='Y'){
				if($joinform['badress_required']=='Y'){

					if(isset($_POST['new_bzipcode'])){
						//우편번호
						$this->validation->set_rules('new_bzipcode', getAlert('mb019'),'trim|required|max_length[7]|xss_clean');
					}else{
						$this->validation->set_rules('bzipcode[]', getAlert('mb019'),'trim|required|max_length[3]|xss_clean');
					}
					//주소
					$this->validation->set_rules('baddress', getAlert('mb020'),'trim|required|max_length[100]|xss_clean');
					//상세 주소
					$this->validation->set_rules('baddress_detail', getAlert('mb021'),'trim|max_length[100]|xss_clean');
				}
			}
			if($joinform['bperson_use']=='Y'){
				//담당자 명
				if($joinform['bperson_required']=='Y') $this->validation->set_rules('bperson', getAlert('mb031'),'trim|required|max_length[32]|xss_clean');
				else  $this->validation->set_rules('bperson', getAlert('mb031'),'trim|max_length[32]|xss_clean');
			}
			if($joinform['bpart_use']=='Y'){
				//담당자 부서명
				if($joinform['bpart_required']=='Y') $this->validation->set_rules('bpart', getAlert('mb032'),'trim|required|max_length[32]|xss_clean');
				else  $this->validation->set_rules('bpart', getAlert('mb032'),'trim|max_length[32]|xss_clean');
			}
			if($joinform['bphone_use']=='Y'){
				//전화번호
				if($joinform['bphone_required']=='Y') $this->validation->set_rules('bphone[]', getAlert('mb034'),'trim|required|max_length[4]|numeric|xss_clean');
				else $this->validation->set_rules('bphone[]', getAlert('mb034'),'trim|max_length[4]|xss_clean');
			}

			if($joinform['bcellphone_use']=='Y'){
				//휴대폰번호
				if($joinform['bcellphone_required']=='Y') $this->validation->set_rules('bcellphone[]', getAlert('mb018'),'trim|required|max_length[4]|numeric|xss_clean');
				else  $this->validation->set_rules('bcellphone[]', getAlert('mb018'),'trim|max_length[4]|xss_clean');
			}
		}

		### //넘어온 추가항목 seq
		foreach($label_pr as $l => $data){$label_arr[]=$l;}
		//추가항목 공백체크
		foreach($label_required as $lk => $v){
			if(!in_array($v,$label_arr)){
				if	($label_required_title[$lk])	$msg	= $label_required_title[$lk].getAlert('mb035'); //은 필수입니다.
				else								$msg	= getAlert('mb036'); //체크된 항목은 필수항목입니다.
				$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();".$callback_default;
				openDialogAlert($msg,400,140,'parent',$callback);
				exit;
			}else{
				$query = $this->db->get_where('fm_joinform',array('joinform_seq'=> $v));
				$form_result = $query -> row_array();
				$label_title = $form_result['label_title'];
				$this->validation->set_rules('label['.$v.'][value][]', $label_title,'trim|required|xss_clean');
			}
		}
		###

		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();".$callback_default;
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}



		if(isset($_POST['mtype']) && $_POST['mtype']=='business' && $joinform['bno_use']=='Y'){
			###
			$return_result = $this->bno_chk('re_chk');
			if(!$return_result['return']){
				$callback = "if(parent.document.getElementsByName('bno')[0]) parent.document.getElementsByName('bno')[0].focus();".$callback_default;
				openDialogAlert($return_result['return_result'],400,140,'parent',$callback);
				exit;
			}
		}

		$mix_check = 0;
		//소문자영문체크
		if(preg_match("/[a-z]/",$_POST['password'])){
			$mix_check += 1;
		}

		//대문자영문체크
		if(preg_match("/[A-Z]/",$_POST['password'])){
			$mix_check += 1;
		}

		//숫자체크
		if(preg_match("/[0-9]/",$_POST['password'])){
			$mix_check += 1;
		}

		//특수문자체크
		if(preg_match("/[!#$%^&*()?+=\/]/",$_POST['password'])){
			$mix_check += 1;
		}

		if($mix_check < 2){
			//비밀번호는 6~20자 영문 대소문자, 숫자, 특수문자 중<br> 2가지 이상 조합이어야 합니다.
			$text = getAlert('mb037');
			$callback = "if(parent.document.getElementsByName('password')[0]) parent.document.getElementsByName('password')[0].focus();".$callback_default;
			openDialogAlert($text,400,140,'parent',$callback);
			exit;
		}

		// 아이디 == 패스워드 동일여부 검사 :: 2017-08-11 lwh
		if($_POST['password'] == $_POST['userid']){
			$text = getAlert('mb063');
			$callback = "if(parent.document.getElementsByName('password')[0]) parent.document.getElementsByName('password')[0].focus();".$callback_default;
			openDialogAlert($text,400,140,'parent',$callback);
			exit;
		}

		if($joinform['recommend_use']=='Y'){
			if(trim($_POST['recommend']) == trim($_POST['userid'])){
				$callback = "if(parent.document.getElementsByName('recommend')[0]) parent.document.getElementsByName('recommend')[0].focus();".$callback_default;
				//본인아이디를 추천할 수 없습니다.
				openDialogAlert(getAlert('mb038'),400,140,'parent',$callback);
				exit;
			}

			if($_POST['recommend']){
				$this->db->where('userid', trim($_POST['recommend']));
				$query = $this->db->get("fm_member");
				$mem_chk = $query->result_array();
				if(!$mem_chk){
					$callback = "if(parent.document.getElementsByName('userid')[0]) parent.document.getElementsByName('userid')[0].focus();".$callback_default;
					//존재하지 않는 추천인ID입니다.
					openDialogAlert(getAlert('mb039'),400,140,'parent',$callback);
					exit;
				}
			}
		}
		###
		###
		$return_result = $this->id_chk('re_chk');
		if(!$return_result['return']){
			$callback = "if(parent.document.getElementsByName('userid')[0]) parent.document.getElementsByName('userid')[0].focus();".$callback_default;
			openDialogAlert($return_result['return_result'],400,140,'parent',$callback);
			exit;
		}

		###
		$this->db->where('userid', $_POST['userid']);
		$query = $this->db->get("fm_member");
		$mem_chk = $query->result_array();
		if($mem_chk){
			$callback = "if(parent.document.getElementsByName('userid')[0]) parent.document.getElementsByName('userid')[0].focus();".$callback_default;
			//이미 등록된 아이디 입니다.
			openDialogAlert(getAlert('mb040'),400,140,'parent',$callback);
			exit;
		}

		###
		if(strlen($_POST['password'])<6 || strlen($_POST['password'])>20){
			$callback = "if(parent.document.getElementsByName('required')[0]) parent.document.getElementsByName('required')[0].focus();".$callback_default;
			//비밀번호 글자 제한 수를 맞춰주세요.
			openDialogAlert(getAlert('mb041'),400,140,'parent',$callback);
			exit;
		}
		###
		if($_POST['password'] != $_POST['re_password']){
			$callback = "if(parent.document.getElementsByName('required')[0]) parent.document.getElementsByName('required')[0].focus();".$callback_default;
			//비밀번호 확인이 일치하지 않습니다.
			openDialogAlert(getAlert('mb042'),400,140,'parent',$callback);
			exit;
		}

		###
		$params = $_POST;
		$params['regist_date']	= date('Y-m-d H:i:s');
		$params['lastlogin_date']	= $params['regist_date'];
		$params['group_seq']	= '1';
		if(isset($_POST['phone']))  $params['phone'] = implode("-",$_POST['phone']);
		if(isset($_POST['cellphone']))  $params['cellphone'] = implode("-",$_POST['cellphone']);
		if(isset($_POST['zipcode']))  $params['zipcode'] = implode("",$_POST['zipcode']);
		if(isset($_POST['new_zipcode']))  $params['zipcode'] = $_POST['new_zipcode'];
		$params['password']	= hash('sha256',md5($_POST['password']));
		$params['marketplace'] = !empty($_COOKIE['marketplace']) ? $_COOKIE['marketplace'] : '';//유입매체
		$params['referer']			= $_COOKIE['shopReferer'];
		$params['referer_domain']	= $_COOKIE['refererDomain'];
		$platform	= 'P';
		if		($this->fammerceMode || $this->storefammerceMode)	$platform	= 'F';
		elseif	($this->_is_mobile_app_agent_android)		$platform	= 'APP_ANDROID';
		elseif	($this->_is_mobile_app_agent_ios)		$platform	= 'APP_IOS';
		elseif	($this->mobileMode || $this->storemobileMode)		$platform	= 'M';
		$params['platform']	= $platform;

		###
		$auth = $this->session->userdata('auth');
		if(isset($auth) && $auth['auth_yn']){
			$params['auth_type']	= $auth['namecheck_type'];
			$params['auth_code']	= $auth['namecheck_check'];
			if($params['auth_type'] != "safe"){//"ipin", "phone"

				/* 실명인증 중복 가입 체크 추가 leewh 2014-12-24 */
				$qry = "select count(*) as cnt from fm_member where auth_code='".$auth["namecheck_check"]."'";
				$query = $this->db->query($qry);
				$member = $query -> row_array();

				if($member["cnt"] > 0) {
					$callback = "parent.location.href = '/member/login?return_url=/mypage/myinfo';";
					//이미 가입된정보입니다. 로그인해주세요.
					$msg = getAlert('mb043');
					$this->session->unset_userdata('auth');
					if ($_SESSION['auth']) $_SESSION['auth']= '';
					openDialogAlert($msg,400,140,'parent',$callback);
					exit;
				}

				$params['auth_vno']		= $auth['namecheck_vno'];
			}else{
				$params['auth_vno']		= $auth['namecheck_key'];
			}
		}

		//초대
		$params['fb_invite']	= $this->session->userdata('fb_invite');

		$params['user_icon']	= ($_POST['user_icon'])?$_POST['user_icon']:1;//@2014-08-06 icon

		// 본인인증을 통해 가입했는지 확인 :: 2015-06-04 lwh
		$auth_intro = $this->session->userdata('auth_intro');
		if($auth_intro['auth_intro_yn'] == 'Y'){
			$params['adult_auth']	= 'Y';
		}

		###########################################################################
		## 2018.0.5.11 userapp : api_key 생성
		$params['api_key'] = $this->appmembermodel->create_api_key($_POST['userid']);
		//-->###########################################################################
		$data = filter_keys($params, $this->db->list_fields('fm_member'));
		
		###
		if(isset($_POST['mtype']) && $_POST['mtype']=='business'){
		    if($_POST['gubun'] == '기업회원') {
		        $params['main_dealer_yn'] = 'n';
		    } else if($_POST['gubun'] == '딜러회원') {
    		    $params['main_dealer_yn'] = $_POST['main_dealer_yn'];
		    } 
		    $this->load->library('upload');
		    $files = $_FILES;
		    
		    $upload_path = "./data/uploads/bcard";
		    $filename = 'bcard_file';
		    
		    $this->upload->initialize($this->set_upload_options($upload_path));
		    if($this->upload->do_upload($filename)) {
		        $upload_data = $this->upload->data();
		        $params['bcard_path'] = str_replace(".", "", $upload_path).'/'.$upload_data['file_name'];
		    } else {
		        openDialogAlert('사업자 등록증을 첨부해주세요.',400,140,'parent',$callback_default);
		        exit;
		    }
		    
		    if(isset($_POST['bcategory'])) {
		        if($_POST['bcategory'] == '기타') {
		            if($_POST['bcategory_text'] == '') {
		                openDialogAlert('기타 사항을 입력해주세요.',400,140,'parent',$callback_default);
		                exit;
		            }
		            $params['bcategory'] = $_POST['bcategory_text'];
		        } 
		        else {
		            $params['bcategory'] = $_POST['bcategory'];
		        }
		    }
		    if(isset($_POST['bmain'])) $params['bmain'] = $_POST['bmain'];
		    if($_POST['bcareer'] == '') {
		        openDialogAlert('경력년수를 입력해주세요.',400,140,'parent',$callback_default);
		        exit;
		    }
	        $params['bcareer'] = $_POST['bcareer'];
			if(isset($_POST['bphone']))  $params['bphone'] = implode("-",$_POST['bphone']);
			if(isset($_POST['bcellphone']))  $params['bcellphone'] = implode("-",$_POST['bcellphone']);
			if(isset($_POST['new_bzipcode']))  $params['bzipcode'] = $_POST['new_bzipcode'];
			
			$result = $this->db->insert('fm_member', $data);
			$memberseq = $this->db->insert_id();
			$params['member_seq'] = $memberseq;
			$bdata = filter_keys($params, $this->db->list_fields('fm_member_business'));
			$result = $this->db->insert('fm_member_business', $bdata);
		} else {
			$result = $this->db->insert('fm_member', $data);
			$memberseq = $this->db->insert_id();
			$params['member_seq'] = $memberseq;
		}
		
		### //추가정보 저장
		foreach ($label_pr as $k => $data){
			foreach ($data['value'] as $j => $subdata){
				$setdata['label_value']= $subdata;
				$setdata['label_sub_value']= $label_sub_pr[$k]['value'][$j];
				$query = $this->db->get_where('fm_joinform',array('joinform_seq'=> $k));
				$form_result = $query -> row_array();
				$setdata['label_title'] = $form_result['label_title'];
				$setdata['joinform_seq'] = $form_result['joinform_seq'];
				$setdata['member_seq'] = $memberseq;
				$setdata['regist_date'] = date('Y-m-d H:i:s');
			$result = $this->db->insert('fm_member_subinfo', $setdata);
			}
		}
		###

		### Private Encrypt
		$email = get_encrypt_qry('email');
		$cellphone = get_encrypt_qry('cellphone');
		$phone = get_encrypt_qry('phone');
		$sql = "update fm_member set {$email}, {$cellphone}, {$phone}, update_date = now() where member_seq = {$memberseq}";
		$this->db->query($sql);

		###
		if($result){

			###
			$app = config_load('member');
			$common_msg = array();

			//직접추천시
			$params['recommend']	= $params['recommend'];

			if(($_POST['mtype'] != 'business' && $app['autoApproval']=='Y') || ($_POST['mtype'] == 'business' && $app['autoApproval_biz']=='Y')) {//자동승인인 경우

				$this->load->model('emoneymodel');
				$this->load->model('pointmodel');

				### 특정기간
				if($app['start_date'] && $app['end_date']){
					$today = date("Y-m-d");
					if($today>=$app['start_date'] && $today<=$app['end_date']){
						$app['emoneyJoin']	= $app['emoneyJoin_limit'];
						$app['pointJoin']	= $app['pointJoin_limit'];
					}
				}

				if($app['emoneyJoin']>0){
					$emoney['type']			= 'join';
					$emoney['emoney']		= $app['emoneyJoin'];
					$emoney['gb']			= 'plus';
					$emoney['memo']			= "회원 가입 마일리지";
					$emoney['memo_lang']	= $this->membermodel->make_json_for_getAlert("mp288");    // 회원 가입 마일리지
					$emoney['limit_date']   = get_emoney_limitdate('join');
					$this->membermodel->emoney_insert($emoney, $memberseq);
					//'마일리지 '.$app['emoneyJoin'].'원'
					$common_msg['emoneyJoin'] = getAlert('mb044',get_currency_price($app['emoneyJoin'],2));
				}

				if($app['pointJoin']>0){
					$point['type']			= 'join';
					$point['point']			= $app['pointJoin'];
					$point['gb']			= 'plus';
					$point['memo']			= "회원 가입 포인트";
					$point['memo_lang']		= $this->membermodel->make_json_for_getAlert("mp289");    // 회원 가입 포인트
					$point['limit_date']	= get_point_limitdate('join');
					$this->membermodel->point_insert($point, $memberseq);
					//'포인트 '.$app['emoneyJoin'].'P'
					$common_msg['pointJoin'] = getAlert('mb045',$app['pointJoin']);
				}

				//추천시
				if($params['recommend'] &&  $params['recommend'] != $params['userid']){//본인추천체크
					$chk = get_data("fm_member",array("userid"=>$params['recommend'],"status"=>"done"));
					if(is_array($chk) && $chk[0]['member_seq']) {

						//추천받은자의 추천받은건수 증가 @2013-06-19
						$this->membermodel->member_recommend_cnt($chk[0]['member_seq']);

						//추천 받은 자 -> 제한함
						$todaymonth = date("Y-m");
						if($app['emoneyRecommend']>0) {
							$recommendtosc['whereis'] = ' and type = \'recommend_to\' and gb = \'plus\' and member_seq = \''.$chk[0]['member_seq'].'\'  and regist_date between \''.$todaymonth.'-01 00:00:00\' and \''.$todaymonth.'-31 23:59:59\' ';//
							$recommendtosc['select']	 = ' count(*) as totalcnt, sum(emoney) as totalemoney ';
							$emrecommendtock = $this->emoneymodel->get_data($recommendtosc);//추천한 회원 마일리지 지급여부

							$maxrecommend = ($app['emoneyLimit']*$app['emoneyRecommend']);

							if( $emrecommendtock['totalcnt'] < $app['emoneyLimit'] && $emrecommendtock['totalemoney'] <= $maxrecommend ) {
								$emoney['type']			= 'recommend_to';
								$emoney['emoney']		= $app['emoneyRecommend'];
								$emoney['gb']			= 'plus';
								$emoney['memo']         = '('.$params['userid'].') 추천 회원 마일리지';
								$emoney['memo_lang']	= $this->membermodel->make_json_for_getAlert("mp236",$params['userid']);    // (%s) 추천 회원 마일리지
								$emoney['limit_date']   = get_emoney_limitdate('recomm');
								$emoney['member_seq_to']	= $memberseq;//2015-02-16
								$this->membermodel->emoney_insert($emoney, $chk[0]['member_seq']);
							}
						}

						if($app['pointRecommend']>0) {
							$recommendtosc['whereis'] = ' and type = \'recommend_to\' and gb = \'plus\' and member_seq = \''.$chk[0]['member_seq'].'\'  and regist_date between \''.$todaymonth.'-01 00:00:00\' and \''.$todaymonth.'-31 23:59:59\' ';//
							$recommendtosc['select']	 = ' count(*) as totalcnt, sum(point) as totalepoint ';
							$pmrecommendtock = $this->pointmodel->get_data($recommendtosc);//추천한 회원 마일리지 지급여부
							$maxrecommend = ($app['pointLimit']*$app['pointRecommend']);

							if( $pmrecommendtock['totalcnt'] < $app['pointLimit'] && $pmrecommendtock['totalepoint'] <= $maxrecommend ) {
								$point['type']			= 'recommend_to';
								$point['point']			= $app['pointRecommend'];
								$point['gb']			= 'plus';
								$point['memo']			= '('.$params['userid'].') 추천 회원 포인트';
								$point['memo_lang']		= $this->membermodel->make_json_for_getAlert("mp237",$params['userid']);    // (%s) 추천 회원 포인트
								$point['limit_date']    = get_point_limitdate('recomm');
								$point['member_seq_to']	= $memberseq;//2015-02-16
								$this->membermodel->point_insert($point, $chk[0]['member_seq']);
							}
						}

						//추천한자(가입자)
						if($app['emoneyJoiner']>0) {
							unset($emoney);
							$emoney['type']             = 'recommend_from';
							$emoney['emoney']           = $app['emoneyJoiner'];
							$emoney['gb']               = 'plus';
							$emoney['memo']             = '['.$params['recommend'].'] 추천 마일리지';
							$emoney['memo_lang']        = $this->membermodel->make_json_for_getAlert("mp243",$params['recommend']);    // [%s] 추천 마일리지
							$emoney['limit_date']       = get_emoney_limitdate('joiner');
							$emoney['member_seq_to']    = $chk[0]['member_seq'];//2015-02-16
							$this->membermodel->emoney_insert($emoney, $memberseq);

							//'마일리지 '.$app['emoneyJoiner'].'원'
							$common_msg['emoneyJoiner'] = getAlert('mb044',get_currency_price($app['emoneyJoiner'],3));
						}
						if($app['pointJoiner']>0) {
							unset($point);
							$point['type']				= 'recommend_from';
							$point['point']				= $app['pointJoiner'];
							$point['gb']				= 'plus';
							$point['memo']				= '['.$params['recommend'].'] 추천 포인트';
							$point['memo_lang']			= $this->membermodel->make_json_for_getAlert("mp244",$params['recommend']);    // [%s] 추천 포인트
							$point['limit_date']		= get_point_limitdate('joiner');
							$point['member_seq_to']		= $chk[0]['member_seq'];//2015-02-16
							$this->membermodel->point_insert($point, $memberseq);

							//'포인트 '.$app['pointJoiner'].'P'
							$common_msg['pointJoiner'] = getAlert('mb045',$app['pointJoiner']);
						}
					}
				}

				//초대시
				if($params['fb_invite']) {
					$chk = get_data("fm_member",array("member_seq"=>$params['fb_invite']));
					if($chk[0]['member_seq']) {

						$fbuserprofile = $this->snssocial->facebooklogin();
						if($fbuserprofile['id']){
							$this->db->where('sns_f', $fbuserprofile['id']);
							$result = $this->db->update('fm_memberinvite', array("joinck"=>'1'));//가입여부 업데이트
						}

						//초대 한 자  -> 제한함
						$todaymonth = date("Y-m");
						if($app['emoneyInvited']>0) {
							$invitedtosc['whereis'] = ' and type = \'invite_from\' and gb = \'plus\' and member_seq = \''.$chk[0]['member_seq'].'\'  and regist_date between \''.$todaymonth.'-01 00:00:00\' and \''.$todaymonth.'-31 23:59:59\' ';//
							$invitedtosc['select']	 = ' count(*) as totalcnt, sum(emoney) as totalemoney ';
							$eminvitedtock = $this->emoneymodel->get_data($invitedtosc);//추천한 회원 마일리지 지급여부
							$maxinvited = ($app['emoneyLimit_invited']*$app['emoneyInvited']);

							if( $eminvitedtock['totalcnt'] <= $app['emoneyLimit_invited'] && $eminvitedtock['totalemoney'] <= $maxinvited ) {
								unset($emoney);
								$emoney['type']				= 'invite_from';
								$emoney['emoney']			= $app['emoneyInvited'];
								$emoney['gb']				= 'plus';
								$emoney['memo']				= '초대 마일리지';
								$emoney['memo_lang']		= $this->membermodel->make_json_for_getAlert("mp275"); // 초대 마일리지
								$emoney['limit_date']		= get_emoney_limitdate('invite_from');
								$emoney['member_seq_to']	= $memberseq;//2015-02-16
								$this->membermodel->emoney_insert($emoney, $chk[0]['member_seq']);
							}
						}
						if($app['pointInvited']>0){
							$invitedtosc['whereis'] = ' and type = \'invite_from\' and gb = \'plus\' and member_seq = \''.$chk[0]['member_seq'].'\'  and regist_date between \''.$todaymonth.'-01 00:00:00\' and \''.$todaymonth.'-31 23:59:59\' ';//
							$invitedtosc['select']	 = ' count(*) as totalcnt, sum(point) as totalpoint ';
							$pminvitedtock = $this->pointmodel->get_data($invitedtosc);//추천한 회원 마일리지 지급여부
							$maxinvited = ($app['pointLimit_invited']*$app['pointInvited']);

							if( $pminvitedtock['totalcnt'] <= $app['pointLimit_invited'] && $pminvitedtock['totalpoint'] <= $maxinvited ) {
								unset($point);
								$point['type']				= 'invite_from';
								$point['point']				= $app['pointInvited'];
								$point['gb']				= 'plus';
								$point['memo']				= '초대 포인트';
								$point['memo_lang']			= $this->membermodel->make_json_for_getAlert("mp276"); // 초대 포인트
								$point['limit_date']		= get_point_limitdate('invite_from');
								$point['member_seq_to']		= $memberseq;//2015-02-16
								$this->membermodel->point_insert($point, $chk[0]['member_seq']);
							}
						}

						//초대 받은 자(가입자)
						if($app['emoneyInvitees']>0){
							$emoney['type']			= 'invite_to';
							$emoney['emoney']		= $app['emoneyInvitees'];
							$emoney['gb']			= 'plus';
							$emoney['memo']			= '초대 회원 마일리지';
							$emoney['memo_lang']		= $this->membermodel->make_json_for_getAlert("mp277"); // 초대 회원 마일리지
							$emoney['limit_date']           = get_emoney_limitdate('invite_to');
							$emoney['member_seq_to']	= $chk[0]['member_seq'];//2015-02-16
							$this->membermodel->emoney_insert($emoney, $memberseq);

							//'마일리지 '.$app['emoneyInvitees'].'원'
							$common_msg['emoneyInvitees'] = getAlert('mb044',get_currency_price($app['emoneyInvitees'],3));
						}

						if($app['pointInvitees']>0){
							unset($point);
							$point['type']				= 'invite_to';
							$point['point']				= $app['pointInvitees'];
							$point['gb']				= 'plus';
							$point['memo']				= '초대 회원 포인트';
							$point['memo_lang']			= $this->membermodel->make_json_for_getAlert("mp278"); // 초대 회원 포인트
							$point['limit_date']		= get_point_limitdate('invite_to');
							$point['member_seq_to']		= $chk[0]['member_seq'];//2015-02-16
							$this->membermodel->point_insert($point, $memberseq);

							//'포인트 '.$app['emoneyInvitees'].'P'
							$common_msg['pointInvitees'] = getAlert('mb045',$app['emoneyInvitees']);
						}
					}
				}
			}else{
				$this->db->where('member_seq', $memberseq);
				$result = $this->db->update('fm_member', array("status"=>'hold'));
			}

			// 회원 가입 통계 저장
			$this->load->model('statsmodel');
			$this->statsmodel->insert_member_stats($memberseq,$_POST['birthday'],$_POST['address'],$_POST['sex']);


			//신규회원가입쿠폰발급
			$this->load->model('couponmodel');
			$sc['whereis'] = ' and (type="member" or type="member_shipping")  and issue_stop != 1 ';//발급중지가 아닌경우
			$coupon_multi_list = $this->couponmodel->get_coupon_multi_list($sc);
			$coupon_multicnt = 0;
			foreach($coupon_multi_list as $coupon_multi){  $coupon_multicnt++;
				$this->couponmodel->_members_downlod( $coupon_multi['coupon_seq'], $memberseq);
			}

			//회원가입 쿠폰이 발행 되었습니다.
			if($coupon_multicnt) $common_msg['coupon_msg'] =getAlert('mb046');

			###
			$commonSmsData = array();
			if	($params['mtype'] == 'business' && $params['bcellphone']){
				$commonSmsData['join']['phone'][] = $params['bcellphone'];
				$commonSmsData['join']['params'][] = $params;
				$commonSmsData['join']['mid'][] = $params['userid'];
			}else if($params['cellphone']) {
				$commonSmsData['join']['phone'][] = $params['cellphone'];
				$commonSmsData['join']['params'][] = $params;
				$commonSmsData['join']['mid'][] = $params['userid'];
			}
			if(count($commonSmsData) > 0){
				commonSendSMS($commonSmsData);
			}
			sendMail($params['email'], 'join', $params['userid'], $params);

			$this->session->unset_userdata('fb_invite');//초대회원초기화

			if(isset($_POST['mtype']) && $_POST['mtype']=='business'){
				$params['user_name'] = $params['bname']; // 기업회원일경우 이름 전달
			}

			if(($_POST['mtype'] != 'business' && $app['autoApproval']=='Y') || ($_POST['mtype'] == 'business' && $app['autoApproval_biz']=='Y'))  {//자동승인인 경우

				### LOG
				$qry = "update fm_member set login_cnt = login_cnt+1, lastlogin_date = now(), login_addr = '".$_SERVER['REMOTE_ADDR']."' where member_seq = '{$memberseq}'";
				$result = $this->db->query($qry);

				## 가입된 회원정보 세션용 재검색 :: 2015-01-26 lwh
				$query = "select A.*,B.business_seq,B.bname,C.group_name, D.label_title, D.label_value from fm_member A LEFT JOIN fm_member_business B ON A.member_seq = B.member_seq left join fm_member_group C on C.group_seq=A.group_seq left join fm_member_subinfo D on A.member_seq=D.member_seq where A.member_seq = '".$memberseq."'";
				$query			= $this->db->query($query);
				$member_data	= $query->result_array();

				### 로그인 이벤트
				$this->load->model('joincheckmodel');
				$jcresult = $this->joincheckmodel->login_joincheck($memberseq);

				if( $jcresult['code'] == 'success' ||  $jcresult['code'] == 'emoney_pay' ) {
					$common_msg['jcresult_msg'] = $jcresult['msg'];
				}

				### SESSION
				$params					= $member_data[0];
				$params['member_seq']	= $memberseq;

				// 사용자앱 설치 쿠폰 발행
				// 회원가입 후 자동 로그인 시에도 발급
				if(checkUserApp(getallheaders())){
					$sc['whereis'] = ' and (type="app_install")  and issue_stop != 1 ';//발급중지가 아닌경우
					$coupon_multi_list = $this->couponmodel->get_coupon_multi_list($sc);
					$coupon_multicnt = 0;
					foreach($coupon_multi_list as $coupon_multi){  $coupon_multicnt++;
						$this->couponmodel->_members_downlod( $coupon_multi['coupon_seq'], $memberseq);
					}
				}

				//unset($_POST);
				//unset($params);
				if($_POST['layermode'] == 'layer' ){
					echo js("parent.openjoinokLayer('{$params['user_name']}');");
				}else{
					$params['user_name'] = urlencode($params['user_name']);
					$callback = "parent.location.href = '/member/register_ok'";
					//가입 되었습니다.
					$msg = getAlert('mb047');

					//2016-05-26 jhr 메세지 재정의

					$msg .= '<br />'.$common_msg['coupon_msg'];

					//$msg .= '<br />가입 '.$common_msg['emoneyJoin'].' '.$common_msg['pointJoin'].' 지급되었습니다';
					if	($common_msg['emoneyJoin'])
						$msg .= getAlert('mb048',array($common_msg['emoneyJoin'],$common_msg['pointJoin']));

					//$msg .= '<br />추천 '.$common_msg['emoneyJoiner'].' '.$common_msg['pointJoiner'].' 지급되었습니다';
					if	($common_msg['emoneyJoiner'])
						$msg .= getAlert('mb049',array($common_msg['emoneyJoiner'],$common_msg['pointJoiner']));

					//$msg .= '<br />초대 '.$common_msg['emoneyInvitees'].' '.$common_msg['pointInvitees'].' 지급되었습니다';
					if	($common_msg['emoneyInvitees'])
						$msg .= getAlert('mb050',array($common_msg['emoneyInvitees'],$common_msg['pointInvitees']));

					if	($common_msg['jcresult_msg'])
						$msg .= '<br />'.$common_msg['jcresult_msg'];



					/*######################## 17.12.18 gcs userapp : 앱 처리 s */
					if($this->mobileapp=='Y'){
						$api_key =  $params['api_key'];

						//쿠폰보유건
						/*$this->load->model('couponmodel');
						$sc['today']			= date('Y-m-d',time());
						$dsc['whereis'] = " and member_seq=".$params['member_seq']." and use_status='unused' AND ( (issue_startdate is null  AND issue_enddate is null ) OR (issue_startdate <='".$sc['today']."' AND issue_enddate >='".$sc['today']."') )";//사용가능한
                        $coupondownloadtotal = $this->couponmodel->get_download_total_count($dsc);*/
                        $coupondownloadtotal = 0;

						echo "<script>var param = {member_seq : ".$params['member_seq'].", user_id : '".$params['userid']."', user_name : '".$params['user_name']."', session_id : '".$this->session->userdata('session_id')."', channel : 'none', reserve : '".$params['emoney']."', balance : '".$params['cash']."', coupon : '".$coupondownloadtotal."', auto_login : 'n', api_key : '".$api_key."'}; var strParam = JSON.stringify(param);";

						if ($this->m_device=='iphone') {
							echo "var dataStr = 'MemberInfo' + '?' + strParam;  window.webkit.messageHandlers.CSharp.postMessage(dataStr);</script>";
						}else{

							echo "var dataStr = 'MemberInfo' + '?' + strParam; CSharp.postMessage(dataStr);</script>";
						}
					}
					/*######################## 17.12.18 gcs userapp : 앱 처리 e */




					openDialogAlert($msg,400,190,'parent',$callback);
				}
			}else{
				if($_POST['layermode'] == 'layer' ){
					//echo js("parent.openjoinokLayer('{$params['user_name']}');");
					echo js("parent.location.href = '/main/index';");
				}else{
					$params['user_name'] = urlencode($params['user_name']);
					$callback = "parent.location.href = '/member/register_ok'";
					//가입 되었습니다.
					$msg = getAlert('mb047');

					//2016-05-26 jhr 메세지 재정의
					$msg .= '<br />'.$common_msg['coupon_msg'];

					//$msg .= '<br />가입 '.$common_msg['emoneyJoin'].' '.$common_msg['pointJoin'].' 지급되었습니다';
					if	($common_msg['emoneyJoin'])
						$msg .= getAlert('mb048',array($common_msg['emoneyJoin'],$common_msg['pointJoin']));

					//$msg .= '<br />추천 '.$common_msg['emoneyJoiner'].' '.$common_msg['pointJoiner'].' 지급되었습니다';
					if	($common_msg['emoneyJoiner'])
						$msg .= getAlert('mb049',array($common_msg['emoneyJoiner'],$common_msg['pointJoiner']));

					//$msg .= '<br />초대 '.$common_msg['emoneyInvitees'].' '.$common_msg['pointInvitees'].' 지급되었습니다';
					if	($common_msg['emoneyInvitees'])
						$msg .= getAlert('mb050',array($common_msg['emoneyInvitees'],$common_msg['pointInvitees']));

					openDialogAlert($msg,400,190,'parent',$callback);
				}
			}
		}
	}

	/**
	*
	* @
	*/
	public function create_member_session($data=array()){

		$this->load->helper('member');
		create_member_session($data);
		/**
		$data['rute'] = ($data['rute']!='f' && $data['sns_f'])?'facebook':$data['rute'];

		// 사업자 회원일 경우 업체명->이름
		if($data['business_seq']){
			$data['user_name'] = $data['bname'];
		}
		$member_data = array(
			'member_seq'		=> $data['member_seq'],
			'userid'			=> $data['userid'],
			'user_name'			=> $data['user_name'],
			'birthday'			=> $data['birthday'],
			'sex'				=> $data['sex'],
			'rute'				=> substr($data['rute'],0,1)
		);
		$tmp = config_load('member');
		if(isset($tmp['sessLimit']) && $tmp['sessLimit']=='Y'){
			$limit = 60 * $tmp['sessLimitMin'];
			$this->session->sess_expiration = $limit;
		}
		$this->session->set_userdata(array('user'=>$member_data));
		**/
	}

	### 회원정보 수정 비밀번호 재확인 :: 2016-04-19 lwh
	public function myinfo_pwchk(){
		$this->load->model('ssl');
		$this->ssl->decode();

		//비밀번호를 입력해주세요.
		if(!$_POST['pwchk']) { openDialogAlert(getAlert('mb160'),400,140,'parent',''); exit; }

		$member_config = config_load('member');
		$passwordId = ($member_config['passwordid'])?$member_config['passwordid'] : "";

		$str_password = $_POST['pwchk'];
		$str_md5 = md5($str_password);
		$str_sha	=	hash('sha256',$str_password);
		$str_sha_md5 = hash('sha256',$str_md5);
		$str_sha_password = hash('sha256',$str_password);
		$str_sha_newpassword = hash('sha512', md5($str_password).$passwordId.$this->userInfo['userid']);
		$query = "select count(*) cnt,member_seq from fm_member where member_seq=? and (`password`=? or `password`=? or `password`=? or `password`=? or `password`=? or `password`=?)";
		$query = $this->db->query($query,array($this->userInfo['member_seq'],$str_password,$str_md5,$str_sha,$str_sha_md5,$str_sha_password,$str_sha_newpassword));
		$data = $query->row_array();
		$count = $data['cnt'];

		if($count > 0){
			echo '
			<form method="post" name="form_chk" id="form_chk" action="../mypage/myinfo" target="_parent">
			<input type="hidden" name="pwchk" value="Y">
			<input type="hidden" name="chk_member_seq" value="'.trim($data['member_seq']).'">
			</form>
			<script>document.getElementById("form_chk").submit();</script>
			';
		}else{
			$callback = "parent.document.getElementById('registFrm').reset();";
			//비밀번호가 올바르지 않습니다.
			openDialogAlert(getAlert('mb161'),400,140,'parent',$callback);
			exit;
		}
	}

	### 휴대폰 인증 :: 2016-04-19 lwh
	public function authphone(){
		$this->load->model('membermodel');
		$member		= $this->membermodel->get_member_data($this->userInfo['member_seq']);
		$auth_cnt	= 1;
		$sendresult	= false;

		//잘못된 접근입니다.
		if(!$member['member_seq']) { echo getAlert('mb066'); exit;}

		if($member['phone_auth']){
			$phoneAuth	= explode('|',$member['phone_auth']);
			$auth_cnt	= $phoneAuth[0];
			$auth_date	= $phoneAuth[1];

			if($auth_cnt >= 3 && date('Ymd') == $auth_date){
				//1일 제한횟수를 모두 사용하셨습니다.
				$msg = getAlert('mb067');
				$result = array("result"=>$sendresult, "msg"=>$msg);
				echo json_encode($result);
				exit;
			}

			if(date('Ymd') == $auth_date){
				$auth_cnt = $auth_cnt + 1;
			}else{
				$auth_cnt = 1;
			}
		}

		$phone		= $_GET['phone'];
		$config		= config_load('member','confirmsendmsg');
		$sendMsg	= $config['confirmsendmsg'];
		$authnum	= rand(10000,99999);

		$sendMsg	= str_replace("{shopname}", $this->config_basic['shopName'], $sendMsg);
		$sendMsg	= str_replace("{phonecertify}", $authnum, $sendMsg);

		$params['msg'] = trim($sendMsg);
		$commonSmsData['member']['phone'] = $phone;
		$commonSmsData['member']['params'] = $params;

		$result = commonSendSMS($commonSmsData);
		if($result['code'] == 0000){
			// 발송횟수 저장
			$this->membermodel->set_member_authphone($auth_cnt,$this->userInfo['member_seq']);

			// 인증번호 세션
			$auth_phone = array('authnum'=>$authnum,'phone'=>$phone);
			$this->session->sess_expiration = (60 * 3);
			$this->session->set_userdata('auth_phone',$auth_phone);

			//발송되었습니다. 3분이내 입력하시기바랍니다.
			$msg = getAlert('mb068');
			$sendresult = true;
		}else{
			//발송에 실패하였습니다. 새로고침 후 시도해주세요.
			$msg = getAlert('mb069');
		}

		$result = array("result"=>$sendresult, "msg"=>$msg);
		echo json_encode($result);
	}

	### 휴대폰 인증 세션 삭제 :: 2016-04-25 lwh
	public function authphone_del(){
		$this->session->unset_userdata('auth_phone');
		echo 'ok';
	}

	### 휴대폰 인증 :: 2016-04-19 lwh
	public function authphone_confirm(){
		$auth_phone = $this->session->userdata('auth_phone');
		if(!$auth_phone['authnum']){
			//인증번호 발송 후 입력해주세요.
			echo "<script>alert('".getAlert('mb070')."');</script>";
			exit;
		}
		if(!$_GET['authnum']){
			//인증번호를 입력해주세요.
			echo "<script>alert('".getAlert('mb071')."');</script>";
			exit;
		}
		if($auth_phone['authnum'] == $_GET['authnum']){
			$this->session->unset_userdata('auth_phone');
			echo '<script type="text/javascript" src="/app/javascript/jquery/jquery.min.js"></script>';
			//인증되었습니다.
			echo '<script>
				var phone = "";
				var ptype = $("#phonetype",parent.document).val();
				$.each($("input[name=\'chg_phone[]\']",parent.document),function(){ phone += $(this).val(); });
				if(phone == "'.$auth_phone['phone'].'"){
					alert("'.getAlert('mb072').'");
					$.each($("input[name=\'chg_phone[]\']",parent.document),function(idx){
						$("input[name=\'"+ptype+"[]\']",parent.document).eq(idx).val($(this).val());
					});
					parent.closeDialog(\'authphone\');
					$(".chg_phone",parent.document).attr(\'disabled\',false);
				}
			</script>';
		}else{
			//인증번호가 일치하지 않습니다.
			echo '<script>alert("'.getAlert('mb073').'");</script>';
		}
	}


	###
	public function myinfo_modify(){

		$this->load->model('ssl');
		$this->ssl->decode();

		if($_POST['seq']!=$this->userInfo['member_seq']){
			 $returnMsg = getAlert('et018');//잘못된 접근입니다.
			openDialogAlert($returnMsg,400,140,'parent',$callback);
			exit;
		}

		$this->mdata = $this->membermodel->get_member_data($this->userInfo['member_seq']);//회원정보


		if( $this->isdemo['isdemo'] && $this->mdata['userid'] == $this->isdemo['isdemoid'] ){
			openDialogAlert($this->isdemo['msg'],500,140,'parent',$callback);
			exit;
		}

		$joinform = config_load('joinform');
		###
		
	   if(isset($_POST['change'])) $change = $_POST['change'];
		
		$mtype = 'member';
		if($this->mdata['business_seq'] || $change == 'change'){
			$mtype = 'business';
		}

		$label_pr = $_POST['label'];
		$label_sub_pr = $_POST['labelsub'];
		$label_required = $_POST['required'];

		### Validation
		if( $mtype == 'member' ) {
			//$this->validation->set_rules('user_name', '이름','trim|required|max_length[32]|xss_clean');
		}

		if( $this->mdata['rute'] == 'none' ) {
			//비밀번호
			$this->validation->set_rules('old_password', getAlert('mb012'),'trim|required|max_length[32]|xss_clean');
		}
		if(!empty($_POST['anniversary'][0]) && !empty($_POST['anniversary'][1]))
			$_POST['anniversary'] = implode("-",$_POST['anniversary']);
		else
			$_POST['anniversary'] = '';

		if(isset($_POST['email'])) $_POST['email'] = implode("@",$_POST['email']);
		if($_POST['email'] == '@' ) $_POST['email'] = '';

		if ( $joinform['email_userid'] == 'Y' && !$_POST['email'] ) {
			$_POST['email'] = $_POST['userid'];
		}

		if( is_array($_POST['births']) ) {
			if( $_POST['births'][0] && $_POST['births'][1] && $_POST['births'][2]) {
				$_POST['birthday'] =  $_POST['births'][0].'-'.str_pad($_POST['births'][1],2 ,"0", STR_PAD_LEFT).'-'.str_pad($_POST['births'][2],2 ,"0", STR_PAD_LEFT);
			}
		}else{
			if($_POST['births']){
				$_POST['birthday'] = $_POST['births'];
			}else{
				$_POST['birthday'] = $_POST['birthday'] ? $_POST['birthday'] : '';
			}
		}

		if(isset($_POST['zipcode']) && !isset($_POST['new_zipcode'])){
			$_POST['new_zipcode'] = implode('',$_POST['zipcode']);
			unset($_POST['zipcode']);

		}
		if(isset($_POST['bzipcode']) && !isset($_POST['new_bzipcode'])){
			$_POST['new_bzipcode'] = implode('',$_POST['bzipcode']);
			unset($_POST['bzipcode']);
		}

		### MEMBER
		if($mtype=='member'){

			if($joinform['email_use']=='Y'){
				if($joinform['email_required']=='Y') {
					//이메일
					$this->validation->set_rules('email', getAlert('mb015'),'trim|required|max_length[64]|valid_email|xss_clean');
				}elseif( !empty($_POST['email'])) {
					$this->validation->set_rules('email', getAlert('mb015'),'trim|max_length[64]|valid_email|xss_clean');
				}
			}

			if($joinform['user_name_use']=='Y'){
				//이름
				if($joinform['user_name_required']=='Y') $this->validation->set_rules('user_name', getAlert('mb016'),'trim|required|max_length[32]|xss_clean');
				else $this->validation->set_rules('user_name', getAlert('mb016'),'trim|max_length[32]|xss_clean');
			}
			if($joinform['phone_use']=='Y'){
				//연락처
				if($joinform['phone_required']=='Y') $this->validation->set_rules('phone[]', getAlert('mb017'),'trim|required|max_length[4]|numeric|xss_clean');
				else $this->validation->set_rules('phone[]', getAlert('mb017'),'trim|max_length[4]|xss_clean');
			}
			if($joinform['cellphone_use']=='Y'){
				//휴대폰번호
				if($joinform['cellphone_required']=='Y') $this->validation->set_rules('cellphone[]', getAlert('mb018'),'trim|required|max_length[4]|numeric|xss_clean');
				else  $this->validation->set_rules('cellphone[]', getAlert('mb018'),'trim|max_length[4]|xss_clean');
			}
			if($joinform['address_use']=='Y'){
				if($joinform['address_required']=='Y'){
					//우편번호
					$this->validation->set_rules('new_zipcode', getAlert('mb019'),'trim|required|max_length[7]|xss_clean');
					//주소
					$this->validation->set_rules('address', getAlert('mb020'),'trim|required|max_length[100]|xss_clean');
					//상세 주소
					$this->validation->set_rules('address_detail', getAlert('mb021'),'trim|max_length[100]|xss_clean');
				}
				else{
					$this->validation->set_rules('new_zipcode', getAlert('mb019'),'trim|max_length[7]|xss_clean');
					$this->validation->set_rules('address', getAlert('mb020'),'trim|max_length[100]|xss_clean');
					$this->validation->set_rules('address_detail', getAlert('mb021'),'trim|max_length[100]|xss_clean');
				}
			}
			if($joinform['birthday_use']=='Y'){
				//생일
				if($joinform['birthday_required']=='Y') $this->validation->set_rules('birthday', getAlert('mb022'),'trim|required|max_length[10]|xss_clean');
				else  $this->validation->set_rules('birthday', getAlert('mb022'),'trim|max_length[10]|xss_clean');
			}
			if($joinform['anniversary_use']=='Y'){
				//기념일
				if($joinform['anniversary_required']=='Y') $this->validation->set_rules('anniversary', getAlert('mb023'),'trim|required|max_length[5]|xss_clean');
				else  $this->validation->set_rules('anniversary', getAlert('mb023'),'trim|max_length[5]|xss_clean');
			}
			if($joinform['nickname_use']=='Y'){
				//닉네임
				if($joinform['nickname_required']=='Y') $this->validation->set_rules('nickname', getAlert('mb024'),'trim|required|max_length[10]|xss_clean');
				else  $this->validation->set_rules('nickname', getAlert('mb024'),'trim|max_length[10]|xss_clean');
			}
			if($joinform['sex_use']=='Y'){
				//성별
				if($joinform['sex_required']=='Y') $this->validation->set_rules('sex', getAlert('mb025'),'trim|required|max_length[6]|xss_clean');
				else  $this->validation->set_rules('sex', getAlert('mb025'),'trim|max_length[6]|xss_clean');
			}
		}

		### BUSINESS
		if($mtype=='business'){
			if($joinform['bemail_use']=='Y'){
				if($joinform['bemail_required']=='Y') {
					//이메일
					$this->validation->set_rules('email', getAlert('mb015'),'trim|required|max_length[64]|valid_email|xss_clean');
				}elseif( !empty($_POST['email']) ) {
					$this->validation->set_rules('email', getAlert('mb015'),'trim|max_length[64]|valid_email|xss_clean');
				}
			}

			if($joinform['bname_use']=='Y'){
				//업체명
				if($joinform['bname_required']=='Y') $this->validation->set_rules('bname', getAlert('mb026'),'trim|required|max_length[32]|xss_clean');
				else  $this->validation->set_rules('bname', getAlert('mb026'),'trim|max_length[32]|xss_clean');
			}
			if($joinform['bceo_use']=='Y'){
				//대표자명
				if($joinform['bceo_required']=='Y') $this->validation->set_rules('bceo', getAlert('mb027'),'trim|required|max_length[32]|xss_clean');
				else  $this->validation->set_rules('bceo', getAlert('mb027'),'trim|max_length[32]|xss_clean');
			}

			if($joinform['bno_use']=='Y'){
				//사업자 등록번호
				if($joinform['bno_required']=='Y') $this->validation->set_rules('bno', getAlert('mb028'),'trim|required|max_length[12]|xss_clean');
				else  $this->validation->set_rules('bno', getAlert('mb028'),'trim|max_length[12]|xss_clean');
			}
			if($joinform['bitem_use']=='Y'){
				if($joinform['bitem_required']=='Y') {
					//업태
					$this->validation->set_rules('bitem', getAlert('mb029'),'trim|required|max_length[40]|xss_clean');
					//종목
					$this->validation->set_rules('bstatus', getAlert('mb030'),'trim|required|max_length[40]|xss_clean');
				}
				else{
					$this->validation->set_rules('bitem', getAlert('mb029'),'trim|max_length[40]|xss_clean');
					$this->validation->set_rules('bstatus', getAlert('mb030'),'trim|max_length[40]|xss_clean');
				}
			}
			if($joinform['badress_use']=='Y'){
				if($joinform['badress_required']=='Y'){
					//우편번호
					$this->validation->set_rules('new_bzipcode', getAlert('mb019'),'trim|required|max_length[7]|xss_clean');
					//주소
					$this->validation->set_rules('baddress', getAlert('mb020'),'trim|required|max_length[100]|xss_clean');
					//상세 주소
					$this->validation->set_rules('baddress_detail', getAlert('mb021'),'trim|max_length[100]|xss_clean');
				}
				else{
					$this->validation->set_rules('new_bzipcode', getAlert('mb019'),'trim|max_length[7]|xss_clean');
					$this->validation->set_rules('baddress', getAlert('mb020'),'trim|max_length[100]|xss_clean');
					$this->validation->set_rules('baddress_detail', getAlert('mb021'),'trim|max_length[100]|xss_clean');
				}
			}
			if($joinform['bperson_use']=='Y'){
				//담당자 명
				if($joinform['bperson_required']=='Y') $this->validation->set_rules('bperson', getAlert('mb031'),'trim|required|max_length[32]|xss_clean');
				else  $this->validation->set_rules('bperson', getAlert('mb031'),'trim|max_length[32]|xss_clean');
			}
			if($joinform['bpart_use']=='Y'){
				//담당자 부서명
				if($joinform['bpart_required']=='Y') $this->validation->set_rules('bpart', getAlert('mb033'),'trim|required|max_length[32]|xss_clean');
				else  $this->validation->set_rules('bpart', getAlert('mb033'),'trim|max_length[32]|xss_clean');
			}
			if($joinform['bphone_use']=='Y'){
				//전화번호
				if($joinform['bphone_required']=='Y') $this->validation->set_rules('bphone[]', getAlert('mb034'),'trim|required|max_length[4]|numeric|xss_clean');
				else $this->validation->set_rules('bphone[]', getAlert('mb034'),'trim|max_length[4]|xss_clean');
			}
			if($joinform['bcellphone_use']=='Y'){
				//휴대폰번호
				if($joinform['bcellphone_required']=='Y') $this->validation->set_rules('bcellphone[]', getAlert('mb018'),'trim|required|max_length[4]|numeric|xss_clean');
				else  $this->validation->set_rules('bcellphone[]', getAlert('mb018'),'trim|max_length[4]|xss_clean');
			}
		}

		//넘어온 추가항목 seq
		foreach($label_pr as $l => $data){$label_arr[]=$l;}
		//추가항목 공백체크
		foreach($label_required as $v){
			if(!in_array($v,$label_arr)){
				$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
				//체크된 항목은 필수항목입니다.
				openDialogAlert(getAlert('mb074'),400,140,'parent',$callback);
				exit;
			}else{
				$query = $this->db->get_where('fm_joinform',array('joinform_seq'=> $v));
				$form_result = $query -> row_array();
				$label_title = $form_result['label_title'];
				$this->validation->set_rules('label['.$v.'][value][]', $label_title,'trim|required|xss_clean');
			}
		}

		if($this->validation->exec()===false){
			$member_config = config_load('member');
			if ( $member_config['confirmPhone'] ) {
				// 본인인증 휴대폰번호 수정 :: 2016-04-19 pjw
				echo '<script type="text/javascript" src="/app/javascript/jquery/jquery.min.js"></script>';
				echo "<script>$(\"input[name='bcellphone[]']\",parent.document).attr(\"disabled\",true);
				$(\"input[name='cellphone[]']\",parent.document).attr(\"disabled\",true);</script>";
			}

			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		if($mtype=='business' && $joinform['bno_use']=='Y'){
			###
			$return_result = $this->bno_chk('re_chk');
			if(!$return_result['return'] && $this->mdata['bno'] != $_POST['bno']){
				$callback = "if(parent.document.getElementsByName('bno')[0]) parent.document.getElementsByName('bno')[0].focus();";
				openDialogAlert($return_result['return_result'],400,140,'parent',$callback);
				exit;
			}
		}


		###
		$params = $_POST;
		$seq	= $_POST['seq'];
		if( $_POST['rute'] == 'none' ) {

			$query = "select password(?) pass";
			$query = $this->db->query($query,array($_POST['old_password']));
			$data = $query->row_array();

			$member_config = config_load('member');
			$passwordId = ($member_config['passwordid'])?$member_config['passwordid'] : "";

			$str_md5 = md5($_POST['old_password']);
			$str_sha	=	hash('sha256',$_POST['old_password']);
			$str_password = $data['pass'];
			// $str_oldpassword = $data['old_pass'];
			$str_sha_md5 = hash('sha256',$str_md5);
			$str_sha_password = hash('sha256',$data['pass']);
			// $str_sha_oldpassword = hash('sha256',$data['old_pass']);
			$str_sha_newpassword = hash('sha512', md5($_POST['old_password']).$passwordId.$this->userInfo['userid']);
			$query = "select count(*) cnt from fm_member where member_seq=? and (`password`=? or `password`=? or `password`=? or `password`=? or `password`=? or `password`=? or `password`=?)";
			$query = $this->db->query($query,array($seq,$str_md5,$str_sha,$str_password,$str_oldpassword,$str_sha_md5,$str_sha_password,$str_sha_newpassword));

			$data = $query->row_array();
			$count = $data['cnt'];

			if($count<1){
				$callback = "if(parent.document.getElementsByName('old_password')[0]) parent.document.getElementsByName('old_password')[0].focus();";
				//기존 비밀번호가 올바르지 않습니다.
				openDialogAlert(getAlert('mb075'),400,140,'parent',$callback);
				exit;
			}
		}
		###
		if(isset($_POST['new_password']) && $_POST['new_password']){
			if(strlen($_POST['new_password'])<6 || strlen($_POST['new_password'])>20){
				$callback = "if(parent.document.getElementsByName('required')[0]) parent.document.getElementsByName('required')[0].focus();";
				//비밀번호 글자 제한 수를 맞춰주세요.
				openDialogAlert(getAlert('mb076'),400,140,'parent',$callback);
				exit;
			}
		}

		$mix_check = 0;
		//소문자영문체크
		if(preg_match("/[a-z]/",$_POST['new_password'])){
			$mix_check += 1;
		}

		//대문자영문체크
		if(preg_match("/[A-Z]/",$_POST['new_password'])){
			$mix_check += 1;
		}

		//숫자체크
		if(preg_match("/[0-9]/",$_POST['new_password'])){
			$mix_check += 1;
		}

		//특수문자체크
		if(preg_match("/[!#$%^&*()?+=\/]/",$_POST['new_password'])){
			$mix_check += 1;
		}

		if($mix_check < 2 && $_POST['new_password']){
			//비밀번호는 6~20자 영문 대소문자, 숫자, 특수문자 중<br> 2가지 이상 조합이어야 합니다.
			$text = getAlert('mb077');
			$callback = "if(parent.document.getElementsByName('new_password')[0]) parent.document.getElementsByName('new_password')[0].focus();".$callback_default;
			openDialogAlert($text,400,140,'parent',$callback);
			exit;
		}

		if(isset($_POST['phone'])) {
			$_POST['phone'] = array_filter($_POST['phone']);
			$params['phone'] = implode("-",$_POST['phone']);
		}
		if(isset($_POST['cellphone'])) {
			$_POST['cellphone'] = array_filter($_POST['cellphone']);
			$params['cellphone'] = implode("-",$_POST['cellphone']);
		}
		if(isset($_POST['new_zipcode']))  $params['zipcode'] = $_POST['new_zipcode'];
		if(isset($_POST['new_password']) && $_POST['new_password'])  $params['password'] = hash('sha256',md5($_POST['new_password']));
		$params['mailing'] = if_empty($params, 'mailing', 'n');
		$params['sms'] = if_empty($params, 'sms', 'n');

		$params['user_icon']	= if_empty($params, 'user_icon', '1');;//@2014-08-06 icon
		$data = filter_keys($params, $this->db->list_fields('fm_member'));
		//print_r($data);

		### BUSINESS CHK
		if($mtype=='business') {
		    if($_POST['gubun'] == '기업회원') {
		        $params['main_dealer_yn'] = 'n';
		    } else if($_POST['gubun'] == '딜러회원') {
		        if($_POST['main_dealer_yn'] != 'n')
		          $params['main_dealer_yn'] = $_POST['main_dealer_yn'];
		    } 
		    
		    if(isset($_POST['bpermit_yn'])) $bpermit_yn = $_POST['bpermit_yn'];
		    if($bpermit_yn != 'y') {
		        $this->load->library('upload');
		        $files = $_FILES;
		        
		        $upload_path = "./data/uploads/bcard";
		        $filename = 'bcard_file';
		        
		        $this->upload->initialize($this->set_upload_options($upload_path));
		        
		        if($this->upload->do_upload($filename)) {
		            $upload_data = $this->upload->data();
		            $params['bcard_path'] = str_replace(".", "", $upload_path).'/'.$upload_data['file_name'];
		        } else if($this->mdata['bpermit_yn'] == 'n'){
		            openDialogAlert('사업자 등록증을 첨부해주세요.',400,140,'parent',$callback_default);
		            exit;
		        }
		    }
			if(isset($_POST['bphone']))		$params['bphone']		= implode("-",$_POST['bphone']);
			if(isset($_POST['bcellphone']))	$params['bcellphone']	= implode("-",$_POST['bcellphone']);
			if(isset($_POST['new_bzipcode']))  $params['bzipcode'] = $_POST['new_bzipcode'];
			$params['baddress_type']	= $_POST['baddress_type'];
			$params['baddress']			= $_POST['baddress'];
			$params['baddress_street']	= $_POST['baddress_street'];
			
			$result = $this->db->update('fm_member',$data,array('member_seq'=>$seq));
			
			$data = filter_keys($params, $this->db->list_fields('fm_member_business'));
			if($this->mdata['business_seq']){
				$this->db->where('business_seq', $this->mdata['business_seq']);
				$result = $this->db->update('fm_member_business', $data);
			}else{
				$data['member_seq'] = $seq;
				$result = $this->db->insert('fm_member_business', $data);
			}
		}

		### //추가정보 저장
		if($label_pr){
			$this->db->delete('fm_member_subinfo', array('member_seq'=>$seq));
			foreach ($label_pr as $k => $data){
				foreach ($data['value'] as $j => $subdata){
					$setdata['label_value']= $subdata;
					$setdata['label_sub_value']= $label_sub_pr[$k]['value'][$j];
					$query = $this->db->get_where('fm_joinform',array('joinform_seq'=> $k));
					$form_result = $query -> row_array();
					$setdata['label_title'] = $form_result['label_title'];
					$setdata['joinform_seq'] = $form_result['joinform_seq'];
					$setdata['member_seq'] = $seq;
					$setdata['regist_date'] = date('Y-m-d H:i:s');
				$result = $this->db->insert('fm_member_subinfo', $setdata);
				}
			}
		}
		###

		###
		// 회원정보 수정 시 이메일, 휴대폰, 연락처 빈값일 때 저장하지 않도록 수정 2016-11-14 by rhm
		$update_qry = array();
		$sql		= "select email,cellphone,phone from fm_member where member_seq='".$seq."'";
		$query = $this->db->query($sql);
		$minfo = $query->row_array();		// 회원정보

		$update_qry[] = ( $minfo['email'] ) ? get_encrypt_qry('email') : "email=''";
		$update_qry[] = ( $minfo['cellphone'] ) ? get_encrypt_qry('cellphone') : "cellphone=''";
		$update_qry[] = ( $minfo['phone'] ) ? get_encrypt_qry('phone') : "phone=''";
		$update_qry = implode(',', $update_qry);

		$sql = "update fm_member set update_date = now(),{$update_qry} where member_seq = {$seq}";
		$result = $this->db->query($sql);

		###
		if($result){
			unset($_POST);

			/*######################## 18.03.12 gcs userapp : 앱 처리 (정보 수정) s */

			if($this->mobileapp=='Y'){
			    $send_params = $this->appmembermodel->memberInfo();
				if($_COOKIE['auto_login']=='y' || $this->session->userdata('auto_login') =='y') { //동일페이지에서 쿠키가 바로 구워지지 않은 경우 고려해서 세션값도 조건으로 처리
					$auto_login = 'y';
				}else{
					$auto_login = 'n';
				}

				echo "<script>
					var param = {
										   member_seq : ".$send_params['member_seq'].",
										   user_id : '".$send_params['user_id']."',
										   user_name : '".$send_params['user_name']."',
										   session_id : '".session_id()."',
										   channel : '".$send_params['channel']."',
										   reserve : '".$send_params['reserve']."',
										   balance : '".$send_params['balance']."',
										   coupon : '".$send_params['coupon']."',
										   auto_login : '".$auto_login."',
										   api_key : '".$send_params['api_key']."'
									  };
					var strParam = JSON.stringify(param);

					var dataStr = 'MemberInfo?' + strParam; ";

				if($this->m_device=='iphone') {
					echo "window.webkit.messageHandlers.CSharp.postMessage(dataStr);";
				}else{
					echo "CSharp.postMessage(dataStr);";
				}
				echo "</script>";
			}

			/*######################## 18.03.12 gcs userapp : 앱 처리 (정보 수정) e */

			if($change == 'change') {
			    $data = array(
			        'member_seq' => $seq,
			        'joinform_seq' => 2,
			        'label_title' => '회원구분',
			        'label_value' => '기업회원'
			    );
			    $this->db->insert('fm_member_subinfo', $data);
    			$callback = "parent.location.href = '/user/my_info_modify/change'";
    			openDialogAlert('기업회원 전환 신청이 완료되었습니다.',400,140,'parent',$callback);
			}
			else {
    			$callback = "parent.location.href = '/user/my_info_modify'";
    			//수정 되었습니다.
    			openDialogAlert(getAlert('mb078'),400,140,'parent',$callback);
			}
		}
	}


	###
	public function withdrawal(){
		//탈퇴사유
		$this->validation->set_rules('reason', getAlert('mb196'),'trim|required|max_length[30]|xss_clean');
		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		###
		$data = $this->membermodel->get_member_data($this->userInfo['member_seq']);

		if( $this->isdemo['isdemo'] && $data['userid'] == $this->isdemo['isdemoid'] ){
			openDialogAlert($this->isdemo['msg'],500,140,'parent',$callback);
			exit;
		}

		### withdrawal_insert
		$params = $_POST;
		$params['member_seq']	= $this->userInfo['member_seq'];
		$params['regist_date']	= date('Y-m-d H:i:s');
		$params['regist_ip']	= $_SERVER['REMOTE_ADDR'];
		$params['user_name']	= $this->userInfo['user_name'];
		$result = $this->membermodel->set_withdrawal_admin($params);//탈퇴

		###
		$commonSmsData = array();
		$commonSmsData['withdrawal']['phone'][] = $data['cellphone'];
		$commonSmsData['withdrawal']['params'][] = $params;
		$commonSmsData['withdrawal']['mid'][] = $data['userid'];
		commonSendSMS($commonSmsData);
		sendMail($data['email'], 'withdrawal', $data['userid'], $params);


		### logout
		$callback = "parent.location.href = '../login_process/logout'";
		//정상적으로 회원 탈퇴가 이뤄졌습니다.<br>\\n그 동안 이용해 주셔서 감사합니다.
		openDialogAlert(getAlert('mb197'),400,140,'parent',$callback);
	}

/**
** 본인인증/안심체크/아이핀 실명인증 체크 관련
**/
	public function niceid2_return(){

		$realname = config_load('realname');
		$auth = $this->session->userdata('auth');
		$findtypess = $this->session->userdata('findtypess');
		$findidss = $this->session->userdata('findidss');

		if(!extension_loaded('CPClient')) {
			dl('CPClient.' . PHP_SHLIB_SUFFIX);
		}
		$module = 'CPClient';


		//**************************************** 필수 수정값 ***************************************************************************
		$sSiteCode 	   = $realname['realnameId'];							// 안심체크 사이트 코드
		$sSitePassword = $realname['realnamePwd'];							// 안심체크 사이트 패스워드
		$sIPINSiteCode = $realname['ipinSikey'];							// 아이핀사이트 코드
		$sIPINPassword = $realname['ipinKeyString'];						// 아이핀사이트 패스워드
		//$sReturnURL = $_SERVER["HTTP_HOST"]."/member/niceid2_return";		//결과 수신 : full URL 입력
		$cb_encode_path = $_SERVER["DOCUMENT_ROOT"]."/namecheck/CPClient";	// 암호화 프로그램의 위치 (절대경로+모듈명)_Linux ..

		//*************************************************************************8******************************************************

		$enc_data = $_POST["enc_data"];								// NICE신용평가정보로부터 받은 사용자 암호화된 결과 데이타

		///////////////////////////////////////////////// 문자열 점검///////////////////////////////////////////////
		if(preg_match('~[^0-9a-zA-Z+/=]~', $enc_data, $match)) {echo "입력 값 확인이 필요합니다"; exit;}
		if(base64_encode(base64_decode($enc_data))!=$enc_data) {echo "입력 값 확인이 필요합니다"; exit;}
		///////////////////////////////////////////////////////////////////////////////////////////////////////////

		if ($enc_data != "") {

			$function = 'get_decode_data';
			if (extension_loaded($module)) {
				$plaindata = $function($sSiteCode,$sSitePassword, $enc_data);
			} else {
				$plaindata = "Module get_response_data is not compiled into PHP";
			}

			if ($plaindata == -1){
				//암/복호화 시스템 오류
				$returnMsg  = getAlert('mb166');
			}else if ($plaindata == -4){
				//복호화 처리 오류
				$returnMsg  = getAlert('mb167');
			}else if ($plaindata == -5){
				//HASH값 불일치 - 복호화 데이터는 리턴됨
				$returnMsg  = getAlert('mb168');
			}else if ($plaindata == -6){
				//복호화 데이터 오류
				$returnMsg  = getAlert('mb169');
			}else if ($plaindata == -9){
				//입력값 오류
				$returnMsg  = getAlert('mb170');
			}else if ($plaindata == -12){
				//사이트 비밀번호 오류
				$returnMsg  = getAlert('mb173');
			}else{

				// 복호화가 정상적일 경우 데이터를 파싱합니다.
				//본인인증이 확인되었습니다.
				$returnMsg  = getAlert('mb174');

				$sRequestNO = GetValueNameCheck($plaindata , "REQ_SEQ");
				$sResult = GetValueNameCheck($plaindata , "NC_RESULT");

				if(strcmp($_SESSION["REQ_SEQ"] , $sRequestNO) )
				{
					$sRequestNO = "";
					//세션값이 다릅니다. 올바른 경로로 접근하시기 바랍니다.
					$err_msg = getAlert('mb175');
					pageClose($err_msg);
					exit;
				}else{

					$auth_data["auth_yn"] = "Y";
					$auth_data["namecheck_type"] = "safe";
					$auth_data["namecheck_name"] = iconv("euc-kr", "utf-8", GetValueNameCheck($plaindata , "NAME"));
					$auth_data["namecheck_sex"] = iconv("euc-kr", "utf-8", GetValueNameCheck($plaindata , "GENDER"));
					$auth_data["namecheck_birth"] = iconv("euc-kr", "utf-8", GetValueNameCheck($plaindata , "BIRTHDATE"));
					$auth_data["namecheck_key"] = iconv("euc-kr", "utf-8", GetValueNameCheck($plaindata , "SAFEID"));
					$auth_data["namecheck_check"] = iconv("euc-kr", "utf-8", GetValueNameCheck($plaindata , "IPIN_DI"));
					$auth_data["namecheck_vno"] = iconv("euc-kr", "utf-8", GetValueNameCheck($plaindata , "VNO_NUM"));


					if(isset($_GET['intro']) && $_GET['intro']=='Y') {//성인인증페이지
						if($auth_data["namecheck_birth"]){
							$adult = date("Y") - substr($auth_data["namecheck_birth"], 0, 4) + 1;
						}
						if($adult>19){
							$auth_intro_data = array('auth_intro_type'=>'auth', 'auth_intro_yn'=>'Y');
							$this->session->sess_expiration = (60 * 5);
							$this->session->set_userdata(array('auth_intro'=>$auth_intro_data));
							// 성인인증 로그 :: 2015-03-13 lwh
							$this->adult_log('namecheck');
							//"성인인증이 성공적으로 완료되었습니다."
							$msg = getAlert('mb083');

							if($_GET['type']=='join'){
								$qry = "select count(*) as cnt from fm_member where auth_code='".$auth_data["namecheck_check"]."'";
								$query = $this->db->query($qry);
								$member = $query -> row_array();

								if($member["cnt"] > 0){
									$url = "/member/login?return_url=" . urlencode("/mypage/myinfo");
									//이미 가입된 정보입니다.
									$msg = getAlert('mb176');
									pageLocation($url, $msg, 'opener');
									pageClose();
									exit;
								}

								$this->session->sess_expiration = (60 * 5);
								$this->session->set_userdata(array('auth'=>$auth_data));
								$_GET['return_url'] = '/member/agreement?authok=1';
							}
						}else{
							//미성년자는 이용할 수 없습니다.
							$err_msg = getAlert('mb177');
							pageClose($err_msg);
							exit;
						}

						$return_url = ($_GET['return_url']) ? $_GET['return_url'] : '/main';
						pageLocation($return_url, $msg, 'opener');
						pageClose();
						exit;

					}elseif(isset($_GET['findidpw']) && $_GET['findidpw']=='Y') {//아이디/패스워드 찾기
						$this->_findidpwresult($auth_data, $plaindata);
					}else{//가입페이지

						$qry = "select count(*) as cnt from fm_member where auth_code='".$auth_data["namecheck_check"]."'";
						$query = $this->db->query($qry);
						$member = $query -> row_array();

						if($member["cnt"] > 0){
							$url = "/member/login?return_url=" . urlencode("/mypage/myinfo");
							//이미 가입된 정보입니다.
							$msg = getAlert('mb176');
							pageLocation($url, $msg, 'opener');
							pageClose();
							exit;
						}


						$this->session->sess_expiration = (60 * 5);
						$this->session->set_userdata(array('auth'=>$auth_data));

						pageLocation('/member/agreement?authok=1', "", 'opener');
						pageClose();
						exit;
					}
				}
			}

			//"잠시 후 다시 시도하여주십시오.<br/>오류가 계속 될 경우 고객센터로 문의하세요."
			$msg = getAlert('mb178');
			pageClose($msg);
			exit;
		}
	}

	public function niceid_phone_return(){

		$realname = config_load('realname');
		$auth = $this->session->userdata('auth');
		$findtypess = $this->session->userdata('findtypess');
		$findidss = $this->session->userdata('findidss');

		if(!extension_loaded('CPClient')) {
			dl('CPClient.' . PHP_SHLIB_SUFFIX);
		}
		$module = 'CPClient';


		//**************************************** 필수 수정값 ***************************************************************************
		$sSiteCode 				= $realname['realnamephoneSikey'];			// 본인인증 사이트 코드
		$sSitePassword		= $realname['realnamePhoneSipwd'];			// 본인인증 사이트 패스워드
		$authtype = "M";      	// 없으면 기본 선택화면, X: 공인인증서, M: 핸드폰, C: 카드
		$popgubun 	= "Y";		//Y : 취소버튼 있음 / N : 취소버튼 없음
		$customize 	= "";			//없으면 기본 웹페이지 / Mobile : 모바일페이지

		//$cb_encode_path	= $_SERVER["DOCUMENT_ROOT"]."/namecheck/CPClient";	// 암호화 프로그램의 위치 (절대경로+모듈명)_Linux ..
		//$sType			= "REQ";
		//$reqseq = `$cb_encode_path SEQ $sSiteCode`;

		//$returnurl		= "http://".$_SERVER["HTTP_HOST"]."/member_process/niceid_phone_return";	// 성공시 이동될 URL
		//$errorurl		= "http://".$_SERVER["HTTP_HOST"]."/member_process/niceid_phone_return";		// 실패시 이동될 URL

		//*************************************************************************8******************************************************

		$enc_data = $_POST["EncodeData"];		// 암호화된 결과 데이타
		$sReserved1 = $_POST['param_r1'];
		$sReserved2 = $_POST['param_r2'];
		$sReserved3 = $_POST['param_r3'];

		//////////////////////////////////////////////// 문자열 점검///////////////////////////////////////////////
		if(preg_match('~[^0-9a-zA-Z+/=]~', $enc_data, $match)) {echo "입력 값 확인이 필요합니다 : ".$match[0]; exit;} // 문자열 점검 추가.
		if(base64_encode(base64_decode($enc_data))!=$enc_data) {echo "입력 값 확인이 필요합니다"; exit;}

		if(preg_match("/[#\&\\+\-%@=\/\\\:;,\.\'\"\^`~\_|\!\/\?\*$#<>()\[\]\{\}]/i", $sReserved1, $match)) {echo "문자열 점검 : ".$match[0]; exit;}
		if(preg_match("/[#\&\\+\-%@=\/\\\:;,\.\'\"\^`~\_|\!\/\?\*$#<>()\[\]\{\}]/i", $sReserved2, $match)) {echo "문자열 점검 : ".$match[0]; exit;}
		if(preg_match("/[#\&\\+\-%@=\/\\\:;,\.\'\"\^`~\_|\!\/\?\*$#<>()\[\]\{\}]/i", $sReserved3, $match)) {echo "문자열 점검 : ".$match[0]; exit;}
		///////////////////////////////////////////////////////////////////////////////////////////////////////////


		if ($enc_data != "") {

			//$plaindata = `$cb_encode_path DEC $sSiteCode $sSitePassword $enc_data`;		// 암호화된 결과 데이터의 복호화
			$function = 'get_decode_data';// 암호화된 결과 데이터의 복호화
			if (extension_loaded($module)) {
				$plaindata = $function($sSiteCode, $sSitePassword, $enc_data);
			} else {
				$plaindata = "Module get_response_data is not compiled into PHP";
			}


			if ($plaindata == -1){
				//암/복호화 시스템 오류
				$returnMsg  = getAlert('mb166');
			}else if ($plaindata == -4){
				//복호화 처리 오류
				$returnMsg  = getAlert('mb167');
			}else if ($plaindata == -5){
				//HASH값 불일치 - 복호화 데이터는 리턴됨
				$returnMsg  = getAlert('mb168');
			}else if ($plaindata == -6){
				//복호화 데이터 오류
				$returnMsg  = getAlert('mb169');
			}else if ($plaindata == -9){
				//입력값 오류
				$returnMsg  = getAlert('mb170');
			}else if ($plaindata == -12){
				//사이트 비밀번호 오류
				$returnMsg  = getAlert('mb173');
			}else{
				//본인인증이 확인되었습니다.
				$returnMsg  = getAlert('mb174');

				// 복호화가 정상적일 경우 데이터를 파싱합니다.
 				//$ciphertime = `$cb_encode_path CTS $sSiteCode $sSitePassword $enc_data`;	// 암호화된 결과 데이터 검증 (복호화한 시간획득)

				$requestnumber	= GetValueNameCheck($plaindata , "REQ_SEQ");
				$responsenumber = GetValueNameCheck($plaindata , "RES_SEQ");
				$authtype		= GetValueNameCheck($plaindata , "AUTH_TYPE");
				$name			= GetValueNameCheck($plaindata , "NAME");
				$birthdate		= GetValueNameCheck($plaindata , "BIRTHDATE");
				$gender			= GetValueNameCheck($plaindata , "GENDER");
				$nationalinfo	= GetValueNameCheck($plaindata , "NATIONALINFO");	//내/외국인정보(사용자 매뉴얼 참조)
				$dupinfo		= GetValueNameCheck($plaindata , "DI");
				$conninfo		= GetValueNameCheck($plaindata , "CI");
				$errcode		= GetValueNameCheck($plaindata , "ERR_CODE");
				$phone_number	= GetValueNameCheck($plaindata , "MOBILE_NO");

				if(strcmp($_SESSION["REQ_SEQ_P"], $requestnumber) != 0  || !$dupinfo)
				{

					$requestnumber		= "";
					$responsenumber		= "";
					$authtype			= "";
					$name				= "";
					$birthdate			= "";
					$gender				= "";
					$nationalinfo		= "";
					$dupinfo			= "";
					$conninfo			= "";

					//세션값이 다릅니다. 올바른 경로로 접근하시기 바랍니다.
					$msg = getAlert('mb175');
					pageClose($msg);
					exit;

				}else{

					/**
					echo "[실명확인결과 : ".$sResult."]<br>";
					echo "[이름 : ".iconv("euc-kr", "utf-8", $name)."]<br>";
					echo "[성별 : ".$gender."]<br>";
					echo "[생년월일 : ".$birthdate."]<br>";
					echo "[내/외국인정보 : ".$nationalinfo."]<br>";

					echo "[DI(64 byte) : ".$dupinfo."]<br>";
					echo "[CI(88 byte) : ".$conninfo."]<br>";

					echo "[요청고유번호 : ".$requestnumber."]<br>";
					echo "[RESERVED1 : ".GetValueNameCheck($plaindata , "RESERVED1")."]<br>";
					echo "[RESERVED2 : ".GetValueNameCheck($plaindata , "RESERVED2")."]<br>";
					echo "[RESERVED3 : ".GetValueNameCheck($plaindata , "RESERVED3")."]<br>";
					**/
					// 2018-05-23 jhr 핸드폰 자릿수는 10, 11로 고정
					/*
					2018-06-27 나이스평가에서 검증완료되어 넘어온 핸드폰 번호는 유효성 검사 제거.
					if	( isset($phone_number) && (strlen($phone_number) < 10 || strlen($phone_number) > 11) ) {
						$msg = "유효하지 않는 핸드폰번호 입니다.";
						pageClose($msg);
					}
					*/

					$auth_data["auth_yn"]			= "Y";
					$auth_data["namecheck_type"]	= "phone";
					$auth_data["namecheck_name"]	= iconv("euc-kr", "utf-8", $name);
					$auth_data["namecheck_sex"]		= iconv("euc-kr", "utf-8", $gender);
					$auth_data["namecheck_birth"]	= iconv("euc-kr", "utf-8", $birthdate);
					$auth_data["namecheck_check"]	= iconv("euc-kr", "utf-8", $dupinfo);//중복체크용
					$auth_data["namecheck_vno"]		= iconv("euc-kr", "utf-8", $conninfo);//주민등록번호와고유키
					$auth_data["phone_number"]		= $phone_number;//핸드폰번호

					if(isset($_GET['intro']) && $_GET['intro']=='Y') {
						if($auth_data["namecheck_birth"]){
							$adult = date("Y") - substr($auth_data["namecheck_birth"], 0, 4) + 1;
						}
						if($adult>19){
							$auth_intro_data = array('auth_intro_type'=>'auth', 'auth_intro_yn'=>'Y');
							$this->session->sess_expiration = (60 * 5);
							$this->session->set_userdata(array('auth_intro'=>$auth_intro_data));
							// 성인인증 로그 :: 2015-03-13 lwh
							$this->adult_log('phone');
							//성인인증이 성공적으로 완료되었습니다
							$msg = getAlert('mb083');

							if($_GET['type']=='join'){
								$qry = "select count(*) as cnt from fm_member where auth_code='".$auth_data["namecheck_check"]."'";
								$query = $this->db->query($qry);
								$member = $query -> row_array();

								if($member["cnt"] > 0){
									$url = "/member/login?return_url=" . urlencode("/mypage/myinfo");
									//이미 가입된 정보입니다.
									$msg = getAlert('mb176');
									pageLocation($url, $msg, 'opener');
									pageClose();
									exit;
								}

								$this->session->sess_expiration = (60 * 5);
								$this->session->set_userdata(array('auth'=>$auth_data));
								$_GET['return_url'] = '/member/agreement?authok=1';
							}
						}else{
							//미성년자는 이용할 수 없습니다.
							$msg = getAlert('mb177');
							pageClose($msg);
							exit;
						}

						$return_url = ($_GET['return_url']) ? $_GET['return_url'] : '/main';
						pageLocation($return_url, $msg, 'opener');
						pageClose();
						exit;
					}elseif(isset($_GET['findidpw']) && $_GET['findidpw']=='Y') {//아이디/패스워드 찾기
						$this->_findidpwresult($auth_data);
					}elseif(isset($_GET['dormancy']) && $_GET['dormancy']=='Y') {//휴면회원 인증
						$this->membermodel->dormancy_off($_GET['dormancy_seq']);
						$auth_dormancy_data = array('auth_dormancy_type'=>'auth', 'auth_dormancy_yn'=>'Y');
						$this->session->sess_expiration = (60 * 5);
						$this->session->set_userdata(array('auth_dormancy'=>$auth_dormancy_data));
						//휴면처리가 성공적으로 해제되었습니다.\\n재로그인후 정상적으로 쇼핑몰 이용이 가능합니다.
						$msg = getAlert('mb179');
						$url = "/member/login?return_url=" . urlencode("/main");
						pageLocation($url, $msg, 'opener');
						pageClose();
						exit;
					}else{//가입페이지

						if( !$auth_data["namecheck_check"] ){
							//세션값이 다릅니다. 올바른 경로로 접근하시기 바랍니다.
							$msg = getAlert('mb175');
							pageClose($msg);
							exit;
						}
						$qry = "select count(*) as cnt from fm_member where auth_code='".$auth_data["namecheck_check"]."'";
						$query = $this->db->query($qry);
						$member = $query -> row_array();

						if($member["cnt"] > 0) {

							$url = "/member/login?return_url=" . urlencode("/mypage/myinfo");
							//이미 가입된 정보입니다.
							$msg = getAlert('mb176');
							pageLocation($url, $msg, 'opener');
							pageClose();
							exit;
						}

						$this->session->sess_expiration = (60 * 5);
						$this->session->set_userdata(array('auth'=>$auth_data));
						pageLocation('/member/agreement?authok=1', "", 'opener');
						pageClose();
						exit;
					}
				}


			}
			//"잠시 후 다시 시도하여주십시오.<br/>오류가 계속 될 경우 고객센터로 문의하세요."
			$msg = getAlert('mb178');
			pageClose($msg);
			exit;

		} else {
			//처리할 암호화 데이타가 없습니다.
			$sRtnMsg = getAlert('mb180');
		}

		pageClose($sRtnMsg);
		exit;
	}

	public function ipin_chk(){
		$realname = config_load('realname');
		$auth = $this->session->userdata('auth');
		$findtypess = $this->session->userdata('findtypess');
		$findidss = $this->session->userdata('findidss');

		if(!extension_loaded('IPINClient')) {
			dl('IPINClient.' . PHP_SHLIB_SUFFIX);
		}
		$module = 'IPINClient';


		$sSiteCode		= $realname['ipinSikey'];
		$sSitePw		= $realname['ipinKeyString'];

		$sEncData					= "";			// 암호화 된 사용자 인증 정보
		$sDecData					= "";			// 복호화 된 사용자 인증 정보

		$sRtnMsg					= "";			// 처리결과 메세지
		$sModulePath	= $_SERVER["DOCUMENT_ROOT"]."/namecheck/IPINClient";

		$sEncData = $_POST['enc_data'];

		//////////////////////////////////////////////// 문자열 점검///////////////////////////////////////////////
		if(preg_match('~[^0-9a-zA-Z+/=]~', $sEncData, $match)) {echo "입력 값 확인이 필요합니다"; exit;}
		if(base64_encode(base64_decode($sEncData))!=$sEncData) {echo "입력 값 확인이 필요합니다!"; exit;}
		///////////////////////////////////////////////////////////////////////////////////////////////////////////

		$sCPRequest = $_SESSION['CPREQUEST'];

		if ($sEncData != "") {

			//$sDecData = `$sModulePath RES $sSiteCode $sSitePw $sEncData`;

			// 사용자 정보를 복호화 합니다.
			$function = 'get_response_data';
				if (extension_loaded($module)) {
					$sDecData = $function($sSiteCode, $sSitePw, $sEncData);
				} else {
					$sDecData = "Module get_response_data is not compiled into PHP";
				}


			if ($sDecData == -9) {
				$sRtnMsg = "입력값 오류 : 복호화 처리시, 필요한 파라미터값의 정보를 정확하게 입력해 주시기 바랍니다.";
			} else if ($sDecData == -12) {
				$sRtnMsg = "NICE신용평가정보에서 발급한 개발정보가 정확한지 확인해 보세요.";
			} else {

				$arrData = preg_split("/\^/", $sDecData);
				$iCount = count($arrData);

				if ($iCount >= 5) {

					$strResultCode	= $arrData[0];			// 결과코드
					if ($strResultCode == 1) {
						$strCPRequest	= $arrData[8];			// CP 요청번호

						if ($sCPRequest == $strCPRequest) {
							//사용자 인증 성공
							$sRtnMsg = getAlert('mb181');

							$strVno      		= $arrData[1];	// 가상주민번호 (13자리이며, 숫자 또는 문자 포함)
							$strUserName		= $arrData[2];	// 이름
							$strDupInfo			= $arrData[3];	// 중복가입 확인값 (64Byte 고유값)
							$strAgeInfo			= $arrData[4];	// 연령대 코드 (개발 가이드 참조)
							$strGender			= $arrData[5];	// 성별 코드 (개발 가이드 참조)
							$strBirthDate		= $arrData[6];	// 생년월일 (YYYYMMDD)
							$strNationalInfo	= $arrData[7];	// 내/외국인 정보 (개발 가이드 참조)

							$auth_data["auth_yn"] = "Y";
							$auth_data["namecheck_type"] = "ipin";
							$auth_data["namecheck_name"] = iconv("euc-kr", "utf-8", $strUserName);
							$auth_data["namecheck_sex"] = iconv("euc-kr", "utf-8", $strGender);
							$auth_data["namecheck_birth"] = iconv("euc-kr", "utf-8", $strBirthDate);
							$auth_data["namecheck_check"] = iconv("euc-kr", "utf-8", $strDupInfo);
							$auth_data["namecheck_vno"] = iconv("euc-kr", "utf-8", $strVno);

							if(isset($_GET['intro']) && $_GET['intro']=='Y'){
								//if($strAgeInfo==7){
								if((int)$strAgeInfo>=6){//##########  2018.02.05 gcs ksm : 17.11.30~ RSA 패치
									$auth_intro_data = array('auth_intro_type'=>'auth', 'auth_intro_yn'=>'Y');
									$this->session->sess_expiration = (60 * 5);
									$this->session->set_userdata(array('auth_intro'=>$auth_intro_data));
									// 성인인증 로그 :: 2015-03-13 lwh
									$this->adult_log('ipin');
									//성인인증이 성공적으로 완료되었습니다
									$msg = getAlert('mb083');

									if($_GET['type']=='join'){
										$qry = "select count(*) as cnt from fm_member where auth_code='".$auth_data["namecheck_check"]."'";
										$query = $this->db->query($qry);
										$member = $query -> row_array();

										if($member["cnt"] > 0){
											$url = "/member/login?return_url=" . urlencode("/mypage/myinfo");
											//이미 가입된 정보입니다.
											$msg = getAlert('mb176');
											pageLocation($url, $msg, 'opener');
											pageClose();
											exit;
										}

										$this->session->sess_expiration = (60 * 5);
										$this->session->set_userdata(array('auth'=>$auth_data));
										$_GET['return_url'] = '/member/agreement?authok=1';
									}
								}else{
									//미성년자는 이용할 수 없습니다.
									$msg = getAlert('mb177');
									pageClose($msg);
									exit;
								}

								$return_url = ($_GET['return_url']) ? $_GET['return_url'] : '/main';
								pageLocation($return_url, $msg, 'opener');
								pageClose();
								exit;
							}elseif(isset($_GET['findidpw']) && $_GET['findidpw']=='Y') {//아이디/패스워드 찾기
								$this->_findidpwresult($auth_data,  $arrData);
							}elseif(isset($_GET['dormancy']) && $_GET['dormancy']=='Y') {//휴면회원 인증
								$this->membermodel->dormancy_off($_GET['dormancy_seq']);
								$auth_dormancy_data = array('auth_dormancy_type'=>'auth', 'auth_dormancy_yn'=>'Y');
								$this->session->sess_expiration = (60 * 5);
								$this->session->set_userdata(array('auth_dormancy'=>$auth_dormancy_data));
								//휴면처리가 성공적으로 해제되었습니다.\\n재로그인후 정상적으로 쇼핑몰 이용이 가능합니다.
								$msg = getAlert('mb179');
								$url = "/member/login?return_url=" . urlencode("/main");
								pageLocation($url, $msg, 'opener');
								pageClose();
								exit;
							}else{//가입페이지
								$qry = "select count(*) as cnt from fm_member where auth_code='".$auth_data["namecheck_check"]."'";
								$query = $this->db->query($qry);
								$member = $query -> row_array();

								if($member["cnt"] > 0){
									$url = "/member/login?return_url=" . urlencode("/mypage/myinfo");
									//이미 가입된 정보입니다.
									$msg = getAlert('mb176');
									pageLocation($url, $msg, 'opener');
									pageClose();
									exit;
								}

								$this->session->sess_expiration = (60 * 5);
								$this->session->set_userdata(array('auth'=>$auth_data));

								pageLocation('/member/agreement?authok=1', "", 'opener');
								pageClose();
								exit;
							}
						} else {
							//CP 요청번호 불일치 : 세션에 넣은 $sCPRequest 데이타를 확인해 주시기 바랍니다.
							$sRtnMsg = getAlert('mb182',$sCPRequest);
						}
					} else {
						//리턴값 확인 후, NICE신용평가정보 개발 담당자에게 문의해 주세요. [$strResultCode]
						$sRtnMsg = getAlert('mb183',$strResultCode);
					}

				} else {
					//리턴값 확인 후, NICE신용평가정보 개발 담당자에게 문의해 주세요. [$strResultCode]
					$sRtnMsg = getAlert('mb183',$strResultCode);
				}

			}
		} else {
			//처리할 암호화 데이타가 없습니다.
			$sRtnMsg = getAlert('mb180');
		}

		pageClose($sRtnMsg);
		exit;
	}


	//아이디/패스워드찾기 완료화면 구성
	public function _findidpwresult($auth_data, $arrData = null) {

		$smsauth = config_load('master');//SMS사용시

		if( $auth_data ) {
			$qry = "select count(*) as cnt, userid, member_seq, rute from fm_member where ";
			if( $this->session->userdata('findtypess') == 'pw'){//비밀번호찾기
				$qry .= " rute = 'none' and   auth_code='".$auth_data["namecheck_check"]."' ";
			}else{
				$qry .= " auth_code='".$auth_data["namecheck_check"]."' ";
			}
			$qry .= " and auth_type != 'none' ";
			$query = $this->db->query($qry);
			$success = $query -> row_array();

			if($success["cnt"] < 1) {
				$qry = "select count(*) as cnt, userid, member_seq, rute from fm_member_dr where ";
				if( $this->session->userdata('findtypess') == 'pw'){//비밀번호찾기
					$qry .= " rute = 'none' and   auth_code='".$auth_data["namecheck_check"]."' ";
				}else{
					$qry .= " auth_code='".$auth_data["namecheck_check"]."' ";
				}
				$qry .= " and auth_type != 'none' ";
				$query = $this->db->query($qry);
				$success = $query -> row_array();
			}

			$success['error'] = false;
			$success['errorid'] = false;
			if($success["cnt"] > 0) {
				if( $this->session->userdata('findidss') ) {
					if( $this->session->userdata('findidss') != $success["userid"] ) {
						$success['error']		= true;
						$success['errorid']	= true;
					}
				}
			}else{
				$success['error'] = true;
			}
		}

		$scdocument = "top.opener.document";
		$scripts[] = "<script type='text/javascript' src='/app/javascript/jquery/jquery.min.js'></script>";
		$scripts[] = "<script type='text/javascript'  charset='utf-8'>";
		$scripts[] = "$(function() {";


		if( $this->session->userdata('findtypess') == 'pw'){//비밀번호찾기

			$scripts[] = '$("#findidfromlay",'.$scdocument.').show();';
			$scripts[] = '$("#findidresultlay",'.$scdocument.').hide();';

			$scripts[] = '$("#findpwfromlay",'.$scdocument.').hide();';
			$scripts[] = '$("#findpwresultlay",'.$scdocument.').show();';
			$scripts[] = '$("#findpwlay1",'.$scdocument.').text("");';
			$scripts[] = '$("#findpwlay2",'.$scdocument.').text("");';
			$scripts[] = '$("#findpwlay3",'.$scdocument.').text("");';
			$scripts[] = '$(".findpwresultfalse1",'.$scdocument.').hide();';
			$scripts[] = '$(".findpwresultfalse2",'.$scdocument.').hide();';
			$scripts[] = '$(".findpwresultok1",'.$scdocument.').hide();';
			$scripts[] = '$(".findpwresultok2",'.$scdocument.').hide();';
			$scripts[] = '$(".findpwresultok3",'.$scdocument.').hide();';

			if( $success ) {
				if( $success['error'] === false ){
					unset($params['password']);
					$this->findpw = chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).substr(mktime()*2,1,4);
					$scripts[] = '$(".findpwresultok1",'.$scdocument.').show();';
					$scripts[] = '$("#findpwlay1",'.$scdocument.').text("'.($this->findpw).'");';

					$this->findpw = hash('sha256',md5($this->findpw));
					$sql = "update fm_member set password = ?, update_date = now() where member_seq = ?";
					$this->db->query($sql,array($this->findpw,$success["member_seq"]));
				}elseif( $success['errorid'] ) {
					$scripts[] = '$(".findpwresultfalse2",'.$scdocument.').show();';
				}else{
					$scripts[] = '$(".findpwresultfalse1",'.$scdocument.').show();';
				}
			}else{
				$scripts[] = '$(".findpwresultfalse1",'.$scdocument.').show();';
			}

		}else{

			$scripts[] = '$("#findpwfromlay",'.$scdocument.').show();';
			$scripts[] = '$("#findpwresultlay",'.$scdocument.').hide();';

			$scripts[] = '$("#findidfromlay",'.$scdocument.').hide();';
			$scripts[] = '$("#findidresultlay",'.$scdocument.').show();';
			$scripts[] = '$("#findidlay1",'.$scdocument.').text("");';
			$scripts[] = '$("#findidlay2",'.$scdocument.').text("");';
			$scripts[] = '$("#findidlay3",'.$scdocument.').text("");';
			$scripts[] = '$(".findidresultok1",'.$scdocument.').hide();';
			$scripts[] = '$(".findidresultok2",'.$scdocument.').hide();';
			$scripts[] = '$(".findidresultok3",'.$scdocument.').hide();';
			$scripts[] = '$(".findidresultfalse",'.$scdocument.').hide();';

			if( $success ) {
				if( $success['error'] === false ) {
					$scripts[] = '$(".findidresultok1",'.$scdocument.').show();';
					$scripts[] = '$("#findidlay1",'.$scdocument.').text("'.($success["userid"]).'");';
				}else{
					$scripts[] = '$(".findidresultfalse",'.$scdocument.').show();';
				}
			}else{
				$scripts[] = '$(".findidresultfalse",'.$scdocument.').show();';
			}
		}
		$scripts[] = 'self.close();';

		$scripts[] = "});";
		$scripts[] = "</script>";
echo '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
foreach($scripts as $script){
	echo $script."\n";
}
echo '</head><body></body></html>';
exit;
	}

	###
	public function auth_chk(){
		###
		//이름
		$this->validation->set_rules('name', getAlert('mb084'),'trim|required|max_length[30]|xss_clean');
		if(isset($_POST['regno'])){
			//주민등록번호
			$this->validation->set_rules('regno', getAlert('mb085'),'trim|required|max_length[13]|numeric|xss_clean');
		}else{
			$this->validation->set_rules('regno1', getAlert('mb085'),'trim|required|max_length[6]|numeric|xss_clean');
			$this->validation->set_rules('regno2', getAlert('mb085'),'trim|required|max_length[7]|numeric|xss_clean');
		}
		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		###
		$realname = config_load('realname');
		$sSiteID = $realname['realnameId'];
		$sSitePW =  $realname['realnamePwd'];

		$cb_encode_path = "/usr/bin/cb_namecheck";

		$strJumin= isset($_POST['regno']) ? $_POST['regno'] : $_POST["regno1"].$_POST["regno2"];		// 주민번호
		$strName = $_POST["name"];							//이름
		$strName = iconv('utf-8', 'euc-kr', $strName);

		$iReturnCode  = "";
		$iReturnCode = `$cb_encode_path $sSiteID $sSitePW $strJumin $strName`;
		switch($iReturnCode){
			case 1: // 성공
				//실명인증이 성공적으로 완료되었습니다.<br>회원가입정보를 입력해 주시기 바랍니다.
				$msg = getAlert('mb079');
				break;
			case 2:
				//www.namecheck.co.kr 의 실명등록확인 또는 02-1600-1522 콜센터로 문의주시기 바랍니다.
				$msg = getAlert('mb080');
				break;
			case 3:
				//"www.namecheck.co.kr 의 실명등록확인 또는 02-1600-1522 콜센터로 문의주시기 바랍니다."
				$msg = getAlert('mb080');
				break;
			case 50:
				//명의도용차단 서비스 가입자
				$msg = getAlert('mb081');
				break;
			default:
				//인증실패
				$msg = getAlert('mb082');
				break;
		}

		$callback = "";
		if(isset($_POST['intro']) && $_POST['intro']=='Y'){
			if($iReturnCode==1){
				$auth_data = array('auth_type'=>'auth', 'auth_yn'=>'Y');
				$this->session->sess_expiration = (60 * 5);
				$this->session->set_userdata(array('auth'=>$auth_data));
				$callback = "parent.document.location = '/main';";
				//성인인증이 성공적으로 완료되었습니다.
				$msg = getAlert('mb083');
			}
		}else{
			if($iReturnCode==1){
				$auth_data = array('auth_type'=>'auth', 'auth_yn'=>'Y');
				$this->session->sess_expiration = (60 * 5);
				$this->session->set_userdata(array('auth'=>$auth_data));
				$img = "/data/skin/".$this->skin."/images/design/btn_ok.gif";
				$callback = "parent.$('#name').attr('readonly',true);
					parent.$('#regno1').attr('readonly',true);
					parent.$('#regno2').attr('readonly',true);
					parent.$('#submit_btn_area').html(\"<img src='{$img}' id='auth_ok_btn' class='hand'>\");
					parent.$('#r_ipin').html('');
				";
			}
		}
		openDialogAlert($msg,400,140,'parent',$callback);
		exit;

	}
	###

	/**
	** 가입, 아이디/패스워드찾기, 성인인증시 : 본인인증/안심체크/아이핀 실명인증 기본 코드생성
	**/
	public function realnamecheck() {
		$realnametype = ($_POST['realnametype'])?$_POST['realnametype']:$_GET['realnametype'];
		$findidpw = ($_POST['findidpw'])?$_POST['findidpw']:$_GET['findidpw'];
		$intro = ($_POST['intro'])?$_POST['intro']:$_GET['intro'];
		$type = ($_POST['type'])?$_POST['type']:$_GET['type'];
		$dormancy = ($_POST['dormancy'])?$_POST['dormancy']:$_GET['dormancy'];
		$dormancy_seq = ($_POST['dormancy_seq'])?$_POST['dormancy_seq']:$_GET['dormancy_seq'];
		$return_url = ($_POST['return_url'])?$_POST['return_url']:$_GET['return_url'];
		$realname = config_load('realname');

		$sReserved1 = ($_POST['sReserved1'])?$_POST['sReserved1']:$_GET['sReserved1'];
		$sReserved2 = ($_POST['sReserved2'])?$_POST['sReserved2']:$_GET['sReserved2'];
		$sReserved3 = ($_POST['sReserved3'])?$_POST['sReserved3']:$_GET['sReserved3'];

		$unsetuserdata = array('findtypess' => '', 'findidss' => '', 'auth' => '');
		$this->session->unset_userdata($unsetuserdata);
		unset($auth);
		if($findidpw){
			$returnurl_intro = "?findidpw=Y";
		}elseif($intro){
			$returnurl_intro = "?intro=Y";
			if($type){
				$returnurl_intro = $returnurl_intro . "&type=" . $type;
			}
		}elseif($dormancy){
			$returnurl_intro = "?dormancy=Y&dormancy_seq=".$dormancy_seq;
		}

		if($return_url){
			$returnurl_intro = ($returnurl_intro) ? $returnurl_intro."&return_url=".urldecode($return_url) : "?return_url=".urldecode($return_url);
		}

		if($findidpw){
			$this->session->sess_expiration = (60 * 5);
			if($sReserved1){
				$this->session->set_userdata(array('findtypess'=>$sReserved1));
			}

			if($sReserved2){
				$this->session->set_userdata(array('findidss'=>$sReserved2));
			}
		}


		if( $realnametype && ($realname['useRealnamephone']=='Y' || $realname['useRealname']=='Y' || $realname['useIpin']=='Y' || $realname['useRealnamephone_adult']=='Y' || $realname['useIpin_adult']=='Y'|| $realname['useRealnamephone_dormancy']=='Y' || $realname['useIpin_dormancy']=='Y') ) {

			if ($_SERVER['HTTPS'] == "on") {
				$HTTP_HOST = "https://".$_SERVER['HTTP_HOST'];
			}else{
				$HTTP_HOST = "http://".$_SERVER['HTTP_HOST'];
			}

			if( $realnametype == 'phone' ) {//본인인증
				//**************************************** 본인인증 : 휴대폰 필수 수정값***************************************************************************
				if(!extension_loaded('CPClient')) {
					dl('CPClient.' . PHP_SHLIB_SUFFIX);
				}
				$module = 'CPClient';

				$sSiteCode 				= $realname['realnamephoneSikey'];			// 본인인증 사이트 코드
				$sSitePassword		= $realname['realnamePhoneSipwd'];			// 본인인증 사이트 패스워드
				$authtype = "M";      	// 없으면 기본 선택화면, X: 공인인증서, M: 핸드폰, C: 카드
				$popgubun 	= "Y";		//Y : 취소버튼 있음 / N : 취소버튼 없음

				if( $this->_is_mobile_agent) {//$this->mobileMode  ||
					$customize 	= "Mobile";			//없으면 기본 웹페이지 / Mobile : 모바일페이지
				}else{
					$customize 	= "";			//없으면 기본 웹페이지 / Mobile : 모바일페이지
				}

				//$cb_encode_path	= $_SERVER["DOCUMENT_ROOT"]."/namecheck/CPClient";	// 암호화 프로그램의 위치 (절대경로+모듈명)_Linux ..
				//$sType			= "REQ";
				//$reqseq = `$cb_encode_path SEQ $sSiteCode`;

				$reqseq = "REQ_0123456789";     // 요청 번호, 이는 성공/실패후에 같은 값으로 되돌려주게 되므로

				// 업체에서 적절하게 변경하여 쓰거나, 아래와 같이 생성한다.
				$function = 'get_cprequest_no';
				if (extension_loaded($module)) {
					$reqseq = $function($sitecode);
				} else {
					$reqseq = "Module get_request_no is not compiled into PHP";
				}

				$returnurl		= $HTTP_HOST."/member_process/niceid_phone_return".$returnurl_intro;	// 성공시 이동될 URL
				$errorurl		= $HTTP_HOST."/member_process/niceid_phone_return".$returnurl_intro;		// 실패시 이동될 URL

				// reqseq값은 성공페이지로 갈 경우 검증을 위하여 세션에 담아둔다.

				$this->session->set_userdata(array('REQ_SEQ_P'=>$reqseq));//$_SESSION["REQ_SEQ"] = $reqseq;
				$_SESSION["REQ_SEQ_P"] = $reqseq;


				// 입력될 plain 데이타를 만든다.1
				$plaindata =  "7:REQ_SEQ" . strlen($reqseq) . ":" . $reqseq .
										  "8:SITECODE" . strlen($sSiteCode) . ":" . $sSiteCode .
										  "9:AUTH_TYPE" . strlen($authtype) . ":". $authtype .
										  "7:RTN_URL" . strlen($returnurl) . ":" . $returnurl .
										  "7:ERR_URL" . strlen($errorurl) . ":" . $errorurl .
										  "11:POPUP_GUBUN" . strlen($popgubun) . ":" . $popgubun .
										  "9:CUSTOMIZE" . strlen($customize) . ":" . $customize.
										  "9:RESERVED1" . strlen($sReserved1) . ":" . $sReserved1.
										  "9:RESERVED2" . strlen($sReserved2) . ":" . $sReserved2.
										  "9:RESERVED3" . strlen($sReserved3) . ":" . $sReserved3;

				//$enc_data = `$cb_encode_path ENC $sSiteCode $sSitePassword $plaindata`;

				$function = 'get_encode_data';
				if (extension_loaded($module)) {
					$enc_data = $function($sSiteCode, $sSitePassword, $plaindata);
				} else {
					$enc_data = "Module get_request_data is not compiled into PHP";
				}

				if( $enc_data == -1 )
				{
					//암/복호화 시스템 오류입니다.
					$returnMsg = getAlert('mb166');
					//$enc_data = "";
				}
				else if( $enc_data== -2 )
				{
					//암호화 처리 오류입니다.
					$returnMsg = getAlert('mb171');
					//$enc_data = "";
				}
				else if( $enc_data== -3 )
				{
					//암호화 데이터 오류 입니다.
					$returnMsg = getAlert('mb172');
					//$enc_data = "";
				}
				else if( $enc_data== -9 )
				{
					//입력값 오류 입니다.
					$returnMsg = getAlert('mb170');
					//$enc_data = "";
				}
				$sEncData = $enc_data;
			}
			elseif( $realnametype == 'ipin' ) {//아이핀체크

					if(!extension_loaded('IPINClient')) {
						dl('IPINClient.' . PHP_SHLIB_SUFFIX);
					}
					$module = 'IPINClient';

					###
					$sSiteCode		= $realname['ipinSikey'];
					$sSitePw			= $realname['ipinKeyString'];

					$sModulePath	= $_SERVER["DOCUMENT_ROOT"]."/namecheck/IPINClient";
					$sReturnURL		= get_connet_protocol().$_SERVER['HTTP_HOST']."/member_process/ipin_chk".$returnurl_intro;

					##
					$sType			= "SEQ";
					//$sCPRequest = `$sModulePath $sType $sSiteCode`;

					$function = 'get_request_no';
					if (extension_loaded($module)) {
						$sCPRequest = $function($sSiteCode);
					} else {
						$sCPRequest = "Module get_request_no is not compiled into PHP";
					}


					$this->session->set_userdata(array('CPREQUEST'=>$sCPRequest));
					$_SESSION['CPREQUEST'] = $sCPRequest;

					##
					$sType			= "REQ";
					$sEncData		= "";
					$sRtnMsg		= "";

					//$sEncData	= `$sModulePath $sType $sSiteCode $sSitePw $sCPRequest $sReturnURL`;//$sCPRequest $sReturnURL

					$function = 'get_request_data';
					if (extension_loaded($module)) {
						$sEncData = $function($sSiteCode, $sSitePw, $sCPRequest, $sReturnURL);
					} else {
						$sEncData = "Module get_request_data is not compiled into PHP";
					}

					if ($sEncData == -9){
						$sRtnMsg = "입력값 오류 : 암호화 처리시, 필요한 파라미터값의 정보를 정확하게 입력해 주시기 바랍니다.";
					}
			}
			else{//안심체크
				//**************************************** 필수 수정값 ***************************************************************************

				if(!extension_loaded('CPClient')) {
					dl('CPClient.' . PHP_SHLIB_SUFFIX);
				}
				$module = 'CPClient';

				$sSiteCode 				= $realname['realnameId'];							// 안심체크 사이트 코드
				$sSitePassword		= $realname['realnamePwd'];						// 안심체크 사이트 패스워드

				$sIPINSiteCode		= $realname['ipinSikey'];								// 아이핀사이트 코드
				$sIPINPassword		= $realname['ipinKeyString'];						// 아이핀사이트 패스워드
				$sReturnURL			= $HTTP_HOST."/member_process/niceid2_return".$returnurl_intro;		//결과 수신 : full URL 입력
				$cb_encode_path	= $_SERVER["DOCUMENT_ROOT"]."/namecheck/CPClient";	// 암호화 프로그램의 위치 (절대경로+모듈명)_Linux ..

				//*******************************************************************************************************************************

				$sRequestNO = "";									//요청고유번호, 이는 성공/실패후에 같은 값으로 되돌려주게 되므로 필요시 사용
				$sClientImg		= "";								//서비스 화면 로고 선택(full 도메인 입력): 사이즈 100*25(px)

				//$sRequestNO = `$cb_encode_path SEQ $sSiteCode`;		//요청고유번호 / 비정상적인 접속 차단을 위해 필요.

				$function = 'get_cprequest_no';//요청고유번호 / 비정상적인 접속 차단을 위해 필요.
					if (extension_loaded($module)) {
						$sRequestNO = $function($sSiteCode);
					} else {
						$sRequestNO = "Module get_request_no is not compiled into PHP";
					}

				$_SESSION["REQ_SEQ"] = $sRequestNO;					//해킹등의 방지를 위하여 세션을 쓴다면, 세션에 요청번호를 넣는다.
				$this->session->set_userdata(array('REQ_SEQ'=>$sRequestNO));

				//echo "sRequestNO : ".$sRequestNO."<br>";

				// 입력될 plain 데이타를 만든다.2
				$plaindata =  "7:RTN_URL" . strlen($sReturnURL) . ":" . $sReturnURL.
							  "7:REQ_SEQ" . strlen($sRequestNO) . ":" . $sRequestNO.
							  "7:IMG_URL" . strlen($sClientImg) . ":" . $sClientImg.
							  "13:IPIN_SITECODE" . strlen($sIPINSiteCode) . ":" . $sIPINSiteCode.
							  "17:IPIN_SITEPASSWORD" . strlen($sIPINPassword) . ":" . $sIPINPassword.
							  "9:RESERVED1" . strlen($sReserved1) . ":" . $sReserved1.
							  "9:RESERVED2" . strlen($sReserved2) . ":" . $sReserved2.
							  "9:RESERVED3" . strlen($sReserved3) . ":" . $sReserved3;

				$function = 'get_encode_data';

				if (extension_loaded($module)) {
					$sEncData = $function($sSiteCode, $sSitePassword, $plaindata);
				} else {
					$sEncData = "Module get_request_data is not compiled into PHP";
				}


				if( $sEncData == -1 )
				{
					//암/복호화 시스템 오류입니다.
					$returnMsg = getAlert('mb166');
				}
				else if( $sEncData== -2 )
				{
					//암호화 처리 오류입니다
					$returnMsg = getAlert('mb171');
				}
				else if( $sEncData== -3 )
				{
					//암호화 데이터 오류 입니다.
					$returnMsg = getAlert('mb172');
				}
				else if( $sEncData== -9 )
				{
					$returnMsg = "입력값 오류 입니다.";
				}
			}

			if(empty($sEncData)) {//실패시
				$returnMsg = '잘못된 접근입니다.';
				pageClose($returnMsg);
				exit;
			}

			if($returnMsg) {//실패시
				pageClose($returnMsg);
				exit;
			}

			$scripts[] = "<script type='text/javascript' src='/app/javascript/jquery/jquery.min.js'></script>";
			$scripts[] = "<script type='text/javascript'>";
			$scripts[] = "$(function() {";
			if( $realnametype == 'phone' ) {//본인인증
				$encodedataform = '<input type="hidden" name="m" value="checkplusSerivce" >';
				$encodedataform .= '<input type="hidden" name="EncodeData" value="'.$sEncData.'" >';
				$action= 'https://nice.checkplus.co.kr/CheckPlusSafeModel/checkplus.cb';
				$scripts[] = 'document.form_chk.submit();';
			}else{
				if( $realnametype == 'ipin' ) {//ipin
					$encodedataform = '<input type="hidden" name="m" value="pubmain" >';
					$action= 'https://cert.vno.co.kr/ipin.cb';
				}else{
					$encodedataform = '<input type="hidden" name="m" value="" >';
					$action = 'https://cert.namecheck.co.kr/NiceID2/certpass_input.asp';
				}
				$encodedataform .= '<input type="hidden" name="enc_data" value="'.$sEncData.'" >';
				$scripts[] = 'document.form_chk.submit();';
			}


			$scripts[] = "});";
			$scripts[] = "</script>";

echo '<html><head>';
foreach($scripts as $script){
	echo $script."\n";
}
echo '</head><body>
<form method="post" name="form_chk" action="'.$action.'">
'.$encodedataform.'
<input type="hidden" name="param_r1" value="'.trim($sReserved1).'">
<input type="hidden" name="param_r2" value="'.trim($sReserved2).'">
<input type="hidden" name="param_r3" value="'.trim($sReserved3).'">
</form>
</body>
</html>
';
			exit;
		}else{
			$returnMsg ="잘못된 접근입니다.";
			pageClose($returnMsg);
			exit;
		}
	}

	public function adult_log($type){
		$sess_data					= $this->session->userdata;

		$log_data['member_seq']		= ($sess_data['user']['member_seq']) ? $sess_data['user']['member_seq'] : '';
		$log_data['userid']			= ($sess_data['user']['userid']) ? $sess_data['user']['userid'] : '';
		$log_data['ip_address']		= ($sess_data['ip_address']) ? $sess_data['ip_address'] : $_SERVER['REMOTE_ADDR'];
		$log_data['auth_type']		= $type;
		$log_data['user_agent']		= $sess_data['user_agent'];
		$log_data['regist_date']	= date('Y-m-d H:i:s');

		$this->db->insert('fm_adult_log', $log_data);

		// 회원테이블에 성인 인증 회원 업데이트 :: 2015-06-04 lwh
		if($sess_data['user']['member_seq']){
			$this->db->where('member_seq', $sess_data['user']['member_seq']);
			$result = $this->db->update('fm_member', array("adult_auth"=>'Y'));
		}
	}
 /**
  ** 본인인증/안심체크/아이핀 실명인증 체크 관련
  **/

   	//회원아이콘 설정
	public function membericonsave(){
		$this->mdata = $this->membermodel->get_member_data($this->userInfo['member_seq']);//회원정보//$this->userInfo['member_seq']
		$user_icon = $this->mdata['user_icon'];
		$user_icon_file = $this->mdata['user_icon_file'];
		if($_FILES['membericonFile']['tmp_name']){
			$this->load->model('usedmodel');
			$data_used = $this->usedmodel->used_limit_check();
			if( $data_used['type'] ){
				$config['upload_path'] = './data/icon/member';
				$config['max_size']	= $this->config_system['uploadLimit'];
				$tmp = @getimagesize($_FILES['membericonFile']['tmp_name']);
				if( $tmp[0] > 30 && $tmp[1] > 30 ){
					//가로*세로 사이즈가 30*30 이하이어야 합니다.
					$msg = getAlert('mb086');
					openDialogAlert($msg,400,150,'parent');
					exit;
				}

				if($user_icon_file){
					@unlink($_SERVER['DOCUMENT_ROOT'].$config['upload_path'].'/'.$user_icon_file);
				}
				$_FILES['membericonFile']['type'] = $tmp['mime'];

				$file_ext		= end(explode('.', $_FILES['membericonFile']['name']));//확장자추출
				$file_name	= 'm_'.$this->userInfo['member_seq'].'.'.$file_ext;//'.str_replace(" ", "", (substr(microtime(), 2, 6))).'
				$file_name	= str_replace("\'", "", $file_name); 	// ' " 제거
				$file_name	= str_replace("\"", "", $file_name); 	// ' " 제거
				$config['file_name'] = $file_name;
				$config['allowed_types'] = 'jpg|gif|jpeg|png';
				$config['overwrite'] = true;
				$this->load->library('Upload', $config);
				if ( ! $this->upload->do_upload('membericonFile'))
				{
					$error = $this->upload->display_errors();
					openDialogAlert($error,400,150,'parent');
					exit;
				}
				$uploadData = $this->upload->data();
				$user_icon = str_replace($_SERVER['DOCUMENT_ROOT'],'',$uploadData['file_path']).$uploadData['raw_name'].$uploadData['file_ext'];
				$this->db->where('member_seq', $this->userInfo['member_seq']);
				$result = $this->db->update('fm_member', array("user_icon"=>99, "user_icon_file"=>$file_name));
			}else{
				openDialogAlert($data_used['msg'],400,140,'parent','');
			}
		}

		$callback = "parent.membericonDisplay('{$user_icon}?".time()."');";
		//등록하였습니다.
		openDialogAlert(getAlert('mb087'),400,140,'parent',$callback);
	}

	public function sns_update_id(){
		$params			= $_POST;
		$userid			= $params['userid'];
		$password		= $params['password'];
		$re_password	= $params['re_password'];

		$this->validation->set_rules('userid', '아이디','trim|required|min_length[6]|max_length[20]|xss_clean');
		$this->validation->set_rules('password', '비밀번호','trim|required|min_length[6]|max_length[32]|xss_clean');
		$this->validation->set_rules('re_password', '비밀번호확인','trim|required|min_length[6]|max_length[32]|xss_clean');

		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			echo $err['value'];
			exit;
		}

		$return_result = $this->id_chk('re_chk');
		if	(!$return_result['return']){
			echo $return_result['return_result'];
			exit;
		}

		if	($password != $re_password){
			echo '비밀번호 확인이 일치하지 않습니다';
			exit;
		}

		$return_result = $this->pw_chk('re_chk');

		if	(!$return_result['return']){
			echo $return_result['return_result'];
			exit;
		}

		$sess_data		= $this->session->userdata;

		$password		= hash('sha256',md5($password));

		$this->db->where('member_seq', $sess_data['user']['member_seq']);
		$update_date	= date('Y-m-d H:i:s');
		$sql = "update
					fm_member set
								userid = '".$userid."',
								password = '".$password."',
								sns_change = 1,
								update_date = '".$update_date."'
					where
						member_seq = '".$sess_data['user']['member_seq']."' and
						rute != 'none' and
						sns_change = 0 ";


		$result = $this->db->query($sql);


		echo 'succ';




	}
	
	private function set_upload_options($upload_path) {
	    if(!is_dir($upload_path)) {
	        mkdir($upload_path, 0777, true);
	    }
	    $config = array();
	    $config['upload_path'] = $upload_path;
	    $config['allowed_types'] = 'gif|jpg|png|pdf';
	    $config['max_size'] = '0';
	    $config['max_width'] = '0';
	    $config['max_height'] = '0';
	    $config['overwrite'] = FALSE;
	    
	    return $config;
	}
}

/* End of file member_process.php */
/* Location: ./app/controllers/member_process.php */