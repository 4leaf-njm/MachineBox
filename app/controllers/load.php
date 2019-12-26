<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);
class load extends front_base {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('membermodel');
        $this->load->helper(array('form', 'url', 'mail', 'sms'));
    }
    
    public function data_update()
    {
        header("Content-Type: application/json");
        
        ## 기계 찾아줘 상태 변경
        $query = "select * from fm_cm_machine_find";
        $query = $this->db->query($query);
        $result = $query->result_array();
        
        foreach($result as $row) {
            if($row['buy_expect_date'] < date('Y-m-d')) {
                $query2 = "select * from fm_cm_machine_find_recommend where find_seq = ".$row['find_seq'];
                $query2 = $this->db->query($query2);
                $result2 = $query2->result_array();
                
                if(count($result2) > 0) {
                    $data = array(
                        'state' => '2'
                    );
                } else {
                    $data = array(
                        'state' => '3'
                    );
                }
                $this->db->where('find_seq', $row['find_seq']);
                $this->db->update('fm_cm_machine_find', $data);
            }
        }
        
        ## 외주 상태 변경
        $query = "select * from fm_cm_machine_outsourcing";
        $query = $this->db->query($query);
        $result = $query->result_array();
        
        foreach($result as $row) {
            if($row['osc_end_date'] < date('Y-m-d')) {
                $data = array(
                    'state' => '2'
                );
                $this->db->where('osc_seq', $row['osc_seq']);
                $this->db->update('fm_cm_machine_outsourcing', $data);
            }
        }
        
        ## 가격제안 답변 기한
        $query = "select * from fm_cm_machine_proposal";
        $query = $this->db->query($query);
        $result = $query->result_array();
        
        foreach($result as $row) {
            if($row['permit_yn'] == 'h' && $row['prop_date'] < date('Y-m-d')) {
                $data = array(
                    'permit_yn' => 'x'
                );
                $this->db->where('prop_seq', $row['prop_seq']);
                $this->db->update('fm_cm_machine_proposal', $data);
            }
            if($row['permit_yn'] == 'c' && $row['counter_permit_yn'] == 'h' && $row['counter_date'] < date('Y-m-d')) {
                $data = array(
                    'counter_permit_yn' => 'x'
                );
                $this->db->where('prop_seq', $row['prop_seq']);
                $this->db->update('fm_cm_machine_proposal', $data);
            }
        }
        
        ## 입찰 종료 처리
        $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_sales_detail c, fm_cm_machine_model d, fm_cm_machine_kind e ".
                 "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and b.model_seq = d.model_seq and b.kind_seq = e.kind_seq and method = '입찰' and bid_yn = 'n'";
        $query = $this->db->query($query);
        $result = $query->result_array();
        
        foreach($result as $row) {
            $bid_user_x = array();
            $bid_date = strtotime('+'.$row['bid_duration'].'days', strtotime($row['sales_date']));
            if(((strtotime(date('Y-m-d H:i:s')) - $bid_date)/3600) >= 0) {
                $query = "select * from fm_cm_machine_bid where info_seq = ".$row['info_seq']." order by bid_price desc";
                $query = $this->db->query($query);
                $result2 = $query->result_array();
                $bid_res_price = NULL;
                if(count($result2) > 0) {
                    for($i=0; $i<count($result2); $i++) {
                        if($i == 0) {
                            $bid_yn = 'y';
                            $bid_res_price = $result2[$i]['bid_price'];
                            $bid_user_y = $result2[$i]['userid'];
                        } else {
                            $bid_yn = 'x';
                            if($result2[$i]['userid'] != $bid_user_y) {
                                $bid_user_x[] = $result2[$i]['userid'];
                            }
                        }
                        $data = array(
                            'bid_yn' => $bid_yn
                        );
                        $this->db->where('bid_seq', $result2[$i]['bid_seq']);
                        $this->db->update('fm_cm_machine_bid', $data);
                    }
                    $data = array(
                        'bid_yn' => 'y',
                        'bid_res_price' => $bid_res_price
                    );
                    $this->db->where('sdet_seq', $row['sdet_seq']);
                    $this->db->update('fm_cm_machine_sales_detail', $data);
                    $bid_user_y_data = $this->getUserData($bid_user_y);
                    $title = "입찰하신 경매 <b>낙찰</b>";
                    $message = "입찰하신 경매가 최종 낙찰되었습니다.";
                    $this->send_common_mail($bid_user_y_data['email'], $title, $message);
                    $this->send_common_sms($bid_user_y_data['cellphone'], $message);
                    
                    $bid_user_x = array_unique($bid_user_x);
                    foreach($bid_user_x as $row2) {
                        $bid_user_x_data = $this->getUserData($row2);
                        $title = "입찰하신 경매 <b>유찰</b>";
                        $message = "입찰하신 경매가 최종 유찰되었습니다.";
                        $this->send_common_mail($bid_user_x_data['email'], $title, $message);
                        $this->send_common_sms($bid_user_x_data['cellphone'], $message);
                    }
                    $sale_user_data = $this->getUserData($row['userid']);
                    $title = "경매 <b>종료</b>";
                    $message = "판매자님께서 경매를 하시는 ".$row['model_name']."이 최종 낙찰되었습니다. 총입찰자: ".count($result2)."명, 최종 입찰금액: ".$bid_res_price."원";
                    $this->send_common_mail($sale_user_data['email'], $title, $message);
                    $this->send_common_sms($sale_user_data['cellphone'], $message);
                } else {
                    if((int)$row['repeat_no'] > 0) {
                        $query = "select * from fm_cm_machine_sales where sales_seq = ".$row['sales_seq'];
                        $query = $this->db->query($query);
                        $sales_data = $query->row_array();
                        $sales_data['sales_date'] = date('Y-m-d H:i:s');
                        $sales_seq = $sales_data['sales_seq'];
                        unset($sales_data['sales_seq']);
                        $this->db->where('sales_seq', $sales_seq);
                        $this->db->update('fm_cm_machine_sales', $sales_data);
                        
                        $query = "select * from fm_cm_machine_sales_info where info_seq = ".$row['info_seq'];
                        $query = $this->db->query($query);
                        $info_data = $query->row_array();
                        $info_data['sales_seq'] = $sales_seq;
                        $info_data['sales_no'] = $this->getSalesNo($row['kind_no']);
						$info_data['sort_price'] = substr((int)$row['bid_start_price'] - ((int)$row['bid_start_price'] * (int)$row['reduction_rate'] / 100), 0, -4).'0000';
                        $info_seq = $row['info_seq'];
                        unset($info_data['info_seq']);
                        $this->db->where('info_seq', $info_seq);
                        $this->db->update('fm_cm_machine_sales_info', $info_data);
                        
                        $query = "select * from fm_cm_machine_sales_detail where info_seq = ".$row['info_seq'];
                        $query = $this->db->query($query);
                        $detail_data = $query->row_array();
                        $detail_data['info_seq'] = $row['info_seq'];
                        $detail_data['bid_yn'] = 'n';
                        $detail_data['bid_res_price'] = NULL;
                        $detail_data['repeat_no'] = (int)$row['repeat_no'] - 1;
                        $detail_data['bid_start_price'] = substr((int)$row['bid_start_price'] - ((int)$row['bid_start_price'] * (int)$row['reduction_rate'] / 100), 0, -4).'0000';
                        $detail_data['bid_current_price'] = substr((int)$row['bid_start_price'] - ((int)$row['bid_start_price'] * (int)$row['reduction_rate'] / 100), 0, -4).'0000';
                        unset($detail_data['sdet_seq']);
                        $this->db->where('info_seq', $info_seq);
                        $this->db->update('fm_cm_machine_sales_detail', $detail_data);
                        
                        /*
                        $query = "select * from fm_cm_machine_sales_picture where info_seq = ".$row['info_seq'];
                        $query = $this->db->query($query);
                        $picture_data = $query->result_array();
                        foreach($picture_data as &$data) {
                            $data['info_seq'] = $info_seq;
                            unset($data['picture_seq']);
                            $this->db->insert('fm_cm_machine_sales_picture', $data);
                        }
                        
                        $query = "select * from fm_cm_machine_sales_option where info_seq = ".$row['info_seq'];
                        $query = $this->db->query($query);
                        $option_data = $query->result_array();
                        foreach($option_data as &$data) {
                            $data['info_seq'] = $info_seq;
                            unset($data['option_seq']);
                            $this->db->insert('fm_cm_machine_sales_option', $data);
                        }
                        
                        $query = "select * from fm_cm_machine_sales_advertise where info_seq = ".$row['info_seq'];
                        $query = $this->db->query($query);
                        $ad_data = $query->result_array();
                        foreach($ad_data as &$data) {
                            $data['sales_seq'] = $sales_seq;
                            unset($data['ad_seq']);
                            $this->db->insert('fm_cm_machine_sales_advertise', $data);
                        }
                        
                        $query = "select * from fm_cm_machine_sales_check where sales_seq = ".$row['sales_seq'];
                        $query = $this->db->query($query);
                        $check_data = $query->row_array();
                        $check_data['sales_seq'] = $sales_seq;
                        unset($check_data['chk_seq']);
                        $this->db->insert('fm_cm_machine_sales_check', $check_data);
                        */
                    } else {
						$query = "select * from fm_cm_machine_sales_detail where info_seq = ".$row['info_seq'];
                        $query = $this->db->query($query);
                        $detail_data = $query->row_array();
                        $detail_data['info_seq'] = $row['info_seq'];
                        $detail_data['bid_yn'] = 'y';
                        $detail_data['bid_res_price'] = NULL;
                        unset($detail_data['sdet_seq']);
                        $this->db->where('info_seq', $row['info_seq']);
                        $this->db->update('fm_cm_machine_sales_detail', $detail_data);
					}
                    $sale_user_data = $this->getUserData($row['userid']);
                    $title = "경매 <b>종료</b>";
                    $message = "판매자님께서 경매를 하시는 ".$row['model_name']."이 최종 유찰되었습니다. 시작가를 하향 조정하시면, 판매가능성이 높아집니다.";
                    $this->send_common_mail($sale_user_data['email'], $title, $message);
                    $this->send_common_sms($sale_user_data['cellphone'], $message);
                }
            }
        }
        
        ## 현장방문 중개수수료 60일 이후 미결제 처리
        $query = "select * from fm_cm_machine_sales_info where visit_pay_yn != 'n'";
        $query = $this->db->query($query);
        $result = $query->result_array();
        
        foreach($result as $row) {
            $pay_date = strtotime('+60 days', strtotime($row['visit_pay_date']));
            $now_date = strtotime(date('Y-m-d H:i:s'));
            if($pay_date < $now_date) {
                $data = array(
                    'visit_pay_yn' => 'n',
                    'visit_pay_date' => NULL
                );
                $this->db->where('info_seq', $row['info_seq']);
                $this->db->update('fm_cm_machine_sales_info', $data);
            }
        }
        
        ## 셀프판매 30일 이후 판매대기 처리
        $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b ".
            "where a.sales_seq = b.sales_seq and (sales_date + interval 1 month) < now() ".
            "and type = 'self' and state = '승인' and wait_yn = 'n' and sales_yn = 'n'";
        $query = $this->db->query($query);
        $result = $query->result_array();
        
        $user_list = array();
        foreach($result as $row) {
            $user_list[] = $row['userid'];
            
            $data = array(
                'wait_yn' => 'y',
                'admin_view_yn' => 'n'
            );
            $this->db->where('info_seq', $row['info_seq']);
            $this->db->update('fm_cm_machine_sales_info', $data);
        }
        
        $user_list = array_unique($user_list);
        foreach($user_list as $row) {
            $userData = $this->getUserData($row);
            $title = "재등록 <b>알림</b>";
            $message = "등록기간이 만료된 기계가 있습니다. 판매대기에서 재등록하시기 바랍니다.";
            $this->send_common_mail($userData['email'], $title, $message);
            $this->send_common_sms($userData['cellphone'], $message);
        }
        
        echo json_encode($res);
    }
    
    private function getUserData($userid) {
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
}