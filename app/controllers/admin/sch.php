<?php

if (! defined('BASEPATH'))
    exit('No direct script access allowed');
require_once (APPPATH . "controllers/base/front_base" . EXT);

class sch extends front_base
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('membermodel');
        $this->load->helper(array(
            'form',
            'url',
            'mail',
            'sms'
        ));
    }

    public function mch_search()
    {
        $tpl = 'search/mch_search.html';
        $skin = $this->skin;

        $this->template_path = $tpl;
        $this->template->assign(array(
            "skin_path" => $this->skin,
            "template_path" => $this->template_path
        ));

        $query = "select * from fm_cm_machine_kind group by kind_type order by kind_no";
        $query = $this->db->query($query);
        $kind_group = $query->result_array();
        $this->template->assign('kind_group', $kind_group);

        $query = $this->db->get('fm_cm_machine_area');
        $result = $query->result_array();
        $this->template->assign('area_list', $result);

        $selected = $this->input->post("selected");
        $cate_k = $this->input->post("cate_k");
        $cate_t = $this->input->post("cate_t");
        $cate_f = $this->input->post("cate_f");
        $cate_m = $this->input->post("cate_m");
        $cate_y = $this->input->post("cate_y");
        $cate_p = $this->input->post("cate_p");
        $cate_a = $this->input->post("cate_a");
        $h = $this->input->post("h");
        $d = $this->input->post("d");
        $o = $this->input->post("o");
        $more = $this->input->post("more");
        $focus = $this->input->post("focus");

        $selected = empty($selected) ? 'k' : $selected;
        $cate_k = empty($cate_k) ? '0' : $cate_k;
        $cate_t = empty($cate_t) ? '0' : $cate_t;
        $cate_f = empty($cate_f) ? '0' : $cate_f;
        $cate_m = empty($cate_m) ? '0' : $cate_m;
        $cate_y = empty($cate_y) ? '0' : $cate_y;
        $cate_p = empty($cate_p) ? '0' : $cate_p;
        $cate_a = empty($cate_a) ? '0' : $cate_a;
        $h = empty($cate_k) ? $cate_k : (empty($h) ? $cate_k : $h);
        $d = empty($cate_k) ? $cate_k : (empty($d) ? $cate_k : $d);
        $o = empty($o) ? '0' : $o;
        $more = empty($more) ? 'n' : $more;
        $focus = empty($focus) ? '#highlightZoneFocus' : $focus;
        
        if($selected == 'k') {
            $cate_t = '0';
            $cate_f = '0';
            $cate_m = '0';
        } else if ($selected == 't') {
            $cate_f = '0';
            $cate_m = '0';
        } else if ($selected == 'f') {
            $cate_m = '0';
        }
        
        if($cate_k == '0') {
            $kind_where = '';
        } else {
            $kind_where = 'where kind_no = '.$cate_k;
        }
        $query = "select * from fm_cm_machine_kind ".$kind_where." order by kind_seq asc";
        $query = $this->db->query($query);
        $kind_type = $query->result_array();
        $this->template->assign('kind_type', $kind_type);
        
        $query = "select * from fm_cm_machine_kind where kind_no = ".$cate_k;
        $query = $this->db->query($query);
        $kind = $query->result_array();
        $kind_list = "";
        if(count($kind) > 0) {
            foreach($kind as $row) {
                $kind_list .= $kind_list == "" ? "'".$row['kind_name']."'" : ", '".$row['kind_name']."'";
                if($cate_t != '0') {
                    if($row['kind_seq'] == $cate_t)
                        $kind_name = "'".$row['kind_name']."'";
                }
            }
        } else {
            $query = "select * from fm_cm_machine_kind where kind_seq = ".$cate_t;
            $query = $this->db->query($query);
            $kind_name = "'".$query->row_array()['kind_name']."'";
        }
        if($cate_k == '0' && $cate_t == '0') {
            $mnf_where = 'group by mnf_name ';
        } else if ($cate_k != '0' && $cate_t == '0'){
            $mnf_where = "where mnf_kind in(".$kind_list.") ";
        } else if ($cate_k == '0' && $cate_t != '0'){
            $mnf_where = "where mnf_kind in(".$kind_name.") ";
        } else if ($cate_k != '0' && $cate_t != '0'){
            $mnf_where = "where mnf_kind in(".$kind_name.") ";
        }
        $query = "select * from fm_cm_machine_manufacturer ".$mnf_where." order by mnf_name asc";
        $query = $this->db->query($query);
        $result = $query->result_array();
        $this->template->assign('mnf_list', $result);
        
        $query = "select * from fm_cm_machine_manufacturer where mnf_seq = ".$cate_f;
        $query = $this->db->query($query);
        $mnf = $query->row_array();
        if($cate_k == '0' && $cate_t == '0' && $cate_f == '0') {
            $model_where = "";
        } else if($cate_k != '0' && $cate_t == '0' && $cate_f == '0') {
            $model_where = "where model_kind in(".$kind_list.") ";
        } else if($cate_k == '0' && $cate_t != '0' && $cate_f == '0') {
            $model_where = "where model_kind in(".$kind_name.") ";
        } else if($cate_k == '0' && $cate_t == '0' && $cate_f != '0') {
            $model_where = "where model_mnf = '".$mnf['mnf_name']."' ";
        } else if($cate_k != '0' && $cate_t != '0' && $cate_f == '0') {
            $model_where = "where model_kind in(".$kind_name.") ";
        } else if($cate_k != '0' && $cate_t == '0' && $cate_f != '0') {
            $model_where = "where model_kind in(".$kind_list.") and model_mnf = '".$mnf['mnf_name']."' ";
        } else if($cate_k == '0' && $cate_t != '0' && $cate_f != '0') {
            $model_where = "where model_kind in(".$kind_name.") and model_mnf = '".$mnf['mnf_name']."' ";
        } else if($cate_k != '0' && $cate_t != '0' && $cate_f != '0'){
            $model_where = "where model_kind in(".$kind_name.") and model_mnf = '".$mnf['mnf_name']."' ";
        }
        $query = "select * from fm_cm_machine_model ".$model_where." order by model_name asc";
        $query = $this->db->query($query);
        $result = $query->result_array();
        $this->template->assign('model_list', $result);
        
        $sale_list_01 = $this->get_search_result('h', $cate_k, $cate_t, $cate_f, $cate_m, $cate_y, $cate_p, $cate_a, $h, $d, $o, $more);
        $sale_list_02 = $this->get_search_result('d', $cate_k, $cate_t, $cate_f, $cate_m, $cate_y, $cate_p, $cate_a, $h, $d, $o, $more);
        $sale_list_03 = $this->get_search_result('c', $cate_k, $cate_t, $cate_f, $cate_m, $cate_y, $cate_p, $cate_a, $h, $d, $o, $more);

        $this->template->assign('sale_list_01', $sale_list_01);
        $this->template->assign('sale_list_02', $sale_list_02);
        $this->template->assign('sale_list_03', $sale_list_03);
        $this->template->assign(array(
            'selected' => $selected,
            'cate_k' => $cate_k,
            'cate_t' => $cate_t,
            'cate_f' => $cate_f,
            'cate_m' => $cate_m,
            'cate_y' => $cate_y,
            'cate_p' => $cate_p,
            'cate_a' => $cate_a,
            'h' => $h,
            'd' => $d,
            'o' => $o,
            'more' => $more,
            'focus' => $focus
        ));

        $this->print_layout($skin . '/' . $tpl);
    }

    public function mch_detail($type, $info_seq)
    {
        $tpl = 'search/mch_detail.html';
        $skin = $this->skin;

        $this->template_path = $tpl;
        $this->template->assign(array(
            "skin_path" => $this->skin,
            "template_path" => $this->template_path
        ));

        if (! $_COOKIE['view_cookie_' . $info_seq]) {
            $this->update_view($info_seq);
        }
        if (! $_COOKIE['like_cookie_' . $info_seq]) {
            $like_yn = 'n';
        } else {
            $like_yn = 'y';
        }

        $recent_cookies = explode(",", $_COOKIE['recent_sale_cookie']);
        $is_recent = false;
        foreach ($recent_cookies as $row) {
            if ($row == $info_seq)
                $is_recent = true;
        }
        if ($is_recent == false) {
            if (isset($_COOKIE['recent_sale_cookie'])) {
                setcookie('recent_sale_cookie', $info_seq . "," . $_COOKIE['recent_sale_cookie'], time() + 3600 * 24 * 3, '/');
            } else {
                setcookie('recent_sale_cookie', $info_seq, time() + 3600 * 24 * 3, '/');
            }
        }

        $like_cnt = $this->get_like_cnt($info_seq);
        $bidData = $this->getBidRestTime($info_seq);
        $resultMap = $this->get_detail_query($type, $info_seq);

        $isSaleUser = $this->loginUserEqualCheck($this->getSaleUser($info_seq));
        $userid = $this->getSaleUser($info_seq)['userid'];
        $this->template->assign('info_list', $resultMap['info_list']);
        $this->template->assign('picture_list', $resultMap['picture_list']);
        $this->template->assign('option_list', $resultMap['option_list']);
        $this->template->assign('other_model', $resultMap['other_model']);
        $this->template->assign('perform', $resultMap['perform']);
        $this->template->assign(array(
            'type' => $type,
            'view_cnt' => $resultMap['view_cnt'],
            'bid_user_cnt' => $resultMap['bid_user_cnt'],
            'like_yn' => $like_yn,
            'like_cnt' => $like_cnt,
            'isSaleUser' => $isSaleUser,
            'userid' => $userid,
            'bidData' => $bidData,
            'grade' => $resultMap['grade'],
            'grade_cnt' => $resultMap['grade_cnt'],
            'sale_ing_cnt' => $resultMap['sale_ing_cnt'],
            'sale_finish_cnt' => $resultMap['sale_finish_cnt'],
            'hotmark_list' => $resultMap['hotmark_list']
        ));

        $this->print_layout($skin . '/' . $tpl);
    }

    public function proposal($type, $info_seq, $mode, $prop_seq)
    {
        if (! $this->sessionCheck()) {
            $this->session->set_flashdata('message', '로그인이 필요한 기능입니다.');
            pageRedirect('/sch/mch_detail/' . $type . '/' . $info_seq);
            exit();
        }
        if ($mode != 'counter' && $this->loginUserEqualCheck($this->getSaleUser($info_seq))) {
            $this->session->set_flashdata('message', '해당 기계의 판매자는 이용하실 수 없습니다.');
            pageRedirect('/sch/mch_detail/' . $type . '/' . $info_seq);
            exit();
        }
        if ($this->get_bpermit_check() == - 1) {
            $this->session->set_flashdata('message', '사업자등록증을 첨부하시고 사업자 인증을 받으셔야 이용하실 수 있습니다. 기업회원으로 전환해주시기 바랍니다.');
            pageRedirect("/user/my_info_modify/change");
            exit();
        }
        if ($this->get_bpermit_check() == 0) {
            $this->session->set_flashdata('message', '관리자가 인증을 처리하고 있습니다. 빠른 시간에 이용하실수 있도록 하겠습니다. 감사합니다.');
            pageRedirect($_SERVER["HTTP_REFERER"]);
            exit();
        }

        $query = "select * from fm_cm_machine_sales_detail a where a.info_seq = " . $info_seq;
        $query = $this->db->query($query);
        $result = $query->row();
        if ($result->price_proposal == null || $result->price_proposal == 0) {
            $this->session->set_flashdata('message', '가격제안이 가능하지 않은 기계입니다.');
            pageRedirect('/sch/mch_detail/' . $type . '/' . $info_seq);
        }
        $tpl = 'search/proposal.html';
        $skin = $this->skin;
        $this->template_path = $tpl;
        $this->template->assign(array(
            "skin_path" => $this->skin,
            "template_path" => $this->template_path
        ));

        if ($mode == 'counter') {
            $query = "select *, a.userid as buy_userid, c.userid as sale_userid from fm_cm_machine_proposal a, fm_cm_machine_sales_info b, fm_cm_machine_sales c where a.info_seq = b.info_seq and b.sales_seq = c.sales_seq and prop_seq = " . $prop_seq;
            $query = $this->db->query($query);
            $prop_info = $query->row_array();
        }
        $this->template->assign(array(
            'type' => $type,
            'info_seq' => $info_seq,
            'mode' => $mode,
            'fixed_price' => $result->fixed_price,
            'price_proposal' => $result->price_proposal,
            'prop_info' => $prop_info
        ));

        $this->print_layout($skin . '/' . $tpl);
    }

    public function proposal_process()
    {
        $type = $this->input->post('type');
        $info_seq = $this->input->post('info_seq');
        $prop_price = $this->input->post('prop_price');
        $prop_date = $this->input->post('prop_date');

        $userData = $this->getUserData();
        $data = array(
            'info_seq' => $info_seq,
            'userid' => $userData['userid'],
            'prop_price' => $prop_price,
            'prop_date' => $prop_date
        );
        $this->db->insert('fm_cm_machine_proposal', $data);
        $prop_seq = $this->db->insert_id();

        $status_log = date('Y년 m월 d일 H:i:s') . ' 구매자 ' . $userData['userid'] . '님이 가격제안을 신청하셨습니다.';
        $data = array(
            'status_log' => $status_log,
            'info_seq' => $info_seq,
            'target_seq' => $prop_seq,
            'target' => '가격제안'
        );
        $this->db->insert('fm_cm_machine_status', $data);

        $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b " . "where a.sales_seq = b.sales_seq and info_seq = " . $info_seq;
        $query = $this->db->query($query);
        $result = $query->row_array();
        $userData = $this->getUserDataById($result['userid']);

        $title = "가격제안 <b>받음</b>";
        $mail_message = "판매 중인 기계에 가격제안이 들어왔습니다.";
        $sms_message = "판매 중인 기계에 가격제안이 들어왔습니다. 자세한 사항은 마이페이지를 참고해주세요.";

        $this->send_common_mail($userData['email'], $title, $mail_message);
        $this->send_common_sms($userData['cellphone'], $sms_message);

        $this->session->set_flashdata('message', '가격제안 신청이 완료되었습니다.');
        pageRedirect('/sch/mch_detail/' . $type . '/' . $info_seq);
    }

    public function proposal_res_process()
    {
        $res_type = $this->input->post('res_type');
        $userid = $this->input->post('userid');
        $sale_userid = $this->input->post('sale_userid');
        $prop_seq = $this->input->post('prop_seq');
        $permit_yn = $this->input->post('permit_yn');
        $counter_price = $this->input->post('counter_price');
        $counter_date = $this->input->post('counter_date');
        $counter_permit_yn = $this->input->post('counter_permit_yn');

        $login_user = $this->getUserData();
        
        $query = "select * from fm_cm_machine_proposal where prop_seq = ".$prop_seq;
        $query = $this->db->query($query);
        $result = $query->row_array();
        
        if ($res_type == 'seller') {
            if ($permit_yn == 'c') {
                $data = array(
                    'permit_yn' => $permit_yn,
                    'counter_price' => $counter_price,
                    'counter_date' => $counter_date,
                    'counter_regdate' => date('Y-m-d H:i:s')
                );
                $title = "카운터딜 <b>제안</b>";
                $mail_message = "카운터딜 제안이 들어왔습니다.";
                $sms_message = "카운터딜 제안이 들어왔습니다. 자세한 사항은 마이페이지를 참고해주세요.";
                $status_log = date('Y년 m월 d일 H:i:s') . ' 판매자 ' . $sale_userid . '님이 가격제안을 거절하고, 카운터 딜을 신청하셨습니다.';
            } else {
                $data = array(
                    'permit_yn' => $permit_yn
                );
                if ($permit_yn == 'y') {
                    $data['permit_date'] = date('Y-m-d H:i:s');
                    
                    $buy_data = array(
                        'info_seq' => $result['info_seq'],
                        'userid' => $result['userid'],
                        'buy_price' => $result['prop_price'],
                        'hope_date' => '-',
                        'hope_time' => '-',
                        'deliver_service' => '-'
                    );
                    $this->db->insert('fm_cm_machine_imdbuy', $buy_data);
                    $title = "가격제안 <b>승인</b>";
                    $mail_message = "요청하신 가격제안이 승인되었습니다.";
                    $sms_message = "요청하신 가격제안이 승인되었습니다. 자세한 사항은 마이페이지를 참고해주세요.";
                    $status_log = date('Y년 m월 d일 H:i:s') . ' 판매자 ' . $sale_userid . '님이 가격제안을 승인하였습니다.';
                } else if ($permit_yn == 'n') {
                    $title = "가격제안 <b>거절</b>";
                    $mail_message = "요청하신 가격제안이 거절되었습니다.";
                    $sms_message = "요청하신 가격제안이 거절되었습니다. 자세한 사항은 마이페이지를 참고해주세요.";
                    $status_log = date('Y년 m월 d일 H:i:s') . ' 판매자 ' . $sale_userid . '님이 가격제안을 거절하였습니다.';
                }
            }
            $userData = $this->getUserDataById($userid);
        } else if ($res_type == 'buyer') {
            $data = array(
                'counter_permit_yn' => $counter_permit_yn
            );
            $this->db->where('prop_seq', $prop_seq);
            $this->db->update('fm_cm_machine_proposal', $data);

            $userData = $this->getUserDataById($sale_userid);

            if ($counter_permit_yn == 'y') {
                $data['permit_date'] = date('Y-m-d H:i:s');
                
                $buy_data = array(
                    'info_seq' => $result['info_seq'],
                    'userid' => $result['userid'],
                    'buy_price' => $result['counter_price'],
                    'hope_date' => '-',
                    'hope_time' => '-',
                    'deliver_service' => '-'
                );
                $this->db->insert('fm_cm_machine_imdbuy', $buy_data);
                $title = "카운터딜 제안 <b>승인</b>";
                $mail_message = "요청한 카운터딜 제안이 승인되었습니다.";
                $sms_message = "요청한 카운터딜 제안이 승인되었습니다. 자세한 사항은 마이페이지를 참고해주세요.";
                $status_log = date('Y년 m월 d일 H:i:s') . ' 구매자 ' . $login_user['userid'] . '님이 카운터 딜 제안을 승인하였습니다.';
            } else if ($counter_permit_yn == 'n') {
                $title = "카운터딜 제안 <b>거절</b>";
                $mail_message = "요청한 카운터딜 제안이 거절되었습니다.";
                $sms_message = "요청한 카운터딜 제안이 거절되었습니다. 자세한 사항은 마이페이지를 참고해주세요.";
                $status_log = date('Y년 m월 d일 H:i:s') . ' 구매자 ' . $login_user['userid'] . '님이 카운터 딜 제안을 거절하였습니다.';
            }
        }
        $this->db->where('prop_seq', $prop_seq);
        $this->db->update('fm_cm_machine_proposal', $data);

        $query = "select * from fm_cm_machine_proposal where prop_seq = " . $prop_seq;
        $query = $this->db->query($query);
        $result = $query->row_array();

        $status_data = array(
            'status_log' => $status_log,
            'info_seq' => $result['info_seq'],
            'target_seq' => $prop_seq,
            'target' => '가격제안'
        );
        $this->db->insert('fm_cm_machine_status', $status_data);

        $this->send_common_mail($userData['email'], $title, $mail_message);
        $this->send_common_sms($userData['cellphone'], $sms_message);

        if ($permit_yn == 'c') {
            $this->session->set_flashdata('message', '카운터딜 제안이 완료되었습니다.');
            pageRedirect('/user/my_sale_ing');
            exit();
        } else {
            $callback = "parent.location.reload()";
            openDialogAlert('가격제안 답변이 완료되었습니다.', 400, 140, 'parent', $callback);
        }
    }

    public function imd_buy($type, $info_seq)
    {
        if (! $this->sessionCheck()) {
            $this->session->set_flashdata('message', '로그인이 필요한 기능입니다.');
            pageRedirect('/sch/mch_detail/' . $type . '/' . $info_seq);
            exit();
        }
        if ($this->loginUserEqualCheck($this->getSaleUser($info_seq))) {
            $this->session->set_flashdata('message', '해당 기계의 판매자는 이용하실 수 없습니다.');
            pageRedirect('/sch/mch_detail/' . $type . '/' . $info_seq);
            exit();
        }
        if ($this->get_bpermit_check() == - 1) {
            $this->session->set_flashdata('message', '사업자등록증을 첨부하시고 사업자 인증을 받으셔야 이용하실 수 있습니다. 기업회원으로 전환해주시기 바랍니다.');
            pageRedirect("/user/my_info_modify/change");
            exit();
        }
        if ($this->get_bpermit_check() == 0) {
            $this->session->set_flashdata('message', '관리자가 인증을 처리하고 있습니다. 빠른 시간에 이용하실수 있도록 하겠습니다. 감사합니다.');
            pageRedirect($_SERVER["HTTP_REFERER"]);
            exit();
        }
        $tpl = 'search/imd_buy.html';
        $skin = $this->skin;
        $this->template_path = $tpl;
        $this->template->assign(array(
            "skin_path" => $this->skin,
            "template_path" => $this->template_path
        ));

        $userData = $this->getUserData();
        
        $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_visit c ".
            "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and c.state = '4' and c.userid = '".$userData['userid']."' and b.info_seq = ".$info_seq;
        $query = $this->db->query($query);
        $result = $query->result_array();
        
        if(empty($result)) {
            $this->template->assign('popup', 'y');
        }
        $this->template->assign(array(
            'type' => $type,
            'info_seq' => $info_seq
        ));
    
        $this->print_layout($skin . '/' . $tpl);
    }

    public function imd_buy_process()
    {
        $type = $this->input->post('type');
        $info_seq = $this->input->post('info_seq');
        $hope_date = $this->input->post('hope_date');
        $hope_time = $this->input->post('hope_time');
        $deliver_service = $this->input->post('deliver_service');

        $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b ".
                 "where a.sales_seq = b.sales_seq and b.info_seq = ".$info_seq;
        $query = $this->db->query($query);
        $result = $query->row_array();
        
        $userData = $this->getUserData();
        $data = array(
            'info_seq' => $info_seq,
            'userid' => $userData['userid'],
            'buy_price' => $result['sort_price'],
            'hope_date' => $hope_date,
            'hope_time' => $hope_time,
            'deliver_service' => $deliver_service
        );
        $this->db->insert('fm_cm_machine_imdbuy', $data);
        $buy_seq = $this->db->insert_id();
        
        $status_log = date('Y년 m월 d일 H:i:s') . ' 구매자 ' . $userData['userid'] . '님이 즉시구매를 신청하였습니다.';
        $data = array(
            'status_log' => $status_log,
            'info_seq' => $info_seq,
            'target_seq' => $buy_seq,
            'target' => '즉시구매'
        );
        $this->db->insert('fm_cm_machine_status', $data);
        
        $this->send_email('imd_buy');
        $this->send_sms('imd_buy');

        $this->session->set_flashdata('message', '즉시구매 신청이 완료되었습니다.');
        pageRedirect('/sch/mch_detail/' . $type . '/' . $info_seq);
    }

    public function visit($type, $info_seq, $estimate_seq)
    {
        if (! $this->sessionCheck()) {
            $this->session->set_flashdata('message', '로그인이 필요한 기능입니다.');
            pageRedirect('/sch/mch_detail/' . $type . '/' . $info_seq);
            exit();
        }
        if (!$estimate_seq && $this->loginUserEqualCheck($this->getSaleUser($info_seq))) {
            $this->session->set_flashdata('message', '해당 기계의 판매자는 이용하실 수 없습니다.');
            pageRedirect('/sch/mch_detail/' . $type . '/' . $info_seq);
            exit();
        }
        if ($this->get_bpermit_check() == - 1) {
            $this->session->set_flashdata('message', '사업자등록증을 첨부하시고 사업자 인증을 받으셔야 이용하실 수 있습니다. 기업회원으로 전환해주시기 바랍니다.');
            pageRedirect("/user/my_info_modify/change");
            exit();
        }
        if ($this->get_bpermit_check() == 0) {
            $this->session->set_flashdata('message', '관리자가 인증을 처리하고 있습니다. 빠른 시간에 이용하실수 있도록 하겠습니다. 감사합니다.');
            pageRedirect($_SERVER["HTTP_REFERER"]);
            exit();
        }
        $tpl = 'search/visit.html';
        $skin = $this->skin;
        $this->template_path = $tpl;
        $this->template->assign(array(
            "skin_path" => $this->skin,
            "template_path" => $this->template_path
        ));

        $this->template->assign(array(
            'type' => $type,
            'info_seq' => $info_seq,
            'estimate_seq' => $estimate_seq
        ));

        $this->print_layout($skin . '/' . $tpl);
    }

    public function visit_process()
    {
        $type = $this->input->post('type');
        $info_seq = $this->input->post('info_seq');
        $estimate_seq = $this->input->post('estimate_seq');
        $hope_date_arr = $this->input->post('hope_date');
        $hope_time_arr = $this->input->post('hope_time');
        
        if(empty($estimate_seq)) {
            $userData = $this->getUserData();
            $data = array(
                'info_seq' => $info_seq,
                'userid' => $userData['userid']
            );
            $this->db->insert('fm_cm_machine_visit', $data);
            $visit_seq = $this->db->insert_id();
            
            for ($i = 0; $i < count($hope_date_arr); $i ++) {
                $data = array(
                    'visit_seq' => $visit_seq,
                    'hope_date' => $hope_date_arr[$i],
                    'hope_time' => $hope_time_arr[$i]
                );
                $this->db->insert('fm_cm_machine_visit_detail', $data);
            }
            $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_model c " . "where a.sales_seq = b.sales_seq and b.model_seq = c.model_seq and info_seq = " . $info_seq;
            $query = $this->db->query($query);
            $result = $query->row_array();
            
            $saleUser = $this->getUserDataById($result['userid']);
            
            $status_log = date('Y년 m월 d일 H:i:s') . ' 구매자 ' . $userData['userid'] . '님이 현장방문을 신청하셨습니다.';
            $data = array(
                'status_log' => $status_log,
                'info_seq' => $info_seq,
                'target_seq' => $visit_seq,
                'target' => '현장방문'
            );
            $this->db->insert('fm_cm_machine_status', $data);
            
            $title = "현장방문 <b>신청</b>";
            $message = "구매 예정자로부터 등록하신 " . $result['model_name'] . "(" . $result['sales_no'] . ")의 매입 현장방문 신청이 들어왔습니다. 미팅 진행을 하시겠습니까 ? \r\n※ 바로가기 URL: https://emachinebox.com/sch/visit_rcv/" . $visit_seq;
            $this->send_common_mail($saleUser['email'], $title, $message);
            $this->send_common_sms($saleUser['cellphone'], $message);
            
            $this->session->set_flashdata('message', '현장방문 신청이 완료되었습니다.');
            pageRedirect('/sch/mch_detail/' . $type . '/' . $info_seq);
        } else {
            $query = "select * from fm_cm_machine_estimate_dealer where estimate_seq = ".$estimate_seq;
            $query = $this->db->query($query);
            $est_data = $query->row_array();
            
            $data = array(
                'info_seq' => $info_seq,
                'userid' => $est_data['userid']
            );
            $this->db->insert('fm_cm_machine_visit', $data);
            $visit_seq = $this->db->insert_id();
            
            for ($i = 0; $i < count($hope_date_arr); $i ++) {
                $data = array(
                    'visit_seq' => $visit_seq,
                    'hope_date' => $hope_date_arr[$i],
                    'hope_time' => $hope_time_arr[$i]
                );
                $this->db->insert('fm_cm_machine_visit_detail', $data);
            }
            $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_model c ". 
                     "where a.sales_seq = b.sales_seq and b.model_seq = c.model_seq and info_seq = " . $info_seq;
            $query = $this->db->query($query);
            $result = $query->row_array();
            
            $data = array(
                'select_yn' => 'y'
            );
            $this->db->where('estimate_seq', $estimate_seq);
            $this->db->update('fm_cm_machine_estimate_dealer', $data);
            
            $saleUser = $this->getUserDataById($result['userid']);
            $buyUser = $this->getUserDataById($est_data['userid']);
            
            $status_log = date('Y년 m월 d일 H:i:s') . ' 판매자 ' . $saleUser['userid'] . '님이 딜러사 '.$est_data['userid'].'님을 선택하고, 현장방문을 신청하셨습니다.';
            $data = array(
                'status_log' => $status_log,
                'info_seq' => $info_seq,
                'target_seq' => $visit_seq,
                'target' => '현장방문'
            );
            $this->db->insert('fm_cm_machine_status', $data);
            
            $title = "현장방문 <b>신청</b>";
            $message = "딜러사 ".$buyUser['userid']."님이 견적서를 제출한 " . $result['model_name'] . "(" . $result['sales_no'] . ")로 부터 판매자 ".$saleUser['userid']."님이 현장방문 신청을 하였습니다. 미팅 진행을 하시겠습니까 ? \r\n※ 바로가기 URL: https://emachinebox.com/sch/visit_rcv/" . $visit_seq."/".$estimate_seq."/buy";
            $this->send_common_mail($buyUser['email'], $title, $message);
            $this->send_common_sms($buyUser['cellphone'], $message);
            
            $this->session->set_flashdata('message', '현장방문 신청이 완료되었습니다.');
            pageRedirect('/user/my_sale_emergency');
        }
    }

    public function visit_rcv($visit_seq, $estimate_seq, $estimate_type)
    {
        $tpl = 'search/visit_rcv.html';
        $skin = $this->skin;

        $this->template_path = $tpl;
        $this->template->assign(array(
            "skin_path" => $this->skin,
            "template_path" => $this->template_path
        ));

        $query = "select *, a.userid as sale_userid, d.userid as visit_userid from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_model c, fm_cm_machine_visit d ". 
        "where a.sales_seq = b.sales_seq and b.model_seq = c.model_seq and b.info_seq = d.info_seq and visit_seq = ".$visit_seq;
        $query = $this->db->query($query);
        $result = $query->row_array();

        if(empty($estimate_seq)) {
            $saleUser = $this->getUserDataById($result['sale_userid']);
            $this->create_member_session($saleUser);
            
            if ($result['visit_pay_yn'] == 'n') {
                $page = '01';
            } else if ($result['visit_pay_yn'] == 'h') {
                $page = '01';
                pageRedirect('/sch/visit_rcv_complete/' . $page . '/' . $visit_seq);
                exit();
            } else if ($result['visit_pay_yn'] == 'y') {
                $page = '02';
                $query = "select * from fm_cm_machine_visit_detail where visit_seq = " . $visit_seq;
                $query = $this->db->query($query);
                $det_list = $query->result_array();
                
                $is_select = false;
                foreach ($det_list as &$row) {
                    $week = array(
                        "일요일",
                        "월요일",
                        "화요일",
                        "수요일",
                        "목요일",
                        "금요일",
                        "토요일"
                    );
                    $weekday = $week[date('w', strtotime($row['hope_date']))];
                    $hope_time = explode(':', $row['hope_time']);
                    $full_date = date('Y년 m월 d일', strtotime($row['hope_date'])) . " " . $weekday . " " . $hope_time[0] . '시 ' . $hope_time[1] . '분';
                    $row['full_date'] = $full_date;
                    if ($row['select_yn'] == 'y') {
                        $is_select = true;
                        $select_seq = $row['vdet_seq'];
                    }
                }
                if ($is_select == true) {
                    $this->session->set_flashdata('message', '이미 미팅 예정일을 선택하였습니다.');
                    pageRedirect('/sch/visit_rcv_complete/' . $page . '/' . $visit_seq . '/' . $select_seq);
                    exit();
                }
                $result['det_list'] = $det_list;
            }
        } else {
            if($estimate_type == 'buy') {
                $buyUser = $this->getUserDataById($result['visit_userid']);
                $this->create_member_session($buyUser);
                
                $page = '02';
                $query = "select * from fm_cm_machine_visit_detail where visit_seq = " . $visit_seq;
                $query = $this->db->query($query);
                $det_list = $query->result_array();
                
                $is_select = false;
                foreach ($det_list as &$row) {
                    $week = array(
                        "일요일",
                        "월요일",
                        "화요일",
                        "수요일",
                        "목요일",
                        "금요일",
                        "토요일"
                    );
                    $weekday = $week[date('w', strtotime($row['hope_date']))];
                    $hope_time = explode(':', $row['hope_time']);
                    $full_date = date('Y년 m월 d일', strtotime($row['hope_date'])) . " " . $weekday . " " . $hope_time[0] . '시 ' . $hope_time[1] . '분';
                    $row['full_date'] = $full_date;
                    if ($row['select_yn'] == 'y') {
                        $is_select = true;
                        $select_seq = $row['vdet_seq'];
                    }
                }
                if ($is_select == true) {
                    $this->session->set_flashdata('message', '이미 미팅 예정일을 선택하였습니다.');
                    pageRedirect('/sch/visit_rcv_complete/' . $page . '/' . $visit_seq . '/' . $select_seq);
                    exit();
                }
                $result['det_list'] = $det_list;
            } else if($estimate_type == 'sale') {
                $saleUser = $this->getUserDataById($result['sale_userid']);
                $this->create_member_session($saleUser);
                
                if ($result['visit_pay_yn'] == 'n') {
                    $page = '01';
                } else if ($result['visit_pay_yn'] == 'h') {
                    $page = '01';
                    pageRedirect('/sch/visit_rcv_complete/' . $page . '/' . $visit_seq);
                    exit();
                } else if ($result['visit_pay_yn'] == 'y') {
                    $page = '01';
                    $this->session->set_flashdata('message', '이미 결제가 확인되었습니다.');
                    pageRedirect('/sch/visit_rcv_complete/' . $page . '/' . $visit_seq);
                    exit();
                }
            }
            $this->template->assign('estimate_seq', $estimate_seq);
        }
        $this->template->assign('page', $page);
        $this->template->assign($result);

        $this->print_layout($skin . '/' . $tpl);
    }

    public function visit_rcv_process()
    {
        $page = $this->input->post('page');
        $visit_seq = $this->input->post('visit_seq');
        $vdet_seq = $this->input->post('vdet_seq');
        $location = $this->input->post('location');
        $estimate_seq = $this->input->post('estimate_seq');

        $query = "select *, a.userid as sale_userid, d.userid as visit_userid from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_model c, fm_cm_machine_visit d " . "where a.sales_seq = b.sales_seq and b.model_seq = c.model_seq and b.info_seq = d.info_seq and visit_seq = " . $visit_seq;
        $query = $this->db->query($query);
        $result = $query->row_array();

        $saleUser = $this->getUserDataById($result['sale_userid']);
        $visitUser = $this->getUserDataById($result['visit_userid']);
        
        if ($page == '01') {
            $data = array(
                'visit_pay_yn' => 'h'
            );
            $this->db->where('info_seq', $result['info_seq']);
            $this->db->update('fm_cm_machine_sales_info', $data);

            $status_log = date('Y년 m월 d일 H:i:s') . ' 판매자 ' . $saleUser['userid'] . '님이 이용수수료 결제를 진행하였습니다. 입금 확인을 해주세요.';
            $data = array(
                'status_log' => $status_log,
                'info_seq' => $result['info_seq'],
                'target_seq' => $visit_seq,
                'target' => '현장방문'
            );
            $this->db->insert('fm_cm_machine_status', $data);
            
            if(empty($estimate_seq)) {
                $query = "select * from fm_cm_machine_estimate_form where estimate_seq = ".$estimate_seq;
                $query = $this->db->query($query);
                $est_data = $query->row_array();
                $result['fee'] = (int) $est_data['estimate_price'] * 1.1 / 100;
            } else {
                $result['fee'] = (int) $result['sort_price'] * 1.1 / 100;
            }
            $title = "현장방문 <b>이용수수료 결제</b>";
            $message = "※ 현장방문 이용수수료 결제 안내\r\n결제수단: 무통장입금\r\n입금계좌: 농협은행 302-1371-4082-81 (예금주: 에스디네트웍스(신동훈))\r\n입금금액: " . number_format($result['fee']) . '원';
            $this->send_common_mail($saleUser['email'], $title, $message);
            $this->send_common_sms($saleUser['cellphone'], $message);
            
            $pay_data = array();
            $pay_data['pay_userid'] = $saleUser['userid'];
            $pay_data['pay_no'] = $this->get_pay_no();
            $pay_data['pay_price'] = $result['fee'];
            $pay_data['pay_method'] = '무통장 입금';
            $pay_data['pay_state'] = '입금대기';
            $pay_data['target_seq'] = $result['info_seq'];
            $pay_data['pay_content'] = '현장미팅 이용수수료 결제';
            if(empty($estimate_seq)) {
                $pay_data['pay_type'] = '현장미팅';
            } else {
                $pay_data['pay_type'] = '비교견적';
            }
            $this->db->insert('fm_cm_machine_pay', $pay_data);
        } else if ($page == '02') {
            $data = array(
                'location' => $location,
                'state' => 2,
                'select_date' => date('Y-m-d H:i:s')
            );
            $this->db->where('visit_seq', $visit_seq);
            $this->db->update('fm_cm_machine_visit', $data);

            $data = array(
                'select_yn' => 'y'
            );
            $this->db->where('vdet_seq', $vdet_seq);
            $this->db->update('fm_cm_machine_visit_detail', $data);

            $query = "select * from fm_cm_machine_visit_detail where vdet_seq = " . $vdet_seq;
            $query = $this->db->query($query);
            $det_data = $query->row_array();
            $week = array(
                "일요일",
                "월요일",
                "화요일",
                "수요일",
                "목요일",
                "금요일",
                "토요일"
            );
            $weekday = $week[date('w', strtotime($det_data['hope_date']))];
            $hope_time = explode(':', $det_data['hope_time']);
            $full_date = date('Y년 m월 d일', strtotime($det_data['hope_date'])) . " " . $weekday . " " . $hope_time[0] . '시 ' . $hope_time[1] . '분';
            
            if(empty($estimate_seq)) {
                $status_log = date('Y년 m월 d일 H:i:s') . ' 판매자 ' . $saleUser['userid'] . '님이 미팅일을 '.$full_date."으로 선택하였습니다.";
                
                $title = "현장방문 <b>미팅일정 안내</b>";
                $message = "※ 현장방문 미팅일정  안내\r\n판매자가 미팅일을 선택했습니다. 마이페이지에서 [승인] 버튼을 눌러주세요. \r\n미팅일: " . $full_date;
                $this->send_common_mail($visitUser['email'], $title, $message);
                $this->send_common_sms($visitUser['cellphone'], $message);
            } else {
                $status_log = date('Y년 m월 d일 H:i:s') . ' 딜러사 ' . $visitUser['userid'] . '님이 미팅일을 '.$full_date."으로 선택하였습니다.";
                
                if($result['visit_pay_yn'] != 'y') {
                    $pay_message = "\r\n판매자님은 이용수수료 결제가 안되어 이용수수료 결제가 필요합니다.\r\n※ 이용수수료 결제하기 URL : https://emachinebox.com/sch/visit_rcv/".$visit_seq."/".$estimate_seq."/sale";
                }
                $title = "현장방문 <b>미팅일정 안내</b>";
                $message = "※ 현장방문 미팅일정  안내\r\n딜러사가 미팅일을 선택했습니다. \r\n미팅일: " . $full_date.$pay_message;
                $this->send_common_mail($saleUser['email'], $title, $message);
                $this->send_common_sms($saleUser['cellphone'], $message);
            }
            $data = array(
                'status_log' => $status_log,
                'info_seq' => $result['info_seq'],
                'target_seq' => $visit_seq,
                'target' => '현장방문'
            );
            $this->db->insert('fm_cm_machine_status', $data);
            
        }
        pageRedirect('/sch/visit_rcv_complete/' . $page . '/' . $visit_seq . '/' . $vdet_seq);
    }

    public function visit_rcv_complete($page, $visit_seq, $vdet_seq)
    {
        $tpl = 'search/visit_rcv_complete.html';
        $skin = $this->skin;
        $this->template_path = $tpl;
        $this->template->assign(array(
            "skin_path" => $this->skin,
            "template_path" => $this->template_path
        ));

        if ($page == '01') {
            $query = "select *, a.userid as sale_userid, d.userid as visit_userid from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_model c, fm_cm_machine_visit d " . "where a.sales_seq = b.sales_seq and b.model_seq = c.model_seq and b.info_seq = d.info_seq and visit_seq = " . $visit_seq;
            $query = $this->db->query($query);
            $result = $query->row_array();
            $result['fee'] = (int) $result['sort_price'] * 1.1 / 100;
        } else if ($page == '02') {
            $query = "select * from fm_cm_machine_visit_detail where vdet_seq = " . $vdet_seq;
            $query = $this->db->query($query);
            $result = $query->row_array();

            $week = array(
                "일요일",
                "월요일",
                "화요일",
                "수요일",
                "목요일",
                "금요일",
                "토요일"
            );
            $weekday = $week[date('w', strtotime($result['hope_date']))];
            $hope_time = explode(':', $result['hope_time']);
            $full_date = date('Y년 m월 d일', strtotime($result['hope_date'])) . " " . $weekday . " " . $hope_time[0] . '시 ' . $hope_time[1] . '분';
            $result['full_date'] = $full_date;
        }
        $this->template->assign('page', $page);
        $this->template->assign($result);

        $this->print_layout($skin . '/' . $tpl);
    }
    
    public function visit_permit_process() {
        $visit_seq = $this->input->post('visit_seq');
        
        $data = array(
            'state' => '3'
        );
        $this->db->where('visit_seq', $visit_seq);
        $this->db->update('fm_cm_machine_visit', $data);
        
        $query = "select *, a.userid as sale_userid, d.userid as visit_userid from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_model c, fm_cm_machine_visit d " . "where a.sales_seq = b.sales_seq and b.model_seq = c.model_seq and b.info_seq = d.info_seq and visit_seq = ".$visit_seq;
        $query = $this->db->query($query);
        $result = $query->row_array();
        
        $query = "select * from fm_cm_machine_visit_detail where select_yn = 'y' and visit_seq = " . $visit_seq;
        $query = $this->db->query($query);
        $result2 = $query->row_array();
        
        $week = array(
            "일요일",
            "월요일",
            "화요일",
            "수요일",
            "목요일",
            "금요일",
            "토요일"
        );
        $weekday = $week[date('w', strtotime($result2['hope_date']))];
        $hope_time = explode(':', $result2['hope_time']);
        $full_date = date('Y년 m월 d일', strtotime($result2['hope_date'])) . " " . $weekday . " " . $hope_time[0] . '시 ' . $hope_time[1] . '분';
        
        $saleUser = $this->getUserDataById($result['sale_userid']);
        $visitUser = $this->getUserDataById($result['visit_userid']);
        
        $status_log = date('Y년 m월 d일 H:i:s') . ' 구매자 ' . $visitUser['userid'] . '님이 미팅일을 승인하여 판매자 '.$saleUser['userid'].'님과 '.$full_date."에 미팅이 잡혔습니다.";
        $data = array(
            'status_log' => $status_log,
            'info_seq' => $result['info_seq'],
            'target_seq' => $visit_seq,
            'target' => '현장방문'
        );
        $this->db->insert('fm_cm_machine_status', $data);
        
        $title = "현장방문 <b>미팅일정 안내</b>";
        $message = "※ 현장방문 미팅일정  안내\r\n현장방문 예약이 완료되었습니다.\r\n방문일: ".$full_date;
        $this->send_common_mail($visitUser['email'], $title, $message);
        $this->send_common_sms($visitUser['cellphone'], $message);
        
        $title = "현장방문 <b>미팅일정 안내</b>";
        $message = "※ 현장방문 미팅일정  안내\r\n구매예정자 ".$visitUser['userid']."님과의 현장방문 예약이 완료되었습니다.\r\n방문일: ".$full_date;
        $this->send_common_mail($saleUser['email'], $title, $message);
        $this->send_common_sms($saleUser['cellphone'], $message);
        
        $callback = "parent.location.reload()";
        openDialogAlert('승인이 완료되었습니다.', 400, 140, 'parent', $callback);
    }

    public function visit_cancel_process() {
        $visit_seq = $this->input->post('visit_seq');
        
        $data = array(
            'state' => '5'
        );
        $this->db->where('visit_seq', $visit_seq);
        $this->db->update('fm_cm_machine_visit', $data);
        
        $query = "select *, a.userid as sale_userid, d.userid as visit_userid from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_model c, fm_cm_machine_visit d " . "where a.sales_seq = b.sales_seq and b.model_seq = c.model_seq and b.info_seq = d.info_seq and visit_seq = ".$visit_seq;
        $query = $this->db->query($query);
        $result = $query->row_array();
        
        $query = "select * from fm_cm_machine_visit_detail where select_yn = 'y' and visit_seq = " . $visit_seq;
        $query = $this->db->query($query);
        $result2 = $query->row_array();
        
        $saleUser = $this->getUserDataById($result['sale_userid']);
        $visitUser = $this->getUserDataById($result['visit_userid']);
        
        if(!empty($result2)) {
            $date = ", ".date('Y년 m월 d일', strtotime($result2['hope_date']))." ".$result2['hope_time'];
            $status_date = date('Y년 m월 d일', strtotime($result2['hope_date']))." ".$result2['hope_time'].'에 있는 ';
        }
        $status_log = date('Y년 m월 d일 H:i:s') . ' 구매자 ' . $visitUser['userid'] . '님이 '.$status_date.'미팅을 취소하였습니다.';
        $data = array(
            'status_log' => $status_log,
            'info_seq' => $result['info_seq'],
            'target_seq' => $visit_seq,
            'target' => '현장방문'
        );
        $this->db->insert('fm_cm_machine_status', $data);
        
        $title = "현장방문 <b>미팅취소 안내</b>";
        $message = $saleUser['userid']." 판매자님 ! ".$visitUser['userid']." 구매예정자님이 ".$result['model_name'].$date."의 현장미팅을 취소하셨습니다. 다른 구매예정자와 현장미팅이 진행되도록 광고를 계속하겠습니다. 결제하신 셀프판매 이용수수료는 기계 당 최초 1회만 청구되므로 다른 구매예정자와 현장미팅 진행 시 별도의 추가 결제는 없습니다. 감사합니다.";
        $this->send_common_mail($saleUser['email'], $title, $message);
        $this->send_common_sms($saleUser['cellphone'], $message);
        
        $callback = "parent.location.reload()";
        openDialogAlert('취소가 완료되었습니다.', 400, 140, 'parent', $callback);
    }
    
    public function pay($pay_type, $seq) {
        $tpl = 'search/pay.html';
        $skin = $this->skin;
        
        $this->template_path = $tpl;
        $this->template->assign(array(
            "skin_path" => $this->skin,
            "template_path" => $this->template_path
        ));
        if($pay_type == 'buy') {
            $query = "select *, a.userid as sale_userid from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_model c " . 
                     "where a.sales_seq = b.sales_seq and b.model_seq = c.model_seq and info_seq = ".$seq;
            $query = $this->db->query($query);
            $result = $query->row_array();
            
            $saleUser = $this->getUserDataById($result['sale_userid']);
            $this->create_member_session($saleUser);
            
            if ($result['visit_pay_yn'] == 'n') {
                $page = '01';
            } else if ($result['visit_pay_yn'] == 'h') {
                $page = '02';
                $result['fee'] = (int) $result['sort_price'] * 1.1 / 100;
            }
        }
        $this->template->assign('page', $page);
        $this->template->assign('pay_type', $pay_type);
        $this->template->assign($result);
        
        $this->print_layout($skin . '/' . $tpl);
    }
    
    public function pay_process() {
        $page = $this->input->post('page');
        $pay_type = $this->input->post('pay_type');
        
        if($pay_type == 'buy') {
            $info_seq = $this->input->post('info_seq');
            
            $query = "select *, a.userid as sale_userid, d.userid as buy_userid from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_model c, fm_cm_machine_imdbuy d ".
                     "where a.sales_seq = b.sales_seq and b.model_seq = c.model_seq and b.info_seq = d.info_seq and b.info_seq = " . $info_seq;
            $query = $this->db->query($query);
            $result = $query->row_array();
            
            $data = array(
                'visit_pay_yn' => 'h'
            );
            $this->db->where('info_seq', $info_seq);
            $this->db->update('fm_cm_machine_sales_info', $data);
            
            $saleUser = $this->getUserDataById($result['sale_userid']);
            
            $result['fee'] = (int) $result['sort_price'] * 1.1 / 100;
            $title = "현장방문 <b>이용수수료 결제</b>";
            $message = "※ 현장방문 이용수수료 결제 안내\r\n결제수단: 무통장입금\r\n입금계좌: 농협은행 302-1371-4082-81 (예금주: 에스디네트웍스(신동훈))\r\n입금금액: " . number_format($result['fee']) . '원';
            $this->send_common_mail($saleUser['email'], $title, $message);
            $this->send_common_sms($saleUser['cellphone'], $message);
            
            $pay_data = array();
            $pay_data['pay_userid'] = $saleUser['userid'];
            $pay_data['pay_no'] = $this->get_pay_no();
            $pay_data['pay_content'] = '현장미팅 이용수수료 결제';
            $pay_data['pay_price'] = $result['fee'];
            $pay_data['pay_method'] = '무통장 입금';
            $pay_data['pay_state'] = '입금대기';
            $pay_data['pay_type'] = '현장미팅';
            $pay_data['target_seq'] = $result['info_seq'];
            $this->db->insert('fm_cm_machine_pay', $pay_data);
            
            pageRedirect('/sch/pay/'.$pay_type. '/' . $result['info_seq']);
        }
    }
    
    public function mch_eval($type, $info_seq)
    {
        if (! $this->sessionCheck()) {
            $this->session->set_flashdata('message', '로그인이 필요한 기능입니다.');
            pageRedirect('/sch/mch_detail/' . $type . '/' . $info_seq);
            exit();
        }
        $tpl = 'search/mch_eval.html';
        $skin = $this->skin;

        $this->template_path = $tpl;
        $this->template->assign(array(
            "skin_path" => $this->skin,
            "template_path" => $this->template_path
        ));

        $query = "select * from fm_cm_machine_sales_info a, fm_cm_machine_model b " . "where a.model_seq = b.model_seq and a.info_seq = " . $info_seq;
        $query = $this->db->query($query);
        $result = $query->row_array();
        $this->template->assign('info', $result);

        $query = "select * from fm_cm_machine_eval where info_seq = " . $info_seq . " order by reg_date desc";
        $query = $this->db->query($query);
        $result = $query->result_array();
        $userData = $this->getUserData();
        foreach ($result as &$row) {
            $grade_01 = $row['grade_01'];
            $grade_02 = $row['grade_02'];
            $grade_03 = $row['grade_03'];
            $grade_04 = $row['grade_04'];
            $grade_05 = $row['grade_05'];

            $grade = ($grade_01 + $grade_02 + $grade_03 + $grade_04 + $grade_05) / 5;
            $row['grade'] = $grade;

            if ($row['userid'] == $userData['userid'])
                $row['isSame'] = 'true';
            else
                $row['isSame'] = 'false';
        }
        $this->template->assign('eval_list', $result);
        $this->template->assign(array(
            'type' => $type,
            'info_seq' => $info_seq
        ));

        $this->print_layout($skin . '/' . $tpl);
    }

    public function mch_eval_form($type, $info_seq)
    {
        if ($this->loginUserEqualCheck($this->getSaleUser($info_seq))) {
            $this->session->set_flashdata('message', '해당 기계의 판매자는 이용하실 수 없습니다.');
            pageRedirect('/sch/mch_eval/' . $type . '/' . $info_seq);
            exit();
        }
        if ($this->get_bpermit_check() == - 1) {
            $this->session->set_flashdata('message', '사업자등록증을 첨부하시고 사업자 인증을 받으셔야 이용하실 수 있습니다. 기업회원으로 전환해주시기 바랍니다.');
            pageRedirect("/user/my_info_modify/change");
            exit();
        }
        if ($this->get_bpermit_check() == 0) {
            $this->session->set_flashdata('message', '관리자가 인증을 처리하고 있습니다. 빠른 시간에 이용하실수 있도록 하겠습니다. 감사합니다.');
            pageRedirect($_SERVER["HTTP_REFERER"]);
            exit();
        }
        $tpl = 'search/mch_eval_form.html';
        $skin = $this->skin;

        $this->template_path = $tpl;
        $this->template->assign(array(
            "skin_path" => $this->skin,
            "template_path" => $this->template_path
        ));

        $query = "select * from fm_cm_machine_sales_info a, fm_cm_machine_model b " . "where a.model_seq = b.model_seq and a.info_seq = " . $info_seq;
        $query = $this->db->query($query);
        $result = $query->row_array();
        $this->template->assign('info', $result);

        $this->template->assign(array(
            'type' => $type,
            'info_seq' => $info_seq
        ));
        $this->print_layout($skin . '/' . $tpl);
    }

    public function mch_eval_process()
    {
        $type = $this->input->post('type');
        $info_seq = $this->input->post('info_seq');
        $grade_01 = $this->input->post('grade_01');
        $grade_02 = $this->input->post('grade_02');
        $grade_03 = $this->input->post('grade_03');
        $grade_04 = $this->input->post('grade_04');
        $grade_05 = $this->input->post('grade_05');
        $content = $this->input->post('content');

        $userData = $this->getUserData();
        $data = array(
            'userid' => $userData['userid'],
            'info_seq' => $info_seq,
            'grade_01' => $grade_01,
            'grade_02' => $grade_02,
            'grade_03' => $grade_03,
            'grade_04' => $grade_04,
            'grade_05' => $grade_05,
            'content' => $content
        );
        $this->db->insert('fm_cm_machine_eval', $data);

        $this->session->set_flashdata('message', '평가 작성이 완료되었습니다.');
        pageRedirect('/sch/mch_eval/' . $type . '/' . $info_seq);
    }

    public function ajax_eval_modify()
    {
        header("Content-Type: application/json");

        $meval_seq = $this->input->post('meval_seq');
        $content = $this->input->post('content');

        $data = array(
            'content' => $content
        );
        $this->db->where('meval_seq', $meval_seq);
        $this->db->update('fm_cm_machine_eval', $data);
        $result = 'true';

        echo json_encode(array(
            'result' => $result
        ));
    }

    public function ajax_update_like($info_seq, $like_yn)
    {
        header("Content-Type: application/json");

        $result = array();
        if ($like_yn == 'y') {
            setcookie('like_cookie_' . $info_seq, '', 0, '/');
            $like_cnt = $this->update_like($info_seq, - 1);
            $like_yn = 'n';
        } else {
            setcookie('like_cookie_' . $info_seq, true, time() + 3600 * 24 * 7, '/');
            $like_cnt = $this->update_like($info_seq, 1);
            $like_yn = 'y';
        }
        $result['like_cnt'] = $like_cnt;
        $result['like_yn'] = $like_yn;
        echo json_encode($result);
    }

    public function ajax_question()
    {
        header("Content-Type: application/json");

        $info_seq = $this->input->post('info_seq');
        $title = $this->input->post('title');
        $content = $this->input->post('content');
        $userData = $this->getUserData();

        $data = array(
            'info_seq' => $info_seq,
            'userid' => $userData['userid'],
            'title' => $title,
            'content' => $content
        );
        $this->db->insert('fm_cm_machine_question', $data);
        $result = array(
            'result' => 'true'
        );
        echo json_encode($result);
    }

    public function qna_res_process() {
        $qna_seq = $this->input->post('qna_seq');
        $res_content = $this->input->post('res_content');
        
        $data = array(
            'res_content' => $res_content
        );
        $this->db->where('qna_seq', $qna_seq);
        $this->db->update('fm_cm_machine_question', $data);
        
        $callback = "parent.location.reload()";
        openDialogAlert('문의 답변이 완료되었습니다.', 400, 140, 'parent', $callback);
    }
    
    public function ajax_bid()
    {
        header("Content-Type: application/json");

        $info_seq = $this->input->post('info_seq');
        $current_price = $this->input->post('current_price');
        $bid_price = $this->input->post('bid_price');
        $userData = $this->getUserData();

        $query = "select * from fm_cm_machine_sales_detail where info_seq = " . $info_seq;
        $query = $this->db->query($query);
        $result = $query->row_array();

        $bid_current_price = $result['bid_current_price'];

        if ($bid_current_price != $current_price) {
            $res = false;
        } else {
            $data = array(
                'bid_price' => $bid_price,
                'userid' => $userData['userid'],
                'info_seq' => $info_seq
            );
            $this->db->insert('fm_cm_machine_bid', $data);

            $data = array(
                'bid_current_price' => $bid_price
            );
            $this->db->where('info_seq', $info_seq);
            $this->db->update('fm_cm_machine_sales_detail', $data);
            $res = true;
        }
        $query = "select count(*)  as bid_user_cnt from (select * from fm_cm_machine_bid where info_seq = " . $info_seq . ") as c";
        $query = $this->db->query($query);
        $result = $query->row_array();
        echo json_encode(array(
            'result' => $res,
            'bid_user_cnt' => $result['bid_user_cnt'],
            'bid_current_price' => $bid_current_price,
            'current_price' => $current_price
        ));
    }

    public function ajax_bid_user()
    {
        header("Content-Type: application/json");

        $info_seq = $this->input->post('info_seq');

        $query = "select *, date_format(reg_date, '%Y.%m.%d  %p %h:%i') as bid_date from fm_cm_machine_bid where info_seq = " . $info_seq;
        $query = $this->db->query($query);
        $result = $query->result_array();

        echo json_encode($result);
    }

    public function ajax_get_item() {
        header("Content-Type: application/json");
        
        $info_seq = $this->input->post('info_seq');
        $type = $this->input->post('type');
        
        if($type == 'turnkey') {
            $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_kind c,".
                "fm_cm_machine_manufacturer d, fm_cm_machine_model e, fm_cm_machine_area f, fm_cm_machine_sales_turnkey g ".
                "where a.sales_seq = b.sales_seq and b.kind_seq = c.kind_seq and b.mnf_seq = d.mnf_seq ".
                "and b.model_seq = e.model_seq and b.area_seq = f.area_seq and a.sales_seq = g.sales_seq and b.info_seq = ". $info_seq;
            $query = $this->db->query($query);
            $sale_item = $query->row_array();
            
            $query2 = "select * from fm_cm_machine_sales_option where info_seq = ".$sale_item['info_seq']." ".
                "order by option_seq asc";
            $query2 = $this->db->query($query2);
            $result = $query2->result_array();
            $sale_item['option_list'] = $result;
        } else {
            $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_kind c,".
                "fm_cm_machine_manufacturer d, fm_cm_machine_model e, fm_cm_machine_area f ".
                "where a.sales_seq = b.sales_seq and b.kind_seq = c.kind_seq and b.mnf_seq = d.mnf_seq ".
                "and b.model_seq = e.model_seq and b.area_seq = f.area_seq and b.info_seq = ". $info_seq;
            $query = $this->db->query($query);
            $sale_item = $query->row_array();
            
            $query2 = "select * from fm_cm_machine_sales_detail where info_seq = ".$sale_item['info_seq'];
            $query2 = $this->db->query($query2);
            $result = $query2->row_array();
            if(!empty($result))
                $sale_item = array_merge($sale_item, $result);
                
                $query2 = "select * from fm_cm_machine_sales_picture where info_seq = ".$sale_item['info_seq']." ".
                    "order by sort asc";
                $query2 = $this->db->query($query2);
                $result = $query2->result_array();
                $sale_item['picture_list'] = $result;
                
                $query2 = "select * from fm_cm_machine_sales_option where info_seq = ".$sale_item['info_seq']." ".
                    "order by option_seq asc";
                $query2 = $this->db->query($query2);
                $result = $query2->result_array();
                $sale_item['option_list'] = $result;
        }
        echo json_encode(array('result' => $sale_item));
    }
    
    public function find_intro()
    {
        $tpl = 'search/find_intro.html';
        $skin = $this->skin;

        $this->template_path = $tpl;
        $this->template->assign(array(
            "skin_path" => $this->skin,
            "template_path" => $this->template_path
        ));

        $query = "select * from fm_cm_machine_find a, fm_cm_machine_kind b " . "where a.kind_seq = b.kind_seq order by reg_date desc limit 3";
        $query = $this->db->query($query);
        $find_list = $query->result_array();

        $query = "select count(*) as pro_cnt from fm_cm_machine_find where state = 1";
        $query = $this->db->query($query);
        $pro_cnt = $query->row()->pro_cnt;

        $query = "select count(*) as tot_cnt from fm_cm_machine_find";
        $query = $this->db->query($query);
        $tot_cnt = $query->row()->tot_cnt;

        $this->template->assign('pro_cnt', $pro_cnt);
        $this->template->assign('tot_cnt', $tot_cnt);
        $this->template->assign('find_list', $find_list);

        $this->print_layout($skin . '/' . $tpl);
    }

    public function find_reg()
    {
        if (! $this->sessionCheck()) {
            $this->session->set_flashdata('message', '로그인이 필요한 페이지입니다');
            pageRedirect("/user/login");
            exit();
        }
        if ($this->get_bpermit_check() == - 1) {
            $this->session->set_flashdata('message', '사업자등록증을 첨부하시고 사업자 인증을 받으셔야 이용하실 수 있습니다. 기업회원으로 전환해주시기 바랍니다.');
            pageRedirect("/user/my_info_modify/change");
            exit();
        }
        if ($this->get_bpermit_check() == 0) {
            $this->session->set_flashdata('message', '관리자가 인증을 처리하고 있습니다. 빠른 시간에 이용하실수 있도록 하겠습니다. 감사합니다.');
            pageRedirect($_SERVER["HTTP_REFERER"]);
            exit();
        }
        $tpl = 'search/find_reg.html';
        $skin = $this->skin;

        $this->template_path = $tpl;
        $this->template->assign(array(
            "skin_path" => $this->skin,
            "template_path" => $this->template_path
        ));

        $this->db->distinct('kind_type');
        $this->db->select('kind_type, kind_no');
        $query = $this->db->get('fm_cm_machine_kind');
        $result = $query->result_array();
        $kind_map = array();
        foreach ($result as $type) {
            $this->db->where('kind_type', $type['kind_type']);
            $query = $this->db->get('fm_cm_machine_kind');
            $kind_list = $query->result_array();
            $kind_map[] = array(
                'kind_type' => $type['kind_type'],
                'kind_no' => $type['kind_no'],
                'kind_list' => $kind_list
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

        $this->print_layout($skin . '/' . $tpl);
    }

    public function find_reg_process()
    {
        $kind_seq_arr = $this->input->post('kind_seq_arr');
        $mnf_name_arr = $this->input->post('mnf_name_arr');
        $model_name_arr = $this->input->post('model_name_arr');
        $area_list_arr = $this->input->post('area_list_arr');
        $model_year_arr = $this->input->post('model_year_arr');
        $hope_price_arr = $this->input->post('hope_price_arr');
        $option_arr = $this->input->post('option_arr');
        $buy_expect_date_arr = $this->input->post('buy_expect_date_arr');
        $deliver_service_arr = $this->input->post('deliver_service_arr');
        $find_cnt = $this->input->post('find_cnt');

        $userData = $this->getUserData();
        for ($i = 0; $i < $find_cnt; $i ++) {
            $data = array(
                'userid' => $userData['userid'],
                'kind_seq' => $kind_seq_arr[$i],
                'mnf_name' => $mnf_name_arr[$i],
                'model_name' => $model_name_arr[$i],
                'area_list' => $area_list_arr[$i],
                'model_year' => $model_year_arr[$i],
                'hope_price' => $hope_price_arr[$i],
                'option' => $option_arr[$i],
                'buy_expect_date' => $buy_expect_date_arr[$i],
                'deliver_service' => $deliver_service_arr[$i]
            );
            $this->db->insert('fm_cm_machine_find', $data);
        }

        $this->session->set_flashdata('message', '기계찾아줘 등록이 완료되었습니다.');
        pageRedirect("/sch/find_sch");
    }

    public function find_sch()
    {
        header("Content-Type:text/html; charset=utf-8");
        
        $tpl = 'search/find_sch.html';
        $skin = $this->skin;

        $this->template_path = $tpl;
        $this->template->assign(array(
            "skin_path" => $this->skin,
            "template_path" => $this->template_path
        ));

        $this->db->distinct('kind_type');
        $this->db->select('kind_type, kind_no');
        $query = $this->db->get('fm_cm_machine_kind');
        $result = $query->result_array();
        $kind_map = array();
        foreach ($result as $type) {
            $this->db->where('kind_type', $type['kind_type']);
            $query = $this->db->get('fm_cm_machine_kind');
            $kind_list = $query->result_array();
            $kind_map[] = array(
                'kind_type' => $type['kind_type'],
                'kind_no' => $type['kind_no'],
                'kind_list' => $kind_list
            );
        }
        $this->template->assign('kind_map', $kind_map);

        $query = "select * from fm_cm_machine_find group by mnf_name order by mnf_name asc";
        $query = $this->db->query($query);
        $result = $query->result_array();
        $this->template->assign('mnf_list', $result);
        
        /*
        $query = "select * from fm_cm_machine_kind where kind_seq = ".$kind;
        $query = $this->db->query($query);
        $kind_data = $query->row_array();
        $query = "select * from fm_cm_machine_manufacturer where mnf_seq = ".$mnf;
        $query = $this->db->query($query);
        $mnf_data = $query->row_array();
        */
        
        $query = "select * from fm_cm_machine_find group by model_name order by model_name asc";
        $query = $this->db->query($query);
        $result = $query->result_array();
        $this->template->assign('model_list', $result);
      
        $state = empty($_GET['state']) ? '0' : $_GET['state'];
        $kind = empty($_GET['kind']) ? '0' : $_GET['kind'];
        $mnf = empty($_GET['mnf']) ? '0' : $_GET['mnf'];
        $model = empty($_GET['model']) ? '0' : $_GET['model'];
        $find_list = $this->get_find_search($state, $kind, $mnf, $model);

        $this->template->assign('find_list', $find_list);
        $this->template->assign(array(
            'state' => $state,
            'kind' => $kind,
            'mnf' => $mnf,
            'model' => $model
        ));

        $this->print_layout($skin . '/' . $tpl);
    }

    public function find_info($find_seq)
    {
        $tpl = 'search/find_info.html';
        $skin = $this->skin;

        $this->template_path = $tpl;
        $this->template->assign(array(
            "skin_path" => $this->skin,
            "template_path" => $this->template_path
        ));

        $query = "select * from fm_cm_machine_find a, fm_cm_machine_kind b " . "where a.kind_seq = b.kind_seq and find_seq = " . $find_seq;
        $query = $this->db->query($query);
        $info = $query->row_array();

        $query = "select * from fm_cm_machine_find_recommend a, fm_cm_machine_sales b, fm_cm_machine_sales_info c, fm_cm_machine_kind d, fm_cm_machine_area e, fm_cm_machine_sales_picture f " . "where a.info_seq = c.info_seq and b.sales_seq = c.sales_seq and c.kind_seq = d.kind_seq and c.area_seq = e.area_seq and c.info_seq = f.info_seq " . "and c.state = '승인' and c.sort_price is not null and c.sort_price != 0 and f.sort = 2 and a.find_seq = " . $find_seq . " " . "order by a.reg_date desc";
        $query = $this->db->query($query);
        $rec_list = $query->result_array();

        foreach ($rec_list as &$row) {
            $query2 = "select * from fm_cm_machine_like where info_seq = " . $row['info_seq'];
            $query2 = $this->db->query($query2);
            $result = $query2->row_array();
            if (empty($result))
                $row['like_cnt'] = 0;
            else
                $row['like_cnt'] = $result['like_cnt'];

            $query2 = "select * from fm_cm_machine_partner where userid = '" . $row['userid'] . "'";
            $query2 = $this->db->query($query2);
            $result = $query2->row_array();
            if (empty($result)) {
                $query3 = "select * from fm_member where userid = '" . $row['userid'] . "'";
                $query3 = $this->db->query($query3);
                $result2 = $query3->row_array();
                $row['partner_profile_path'] = "/data/uploads/common/no-image.png";
                $row['partner_reg_date'] = $result2['regist_date'];
            } else {
                $row['partner_profile_path'] = $result['profile_path'];
                $row['partner_reg_date'] = $result['reg_date'];
            }
        }

        $this->template->assign('info', $info);
        $this->template->assign('rec_list', $rec_list);
        $this->template->assign('find_seq', $find_seq);

        $this->print_layout($skin . '/' . $tpl);
    }

    public function find_my_mch($find_seq, $state)
    {
        if (! $this->sessionCheck()) {
            $this->session->set_flashdata('message', '로그인이 필요한 페이지입니다.');
            pageRedirect('/user/login');
            exit();
        }
        if ($this->get_bpermit_check() == - 1) {
            $this->session->set_flashdata('message', '사업자등록증을 첨부하시고 사업자 인증을 받으셔야 이용하실 수 있습니다. 기업회원으로 전환해주시기 바랍니다.');
            pageRedirect("/user/my_info_modify/change");
            exit();
        }
        if ($this->get_bpermit_check() == 0) {
            $this->session->set_flashdata('message', '관리자가 인증을 처리하고 있습니다. 빠른 시간에 이용하실수 있도록 하겠습니다. 감사합니다.');
            pageRedirect($_SERVER["HTTP_REFERER"]);
            exit();
        }
        if ($state == '2') {
            $this->session->set_flashdata('message', '상담이 완료되어 기계를 추천할 수 없습니다.');
            pageRedirect('/sch/find_info/' . $find_seq);
            exit();
        }
        if ($state == '3') {
            $this->session->set_flashdata('message', '상담이 종료되어 기계를 추천할 수 없습니다.');
            pageRedirect('/sch/find_info/' . $find_seq);
            exit();
        }
        $userData = $this->getUserData();
        $query = "select userid from fm_cm_machine_find where find_seq = " . $find_seq;
        $query = $this->db->query($query);
        $userid = $query->row()->userid;
        if ($userid == $userData['userid']) {
            $this->session->set_flashdata('message', '기계찾아줘를 신청한 사람은 추천하실 수 없습니다.');
            pageRedirect('/sch/find_info/' . $find_seq);
            return;
        }
        $tpl = 'search/find_my_mch.html';
        $skin = $this->skin;

        $this->template_path = $tpl;
        $this->template->assign(array(
            "skin_path" => $this->skin,
            "template_path" => $this->template_path
        ));

        $query = "select * from fm_cm_machine_sales b, fm_cm_machine_sales_info c, fm_cm_machine_kind d, fm_cm_machine_area e, fm_cm_machine_sales_picture f " . "where b.sales_seq = c.sales_seq and c.kind_seq = d.kind_seq and c.area_seq = e.area_seq and c.info_seq = f.info_seq " . "and c.state = '승인' and c.sort_price is not null and c.sort_price != 0 and f.sort = 2 and userid ='" . $userData['userid'] . "' " . "order by sales_date desc";
        $query = $this->db->query($query);
        $my_list = $query->result_array();

        foreach ($my_list as &$row) {
            $query2 = "select * from fm_cm_machine_like where info_seq = " . $row['info_seq'];
            $query2 = $this->db->query($query2);
            $result = $query2->row_array();
            if (empty($result))
                $row['like_cnt'] = 0;
            else
                $row['like_cnt'] = $result['like_cnt'];

            $query2 = "select * from fm_cm_machine_partner where userid = '" . $row['userid'] . "'";
            $query2 = $this->db->query($query2);
            $result = $query2->row_array();
            if (empty($result)) {
                $query3 = "select * from fm_member where userid = '" . $row['userid'] . "'";
                $query3 = $this->db->query($query3);
                $result2 = $query3->row_array();
                $row['partner_profile_path'] = "/data/uploads/common/no-image.png";
                $row['partner_reg_date'] = $result2['regist_date'];
            } else {
                $row['partner_profile_path'] = $result['profile_path'];
                $row['partner_reg_date'] = $result['reg_date'];
            }
        }

        $this->template->assign('my_list', $my_list);
        $this->template->assign('find_seq', $find_seq);
        $this->print_layout($skin . '/' . $tpl);
    }

    public function find_rec_process()
    {
        $info_seq = $this->input->post('info_seq');
        $find_seq = $this->input->post('find_seq');
        $price = $this->input->post('price');

        $query = "select * from fm_cm_machine_find_recommend where find_seq = " . $find_seq . " and info_seq = " . $info_seq;
        $query = $this->db->query($query);
        $result = $query->result_array();
        if (! empty($result)) {
            $this->session->set_flashdata('message', '해당 기계는 이미 추천하신 기계입니다.');
            pageRedirect('/sch/find_my_mch/' . $find_seq);
            return;
        }

        $userData = $this->getUserData();
        $data = array(
            'userid' => $userData['userid'],
            'find_seq' => $find_seq,
            'info_seq' => $info_seq,
            'price' => $price
        );
        $this->db->insert('fm_cm_machine_find_recommend', $data);
        $this->session->set_flashdata('message', '기계 추천이 완료되었습니다.');
        pageRedirect("/sch/find_info/" . $find_seq);
    }

    private function get_search_result($type, $cate_k, $cate_t, $cate_f, $cate_m, $cate_y, $cate_p, $cate_a, $h, $d, $o, $more)
    {
        if ($type == 'h') {
            $ad_from_query = ", fm_cm_machine_sales_advertise f";
            $ad_where_query = "and b.info_seq = f.info_seq and f.ad_name = '하이라이트' and date_format(now(), '%Y-%m-%d') between start_date and end_date ";
            if ($h != '0')
                $ad_where_query .= "and c.kind_no = " . $h . " ";
        } else if ($type == 'd') {
            $ad_from_query = ", fm_cm_machine_sales_advertise f";
            $ad_where_query = "and b.info_seq = f.info_seq and f.ad_name = '딜러존' and date_format(now(), '%Y-%m-%d') between start_date and end_date ";
            if ($d != '0')
                $ad_where_query .= "and c.kind_no = " . $d . " ";
        } else if ($type == 'c') {
            $ad_from_query = "";
            $ad_where_query = "";
        }
        if ($cate_k != '0' && $type == 'c') {
            $where_query .= "and c.kind_no=" . $cate_k . " ";
        }
        if($cate_t != '0') {
            $where_query .= "and b.kind_seq=" . $cate_t . " ";
        }
        if ($cate_f != '0') {
            $where_query .= "and b.mnf_seq=" . $cate_f . " ";
        }
        if ($cate_m != '0') {
            $where_query .= "and b.model_seq=" . $cate_m . " ";
        }
        if ($cate_y != '0') {
            $value = explode(":", $cate_y);
            $min = $value[0];
            $max = $value[1];
            if ($min == - 1)
                $where_query .= "and b.model_year <= " . $max . " ";
            else if ($max == - 1)
                $where_query .= "and b.model_year >= " . $min . " ";
            else
                $where_query .= "and b.model_year between " . $min . " and " . $max . " ";
        }
        if ($cate_p != '0') {
            $value = explode(":", $cate_p);
            $min = $value[0];
            $max = $value[1];
            if ($min == - 1)
                $where_query .= "and b.sort_price <= " . $max . " ";
            else if ($max == - 1)
                $where_query .= "and b.sort_price >= " . $min . " ";
            else
                $where_query .= "and b.sort_price between " . $min . " and " . $max . " ";
        }
        if ($cate_a != '0') {
            $where_query .= "and b.area_seq=" . $cate_a . " ";
        }
        if ($type == 'c') {
            if ($o == '0')
                $order_query = "order by sort_price asc";
            else if ($o == '1')
                $order_query = "order by sales_date desc";
            else if ($o == '2')
                $order_query = "order by sort_price desc";
            else if ($o == '3')
                $order_query = "order by sort_price asc";
            else if ($o == '4')
                $order_query = "order by model_year desc";
            else
                $order_query = "";
        }

        $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_kind c, fm_cm_machine_sales_picture d, fm_cm_machine_area e, fm_cm_machine_model g, fm_cm_machine_manufacturer h" . $ad_from_query . " " .
                 "where a.sales_seq = b.sales_seq and b.kind_seq = c.kind_seq and b.info_seq = d.info_seq and b.area_seq = e.area_seq and b.model_seq = g.model_seq and b.mnf_seq = h.mnf_seq " .
                 "and b.state = '승인' and d.sort = 2 and b.sort_price is not null " . $ad_where_query . $where_query . $order_query;
        $query = $this->db->query($query);
        $result = $query->result_array();

        if ($type == 'c') {
            $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_kind c, fm_cm_machine_sales_picture d, fm_cm_machine_area e, fm_cm_machine_sales_advertise f " . $ad_from_query . " " . "where a.sales_seq = b.sales_seq and b.kind_seq = c.kind_seq and b.info_seq = d.info_seq and b.area_seq = e.area_seq and b.info_seq = f.info_seq " . "and b.state = '승인' and d.sort = 2 and b.sort_price is not null and f.ad_name = '자동 업데이트' and f.update_time is not null " . $where_query . ' ' . "order by update_time desc";
            $query = $this->db->query($query);
            $update_result = $query->result_array();

            $idx = 0;
            foreach ($result as $row) {
                foreach ($update_result as $row2) {
                    if ($row['sales_seq'] == $row2['sales_seq']) {
                        array_splice($result, $idx, 1);
                        $idx --;
                        break;
                    }
                }
                $idx ++;
            }
            $arr_front = array_slice($result, 0, 5);
            $arr_end = array_slice($result, 5);
            $result = array_merge($arr_front, $update_result, $arr_end);
        }

        $idx = 0;
        foreach ($result as &$row) {
            $query2 = "select * from fm_cm_machine_sales_detail where info_seq = " . $row['info_seq'];
            $query2 = $this->db->query($query2);
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
            $query2 = $this->db->query($query2);
            $result2 = $query2->row_array();
            if (! empty($result2)) {
                $row = array_merge($row, $result2);
            }
        }
        foreach ($result as &$row) {
            $query3 = "select * from fm_cm_machine_like a where info_seq = " . $row['info_seq'];
            $query3 = $this->db->query($query3);
            $result3 = $query3->row_array();
            if (empty($result3))
                $row['like_cnt'] = 0;
            else
                $row['like_cnt'] = $result3['like_cnt'];
        }
        foreach ($result as &$row) {
            $query3 = "select * from fm_cm_machine_partner where userid = '" . $row['userid'] . "'";
            $query3 = $this->db->query($query3);
            $result3 = $query3->row_array();
            if (empty($result3)) {
                $query4 = "select * from fm_member where userid = '" . $row['userid'] . "'";
                $query4 = $this->db->query($query4);
                $result4 = $query4->row_array();
                $row['partner_profile_path'] = "/data/uploads/common/no-image.png";
                $row['partner_reg_date'] = $result4['regist_date'];
            } else {
                $row['partner_profile_path'] = $result3['profile_path'];
                $row['partner_reg_date'] = $result3['reg_date'];
            }
        }
        foreach ($result as &$row) {
            $query = "select COALESCE(convert(avg(grade), signed integer), 0) as grade, count(*) as grade_cnt from fm_cm_machine_sales_eval a, fm_cm_machine_sales b, fm_cm_machine_sales_info c where a.info_seq = c.info_seq and b.sales_seq = c.sales_seq and b.userid = '" . $row['userid'] . "'";
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

    private function get_detail_query($type, $info_seq)
    {
        $resultMap = array();

        if ($type == 'self') {
            $from_query = ", fm_cm_machine_sales_detail f";
            $where_query = "and a.info_seq = f.info_seq";
        } else {
            $from_query = "";
            $where_query = "";
        }
        $query = "select * from fm_cm_machine_sales g, fm_cm_machine_sales_info a, fm_cm_machine_kind b, fm_cm_machine_manufacturer c, fm_cm_machine_model d, fm_cm_machine_area e" . $from_query . " " . "where g.sales_seq = a.sales_seq and a.kind_seq = b.kind_seq and a.mnf_seq = c.mnf_seq and a.model_seq = d.model_seq and a.area_seq = e.area_seq " . "and a.info_seq = " . $info_seq . " " . $where_query;
        $query = $this->db->query($query);
        $result = $query->result_array();
        $sales_seq = $result[0]['sales_seq'];
        $model_seq = $result[0]['model_seq'];
        $sort_price = $result[0]['sort_price'];
        $userid = $result[0]['userid'];

        $query = "select * from fm_cm_machine_partner a, fm_cm_machine_area b where a.area_seq = b.area_seq and userid = '" . $this->getSaleUser($result[0]['info_seq'])['userid'] . "'";
        $query = $this->db->query($query);
        $result2 = $query->row_array();
        if (empty($result2)) {
            $query2 = "select * from fm_member where userid = '" . $this->getSaleUser($result[0]['info_seq'])['userid'] . "'";
            $query2 = $this->db->query($query2);
            $result3 = $query2->row_array();
            $result[0]['partner_profile_path'] = "/data/uploads/common/no-image.png";
            $result[0]['partner_reg_date'] = $result3['regist_date'];
            $result[0]['partner_area_name'] = '';
        } else {
            $result[0]['partner_profile_path'] = $result2['profile_path'];
            $result[0]['partner_reg_date'] = $result2['reg_date'];
            $result[0]['partner_area_name'] = $result2['area_name'];
        }

        $resultMap['info_list'] = $result[0];

        $query = "select * from fm_cm_machine_sales_info a, fm_cm_machine_sales_picture b " . "where a.info_seq = b.info_seq and a.info_seq = " . $info_seq . " order by sort asc";
        $query = $this->db->query($query);
        $result = $query->result_array();
        $resultMap['picture_list'] = $result;

        $query = "select * from fm_cm_machine_sales_info a, fm_cm_machine_sales_option b " . "where a.info_seq = b.info_seq and a.info_seq = " . $info_seq . " order by option_seq asc";
        $query = $this->db->query($query);
        $result = $query->result_array();
        $resultMap['option_list'] = $result;

        $query = "select * from fm_cm_machine_view a where a.info_seq = " . $info_seq;
        $query = $this->db->query($query);
        $result = $query->row();
        $resultMap['view_cnt'] = $result->view_cnt;

        $resultMap['other_model'] = $this->get_other_model_price($model_seq, $sort_price);

        $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b left outer join " . 
                 "fm_cm_machine_perform c on b.info_seq = c.info_seq where a.sales_seq = b.sales_seq and b.info_seq = " . $info_seq;
        $query = $this->db->query($query);
        $result = $query->row_array();
        $resultMap['perform'] = $result;

        $query = "select count(*)  as bid_user_cnt from (select * from fm_cm_machine_bid where info_seq = " . $info_seq . ") as c";
        $query = $this->db->query($query);
        $result = $query->row_array();
        $resultMap['bid_user_cnt'] = $result['bid_user_cnt'];

        $query = "select COALESCE(convert(avg(grade), signed integer), 0) as grade, count(*) as grade_cnt from fm_cm_machine_sales_eval a, fm_cm_machine_sales b, fm_cm_machine_sales_info c where a.info_seq = c.info_seq and b.sales_seq = c.sales_seq and b.userid = '" . $userid . "'";
        $query = $this->db->query($query);
        $resultMap['grade'] = $query->row()->grade;
        $resultMap['grade_cnt'] = $query->row()->grade_cnt;

        $query = "select count(*) as sale_ing_cnt from fm_cm_machine_sales a, fm_cm_machine_sales_info b where a.sales_seq = b.sales_seq and b.state = '승인' and b.sales_yn = 'n' and a.userid = '" . $userid . "'";
        $query = $this->db->query($query);
        $resultMap['sale_ing_cnt'] = $query->row()->sale_ing_cnt;

        $query = "select count(*) as sale_finish_cnt from fm_cm_machine_sales a, fm_cm_machine_sales_info b where a.sales_seq = b.sales_seq and b.state = '승인' and b.sales_yn = 'y' and a.userid = '" . $userid . "'";
        $query = $this->db->query($query);
        $resultMap['sale_finish_cnt'] = $query->row()->sale_finish_cnt;

        $query = "select * from fm_cm_machine_sales_check where sales_seq = " . $sales_seq;
        $query = $this->db->query($query);
        $result = $query->row_array();
        $resultMap['check_list'] = $result;

        $query = "select hotmark_list from fm_cm_machine_sales_advertise where ad_name = '핫마크' and (date_format(now(), '%Y-%m-%d') between start_date and end_date) and info_seq = " . $info_seq;
        $query = $this->db->query($query);
        $result = $query->row_array();
        $resultMap['hotmark_list'] = $result['hotmark_list'];
        return $resultMap;
    }

    private function update_view($info_seq)
    {
        $query = "select * from fm_cm_machine_view a where a.info_seq = " . $info_seq;
        $query = $this->db->query($query);
        $result = $query->row();
        if (empty($result)) {
            $data = array(
                'info_seq' => $info_seq,
                'view_cnt' => 1
            );
            $this->db->insert('fm_cm_machine_view', $data);
        } else {
            $data = array(
                'view_cnt' => $result->view_cnt + 1
            );
            $this->db->where('info_seq', $info_seq);
            $this->db->update('fm_cm_machine_view', $data);
        }
        setcookie('view_cookie_' . $info_seq, true, time() + 3600 * 24, '/');
    }

    private function update_like($info_seq, $cnt)
    {
        $like_cnt = $this->get_like_cnt($info_seq);

        $data = array(
            'like_cnt' => $like_cnt + $cnt
        );
        $this->db->where('info_seq', $info_seq);
        $this->db->update('fm_cm_machine_like', $data);
        $like_cnt = $like_cnt + $cnt;
        return $like_cnt;
    }

    private function get_like_cnt($info_seq)
    {
        $query = "select * from fm_cm_machine_like a where a.info_seq = " . $info_seq;
        $query = $this->db->query($query);
        $result = $query->row();
        if (empty($result)) {
            $data = array(
                'info_seq' => $info_seq,
                'like_cnt' => 0
            );
            $this->db->insert('fm_cm_machine_like', $data);
            $like_cnt = 0;
        } else {
            $like_cnt = $result->like_cnt;
        }
        return $like_cnt;
    }

    private function get_other_model_price($model_seq, $sort_price)
    {
        $query = "select sort_price from fm_cm_machine_sales_info a where a.sort_price != 0 and a.sort_price is not null and a.model_seq = " . $model_seq;
        $query = $this->db->query($query);
        $result = $query->result_array();

        $min = $result[0]['sort_price'];
        $max = 0;
        foreach ($result as $row) {
            $row = $row['sort_price'];
            if ($row < $min)
                $min = $row;
            if ($row > $max)
                $max = $row;
        }
        $min_range = $sort_price - $min;
        $max_range = $max - $sort_price;
        if ($min_range == $max_range) {
            $range = 50;
        } else if ($min_range > $max_range) {
            if ($max == $sort_price)
                $range = 90;
            else
                $range = 75;
        } else {
            if ($min == $sort_price)
                $range = 10;
            else
                $range = 25;
        }
        $result = array(
            'min' => $min,
            'max' => $max,
            'range' => $range
        );
        return $result;
    }

    private function getBidRestTime($info_seq)
    {
        $query = "select *, UNIX_TIMESTAMP(now()) as now_date, UNIX_TIMESTAMP(date_add(sales_date, interval +bid_duration day)) as bid_date " . "from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_sales_detail c " . "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and b.info_seq = " . $info_seq;
        $query = $this->db->query($query);
        $result = $query->row_array();

        $now_date = $result['now_date'];
        $bid_date = $result['bid_date'];

        $date1 = $bid_date;
        $date2 = $now_date;

        $data = array();
        $data['restTime'] = $date1 - $date2;
        $data['isEnd'] = false;
        if ($date1 - $date2 <= 0) {
            $data['isEnd'] = true;
        }
        return $data;
    }

    private function get_find_search($state, $kind, $mnf, $model)
    {
        if ($state == '0') {
            $where_query = "";
        } else if ($state == '1' || $state == '2' || $state == '3') {
            $where_query = "and state = " . $state . " ";
        }

        if ($kind == '0')
            $where_query .= "";
        else
            $where_query .= "and a.kind_seq = " . $kind . " ";

        if ($mnf == '0')
            $where_query .= "";
        else
            $where_query .= "and a.mnf_name = '" . $mnf . "' ";

        if ($model == '0')
            $where_query .= "";
        else
            $where_query .= "and a.model_name = '" . $model . "' ";

        $query = "select * from fm_cm_machine_find a, fm_cm_machine_kind b " . "where a.kind_seq = b.kind_seq " . $where_query . " order by reg_date desc";
        $query = $this->db->query($query);
        $result = $query->result_array();

        return $result;
    }

    private function getUserData()
    {
        $this->userInfo = $this->session->userdata('user');
        return $this->membermodel->get_member_data($this->userInfo['member_seq']);
    }

    private function getUserDataById($userid)
    {
        $query = "select * from fm_member where userid='" . $userid . "'";
        $query = $this->db->query($query);
        $result = $query->row_array();
        return $this->membermodel->get_member_data($result['member_seq']);
    }

    private function getUserDataBySeq($member_seq)
    {
        return $this->membermodel->get_member_data($member_seq);
    }

    private function sessionCheck()
    {
        $this->userInfo = $this->session->userdata('user');
        if (! $this->userInfo['member_seq']) {
            return false;
        } else {
            return true;
        }
    }

    private function loginUserEqualCheck($saleUser)
    {
        $userData = $this->getUserData();
        if ($userData['userid'] == $saleUser['userid'])
            return true;
        else
            return false;
    }

    private function getSaleUser($info_seq)
    {
        $query = "select userid from fm_cm_machine_sales a, fm_cm_machine_sales_info b where a.sales_seq = b.sales_seq and b.info_seq = " . $info_seq;
        $query = $this->db->query($query);
        $result = $query->row_array();
        return $result;
    }

    private function send_email($apply_type)
    {
        $userData = $this->getUserData();
        $email = $userData['email'];
        if ($email) {
            sch_apply_mail($email, $apply_type);
        }
    }

    private function send_sms($apply_type)
    {
        $userData = $this->getUserData();
        $phone = $userData['cellphone'];
        if ($phone) {
            sch_apply_sms($phone, $apply_type);
        }
    }

    private function send_common_mail($email, $title, $message)
    {
        if ($email) {
            send_common_mail($email, $title, $message);
        }
    }

    private function send_common_sms($phone, $message)
    {
        if ($phone) {
            send_common_sms($phone, $message);
        }
    }

    private function get_bpermit_check()
    {
        $userData = $this->getUserData();
        $query = "select * from fm_member_business where member_seq = " . $userData['member_seq'];
        $query = $this->db->query($query);
        $result = $query->row_array();
        if (empty($result)) {
            return - 1;
        } else {
            if ($result['bpermit_yn'] == 'y') {
                return 1;
            } else {
                return 0;
            }
        }
    }

    private function create_member_session($data = array())
    {
        $this->load->helper('member');
        create_member_session($data);
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