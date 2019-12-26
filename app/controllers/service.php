<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);
class service extends front_base {

	public function main_index()
	{
		redirect("/service/index");
	}

	public function index()
	{

	}

	/* 고객센터 */
	public function cs()
	{
		$arr = config_load('bank');
		if($arr) foreach(config_load('bank') as $k => $v){
			list($tmp) = code_load('bankCode',$v['bank']);
			$v['bank'] = $tmp['value'];
			$bank[] = $v;
		}
		
		// 반응형에선 left 메뉴 따로 정의 :: 2019-02-13 pjw
		if($this->config_system['operation_type'] == 'light'){
			$this->template->define('board_lnb', $this->skin."/_modules/common/board_lnb.html");
		}

		$this->template->assign(array('bank'=>$bank));
		$this->print_layout($this->template_path());
	}

	/* 회사소개 */
	public function company(){
		$this->print_layout($this->template_path());
	}

	/* 이용약관 */
	public function agreement(){
		$arrBasic = ($this->config_basic)?$this->config_basic:config_load('basic');

		//20170920 shopName -> companyName 으로 변경(db쪽에 shopName 치환코드가 있는 관계로 소스에서만 설정) ldb
		$this->template->assign('shopName',$arrBasic['companyName']);

		$data = config_load('member');
		$data['agreement'] = str_replace("{shopName}",$arrBasic['companyName'],$data['agreement']);

		$this->template->assign($data);
		$this->print_layout($this->template_path());
	}

	/* 개인정보처리방침 */
	public function privacy(){
		$arrBasic = ($this->config_basic)?$this->config_basic:config_load('basic');

		//20170920 shopName -> companyName 으로 변경(db쪽에 shopName 치환코드가 있는 관계로 소스에서만 설정) ldb
		$this->template->assign('shopName',$arrBasic['companyName']);

		$data = config_load('member');
		$data['privacy'] = str_replace("{shopName}",$arrBasic['companyName'],$data['privacy']);
		$data['privacy'] = str_replace("{domain}",$arrBasic['domain'],$data['privacy']);
		
		//개인정보 관련 문구개선 @2016-09-06 ysm
		$data['privacy'] = str_replace("{책임자명}",$arrBasic['member_info_manager'],$data['privacy']);
		$data['privacy'] = str_replace("{책임자담당부서}",$arrBasic['member_info_part'],$data['privacy']);
		$data['privacy'] = str_replace("{책임자직급}",$arrBasic['member_info_rank'],$data['privacy']);
		$data['privacy'] = str_replace("{책임자연락처}",$arrBasic['member_info_tel'],$data['privacy']);
		$data['privacy'] = str_replace("{책임자이메일}",$arrBasic['member_info_email'],$data['privacy']);

		$this->template->assign($data);
		$this->print_layout($this->template_path());
	}

	/* 이용안내 */
	public function guide(){
		$this->load->model("providershipping");
		$data = $this->providershipping->get_provider_shipping('1');
		$this->template->assign('deliveryCompanyName',$data['deliveryCompanyCodeMapping']?$data['deliveryCompanyCodeMapping'][$data['deliveryCompanyCode'][0]]:'');

		// 반송주소 가져오기
		$this->load->model("shippingmodel");
		$shippingBase = $this->shippingmodel->get_shipping_base();
		$shippingAddress = $this->shippingmodel->get_shipping_address($shippingBase['refund_address_seq'], $shippingBase['refund_scm_type']);
		$shippingAddressText = $shippingAddress['address_zipcode'] . ' ' . 
											($shippingAddress['address_type']=='street' ? $shippingAddress['address_street'] : $shippingAddress['address']) . 
											($shippingAddress['address_detail'] ? ' ' . $shippingAddress['address_detail'] : '');
		$this->template->assign("config_shipping",$shippingAddressText);

		$this->print_layout($this->template_path());
	}

	/* 제휴안내 */
	public function partnership(){
		$this->print_layout($this->template_path());
	}


	public function partnership_send(){

		$file_path	= "../../data/email/".get_lang(true)."/partnership.html";
		$_POST["zipcode"] = $_POST["recipient_zipcode"][0]."-".$_POST["recipient_zipcode"][1];
		$this->template->assign(array('order'=>$_POST));
		$this->template->compile_dir = ROOTPATH."data/email/".get_lang(true)."/";
		$this->template->define(array('tpl'=>$file_path));
		$bodyTpl = $this->template->fetch('tpl');
		$body	= trim($bodyTpl);
		$body	= preg_replace("/\/data\/mail/", $domain."/data/mail", $body);
		$body	= str_replace("http://http://", "http://", $body);

		$email = config_load('email');

		$email['partnership_skin'] = $out;

		$adminEmail = $basic['partnershipEmail']?$basic['partnershipEmail']:$basic['companyEmail'];

		require_once $_SERVER['DOCUMENT_ROOT']."/app/libraries/Email_send.class.php";
		$mail		= new Mail(isset($params));
		$basic = ($this->config_basic)?$this->config_basic:config_load('basic');
		$headers['From']		= $_POST["email"];
		$headers['Name']	= $_POST["writer"];
		$headers['Subject'] = "[".$_POST["company"]."]".$_POST["qtype"]." 문의입니다.";
		$headers['To']			= $basic['partnershipEmail'];//"kbm@gabia.com; ".
		$resSend = $mail->send($headers, $body);

		if($resSend){
			$callback = "parent.document.location.reload();";
			//문의가 접수되었습니다.
			openDialogAlert(getAlert('et023'),400,140,'parent',$callback);
		}else{
			//문의가 접수중 에러가 발생되었습니다<br>잠시 후 다시 시도하여 주십시오.
			openDialogAlert(getAlert('et024'),400,140,'parent',$callback);
		}


	}

	public function policy(){
		if( defined('__ISUSER__') != true ) {
			$arrBasic = ($this->config_basic)?$this->config_basic:config_load('basic');
			$member = config_load('member');
			
			//20170920 shopName -> companyName 으로 변경(db쪽에 shopName 치환코드가 있는 관계로 소스에서만 설정) ldb
			//비회원 개인정보 수집-이용 약관동의 추가
			$privacy['policy'] = str_replace("{domain}",$arrBasic['domain'],str_replace("{shopName}",$arrBasic['companyName'],$member['policy']));
		}
		$this->template->assign($privacy);
		$this->print_layout($this->template_path());
	}

	public function cancellation(){
		$arrOrder = config_load('order');

		//20170920 shopName -> companyName 으로 변경(db쪽에 shopName 치환코드가 있는 관계로 소스에서만 설정) ldb
		$privacy['cancellation'] = str_replace("{domain}",$arrBasic['domain'],str_replace("{shopName}",$arrBasic['companyName'],$arrOrder['cancellation']));
		$this->template->assign($privacy);
		$this->print_layout($this->template_path());
	}
}

