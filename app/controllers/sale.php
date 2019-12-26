<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);
class sale extends front_base {
    
    public function __construct()
    {
        parent::__construct();
        $this->load->model('membermodel');
        $this->load->helper(array('form', 'url', 'mail', 'sms'));
    }
    
    public function index() {
        redirect("/sale/sale_index");
    }
    
    public function sale_index()
    {
        $tpl = 'sale/sale_index.html';
        $skin = $this->skin;
        
        $this->template_path = $tpl;
        $this->template->assign(array("skin_path"=>$this->skin, 
                                      "template_path"=>$this->template_path));
        
        $this->print_layout($skin.'/'.$tpl);
    }
    
    public function self_intro()
    {
        $tpl = 'sale/self_intro.html';
        $skin = $this->skin;
        
        $this->template_path = $tpl;
        $this->template->assign(array("skin_path"=>$this->skin,
                                      "template_path"=>$this->template_path));
        
        $this->print_layout($skin.'/'.$tpl);
    }
    
    public function self_reg($info_seq, $page_type, $sale_type, $wait_yn)
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
        $tpl = 'sale/self_reg.html';
        $skin = $this->skin;
        
        $this->template_path = $tpl;
        $this->template->assign(array("skin_path"=>$this->skin,
                                      "template_path"=>$this->template_path));
        
        $this->db->distinct('kind_type');
        $this->db->select('kind_type, kind_no');
        $query = $this->db->get('fm_cm_machine_kind');
        $result = $query->result_array();
        $kind_map = array();
        foreach($result as $type) {
            $this->db->where('kind_type', $type['kind_type']);
            $query = $this->db->get('fm_cm_machine_kind');
            $kind_list = $query->result_array();
            $kind_map[] = array('kind_type'=>$type['kind_type'],
                'kind_no'=>$type['kind_no'],
                'kind_list'=>$kind_list
            );
        }
        $this->template->assign('kind_map', $kind_map);
        
        $query = "select * from fm_cm_machine_manufacturer group by mnf_name order by mnf_name asc";
        $query = $this->db->query($query);
        $result = $query->result_array();
        $this->template->assign('mnf_list', $result);
        
        /*
        $query = "select * from fm_cm_machine_model order by model_name asc";
        $query = $this->db->query($query);
        $result = $query->result_array();
        $this->template->assign('model_list', $result);
        */
        
        $query = $this->db->get('fm_cm_machine_area');
        $result = $query->result_array();
        $this->template->assign('area_list', $result);
        
        if(isset($info_seq) && $page_type != 'temp') {
            $sale_info = $this->get_detail_query($sale_type, $info_seq);
            $this->template->assign($sale_info['info_list']);
            $this->template->assign($sale_info);
        }
        if($page_type == 'temp')
            $this->template->assign('temp_seq', $info_seq);
        $this->template->assign('page_type', $page_type);
        $this->template->assign('wait_yn', $wait_yn);
        
        $this->print_layout($skin.'/'.$tpl);
    }
    
    public function self_process() {
        $kind_no = $this->input->post('kind_no');
        $kind_seq = $this->input->post('kind_seq');
        $mnf_seq = $this->input->post('mnf_seq');
        $input_mnf = $this->input->post('input_mnf');
        $txt_mnf = $this->input->post('txt_mnf');
        $model_seq = $this->input->post('model_seq');
        $input_model = $this->input->post('input_model');
        $txt_model = $this->input->post('txt_model');
        $area_seq = $this->input->post('area_seq');
        $model_year = $this->input->post('model_year');
        $serial_num = $this->input->post('serial_num');
        $size = $this->input->post('size');
        $weight = $this->input->post('weight');
        $controller = $this->input->post('controller');
        $method = $this->input->post('method');
        $option_name_arr = $this->input->post('option_name_arr');
        $part_arr = $this->input->post('part');
        $sort_arr = $this->input->post('sort');
        
        $perform_check_yn = $this->input->post('perform_check_yn');
        $online_eval_yn = $this->input->post('online_eval_yn');
        $online_eval_option = $this->input->post('online_eval_option');
        $total_price = $this->input->post('total_price');
        $pay_method = $this->input->post('pay_method');
        
        $info_seq = $this->input->post('info_seq');
        $page_type = $this->input->post('page_type');
        $wait_yn = $this->input->post('wait_yn');
        
        if(!empty($info_seq)) {
            $mode = 'modify';
        } else {
            $mode = 'insert';
        }
        
        $pay_method = (int)$total_price == 0 ? "-" : $pay_method;
        
        $this->load->library('upload');
        $files = $_FILES;
        
        $query = "select * from fm_cm_machine_kind where kind_seq = ".$kind_seq;
        $query = $this->db->query($query);
        $kind = $query->row_array();
        if($input_mnf == 'true' && isset($txt_mnf)) {
            $data = array(
                'mnf_name' => $txt_mnf,
                'mnf_kind' => $kind['kind_name']
            );
            $this->db->insert('fm_cm_machine_manufacturer', $data);
            $mnf_seq = $this->db->insert_id();
        }
        if($input_model == 'true' && isset($txt_model)) {
            $query = "select * from fm_cm_machine_manufacturer where mnf_seq = ".$mnf_seq;
            $query = $this->db->query($query);
            $mnf = $query->row_array();
            $data = array(
                'model_name' => $txt_model,
                'model_kind' => $kind['kind_name'],
                'model_mnf' => $mnf['mnf_name']
            );
            $this->db->insert('fm_cm_machine_model', $data);
            $model_seq = $this->db->insert_id();
        }
        $userData = $this->getUserData();
        if($mode == 'insert') {
            $sales_data = array();
            $sales_data['type'] = 'self';
            $sales_data['userid'] = $userData['userid'];
            $sales_data['total_price'] = $total_price;
            $sales_data['pay_method'] = $pay_method;
            $this->db->insert('fm_cm_machine_sales', $sales_data);
            $sales_seq = $this->db->insert_id();
            
            if($pay_method == '무통장 입금') {
                if($total_price == 0) {
                    $state = '승인';
                    $url = '/sale/sale_complete/self/'.$sales_seq;
                } else {
                    $state = '입금대기';
                    $url = '/sale/sale_complete/self/'.$sales_seq;
                }
            } else {
                $state = '승인';
                $url = '/sale/sale_complete/self/'.$sales_seq;
            }
            
            $info_data = array(
                'sales_no' => $this->getSalesNo($kind_no),
                'sales_seq' => $sales_seq,
                'kind_seq' => $kind_seq,
                'mnf_seq' => $mnf_seq,
                'model_seq' => $model_seq,
                'area_seq' => $area_seq,
                'model_year' => $model_year,
                'serial_num' => $serial_num,
                'size' => $size,
                'weight' => $weight,
                'controller' => $controller,
                'state' => $state
            );
            if($state == '승인') 
                $info_data['state_date'] = date('Y-m-d H:i:s');
            $this->db->insert('fm_cm_machine_sales_info', $info_data);
            $info_seq = $this->db->insert_id();
            
            $option_index = 1;
            
            foreach($option_name_arr as $option) {
                $option_data = array();
                $option_data['info_seq'] = $info_seq;
                $option_data['option_name'] = $option;
                $this->db->insert('fm_cm_machine_sales_option', $option_data);
            }
            
            $upload_path = "./data/uploads/machine";
            $filename = 'machine_picture_'.$option_index;
            $cnt = count($_FILES[$filename]['name']);
            for($i=0; $i<$cnt; $i++) {
                if($files[$filename]['name'][$i] == null) continue;
                
                $_FILES[$filename]['name'] = $files[$filename]['name'][$i];
                $_FILES[$filename]['type'] = $files[$filename]['type'][$i];
                $_FILES[$filename]['tmp_name'] = $files[$filename]['tmp_name'][$i];
                $_FILES[$filename]['error'] = $files[$filename]['error'][$i];
                $_FILES[$filename]['size'] = $files[$filename]['size'][$i];
                
                $this->upload->initialize($this->set_upload_options());
                if($this->upload->do_upload($filename)) {
                    $upload_data = $this->upload->data();
                    $picture_data = array();
                    $picture_data['info_seq'] = $info_seq;
                    $picture_data['path'] = str_replace(".", "", $upload_path).'/'.$upload_data['file_name'];
                    $picture_data['part'] = $part_arr[$i];
                    $picture_data['sort'] = $sort_arr[$i];
                    $this->db->insert('fm_cm_machine_sales_picture', $picture_data);
                }
            }
            $method = $this->input->post('method');
            $self_deliver_condition = $this->input->post('self_deliver_condition');
            $deliver_service = $this->input->post('deliver_service');
            
            $detail_data = array(
                'info_seq' => $info_seq,
                'method' => $method,
                'self_deliver_condition' => $self_deliver_condition,
                'deliver_service' => $deliver_service,
            );
            $update = array();
            if($method == "고정가격판매") {
                $fixed_price = $this->input->post('fixed_price');
                $price_proposal = $this->input->post('price_proposal');
                
                $detail_data['fixed_price'] = $fixed_price;
                $detail_data['price_proposal'] = $price_proposal;
                $update['sort_price'] = $fixed_price;
            } else if ($method == "입찰") {
                $bid_duration = $this->input->post('bid_duration');
                $bid_start_price = $this->input->post('bid_start_price');
                $bid_price = $this->input->post('bid_price');
                $reduction_rate = $this->input->post('reduction_rate');
                $repeat_no = $this->input->post('repeat_no');
                
                $detail_data['bid_duration'] = $bid_duration;
                $detail_data['bid_start_price'] = $bid_start_price;
                $detail_data['bid_current_price'] = $bid_start_price;
                $detail_data['bid_price'] = $bid_price;
                $detail_data['reduction_rate'] = $reduction_rate;
                $detail_data['repeat_no'] = $repeat_no;
                $update['sort_price'] = $bid_start_price;
            }
            $this->db->insert('fm_cm_machine_sales_detail', $detail_data);
            $this->db->where('info_seq', $info_seq);
            $this->db->update('fm_cm_machine_sales_info', $update);
            
            $ad_name_arr = $this->input->post('ad_name');
            $ad_price_arr = $this->input->post('ad_price');
            
            $pay_content = "";
            $pay_price = 0;
            for($i=0; $i<count($ad_name_arr); $i++) {
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
                $pay_price += $ad_price_arr[$i];
            }
            $this->insert_check_data($sales_seq);
            
            $pay_data_list = array();
            if($perform_check_yn == 'y') {
                $perform_data = array();
                $perform_data['info_seq'] = $info_seq;
                $this->db->insert('fm_cm_machine_perform', $perform_data);
                
                if($pay_method == '무통장 입금')
                    $pay_state = '입금대기';
                else
                    $pay_state = '결제완료';
                $pay_data = array();
                $pay_data['pay_userid'] = $userData['userid'];
                $pay_data['pay_content'] = '오프라인 성능검사 결제';
                $pay_data['pay_price'] = 150000;
                $pay_data['pay_method'] = $pay_method;
                $pay_data['pay_state'] = $pay_state;
                $pay_data['pay_type'] = '성능검사';
                $pay_data_list[] = $pay_data;
            }
            if($online_eval_yn == 'y') {
                $eval_data = array();
                $eval_data['info_seq'] = $info_seq;
                $eval_data['eval_name'] = $online_eval_option;
                $this->db->insert('fm_cm_machine_online_eval', $eval_data);
                
                if($online_eval_option == '온라인 기계평가 3')
                    $pay_price = 30000;
                else if($online_eval_option == '온라인 기계평가 5')
                    $pay_price = 50000;
                
                if($pay_method == '무통장 입금')
                    $pay_state = '입금대기';
                else
                    $pay_state = '결제완료';
                $pay_data = array();
                $pay_data['pay_userid'] = $userData['userid'];
                $pay_data['pay_content'] = $online_eval_option.' 결제';
                $pay_data['pay_price'] = $pay_price;
                $pay_data['pay_method'] = $pay_method;
                $pay_data['pay_state'] = $pay_state;
                $pay_data['pay_type'] = '기계평가';
                $pay_data_list[] = $pay_data;
            }
            
            if($pay_content != "") {
                if($pay_method == '무통장 입금')
                    $pay_state = '입금대기';
                else
                    $pay_state = '결제확인';
                $pay_data = array();
                $pay_data['pay_userid'] = $userData['userid'];
                $pay_data['pay_content'] = '프리미엄광고 ('.$pay_content.') 결제';
                $pay_data['pay_price'] = $pay_price;
                $pay_data['pay_method'] = $pay_method;
                $pay_data['pay_state'] = $pay_state;
                $pay_data['pay_type'] = '프리미엄광고';
                $pay_data_list[] = $pay_data;
            }
            foreach($pay_data_list as &$pay_data) {
                $pay_data['pay_no'] = $this->get_pay_no();
                $pay_data['target_seq'] = $info_seq;
                $this->db->insert('fm_cm_machine_pay', $pay_data);
            }
            
            $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_kind c, fm_cm_machine_manufacturer d, fm_cm_machine_model e ".
                 "where a.sales_seq = b.sales_seq and b.kind_seq = c.kind_seq and b.mnf_seq = d.mnf_seq and b.model_seq = e.model_seq and b.info_seq = ".$info_seq;
            $query = $this->db->query($query);
            $result = $query->row_array();
            
            $data = array(
                'userid' => $userData['userid'],
                'sales_no' => $this->getSalesNo($kind_no),
                'kind_name' => $result['kind_name'],
                'model_name' => $result['model_name'],
                'mnf_name' => $result['mnf_name'],
                'size' => $result['size'],
                'weight' => $result['weight'],
                'service_list' => $deliver_service,
                'pay_price' => 0,
                'pay_state' => '승인대기',
                'deliv_state' => '작업시작전',
                'info_seq' => $info_seq
            );
            if($deliver_service != '신청안함')
                $this->db->insert('fm_cm_machine_delivery', $data);

            if($state == '승인') {
                // $this->send_sms('self');
                // $this->send_email('self');
            } else if ($state == '입금대기' && $pay_method == '무통장 입금'){
                $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_model c where a.sales_seq = b.sales_seq and b.model_seq = c.model_seq and info_seq = ".$info_seq;
                $query = $this->db->query($query);
                $prev_data = $query->row_array();

                $title = "유료서비스 <b>결제 안내</b>";
                $message = "※ 유료서비스 결제안내\r\n판매자 " . $userData['userid'] . '님이 등록하신 '.$prev_data['model_name']."(" . $prev_data['sales_no'] . ")의 유료서비스 결제가 필요합니다. 결제 완료 후 30분 이내 광고가 등록됩니다.\r\n- 입금안내 : 에스디네트웍스(신동훈), 농협은행, 계좌번호 302-1371-4082-81, 결제금액 ".number_format($total_price)."원";

                $this->send_common_sms($userData['cellphone'], $message);
                $this->send_common_mail($userData['email'], $title, $message);
            }
            pageRedirect($url);
            exit;
        } else if ($mode == 'modify') {
            $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_sales_detail c, fm_cm_machine_model d where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and b.model_seq = d.model_seq and b.info_seq = ".$info_seq;
            $query = $this->db->query($query);
            $prev_data = $query->row_array();

            if ($total_price == 0) {
                $is_pay = false;
                $state = '승인';
            } else if($prev_data['total_price'] < $total_price) {
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
                'kind_seq' => $kind_seq,
                'mnf_seq' => $mnf_seq,
                'model_seq' => $model_seq,
                'area_seq' => $area_seq,
                'model_year' => $model_year,
                'serial_num' => $serial_num,
                'size' => $size,
                'weight' => $weight,
                'controller' => $controller,
                'state' => $state,
            );
            if($wait_yn == 'y') {
                $sales_wait_data['sales_date'] = date('Y-m-d H:i:s');
                $info_data['wait_yn'] = 'n';
                $info_data['admin_view_yn'] = 'n';
            }
            $this->db->where('info_seq', $info_seq);
            $this->db->delete('fm_cm_machine_sales_option');
            foreach($option_name_arr as $option) {
                $option_data = array();
                $option_data['info_seq'] = $info_seq;
                $option_data['option_name'] = $option;
                $this->db->insert('fm_cm_machine_sales_option', $option_data);
            }
            
            $upload_path = "./data/uploads/machine";
            $filename = 'machine_picture_1';
            $cnt = count($_FILES[$filename]['name']);
            for($i=0; $i<$cnt; $i++) {
                if($files[$filename]['name'][$i] == null) continue;
                $_FILES[$filename]['name'] = $files[$filename]['name'][$i];
                $_FILES[$filename]['type'] = $files[$filename]['type'][$i];
                $_FILES[$filename]['tmp_name'] = $files[$filename]['tmp_name'][$i];
                $_FILES[$filename]['error'] = $files[$filename]['error'][$i];
                $_FILES[$filename]['size'] = $files[$filename]['size'][$i];
                
                $this->upload->initialize($this->set_upload_options());
                if($this->upload->do_upload($filename)) {
                    $upload_data = $this->upload->data();
                    $picture_data = array();
                    $picture_data['info_seq'] = $info_seq;
                    $picture_data['path'] = str_replace(".", "", $upload_path).'/'.$upload_data['file_name'];
                    $picture_data['part'] = $part_arr[$i];
                    $picture_data['sort'] = $sort_arr[$i];
                    $this->db->where('info_seq', $info_seq)
                             ->where('part', $part_arr[$i]);
                    $this->db->delete('fm_cm_machine_sales_picture');
                    $this->db->insert('fm_cm_machine_sales_picture', $picture_data);
                } 
            }
            $method = $this->input->post('method');
            $self_deliver_condition = $this->input->post('self_deliver_condition');
            $deliver_service = $this->input->post('deliver_service');
            
            $detail_data = array(
                'info_seq' => $info_seq,
                'method' => $method,
                'self_deliver_condition' => $self_deliver_condition,
                'deliver_service' => $deliver_service,
            );
            $update = array();
            if($method == "고정가격판매") {
                $fixed_price = $this->input->post('fixed_price');
                $price_proposal = $this->input->post('price_proposal');
                
                $detail_data['fixed_price'] = $fixed_price;
                $detail_data['price_proposal'] = $price_proposal;
                $update['sort_price'] = $fixed_price;
            } else if ($method == "입찰") {
                $bid_duration = $this->input->post('bid_duration');
                $bid_start_price = $this->input->post('bid_start_price');
                $bid_price = $this->input->post('bid_price');
                $reduction_rate = $this->input->post('reduction_rate');
                $repeat_no = $this->input->post('repeat_no');
                
                $detail_data['bid_duration'] = $bid_duration;
                $detail_data['bid_start_price'] = $bid_start_price;
                $detail_data['bid_current_price'] = $prev_data['bid_current_price'] < $bid_start_price ? $bid_start_price : $prev_data['bid_current_price'];
                $detail_data['bid_price'] = $bid_price;
                $detail_data['reduction_rate'] = $reduction_rate;
                $detail_data['repeat_no'] = $repeat_no;
                $detail_data['bid_res_price'] = $prev_data['bid_res_price'];
                $detail_data['bid_yn'] = $prev_data['bid_yn'];
                $update['sort_price'] = $detail_data['bid_current_price'];
            }
            $this->db->where('info_seq', $info_seq);
            $this->db->delete('fm_cm_machine_sales_detail');
            $this->db->insert('fm_cm_machine_sales_detail', $detail_data);
            $this->db->where('info_seq', $info_seq);
            $this->db->update('fm_cm_machine_sales_info', $update);
            
            $ad_name_arr = $this->input->post('ad_name');
            $ad_price_arr = $this->input->post('ad_price');
            
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
            $this->insert_check_data($prev_data['sales_seq']);
            
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
            
            if($wait_yn == 'y') {
                $this->db->where('sales_seq', $prev_data['sales_seq']);
                $this->db->update('fm_cm_machine_sales', $sales_wait_data);
            }

            if($is_pay == true) {
                $this->db->where('sales_seq', $prev_data['sales_seq']);
                $this->db->update('fm_cm_machine_sales', $sales_data);
                
                foreach($pay_data_list as &$pay_data) {
                    $pay_data['pay_no'] = $this->get_pay_no();
                    $pay_data['target_seq'] = $info_seq;
                    $this->db->insert('fm_cm_machine_pay', $pay_data);
                }
                $title = "유료서비스 변경<b>결제 안내</b>";
                $message = "※ 유료서비스 변경 결제안내\r\n판매자 " . $userData['userid'] . '님이 등록하신 '.$prev_data['model_name']."(" . $prev_data['sales_no'] . ")의 유료서비스가 변경되어 차액에 대한 결제가 필요합니다.\r\n- 입금안내 : 에스디네트웍스(신동훈), 농협은행, 계좌번호 302-1371-4082-81, 결제금액 ".number_format($diff_price)."원";
                
                $this->send_common_sms($userData['cellphone'], $message);
                $this->send_common_mail($userData['email'], $title, $message);
                
                if($wait_yn == 'y') 
                    $text = "기계 재등록이 완료되었습니다.\\n서비스 변경에 대한 차액 결제가 필요합니다. 결제 안내문자를 확인해주세요.";
                else 
                    $text = "기계정보 변경이 완료되었습니다.\\n서비스 변경에 대한 차액 결제가 필요합니다. 결제 안내문자를 확인해주세요.";
            } else {
                if($wait_yn == 'y')
                    $text = "기계 재등록이 완료되었습니다.";
                else 
                    $text = '기계정보 변경이 완료되었습니다.';
            }
            $this->db->where('info_seq', $info_seq);
            $this->db->update('fm_cm_machine_sales_info', $info_data);
            
            pageRedirect("/user/my_sale_".$page_type);
            $this->session->set_flashdata('message', $text);
        } 
    }
    
    public function emergency_intro()
    {
        $tpl = 'sale/emergency_intro.html';
        $skin = $this->skin;
        
        $this->template_path = $tpl;
        $this->template->assign(array("skin_path"=>$this->skin,
                                      "template_path"=>$this->template_path));
        
        $this->print_layout($skin.'/'.$tpl);
    }
    
    public function emergency_reg($info_seq, $page_type, $sale_type)
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
        $tpl = 'sale/emergency_reg.html';
        $skin = $this->skin;
        
        $this->template_path = $tpl;
        $this->template->assign(array("skin_path"=>$this->skin,
                                      "template_path"=>$this->template_path));
        
        $this->db->distinct('kind_type');
        $this->db->select('kind_type, kind_no');
        $query = $this->db->get('fm_cm_machine_kind');
        $result = $query->result_array();
        $kind_map = array();
        foreach($result as $type) {
            $this->db->where('kind_type', $type['kind_type']);
            $query = $this->db->get('fm_cm_machine_kind');
            $kind_list = $query->result_array();
            $kind_map[] = array('kind_type'=>$type['kind_type'],
                'kind_no'=>$type['kind_no'],
                'kind_list'=>$kind_list
            );
        }
        $this->template->assign('kind_map', $kind_map);
        
        $query = "select * from fm_cm_machine_manufacturer group by mnf_name order by mnf_name asc";
        $query = $this->db->query($query);
        $result = $query->result_array();
        $this->template->assign('mnf_list', $result);
        
        /*
         $query = "select * from fm_cm_machine_model order by model_name asc";
         $query = $this->db->query($query);
         $result = $query->result_array();
         $this->template->assign('model_list', $result);
         */
        
        $query = $this->db->get('fm_cm_machine_area');
        $result = $query->result_array();
        $this->template->assign('area_list', $result);
        
        if(isset($info_seq) && $page_type != 'temp') {
            $sale_info = $this->get_detail_query($sale_type, $info_seq);
            $this->template->assign($sale_info['info_list']);
            $this->template->assign($sale_info);
        }
        if($page_type == 'temp')
            $this->template->assign('temp_seq', $info_seq);
            $this->template->assign('page_type', $page_type);
            
        $this->print_layout($skin.'/'.$tpl);
    }
    
    public function emergency_process() {
        $kind_no_arr = $this->input->post('kind_no_arr');
        $kind_seq_arr = $this->input->post('kind_seq_arr');
        $mnf_seq_arr = $this->input->post('mnf_seq_arr');
        $input_mnf_arr = $this->input->post('input_mnf_arr');
        $txt_mnf_arr = $this->input->post('txt_mnf_arr');
        $model_seq_arr = $this->input->post('model_seq_arr');
        $input_model_arr = $this->input->post('input_model_arr');
        $txt_model_arr = $this->input->post('txt_model_arr');
        $area_seq_arr = $this->input->post('area_seq_arr');
        $model_year_arr = $this->input->post('model_year_arr');
        $serial_num_arr = $this->input->post('serial_num_arr');
        $size_arr = $this->input->post('size_arr');
        $weight_arr = $this->input->post('weight_arr');
        $controller_arr = $this->input->post('controller_arr');
        $hope_price_arr = $this->input->post('hope_price_arr');
        $deliver_condition_arr = $this->input->post('deliver_condition_arr');
        $machine_cnt = $this->input->post('machine_cnt');
        $machine_remove = $this->input->post('machine_remove');
        $option_index_arr = $this->input->post('option_index_arr');
        $option_name_arr = $this->input->post('option_name_arr');
        $part_arr = $this->input->post('part');
        $sort_arr = $this->input->post('sort');
        
        $userData = $this->getUserData();
        $sales_data = array();
        $sales_data['type'] = 'emergency';
        $sales_data['userid'] = $userData['userid'];
        $this->db->insert('fm_cm_machine_sales', $sales_data);
        $sales_seq = $this->db->insert_id();
        
        $info_seq = $this->input->post('info_seq');
        $page_type = $this->input->post('page_type');
        
        if(!empty($info_seq)) {
            $mode = 'modify';
        } else {
            $mode = 'insert';
        }
        $this->load->library('upload');
        $files = $_FILES;
        
        if($mode == 'insert') {
            for($i=0; $i<count($kind_seq_arr); $i++) {
                $query = "select * from fm_cm_machine_kind where kind_seq = ".$kind_seq_arr[$i];
                $query = $this->db->query($query);
                $kind = $query->row_array();
                if($input_mnf_arr[$i] == 'true' && isset($txt_mnf_arr[$i])) {
                    $data = array(
                        'mnf_name' => $txt_mnf_arr[$i],
                        'mnf_kind' => $kind['kind_name']
                    );
                    $this->db->insert('fm_cm_machine_manufacturer', $data);
                    $mnf_seq_arr[$i] = $this->db->insert_id();
                }
                if($input_model_arr[$i] == 'true' && isset($txt_model_arr[$i])) {
                    $query = "select * from fm_cm_machine_manufacturer where mnf_seq = ".$mnf_seq_arr[$i];
                    $query = $this->db->query($query);
                    $mnf = $query->row_array();
                    $data = array(
                        'model_name' => $txt_model_arr[$i],
                        'model_kind' => $kind['kind_name'],
                        'model_mnf' => $mnf['mnf_name']
                    );
                    $this->db->insert('fm_cm_machine_model', $data);
                    $model_seq_arr[$i] = $this->db->insert_id();
                }
                
                $info_data = array(
                    'sales_no' => $this->getSalesNo($kind_no_arr[$i]),
                    'sales_seq' => $sales_seq,
                    'kind_seq' => $kind_seq_arr[$i],
                    'mnf_seq' => $mnf_seq_arr[$i],
                    'model_seq' => $model_seq_arr[$i],
                    'area_seq' => $area_seq_arr[$i],
                    'model_year' => $model_year_arr[$i],
                    'serial_num' => $serial_num_arr[$i],
                    'size' => $size_arr[$i],
                    'weight' => $weight_arr[$i],
                    'controller' => $controller_arr[$i],
                    'hope_price' => $hope_price_arr[$i],
                    'deliver_condition' => $deliver_condition_arr[$i],
                    'state' => '미승인'
                );
                $this->db->insert('fm_cm_machine_sales_info', $info_data);
                $info_seq = $this->db->insert_id();
                
                $option_index = array_shift($option_index_arr);
                
                foreach($option_name_arr as $option) {
                    $split = explode('_', $option);
                    $split[0] = str_replace('#', '', $split[0]);
                    if($split[0] == $option_index) {
                        $option_data = array();
                        $option_data['info_seq'] = $info_seq;
                        $option_data['option_name'] = $split[1];
                        $this->db->insert('fm_cm_machine_sales_option', $option_data);
                    }
                }
                $upload_path = "./data/uploads/machine";
                $filename = 'machine_picture_'.$option_index;
                $cnt = count($_FILES[$filename]['name']);
                for($j=0; $j<$cnt; $j++) {
                    if($files[$filename]['name'][$j] == null) continue;
                    
                    $_FILES[$filename]['name'] = $files[$filename]['name'][$j];
                    $_FILES[$filename]['type'] = $files[$filename]['type'][$j];
                    $_FILES[$filename]['tmp_name'] = $files[$filename]['tmp_name'][$j];
                    $_FILES[$filename]['error'] = $files[$filename]['error'][$j];
                    $_FILES[$filename]['size'] = $files[$filename]['size'][$j];
                    
                    $this->upload->initialize($this->set_upload_options());
                    if($this->upload->do_upload($filename)) {
                        $upload_data = $this->upload->data();
                        $picture_data = array();
                        $picture_data['info_seq'] = $info_seq;
                        $picture_data['path'] = str_replace(".", "", $upload_path).'/'.$upload_data['file_name'];
                        $picture_data['part'] = $part_arr[$j];
                        $picture_data['sort'] = $sort_arr[$j];
                        $this->db->insert('fm_cm_machine_sales_picture', $picture_data);
                    }
                }
            }
            $this->insert_check_data($sales_seq);
            
            //$this->send_email('emergency');
            //$this->send_sms('emergency');
            pageRedirect("/sale/sale_complete/emergency");
            exit;
        } else if ($mode == 'modify') {
            $kind_seq = $this->input->post('kind_seq');
            $mnf_seq = $this->input->post('mnf_seq');
            $input_mnf = $this->input->post('input_mnf');
            $txt_mnf = $this->input->post('txt_mnf');
            $model_seq = $this->input->post('model_seq');
            $input_model = $this->input->post('input_model');
            $txt_model = $this->input->post('txt_model');
            $area_seq = $this->input->post('area_seq');
            $model_year = $this->input->post('model_year');
            $serial_num = $this->input->post('serial_num');
            $size = $this->input->post('size');
            $weight = $this->input->post('weight');
            $controller = $this->input->post('controller');
            $hope_price = $this->input->post('hope_price');
            $deliver_condition = $this->input->post('deliver_condition');
            $part_arr = $this->input->post('part');
            $sort_arr = $this->input->post('sort');
            
            $query = "select * from fm_cm_machine_kind where kind_seq = ".$kind_seq;
            $query = $this->db->query($query);
            $kind = $query->row_array();
            if($input_mnf == 'true' && isset($txt_mnf)) {
                $data = array(
                    'mnf_name' => $txt_mnf,
                    'mnf_kind' => $kind['kind_name']
                );
                $this->db->insert('fm_cm_machine_manufacturer', $data);
                $mnf_seq = $this->db->insert_id();
            }
            if($input_model_arr[$i] == 'true' && isset($txt_model_arr[$i])) {
                $query = "select * from fm_cm_machine_manufacturer where mnf_seq = ".$mnf_seq;
                $query = $this->db->query($query);
                $mnf = $query->row_array();
                $data = array(
                    'model_name' => $txt_model,
                    'model_kind' => $kind['kind_name'],
                    'model_mnf' => $mnf['mnf_name']
                );
                $this->db->insert('fm_cm_machine_model', $data);
                $model_seq = $this->db->insert_id();
            }
            
            $info_data = array(
                'kind_seq' => $kind_seq,
                'mnf_seq' => $mnf_seq,
                'model_seq' => $model_seq,
                'area_seq' => $area_seq,
                'model_year' => $model_year,
                'serial_num' => $serial_num,
                'size' => $size,
                'weight' => $weight,
                'controller' => $controller,
                'hope_price' => $hope_price,
                'deliver_condition' => $deliver_condition
            );
            $this->db->where('info_seq', $info_seq);
            $this->db->update('fm_cm_machine_sales_info', $info_data);
            
            $this->db->where('info_seq', $info_seq);
            $this->db->delete('fm_cm_machine_sales_option');
            foreach($option_name_arr as $option) {
                $option_data = array();
                $option_data['info_seq'] = $info_seq;
                $option_data['option_name'] = $option;
                $this->db->insert('fm_cm_machine_sales_option', $option_data);
            }
            
            $upload_path = "./data/uploads/machine";
            $filename = 'machine_picture_1';
            $cnt = count($_FILES[$filename]['name']);
            for($i=0; $i<$cnt; $i++) {
                if($files[$filename]['name'][$i] == null) continue;
                $_FILES[$filename]['name'] = $files[$filename]['name'][$i];
                $_FILES[$filename]['type'] = $files[$filename]['type'][$i];
                $_FILES[$filename]['tmp_name'] = $files[$filename]['tmp_name'][$i];
                $_FILES[$filename]['error'] = $files[$filename]['error'][$i];
                $_FILES[$filename]['size'] = $files[$filename]['size'][$i];
                
                $this->upload->initialize($this->set_upload_options());
                if($this->upload->do_upload($filename)) {
                    $upload_data = $this->upload->data();
                    $picture_data = array();
                    $picture_data['info_seq'] = $info_seq;
                    $picture_data['path'] = str_replace(".", "", $upload_path).'/'.$upload_data['file_name'];
                    $picture_data['part'] = $part_arr[$i];
                    $picture_data['sort'] = $sort_arr[$i];
                    $this->db->where('info_seq', $info_seq)
                    ->where('part', $part_arr[$i]);
                    $this->db->delete('fm_cm_machine_sales_picture');
                    $this->db->insert('fm_cm_machine_sales_picture', $picture_data);
                }
            }
            pageRedirect("/user/my_sale_".$page_type);
            $this->session->set_flashdata('message', '기계정보 변경이 완료되었습니다.');
        }
        
    }
    
    public function direct_intro()
    {
        $tpl = 'sale/direct_intro.html';
        $skin = $this->skin;
        
        $this->template_path = $tpl;
        $this->template->assign(array("skin_path"=>$this->skin,
                                      "template_path"=>$this->template_path));
        
        $this->print_layout($skin.'/'.$tpl);
    }
    
    public function direct_reg($info_seq, $page_type, $sale_type)
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
        $tpl = 'sale/direct_reg.html';
        $skin = $this->skin;
        
        $this->template_path = $tpl;
        $this->template->assign(array("skin_path"=>$this->skin,
                                      "template_path"=>$this->template_path));
        
        $this->db->distinct('kind_type');
        $this->db->select('kind_type, kind_no');
        $query = $this->db->get('fm_cm_machine_kind');
        $result = $query->result_array();
        $kind_map = array();
        foreach($result as $type) {
            $this->db->where('kind_type', $type['kind_type']);
            $query = $this->db->get('fm_cm_machine_kind');
            $kind_list = $query->result_array();
            $kind_map[] = array('kind_type'=>$type['kind_type'],
                                  'kind_no'=>$type['kind_no'],
                                  'kind_list'=>$kind_list
            );
        }
        $this->template->assign('kind_map', $kind_map);
        
        $query = "select * from fm_cm_machine_manufacturer group by mnf_name order by mnf_name asc";
        $query = $this->db->query($query);
        $result = $query->result_array();
        $this->template->assign('mnf_list', $result);
        
        /*
         $query = "select * from fm_cm_machine_model order by model_name asc";
         $query = $this->db->query($query);
         $result = $query->result_array();
         $this->template->assign('model_list', $result);
         */
        
        $query = $this->db->get('fm_cm_machine_area');
        $result = $query->result_array();
        $this->template->assign('area_list', $result);
        
        if(isset($info_seq) && $page_type != 'temp') {
            $sale_info = $this->get_detail_query($sale_type, $info_seq);
            $this->template->assign($sale_info['info_list']);
            $this->template->assign($sale_info);
        }
        if($page_type == 'temp')
            $this->template->assign('temp_seq', $info_seq);
        $this->template->assign('page_type', $page_type);
        
        $this->print_layout($skin.'/'.$tpl);
    }
    
    public function direct_process() {
        $kind_no_arr = $this->input->post('kind_no_arr');
        $kind_seq_arr = $this->input->post('kind_seq_arr');
        $mnf_seq_arr = $this->input->post('mnf_seq_arr');
        $input_mnf_arr = $this->input->post('input_mnf_arr');
        $txt_mnf_arr = $this->input->post('txt_mnf_arr');
        $model_seq_arr = $this->input->post('model_seq_arr');
        $input_model_arr = $this->input->post('input_model_arr');
        $txt_model_arr = $this->input->post('txt_model_arr');
        $area_seq_arr = $this->input->post('area_seq_arr');
        $model_year_arr = $this->input->post('model_year_arr');
        $serial_num_arr = $this->input->post('serial_num_arr');
        $size_arr = $this->input->post('size_arr');
        $weight_arr = $this->input->post('weight_arr');
        $controller_arr = $this->input->post('controller_arr');
        $hope_price_arr = $this->input->post('hope_price_arr');
        $deliver_condition_arr = $this->input->post('deliver_condition_arr');
        $machine_cnt = $this->input->post('machine_cnt');
        $machine_remove = $this->input->post('machine_remove');
        $option_index_arr = $this->input->post('option_index_arr');
        $option_name_arr = $this->input->post('option_name_arr');
        $part_arr = $this->input->post('part');
        $sort_arr = $this->input->post('sort');
        
        $info_seq = $this->input->post('info_seq');
        $page_type = $this->input->post('page_type');
        
        if(!empty($info_seq)) {
            $mode = 'modify';
        } else {
            $mode = 'insert';
        }
        
        $this->load->library('upload');
        $files = $_FILES;
     
        if($mode == 'insert') {
            $userData = $this->getUserData();
            $sales_data = array();
            $sales_data['type'] = 'direct';
            $sales_data['userid'] = $userData['userid'];
            $this->db->insert('fm_cm_machine_sales', $sales_data);
            $sales_seq = $this->db->insert_id();
            
            for($i=0; $i<count($kind_seq_arr); $i++) {
                $query = "select * from fm_cm_machine_kind where kind_seq = ".$kind_seq_arr[$i];
                $query = $this->db->query($query);
                $kind = $query->row_array();
                if($input_mnf_arr[$i] == 'true' && isset($txt_mnf_arr[$i])) {
                    $data = array(
                        'mnf_name' => $txt_mnf_arr[$i],
                        'mnf_kind' => $kind['kind_name']
                    );
                    $this->db->insert('fm_cm_machine_manufacturer', $data);
                    $mnf_seq_arr[$i] = $this->db->insert_id();
                }
                
                if($input_model_arr[$i] == 'true' && isset($txt_model_arr[$i])) {
                    $query = "select * from fm_cm_machine_manufacturer where mnf_seq = ".$mnf_seq_arr[$i];
                    $query = $this->db->query($query);
                    $mnf = $query->row_array();
                    $data = array(
                        'model_name' => $txt_model_arr[$i],
                        'model_kind' => $kind['kind_name'],
                        'model_mnf' => $mnf['mnf_name']
                    );
                    $this->db->insert('fm_cm_machine_model', $data);
                    $model_seq_arr[$i] = $this->db->insert_id();
                }
                $info_data = array(
                    'sales_no' => $this->getSalesNo($kind_no_arr[$i]),
                    'sales_seq' => $sales_seq,
                    'kind_seq' => $kind_seq_arr[$i],
                    'mnf_seq' => $mnf_seq_arr[$i],
                    'model_seq' => $model_seq_arr[$i],
                    'area_seq' => $area_seq_arr[$i],
                    'model_year' => $model_year_arr[$i],
                    'serial_num' => $serial_num_arr[$i],
                    'size' => $size_arr[$i],
                    'weight' => $weight_arr[$i],
                    'controller' => $controller_arr[$i],
                    'hope_price' => $hope_price_arr[$i],
                    'deliver_condition' => $deliver_condition_arr[$i],
                    'state' => '미승인'
                );
                $this->db->insert('fm_cm_machine_sales_info', $info_data);
                $info_seq = $this->db->insert_id();
                $option_index = array_shift($option_index_arr);
                
                foreach($option_name_arr as $option) {
                    $split = explode('_', $option);
                    $split[0] = str_replace('#', '', $split[0]);
                    if($split[0] == $option_index) {
                        $option_data = array();
                        $option_data['info_seq'] = $info_seq;
                        $option_data['option_name'] = $split[1];
                        $this->db->insert('fm_cm_machine_sales_option', $option_data);
                    }
                }
                $upload_path = "./data/uploads/machine";
                $filename = 'machine_picture_'.$option_index;
                $cnt = count($_FILES[$filename]['name']);
                for($j=0; $j<$cnt; $j++) {
                    if($files[$filename]['name'][$j] == null) continue;
                    
                    $_FILES[$filename]['name'] = $files[$filename]['name'][$j];
                    $_FILES[$filename]['type'] = $files[$filename]['type'][$j];
                    $_FILES[$filename]['tmp_name'] = $files[$filename]['tmp_name'][$j];
                    $_FILES[$filename]['error'] = $files[$filename]['error'][$j];
                    $_FILES[$filename]['size'] = $files[$filename]['size'][$j];
                    
                    $this->upload->initialize($this->set_upload_options());
                    if($this->upload->do_upload($filename)) {
                        $upload_data = $this->upload->data();
                        $picture_data = array();
                        $picture_data['info_seq'] = $info_seq;
                        $picture_data['path'] = str_replace(".", "", $upload_path).'/'.$upload_data['file_name'];
                        $picture_data['part'] = $part_arr[$j];
                        $picture_data['sort'] = $sort_arr[$j];
                        $this->db->insert('fm_cm_machine_sales_picture', $picture_data);
                    }
                }
            }
            $this->insert_check_data($sales_seq);
            
            //$this->send_email('direct');
            //$this->send_sms('direct');
            pageRedirect("/sale/sale_complete/direct");
        } else if ($mode == 'modify'){
            $kind_seq = $this->input->post('kind_seq');
            $mnf_seq = $this->input->post('mnf_seq');
            $input_mnf = $this->input->post('input_mnf');
            $txt_mnf = $this->input->post('txt_mnf');
            $model_seq = $this->input->post('model_seq');
            $input_model = $this->input->post('input_model');
            $txt_model = $this->input->post('txt_model');
            $area_seq = $this->input->post('area_seq');
            $model_year = $this->input->post('model_year');
            $serial_num = $this->input->post('serial_num');
            $size = $this->input->post('size');
            $weight = $this->input->post('weight');
            $controller = $this->input->post('controller');
            $hope_price = $this->input->post('hope_price');
            $deliver_condition = $this->input->post('deliver_condition');
            $part_arr = $this->input->post('part');
            $sort_arr = $this->input->post('sort');
            
            $query = "select * from fm_cm_machine_kind where kind_seq = ".$kind_seq;
            $query = $this->db->query($query);
            $kind = $query->row_array();
            if($input_mnf == 'true' && isset($txt_mnf)) {
                $data = array(
                    'mnf_name' => $txt_mnf,
                    'mnf_kind' => $kind['kind_name']
                );
                $this->db->insert('fm_cm_machine_manufacturer', $data);
                $mnf_seq = $this->db->insert_id();
            }
            if($input_model_arr[$i] == 'true' && isset($txt_model_arr[$i])) {
                $query = "select * from fm_cm_machine_manufacturer where mnf_seq = ".$mnf_seq;
                $query = $this->db->query($query);
                $mnf = $query->row_array();
                $data = array(
                    'model_name' => $txt_model,
                    'model_kind' => $kind['kind_name'],
                    'model_mnf' => $mnf['mnf_name']
                );
                $this->db->insert('fm_cm_machine_model', $data);
                $model_seq = $this->db->insert_id();
            }
            
            $info_data = array(
                'kind_seq' => $kind_seq,
                'mnf_seq' => $mnf_seq,
                'model_seq' => $model_seq,
                'area_seq' => $area_seq,
                'model_year' => $model_year,
                'serial_num' => $serial_num,
                'size' => $size,
                'weight' => $weight,
                'controller' => $controller,
                'hope_price' => $hope_price,
                'deliver_condition' => $deliver_condition
            );
            $this->db->where('info_seq', $info_seq);
            $this->db->update('fm_cm_machine_sales_info', $info_data);
            
            $this->db->where('info_seq', $info_seq);
            $this->db->delete('fm_cm_machine_sales_option');
            foreach($option_name_arr as $option) {
                $option_data = array();
                $option_data['info_seq'] = $info_seq;
                $option_data['option_name'] = $option;
                $this->db->insert('fm_cm_machine_sales_option', $option_data);
            }
            
            $upload_path = "./data/uploads/machine";
            $filename = 'machine_picture_1';
            $cnt = count($_FILES[$filename]['name']);
            for($i=0; $i<$cnt; $i++) {
                if($files[$filename]['name'][$i] == null) continue;
                $_FILES[$filename]['name'] = $files[$filename]['name'][$i];
                $_FILES[$filename]['type'] = $files[$filename]['type'][$i];
                $_FILES[$filename]['tmp_name'] = $files[$filename]['tmp_name'][$i];
                $_FILES[$filename]['error'] = $files[$filename]['error'][$i];
                $_FILES[$filename]['size'] = $files[$filename]['size'][$i];
                
                $this->upload->initialize($this->set_upload_options());
                if($this->upload->do_upload($filename)) {
                    $upload_data = $this->upload->data();
                    $picture_data = array();
                    $picture_data['info_seq'] = $info_seq;
                    $picture_data['path'] = str_replace(".", "", $upload_path).'/'.$upload_data['file_name'];
                    $picture_data['part'] = $part_arr[$i];
                    $picture_data['sort'] = $sort_arr[$i];
                    $this->db->where('info_seq', $info_seq)
                             ->where('part', $part_arr[$i]);
                    $this->db->delete('fm_cm_machine_sales_picture');
                    $this->db->insert('fm_cm_machine_sales_picture', $picture_data);
                }
            }
            pageRedirect("/user/my_sale_".$page_type);
            $this->session->set_flashdata('message', '기계정보 변경이 완료되었습니다.');
        }
    }
    
    public function getLowestModel() {
        header("Content-Type: application/json");

        $kind_name = $_POST['kind_name'];
        $mnf_name = $_POST['mnf_name'];
        $model_name = $_POST['model_name'];
        
        if(!empty($kind_name)) $data['kind'] = $kind_name;
        if(!empty($mnf_name)) $data['mnf'] = $mnf_name;
        if(!empty($model_name)) $data['model'] = $model_name;
        
        $response = $this->getLowestQuery($data);
        
        echo json_encode(array("query" => $response['query'], "result1" => $response['result1'], "result2" => $response['result2'],
                               "result3" => $response['result3'], "result4" => $response['result4']));
    }
    
    public function turnkey_intro()
    {
        $tpl = 'sale/turnkey_intro.html';
        $skin = $this->skin;
        
        $this->template_path = $tpl;
        $this->template->assign(array("skin_path"=>$this->skin,
            "template_path"=>$this->template_path));
        
        $this->print_layout($skin.'/'.$tpl);
    }
    
    public function turnkey_reg($info_seq, $page_type, $sale_type) {
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
        $tpl = 'sale/turnkey_reg.html';
        $skin = $this->skin;
        
        $this->template_path = $tpl;
        $this->template->assign(array("skin_path"=>$this->skin,
            "template_path"=>$this->template_path));
        
        $this->db->distinct('kind_type');
        $this->db->select('kind_type, kind_no');
        $query = $this->db->get('fm_cm_machine_kind');
        $result = $query->result_array();
        $kind_map = array();
        foreach($result as $type) {
            $this->db->where('kind_type', $type['kind_type']);
            $query = $this->db->get('fm_cm_machine_kind');
            $kind_list = $query->result_array();
            $kind_map[] = array('kind_type'=>$type['kind_type'],
                'kind_no'=>$type['kind_no'],
                'kind_list'=>$kind_list
            );
        }
        $this->template->assign('kind_map', $kind_map);
        
        $query = "select * from fm_cm_machine_manufacturer group by mnf_name order by mnf_name asc";
        $query = $this->db->query($query);
        $result = $query->result_array();
        $this->template->assign('mnf_list', $result);
        
        /*
         $query = "select * from fm_cm_machine_model order by model_name asc";
         $query = $this->db->query($query);
         $result = $query->result_array();
         $this->template->assign('model_list', $result);
         */
        
        $query = $this->db->get('fm_cm_machine_area');
        $result = $query->result_array();
        $this->template->assign('area_list', $result);
        
        if($page_type == 'temp')
            $this->template->assign('temp_seq', $info_seq);
        $this->template->assign('page_type', $page_type);
            
        $this->print_layout($skin.'/'.$tpl);
    }
    
    public function turnkey_process() {
        $kind_no_arr = $this->input->post('kind_no_arr');
        $kind_seq_arr = $this->input->post('kind_seq_arr');
        $mnf_seq_arr = $this->input->post('mnf_seq_arr');
        $input_mnf_arr = $this->input->post('input_mnf_arr');
        $txt_mnf_arr = $this->input->post('txt_mnf_arr');
        $model_seq_arr = $this->input->post('model_seq_arr');
        $input_model_arr = $this->input->post('input_model_arr');
        $txt_model_arr = $this->input->post('txt_model_arr');
        $model_year_arr = $this->input->post('model_year_arr');
        $pur_price_arr = $this->input->post('pur_price_arr');
        $remark_arr = $this->input->post('remark_arr');
        $machine_cnt = $this->input->post('machine_cnt');
        $machine_remove = $this->input->post('machine_remove');
        $option_index_arr = $this->input->post('option_index_arr');
        $option_name_arr = $this->input->post('option_name_arr');
        $factory = $this->input->post('factory');
        $production = $this->input->post('production');
        $area_seq = $this->input->post('area_seq');
        $quantity = $this->input->post('quantity');
        $creditor = $this->input->post('creditor');
        $last_date = $this->input->post('last_date');
        $expect_date = $this->input->post('expect_date');
        
        $userData = $this->getUserData();
        
        $sales_data = array();
        $sales_data['userid'] = $userData['userid'];
        $sales_data['type'] = 'turnkey';
        $this->db->insert('fm_cm_machine_sales', $sales_data);
        $sales_seq = $this->db->insert_id();
        
        $turnkey_data = array();
        $turnkey_data['sales_seq'] = $sales_seq;
        $turnkey_data['factory'] = $factory;
        $turnkey_data['production'] = $production;
        $turnkey_data['quantity'] = $quantity;
        $turnkey_data['creditor'] = $creditor;
        $turnkey_data['last_date'] = $last_date;
        $turnkey_data['expect_date'] = $expect_date;
        $this->db->insert('fm_cm_machine_sales_turnkey', $turnkey_data);
        $turnkey_seq = $this->db->insert_id();
        
        for($i=0; $i<count($kind_seq_arr); $i++) {
            $query = "select * from fm_cm_machine_kind where kind_seq = ".$kind_seq_arr[$i];
            $query = $this->db->query($query);
            $kind = $query->row_array();
            if($input_mnf_arr[$i] == 'true' && isset($txt_mnf_arr[$i])) {
                $data = array(
                    'mnf_name' => $txt_mnf_arr[$i],
                    'mnf_kind' => $kind['kind_name']
                );
                $this->db->insert('fm_cm_machine_manufacturer', $data);
                $mnf_seq_arr[$i] = $this->db->insert_id();
            }
            if($input_model_arr[$i] == 'true' && isset($txt_model_arr[$i])) {
                $query = "select * from fm_cm_machine_manufacturer where mnf_seq = ".$mnf_seq_arr[$i];
                $query = $this->db->query($query);
                $mnf = $query->row_array();
                $data = array(
                    'model_name' => $txt_model_arr[$i],
                    'model_kind' => $kind['kind_name'],
                    'model_mnf' => $mnf['mnf_name']
                );
                $this->db->insert('fm_cm_machine_model', $data);
                $model_seq_arr[$i] = $this->db->insert_id();
            }
            $info_data = array(
                'sales_seq' => $sales_seq,
                'sales_no' => $this->getSalesNo($kind_no_arr[$i]),
                'kind_seq' => $kind_seq_arr[$i],
                'mnf_seq' => $mnf_seq_arr[$i],
                'model_seq' => $model_seq_arr[$i],
                'area_seq' => $area_seq,
                'model_year' => $model_year_arr[$i],
                'pur_price' => $pur_price_arr[$i],
                'sort_price' => $pur_price_arr[$i],
                'remark' => $remark_arr[$i],
                'state' => '미승인'
            );
            $this->db->insert('fm_cm_machine_sales_info', $info_data);
            $info_seq = $this->db->insert_id();
            $option_index = array_shift($option_index_arr);
            
            foreach($option_name_arr as $option) {
                $split = explode('_', $option);
                $split[0] = str_replace('#', '', $split[0]);
                if($split[0] == $option_index) {
                    $option_data = array();
                    $option_data['info_seq'] = $info_seq;
                    $option_data['option_name'] = $split[1];
                    $this->db->insert('fm_cm_machine_sales_option', $option_data);
                }
            }
        }
        $this->insert_check_data($sales_seq);
        
        //$this->send_email('turnkey');
        //$this->send_sms('turnkey');
        pageRedirect("/sale/sale_complete/turnkey");
    }
    
    public function sale_complete($type, $sales_seq) {
        if(!$this->sessionCheck()) {
            $this->session->set_flashdata('message', '로그인이 필요한 페이지입니다');
            pageRedirect("/user/login");
        }
        $tpl = 'sale/sale_complete.html';
        $skin = $this->skin;
        
        $this->template_path = $tpl;
        $this->template->assign(array("skin_path"=>$this->skin,
            "template_path"=>$this->template_path));
        
        if(isset($sales_seq)) {
            $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b where a.sales_seq = b.sales_seq and a.sales_seq = ".$sales_seq;
            $query = $this->db->query($query);
            $result = $query->row_array();
            $this->template->assign('sales_seq', $sales_seq);
        }
        $this->template->assign('type', $type);
        $this->template->assign($result);
        
        $this->print_layout($skin.'/'.$tpl);
    }   
    
    public function get_mnf_list() {
        header("Content-Type: application/json");
        
        $kind_name = $this->input->post('kind_name');
        
        $query = "select * from fm_cm_machine_manufacturer where mnf_kind = '".$kind_name."' order by mnf_name asc";
        $query = $this->db->query($query);
        $mnf_list = $query->result_array();
        
        echo json_encode(array('mnf_list' => $mnf_list));
    }
    
    public function get_model_list() {
        header("Content-Type: application/json");
        
        $kind_name = $this->input->post('kind_name');
        $mnf_name = $this->input->post('mnf_name');
        
        $query = "select * from fm_cm_machine_model where model_kind = '".$kind_name."' and model_mnf = '".$mnf_name."' order by model_name asc";
        $query = $this->db->query($query);
        $model_list = $query->result_array();
        
        echo json_encode(array('model_list' => $model_list));
    }
    
    public function get_mnf_one() {
        header("Content-Type: application/json");
        
        $kind_name = $this->input->post('kind_name');
        $mnf_name = $this->input->post('mnf_name');
        
        $query = "select * from fm_cm_machine_manufacturer where mnf_kind = '".$kind_name."' and mnf_name = '".$mnf_name."'";
        $query = $this->db->query($query);
        $item = $query->row_array();
        if(empty($item))
            $result = false;
        else
            $result = true;
        
        echo json_encode(array('result' => $result, 'item' => $item));
    }
    
    public function get_model_one() {
        header("Content-Type: application/json");
        
        $mnf_name = $this->input->post('mnf_name');
        $model_name = $this->input->post('model_name');
        
        $query = "select * from fm_cm_machine_model where model_mnf = '".$mnf_name."' and model_name = '".$model_name."'";
        $query = $this->db->query($query);
        $item = $query->row_array();
        if(empty($item))
            $result = false;
        else
            $result = true;
                
        echo json_encode(array('result' => $result, 'item' => $item));
    }
    
    private function set_upload_options() {
        $upload_path = "./data/uploads/machine";
        if(!is_dir($upload_path)) {
            mkdir($upload_path, 0777, true);
        }
        $config = array();
        $config['upload_path'] = $upload_path;
        $config['allowed_types'] = 'gif|jpg|png';
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
            sale_reg_mail($email, $reg_type);
        }
    }
    
    private function send_sms($reg_type) {
        $userData = $this->getUserData();
        $phone = $userData['cellphone'];
        if($phone) {
            sale_reg_sms($phone, $reg_type);
        }
    }
    
    private function send_common_sms($phone, $message) {
        if($phone) {
            send_common_sms($phone, $message);
        }
    }
    
    private function send_common_mail($email, $title, $message) {
        if($email) {
            send_common_mail($email, $title, $message);
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
    
    private function getSalesNo($kind_no) {
        $query = "select date_format(curdate(), '%y%m%d') as today, count(*) as count ".
            "from fm_cm_machine_sales a, fm_cm_machine_kind b, ".
            "fm_cm_machine_sales_info c where a.sales_seq = c.sales_seq ".
            "and b.kind_seq = c.kind_seq and b.kind_no = ". $kind_no." ".
            "and substring(sales_no, 3, 6) = date_format(curdate(), '%y%m%d')";
        $query = $this->db->query($query);
        $result = $query->row_array();
        $no = (int)$result['count'] + 1;
        $no = sprintf("%04d", $no);
        $kind_no = sprintf("%02d", $kind_no);
        return $kind_no.$result['today'].$no;
    }
    
    private function getLowestQuery($data) {
         $binding_arr = array();
         $from_add = "";
         $where_add = "";
         
         $from_add .= ", fm_cm_machine_kind e";
         $from_add .= ", fm_cm_machine_manufacturer f";
         $from_add .= ", fm_cm_machine_model g";
         $where_add .= "and e.kind_seq = a.kind_seq ";
         $where_add .= "and f.mnf_seq = a.mnf_seq ";
         $where_add .= "and g.model_seq = a.model_seq ";
         foreach($data as $key => $value) {
             if($key == "kind") {
                $where_add .= "and e.kind_name = '".$value."' ";
             } else if($key == "mnf") {
                 $where_add .= "and f.mnf_name = '".$value."' ";
             } else if($key == "model") {
                 $where_add .= "and g.model_name = '".$value."' ";
             }
         }
         
        // 다이렉트 최저가
         $query = "select * from fm_cm_machine_sales b".$from_add.", fm_cm_machine_sales_info a ".
                  "left outer join fm_cm_machine_sales_picture c on a.info_seq = c.info_seq and c.sort = 2 ". 
                  "where b.sales_seq = a.sales_seq and a.state = '승인' and b.type = 'direct' and a.real_price > 0 ".
                  $where_add." order by a.real_price asc limit 1";
        $response['query'] = $query;
        $query = $this->db->query($query);
        $result1 = $query->row();
        
        // 긴급판매 최저가
        $query = "select * from fm_cm_machine_sales b".$from_add.", fm_cm_machine_sales_info a ".
                 "left outer join fm_cm_machine_sales_picture c on a.info_seq = c.info_seq and c.sort = 2 ". 
                 "where b.sales_seq = a.sales_seq and a.info_seq = c.info_seq and a.state = '승인' and b.type = 'emergency' and a.real_price > 0 ".
                 $where_add . " order by a.real_price asc limit 1";
        $query = $this->db->query($query);
        $result2 = $query->row();
            
        // 셀프 - 고정가격판매 최저가
        $query = "select * from fm_cm_machine_sales b, fm_cm_machine_sales_detail c".$from_add.", ".
                 "fm_cm_machine_sales_info a left outer join fm_cm_machine_sales_picture d on a.info_seq = d.info_seq and d.sort = 2 ".
                 "where b.sales_seq = a.sales_seq and a.info_seq = c.info_seq and ".
                 "a.info_seq = d.info_seq and a.state = '승인' and b.type = 'self' and c.method='고정가격판매' ".
                 $where_add . " order by c.fixed_price asc limit 1";
        $query = $this->db->query($query);
        $result3 = $query->row();
        
        // 셀프 - 입찰판매 최저가
        $query = "select * from fm_cm_machine_sales b, fm_cm_machine_sales_detail c".$from_add.", ".
            "fm_cm_machine_sales_info a left outer join fm_cm_machine_sales_picture d on a.info_seq = d.info_seq and d.sort = 2 ".
                 "where b.sales_seq = a.sales_seq and a.info_seq = c.info_seq and ".
                 "a.info_seq = d.info_seq and a.state = '승인' and b.type = 'self' and c.method='입찰' ".
                 $where_add . " order by c.bid_price asc limit 1";
        $query = $this->db->query($query);
        $result4 = $query->row();
        
        $response['result1'] = $result1;
        $response['result2'] = $result2;
        $response['result3'] = $result3;
        $response['result4'] = $result4;
        
        return $response;
    }
    
    private function insert_check_data($sales_seq) {
        $check_01_res = $this->input->post('check_01_res');
        $check_01_det = $this->input->post('check_01_det');
        $check_02_res = $this->input->post('check_02_res');
        $check_02_det = $this->input->post('check_02_det');
        $check_03_res = $this->input->post('check_03_res');
        $check_04_res = $this->input->post('check_04_res');
        
        $check_data = array();
  
        $check_data['sales_seq'] = $sales_seq;
        $check_data['check_01_res'] = $check_01_res;
        $check_data['check_01_det'] = $check_01_det;
        $check_data['check_02_res'] = $check_02_res;
        $check_data['check_02_det'] = $check_02_det;
        $check_data['check_03_res'] = $check_03_res;
        $check_data['check_04_res'] = $check_04_res;
        
        $this->db->where('sales_seq', $sales_seq);
        $this->db->delete('fm_cm_machine_sales_check');
        $this->db->insert('fm_cm_machine_sales_check', $check_data);
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
