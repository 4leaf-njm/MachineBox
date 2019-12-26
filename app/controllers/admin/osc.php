<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class osc extends admin_base {

	public function __construct() {
		parent::__construct();
		$this->load->model('membermodel');
		$this->load->helper(array('form', 'url', 'mail', 'sms'));
	}

	public function index()
	{
		redirect("/admin/osc/osc_status");
	}

	public function osc_status() {
	    $this->admin_menu();
	    $this->tempate_modules();
	    
	    $file_path	= $this->template_path();
	    
	    $query = "select * from fm_cm_machine_outsourcing a, fm_cm_machine_category b where a.cate_seq = b.cate_seq order by reg_date desc";
	    $query = $this->db->query($query);
	    $osc_list = $query->result_array();
	    
	    foreach($osc_list as &$row) {
	        $query = "select count(*) as apply_cnt from fm_cm_machine_partner_osc where osc_seq = ".$row['osc_seq'];
	        $query = $this->db->query($query);
	        $result = $query->row_array();
	        $row['apply_cnt'] = $result['apply_cnt'];
	        
	        $query = "select * from fm_cm_machine_partner_osc a, fm_cm_machine_partner b where a.partner_seq = b.partner_seq and osc_seq = ".$row['osc_seq']." order by state desc, a.reg_date desc limit 1";
	        $query = $this->db->query($query);
	        $result = $query->row_array();
	        $row['ptn_userid'] = $result['userid'];
	        $row['ptn_state'] = $result['state'];
	    }
	    
	    $this->template->assign('osc_list', $osc_list);
	    $this->template->define(array('tpl'=>$file_path));
	    $this->template->print_("tpl");
	}
	
	public function osc_permit_process() {
	    $osc_seq = $this->input->post('osc_seq');
	    $state = $this->input->post('state');
	    $message = $this->input->post('message');
	    
	    $query = "select * from fm_cm_machine_outsourcing where osc_seq = ".$osc_seq;
	    $query = $this->db->query($query);
	    $result = $query->row_array();
	    
	    $data = array(
	        'permit_yn' => $state
	    );
	    
	    if($state == 'y') {
	        $html = '승인';
	        $str_state = '승인';
	        $title = "외주등록 <b>승인</b>";
	        $data['permit_date'] = date('Y-m-d H:i:s');
	    } else if($state == 'n') {
	        $html = '<span style=\"color:#FF4848;\">미승인</span>';
	        $str_state = '미승인';
	        $title = "외주등록 <b>미승인</b>";
	    }
	    $this->db->where('osc_seq', $osc_seq);
	    $this->db->update('fm_cm_machine_outsourcing', $data);
	    
	    $userData = $this->getUserData($result['userid']);
	    $this->send_common_mail($userData['email'], $title, $message);
	    $this->send_common_sms($userData['cellphone'], $message);
	    
	    $callback = "parent.document.getElementById('item-state-".$osc_seq."').innerHTML = '".$html."';";
	    openDialogAlert('[' . $str_state . '] 상태로 변경이 완료되었습니다.',400,140,'parent',$callback);
	}
	
	public function osc_regist() {
	    $this->admin_menu();
	    $this->tempate_modules();
	    
	    $file_path	= $this->template_path();
	    
	    $osc_seq = $this->input->get('seq');
	    $reg_mode = $this->input->get('reg_mode');
	    
	    $query = $this->db->get('fm_cm_machine_area');
	    $result = $query->result_array();
	    $this->template->assign('area_list', $result);
	    
	    $query = $this->db->get('fm_cm_machine_category');
	    $cate_list = $query->result_array();
	    $this->template->assign('cate_list', $cate_list);
	    
	    $query = $this->db->get('fm_cm_machine_category_sub');
	    $result = $query->result_array();
	    
	    $cate_sub_list = array();
	    foreach($cate_list as $row) {
	        $this->db->where('cate_seq', $row['cate_seq']);
	        $query = $this->db->get('fm_cm_machine_category_sub');
	        $sub_list = $query->result_array();
	        if(!empty($sub_list))
	            $cate_sub_list[] = $sub_list;
	    }
	    $this->template->assign('cate_sub_list', $cate_sub_list);
	    
	    $this->template->assign('osc_seq', $osc_seq);
	    $this->template->assign('reg_mode', $reg_mode);
	    
	    $this->template->define(array('tpl'=>$file_path));
	    $this->template->print_("tpl");
	}
	
	public function ajax_get_osc() {
	    header("Content-Type: application/json");
	    
	    $osc_seq = $this->input->post('osc_seq');
	    
	    $query = "select * from fm_cm_machine_outsourcing a, fm_cm_machine_category b, fm_cm_machine_area d ".
	             "where a.cate_seq = b.cate_seq and a.area_seq = d.area_seq and osc_seq = ".$osc_seq;
	    $query = $this->db->query($query);
	    $osc_item = $query->row_array();
	    
	    $query2 = "select * from fm_cm_machine_outsourcing_picture where osc_seq = ".$osc_item['osc_seq'];
	    $query2 = $this->db->query($query2);
	    $result = $query2->result_array();
	    $osc_item['picture_list'] = $result;
	    
	    echo json_encode($osc_item);
	}
	
	public function osc_regist_process() {
	    $reg_mode = $this->input->post('reg_mode');
	    $osc_seq = $this->input->post('osc_seq');
	    $userid = $this->input->post('userid');
	    $cate_seq = $this->input->post('cate_seq');
	    $cate_sub = $this->input->post('cate_sub');
	    $area_seq = $this->input->post('area_seq');
	    $osc_name = $this->input->post('osc_name');
	    $expect_date = $this->input->post('expect_date');
	    $budget = $this->input->post('budget');
	    $osc_content = $this->input->post('osc_content');
	    $osc_tech_arr = $this->input->post('osc_tech_arr');
	    $osc_end_date = $this->input->post('osc_end_date');
	    $start_expect_date = $this->input->post('start_expect_date');
	    
	    $osc_tech = '';
	    foreach($osc_tech_arr as $value) {
            $osc_tech .= $osc_tech == '' ? $value : ",".$value;	        
	    }
	    
	    $osc_data = array();
	    $osc_data['userid'] = $userid;
	    $osc_data['cate_seq'] = $cate_seq;
	    $osc_data['cate_sub'] = $cate_sub;
	    $osc_data['area_seq'] = $area_seq;
	    $osc_data['osc_name'] = $osc_name;
	    $osc_data['expect_date'] = $expect_date;
	    $osc_data['budget'] = $budget;
	    $osc_data['osc_tech'] = $osc_tech;
	    $osc_data['osc_content'] = $osc_content;
	    $osc_data['osc_end_date'] = $osc_end_date;
	    $osc_data['start_expect_date'] = $start_expect_date;
	    
	    $callback = "parent.location.reload()";
	    if($reg_mode == 'insert') {
	        $osc_data['osc_no'] = $this->getOscNo($cate_seq);
	        $this->db->insert('fm_cm_machine_outsourcing', $osc_data);
	        $osc_seq = $this->db->insert_id();
    	    openDialogAlert('등록이 완료되었습니다.',400,140,'parent',$callback);
	    } else if ($reg_mode == 'modify') {
	        $query = "select * from fm_cm_machine_outsourcing a where osc_seq = ".$osc_seq;
	        $query = $this->db->query($query);
	        $result = $query->row_array();
	        if($result['cate_seq'] != $cate_seq)
	            $osc_data['osc_no'] = $this->getOscNo($cate_seq);
    	    $this->db->where('osc_seq', $osc_seq);
    	    $this->db->update('fm_cm_machine_outsourcing', $osc_data);
    	    openDialogAlert('변경이 완료되었습니다.',400,140,'parent',$callback);
	    }
	}
	
	public function osc_delete_process() {
	    $osc_seq = $this->input->post('osc_seq');
	    
	    $this->db->where('osc_seq', $osc_seq);
	    $this->db->delete('fm_cm_machine_outsourcing');
	    
	    $this->db->where('osc_seq', $osc_seq);
	    $this->db->delete('fm_cm_machine_outsourcing_picture');
	    
	    $this->db->where('osc_seq', $osc_seq);
	    $this->db->delete('fm_cm_machine_outsourcing_request');
	    
	    $callback = "parent.location.reload()";
	    openDialogAlert('삭제가 완료되었습니다.',400,140,'parent',$callback);
	}
	
	public function osc_apply() {
	    $this->admin_menu();
	    $this->tempate_modules();
	    
	    $file_path	= $this->template_path();
	    
	    $query = "select * from fm_cm_machine_outsourcing a, fm_cm_machine_category b where a.cate_seq = b.cate_seq and permit_yn = 'y' order by reg_date desc";
	    $query = $this->db->query($query);
	    $osc_list = $query->result_array();
	    
	    foreach($osc_list as &$row) {
	        $query = "select *, a.reg_date as apply_date from fm_cm_machine_partner_osc a, fm_cm_machine_partner b where a.partner_seq = b.partner_seq and a.osc_seq = ".$row['osc_seq'];
	        $query = $this->db->query($query);
	        $result = $query->result_array();
	        $row['apply_list'] = $result;
	    }
	    
	    $this->template->assign('osc_list', $osc_list);
	    $this->template->define(array('tpl'=>$file_path));
	    $this->template->print_("tpl");
	}
	
	public function get_osc_apply_list() {
	    header("Content-Type: application/json");
	    
	    $osc_seq = $this->input->post('osc_seq');
	    
	    $query = "select *, a.reg_date as reg_date_1, b.reg_date as reg_date_2 from fm_cm_machine_partner a, fm_cm_machine_partner_osc b where a.partner_seq = b.partner_seq and osc_seq = ".$osc_seq." order by b.state desc, b.reg_date desc";
	    $query = $this->db->query($query);
	    $apply_list = $query->result_array();
	    
	    echo json_encode(array('apply_list' => $apply_list, 'apply_cnt' => count($apply_list)));
	}
	
	public function ptn_status() {
	    $this->admin_menu();
	    $this->tempate_modules();
	    
	    $file_path	= $this->template_path();
	    
	    $query = "select * from fm_cm_machine_partner a, fm_cm_machine_category b where a.cate_seq = b.cate_seq order by reg_date desc";
	    $query = $this->db->query($query);
	    $ptn_list = $query->result_array();
	    
	    foreach($ptn_list as &$row) {
	        $query = "select count(*) as grade_cnt from fm_cm_machine_partner_eval where partner_seq = ".$row['partner_seq'];
	        $query = $this->db->query($query);
	        $row['grade_cnt'] = $query->row()->grade_cnt;
	        
	        $query = "select count(*) as finish_cnt from fm_cm_machine_partner_osc where state = 3 and partner_seq = ".$row['partner_seq'];
	        $query = $this->db->query($query);
	        $row['finish_cnt'] = $query->row()->finish_cnt;
	    }
	    
	    $this->template->assign('ptn_list', $ptn_list);
	    $this->template->define(array('tpl'=>$file_path));
	    $this->template->print_("tpl");
	}
	
	public function ptn_modify() {
	    $this->admin_menu();
	    $this->tempate_modules();
	    
	    $file_path	= $this->template_path();
	    
	    $partner_seq = $this->input->get('seq');
	    
	    $this->template->assign('partner_seq', $partner_seq);
	    
	    $query = $this->db->get('fm_cm_machine_area');
	    $result = $query->result_array();
	    $this->template->assign('area_list', $result);
	    
	    $query = $this->db->get('fm_cm_machine_category');
	    $cate_list = $query->result_array();
	    $this->template->assign('cate_list', $cate_list);
	    
	    $this->template->define(array('tpl'=>$file_path));
	    $this->template->print_("tpl");
	}
	
	public function ajax_get_ptn() {
	    header("Content-Type: application/json");
	    
	    $ptn_seq = $this->input->post('ptn_seq');
	    
	    $query = "select * from fm_cm_machine_partner a, fm_cm_machine_category b, fm_cm_machine_area c ".
	   	    "where a.cate_seq = b.cate_seq and a.area_seq = c.area_seq and partner_seq = ".$ptn_seq;
	    
	    $query = $this->db->query($query);
	    $ptn_item = $query->row_array();
	    
	    $query2 = "select * from fm_cm_machine_partner_certificate where partner_seq = ".$ptn_item['partner_seq']." order by cert_seq asc";
	    $query2 = $this->db->query($query2);
	    $cert_list = $query2->result_array();
	    $ptn_item['cert_list'] = $cert_list;
	    
	    $query2 = "select * from fm_cm_machine_partner_portfolio where partner_seq = ".$ptn_item['partner_seq']." order by pofol_seq asc";
	    $query2 = $this->db->query($query2);
	    $pofol_list = $query2->result_array();
	    
	    foreach($pofol_list as &$row) {
	        $query2 = "select * from fm_cm_machine_partner_portfolio_picture where pofol_seq = ".$row['pofol_seq']." order by picture_seq asc";
	        $query2 = $this->db->query($query2);
	        $result = $query2->result_array();
	        $row['picture_list'] = $result;
	    }
	    $ptn_item['pofol_list'] = $pofol_list;
	    
	    echo json_encode($ptn_item);
	}
	
	public function ptn_modify_process() {
	    $cert_name_arr = $this->input->post('cert_name_arr');
	    $cert_org_arr = $this->input->post('cert_org_arr');
	    $cert_date_arr = $this->input->post('cert_date_arr');
	    $cert_tech_arr = $this->input->post('cert_tech_arr');
	    $pofol_name_arr = $this->input->post('pofol_name_arr');
	    $pofol_cate_arr = $this->input->post('pofol_cate_arr');
	    $start_date_arr = $this->input->post('start_date_arr');
	    $end_date_arr = $this->input->post('end_date_arr');
	    $pofol_content_arr = $this->input->post('pofol_content_arr');
	    $pofol_picture_seq = $this->input->post('pofol_picture_seq');

	    $ptn_seq = $this->input->post('ptn_seq');
	    $area_seq = $this->input->post('area_seq');
	    $career_year = $this->input->post('career_year');
	    $career_type = $this->input->post('career_type');
	    $cate_seq = $this->input->post('cate_seq');
	    $main_service = $this->input->post('main_service');
	    $introduce = $this->input->post('introduce');
	    
	    $partner_data = array();
	    $partner_data['area_seq'] = $area_seq;
	    $partner_data['career_year'] = $career_year;
	    $partner_data['career_type'] = $career_type;
	    $partner_data['cate_seq'] = $cate_seq;
	    $partner_data['main_service'] = $main_service;
	    $partner_data['introduce'] = $introduce;
	    
	    $this->load->library('upload');   
		$files = $_FILES;	

	    $upload_path = "./data/uploads/profile";
	    $filename = 'profile_image';
	    

	    $this->upload->initialize($this->set_upload_options($upload_path));
	    if($this->upload->do_upload($filename)) {
	        $upload_data = $this->upload->data();
	        $partner_data['profile_path'] = str_replace(".", "", $upload_path).'/'.$upload_data['file_name'];
	    }

	    $this->db->where('partner_seq', $ptn_seq);
	    $this->db->update('fm_cm_machine_partner', $partner_data);
	    
	    $this->db->where('partner_seq', $ptn_seq);
	    $this->db->delete('fm_cm_machine_partner_certificate');
	    for($i=0; $i<count($cert_name_arr); $i++) {
	        $cert_data = array(
	            'partner_seq' => $ptn_seq,
	            'cert_name' => $cert_name_arr[$i],
	            'cert_date' => $cert_date_arr[$i],
	            'cert_org' => $cert_org_arr[$i],
	        );
	        $this->db->insert('fm_cm_machine_partner_certificate', $cert_data);
	    }
	    
	    $seq_list = "";
	    for($i=0; $i<count($pofol_picture_seq); $i++) {
	    	$seq_list .= $seq_list == "" ? $pofol_picture_seq[$i] : ", ".$pofol_picture_seq[$i];
	    }
	    $query = "select * from fm_cm_machine_partner_portfolio where partner_seq = ".$ptn_seq;
	    $query = $this->db->query($query);
	    $result = $query->result_array();
	    $seq_arr = array();
	    foreach($result as $row) {
	    	$query = "delete from fm_cm_machine_partner_portfolio_picture where pofol_seq = ".$row['pofol_seq']." and picture_seq not in(".$seq_list.")";
	    	$this->db->query($query);
	   		$seq_arr[] = $row['pofol_seq'];
	    }

	    $this->db->where('partner_seq', $ptn_seq);
	    $this->db->delete('fm_cm_machine_partner_portfolio');
	    for($i=0; $i<count($pofol_name_arr); $i++) {
	        $pofol_data = array(
	            'partner_seq' => $ptn_seq,
	            'pofol_name' => $pofol_name_arr[$i],
	            'pofol_cate' => $pofol_cate_arr[$i],
	            'start_date' => $start_date_arr[$i],
	            'end_date' => $end_date_arr[$i],
	            'pofol_content' => $pofol_content_arr[$i]
	        );
	        $this->db->insert('fm_cm_machine_partner_portfolio', $pofol_data);
	        $pofol_seq = $this->db->insert_id();

	        $upload_path = "./data/uploads/portfolio";
            $filename = 'pofol_picture_'.($i+1);
            $cnt = count($_FILES[$filename]['name']);
           
            $seq_data = array(
            	'pofol_seq' => $pofol_seq
            );
			$this->db->where('pofol_seq', $seq_arr[$i]);
	   		$this->db->update('fm_cm_machine_partner_portfolio_picture', $seq_data);

            for($j=0; $j<$cnt; $j++) {
                if($files[$filename]['name'][$j] == null) continue;
                
                $_FILES[$filename]['name'] = $files[$filename]['name'][$j];
                $_FILES[$filename]['type'] = $files[$filename]['type'][$j];
                $_FILES[$filename]['tmp_name'] = $files[$filename]['tmp_name'][$j];
                $_FILES[$filename]['error'] = $files[$filename]['error'][$j];
                $_FILES[$filename]['size'] = $files[$filename]['size'][$j];
                
                $this->upload->initialize($this->set_upload_options($upload_path));
                if($this->upload->do_upload($filename)) {
                    $upload_data = $this->upload->data();
                    $picture_data = array();
                    $picture_data['pofol_seq'] = $pofol_seq;
                    $picture_data['path'] = str_replace(".", "", $upload_path).'/'.$upload_data['file_name'];
                    $this->db->insert('fm_cm_machine_partner_portfolio_picture', $picture_data);
                } 
            }
	    }
	    pageRedirect("/admin/osc/ptn_modify?seq=".$ptn_seq);
        $this->session->set_flashdata('message', '변경이 완료되었습니다.');
	}
	
	public function ptn_apply() {
	    $this->admin_menu();
	    $this->tempate_modules();
	    
	    $file_path	= $this->template_path();
	    
	    $query = "select * from fm_cm_machine_outsourcing_request order by reg_date desc";
	    $query = $this->db->query($query);
	    $req_list = $query->result_array();
	    
	    foreach($req_list as &$row) {
	        $query = "select * from fm_cm_machine_outsourcing a, fm_cm_machine_category b where a.cate_seq = b.cate_seq and a.osc_seq = ".$row['osc_seq'];
	        $query = $this->db->query($query);
	        $osc_info = $query->row_array();
	        $row['osc_info'] = $osc_info;
	        
	        $query = "select * from fm_cm_machine_partner a, fm_cm_machine_category b where a.cate_seq = b.cate_seq and a.partner_seq = ".$row['partner_seq'];
	        $query = $this->db->query($query);
	        $ptn_info = $query->row_array();
        
	        $query = "select count(*) as grade_cnt from fm_cm_machine_partner_eval where partner_seq = ".$ptn_info['partner_seq'];
            $query = $this->db->query($query);
            $ptn_info['grade_cnt'] = $query->row()->grade_cnt;
            
            $query = "select count(*) as finish_cnt from fm_cm_machine_partner_osc where state = 3 and partner_seq = ".$ptn_info['partner_seq'];
            $query = $this->db->query($query);
            $ptn_info['finish_cnt'] = $query->row()->finish_cnt;
            
	        $row['ptn_info'] = $ptn_info;
	    }
	    
	    $this->template->assign('req_list', $req_list);
	    $this->template->define(array('tpl'=>$file_path));
	    $this->template->print_("tpl");
	}
	
	public function ptn_apply_permit() {
	    $oreq_seq = $this->input->post('oreq_seq');
	    
	    $data = array(
	        'pres_state' => '1',
	        'permit_date' => date('Y-m-d H:i:s')
	    );
	    $this->db->where('oreq_seq', $oreq_seq);
	    $this->db->update('fm_cm_machine_outsourcing_request', $data);
	    
	    $callback = "parent.location.reload()";
	    openDialogAlert('승인이 완료되었습니다.',400,140,'parent',$callback);
	}
	
	public function get_ptn_finish_list() {
	    header("Content-Type: application/json");
	    
	    $partner_seq = $this->input->post('partner_seq');
	    
	    $query = "select * from fm_cm_machine_partner_osc a, fm_cm_machine_outsourcing b where a.osc_seq = b.osc_seq and a.state = 3 and a.partner_seq = ".$partner_seq." order by a.finish_date desc";
	    $query = $this->db->query($query);
	    $finish_list = $query->result_array();
	    
	    echo json_encode(array('finish_list' => $finish_list));
	}
	
	public function get_ptn_grade_list() {
	    header("Content-Type: application/json");
	    
	    $partner_seq = $this->input->post('partner_seq');
	    
	    $query = "select * from fm_cm_machine_partner_eval where partner_seq = ".$partner_seq." order by reg_date desc";
	    $query = $this->db->query($query);
	    $grade_list = $query->result_array();
	    
	    echo json_encode(array('grade_list' => $grade_list));
	}
	
	public function osc_excel_download() {
	    $osc_seq = $this->input->get('seq');
	    
	    $query = "select * from fm_cm_machine_outsourcing a, fm_cm_machine_category b, fm_cm_machine_area c where a.cate_seq = b.cate_seq and a.area_seq = c.area_seq and osc_seq = ".$osc_seq." order by reg_date desc";
	    $query = $this->db->query($query);
	    $osc_item = $query->row_array();
	    
	    $query = "select count(*) as apply_cnt from fm_cm_machine_partner_osc where osc_seq = ".$osc_seq;
        $query = $this->db->query($query);
        $result = $query->row_array();
        $osc_item['apply_cnt'] = $result['apply_cnt'];
        
        $query = "select * from fm_cm_machine_partner_osc a, fm_cm_machine_partner b where a.partner_seq = b.partner_seq and osc_seq = ".$osc_seq." order by state desc, a.reg_date desc limit 1";
        $query = $this->db->query($query);
        $result = $query->row_array();
        $osc_item['ptn_userid'] = $result['userid'];
        $osc_item['ptn_state'] = $result['state'];
	    
	    ini_set('memory_limit', '5120M');
	    set_time_limit(0);
	    
	    if($osc_item['state'] == '1') {
	        $osc_state = '모집중';
	    } else {
	        $osc_state = '모집마감';
	    }
	    if($osc_item['permit_yn'] == 'y') {
	        $osc_permit = '승인';
	    } else {
	        $osc_permit = '미승인';
	    }
	    if($osc_item['ptn_state'] == '0') {
	        $ptn_state = '대기';
	    } else if($osc_item['ptn_state'] == '1') {
	        $ptn_state = '미팅';
	    } else if($osc_item['ptn_state'] == '2') {
	        $ptn_state = '계약';
	    } else if($osc_item['ptn_state'] == '3') {
	        $ptn_state = '완료';
	    } else {
	        $ptn_state = '-';
	    }
	    if(empty($osc_item['ptn_userid'])) {
	        $ptn_userid = '-';
	    } else {
	        $ptn_userid = $osc_item['ptn_userid'];
	    }
	    
	    $this->load->library('pxl');
	    $this->objPHPExcel = new PHPExcel();
	    
	    # 시트지정
	    $this->objPHPExcel->setActiveSheetIndex(0);
	    $this->objPHPExcel->getActiveSheet()->setTitle('Sheet1');
	    # cell 헤더 설정
	    $this->objPHPExcel->getActiveSheet()->setCellValue('A1', '등록일');
	    $this->objPHPExcel->getActiveSheet()->setCellValue('B1', '등록번호');
	    $this->objPHPExcel->getActiveSheet()->setCellValue('C1', '아이디');
	    $this->objPHPExcel->getActiveSheet()->setCellValue('D1', '카테고리');
	    $this->objPHPExcel->getActiveSheet()->setCellValue('E1', '세부카테고리');
	    $this->objPHPExcel->getActiveSheet()->setCellValue('F1', '외주명');
        $this->objPHPExcel->getActiveSheet()->setCellValue('G1', '예상기간');
        $this->objPHPExcel->getActiveSheet()->setCellValue('H1', '지출예산');
        $this->objPHPExcel->getActiveSheet()->setCellValue('I1', '외주업무 지역');
        $this->objPHPExcel->getActiveSheet()->setCellValue('J1', '외주업무 내용');
        $this->objPHPExcel->getActiveSheet()->setCellValue('K1', '관련 기술');
        $this->objPHPExcel->getActiveSheet()->setCellValue('L1', '모집 마감일');
        $this->objPHPExcel->getActiveSheet()->setCellValue('M1', '업무시작 예상일');
        $this->objPHPExcel->getActiveSheet()->setCellValue('N1', '지원자수');
        $this->objPHPExcel->getActiveSheet()->setCellValue('O1', '진행상태');
        $this->objPHPExcel->getActiveSheet()->setCellValue('P1', '수주사');
        $this->objPHPExcel->getActiveSheet()->setCellValue('Q1', '계약금액');
        $this->objPHPExcel->getActiveSheet()->setCellValue('R1', '모집여부');
        $this->objPHPExcel->getActiveSheet()->setCellValue('S1', '승인상태');
	        
	    $this->objPHPExcel->getActiveSheet()->getStyle('A1:S1')->getFont()->setSize(10);
	    $this->objPHPExcel->getActiveSheet()->getStyle('A1:S1')->getFont()->setBold(true);
	    $this->objPHPExcel->getActiveSheet()->getStyle('A1:S1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('F0F0F0');
	    $this->objPHPExcel->getActiveSheet()->getStyle('A1:S1')->getBorders()->getBottom()->getColor()->setRGB('5A5A5A');
	    $this->objPHPExcel->getActiveSheet()->getStyle('A1:S1')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
	    $this->objPHPExcel->getActiveSheet()->getStyle('A1:S1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	    $this->objPHPExcel->getActiveSheet()->getStyle('A1:S1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	    $this->objPHPExcel->getActiveSheet()->getStyle('A1:S2')->getAlignment()->setWrapText(true);
	    $this->objPHPExcel->getActiveSheet()->getStyle('A2:S2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	    $this->objPHPExcel->getActiveSheet()->getStyle('A2:S2')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	    $this->objPHPExcel->getActiveSheet()->getStyle('B2')->getNumberFormat()->setFormatCode('0000000000');
	    $this->objPHPExcel->getActiveSheet()->getDefaultRowDimension() -> setRowHeight(40);
	    $this->objPHPExcel->getActiveSheet()->getRowDimension(1) -> setRowHeight(20);
	    $this->objPHPExcel->getActiveSheet()->getRowDimension(2) -> setRowHeight(100);
	    $this->objPHPExcel->getActiveSheet()->getColumnDimension("A") -> setWidth(20);
	    $this->objPHPExcel->getActiveSheet()->getColumnDimension("B") -> setWidth(15);
	    $this->objPHPExcel->getActiveSheet()->getColumnDimension("C") -> setWidth(15);
	    $this->objPHPExcel->getActiveSheet()->getColumnDimension("D") -> setWidth(15);
	    $this->objPHPExcel->getActiveSheet()->getColumnDimension("E") -> setWidth(20);
	    $this->objPHPExcel->getActiveSheet()->getColumnDimension("F") -> setWidth(25);
	    $this->objPHPExcel->getActiveSheet()->getColumnDimension("G") -> setWidth(15);
	    $this->objPHPExcel->getActiveSheet()->getColumnDimension("H") -> setWidth(15);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("I") -> setWidth(15);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("J") -> setWidth(50);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("K") -> setWidth(20);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("L") -> setWidth(20);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("M") -> setWidth(20);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("N") -> setWidth(15);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("O") -> setWidth(15);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("P") -> setWidth(20);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("Q") -> setWidth(15);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("R") -> setWidth(15);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("S") -> setWidth(15);
	    
	    $n=2;
	        
        $this->objPHPExcel->getActiveSheet()->setCellValue('A'.$n, $osc_item['reg_date']);
        $this->objPHPExcel->getActiveSheet()->setCellValue('B'.$n, $osc_item['osc_no']);
        $this->objPHPExcel->getActiveSheet()->setCellValue('C'.$n, $osc_item['userid']);
        $this->objPHPExcel->getActiveSheet()->setCellValue('D'.$n, $osc_item['cate_name']);
        $this->objPHPExcel->getActiveSheet()->setCellValue('E'.$n, $osc_item['cate_sub']);
        $this->objPHPExcel->getActiveSheet()->setCellValue('F'.$n, $osc_item['osc_name']);
        $this->objPHPExcel->getActiveSheet()->setCellValue('G'.$n, $osc_item['expect_date']);
        $this->objPHPExcel->getActiveSheet()->setCellValue('H'.$n, $this->price_format($osc_item['budget'])."원");
        $this->objPHPExcel->getActiveSheet()->setCellValue('I'.$n, $osc_item['area_name']);
        $this->objPHPExcel->getActiveSheet()->setCellValue('J'.$n, $osc_item['osc_content']);
        $this->objPHPExcel->getActiveSheet()->setCellValue('K'.$n, $osc_item['osc_tech']);
        $this->objPHPExcel->getActiveSheet()->setCellValue('L'.$n, $osc_item['osc_end_date']);
        $this->objPHPExcel->getActiveSheet()->setCellValue('M'.$n, $osc_item['start_expect_date']);
        $this->objPHPExcel->getActiveSheet()->setCellValue('N'.$n, $osc_item['apply_cnt']."명");
        $this->objPHPExcel->getActiveSheet()->setCellValue('O'.$n, $ptn_state);
        $this->objPHPExcel->getActiveSheet()->setCellValue('P'.$n, $ptn_userid);
        $this->objPHPExcel->getActiveSheet()->setCellValue('Q'.$n, '-');
        $this->objPHPExcel->getActiveSheet()->setCellValue('R'.$n, $osc_state);
        $this->objPHPExcel->getActiveSheet()->setCellValue('S'.$n, $osc_permit);
	  
	    $filename = '외주정보_'.date('Ymd').'.xls';
	    header("Content-charset=utf-8");
	    header("Pragma: public");
	    header("Expires: 0");
	    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	    header("Content-Type: application/force-download");
	    header("Content-Type: application/octet-stream");
	    header("Content-Type: application/download");
	    header('Content-Disposition: attachment;filename="'.urlencode($filename).'"');
	    header("Content-Transfer-Encoding: binary");
	    
	    $objWriter = IOFactory::createWriter($this->objPHPExcel, "Excel5");
	    $objWriter->save('php://output');
	}
	
	public function ptn_excel_download() {
	    $ptn_seq = $this->input->get('seq');
	    
	    $query = "select * from fm_cm_machine_partner a, fm_cm_machine_category b, fm_cm_machine_area c where a.cate_seq = b.cate_seq and a.area_seq = c.area_seq and partner_seq = ".$ptn_seq." order by reg_date desc";
	    $query = $this->db->query($query);
	    $ptn_item = $query->row_array();
	    
	    $query = "select count(*) as grade_cnt from fm_cm_machine_partner_eval where partner_seq = ".$ptn_seq;
        $query = $this->db->query($query);
        $ptn_item['grade_cnt'] = $query->row()->grade_cnt;
        
        $query = "select count(*) as finish_cnt from fm_cm_machine_partner_osc where state = 3 and partner_seq = ".$ptn_seq;
        $query = $this->db->query($query);
        $ptn_item['finish_cnt'] = $query->row()->finish_cnt;
	    
        if(empty($ptn_item['tech_list'])) {
            $tech_list = '없음';
        } else {
            $tech_list = $ptn_item['tech_list'];
        }
	    ini_set('memory_limit', '5120M');
	    set_time_limit(0);
	    
	    $this->load->library('pxl');
	    $this->objPHPExcel = new PHPExcel();
	    
	    # 시트지정
	    $this->objPHPExcel->setActiveSheetIndex(0);
	    $this->objPHPExcel->getActiveSheet()->setTitle('Sheet1');
	    # cell 헤더 설정
	    $this->objPHPExcel->getActiveSheet()->setCellValue('A1', '등록일');
	    $this->objPHPExcel->getActiveSheet()->setCellValue('B1', '아이디');
	    $this->objPHPExcel->getActiveSheet()->setCellValue('C1', '업력');
	    $this->objPHPExcel->getActiveSheet()->setCellValue('D1', '업태');
	    $this->objPHPExcel->getActiveSheet()->setCellValue('E1', '지역');
	    $this->objPHPExcel->getActiveSheet()->setCellValue('F1', '주서비스');
	    $this->objPHPExcel->getActiveSheet()->setCellValue('G1', '카테고리');
	    $this->objPHPExcel->getActiveSheet()->setCellValue('H1', '보유기술');
	    $this->objPHPExcel->getActiveSheet()->setCellValue('I1', '회사소개');
	    $this->objPHPExcel->getActiveSheet()->setCellValue('J1', '실적');
	    $this->objPHPExcel->getActiveSheet()->setCellValue('K1', '평가');
	    
	    $this->objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getFont()->setSize(10);
	    $this->objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getFont()->setBold(true);
	    $this->objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('F0F0F0');
	    $this->objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getBorders()->getBottom()->getColor()->setRGB('5A5A5A');
	    $this->objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
	    $this->objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	    $this->objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	    $this->objPHPExcel->getActiveSheet()->getStyle('A1:K2')->getAlignment()->setWrapText(true);
	    $this->objPHPExcel->getActiveSheet()->getStyle('A2:K2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	    $this->objPHPExcel->getActiveSheet()->getStyle('A2:K2')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	    $this->objPHPExcel->getActiveSheet()->getDefaultRowDimension() -> setRowHeight(40);
	    $this->objPHPExcel->getActiveSheet()->getRowDimension(1) -> setRowHeight(20);
	    $this->objPHPExcel->getActiveSheet()->getRowDimension(2) -> setRowHeight(100);
	    $this->objPHPExcel->getActiveSheet()->getColumnDimension("A") -> setWidth(20);
	    $this->objPHPExcel->getActiveSheet()->getColumnDimension("B") -> setWidth(20);
	    $this->objPHPExcel->getActiveSheet()->getColumnDimension("C") -> setWidth(15);
	    $this->objPHPExcel->getActiveSheet()->getColumnDimension("D") -> setWidth(25);
	    $this->objPHPExcel->getActiveSheet()->getColumnDimension("E") -> setWidth(15);
	    $this->objPHPExcel->getActiveSheet()->getColumnDimension("F") -> setWidth(25);
	    $this->objPHPExcel->getActiveSheet()->getColumnDimension("G") -> setWidth(20);
	    $this->objPHPExcel->getActiveSheet()->getColumnDimension("H") -> setWidth(20);
	    $this->objPHPExcel->getActiveSheet()->getColumnDimension("I") -> setWidth(50);
	    $this->objPHPExcel->getActiveSheet()->getColumnDimension("J") -> setWidth(12);
	    $this->objPHPExcel->getActiveSheet()->getColumnDimension("K") -> setWidth(12);
	    
	    $n=2;
	    
	    $this->objPHPExcel->getActiveSheet()->setCellValue('A'.$n, $ptn_item['reg_date']);
	    $this->objPHPExcel->getActiveSheet()->setCellValue('B'.$n, $ptn_item['userid']);
	    $this->objPHPExcel->getActiveSheet()->setCellValue('C'.$n, $ptn_item['career_year']);
	    $this->objPHPExcel->getActiveSheet()->setCellValue('D'.$n, $ptn_item['career_type']);
	    $this->objPHPExcel->getActiveSheet()->setCellValue('E'.$n, $ptn_item['area_name']);
	    $this->objPHPExcel->getActiveSheet()->setCellValue('F'.$n, $ptn_item['main_service']);
	    $this->objPHPExcel->getActiveSheet()->setCellValue('G'.$n, $ptn_item['cate_name']);
	    $this->objPHPExcel->getActiveSheet()->setCellValue('H'.$n, $tech_list);
	    $this->objPHPExcel->getActiveSheet()->setCellValue('I'.$n, $ptn_item['introduce']);
	    $this->objPHPExcel->getActiveSheet()->setCellValue('J'.$n, $ptn_item['finish_cnt']."건");
	    $this->objPHPExcel->getActiveSheet()->setCellValue('K'.$n, $ptn_item['grade_cnt']."건");
	    
	    $filename = '파트너정보_'.date('Ymd').'.xls';
	    header("Content-charset=utf-8");
	    header("Pragma: public");
	    header("Expires: 0");
	    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	    header("Content-Type: application/force-download");
	    header("Content-Type: application/octet-stream");
	    header("Content-Type: application/download");
	    header('Content-Disposition: attachment;filename="'.urlencode($filename).'"');
	    header("Content-Transfer-Encoding: binary");
	    
	    $objWriter = IOFactory::createWriter($this->objPHPExcel, "Excel5");
	    $objWriter->save('php://output');
	}
	
	private function getOscNo($cate_seq) {
	    $query = "select date_format(curdate(), '%y%m%d') as today, count(*) as count ".
	   	    "from fm_cm_machine_outsourcing where cate_seq = ". $cate_seq." ".
	   	    "and substring(osc_no, 2, 6) = date_format(curdate(), '%y%m%d')";
	    $query = $this->db->query($query);
	    $result = $query->row_array();
	    $no = (int)$result['count'] + 1;
	    $no = sprintf("%03d", $no);
	    return $cate_seq.$result['today'].$no;
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
	
	private function getUserData($userid) {
	    $query = "select * from fm_member where userid='".$userid."'";
	    $query = $this->db->query($query);
	    $result = $query->row_array();
	    return $this->membermodel->get_member_data($result['member_seq']);
	}

	private function set_upload_options($upload_path) {
	    if(!is_dir($upload_path)) {
	        mkdir($upload_path, 0777, true);
	    }
	    $config = array();
	    $config['upload_path'] = $upload_path;
	    $config['allowed_types'] = 'gif|jpg|png|hwp|pdf';
	    $config['max_size'] = '0';
	    $config['max_width'] = '0';
	    $config['max_height'] = '0';
	    $config['overwrite'] = FALSE;
	    
	    return $config;
	}
	private function price_format($num) {
	    if(!ctype_digit($num))
	        $num = (string)$num;
        $won = array('', '만', '억', '조', '경', '해');
        $rtn = '';
        $len = strlen($num);
        $mod = $len % 4;
        if($mod) {
            $mod = 4 - $mod;
            $num = str_pad($num, $len + $mod, '0', STR_PAD_LEFT);
        }
        $arr = str_split($num, 4);
        for($i=0,$cnt=count($arr);$i<$cnt;$i++) {
            if($tmp = (int)$arr[$i])
                $rtn .= $tmp.$won[$cnt - $i - 1];
        }
        return $rtn;
   }  
}