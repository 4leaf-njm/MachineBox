<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);
class mch extends front_base {
    public function __construct()
    {
        parent::__construct();
        $this->load->model('membermodel');
        $this->load->helper(array('form', 'url', 'mail', 'sms'));
    }
    
    public function partner_reg()
    {
        if(!$this->sessionCheck()) {
            $this->session->set_flashdata('message', '로그인이 필요한 페이지입니다');
            pageRedirect("/user/login");
            exit;
        }
        if($this->get_bpermit_check() == -1) {
            $this->session->set_flashdata('message', '사업자등록증을 첨부하시고 사업자 인증을 받으셔야 이용하실 수 있습니다. 기업회원으로 전환해주시기 바랍니다.');
            pageRedirect("/user/my_info_modify/change");
            exit;
        }
        if($this->get_bpermit_check() == 0) {
            $this->session->set_flashdata('message', '관리자가 인증을 처리하고 있습니다. 빠른 시간에 이용하실수 있도록 하겠습니다. 감사합니다.');
            pageRedirect($_SERVER["HTTP_REFERER"]);
            exit;
        }
        $tpl = 'mch/partner_reg.html';
        $skin = $this->skin;
        
        $this->template_path = $tpl;
        $this->template->assign(array("skin_path"=>$this->skin,
                                      "template_path"=>$this->template_path));
        
        $query = $this->db->get('fm_cm_machine_area');
        $result = $query->result_array();
        $this->template->assign('area_list', $result);
        
        $query = $this->db->get('fm_cm_machine_category');
        $result = $query->result_array();
        $this->template->assign('cate_list', $result);
        
        $userInfo = $this->getUserData();
        $partner = $this->getPartnerSeq($userInfo['userid']);
        if(!empty($partner)) {
            $partner_info = $this->get_partner_info($partner['partner_seq']);
            $this->template->assign($partner_info);
        }
        $this->template->assign(array('userInfo' => $userInfo));
        
        $this->print_layout($skin.'/'.$tpl);
    }
    
    public function partner_process() {
        $cert_name_arr = $this->input->post('cert_name_arr');
        $cert_org_arr = $this->input->post('cert_org_arr');
        $cert_date_arr = $this->input->post('cert_date_arr');
        $pofol_name_arr = $this->input->post('pofol_name_arr');
        $pofol_cate_arr = $this->input->post('pofol_cate_arr');
        $pofol_startdate_arr = $this->input->post('pofol_startdate_arr');
        $pofol_enddate_arr = $this->input->post('pofol_enddate_arr');
        $pofol_content_arr = $this->input->post('pofol_content_arr');
        $pofol_picture_seq_arr = $this->input->post('pofol_picture_seq_arr');
        $pofol_picture_path_arr = $this->input->post('pofol_picture_path_arr');
        $pofol_picture_index_arr = $this->input->post('pofol_picture_index_arr');
        $pofol_picture_prev_index_arr = $this->input->post('pofol_picture_prev_index_arr');
        
        $pofol_cnt = $this->input->post('pofol_cnt');
        $area_seq = $this->input->post('area_seq');
        $career_year = $this->input->post('career_year');
        $career_type = $this->input->post('career_type');
        $cate_seq = $this->input->post('cate_seq');
        $main_service = $this->input->post('main_service');
        $introduce = $this->input->post('introduce');
        $tech_list = $this->input->post('tech_list');
        
        $partner_seq = $this->input->post('partner_seq');
        if(empty($partner_seq))
            $mode = 'insert';
        else
            $mode = 'modify';
        $userData = $this->getUserData();
        $partner_data = array();
        $partner_data['userid'] = $userData['userid'];
        $partner_data['area_seq'] = $area_seq;
        $partner_data['career_year'] = $career_year;
        $partner_data['career_type'] = $career_type;
        $partner_data['cate_seq'] = $cate_seq;
        $partner_data['main_service'] = $main_service;
        $partner_data['introduce'] = $introduce;
        $partner_data['tech_list'] = $tech_list;

        $this->load->library('upload');
        $files = $_FILES;
        
        $upload_path = "./data/uploads/profile";
        $filename = 'profile_file';
        
        $this->upload->initialize($this->set_upload_options($upload_path));
        if($this->upload->do_upload($filename)) {
            $upload_data = $this->upload->data();
            $partner_data['profile_path'] = str_replace(".", "", $upload_path).'/'.$upload_data['file_name'];
        } else {
            if($mode == 'insert')
                $partner_data['profile_path'] = '/data/uploads/common/no-image.png';
        }
        if($mode == 'modify') {
            $this->db->where('partner_seq', $partner_seq);
            $this->db->update('fm_cm_machine_partner', $partner_data);
        } else {
            $this->db->insert('fm_cm_machine_partner', $partner_data);
            $partner_seq = $this->db->insert_id();
        }
        
        $this->db->where('partner_seq', $partner_seq);
        $this->db->delete('fm_cm_machine_partner_certificate');
        for($i=0; $i<count($cert_name_arr); $i++) {
            $cert_data = array(
                'partner_seq' => $partner_seq,
                'cert_name' => $cert_name_arr[$i],
                'cert_date' => $cert_date_arr[$i],
                'cert_org' => $cert_org_arr[$i],
            );
            $this->db->insert('fm_cm_machine_partner_certificate', $cert_data);
        }
        
        $query = "select * from fm_cm_machine_partner_portfolio where partner_seq = ".$partner_seq;
        $query = $this->db->query($query);
        $result = $query->result_array();
        foreach($result as $row) {
            $this->db->where('pofol_seq', $row['pofol_seq']);
            $this->db->delete('fm_cm_machine_partner_portfolio_picture');
        }
        $this->db->where('partner_seq', $partner_seq);
        $this->db->delete('fm_cm_machine_partner_portfolio');
        
        for($i=0; $i<count($pofol_name_arr); $i++) {
            $pofol_data = array(
                'partner_seq' => $partner_seq,
                'pofol_name' => $pofol_name_arr[$i],
                'pofol_cate' => $pofol_cate_arr[$i],
                'start_date' => $pofol_startdate_arr[$i],
                'end_date' => $pofol_enddate_arr[$i],
                'pofol_content' => $pofol_content_arr[$i]
            );
            $this->db->insert('fm_cm_machine_partner_portfolio', $pofol_data);
            $pofol_seq = $this->db->insert_id();
            
            for($idx1=0; $idx1<count($pofol_picture_seq_arr); $idx1++) {
                if($i == $pofol_picture_prev_index_arr[$idx1]) {
                    $picture_data = array();
                    $picture_data['pofol_seq'] = $pofol_seq;
                    $picture_data['path'] = $pofol_picture_path_arr[$idx1];
                    $this->db->insert('fm_cm_machine_partner_portfolio_picture', $picture_data);
                }
            }
            $file_idx = 0;
            for($idx2=1; $idx2<$pofol_cnt; $idx2++) {
                $upload_path = "./data/uploads/portfolio";
                $filename = 'pofol_picture_'.$idx2;
                $cnt = count($files[$filename]['name']);
                for($j=0; $j<$cnt; $j++) {
                    if($files[$filename]['name'][$j] == null) continue;
                    if($i == $pofol_picture_index_arr[$file_idx++]) {
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
            }
        }
        //$this->send_email('partner');
        //$this->send_sms('partner');
        
        if($mode == 'modify') 
            $this->session->set_flashdata('message', '파트너 정보 수정이 완료되었습니다.');
        else            
            $this->session->set_flashdata('message', '파트너 정보 등록이 완료되었습니다.');
            
        pageRedirect("/user/mypage");
    }
    
    public function osc_reg($osc_seq, $page_type)
    {
        if(!$this->sessionCheck()) {
            $this->session->set_flashdata('message', '로그인이 필요한 페이지입니다');
            pageRedirect("/user/login");
        }
        $tpl = 'mch/osc_reg.html';
        $skin = $this->skin;
        
        $this->template_path = $tpl;
        $this->template->assign(array("skin_path"=>$this->skin,
            "template_path"=>$this->template_path));
                
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
        
        if(isset($osc_seq) && $page_type != 'temp') {
            $osc_info = $this->get_osc_info($osc_seq);
            $this->template->assign($osc_info);
        }
        if($page_type == 'temp') 
            $this->template->assign('temp_seq', $osc_seq);
        $this->template->assign('page_type', $page_type);
        
        $this->print_layout($skin.'/'.$tpl);
    }
    
    public function osc_process() {
        $osc_seq = $this->input->post('osc_seq');
        $page_type = $this->input->post('page_type');
        $file_cnt = $this->input->post('file_cnt');
        $osc_tech = $this->input->post('osc_tech');
        $cate_seq = $this->input->post('cate_seq');
        $cate_sub = $this->input->post('cate_sub');
        $area_seq = $this->input->post('area_seq');
        $osc_name = $this->input->post('osc_name');
        $expect_date = $this->input->post('expect_date');
        $budget = $this->input->post('budget');
        $osc_content = $this->input->post('osc_content');
        $osc_end_date = $this->input->post('osc_end_date');
        $start_expect_date = $this->input->post('start_expect_date');
        $picture_seq_arr = $this->input->post('picture_seq_arr');
        $picture_path_arr = $this->input->post('picture_path_arr');
        
        $userData = $this->getUserData();
        $osc_data = array();
        $osc_data['userid'] = $userData['userid'];
        $osc_data['osc_no'] = $this->getOscNo($cate_seq);
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
        
        if(!empty($osc_seq)) {
            $this->db->where('osc_seq', $osc_seq);
            $this->db->update('fm_cm_machine_outsourcing', $osc_data);
            $mode = 'modify';
        } else {
            $this->db->insert('fm_cm_machine_outsourcing', $osc_data);
            $osc_seq = $this->db->insert_id();
            $mode = 'insert';
        }
        
        $this->load->library('upload');
        $files = $_FILES;
  
        $this->db->where('osc_seq', $osc_seq);
        $this->db->delete('fm_cm_machine_outsourcing_picture');
        for($idx=0; $idx<count($picture_seq_arr); $idx++) {
            $picture_data = array();
            $picture_data['osc_seq'] = $osc_seq;
            $picture_data['path'] = $picture_path_arr[$idx];
            $this->db->insert('fm_cm_machine_outsourcing_picture', $picture_data);
        }
        for($idx=1; $idx<$file_cnt; $idx++) {
            $upload_path = "./data/uploads/outsourcing";
            $filename = 'pofol_picture_'.$idx;
            $cnt = count($_FILES[$filename]['name']);
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
                    $picture_data['osc_seq'] = $osc_seq;
                    $picture_data['path'] = str_replace(".", "", $upload_path).'/'.$upload_data['file_name'];
                    $this->db->insert('fm_cm_machine_outsourcing_picture', $picture_data);
                }
            }
        }
        if($mode == 'modify') {
            $this->session->set_flashdata('message', '외주정보 수정이 완료되었습니다.');
            pageRedirect("/user/my_osc_".$page_type);
        } else if ($mode == 'insert'){
            $this->send_email('osc');
            $this->send_sms('osc');
            
            $this->session->set_flashdata('message', '외주 등록이 완료되었습니다.');
            pageRedirect("/user/my_osc_wait");
        }
    }
    
    public function partner_sch($type='c', $value=1, $order=1, $page=1) {
        $tpl = 'mch/partner_sch.html';
        $skin = $this->skin;
        
        $this->template_path = $tpl;
        $this->template->assign(array("skin_path"=>$this->skin,
            "template_path"=>$this->template_path));
       
        $query = $this->db->get('fm_cm_machine_category');
        $result = $query->result_array();
        $this->template->assign('cate_list', $result);
        $query = $this->db->get('fm_cm_machine_area');
        $result = $query->result_array();
        $this->template->assign('area_list', $result);
        
        $searchData = $this->get_search_partner($type, $value, $order, $page);

        $this->template->assign('partner_list', $searchData['partner_list']);
        $this->template->assign('page_num', $searchData['page_num']);

        $new_list = $this->new_partner_list();
        $this->template->assign('new_list', $new_list);
        
        $this->template->assign(array('type' => $type, 'value' => $value, 'order' => $order, 'page' => $page));
        
        $this->print_layout($skin.'/'.$tpl);
    }
    
    public function osc_sch($cate=1, $order=1, $page=1) {
        $tpl = 'mch/osc_sch.html';
        $skin = $this->skin;
        
        $this->template_path = $tpl;
        $this->template->assign(array("skin_path"=>$this->skin,
            "template_path"=>$this->template_path));
        
        $query = $this->db->get('fm_cm_machine_category');
        $result = $query->result_array();
        $this->template->assign('cate_list', $result);
        
        $oscData = $this->get_search_osc($cate, $order, $page);
        
        $this->template->assign('osc_list', $oscData['osc_list']);
        $this->template->assign('page_num', $oscData['page_num']);
        
        $this->template->assign(array('cate' => $cate, 'order' => $order, 'page' => $page));
        
        $this->print_layout($skin.'/'.$tpl);
    }
    
    public function partner_info($partner_seq) {
        $tpl = 'mch/partner_info.html';
        $skin = $this->skin;
        
        $this->template_path = $tpl;
        $this->template->assign(array("skin_path"=>$this->skin,
            "template_path"=>$this->template_path));
        
        $resultMap = $this->get_partner_info($partner_seq);
        
        $this->template->assign('info', $resultMap['info']);
        $this->template->assign('cert_list', $resultMap['cert_list']);
        $this->template->assign('pofol_list', $resultMap['pofol_list']);
        
        $this->print_layout($skin.'/'.$tpl);
    }
    
    public function osc_info($osc_seq) {
        $tpl = 'mch/osc_info.html';
        $skin = $this->skin;
        
        $this->template_path = $tpl;
        $this->template->assign(array("skin_path"=>$this->skin,
            "template_path"=>$this->template_path));
        
        $recent_cookies = explode(",", $_COOKIE['recent_osc_cookie']);
        $is_recent = false;
        foreach($recent_cookies as $row) {
            if($row == $osc_seq)
                $is_recent = true;
        }
        if($is_recent == false) {
            if(isset($_COOKIE['recent_osc_cookie'])) {
                setcookie('recent_osc_cookie', $osc_seq.",".$_COOKIE['recent_osc_cookie'], time() + 3600 * 24 * 3, '/');
            } else {
                setcookie('recent_osc_cookie', $osc_seq, time() + 3600 * 24 * 3, '/');
            }
        }
        
        $resultMap = $this->get_osc_info($osc_seq);
        $this->template->assign('info', $resultMap['info']);
        
        $this->print_layout($skin.'/'.$tpl);
    }
    
    
    public function osc_req($partner_seq) {
        if(!$this->sessionCheck()) {
            $this->session->set_flashdata('message', '로그인이 필요한 페이지입니다');
            pageRedirect("/user/login");
            return;
        }
        if($this->loginUserEqualCheck($this->getPartnerId($partner_seq))) {
            $this->session->set_flashdata('message', '나에게는 지원 요청을 할 수 없습니다.');
            pageRedirect('/mch/partner_info/'.$partner_seq);
            return;
        }
        $tpl = 'mch/osc_req.html';
        $skin = $this->skin;
        $this->template_path = $tpl;
        $this->template->assign(array("skin_path"=>$this->skin,
            "template_path"=>$this->template_path));
        
        $userData = $this->getUserData();
        $query = "select * from fm_cm_machine_outsourcing a, fm_cm_machine_area b ".
                 "where a.area_seq = b.area_seq and a.state = 1 and userid = '". $userData['userid']. "' order by reg_date desc";
        $query = $this->db->query($query);
        $osc_list = $query->result_array();
            
        foreach($osc_list as &$row) {
            $osc_tech = $row['osc_tech'];
            $tech_list = explode(',', $osc_tech);
            $row['tech_list'] = $tech_list;
            
            $query2 = "select count(*) as apply_cnt from fm_cm_machine_partner_osc where admin_yn = 'y' and osc_seq = ".$row['osc_seq'];
            $query2 = $this->db->query($query2);
            $result = $query2->row_array();
            $row['apply_cnt'] = $result['apply_cnt'];
            
            if(strtotime($row['reg_date']) > strtotime(date('Y-m-d H:i:s', strtotime('-1 days')))) {
                $row['is_new'] = true;
            }
        }
           
        $this->template->assign('osc_list', $osc_list);
        $this->template->assign('partner_seq', $partner_seq);
        
        $this->print_layout($skin.'/'.$tpl);
    }
    public function osc_apply_process() {
        $osc_seq = $this->input->post('osc_seq');
        $state = $this->input->post('state');
        
        if(!$this->sessionCheck()) {
            $this->session->set_flashdata('message', '로그인이 필요한 기능입니다.');
            pageRedirect("/user/login");
            exit;
        } else if(empty($this->getPartnerSeq($this->getUserData()['userid']))) {
            $this->session->set_flashdata('message', '파트너 등록이 필요합니다. 자세한 프로필 등록은 외주사로부터 수주를 받을 확율을 높여줍니다.');
            pageRedirect('/mch/partner_reg');
            exit;
        } else if($state == '2') {
            $this->session->set_flashdata('message', '모집이 마감된 외주 업무입니다.');
            pageRedirect('/mch/osc_info/'.$osc_seq);
            exit;
        } else if($this->loginUserEqualCheck($this->getOscId($osc_seq))) {
            $this->session->set_flashdata('message', '자신이 의뢰한 외주 업무에 지원할 수 없습니다.');
            pageRedirect('/mch/osc_info/'.$osc_seq);
            exit;
        }
        $partner_seq = $this->getPartnerSeq($this->getUserData()['userid'])['partner_seq'];
        
        $query = "select * from fm_cm_machine_partner_osc where partner_seq = ".$partner_seq." and osc_seq = ".$osc_seq; 
        $query = $this->db->query($query);
        $result = $query->row_array();
        
        if(empty($result)) {
            $data = array(
                'partner_seq' => $partner_seq,
                'osc_seq' => $osc_seq
            );
            $this->db->insert('fm_cm_machine_partner_osc', $data);
            $this->session->set_flashdata('message', '지원 되었습니다.');
        } else {
            $this->session->set_flashdata('message', '이미 지원하신 외주입니다.');
        }
        pageRedirect('/mch/osc_info/'.$osc_seq);
    }
    
    public function osc_req_process() {
        $partner_seq = $this->input->post('partner_seq');
        $osc_seq = $this->input->post('osc_seq');
        
        $query = "select * from fm_cm_machine_outsourcing_request ".
                 "where osc_seq = ".$osc_seq." and partner_seq = ". $partner_seq;    
        $query = $this->db->query($query);
        $result = $query->row_array();
        
        if(empty($result)) {
            $data = array(
                'partner_seq' => $partner_seq,
                'osc_seq' => $osc_seq
            );
            $this->db->insert('fm_cm_machine_outsourcing_request', $data);
            $this->session->set_flashdata('message', '지원요청이 완료되었습니다.');
            pageRedirect("/mch/partner_info/".$partner_seq);
        } else if($result['pres_state'] == 0 || $result['pres_state'] == 1 || $result['pres_state'] == 2) {
            $this->session->set_flashdata('message', '이미 지원요청한 파트너입니다.');
            pageRedirect("/mch/osc_req/".$partner_seq);
        } else if($result['pres_state'] == 3) {
            $this->session->set_flashdata('message', '파트너가 거절한 외주입니다.');
            pageRedirect("/mch/osc_req/".$partner_seq);
        }
    }
    
    public function partner_eval($osc_seq, $partner_seq) {
        if(!$this->sessionCheck()) {
            $this->session->set_flashdata('message', '로그인이 필요한 페이지입니다');
            pageRedirect("/user/login");
        }
        $tpl = 'mch/partner_eval.html';
        $skin = $this->skin;
        
        $this->template_path = $tpl;
        $this->template->assign(array("skin_path"=>$this->skin,
            "template_path"=>$this->template_path));
        
        $osc_info = $this->get_osc_info($osc_seq);
        $ptn_info = $this->get_partner_info($partner_seq);
        
        $this->template->assign('osc_info', $osc_info['info']);
        $this->template->assign('ptn_info', $ptn_info['info']);
        
        $this->print_layout($skin.'/'.$tpl);
    }
    
    public function partner_eval_process() {
        $partner_seq = $this->input->post('partner_seq');
        $grade_01 = $this->input->post('grade_01');
        $grade_02 = $this->input->post('grade_02');
        $grade_03 = $this->input->post('grade_03');
        $grade_04 = $this->input->post('grade_04');
        $grade_05 = $this->input->post('grade_05');
        $grade = $this->input->post('grade');
        $content = $this->input->post('content');
        
        $userData = $this->getUserData();
        $query = "select * from fm_cm_machine_partner_eval where partner_seq = ".$partner_seq." and userid = '".$userData['userid']."'";
        $query = $this->db->query($query);
        $result = $query->row_array();
        if(!empty($result)) {
            $this->session->set_flashdata('message', '이미 평가를 작성하셨습니다.');
            pageRedirect('/user/my_osc_finish');
            exit;
        }
        $data = array(
            'userid' => $userData['userid'],
            'partner_seq' => $partner_seq,
            'grade_01' => $grade_01,
            'grade_02' => $grade_02,
            'grade_03' => $grade_03,
            'grade_04' => $grade_04,
            'grade_05' => $grade_05,
            'grade' => $grade,
            'content' => $content
        );
        $this->db->insert('fm_cm_machine_partner_eval', $data);
        
        $this->session->set_flashdata('message', '평가 작성이 완료되었습니다.');
        pageRedirect('/user/my_osc_finish');
    }
    
    public function download_pofol() {
        $pofol_path = '.'.$this->input->post('pofol_path');
        $path_split = explode('/', $pofol_path);
        $name = $path_split[4];
        $this->load->helper('download');
        
        $realpath = realpath($pofol_path);
        $data = file_get_contents($realpath);
        force_download($name, $data);
    }
    
    private function get_search_partner($type, $value, $order, $page) {
        if($type == 'c') {
            if($value == '1')
                $where_query = "";
            else
                $where_query = "and cate_seq = ".($value-1);            
        } else if ($type == 'a') {
            $where_query = "and b.area_seq = ".$value;
        }
        if($order == '1') {
            $order_query = "order by reg_date desc";
        } else if ($order == '2') {
            $order_query = "order by (select count(*) from fm_cm_machine_partner_eval where partner_seq = a.partner_seq) desc, reg_date desc";
        } else if ($order == '3') {
            $order_query = "order by (select count(*) from fm_cm_machine_partner_review where partner_seq = a.partner_seq) desc, reg_date desc";
        }
        
        $query = "select * from fm_cm_machine_partner a, fm_cm_machine_area b where a.area_seq = b.area_seq ".$where_query." ".$order_query;
        $query = $this->db->query($query);
        
        $total = $query->num_rows();
        $per_page = 4;
        
        $page_num = ($total / $per_page) + (($total % $per_page != 0) ? 1 : 0);
        if($page_num == 0)
            $page_num = 1;
        $query = "select * from fm_cm_machine_partner a, fm_cm_machine_area b where a.area_seq = b.area_seq ".
                 $where_query." ".$order_query. " limit ". ($page-1) * $per_page.", ".$per_page;
        $query = $this->db->query($query);
        $result = $query->result_array();
        
        foreach($result as &$row) {
            $query = "select COALESCE(convert(avg(grade), signed integer), 0) as grade, COALESCE(round(avg(grade), 1), 0) as grade_origin, count(*) as grade_cnt from fm_cm_machine_partner_eval where partner_seq = ".$row['partner_seq'];
            $query = $this->db->query($query);
            $row['grade'] = $query->row()->grade;
            $row['grade_origin'] = $query->row()->grade_origin;
            $row['grade_cnt'] = $query->row()->grade_cnt;
            
            $query = "select count(*) as finish_cnt from fm_cm_machine_partner_osc where state = 3 and partner_seq = ".$row['partner_seq'];
            $query = $this->db->query($query);
            $row['finish_cnt'] = $query->row()->finish_cnt;
            
            $query2 = "select * from fm_cm_machine_partner_certificate where partner_seq = ".$row['partner_seq'];
            $query2 = $this->db->query($query2);
            $cert_list = $query2->result_array();
            $row['cert_list'] = $cert_list;
        }
        $data = array();
        $data['partner_list'] = $result;
        $data['page_num'] = $page_num;
        return $data;
    }
    
    private function get_search_osc($cate, $order, $page) {
        if($cate == '1')
            $where_query = "";
        else
            $where_query = "and cate_seq = ".($cate-1);
        
        if($order == '1') {
            $order_query = "order by state asc, reg_date desc";
        } else if ($order == '2') {
            $order_query = "order by state asc, (select count(*) from fm_cm_machine_partner_osc where osc_seq = a.osc_seq) desc, reg_date desc";
        } else if ($order == '3') {
            $order_query = "order by state asc, budget desc, reg_date desc";
        }
        $query = "select * from fm_cm_machine_outsourcing a, fm_cm_machine_area b where a.area_seq = b.area_seq and permit_yn = 'y' and contract_yn = 'n' ".$where_query." ".$order_query;
        $query = $this->db->query($query);
        
        $total = $query->num_rows();
        $per_page = 5;
        
        $page_num = ($total / $per_page) + (($total % $per_page != 0) ? 1 : 0);
        if($page_num == 0)
            $page_num = 1;
        
        $query = "select * from fm_cm_machine_outsourcing a, fm_cm_machine_area b where a.area_seq = b.area_seq and permit_yn = 'y' and contract_yn = 'n' ".
                  $where_query." ".$order_query. " limit ". ($page-1) * $per_page.", ".$per_page;
        $query = $this->db->query($query);
        $result = $query->result_array();
        
        foreach($result as &$row) {
            $osc_tech = $row['osc_tech'];
            if(!empty($osc_tech)) {
                $tech_list = explode(',', $osc_tech);
                $row['tech_list'] = $tech_list; 
            }
            $query2 = "select count(*) as apply_cnt from fm_cm_machine_partner_osc where admin_yn = 'y' and osc_seq = ".$row['osc_seq'];
            $query2 = $this->db->query($query2);
            $result2 = $query2->row_array();
            $row['apply_cnt'] = $result2['apply_cnt'];
            
            if(strtotime($row['reg_date']) > strtotime(date('Y-m-d H:i:s', strtotime('-1 days')))) {
                $row['is_new'] = true;
            }
        }
        $data = array();
        $data['osc_list'] = $result;
        $data['page_num'] = $page_num;
        return $data;
    }
    
    private function new_partner_list() {
        $query = "select * from fm_member order by regist_date desc";
        $query = $this->db->query($query);
        $new_list = $query->result_array();
        foreach($new_list as &$row) {
            $query = "select * from fm_member_business where member_seq = ".$row['member_seq'];
            $query = $this->db->query($query);
            $result = $query->row_array();
            
            $addr_arr = array();
            if(empty($result)) {
                $address = explode(' ', $row['address'])[0];
                for($i=0; $i<mb_strlen($address,"UTF-8"); $i++)
                    $addr_arr[] = mb_substr($address, $i, 1, "UTF-8"); 
                if(empty($addr_arr))
                    continue;
                
                $query = "select * from fm_cm_machine_area";
                $query = $this->db->query($query);
                $result2 = $query->result_array();

                foreach($result2 as $row2) {
                    $row2_arr = array();
                    for($i=0; $i<mb_strlen($row2['area_name'],"UTF-8"); $i++)
                        $row2_arr[] = mb_substr($row2['area_name'], $i, 1, "UTF-8"); 
                    
                    if(count(array_intersect($addr_arr, $row2_arr)) == 2) {
                        $row['area_name'] = $row2['area_name'];
                    }
                }
            } else {
                $address = explode(' ', $result['baddress'])[0];
                for($i=0; $i<mb_strlen($address,"UTF-8"); $i++)
                    $addr_arr[] = mb_substr($address, $i, 1, "UTF-8"); 
                if(empty($addr_arr))
                    continue;
               
                $query = "select * from fm_cm_machine_area";
                $query = $this->db->query($query);
                $result2 = $query->result_array();
                
                foreach($result2 as $row2) {
                    $row2_arr = array();
                    for($i=0; $i<mb_strlen($row2['area_name'],"UTF-8"); $i++)
                        $row2_arr[] = mb_substr($row2['area_name'], $i, 1, "UTF-8"); 
                    
                    if(count(array_intersect($addr_arr, $row2_arr)) == 2) {
                        $row['area_name'] = $row2['area_name'];
                    }
                }
            }
            
            $query = "select * from fm_cm_machine_partner a, fm_cm_machine_area b where a.area_seq = b.area_seq and a.userid = '".$row['userid']."'";
            $query = $this->db->query($query);
            $result = $query->row_array();
            if(!empty($result)) {
                $row['area_name'] = $result['area_name'];
            }
            
            $query = "select * from fm_member_business where member_seq = '".$row['member_seq']."'";
            $query = $this->db->query($query);
            $result = $query->row_array();
            if(!empty($result)) {
                $row['bname'] = $result['bname'];
                $row['bitem'] = $result['bitem'];
                $row['bstatus'] = $result['bstatus'];
            }
        }
        return $new_list;
    }
    
    private function get_partner_info($partner_seq) {
        $query = "select * from fm_cm_machine_partner a, fm_cm_machine_area b, fm_cm_machine_category c where a.area_seq = b.area_seq ".
                 "and a.cate_seq = c.cate_seq and a.partner_seq = ".$partner_seq;
        $query = $this->db->query($query);
        $info = $query->row_array();
        
        $query = "select COALESCE(convert(avg(grade), signed integer), 0) as grade, COALESCE(round(avg(grade), 1), 0) as grade_origin, count(*) as grade_cnt from fm_cm_machine_partner_eval where partner_seq = ".$info['partner_seq'];
        $query = $this->db->query($query);
        $info['grade'] = $query->row()->grade;
        $info['grade_origin'] = $query->row()->grade_origin;
        $info['grade_cnt'] = $query->row()->grade_cnt;
        
        $query = "select count(*) as finish_cnt from fm_cm_machine_partner_osc where state = 3 and partner_seq = ".$info['partner_seq'];
        $query = $this->db->query($query);
        $info['finish_cnt'] = $query->row()->finish_cnt;
        
        $query = "select * from fm_cm_machine_partner_certificate where partner_seq = ".$partner_seq;
        $query = $this->db->query($query);
        $cert_list = $query->result_array();
        
        $query = "select * from fm_cm_machine_partner_portfolio where partner_seq = ".$partner_seq;
        $query = $this->db->query($query);
        $pofol_list = $query->result_array();
        
        foreach($pofol_list as &$row) {
            $query2 = "select * from fm_cm_machine_partner_portfolio_picture where pofol_seq = ".$row['pofol_seq'];
            $query2 = $this->db->query($query2);
            $picture_list = $query2->result_array();
            $row['picture_list'] = $picture_list;
        }
        $resultMap = array();
        $resultMap['info'] = $info;
        $resultMap['cert_list'] = $cert_list;
        $resultMap['pofol_list'] = $pofol_list;
        
        return $resultMap;
    }
    
    private function get_osc_info($osc_seq) {
        $query = "select * from fm_cm_machine_outsourcing a, fm_cm_machine_area b where a.area_seq = b.area_seq ".
            "and a.osc_seq = ".$osc_seq;
        $query = $this->db->query($query);
        $info = $query->row_array();
        
        $osc_tech = $info['osc_tech'];
        if(!empty($osc_tech)) {
            $tech_list = explode(',', $osc_tech);
            $info['tech_list'] = $tech_list;
        }
        $query = "select * from fm_cm_machine_outsourcing_picture where osc_seq = ".$info['osc_seq'];
        $query = $this->db->query($query);
        $result = $query->result_array();
        $info['picture_list'] = $result;
        
        $query = "select count(*) as apply_cnt from fm_cm_machine_partner_osc where admin_yn = 'y' and osc_seq = ".$info['osc_seq'];
        $query = $this->db->query($query);
        $result = $query->row_array();
        $info['apply_cnt'] = $result['apply_cnt'];
     
        $partner = $this->getPartnerSeq($info['userid']);
        if(empty($partner)) {
            $info['isPartner'] = 'false';
            $info['finish_cnt'] = 0;
        } else {
            $partnerInfo = $this->get_partner_info($partner['partner_seq']);
            $info['isPartner'] = 'true';
            $partnerInfo = $partnerInfo['info'];
            $info['profile_path'] = $partnerInfo['profile_path'];
            $info['main_service'] = $partnerInfo['main_service'];
            $info['grade'] = $partnerInfo['grade'];
            $info['grade_origin'] = $partnerInfo['grade_origin'];
            $info['grade_cnt'] = $partnerInfo['grade_cnt'];
            $info['finish_cnt'] = $partnerInfo['finish_cnt'];
        }
        $query = "select count(*) as osc_finish_cnt from fm_cm_machine_outsourcing where finish_yn = 'y' and userid = '".$info['userid']."'";
        $query = $this->db->query($query);
        $result = $query->row_array();
        $info['finish_cnt'] += $result['osc_finish_cnt'];

        $resultMap = array();
        $resultMap['info'] = $info;
        return $resultMap;
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
    
    private function send_email($reg_type) {
        $userData = $this->getUserData();
        $email	= $userData['email'];
        if($email){
            mch_reg_mail($email, $reg_type);
        }
    }
    
    private function send_sms($reg_type) {
        $userData = $this->getUserData();
        $phone = $userData['cellphone'];
        if($phone) {
            mch_reg_sms($phone, $reg_type);
        }
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
        return $this->membermodel->get_member_data($this->userInfo['member_seq']);
    }
    
    private function loginUserEqualCheck($info) {
        $userData = $this->getUserData();
        if($userData['userid'] == $info['userid'])
            return true;
        else
            return false;
    }
    
    private function getPartnerId($partner_seq) {
        $query = "select userid from fm_cm_machine_partner where partner_seq = ".$partner_seq;
        $query = $this->db->query($query);
        $result = $query->row_array();
        return $result;
    }
    
    private function getPartnerSeq($userid) {
        $query = "select partner_seq from fm_cm_machine_partner where userid = '".$userid. "' limit 1";
        $query = $this->db->query($query);
        $result = $query->row_array();
        return $result;
    }
    
    private function getOscId($osc_seq) {
        $query = "select userid from fm_cm_machine_outsourcing where osc_seq = ".$osc_seq;
        $query = $this->db->query($query);
        $result = $query->row_array();
        return $result;
    }
    
    private function get_bpermit_check() {
        $userData = $this->getUserData();
        $query = "select * from fm_member_business where member_seq = ".$userData['member_seq'];
        $query = $this->db->query($query);
        $result = $query->row_array();
        if(empty($result)) {
            return -1;
        } else {
            if($result['bpermit_yn'] == 'y') {
                return 1;
            } else {
                return 0;
            }
        }
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
}