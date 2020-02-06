<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class com extends admin_base {

	public function __construct() {
		parent::__construct();
		$this->load->model('membermodel');
		$this->load->helper(array('form', 'url', 'mail', 'sms'));
	}

	public function index()
	{
		redirect("/admin/com/category");
	}

	## 카테고리 관리
	public function category() {
	    $this->admin_menu();
	    $this->tempate_modules();
	    
	    $file_path	= $this->template_path();
	    
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
	    
	    $query = "select * from fm_cm_machine_manufacturer order by mnf_name asc";
	    $query = $this->db->query($query);
	    $mnf_list = $query->result_array();
	    
	    
	    $this->template->assign('kind_map', $kind_map);
	    $this->template->assign('mnf_list', $mnf_list);
	    $this->template->assign('type', $_GET['type']);
	    $this->template->assign('mnf_kind', $_GET['kind']);
	    $this->template->assign('model_kind', $_GET['kind']);
	    $this->template->assign('model_mnf', $_GET['mnf']);
	    $this->template->define(array('tpl'=>$file_path));
	    $this->template->print_("tpl");
	}
	
	## 카테고리 조회
	public function ajax_get_category() {
	    header("Content-Type: application/json");
	    
	    $type = $this->input->post('type');
	    $mnf_kind = $this->input->post('mnf_kind');
	    $model_kind = $this->input->post('model_kind');
	    $model_mnf = $this->input->post('model_mnf');
	    
	    if($type == 'kind') {
	        $query = "select * from fm_cm_machine_kind order by kind_no asc, kind_seq asc";
	        $query = $this->db->query($query);
	        $result = $query->result_array();
	    } else if ($type == 'mnf') {
	        $query = "select * from fm_cm_machine_kind where kind_seq = ".$mnf_kind;
	        $query = $this->db->query($query);
	        $kind_data = $query->row_array();
	        
	        if($mnf_kind == '0') {
	            $mnf_where = "";
	        } else {
	            $mnf_where = "where mnf_kind = '".$kind_data['kind_name']."' ";
	        }
	        $query = "select * from fm_cm_machine_manufacturer ".$mnf_where."order by mnf_name asc";
	        $query = $this->db->query($query);
	        $result = $query->result_array();
	    } else if ($type == 'model') {
	        $query = "select * from fm_cm_machine_kind where kind_seq = ".$model_kind;
	        $query = $this->db->query($query);
	        $kind_data = $query->row_array();
	        $query = "select * from fm_cm_machine_manufacturer where mnf_seq = ".$model_mnf;
	        $query = $this->db->query($query);
	        $mnf_data = $query->row_array();
	        
	        if($model_kind == '0' && $model_mnf == '0') {
	            $model_where = "";
	        } else if($model_kind != '0' && $model_mnf == '0') {
	            $model_where = "where model_kind = '".$kind_data['kind_name']."' ";
	        } else if($model_kind == '0' && $model_mnf != '0') {
	            $model_where = "where model_mnf = '".$mnf_data['mnf_name']."' ";
	        } else {
	            $model_where = "where model_kind = '".$kind_data['kind_name']."' and model_mnf = '".$mnf_data['mnf_name']."' ";
	        }
	        $query = "select * from fm_cm_machine_model ".$model_where."order by model_name asc";
	        $query = $this->db->query($query);
	        $result = $query->result_array();
	    }
	    echo json_encode($result);
	}
	
	## 카테고리 추가
	public function ajax_add_category() {
	    header("Content-Type: application/json");
	    
	    $type = $this->input->post('type');
	    $name = $this->input->post('name');
	    $kind = $this->input->post('kind');
	    $mnf_kind = $this->input->post('mnf_kind');
	    $model_kind = $this->input->post('model_kind');
	    $model_mnf = $this->input->post('model_mnf');
	    
	    if($type == 'kind') {
	        $query = "select * from fm_cm_machine_kind where kind_type = '".$kind."' limit 1";
	        $query = $this->db->query($query);
	        $result = $query->row_array();
	        if(empty($result)) {
	            $query = "select * from fm_cm_machine_kind order by kind_no desc limit 1";
	            $query = $this->db->query($query);
	            $result = $query->row_array();
	            $kind_no = (int)$result['kind_no'] + 1;
	            
	        } else {
	            $kind_no = $result['kind_no'];
	        }
	        $data = array(
	            'kind_type' => $kind,
	            'kind_name' => $name,
	            'kind_no' => $kind_no
	        );
	        $this->db->insert('fm_cm_machine_kind', $data);
	    } else if ($type == 'mnf') {
	        $query = "select * from fm_cm_machine_kind where kind_seq = ".$mnf_kind;
	        $query = $this->db->query($query);
	        $kind_data = $query->row_array();
	        
	        $data = array(
	            'mnf_name' => $name,
	            'mnf_kind' => $kind_data['kind_name']
	        );
	        $this->db->insert('fm_cm_machine_manufacturer', $data);
	    } else if ($type == 'model') {
	        $query = "select * from fm_cm_machine_kind where kind_seq = ".$model_kind;
	        $query = $this->db->query($query);
	        $kind_data = $query->row_array();
	        $query = "select * from fm_cm_machine_manufacturer where mnf_seq = ".$model_mnf;
	        $query = $this->db->query($query);
	        $mnf_data = $query->row_array();
	        
	        $data = array(
	            'model_name' => $name,
	            'model_kind' => $kind_data['kind_name'],
	            'model_mnf' => $mnf_data['mnf_name']
	        );
	        $this->db->insert('fm_cm_machine_model', $data);
	    }
	    $res = 'true';
	    echo json_encode(array('result' => $res));
	}
	
	## 카테고리 변경
	public function ajax_modify_category() {
	    header("Content-Type: application/json");
	    
	    $type = $this->input->post('type');
	    $seq = $this->input->post('seq');
	    $name = $this->input->post('name');
	    $kind = $this->input->post('kind');
	    $mnf_kind = $this->input->post('mnf_kind');
	    $model_kind = $this->input->post('model_kind');
	    $model_mnf = $this->input->post('model_mnf');
	    
	    if($type == 'kind') {
	        $query = "select * from fm_cm_machine_kind where kind_type = '".$kind."' limit 1";
	        $query = $this->db->query($query);
	        $result = $query->row_array();
	        if(empty($result)) {
	            $query = "select * from fm_cm_machine_kind order by kind_no desc limit 1";
	            $query = $this->db->query($query);
	            $result = $query->row_array();
	            $kind_no = (int)$result['kind_no'] + 1;
	            
	        } else {
	            $kind_no = $result['kind_no'];
	        }
	        $data = array(
	            'kind_type' => $kind,
	            'kind_name' => $name,
	            'kind_no' => $kind_no
	        );
	        $this->db->where('kind_seq', $seq);
	        $this->db->update('fm_cm_machine_kind', $data);
	    } else if ($type == 'mnf') {
	        $query = "select * from fm_cm_machine_kind where kind_seq = ".$mnf_kind;
	        $query = $this->db->query($query);
	        $kind_data = $query->row_array();
	        
	        $data = array(
	            'mnf_name' => $name,
	            'mnf_kind' => $kind_data['kind_name']
	        );
	        $this->db->where('mnf_seq', $seq);
	        $this->db->update('fm_cm_machine_manufacturer', $data);
	    } else if ($type == 'model') {
	        $query = "select * from fm_cm_machine_kind where kind_seq = ".$model_kind;
	        $query = $this->db->query($query);
	        $kind_data = $query->row_array();
	        $query = "select * from fm_cm_machine_manufacturer where mnf_seq = ".$model_mnf;
	        $query = $this->db->query($query);
	        $mnf_data = $query->row_array();
	        
	        $data = array(
	            'model_name' => $name,
	            'model_kind' => $kind_data['kind_name'],
	            'model_mnf' => $mnf_data['mnf_name']
	        );
	        $this->db->where('model_seq', $seq);
	        $this->db->update('fm_cm_machine_model', $data);
	    }
	    $result = 'true';
	    echo json_encode(array('result' => $result));
	}
	
	## 카테고리 제거
	public function ajax_remove_category() {
	    header("Content-Type: application/json");
	    
	    $type = $this->input->post('type');
	    $seq = $this->input->post('seq');
	    
	    if($type == 'kind') {
	        $this->db->where('kind_seq', $seq);
	        $this->db->delete('fm_cm_machine_kind');
	    } else if ($type == 'mnf') {
	        $this->db->where('mnf_seq', $seq);
	        $this->db->delete('fm_cm_machine_manufacturer');
	    } else if ($type == 'model') {
	        $this->db->where('model_seq', $seq);
	        $this->db->delete('fm_cm_machine_model');
	    }
	    $result = 'true';
	    echo json_encode(array('result' => $result));
	}
	
	## 문의 관리
	public function qna() {
	    $this->admin_menu();
	    $this->tempate_modules();
	    
	    $file_path	= $this->template_path();
	    
	    $query = "select * from fm_cm_machine_my_qna order by reg_date desc";
	    $query = $this->db->query($query);
	    $qna_list = $query->result_array();
	    
	    $this->template->assign('qna_list', $qna_list);
	    
	    $this->template->define(array('tpl'=>$file_path));
	    $this->template->print_("tpl");
	}
	
	## 문의 답변 처리
	public function qna_process() {
	    $qna_seq = $this->input->post('qna_seq');
	    $reply = $this->input->post('reply');
	    
	    $data = array(
	        'reply' => $reply
	    );
	    $this->db->where('qna_seq', $qna_seq);
	    $this->db->update('fm_cm_machine_my_qna', $data);
	    
	    $callback = "parent.location.reload()";
	    openDialogAlert('답변이 작성되었습니다.',400,140,'parent',$callback);
	}
	
	## 후기 관리
	public function rev() {
	    $this->admin_menu();
	    $this->tempate_modules();
	    
	    $file_path	= $this->template_path();
	    
	    $query = "select * from fm_cm_machine_sales_review order by reg_date desc";
	    $query = $this->db->query($query);
	    $rev_list = $query->result_array();
	    
	    $this->template->assign('rev_list', $rev_list);
	    
	    $this->template->define(array('tpl'=>$file_path));
	    $this->template->print_("tpl");
	}
	
	## 후기 등록
	public function rev_regist() {
	    $this->admin_menu();
	    $this->tempate_modules();
	    
	    $file_path	= $this->template_path();
	    
	    $rev_seq = $_GET['seq'];
	    if(!empty($rev_seq)) {
    	    $query = "select * from fm_cm_machine_sales_review where rev_seq = ".$rev_seq;
    	    $query = $this->db->query($query);
    	    $rev = $query->row_array();
    	    $this->template->assign($rev);
	    }
	    if(empty($_GET['reg_mode'])) $_GET['reg_mode'] = 'insert';
	    $this->template->assign('reg_mode', $_GET['reg_mode']);
	    $this->template->assign('seq', $rev_seq);
	    
	    $this->template->define(array('tpl'=>$file_path));
	    $this->template->print_("tpl");
	}
	
	## 후기 등록 처리
	public function rev_regist_process() {
	    $type = $this->input->post('type');
	    $userid = $this->input->post('userid');
	    $target_userid = $this->input->post('target_userid');
	    $grade_01 = $this->input->post('grade_01');
	    $grade_02 = $this->input->post('grade_02');
	    $grade_03 = $this->input->post('grade_03');
	    $grade_04 = $this->input->post('grade_04');
	    $grade_05 = $this->input->post('grade_05');
	    $grade = $this->input->post('grade');
	    $title = $this->input->post('title');
	    $content = $this->input->post('content');
	    $rev_seq = $this->input->post('rev_seq');
	    $reg_mode = $this->input->post('reg_mode');
	    
	    $data = array(
	        'type' => $type,
	        'target_userid' => $target_userid,
	        'userid' => $userid,
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
	    
	    $upload_path = "./data/uploads/review";
	    $filename = 'rev_image';
	    
	    $this->upload->initialize($this->set_upload_options($upload_path));
	    if($this->upload->do_upload($filename)) {
	        $upload_data = $this->upload->data();
	        $data['path'] = str_replace(".", "", $upload_path).'/'.$upload_data['file_name'];
	    } else if($reg_mode == 'insert'){
	        $data['path'] = '/data/uploads/common/no-image.png';
	    }
	    if($reg_mode == 'insert') {
    	    $this->db->insert('fm_cm_machine_sales_review', $data);
    	    
    	    $callback = "parent.location.reload()";
    	    openDialogAlert('등록이 완료되었습니다.',400,140,'parent',$callback);
	    } else if ($reg_mode == 'modify') {
	        $this->db->where('rev_seq', $rev_seq);
	        $this->db->update('fm_cm_machine_sales_review', $data);
	        
	        $callback = "parent.location.reload()";
	        openDialogAlert('수정이 완료되었습니다.',400,140,'parent',$callback);
	    }
	}
	
	## 후기 삭제 처리
	public function rev_delete_process() {
	    $rev_seq = $this->input->post('rev_seq');
	    
	    $this->db->where('rev_seq', $rev_seq);
        $this->db->delete('fm_cm_machine_sales_review');
        
	    $callback = "parent.location.reload()";
	    openDialogAlert('삭제가 완료되었습니다.',400,140,'parent',$callback);
	}
	
	## 평가 관리
	public function eval() {
	    $this->admin_menu();
	    $this->tempate_modules();
	    
	    $file_path	= $this->template_path();
	    
	    $tab_menu = isset($_GET['tab_menu']) ? $_GET['tab_menu'] : '01';
	    
	    if($tab_menu == '01') {
	        $query = "select * from fm_cm_machine_sales_eval order by reg_date desc";
	        $query = $this->db->query($query);
	        $eval_list = $query->result_array();
	    } else if ($tab_menu == '02') {
	        $query = "select * from fm_cm_machine_partner_eval order by reg_date desc";
	        $query = $this->db->query($query);
	        $eval_list = $query->result_array();
	    }
	    $this->template->assign('eval_list', $eval_list);
	    $this->template->assign('tab_menu', $tab_menu);
	    
	    $this->template->define(array('tpl'=>$file_path));
	    $this->template->print_("tpl");
	}
	
	## 평가 수정 처리
	public function eval_modify_process() {
	    $tab_menu = $this->input->post('tab_menu');
	    $seval_seq = $this->input->post('seval_seq');
	    $peval_seq = $this->input->post('peval_seq');
	    $content = $this->input->post('content');
	    $grade = $this->input->post('grade');
	    $grade_01 = $this->input->post('grade_01');
	    $grade_02 = $this->input->post('grade_02');
	    $grade_03 = $this->input->post('grade_03');
	    $grade_04 = $this->input->post('grade_04');
	    $grade_05 = $this->input->post('grade_05');
	    
	    $data = array(
	        'content' => $content,
	        'grade' => $grade,
	        'grade_01' => $grade_01,
	        'grade_02' => $grade_02,
	        'grade_03' => $grade_03,
	        'grade_04' => $grade_04,
	        'grade_05' => $grade_05,
	    );
	    if($tab_menu == '01') {
    	    $this->db->where('seval_seq', $seval_seq);
    	    $this->db->update('fm_cm_machine_sales_eval', $data);
	    } else if($tab_menu == '02') {
            $this->db->where('peval_seq', $peval_seq);
    	    $this->db->update('fm_cm_machine_partner_eval', $data);
	    }
	    $callback = "parent.location.reload()";
	    openDialogAlert('수정이 완료되었습니다.',400,140,'parent',$callback);
	}
	
	## 평가 삭제 처리
	public function eval_delete_process() {
	    $tab_menu = $this->input->post('tab_menu');
	    $seval_seq = $this->input->post('seval_seq');
	    $peval_seq = $this->input->post('peval_seq');
	    
	    if($tab_menu == '01') {
	        $this->db->where('seval_seq', $seval_seq);
	        $this->db->delete('fm_cm_machine_sales_eval');
	    } else if($tab_menu == '02') {
	        $this->db->where('peval_seq', $peval_seq);
	        $this->db->delete('fm_cm_machine_partner_eval');
	    }
	    $callback = "parent.location.reload()";
	    openDialogAlert('삭제가 완료되었습니다.',400,140,'parent',$callback);
	}
	
	## 공식딜러 관리
	public function dealer()
	{
	    $auth = $this->authmodel->manager_limit_act('member_view');
	    if(!$auth){
	        pageBack("관리자 권한이 없습니다.");
	        exit;
	    }
	    $this->load->model('snsmember');
	    $this->load->model('membermodel');
	    $this->load->model('providermodel');
	    $this->admin_menu();
	    $this->tempate_modules();
	    $file_path	= $this->template_path();
	    
	    // 검색조건이 없을 경우 기본 세팅 검색조건을 가져옵니다.
	    if( count($_GET) == 0 ){
	        $this->load->model('searchdefaultconfigmodel');
	        $data_search_default_str = $this->searchdefaultconfigmodel->get_search_default_config('admin/member/catalog');
	        if($data_search_default_str['search_info']){
	            parse_str($data_search_default_str['search_info'], $data_search_default);
	            foreach($data_search_default as $key => $val){
	                if(strstr($key,"default_period")){
	                    $key		= str_replace("default_period_","",$key);
	                    $search_date = $this->searchdefaultconfigmodel->get_search_format_date($val);
	                    
	                    if($key == "anniversary"){
	                        $sdt_tmp = explode("-",$search_date['start_date']);
	                        $edt_tmp = explode("-",$search_date['end_date']);
	                        
	                        $_GET[$key.'_sdate'][] = $sdt_tmp[1];
	                        $_GET[$key.'_sdate'][] = $sdt_tmp[2];
	                        $_GET[$key.'_edate'][] = $edt_tmp[1];
	                        $_GET[$key.'_edate'][] = $edt_tmp[2];
	                    }else{
	                        $_GET[$key.'_sdate'] = $search_date['start_date'];
	                        $_GET[$key.'_edate'] = $search_date['end_date'];
	                    }
	                }else{
	                    $key = str_replace("default_","",$key);
	                    $_GET[$key]		= $val;
	                }
	            }
	        }
	    }
	    
	    $provider	= $this->providermodel->provider_goods_list();
	    $this->template->assign('provider',$provider);
	    
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
	    $auth_member_down	= $this->authmodel->manager_limit_act('member_download');
	    if( !$this->isplusfreenot ){ //무료몰인경우 다운권한 없음
	        $auth_member_down = false;
	    }
	    if(isset($auth_member_down)) $this->template->assign('auth_member_down',$auth_member_down);
	    
	    ###
	    if($_GET['header_search_keyword']) $_GET['keyword'] = $_GET['header_search_keyword'];
	    
	    ### GROUP
	    $group_arr = $this->membermodel->find_group_list();
	    
	    if( !empty($_GET['semoney']))	$_GET['semoney']	= get_cutting_price($_GET['semoney']);
	    if( !empty($_GET['eemoney']))	$_GET['eemoney']	= get_cutting_price($_GET['eemoney']);
	    if( !empty($_GET['spoint']))	$_GET['spoint']		= get_cutting_price($_GET['spoint']);
	    if( !empty($_GET['epoint']))	$_GET['epoint']		= get_cutting_price($_GET['epoint']);
	    if( !empty($_GET['scash']))		$_GET['scash']		= get_cutting_price($_GET['scash']);
	    if( !empty($_GET['ecash']))		$_GET['ecash']		= get_cutting_price($_GET['ecash']);
	    
	    ### SEARCH
	    //print_r($_POST);
	    if( !$_GET['member_seq'] ) unset($_GET['member_seq']);//crm 사용되는 문제로 검색시 값이 없으면 초기화 @2016-07-21 ysm
	    $sc = $this->input->get();
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
	    
	    $sc['dealer'] = 'y';
	    
	    ### MEMBER
	    $data = $this->membermodel->admin_member_list_spout($sc); //프로세스 변경 kmj
	    
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
	        
	        //기업회원 정보 매칭 kmj
	        if($datarow['mtype'] == 'business'){
	            $datarow['type']	= '기업';
	            
	            $query = "select label_value as gubun from fm_member_subinfo where label_title = '회원구분' and member_seq = ".$datarow['member_seq'];
	            $query = $this->db->query($query);
	            $datarow['gubun'] = $query->row()->gubun == '기업회원' ? '기업' : '딜러';
	            
	            $bus_info = $this->db->query("seLECT
						business_seq, bname, bcellphone, bphone
					fROM
						fm_member_business
					wHERE
						member_seq = ? limit 0, 1", $datarow['member_seq'])->result_array();
	            
	            if($bus_info[0]){
	                $datarow['business_seq']	= $bus_info[0]['business_seq'];
	                $datarow['user_name']		= $bus_info[0]['bname'];
	                $datarow['cellphone']		= $bus_info[0]['bcellphone'];
	                $datarow['phone']			= $bus_info[0]['bphone'];
	            } else {
	                $datarow['business_seq']	= '';
	                $datarow['user_name']		= '';
	                $datarow['cellphone']		= '';
	                $datarow['phone']			= '';
	            }
	        } else {
	            $datarow['type']	= '개인';
	            $datarow['gubun'] = '개인';
	        }
	        
	        //그룹 정보 매칭 kmj
	        $group_info = $this->db->query("seLECT
						group_name
					fROM
						fm_member_group
					wHERE
						group_seq = ? limit 0, 1", $datarow['group_seq'])->result_array();
	        if($group_info[0]){
	            $datarow['group_name'] = $group_info[0]['group_name'];
	        } else {
	            $datarow['group_name'] = '';
	        }
	        
	        //유입 정보 매칭 kmj
	        if(!$datarow['referer_domain']){
	            $datarow['referer_name'] = '직접입력';
	        } else {
	            $referer_info = $this->db->query("seLECT
							referer_group_name
						fROM
							fm_referer_group
						wHERE
							referer_group_url = ? limit 0, 1", $datarow['referer_domain'])->result_array();
	            if($referer_info[0]){
	                $datarow['referer_name'] = $referer_info[0]['referer_group_name'];
	            } else {
	                $datarow['referer_name'] = '기타';
	            }
	        }
	        
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
	    $paginlay = pagingtag($sc['searchcount'],$sc['perpage'],$this->membermodel->admin_member_url($file_path).'?', getLinkFilter('',array_keys($sc)) );
	    
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
	    $this->template->assign('sc',$sc);
	    
	    
	    $this->template->assign('query_string',get_query_string());
	    
	    $this->template->define('member_list',$this->skin.'/member/member_list.html');
	    $this->template->define('member_search',$this->skin.'/member/member_search.html');
	    $this->template->define(array('tpl'=>$file_path));
	    $this->template->print_("tpl");
	}
	
	## 공식딜러 승인
	public function dealer_permit() {
	    header("Content-Type: application/json");
	    
	    $member_seq = $this->input->post('member_seq');
	    
	    $data = array(
	        'main_dealer_yn' => 'y'
	    );
	    $this->db->where('member_seq', $member_seq);
	    $this->db->update('fm_member_business', $data);
	}
	
	## 성능검사 관리
    public function perform() {
        $this->admin_menu();
	    $this->tempate_modules();
	    
	    $file_path	= $this->template_path();
	    
        $query = "select * from fm_cm_machine_perform a, fm_cm_machine_sales b, fm_cm_machine_sales_info c where a.info_seq = c.info_seq and b.sales_seq = c.sales_seq order by a.reg_date desc";
        $query = $this->db->query($query);
        $perform_list = $query->result_array();
	   
        foreach($perform_list as &$row) {
            $query = "select * from fm_cm_machine_pay where pay_type = '성능검사' and target_seq = ".$row['info_seq'];
            $query = $this->db->query($query);
            $result = $query->row_array();
            $row['pay_state'] = $result['pay_state'];
        }
	    $this->template->assign('perform_list', $perform_list);
        
	    $this->template->define(array('tpl'=>$file_path));
	    $this->template->print_("tpl");
    }
	
	## 성능검사 업로드
    public function upload_perform() {
        $perform_seq = $this->input->post('perform_seq');    
        
        $this->load->library('upload');
	    
	    $upload_path = "./data/uploads/perform";
	    $filename = 'upload_file';
        
	    $this->upload->initialize($this->set_upload_options($upload_path));
	    if($this->upload->do_upload($filename)) {
	        $upload_data = $this->upload->data();
            $data = array();
	        $data['upload'] = str_replace(".", "", $upload_path).'/'.$upload_data['file_name'];
            
            $this->db->where('perform_seq', $perform_seq);
            $this->db->update('fm_cm_machine_perform', $data);

            $callback = "parent.location.reload()";
            openDialogAlert('업로드 되었습니다.',400,140,'parent',$callback);
	    } else {
            $callback = "parent.location.reload()";
            openDialogAlert('업로드 중 에러가 발생했습니다.',400,140,'parent',$callback);
        }
    }
	
	## 성능검사 업로드 취소
    public function delete_perform() {
        $perform_seq = $this->input->post('perform_seq');    
        
        $data = array();
        $data['upload'] = NULL;

        $this->db->where('perform_seq', $perform_seq);
        $this->db->update('fm_cm_machine_perform', $data);

        $callback = "parent.location.reload()";
        openDialogAlert('업로드 취소되었습니다.',400,140,'parent',$callback);
    }
	
	## 성능검사 보고서
    public function perform_report() {
	    $this->admin_menu();
	    $this->tempate_modules();
	    
	    $file_path	= $this->template_path();
	    
	    $this->template_path = $tpl;
	    $this->template->assign(array("template_path"=>$this->template_path));
	    
	    $perform_seq = $this->input->get('seq');
	    
	    $userData = $this->getUserData();
	    
        $query = "select * from fm_cm_machine_perform where perform_seq = ".$perform_seq;
        $query = $this->db->query($query);
        $result = $query->row_array();
	        
	    $this->template->assign('info', $result);
	    $this->template->assign('is_popup', 'true');
	    
        $this->template->define(array('tpl'=>$file_path));
        $this->template->print_("tpl");
	}
	
	## 이메일 관리
	public function email()
	{
		$auth = $this->authmodel->manager_limit_act('member_view');
		if(!$auth){
			pageBack("관리자 권한이 없습니다.");
			exit;
		}
		$this->load->model('snsmember');
		$this->load->model('membermodel');
		$this->load->model('providermodel');
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		// 검색조건이 없을 경우 기본 세팅 검색조건을 가져옵니다.
		if( count($_GET) == 0 ){
			$this->load->model('searchdefaultconfigmodel');
			$data_search_default_str = $this->searchdefaultconfigmodel->get_search_default_config('admin/member/catalog');
			if($data_search_default_str['search_info']){
				parse_str($data_search_default_str['search_info'], $data_search_default);
				foreach($data_search_default as $key => $val){
					if(strstr($key,"default_period")){
						$key		= str_replace("default_period_","",$key);
						$search_date = $this->searchdefaultconfigmodel->get_search_format_date($val);

						if($key == "anniversary"){
							$sdt_tmp = explode("-",$search_date['start_date']);
							$edt_tmp = explode("-",$search_date['end_date']);

							$_GET[$key.'_sdate'][] = $sdt_tmp[1];
							$_GET[$key.'_sdate'][] = $sdt_tmp[2];
							$_GET[$key.'_edate'][] = $edt_tmp[1];
							$_GET[$key.'_edate'][] = $edt_tmp[2];
						}else{
							$_GET[$key.'_sdate'] = $search_date['start_date'];
							$_GET[$key.'_edate'] = $search_date['end_date'];
						}
					}else{
						$key = str_replace("default_","",$key);
						$_GET[$key]		= $val;
					}
				}
			}
		}

		$provider	= $this->providermodel->provider_goods_list();
		$this->template->assign('provider',$provider);

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
		$auth_member_down	= $this->authmodel->manager_limit_act('member_download');
		if( !$this->isplusfreenot ){ //무료몰인경우 다운권한 없음
			$auth_member_down = false;
		}
		if(isset($auth_member_down)) $this->template->assign('auth_member_down',$auth_member_down);

		###
		if($_GET['header_search_keyword']) $_GET['keyword'] = $_GET['header_search_keyword'];

		### GROUP
		$group_arr = $this->membermodel->find_group_list();

		if( !empty($_GET['semoney']))	$_GET['semoney']	= get_cutting_price($_GET['semoney']);
		if( !empty($_GET['eemoney']))	$_GET['eemoney']	= get_cutting_price($_GET['eemoney']);
		if( !empty($_GET['spoint']))	$_GET['spoint']		= get_cutting_price($_GET['spoint']);
		if( !empty($_GET['epoint']))	$_GET['epoint']		= get_cutting_price($_GET['epoint']);
		if( !empty($_GET['scash']))		$_GET['scash']		= get_cutting_price($_GET['scash']);
		if( !empty($_GET['ecash']))		$_GET['ecash']		= get_cutting_price($_GET['ecash']);

		### SEARCH
		//print_r($_POST);
		if( !$_GET['member_seq'] ) unset($_GET['member_seq']);//crm 사용되는 문제로 검색시 값이 없으면 초기화 @2016-07-21 ysm
		$sc = $this->input->get();
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

		$sc['page'] = '0';
		$sc['perpage'] = '1000000000000';
		
		### MEMBER
		$data = $this->membermodel->admin_member_list_spout($sc); //프로세스 변경 kmj

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

			//기업회원 정보 매칭 kmj
			if($datarow['mtype'] == 'business'){
				$datarow['type']	= '기업';
                
				$query = "select label_value as gubun from fm_member_subinfo where label_title = '회원구분' and member_seq = ".$datarow['member_seq'];
				$query = $this->db->query($query);
				$datarow['gubun'] = $query->row()->gubun == '기업회원' ? '기업' : '딜러';

				$bus_info = $this->db->query("seLECT 
						business_seq, bname, bcellphone, bphone 
					fROM
						fm_member_business
					wHERE
						member_seq = ? limit 0, 1", $datarow['member_seq'])->result_array();

				if($bus_info[0]){
					$datarow['business_seq']	= $bus_info[0]['business_seq'];
					$datarow['user_name']		= $bus_info[0]['bname'];
					$datarow['cellphone']		= $bus_info[0]['bcellphone'];
					$datarow['phone']			= $bus_info[0]['bphone'];
				} else {
					$datarow['business_seq']	= '';
					$datarow['user_name']		= '';
					$datarow['cellphone']		= '';
					$datarow['phone']			= '';
				}
			} else {
				$datarow['type']	= '개인';
				$datarow['gubun'] = '개인';
			}

			//그룹 정보 매칭 kmj
			$group_info = $this->db->query("seLECT 
						group_name
					fROM
						fm_member_group
					wHERE
						group_seq = ? limit 0, 1", $datarow['group_seq'])->result_array();
			if($group_info[0]){
				$datarow['group_name'] = $group_info[0]['group_name'];
			} else {
				$datarow['group_name'] = '';
			}

			//유입 정보 매칭 kmj
			if(!$datarow['referer_domain']){
				$datarow['referer_name'] = '직접입력';
			} else {
				$referer_info = $this->db->query("seLECT 
							referer_group_name
						fROM
							fm_referer_group
						wHERE
							referer_group_url = ? limit 0, 1", $datarow['referer_domain'])->result_array();
				if($referer_info[0]){
					$datarow['referer_name'] = $referer_info[0]['referer_group_name'];
				} else {
					$datarow['referer_name'] = '기타';
				}
			}

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
		$paginlay = pagingtag($sc['searchcount'],$sc['perpage'],$this->membermodel->admin_member_url($file_path).'?', getLinkFilter('',array_keys($sc)) );

		if(empty($paginlay))$paginlay = '<p><a class="on red">1</a><p>';

		//가입환경
		$sitetypeloop = sitetype($_GET['sitetype'], 'image', 'array');
		$this->template->assign('sitetypeloop',$sitetypeloop);

		//가입양식
		$ruteloop = memberrute($_GET['rute'], 'image', 'array');
		$this->template->assign('ruteloop',$ruteloop);

		$this->template->assign('page', 'email');
		$this->template->assign('pagin',$paginlay);
		$this->template->assign('group_arr',$group_arr);
		$this->template->assign('perpage',$sc['perpage']);
		$this->template->assign('sc',$sc);


		$this->template->assign('query_string',get_query_string());

		// 보안키 입력창
		$member_download_info = $this->skin.'/member/member_download_info.html';
		$this->template->define(array("member_download_info"=>$member_download_info));
		
		$this->template->define('member_list',$this->skin.'/member/member_list.html');
		$this->template->define('member_search',$this->skin.'/member/member_search.html');
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}
	
	## 머신박스존 메일 발송
	public function send_machinezone_mail() {
        header("Content-Type: application/json");
	    
	    $member_list = $this->input->post('member_list'); 
		$member_list = explode(',', $member_list);
		
		$result = false;
		$email_array = array();
		foreach($member_list as $row) {
			$member = $this->membermodel->get_member_data($row);
			if(!in_array($member['email'], $email_array)) {
				$email_array[] = $member['email'];
			}
		}
		foreach($email_array as $row) {
			send_machinezone_mail($row);
		}
		$result = true;
        echo json_encode(array('result' => $result));
    }
	
	private function send_common_mail($email, $title, $message) {
	    if($email) {
	        send_common_mail($email, $title, $message);
	    }
	}
	
	private function send_common_sms($phone, $message) {
	    if($phone) {
	        //send_common_sms($phone, $message);
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
	    $config['allowed_types'] = 'gif|jpg|png';
	    $config['max_size'] = '0';
	    $config['max_width'] = '0';
	    $config['max_height'] = '0';
	    $config['overwrite'] = FALSE;
	    
	    return $config;
	}
}