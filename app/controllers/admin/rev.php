<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);
class rev extends front_base {

    public function __construct() {
        parent::__construct();
        $this->load->model('membermodel');
    }
    
    ## 후기 정보
    public function rev_info($rev_seq) {
        $tpl = 'review/rev_info.html';
        $skin = $this->skin;
        
        $this->template_path = $tpl;
        $this->template->assign(array("skin_path"=>$this->skin,
            "template_path"=>$this->template_path));
        
        $query = "select * from fm_cm_machine_sales_review where rev_seq = ".$rev_seq;
        $query = $this->db->query($query);
        $info = $query->row_array();
        
        if(!$_COOKIE['rev_view_cookie_'.$rev_seq]) {
            $data = array(
                'view_cnt' => $info['view_cnt'] + 1
            );
            $this->db->where('rev_seq', $rev_seq);
            $this->db->update('fm_cm_machine_sales_review', $data);
            setcookie('rev_view_cookie_'.$rev_seq, true, time() + 3600 * 24, '/');
            $info['view_cnt'] = $info['view_cnt'] + 1;
        }
        
        $this->template->assign('info', $info);
        
        $this->print_layout($skin.'/'.$tpl);
    }
    
    ## 후기 작성
    public function rev_write($type, $seq) {
        if(!$this->sessionCheck()) {
            $this->session->set_flashdata('message', '로그인이 필요한 페이지입니다');
            pageRedirect("/user/login");
        }
        $tpl = 'review/rev_write.html';
        $skin = $this->skin;
        
        $this->template_path = $tpl;
        $this->template->assign(array("skin_path"=>$this->skin,
            "template_path"=>$this->template_path));
        
        $userData = $this->getUserData();
        
        if($type == 's') {
            $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b where info_seq = ".$seq;
            $query = $this->db->query($query);
            $result = $query->row_array();
            $target_userid = $result['userid'];
        } else if($type == 'o') {
            $query = "select * from fm_cm_machine_partner where partner_seq = ".$seq;
            $query = $this->db->query($query);
            $result = $query->row_array();
            $target_userid = $result['userid'];
        }
        $this->template->assign('type', $type);
        $this->template->assign('seq', $seq);
        $this->template->assign('userid', $userData['userid']);
        $this->template->assign('target_userid', $target_userid);
        
        $this->print_layout($skin.'/'.$tpl);
    }
    
    ## 후기 작성 처리
    public function rev_write_process() {
        $type = $this->input->post('type');
        $target_userid = $this->input->post('target_userid');
        $grade_01 = $this->input->post('grade_01');
        $grade_02 = $this->input->post('grade_02');
        $grade_03 = $this->input->post('grade_03');
        $grade_04 = $this->input->post('grade_04');
        $grade_05 = $this->input->post('grade_05');
        $grade = $this->input->post('grade');
        $title = $this->input->post('title');
        $content = $this->input->post('content');
        
        $userData = $this->getUserData();
        $data = array(
            'type' => $type,
            'target_userid' => $target_userid,
            'userid' => $userData['userid'],
            'grade_01' => $grade_01,
            'grade_02' => $grade_02,
            'grade_03' => $grade_03,
            'grade_04' => $grade_04,
            'grade_05' => $grade_05,
            'grade' => $grade,
            'title' => $title,
            'content' => $content
        );
        
        $this->load->library('upload');
        $files = $_FILES;
        
        $upload_path = "./data/uploads/review";
        $filename = 'rev_image';
        
        $this->upload->initialize($this->set_upload_options($upload_path));
        if($this->upload->do_upload($filename)) {
            $upload_data = $this->upload->data();
            $data['path'] = str_replace(".", "", $upload_path).'/'.$upload_data['file_name'];
        } else {
            $data['path'] = '/data/uploads/common/no-image.png';
        }
        $this->db->insert('fm_cm_machine_sales_review', $data);
        
        $callback = "parent.location.reload()";
        openDialogAlert('작성이 완료되었습니다.',400,140,'parent',$callback);
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
    
    private function set_upload_options($upload_path) {
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
}