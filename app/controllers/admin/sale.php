<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class sale extends admin_base {

	public function __construct() {
		parent::__construct();
		$this->load->model('membermodel');
		$this->load->helper(array('form', 'url', 'mail', 'sms'));
	}

	public function index()
	{
		redirect("/admin/sale/self_status");
	}

	## 셀프판매 현황
	public function self_status() {
	    $this->admin_menu();
	    $this->tempate_modules();
	    
	    $tab_menu = '01';
	    if(isset($_GET['tab_menu'])) {
	        $tab_menu = $_GET['tab_menu'];
	    }
	    
	    // 상품검색폼
	    $this->template->define(array('goods_search_form' => $this->skin.'/goods/goods_search_form.html'));
	    
	    // 기본검색설정폼 분리 2015-05-04
	    $this->template->define(array('set_search_default' => $this->skin.'/goods/_set_search_default_goods.html'));
	    $this->template->assign(array('search_page'=>uri_string()));
	    $file_path	= $this->template_path();
	    
	    $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_kind c,".
	   	    "fm_cm_machine_manufacturer d, fm_cm_machine_model e, fm_cm_machine_area f, fm_cm_machine_sales_detail g ".
	   	    "where a.sales_seq = b.sales_seq and b.kind_seq = c.kind_seq and b.mnf_seq = d.mnf_seq ".
	   	    "and b.model_seq = e.model_seq and b.area_seq = f.area_seq and b.info_seq = g.info_seq and type = 'self' and method = '고정가격판매' and sales_yn = 'n' and b.state != '등록취소' ".
	   	    "order by sales_date desc";
	    $query = $this->db->query($query);
	    $self_list_01 = $query->result_array();
	    
	    foreach($self_list_01 as &$row) {
	        $query2 = "select * from fm_cm_machine_sales_picture where info_seq = ".$row['info_seq']." ".
	   	        "order by sort asc";
	        $query2 = $this->db->query($query2);
	        $result = $query2->result_array();
	        $row['picture_list'] = $result;
	        
	        $query2 = "select * from fm_cm_machine_sales_check where sales_seq = ".$row['sales_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['check_list'] = $result;
	        
	        $query2 = "select * from fm_cm_machine_sales_advertise where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->result_array();
	        $row['ad_list'] = $result;
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_visit where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['visit_cnt'] = $result['cnt'];
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_proposal where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['proposal_cnt'] = $result['cnt'];
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_imdbuy where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['imdbuy_cnt'] = $result['cnt'];
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_selling where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['selling_cnt'] = $result['cnt'];
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_question where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['qna_cnt'] = $result['cnt'];
	    }
	    
	    $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_kind c,".
	   	    "fm_cm_machine_manufacturer d, fm_cm_machine_model e, fm_cm_machine_area f, fm_cm_machine_sales_detail g ".
	   	    "where a.sales_seq = b.sales_seq and b.kind_seq = c.kind_seq and b.mnf_seq = d.mnf_seq ".
	   	    "and b.model_seq = e.model_seq and b.area_seq = f.area_seq and b.info_seq = g.info_seq and type = 'self' and method = '입찰' and sales_yn = 'n' and b.state != '등록취소' ".
	   	    "order by sales_date desc";
	    $query = $this->db->query($query);
	    $self_list_02 = $query->result_array();
	    
	    foreach($self_list_02 as &$row) {
	        $query2 = "select * from fm_cm_machine_sales_picture where info_seq = ".$row['info_seq']." ".
	   	        "order by sort asc";
	        $query2 = $this->db->query($query2);
	        $result = $query2->result_array();
	        $row['picture_list'] = $result;
	        
	        if($row['bid_yn'] == 'n')
	           $row['bid_state'] = '진행중';
           else {
               $query2 = "select * from fm_cm_machine_bid where info_seq = ".$row['info_seq'];
               $query2 = $this->db->query($query2);
               $result = $query2->result_array();
               
               if(empty($result)) 
                   $row['bid_state'] = '유찰';
               else
                   $row['bid_state'] = '낙찰';
           }
        
	        $query2 = "select * from fm_cm_machine_sales_check where sales_seq = ".$row['sales_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['check_list'] = $result;
	        
	        $query2 = "select * from fm_cm_machine_sales_advertise where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->result_array();
	        $row['ad_list'] = $result;
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_visit where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['visit_cnt'] = $result['cnt'];
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_proposal where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['proposal_cnt'] = $result['cnt'];
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_imdbuy where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['imdbuy_cnt'] = $result['cnt'];
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_sales_info a, fm_cm_machine_sales_detail b, fm_cm_machine_bid c where a.info_seq = b.info_seq and a.info_seq = c.info_seq and b.bid_yn = 'n' and a.state != '등록취소' and a.info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['bid_cnt_01'] = $result['cnt'];
	        $query2 = "select count(*) as cnt from fm_cm_machine_sales_info a, fm_cm_machine_sales_detail b, fm_cm_machine_bid c where a.info_seq = b.info_seq and a.info_seq = c.info_seq and b.bid_yn = 'y' and a.state != '등록취소' and c.bid_yn = 'x' and a.info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['bid_cnt_02'] = $result['cnt'];
	        $query2 = "select count(*) as cnt from fm_cm_machine_sales_info a, fm_cm_machine_sales_detail b, fm_cm_machine_bid c where a.info_seq = b.info_seq and a.info_seq = c.info_seq and b.bid_yn = 'y' and a.state != '등록취소' and c.bid_yn = 'y' and a.info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['bid_cnt_03'] = $result['cnt'];
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_question where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['qna_cnt'] = $result['cnt'];
	    }
	    
	    $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_kind c,".
	   	    "fm_cm_machine_manufacturer d, fm_cm_machine_model e, fm_cm_machine_area f, fm_cm_machine_sales_detail g ".
	   	    "where a.sales_seq = b.sales_seq and b.kind_seq = c.kind_seq and b.mnf_seq = d.mnf_seq ".
	   	    "and b.model_seq = e.model_seq and b.area_seq = f.area_seq and b.info_seq = g.info_seq and type = 'self' and method = '고정가격판매' and sales_yn = 'y' and b.state != '등록취소' ".
	   	    "order by sales_date desc";
	    $query = $this->db->query($query);
	    $self_finish_list_01 = $query->result_array();
	    
	    foreach($self_finish_list_01 as &$row) {
	        $query2 = "select * from fm_cm_machine_sales_picture where info_seq = ".$row['info_seq']." ".
	   	        "order by sort asc";
	        $query2 = $this->db->query($query2);
	        $result = $query2->result_array();
	        $row['picture_list'] = $result;
	        
	        $query2 = "select * from fm_cm_machine_sales_check where sales_seq = ".$row['sales_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['check_list'] = $result;
	        
	        $query2 = "select * from fm_cm_machine_sales_advertise where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->result_array();
	        $row['ad_list'] = $result;
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_visit where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['visit_cnt'] = $result['cnt'];
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_proposal where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['proposal_cnt'] = $result['cnt'];
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_imdbuy where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['imdbuy_cnt'] = $result['cnt'];
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_selling where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['selling_cnt'] = $result['cnt'];
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_question where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['qna_cnt'] = $result['cnt'];
	    }
	    
	    $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_kind c,".
	   	    "fm_cm_machine_manufacturer d, fm_cm_machine_model e, fm_cm_machine_area f, fm_cm_machine_sales_detail g ".
	   	    "where a.sales_seq = b.sales_seq and b.kind_seq = c.kind_seq and b.mnf_seq = d.mnf_seq ".
	   	    "and b.model_seq = e.model_seq and b.area_seq = f.area_seq and b.info_seq = g.info_seq and type = 'self' and method = '입찰' and sales_yn = 'y' and b.state != '등록취소' ".
	   	    "order by sales_date desc";
	    $query = $this->db->query($query);
	    $self_finish_list_02 = $query->result_array();
	    
	    foreach($self_finish_list_02 as &$row) {
	        $query2 = "select * from fm_cm_machine_sales_picture where info_seq = ".$row['info_seq']." ".
	   	        "order by sort asc";
	        $query2 = $this->db->query($query2);
	        $result = $query2->result_array();
	        $row['picture_list'] = $result;
	        
	        if($row['bid_yn'] == 'n')
	            $row['bid_state'] = '진행중';
	            else {
	                $query2 = "select * from fm_cm_machine_bid where info_seq = ".$row['info_seq'];
	                $query2 = $this->db->query($query2);
	                $result = $query2->result_array();
	                
	                if(empty($result))
	                    $row['bid_state'] = '유찰';
	                    else
	                        $row['bid_state'] = '낙찰';
	            }
	            
	            $query2 = "select * from fm_cm_machine_sales_check where sales_seq = ".$row['sales_seq'];
	            $query2 = $this->db->query($query2);
	            $result = $query2->row_array();
	            $row['check_list'] = $result;
	            
	            $query2 = "select * from fm_cm_machine_sales_advertise where info_seq = ".$row['info_seq'];
	            $query2 = $this->db->query($query2);
	            $result = $query2->result_array();
	            $row['ad_list'] = $result;
	            
	            $query2 = "select count(*) as cnt from fm_cm_machine_visit where info_seq = ".$row['info_seq'];
	            $query2 = $this->db->query($query2);
	            $result = $query2->row_array();
	            $row['visit_cnt'] = $result['cnt'];
	            
	            $query2 = "select count(*) as cnt from fm_cm_machine_proposal where info_seq = ".$row['info_seq'];
	            $query2 = $this->db->query($query2);
	            $result = $query2->row_array();
	            $row['proposal_cnt'] = $result['cnt'];
	            
	            $query2 = "select count(*) as cnt from fm_cm_machine_imdbuy where info_seq = ".$row['info_seq'];
	            $query2 = $this->db->query($query2);
	            $result = $query2->row_array();
	            $row['imdbuy_cnt'] = $result['cnt'];
	            
	            $query2 = "select count(*) as cnt from fm_cm_machine_sales_info a, fm_cm_machine_sales_detail b, fm_cm_machine_bid c where a.info_seq = b.info_seq and a.info_seq = c.info_seq and b.bid_yn = 'n' and a.state != '등록취소' and a.info_seq = ".$row['info_seq'];
	            $query2 = $this->db->query($query2);
	            $result = $query2->row_array();
	            $row['bid_cnt_01'] = $result['cnt'];
	            $query2 = "select count(*) as cnt from fm_cm_machine_sales_info a, fm_cm_machine_sales_detail b, fm_cm_machine_bid c where a.info_seq = b.info_seq and a.info_seq = c.info_seq and b.bid_yn = 'y' and a.state != '등록취소' and c.bid_yn = 'x' and a.info_seq = ".$row['info_seq'];
	            $query2 = $this->db->query($query2);
	            $result = $query2->row_array();
	            $row['bid_cnt_02'] = $result['cnt'];
	            $query2 = "select count(*) as cnt from fm_cm_machine_sales_info a, fm_cm_machine_sales_detail b, fm_cm_machine_bid c where a.info_seq = b.info_seq and a.info_seq = c.info_seq and b.bid_yn = 'y' and a.state != '등록취소' and c.bid_yn = 'y' and a.info_seq = ".$row['info_seq'];
	            $query2 = $this->db->query($query2);
	            $result = $query2->row_array();
	            $row['bid_cnt_03'] = $result['cnt'];
	            
	            $query2 = "select count(*) as cnt from fm_cm_machine_question where info_seq = ".$row['info_seq'];
	            $query2 = $this->db->query($query2);
	            $result = $query2->row_array();
	            $row['qna_cnt'] = $result['cnt'];
	    }
	    
	    $this->template->assign('self_list_01', $self_list_01);
	    $this->template->assign('self_list_02', $self_list_02);
	    $this->template->assign('self_finish_list_01', $self_finish_list_01);
	    $this->template->assign('self_finish_list_02', $self_finish_list_02);
	    
	    $this->template->assign('tab_menu', $tab_menu);
	    
	    $this->template->define(array('tpl'=>$file_path));
	    $this->template->print_("tpl");
	}
	
	## 긴급판매 현황
	public function emergency_status() {
	    $this->admin_menu();
	    $this->tempate_modules();
	    
	    // 상품검색폼
	    $this->template->define(array('goods_search_form' => $this->skin.'/goods/goods_search_form.html'));
	    
	    // 기본검색설정폼 분리 2015-05-04
	    $this->template->define(array('set_search_default' => $this->skin.'/goods/_set_search_default_goods.html'));
	    $this->template->assign(array('search_page'=>uri_string()));
	    $file_path	= $this->template_path();
	    
	    $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_kind c,".
	   	    "fm_cm_machine_manufacturer d, fm_cm_machine_model e, fm_cm_machine_area f ".
	   	    "where a.sales_seq = b.sales_seq and b.kind_seq = c.kind_seq and b.mnf_seq = d.mnf_seq ".
	   	    "and b.model_seq = e.model_seq and b.area_seq = f.area_seq and type = 'emergency' and sales_yn = 'n' and b.state != '등록취소' ".
	   	    "order by sales_date desc, b.info_seq asc";
	    $query = $this->db->query($query);
	    $sale_list = $query->result_array();
	    
	    foreach($sale_list as &$row) {
	        $query2 = "select * from fm_cm_machine_sales_picture where info_seq = ".$row['info_seq']." ".
	   	        "order by sort asc";
	        $query2 = $this->db->query($query2);
	        $result = $query2->result_array();
	        $row['picture_list'] = $result;
	        
	        $query2 = "select * from fm_cm_machine_sales_check where sales_seq = ".$row['sales_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['check_list'] = $result;
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_visit where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['visit_cnt'] = $result['cnt'];
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_proposal where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['proposal_cnt'] = $result['cnt'];
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_imdbuy where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['imdbuy_cnt'] = $result['cnt'];
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_selling where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['selling_cnt'] = $result['cnt'];
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_question where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['qna_cnt'] = $result['cnt'];
	    }
	    
	    $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_kind c,".
	   	    "fm_cm_machine_manufacturer d, fm_cm_machine_model e, fm_cm_machine_area f ".
	   	    "where a.sales_seq = b.sales_seq and b.kind_seq = c.kind_seq and b.mnf_seq = d.mnf_seq ".
	   	    "and b.model_seq = e.model_seq and b.area_seq = f.area_seq and type = 'emergency' and sales_yn = 'y' and b.state != '등록취소' ".
	   	    "order by sales_date desc, b.info_seq asc";
	    $query = $this->db->query($query);
	    $sale_finish_list = $query->result_array();
	    
	    foreach($sale_finish_list as &$row) {
	        $query2 = "select * from fm_cm_machine_sales_picture where info_seq = ".$row['info_seq']." ".
	   	        "order by sort asc";
	        $query2 = $this->db->query($query2);
	        $result = $query2->result_array();
	        $row['picture_list'] = $result;
	        
	        $query2 = "select * from fm_cm_machine_sales_check where sales_seq = ".$row['sales_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['check_list'] = $result;
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_visit where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['visit_cnt'] = $result['cnt'];
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_proposal where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['proposal_cnt'] = $result['cnt'];
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_imdbuy where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['imdbuy_cnt'] = $result['cnt'];
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_selling where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['selling_cnt'] = $result['cnt'];
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_question where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['qna_cnt'] = $result['cnt'];
	    }
	    
	    $this->template->assign('sale_list', $sale_list);
	    $this->template->assign('sale_finish_list', $sale_finish_list);
	    
	    $this->template->define(array('tpl'=>$file_path));
	    $this->template->print_("tpl");
	}
	
	## 머박다이렉트 현황
	public function direct_status() {
	    $this->admin_menu();
	    $this->tempate_modules();
	    
	    // 상품검색폼
	    $this->template->define(array('goods_search_form' => $this->skin.'/goods/goods_search_form.html'));
	    
	    // 기본검색설정폼 분리 2015-05-04
	    $this->template->define(array('set_search_default' => $this->skin.'/goods/_set_search_default_goods.html'));
	    $this->template->assign(array('search_page'=>uri_string()));
	    $file_path	= $this->template_path();
	    
	    $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_kind c,".
	   	    "fm_cm_machine_manufacturer d, fm_cm_machine_model e, fm_cm_machine_area f ".
	   	    "where a.sales_seq = b.sales_seq and b.kind_seq = c.kind_seq and b.mnf_seq = d.mnf_seq ".
	   	    "and b.model_seq = e.model_seq and b.area_seq = f.area_seq and type = 'direct' and sales_yn = 'n' and b.state != '등록취소' ".
	   	    "order by sales_date desc, b.info_seq asc";
	    $query = $this->db->query($query);
	    $sale_list = $query->result_array();
	    
	    foreach($sale_list as &$row) {
	        $query2 = "select * from fm_cm_machine_sales_picture where info_seq = ".$row['info_seq']." ".
	   	        "order by sort asc";
	        $query2 = $this->db->query($query2);
	        $result = $query2->result_array();
	        $row['picture_list'] = $result;
	        
	        $query2 = "select * from fm_cm_machine_sales_check where sales_seq = ".$row['sales_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['check_list'] = $result;
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_visit where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['visit_cnt'] = $result['cnt'];
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_proposal where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['proposal_cnt'] = $result['cnt'];
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_imdbuy where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['imdbuy_cnt'] = $result['cnt'];
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_selling where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['selling_cnt'] = $result['cnt'];
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_question where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['qna_cnt'] = $result['cnt'];
	    }
	    
	    $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_kind c,".
	   	    "fm_cm_machine_manufacturer d, fm_cm_machine_model e, fm_cm_machine_area f ".
	   	    "where a.sales_seq = b.sales_seq and b.kind_seq = c.kind_seq and b.mnf_seq = d.mnf_seq ".
	   	    "and b.model_seq = e.model_seq and b.area_seq = f.area_seq and type = 'direct' and sales_yn = 'y' and b.state != '등록취소' ".
	   	    "order by sales_date desc, b.info_seq asc";
	    $query = $this->db->query($query);
	    $sale_finish_list = $query->result_array();
	    
	    foreach($sale_finish_list as &$row) {
	        $query2 = "select * from fm_cm_machine_sales_picture where info_seq = ".$row['info_seq']." ".
	   	        "order by sort asc";
	        $query2 = $this->db->query($query2);
	        $result = $query2->result_array();
	        $row['picture_list'] = $result;
	        
	        $query2 = "select * from fm_cm_machine_sales_check where sales_seq = ".$row['sales_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['check_list'] = $result;
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_visit where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['visit_cnt'] = $result['cnt'];
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_proposal where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['proposal_cnt'] = $result['cnt'];
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_imdbuy where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['imdbuy_cnt'] = $result['cnt'];
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_selling where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['selling_cnt'] = $result['cnt'];
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_question where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['qna_cnt'] = $result['cnt'];
	    }
	    
	    $this->template->assign('sale_list', $sale_list);
	    $this->template->assign('sale_finish_list', $sale_finish_list);
	    
	    $this->template->define(array('tpl'=>$file_path));
	    $this->template->print_("tpl");
	}
	
	## 턴키매각 현황
	public function turnkey_status() {
	    $this->admin_menu();
	    $this->tempate_modules();
	    
	    // 상품검색폼
	    $this->template->define(array('goods_search_form' => $this->skin.'/goods/goods_search_form.html'));
	    
	    // 기본검색설정폼 분리 2015-05-04
	    $this->template->define(array('set_search_default' => $this->skin.'/goods/_set_search_default_goods.html'));
	    $this->template->assign(array('search_page'=>uri_string()));
	    $file_path	= $this->template_path();
	    
	    $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_kind c,".
	   	    "fm_cm_machine_manufacturer d, fm_cm_machine_model e, fm_cm_machine_area f, fm_cm_machine_sales_turnkey g ".
	   	    "where a.sales_seq = b.sales_seq and b.kind_seq = c.kind_seq and b.mnf_seq = d.mnf_seq ".
	   	    "and b.model_seq = e.model_seq and b.area_seq = f.area_seq and a.sales_seq = g.sales_seq and type = 'turnkey' and sales_yn = 'n' and b.state != '등록취소' ".
	   	    "order by sales_date desc, b.info_seq asc";
	    $query = $this->db->query($query);
	    $sale_list = $query->result_array();
	    
	    foreach($sale_list as &$row) {
	        $query2 = "select * from fm_cm_machine_sales_check where sales_seq = ".$row['sales_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['check_list'] = $result;
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_visit where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['visit_cnt'] = $result['cnt'];
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_proposal where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['proposal_cnt'] = $result['cnt'];
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_imdbuy where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['imdbuy_cnt'] = $result['cnt'];
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_selling where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['selling_cnt'] = $result['cnt'];
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_question where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['qna_cnt'] = $result['cnt'];
	    }
	    
	    $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_kind c,".
	   	    "fm_cm_machine_manufacturer d, fm_cm_machine_model e, fm_cm_machine_area f, fm_cm_machine_sales_turnkey g ".
	   	    "where a.sales_seq = b.sales_seq and b.kind_seq = c.kind_seq and b.mnf_seq = d.mnf_seq ".
	   	    "and b.model_seq = e.model_seq and b.area_seq = f.area_seq and type = 'turnkey' and sales_yn = 'y' and b.state != '등록취소' ".
	   	    "order by sales_date desc, b.info_seq asc";
	    $query = $this->db->query($query);
	    $sale_finish_list = $query->result_array();
	    
	    foreach($sale_finish_list as &$row) {
	        $query2 = "select * from fm_cm_machine_sales_check where sales_seq = ".$row['sales_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['check_list'] = $result;
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_visit where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['visit_cnt'] = $result['cnt'];
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_proposal where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['proposal_cnt'] = $result['cnt'];
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_imdbuy where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['imdbuy_cnt'] = $result['cnt'];
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_selling where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['selling_cnt'] = $result['cnt'];
	        
	        $query2 = "select count(*) as cnt from fm_cm_machine_question where info_seq = ".$row['info_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['qna_cnt'] = $result['cnt'];
	    }
	    
	    $this->template->assign('sale_list', $sale_list);
	    $this->template->assign('sale_finish_list', $sale_finish_list);
	    
	    $this->template->define(array('tpl'=>$file_path));
	    $this->template->print_("tpl");
	}
	
	## 판매 승인
	public function sale_permit()
	{
		$this->admin_menu();
		$this->tempate_modules();

		// 상품검색폼
		$this->template->define(array('goods_search_form' => $this->skin.'/goods/goods_search_form.html'));

		// 기본검색설정폼 분리 2015-05-04
		$this->template->define(array('set_search_default' => $this->skin.'/goods/_set_search_default_goods.html'));
		$this->template->assign(array('search_page'=>uri_string()));
		$file_path	= $this->template_path();

		$query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_kind c,".
		         "fm_cm_machine_manufacturer d, fm_cm_machine_model e, fm_cm_machine_area f ".
		         "where a.sales_seq = b.sales_seq and b.kind_seq = c.kind_seq and b.mnf_seq = d.mnf_seq ".
		         "and b.model_seq = e.model_seq and b.area_seq = f.area_seq and type in('emergency', 'direct') and b.state != '등록취소' ".
		         "order by state asc, type asc, sales_date desc";
	
		$query = $this->db->query($query);
		$sale_list = $query->result_array();
	
		foreach($sale_list as &$row) {
		    $query2 = "select * from fm_cm_machine_sales_picture where info_seq = ".$row['info_seq']." ".
		              "order by sort asc";
		    $query2 = $this->db->query($query2);
		    $result = $query2->result_array();
		    $row['picture_list'] = $result;
		    
		    $query2 = "select * from fm_cm_machine_sales_check where sales_seq = ".$row['sales_seq'];
		    $query2 = $this->db->query($query2);
		    $result = $query2->row_array();
		    $row['check_list'] = $result;
		}
		$this->template->assign('sale_list', $sale_list);
		
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}
	
	## 판매 승인 처리
	public function sale_permit_process() {
	    $info_seq = $this->input->post('info_seq');
	    $state = $this->input->post('state');
	    $message = $this->input->post('message');
	    $type = $this->input->post('type');
	    $real_price = $this->input->post('real_price');
	    
	    $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b where a.sales_seq = b.sales_seq and b.info_seq = ".$info_seq;
	    $query = $this->db->query($query);
	    $result = $query->row_array();
	    
	    $data = array(
	        'state' => $state
	    );
	    if($state == '승인')
	        $data['state_date'] = date('Y-m-d H:i:s');
	    $this->db->where('info_seq', $info_seq);
	    $this->db->update('fm_cm_machine_sales_info', $data);
	    
	    if($type == 'emergency' || $type == 'direct') {
	        if($state == '승인') {
	            $data = array(
	                'real_price' => $real_price,
	                'sort_price' => $real_price
	            );
	            $this->db->where('info_seq', $info_seq);
	            $this->db->update('fm_cm_machine_sales_info', $data);
	        } else {
	            $data = array(
	                'real_price' => '',
	                'sort_price' => ''
	            );
	            $this->db->where('info_seq', $info_seq);
	            $this->db->update('fm_cm_machine_sales_info', $data);
	        }
	    }
	    
	    if($state == '승인') {
	        $html = '승인';
	    } else if($state == '미승인' || $state == '보류') {
	        $html = '<span style=\"color:#FF4848;\">'.$state.'</span>';
	    }
	    $userData = $this->getUserData($result['userid']);
	    $this->send_email($state, $userData['email'], $message);
	    $this->send_sms($state, $userData['cellphone'], $message);

	    //$callback = "parent.document.getElementById('item-state-".$info_seq."').innerHTML = '".$html."';";
	    $callback = "parent.location.reload()";
	    openDialogAlert('[' . $state . '] 상태로 변경이 완료되었습니다.',400,140,'parent',$callback);
	}

	## 판매 등록
	public function sale_regist() {
	    $this->admin_menu();
	    $this->tempate_modules();
	    
	    $file_path	= $this->template_path();
	    
	    /*
	    $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_kind c,".
	   	    "fm_cm_machine_manufacturer d, fm_cm_machine_model e, fm_cm_machine_area f ".
	   	    "where a.sales_seq = b.sales_seq and b.kind_seq = c.kind_seq and b.mnf_seq = d.mnf_seq ".
	   	    "and b.model_seq = e.model_seq and b.area_seq = f.area_seq and type in('emergency', 'direct') and state = '승인' ".
	   	    "order by type asc, sales_date desc";
	    
	    $query = $this->db->query($query);
	    $sale_list = $query->result_array();
	    
	    foreach($sale_list as &$row) {
	        $query2 = "select * from fm_cm_machine_sales_picture where info_seq = ".$row['info_seq']." ".
	   	        "order by sort asc";
	        $query2 = $this->db->query($query2);
	        $result = $query2->result_array();
	        $row['picture_list'] = $result;
	    }
	    
	    $this->template->assign('sale_list', $sale_list);
	    */
	    
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
	    
	    $query = $this->db->get('fm_cm_machine_model');
	    $result = $query->result_array();
	    $this->template->assign('model_list', $result);
	    
	    $query = $this->db->get('fm_cm_machine_area');
	    $result = $query->result_array();
	    $this->template->assign('area_list', $result);
	    
	    if(empty($_GET['reg_mode'])) $_GET['reg_mode'] = 'insert';
	    $this->template->assign('type', $_GET['type']);
	    $this->template->assign('reg_mode', $_GET['reg_mode']);
	    $this->template->assign('seq', $_GET['seq']);
	    
	    $this->template->define(array('tpl'=>$file_path));
	    $this->template->print_("tpl");
	}
	
	## 기계정보 조회
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
	
	## 판매 등록 처리
	public function sale_regist_process() {
	    $sales_seq = $this->input->post('sales_seq');
	    $info_seq = $this->input->post('info_seq');
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
	    $deliver_condition = $this->input->post('deliver_condition');
	    $option_arr = $this->input->post('option_arr');
	    $hope_price = $this->input->post('hope_price');
	    $real_price = $this->input->post('real_price');
	    $method = $this->input->post('method');
	    $part_arr = $this->input->post('part');
	    $sort_arr = $this->input->post('sort');
	    $reg_mode = $this->input->post('reg_mode');
	    $type = $this->input->post('type');
	    $userid = $this->input->post('userid');
	    
	    $turnkey_seq = $this->input->post('turnkey_seq');
	    $factory = $this->input->post('factory');
	    $production = $this->input->post('production');
	    $quantity = $this->input->post('quantity');
	    $last_date = $this->input->post('last_date');
	    $creditor = $this->input->post('creditor');
	    $expect_date = $this->input->post('expect_date');
	    $pur_price = $this->input->post('pur_price');
	    $remark = $this->input->post('remark');
	    
	    if($input_mnf == 'true' && isset($txt_mnf)) {
	       $query = "select * from fm_cm_machine_kind where kind_seq = ".$kind_seq;
	       $query = $this->db->query($query);
	       $kind = $query->row_array();
	       $data = array(
	           'mnf_name' => $txt_mnf,
	           'mnf_kind' => $kind['kind_name']
	       );
	       $this->db->insert('fm_cm_machine_manufacturer', $data);
	       $mnf_seq = $this->db->insert_id();
	    }
	    if($input_model == 'true' && isset($txt_model)) {
	        $query = "select * from fm_cm_machine_kind where kind_seq = ".$kind_seq;
	        $query = $this->db->query($query);
	        $kind = $query->row_array();
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
	    $this->load->library('upload');
	    $files = $_FILES;
	    if($type == 'self') {
	        $sales_data = array(
	            'userid' => $userid
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
	            'controller' => $controller
	        );
	        
	        $detail_data = array(
	            'method' => $method,
	        );
	        $update = array();
	        if($method == "고정가격판매") {
	            $fixed_price = $this->input->post('fixed_price');
	            $price_proposal = $this->input->post('price_proposal');
	            
	            $detail_data['method'] = $method;
	            $detail_data['fixed_price'] = $fixed_price;
	            $detail_data['price_proposal'] = $price_proposal;
	            $detail_data['bid_duration'] = NULL;
	            $detail_data['bid_start_price'] = NULL;
	            $detail_data['bid_current_price'] = NULL;
	            $detail_data['bid_price'] = NULL;
	            $detail_data['reduction_rate'] = NULL;
	            $detail_data['repeat_no'] = NULL;
	            $update['sort_price'] = $fixed_price;
	        } else if ($method == "입찰") {
	            $bid_duration = $this->input->post('bid_duration');
	            $bid_start_price = $this->input->post('bid_start_price');
	            $bid_price = $this->input->post('bid_price');
	            $reduction_rate = $this->input->post('reduction_rate');
	            $repeat_no = $this->input->post('repeat_no');
	            
	            $detail_data['method'] = $method;
	            $detail_data['bid_duration'] = $bid_duration;
	            $detail_data['bid_start_price'] = $bid_start_price;
	            $detail_data['bid_current_price'] = $bid_start_price;
	            $detail_data['bid_price'] = $bid_price;
	            $detail_data['reduction_rate'] = $reduction_rate;
	            $detail_data['repeat_no'] = $repeat_no;
	            $detail_data['fixed_price'] = NULL;
	            $detail_data['price_proposal'] = NULL;
	            $update['sort_price'] = $bid_price;
	        }
	    } else if($type == 'emergency' || $type == 'direct') {
	        $sales_data = array(
	            'userid' => $userid
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
	            'hope_price' => $hope_price,
	            'real_price' => $real_price,
	            'sort_price' => $real_price,
	            'deliver_condition' => $deliver_condition
	        );
	    } else if ($type == 'turnkey') {
	        $sales_data = array(
	            'userid' => $userid
	        );
	        $info_data = array(
	            'kind_seq' => $kind_seq,
	            'mnf_seq' => $mnf_seq,
	            'model_seq' => $model_seq,
	            'area_seq' => $area_seq,
	            'model_year' => $model_year,
	            'pur_price' => $pur_price,
	            'remark' => $remark,
	        );
	        $turnkey_data = array(
	            'factory' => $factory,
	            'production' => $production,
	            'quantity' => $quantity,
	            'last_date' => $last_date,
	            'creditor' => $creditor,
	            'expect_date' => $expect_date,
	        );
	    }
	    if($reg_mode == 'insert') {
	        if(isset($sales_data)) {
	            $sales_data['type'] = $type;
	            $this->db->insert('fm_cm_machine_sales', $sales_data);
	            $sales_seq = $this->db->insert_id();
	        }
	        if(isset($info_data)) {
	            $info_data['sales_no'] = $this->getSalesNo($kind_no);
	            $info_data['sales_seq'] = $sales_seq;
	            $info_data['state'] = '미승인';
	            //$info_data['state_date'] = date('Y-m-d H:i:s');
	            $this->db->insert('fm_cm_machine_sales_info', $info_data);
	            $info_seq = $this->db->insert_id();
	            foreach($option_arr as $option) {
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
	                    $this->db->insert('fm_cm_machine_sales_picture', $picture_data);
	                }
	            }
	        }
	        if(isset($detail_data)) {
	            $detail_data['info_seq'] = $info_seq;
	            $detail_data['self_deliver_condition'] = '없음';
	            $detail_data['deliver_service'] = '신청안함';
	            $this->db->insert('fm_cm_machine_sales_detail', $detail_data);
	            $this->db->where('info_seq', $info_seq);
	            $this->db->update('fm_cm_machine_sales_info', $update);
	        }
	        if(isset($turnkey_data)) {
	            $turnkey_data['sales_seq'] = $sales_seq;
	            $this->db->insert('fm_cm_machine_sales_turnkey', $turnkey_data);
	        }
	        $callback = "parent.location.reload()";
	        openDialogAlert('등록이 완료되었습니다.',400,140,'parent',$callback);
	    } else if($reg_mode == 'modify') {
	        if(isset($sales_data)) {
	            $this->db->where('sales_seq', $sales_seq);
	            $this->db->update('fm_cm_machine_sales', $sales_data);
	        }
	        if(isset($info_data)) {
	            $query = "select * from fm_cm_machine_sales_info a, fm_cm_machine_kind b where a.kind_seq = b.kind_seq and info_seq = ".$info_seq;
	            $query = $this->db->query($query);
	            $result = $query->row_array();
	            if($result['kind_no'] != $kind_no)
	               $info_data['sales_no'] = $this->getSalesNo($kind_no);
	            $this->db->where('info_seq', $info_seq);
	            $this->db->update('fm_cm_machine_sales_info', $info_data);
	            
	            $this->db->where('info_seq', $info_seq);
	            $this->db->delete('fm_cm_machine_sales_option');
	            foreach($option_arr as $option) {
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
	        }
	        if(isset($detail_data)) {
	            $query = "select * from fm_cm_machine_sales_detail where info_seq = ".$info_seq;
	            $query = $this->db->query($query);
	            $result = $query->row_array();
	            if(empty($result)) {
	                $detail_data['info_seq'] = $info_seq;
	                $detail_data['self_deliver_condition'] = '없음';
	                $detail_data['deliver_service'] = '신청안함';
	                $this->db->insert('fm_cm_machine_sales_detail', $detail_data);
	            } else {
    	            $this->db->where('info_seq', $info_seq);
    	            $this->db->update('fm_cm_machine_sales_detail', $detail_data);
	            }
	            $this->db->where('info_seq', $info_seq);
	            $this->db->update('fm_cm_machine_sales_info', $update);
	        }
	        if(isset($turnkey_data)) {
	            $this->db->where('turnkey_seq', $turnkey_seq);
	            $this->db->update('fm_cm_machine_sales_turnkey', $turnkey_data);
	        }
	        $callback = "parent.location.reload()";
	        openDialogAlert('수정이 완료되었습니다.',400,140,'parent',$callback);
	    }
	}
	
	## 판매방식 변경
	public function change_type_process() {
	    $type = $this->input->post('type');
	    $info_seq = $this->input->post('info_seq');
	    
	    $query = "select * from fm_cm_machine_sales_info where info_seq = ".$info_seq;
	    $query = $this->db->query($query);
	    $result = $query->row_array();
	    $data = array(
	        'type' => $type
	    );
	    $this->db->where('sales_seq', $result['sales_seq']);
	    $this->db->update('fm_cm_machine_sales', $data);
	    
	    $this->db->where('info_seq', $info_seq);
	    $this->db->delete('fm_cm_machine_sales_detail');
	    
	    $callback = "parent.location.href = '/admin/sale/sale_regist?reg_mode=modify&type=".$type."&seq=".$info_seq."'";
	    openDialogAlert('판매방식 변경이 완료되었습니다.',400,140,'parent',$callback);
	}
	
	## 현장방문
	public function visit() {
	    $this->admin_menu();
	    $this->tempate_modules();
	    
	    $file_path	= $this->template_path();
	    
	    $info_seq = $this->input->get('no');
	    
        $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b ".
                 "where a.sales_seq = b.sales_seq and info_seq = ".$info_seq." ".
   	             "order by b.sales_no asc";
        $query = $this->db->query($query);
        $visit = $query->row_array();
        
        $query = "select * from fm_cm_machine_status where target = '현장방문' and info_seq = ".$info_seq." ".
                 "order by status_seq desc";
        $query = $this->db->query($query);
        $result = $query->result_array();
        $visit['status_list'] = $result;
        
        $query = "select count(*) as finish_cnt from fm_cm_machine_visit where state = '4' and info_seq = ".$info_seq;
        $query = $this->db->query($query);
        $result = $query->row_array();
        $visit['finish_cnt'] = $result['finish_cnt'];
        
        $visit['reg_rest_time'] = $this->getRestTime(date('Y-m-d H:i:s', strtotime('+30 days', strtotime($visit['sales_date'])))).' 남음';
        $visit['pay_rest_time'] = $visit['visit_pay_yn'] == 'y' ? $this->getRestTime(date('Y-m-d H:i:s', strtotime('+60 days', strtotime($visit['visit_pay_date'])))).' 남음' : '-';
        $this->template->assign('visit', $visit);
        
	    $this->template->define(array('tpl'=>$file_path));
	    $this->template->print_("tpl");
	}
	
	## 현장방문 결제 확인
	public function visit_pay_check() {
	    $info_seq = $this->input->post('info_seq');
	    
	    $data = array(
	        'visit_pay_yn' => 'y',
	        'visit_pay_date' => date('Y-m-d H:i:s')
	    );
	    $this->db->where('info_seq', $info_seq);
	    $this->db->update('fm_cm_machine_sales_info', $data);
	    
	    $query = "select *, a.userid as sale_userid, d.userid as visit_userid, d.state as visit_state from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_model c, fm_cm_machine_visit d ".
	             "where a.sales_seq = b.sales_seq and b.model_seq = c.model_seq and b.info_seq = d.info_seq and d.info_seq = " . $info_seq;
	    $query = $this->db->query($query);
	    $result = $query->result_array();
	    
	    $saleUser = $this->getUserData($result[0]['sale_userid']);
	    
	    $query = "select * from fm_cm_machine_pay where pay_type = '현장미팅' and pay_state = '입금대기' and target_seq = ".$info_seq." order by reg_date desc limit 1";
	    $query = $this->db->query($query);
	    $pay_seq = $query->row_array()['pay_seq'];
	    $data = array(
	        'pay_state' => '결제확인'
	    );
	    $this->db->where('pay_seq', $pay_seq);
	    $this->db->update('fm_cm_machine_pay', $data);
	    
	    $cnt = 1;
	    $visit_list = "";
	    foreach($result as $row) {
	        if($row['visit_state'] == '1')
	           $visit_list .= $cnt++.". https://emachinebox.com/sch/visit_rcv/" . $row['visit_seq']."\r\n";
	    }
	    if(!empty($visit_list)) {
	        $visit_list = "\r\n※ 구매자 방문신청 내역 바로가기 URL\r\n".$visit_list;
	    }
	    $status_log = date('Y년 m월 d일 H:i:s') . ' 판매자 ' . $saleUser['userid'] . '님의 이용수수료 입금이 확인되었습니다.';
	    $data = array(
	        'status_log' => $status_log,
	        'info_seq' => $info_seq,
	        'target_seq' => '',
	        'target' => '현장방문'
	    );
	    $this->db->insert('fm_cm_machine_status', $data);
	    
	    $title = "현장방문 <b>입금확인 안내</b>";
	    $message = "※ 이용수수료 입금확인  안내\r\n판매자 " . $saleUser['userid'] . '님이 등록하신 '.$result[0]['model_name']."(" . $result[0]['sales_no'] . ")의 이용수수료 입금이 확인되었습니다.".$visit_list;
	    
	    $this->send_common_mail($saleUser['email'], $title, $message);
	    $this->send_common_sms($saleUser['cellphone'], $message);
	    $callback = "parent.location.reload()";
	    openDialogAlert('입금 확인이 되었습니다.',400,200,'parent',$callback);
	}
	
	## 가격제안
	public function proposal() {
	    $this->admin_menu();
	    $this->tempate_modules();
	    
	    $file_path	= $this->template_path();
	    
	    $info_seq = $this->input->get('no');
	    
	    $query = "select * from fm_cm_machine_status a, fm_cm_machine_sales_info b, fm_cm_machine_proposal c ".
    	   	     "where a.info_seq = b.info_seq and a.target_seq = c.prop_seq and a.target='가격제안' and a.info_seq = ".$info_seq." ".
    	   	     "order by a.reg_date desc";
	    $query = $this->db->query($query);
	    $prop_list = $query->result_array();
	    
	    foreach($prop_list as &$row) {
	        $query = "select userid as sales_userid, type from fm_cm_machine_sales a, fm_cm_machine_sales_info b where a.sales_seq = b.sales_seq and b.info_seq = ".$row['info_seq'];
	        $query = $this->db->query($query);
	        $result = $query->row_array();
	        $row['sales_userid'] = $result['sales_userid'];
	        $row['type'] = $result['type'];
	    }
	    $this->template->assign('prop_list', $prop_list);
	    
	    $this->template->define(array('tpl'=>$file_path));
	    $this->template->print_("tpl");
	}
	
	## 즉시구매
	public function imd_buy() {
	    $this->admin_menu();
	    $this->tempate_modules();
	    
	    $file_path	= $this->template_path();
	    
	    $info_seq = $this->input->get('no');
	    
        $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b ".
   	        "where a.sales_seq = b.sales_seq and info_seq = ".$info_seq." ".
   	        "order by b.sales_no asc";
        $query = $this->db->query($query);
        $buy = $query->row_array();
        
        $query = "select * from fm_cm_machine_status a, fm_cm_machine_imdbuy b ".
                 "where a.target_seq = b.buy_seq and target = '즉시구매' and a.info_seq = ".$buy['info_seq']." ".
                 "order by status_seq desc";
        $query = $this->db->query($query);
        $result = $query->result_array();
        $buy['status_list'] = $result;
        
        $this->template->assign('buy', $buy);
	        
	    $this->template->define(array('tpl'=>$file_path));
	    $this->template->print_("tpl");
	}
	
	## 즉시구매 승인 처리
	public function imdbuy_permit_process() {
	    $buy_seq = $this->input->post('buy_seq');
	    
	    $data = array(
	        'permit_yn' => 'y',
	    );
	    $this->db->where('buy_seq', $buy_seq);
	    $this->db->update('fm_cm_machine_imdbuy', $data);
	    
	    $query = "select *, a.userid as sale_userid, d.userid as buy_userid from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_model c, fm_cm_machine_imdbuy d ".
	   	    "where a.sales_seq = b.sales_seq and b.model_seq = c.model_seq and b.info_seq = d.info_seq and d.buy_seq = " . $buy_seq;
	    $query = $this->db->query($query);
	    $result = $query->row_array();
	    
	    $saleUser = $this->getUserData($result['sale_userid']);
	    $buyUser = $this->getUserData($result['buy_userid']);
	    
	    if($result['visit_pay_yn'] != 'y') {
    	    $pay_message = "\r\n판매자님은 이용수수료 결제가 안되어 이용수수료 결제가 필요합니다.\r\n※ 이용수수료 결제하기 URL : https://emachinebox.com/sch/pay/buy/".$result['info_seq'];
	    }
	    $title = "즉시구매 <b>신청 안내</b>";
	    $message = "※ 즉시구매 신청 안내\r\n판매자 " . $saleUser['userid'] . '님이 등록하신 '.$result['model_name']."(" . $result['sales_no'] . ")에 즉시구매 신청이 들어왔습니다.".$pay_message;
	    
	    $this->send_common_mail($saleUser['email'], $title, $message);
	    $this->send_common_sms($saleUser['cellphone'], $message);
	    
	    /*
	    $title = "즉시구매 <b>승인 안내</b>";
	    $message = "※ 즉시구매 승인 안내\r\n구매자 " . $buyUser['userid'] . '님의 '.$result['model_name']."(" . $result['sales_no'] . ")에 대한 즉시구매 신청이 승인되었습니다.";
	    
	    $this->send_common_mail($buyUser['email'], $title, $message);
	    $this->send_common_sms($buyUser['cellphone'], $message);
	    */
	    $callback = "parent.location.reload()";
	    openDialogAlert('승인 되었습니다.',400,140,'parent',$callback);
	}
	
	## 기계매매
	public function selling() {
	    $this->admin_menu();
	    $this->tempate_modules();
	    
	    $file_path	= $this->template_path();
	    
	    $info_seq = $this->input->get('no');
	    
	    $query = "select * from fm_cm_machine_status a, fm_cm_machine_selling b where a.target_seq = b.sell_seq and target='기계매매' and a.info_seq = ".$info_seq." order by b.reg_date desc";
	    $query = $this->db->query($query);
	    $selling_list = $query->result_array();
	    
	    foreach($selling_list as &$row) {
	        $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b ".
    	   	         "where a.sales_seq = b.sales_seq and info_seq = ".$row['info_seq'];
	        $query = $this->db->query($query);
	        $result = $query->row_array();
	        $row['sales_no'] = $result['sales_no'];
	    }
	    
        $this->template->assign('selling_list', $selling_list);
        
	    $this->template->define(array('tpl'=>$file_path));
	    $this->template->print_("tpl");
	}
	
	## 문의
	public function qna() {
	    $this->admin_menu();
	    $this->tempate_modules();
	    
	    $file_path	= $this->template_path();
	    
	    $info_seq = $this->input->get('no');
	    
	    $query = "select * from fm_cm_machine_question where info_seq = ".$info_seq." order by reg_date desc";
	    $query = $this->db->query($query);
	    $qna_list = $query->result_array();
	    
	    foreach($qna_list as &$row) {
	        $query = "select userid as sales_userid, type, sales_no from fm_cm_machine_sales a, fm_cm_machine_sales_info b where a.sales_seq = b.sales_seq and b.info_seq = ".$row['info_seq'];
	        $query = $this->db->query($query);
	        $result = $query->row_array();
	        $row['sales_userid'] = $result['sales_userid'];
	        $row['type'] = $result['type']; 
	        $row['sales_no'] = $result['sales_no']; 
	    }
	    $this->template->assign('qna_list', $qna_list);
	    
	    $this->template->define(array('tpl'=>$file_path));
	    $this->template->print_("tpl");
	}
	
	## 문의 보내기
	public function ajax_send_qna() {
	    header("Content-Type: application/json");
	    
	    $type = $this->input->post('type');
	    $qna_seq = $this->input->post('qna_seq');
	    $userid = $this->input->post('userid');
	    $title = $this->input->post('title');
	    $content = $this->input->post('content');
	    
	    $userData = $this->getUserData($userid);
	    if($type == '전송') {
	        $data = array(
	            'title' => $title,
	            'content' => $content,
	            'send_yn' => 'y'
	        );
	        $message = $userid."님이 문의하신 글입니다.\n제목: ".$title."\n내용: ".$content;
	    } else if ($type == '답변') {
	        $data = array(
	            'res_content' => $content,
	            'res_yn' => 'y',
	            'res_date' => date('Y-m-d H:i:s')
	        );
	        $message = "판매자 ".$userid."님이 답변하신 글입니다.\n내용: ".$content;
	    }
	    $this->db->where('qna_seq', $qna_seq);
	    $this->db->update('fm_cm_machine_question', $data);
	    
	    $this->send_common_sms($userData['cellphone'], $message);
	    echo json_encode(array('result' => 'true'));
	}

	## 입찰
	public function bid() {
	    $this->admin_menu();
	    $this->tempate_modules();
	    
	    $file_path	= $this->template_path();
	    
	    $tab_menu = $this->input->get('tab_menu');
	    if(empty($tab_menu)) {
	        $tab_menu = '01';
	    }
	    $info_seq = $this->input->get('no');
	    
        if($tab_menu == '01') {
            $bid_where_query = "and bid_yn = 'n' ";
        } else if ($tab_menu == '02') {
            $bid_where_query = "and bid_yn = 'y' ";
            $apply_where_query = "and bid_yn = 'x' ";
        } else if ($tab_menu == '03') {
            $bid_where_query = "and bid_yn = 'y' ";
            $apply_where_query = "and bid_yn = 'y' ";
        }
	    $query = "select *, UNIX_TIMESTAMP(now()) as now_date, UNIX_TIMESTAMP(date_add(sales_date, interval +bid_duration day)) as bid_date ".
	             "from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_sales_detail c ".
	   	         "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and type = 'self' and method = '입찰' and b.info_seq = ".$info_seq." ".
	   	         "order by sales_date desc";
	    $query = $this->db->query($query);
	    $bid = $query->row_array();
	    
	    if(!empty($bid)) {
	        $query = "select * from fm_cm_machine_bid where info_seq = ".$bid['info_seq']. " ". $apply_where_query. "order by bid_price asc";
	        $query = $this->db->query($query);
	        $result = $query->result_array();
	        if($tab_menu == '02' || $tab_menu == '03') {
	            if($bid['bid_yn'] == 'y')
	                $bid['apply_list'] = $result;
	        } else {
	            $bid['apply_list'] = $result;
	        }
	    }
        $now_date = $bid['now_date'];
        $bid_date = $bid['bid_date'];
        
        $date1 = $bid_date;
        $date2 = $now_date;
        
        $restTime = $date1 - $date2;
        $bid['restTime'] = $restTime;
        
        $bid_duration_date = date('Y-m-d H:i:s', strtotime('+'.$bid['bid_duration'].' days', strtotime($bid['sales_date'])));
        $bid['bid_duration_date'] = $bid_duration_date;
	        
	    $this->template->assign('tab_menu', $tab_menu);
	    $this->template->assign('bid', $bid);
	    $this->template->assign('no', $info_seq);
	    
	    $this->template->define(array('tpl'=>$file_path));
	    $this->template->print_("tpl");
	}
	
	## 입찰 취소
	public function bid_cancel_process() {
	    $bid_seq = $this->input->post('bid_seq');
	    
	    $this->db->where('bid_seq', $bid_seq);
	    $this->db->delete('fm_cm_machine_bid');
	    
	    $callback = "parent.location.reload()";
	    openDialogAlert('취소가 완료되었습니다.',400,140,'parent',$callback);
	}
	
	## 입찰 남은기간
	public function get_bid_time() {
	    header("Content-Type: application/json");
	    
	    $info_seq = $this->input->post('info_seq');
	    
	    $query = "select *, UNIX_TIMESTAMP(now()) as now_date, UNIX_TIMESTAMP(date_add(sales_date, interval +bid_duration day)) as bid_date ".
	   	    "from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_sales_detail c ".
	   	    "where a.sales_seq = b.sales_seq and b.info_seq = c.info_seq and b.info_seq = ".$info_seq;
	    $query = $this->db->query($query);
	    $result = $query->row_array();
	    
	    if($result['bid_yn'] == 'y') 
	        $data = '입찰기간이 종료되었습니다.';
        else {
            $now_date = $result['now_date'];
            $bid_date = $result['bid_date'];
            
            $date1 = $bid_date;
            $date2 = $now_date;
            
            $restTime = $date1 - $date2;
            $data = date('d일 H시간 i분 s초', $restTime);
        } 
        echo json_encode($data);
	}
	
	## 턴키매각 승인
	public function turnkey_permit()
	{
	    $this->admin_menu();
	    $this->tempate_modules();
	    
	    $file_path	= $this->template_path();
	    
	    $query = "select * from fm_cm_machine_turnkey a, fm_cm_machine_area b ".
	   	         "where a.area_seq = b.area_seq order by state asc, reg_date desc";
	    $query = $this->db->query($query);
	    $turnkey_list = $query->result_array();
	    
	    foreach($turnkey_list as &$row) {
	        $query2 = "select * from fm_cm_machine_turnkey_info a, fm_cm_machine_kind b, fm_cm_machine_manufacturer c, fm_cm_machine_model d ".
	   	              "where a.kind_seq = b.kind_seq and a.mnf_seq = c.mnf_seq and a.model_seq = d.model_seq and turnkey_seq = ".$row['turnkey_seq']." ".
	   	              "order by tinfo_seq asc";
	        $query2 = $this->db->query($query2);
	        $tinfo_list = $query2->result_array();
	        
	        foreach($tinfo_list as &$info) {
	            $query2 = "select * from fm_cm_machine_sales_option where tinfo_seq = ".$info['tinfo_seq']." order by option_seq asc";
   	            $query2 = $this->db->query($query2);
   	            $result = $query2->result_array();
   	            $option_list = '';
   	            foreach($result as $option) {
   	                $option_list .= $option_list == '' ? $option['option_name'] : ", ".$option['option_name'];
   	            }
   	            $info['option_list'] = $option_list;
	        }
	        $row['tinfo_list'] = $tinfo_list;
	        
	        $query2 = "select * from fm_cm_machine_sales_check where turnkey_seq = ".$row['turnkey_seq'];
	        $query2 = $this->db->query($query2);
	        $result = $query2->row_array();
	        $row['check_list'] = $result;
	    }
	    $this->template->assign('turnkey_list', $turnkey_list);
	    
	    $this->template->define(array('tpl'=>$file_path));
	    $this->template->print_("tpl");
	}
	
	## 턴키매각 승인 처리
	public function turnkey_permit_process() {
	    $turnkey_seq = $this->input->post('turnkey_seq');
	    $state = $this->input->post('state');
	    $message = $this->input->post('message');
	    
	    $query = "select * from fm_cm_machine_turnkey where turnkey_seq = ".$turnkey_seq;
	    $query = $this->db->query($query);
	    $result = $query->row_array();
	    
	    $data = array(
	        'state' => $state
	    );
	    $this->db->where('turnkey_seq', $turnkey_seq);
	    $this->db->update('fm_cm_machine_turnkey', $data);
	    
	    $userData = $this->getUserData($result['userid']);
	    //$this->send_email($state, $userData['email'], $message);
	    $this->send_sms($state, $userData['cellphone'], $message);
	    
	    if($state == '승인') {
	        $html = '승인';
	    } else if($state == '미승인' || $state == '보류') {
	        $html = '<span style=\"color:#FF4848;\">'.$state.'</span>';
	    }
	    // $callback = "parent.document.getElementById('item-state-".$turnkey_seq."').innerHTML = '".$html."';";
	    $callback = "parent.location.reload()";
	    openDialogAlert('[' . $state . '] 상태로 변경이 완료되었습니다.',400,140,'parent',$callback);
	}
	
	## 판매기계 삭제 처리
	public function sale_delete_process() {
	   $info_seq = $this->input->post('info_seq');
	   
       $query = "select * from fm_cm_machine_sales_info where info_seq = ".$info_seq;
       $query = $this->db->query($query);
       $result = $query->row_array();
       $sales_seq = $result['sales_seq'];
       
       $this->db->where('info_seq', $info_seq);
       $this->db->delete('fm_cm_machine_sales_info');
       
       $query = "select * from fm_cm_machine_sales_info where sales_seq = ".$sales_seq;
       $query = $this->db->query($query);
       $result = $query->row_array();
       
       if(empty($result)) {
           $this->db->where('sales_seq', $sales_seq);
           $this->db->delete('fm_cm_machine_sales');
           
           $this->db->where('sales_seq', $sales_seq);
           $this->db->delete('fm_cm_machine_sales_turnkey');
       }
	   
	   $callback = "parent.location.reload()";
	   openDialogAlert('삭제가 완료되었습니다.',400,140,'parent',$callback);
	}
	
	public function get_userdata() {
	    header("Content-Type: application/json");
	    
	    $member_seq = $this->input->post('member_seq');
	    $userData = $this->getUserDataBySeq($member_seq);
	    
	    echo json_encode($userData);
	}
	
	public function service_regist_process() {
	    $info_seq = $this->input->post('info_seq');
	    $service_list = $this->input->post('chk_service');
	    
	    $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_model c ".
	             "where a.sales_seq = b.sales_seq and b.model_seq = c.model_seq and info_seq = ".$info_seq;
	    $query = $this->db->query($query);
	    $sales_data = $query->row_array();
	    
	    $saleUser = $this->getUserData($sales_data['userid']);
	    
	    $ad_pay_content = "";
	    $ad_pay_price = 0;
	    foreach($service_list as $row) {
	        if($row == '하이라이트' || $row == '딜러존' || $row == '자동 업데이트' || $row == '핫마크') {
	            $query = "select * from fm_cm_machine_sales_advertise where ad_name = '".$row."' and info_seq = ".$info_seq;
	            $query = $this->db->query($query);
	            $result = $query->row_array();
	            if(empty($result)) {
	                $ad_data = array();
	                $ad_data['info_seq'] = $info_seq;
	                $ad_data['ad_name'] = $row;
	                if($row == '하이라이트' || $row == '딜러존')
	                   $ad_data['price'] = 100000;
                    else if($row == '자동 업데이트') {
                        $ad_data['price'] = 30000;
                        $ad_data['remaining'] = 10;
                    } else if($row == '핫마크') {
                        $ad_data['price'] = 10000;
                    }
                    $this->db->insert('fm_cm_machine_sales_advertise', $ad_data);
                    
                    $ad_pay_content .= $ad_pay_content == "" ? $row : ", ".$row;
                    $ad_pay_price += $ad_data['price'];
                    
                    $ad_data = array();
                    $ad_data['start_date'] = date('Y-m-d');
                    $ad_data['end_date'] = date('Y-m-d', strtotime('+30 days'));
                    $this->db->where('info_seq', $info_seq);
                    $this->db->update('fm_cm_machine_sales_advertise', $ad_data);
	            }
	        } else if($row == '성능검사') {
	            $this->db->where('info_seq', $info_seq);
	            $this->db->delete('fm_cm_machine_perform');
	            
	            $data = array(
	                'info_seq' => $info_seq
	            );
	            $this->db->insert('fm_cm_machine_perform', $data);
	            
	            $pay_data = array();
	            $pay_data['pay_userid'] = $sales_data['userid'];
	            $pay_data['pay_content'] = '오프라인 성능검사 결제';
	            $pay_data['pay_price'] = 150000;
	            $pay_data['pay_method'] = '무통장 입금';
	            $pay_data['pay_state'] = '입금대기';
	            $pay_data['pay_type'] = '성능검사';
	            $pay_data['pay_no'] = $this->get_pay_no();
	            $pay_data['target_seq'] = $info_seq;
	            $this->db->insert('fm_cm_machine_pay', $pay_data);
	            
	            $title = "성능검사 <b>결제안내</b>";
	            $message = "※ 성능검사 결제안내\r\n판매자 " . $saleUser['userid'] . '님이 등록하신 '.$sales_data['model_name']."(" . $sales_data['sales_no'] . ")에 대한 성능검사가 신청되었습니다. \r\n아래의 계좌로 입금해주시면 서비스를 이용하실 수 있습니다.\r\n농협은행, 에스디네트웍스(신동훈), 계좌번호 302-1371-4082-81\r\n결제금액 : ".number_format($pay_data['pay_price'])."원";
	            
	            $this->send_common_mail($saleUser['email'], $title, $message);
	            $this->send_common_sms($saleUser['cellphone'], $message);
	        } else if($row == '온라인 기계평가 3' || $row == '온라인 기계평가 5') {
	            $this->db->where('info_seq', $info_seq);
	            $this->db->delete('fm_cm_machine_online_eval');
	            
	            $data = array(
	                'info_seq' => $info_seq,
	                'eval_name' => $row
	            );
	            $this->db->insert('fm_cm_machine_online_eval', $data);
	            
	            if($row == '온라인 기계평가 3')
	                $pay_price = 30000;
                else if($row == '온라인 기계평가 5')
                    $pay_price = 50000;
	            $pay_data = array();
	            $pay_data['pay_userid'] = $sales_data['userid'];
	            $pay_data['pay_content'] = $row.' 결제';
	            $pay_data['pay_price'] = $pay_price;
	            $pay_data['pay_method'] = '무통장 입금';
	            $pay_data['pay_state'] = '입금대기';
	            $pay_data['pay_type'] = '기계평가';
	            $pay_data['pay_no'] = $this->get_pay_no();
	            $pay_data['target_seq'] = $info_seq;
	            $this->db->insert('fm_cm_machine_pay', $pay_data);
	            
	            $title = "기계평가 <b>결제안내</b>";
	            $message = "※ 기계평가 결제안내\r\n판매자 " . $saleUser['userid'] . '님이 등록하신 '.$sales_data['model_name']."(" . $sales_data['sales_no'] . ")에 대한 ".$row."가 신청되었습니다. \r\n아래의 계좌로 입금해주시면 서비스를 이용하실 수 있습니다.\r\n농협은행, 에스디네트웍스(신동훈), 계좌번호 302-1371-4082-81\r\n결제금액 : ".number_format($pay_data['pay_price'])."원";
	            
	            $this->send_common_mail($saleUser['email'], $title, $message);
	            $this->send_common_sms($saleUser['cellphone'], $message);
	        }
	    }
	    if($ad_pay_content != "") {
	        $pay_data = array();
	        $pay_data['pay_userid'] = $sales_data['userid'];
	        $pay_data['pay_content'] = '프리미엄광고 ('.$ad_pay_content.') 결제';
	        $pay_data['pay_price'] = $ad_pay_price;
	        $pay_data['pay_method'] = '무통장 입금';
	        $pay_data['pay_state'] = '입금대기';
	        $pay_data['pay_type'] = '프리미엄광고';
	        $pay_data['pay_no'] = $this->get_pay_no();
	        $pay_data['target_seq'] = $info_seq;
	        $this->db->insert('fm_cm_machine_pay', $pay_data);
	        
	        $title = "프리미엄광고 <b>결제안내</b>";
	        $message = "※ 프리미엄광고 결제안내\r\n판매자 " . $saleUser['userid'] . '님이 등록하신 '.$sales_data['model_name']."(" . $sales_data['sales_no'] . ")에 대한 프리미엄 광고가 신청되었습니다. \r\n아래의 계좌로 입금해주시면 서비스를 이용하실 수 있습니다.\r\n농협은행, 에스디네트웍스(신동훈), 계좌번호 302-1371-4082-81\r\n결제금액 : ".number_format($ad_pay_price)."원";
	        
	        $this->send_common_mail($saleUser['email'], $title, $message);
	        $this->send_common_sms($saleUser['cellphone'], $message);
	    }
	    $data = array(
	        'state' => '입금대기'
	    );
	    $this->db->where('info_seq', $info_seq);
	    $this->db->update('fm_cm_machine_sales_info', $data);
	    
	    $callback = "parent.location.reload()";
	    openDialogAlert('유료서비스 등록이 완료되었습니다.',400,160,'parent',$callback);
	}
	
	public function service_cancel_process() {
	    $info_seq = $this->input->post('info_seq');
	    $service_list = $this->input->post('chk_service');
	    
	    foreach($service_list as $row) {
	        if($row == '하이라이트' || $row == '딜러존' || $row == '자동 업데이트' || $row == '핫마크') {
                $this->db->where('info_seq', $info_seq)
                         ->where('ad_name', $row);
                $this->db->delete('fm_cm_machine_sales_advertise');
	        } else if($row == '성능검사') {
	            $this->db->where('info_seq', $info_seq);
	            $this->db->delete('fm_cm_machine_perform');
	        } else if($row == '온라인 기계평가 3' || $row == '온라인 기계평가 5') {
	            $this->db->where('info_seq', $info_seq);
	            $this->db->delete('fm_cm_machine_online_eval');
	        }
	    }
	    $callback = "parent.location.reload()";
	    openDialogAlert('유료서비스 취소가 완료되었습니다.',400,160,'parent',$callback);
	}
	
	public function get_service_info() {
	    header("Content-Type: application/json");
	    
	    $info_seq = $this->input->post('info_seq');
	    
	    $query = "select * from fm_cm_machine_pay where pay_type = '프리미엄광고' and target_seq = ".$info_seq." order by reg_date desc";
	    $query = $this->db->query($query);
	    $ad_pay_info = $query->result_array();
	    
	    $query = "select * from fm_cm_machine_sales_advertise where info_seq = ".$info_seq;
	    $query = $this->db->query($query);
	    $result = $query->result_array();
	    
	    $ad_info = array();
	    $using_service = "";
	    foreach($result as $row) {
	        $using_service .= $using_service == "" ? $row['ad_name'] : ", ".$row['ad_name'];
	        $start_date = $row['start_date'];
	        $end_date = $row['end_date'];
	    }
	    $ad_info['using_service'] = $using_service;
	    $ad_info['start_date'] = $start_date;
	    $ad_info['end_date'] = $end_date;
	    
	    $query = "select * from fm_cm_machine_pay where pay_type = '성능검사' and target_seq = ".$info_seq." order by reg_date desc";
	    $query = $this->db->query($query);
	    $perform_pay_info = $query->result_array();
	    
	    $query = "select * from fm_cm_machine_pay where pay_type = '기계평가' and target_seq = ".$info_seq." order by reg_date desc";
	    $query = $this->db->query($query);
	    $eval_pay_info = $query->result_array();
	    
	    $query = "select * from fm_cm_machine_perform where info_seq = ".$info_seq;
	    $query = $this->db->query($query);
	    $result = $query->row_array();
	    if(empty($result))
	        $using_perform = '미이용';
	    else
	        $using_perform = '이용중 (성능검사)';
        $query = "select * from fm_cm_machine_online_eval where info_seq = ".$info_seq;
        $query = $this->db->query($query);
        $result = $query->row_array();
        if(empty($result))
            $using_eval = '미이용';
        else
            $using_eval = '이용중 ('.$result['eval_name'].')';
	    echo json_encode(array('ad_pay_info' => $ad_pay_info, 'ad_info' => $ad_info, 'perform_pay_info' => $perform_pay_info, 'eval_pay_info' => $eval_pay_info, 'using_perform' => $using_perform, 'using_eval' => $using_eval));
	}
	
	public function is_pay_check() {
	    header("Content-Type: application/json");
	    
	    $info_seq = $this->input->post('info_seq');
	    
	    $query = "select * from fm_cm_machine_sales_info where info_seq = ".$info_seq;
	    $query = $this->db->query($query);
	    $result = $query->row_array();
	    
	    echo json_encode($result['state']);
	}
	
	public function service_pay_check() {
	    $info_seq = $this->input->post('info_seq');
	    
	    $data = array(
	        'pay_state' => '결제확인'
	    );
	    $this->db->where('target_seq', $info_seq)
	             ->where_in('pay_type', array('프리미엄광고', '성능검사', '기계평가'));
	    $this->db->update('fm_cm_machine_pay', $data);
	    
	    $data = array(
	        'state' => '승인'
	    );
	    $this->db->where('info_seq', $info_seq);
	    $this->db->update('fm_cm_machine_sales_info', $data);
	    
	    $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_pay c, fm_cm_machine_model d ".
	             "where a.sales_seq = b.sales_seq and b.info_seq = c.target_seq and b.model_seq = d.model_seq and pay_type in('프리미엄광고', '성능검사', '기계평가') and target_seq = ".$info_seq;
	    $query = $this->db->query($query);
	    $result = $query->result_array();
	    
	    $price = 0;
	    foreach($result as $row) {
	        $price += $row['pay_price'];
	    }
	    $saleUser = $this->getUserData($result[0]['pay_userid']);
	    
	    $title = "결제확인 <b>안내</b>";
	    $message = "※ 결제확인 안내\r\n판매자 " . $saleUser['userid'] . '님이 등록하신 '.$result[0]['model_name']."(" . $result[0]['sales_no'] . ")에 대한 결제확인이 완료되었습니다.\r\n결제금액 : ".number_format($price)."원";
	    
	    $this->send_common_mail($saleUser['email'], $title, $message);
	    $this->send_common_sms($saleUser['cellphone'], $message);
	    
	    $callback = "parent.location.reload()";
	    openDialogAlert('결제확인이 완료되었습니다.',400,160,'parent',$callback);
	}
	
	public function ad_setting_process() {
	    $info_seq = $this->input->post('info_seq');
	    $start_date = $this->input->post('start_date');
	    $end_date = $this->input->post('end_date');
	    
	    $data = array(
	        'start_date' => $start_date,
	        'end_date' => $end_date
	    );
	    $this->db->where('info_seq', $info_seq);
	    $this->db->update('fm_cm_machine_sales_advertise', $data);
	    
	    $callback = "parent.location.reload()";
	    openDialogAlert('설정이 완료되었습니다.',400,160,'parent',$callback);
	}
	
	public function estimate_regist_process() {
	    header("Content-Type: application/json");
	    
	    $info_seq = $this->input->post('info_seq');
	    $dealer_list = $this->input->post('dealer_list');
	    
	    $dealer_list = explode(',', $dealer_list);
	    foreach($dealer_list as $row) {
	        $userData = $this->getUserDataBySeq($row);
	        $query = "select * from fm_cm_machine_estimate_dealer where info_seq = ".$info_seq." and userid = '".$userData['userid']."'";
	        $query = $this->db->query($query);
	        $result = $query->row_array();
	        if(empty($result)) {
	            $data = array(
	                'userid' => $userData['userid'],
	                'info_seq' => $info_seq,
	                'state' => '작성대기'
	            );
	            $this->db->insert('fm_cm_machine_estimate_dealer', $data);
	           
	            $query = "select * from fm_cm_machine_sales_info a, fm_cm_machine_model b ".
	   	            "where a.model_seq = b.model_seq and info_seq = ".$info_seq;
	            $query = $this->db->query($query);
	            $result = $query->row_array();
	            
	            $title = "견적서 요청 <b>안내</b>";
	            $message = "※ 견적서 요청 안내\r\n공식딜러 " . $userData['userid'] . '님에게 '.$result['model_name']."(" . $result['sales_no'] . ")에 대한 견적서 작성요청이 들어왔습니다.\r\n마이페이지의 견적서 양식을 작성 후 제출해주세요.";
	            
	            $this->send_common_mail($userData['email'], $title, $message);
	            $this->send_common_sms($userData['cellphone'], $message);
	        }
	    }
	    $data = array(
	        'estimate_yn' => 'h'
	    );
	    $this->db->where('info_seq', $info_seq);
	    $this->db->update('fm_cm_machine_sales_info', $data);
	    
	    echo json_encode(array('result' => true));
	}
	
	public function get_estimate_info() {
	    header("Content-Type: application/json");
	    
	    $info_seq = $this->input->post('info_seq');
	    
        $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b ".
                 "where a.sales_seq = b.sales_seq and info_seq = ".$info_seq;
        $query = $this->db->query($query);
        $info = $query->row_array();

        $query = "select * from fm_cm_machine_estimate_dealer where info_seq = ".$info_seq." order by reg_date desc";
        $query = $this->db->query($query);
        $list = $query->result_array();
        
	    echo json_encode(array('info' => $info, 'list' => $list));
	}
	
	public function estimate_admin_regist() {
	    header("Content-Type: application/json");
	    
	    $info_seq = $this->input->post('info_seq');
	    $member_seq = $this->input->post('member_seq');
	    
	    $userData = $this->getUserDataBySeq($member_seq);
        $query = "select * from fm_cm_machine_sales_info a, fm_cm_machine_model b, fm_cm_machine_estimate_dealer c ".
   	        "where a.model_seq = b.model_seq and a.info_seq = c.info_seq and c.info_seq = ".$info_seq." and userid = '".$userData['userid']."'";
        $query = $this->db->query($query);
        $result = $query->row_array();
        
        if(empty($result)) {
            $data = array(
                'userid' => $userData['userid'],
                'info_seq' => $info_seq,
                'state' => '작성대기'
            );
            $this->db->insert('fm_cm_machine_estimate_dealer', $data);
            $estimate_seq = $this->db->insert_id();
        } else {
            $estimate_seq = $result['estimate_seq'];
        }
	    $data = array(
	        'estimate_yn' => 'h'
	    );
	    $this->db->where('info_seq', $info_seq);
	    $this->db->update('fm_cm_machine_sales_info', $data);
	    
	    $query = "select * from fm_cm_machine_estimate_form where estimate_seq = ".$estimate_seq;
	    $query = $this->db->query($query);
	    $result = $query->row_array();
	    
	    $res = true;
	    if(!empty($result)) {
	        $res = false;
	    }
	    echo json_encode(array('result' => $res, 'estimate_seq' => $estimate_seq));
	}
	
	public function estimate_send_process() {
	    $info_seq = $this->input->post('info_seq');
	    
	    $data = array(
	        'estimate_yn' => 'y'
	    );
	    $this->db->where('info_seq', $info_seq);
	    $this->db->update('fm_cm_machine_sales_info', $data);
	    
	    $query = "select * from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_model c ".
	   	    "where a.sales_seq = b.sales_seq and b.model_seq = c.model_seq and b.info_seq = ".$info_seq;
	    $query = $this->db->query($query);
	    $result = $query->row_array();
	    $userData = $this->getUserData($result['userid']);
	    
	    $title = "긴급판매 견적서 작성완료 <b>안내</b>";
	    $message = "※ 긴급판매 견적서 작성완료 안내\r\n판매자 " . $userData['userid'] . '님이 등록하신 '.$result['model_name']."(" . $result['sales_no'] . ")에 대한 견적서가 작성되었습니다. \r\n마이페이지에서 희망하는 견적서를 선택해주세요.";
	    
	    $this->send_common_mail($userData['email'], $title, $message);
	    $this->send_common_sms($userData['cellphone'], $message);
	    
	    $callback = "parent.location.reload()";
	    openDialogAlert('전송이 완료되었습니다.',400,160,'parent',$callback);
	}
	
	public function find_status() {
	    $this->admin_menu();
	    $this->tempate_modules();
	    
	    // 상품검색폼
	    $this->template->define(array('goods_search_form' => $this->skin.'/goods/goods_search_form.html'));
	    
	    // 기본검색설정폼 분리 2015-05-04
	    $this->template->define(array('set_search_default' => $this->skin.'/goods/_set_search_default_goods.html'));
	    $this->template->assign(array('search_page'=>uri_string()));
	    $file_path	= $this->template_path();
	    
	    $query = "select * from fm_cm_machine_find a, fm_cm_machine_kind b where a.kind_seq = b.kind_seq order by reg_date desc";
	    $query = $this->db->query($query);
	    $find_list = $query->result_array();
	    
	    $this->template->assign('find_list', $find_list);
	    
	    $this->template->define(array('tpl'=>$file_path));
	    $this->template->print_("tpl");
	}
	
	public function find_regist() {
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
	    $this->template->assign('kind_map', $kind_map);
	    
	    $query = "select * from fm_cm_machine_manufacturer group by mnf_name order by mnf_name asc";
	    $query = $this->db->query($query);
	    $result = $query->result_array();
	    $this->template->assign('mnf_list', $result);
	    
	    $query = $this->db->get('fm_cm_machine_model');
	    $result = $query->result_array();
	    $this->template->assign('model_list', $result);
	    
	    $query = $this->db->get('fm_cm_machine_area');
	    $result = $query->result_array();
	    $this->template->assign('area_list', $result);
	    
	    if(empty($_GET['reg_mode'])) $_GET['reg_mode'] = 'insert';
	    $this->template->assign('reg_mode', $_GET['reg_mode']);
	    $this->template->assign('seq', $_GET['seq']);
	    
	    $this->template->define(array('tpl'=>$file_path));
	    $this->template->print_("tpl");
	}
	
	public function ajax_get_find() {
	    header("Content-Type: application/json");
	    
	    $find_seq = $this->input->post('find_seq');
	    
	    $query = "select * from fm_cm_machine_find a, fm_cm_machine_kind b where a.kind_seq = b.kind_seq and find_seq = ".$find_seq." order by reg_date desc";
        $query = $this->db->query($query);
        $find_item = $query->row_array();
        
	    echo json_encode(array('result' => $find_item));
	}
	
	public function find_regist_process() {
	    $find_seq = $this->input->post('find_seq');
	    $kind_seq = $this->input->post('kind_seq');
	    $mnf_name = $this->input->post('mnf_name');
	    $model_name = $this->input->post('model_name');
	    $area_list = $this->input->post('area_list');
	    $model_year = $this->input->post('model_year');
	    $hope_price = $this->input->post('hope_price');
	    $option = $this->input->post('option');
	    $buy_expect_date = $this->input->post('buy_expect_date');
	    $deliver_service_arr = $this->input->post('deliver_service');
	    $reg_mode = $this->input->post('reg_mode');
	    $userid = $this->input->post('userid');

	    $deliver_service = '';
	    foreach($deliver_service_arr as $row) {
	        $deliver_service .= $deliver_service == '' ? $row : ','.$row;
	    }
        $find_data = array(
            'userid' => $userid,
            'kind_seq' => $kind_seq,
            'mnf_name' => $mnf_name,
            'model_name' => $model_name,
            'area_list' => $area_list,
            'model_year' => $model_year,
            'hope_price' => $hope_price,
            'option' => $option,
            'buy_expect_date' => $buy_expect_date,
            'deliver_service' => $deliver_service,
        );
	    if($reg_mode == 'insert') {
	        if(isset($find_data)) {
	            $find_data['state'] = '1';
	            $this->db->insert('fm_cm_machine_find', $find_data);
	        }
	        $callback = "parent.location.reload()";
	        openDialogAlert('신청이 완료되었습니다.',400,140,'parent',$callback);
	    } else if($reg_mode == 'modify') {
	        if(isset($find_data)) {
	            $this->db->where('find_seq', $find_seq);
	            $this->db->update('fm_cm_machine_find', $find_data);
	        }
	        $callback = "parent.location.reload()";
	        openDialogAlert('수정이 완료되었습니다.',400,140,'parent',$callback);
	    }
	}
	
	public function find_delete_process() {
	    $find_seq = $this->input->post('find_seq');
	    
	    $this->db->where('find_seq', $find_seq);
	    $this->db->delete('fm_cm_machine_find');
	    
	    $callback = "parent.location.reload()";
	    openDialogAlert('삭제가 완료되었습니다.',400,140,'parent',$callback);
	}
	
	public function estimate_form() {
	    $this->admin_menu();
	    $this->tempate_modules();
	    
	    $file_path	= $this->template_path();
	    
	    $this->template_path = $tpl;
	    $this->template->assign(array("template_path"=>$this->template_path));
	    
	    $estimate_seq = $this->input->get('seq');
	    $mode = $this->input->get('mode');
	    
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
	    
        $this->template->assign('is_admin', 'y');
	        
        $this->template->define(array('tpl'=>$file_path));
        $this->template->print_("tpl");
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
	    
	    $this->session->set_flashdata('message', '제출이 완료되었습니다.');
	    pageRedirect('estimate_form?seq='.$estimate_seq.'&mode=view&popup=y');
	}
	
	public function excel_download() {
	    $type = $this->input->get('type');
	    $info_seq = $this->input->get('seq');
	    
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
        ini_set('memory_limit', '5120M');
        set_time_limit(0);
        
        if($type == 'self') {
            $type_nm = '셀프판매';
        } else if ($type == 'emergency') {
            $type_nm = '긴급판매';
        } else if ($type == 'direct') {
            $type_nm = '머박다이렉트';
        } else if ($type == 'turnkey') {
            $type_nm = '턴키매각';
        }
        if($sale_item['state'] == '승인') {
            $sale_state = '판매중';
        } else {
            $sale_state = '판매대기';
        }
        
        
	    $this->load->library('pxl');
	    $this->objPHPExcel = new PHPExcel();
	    
	    # 시트지정
	    $this->objPHPExcel->setActiveSheetIndex(0);
	    $this->objPHPExcel->getActiveSheet()->setTitle('Sheet1');
	    # cell 헤더 설정
	    $this->objPHPExcel->getActiveSheet()->setCellValue('A1', '등록일');
	    $this->objPHPExcel->getActiveSheet()->setCellValue('B1', '등록번호');
	    $this->objPHPExcel->getActiveSheet()->setCellValue('C1', '기계분류');
	    $this->objPHPExcel->getActiveSheet()->setCellValue('D1', '기계종류');
	    $this->objPHPExcel->getActiveSheet()->setCellValue('E1', '제조사');
	    $this->objPHPExcel->getActiveSheet()->setCellValue('F1', '모델명');
	    if($type == 'turnkey') {
	       $this->objPHPExcel->getActiveSheet()->setCellValue('G1', '연식');
	       $this->objPHPExcel->getActiveSheet()->setCellValue('H1', '매입가');
	       $this->objPHPExcel->getActiveSheet()->setCellValue('I1', '옵션');
	       $this->objPHPExcel->getActiveSheet()->setCellValue('J1', '특이사항');
	       $this->objPHPExcel->getActiveSheet()->setCellValue('K1', '공장명');
	       $this->objPHPExcel->getActiveSheet()->setCellValue('L1', '생산내용');
	       $this->objPHPExcel->getActiveSheet()->setCellValue('M1', '지역');
	       $this->objPHPExcel->getActiveSheet()->setCellValue('N1', '총기계 수량');
	       $this->objPHPExcel->getActiveSheet()->setCellValue('O1', '마지막 기계가동일');
	       $this->objPHPExcel->getActiveSheet()->setCellValue('P1', '채권자 내역');
	       $this->objPHPExcel->getActiveSheet()->setCellValue('Q1', '매각 예정일');
	       $cell_loc = array('R', 'S', 'T');
	    } else {
    	    $this->objPHPExcel->getActiveSheet()->setCellValue('G1', '판매지역');
    	    $this->objPHPExcel->getActiveSheet()->setCellValue('H1', '연식');
    	    $this->objPHPExcel->getActiveSheet()->setCellValue('I1', '시리얼넘버');
    	    $this->objPHPExcel->getActiveSheet()->setCellValue('J1', '기계크기');
    	    $this->objPHPExcel->getActiveSheet()->setCellValue('K1', '기계중량');
    	    $this->objPHPExcel->getActiveSheet()->setCellValue('L1', '컨트롤러');
    	    $this->objPHPExcel->getActiveSheet()->setCellValue('M1', '옵션');
	        
    	    if($type == 'self') {
        	    $this->objPHPExcel->getActiveSheet()->setCellValue('N1', '판매방법');
    	        if($sale_item['method'] == '고정가격판매') {
            	    $this->objPHPExcel->getActiveSheet()->setCellValue('O1', '고정가격 판매금액');
            	    $this->objPHPExcel->getActiveSheet()->setCellValue('P1', '구매자 가격제안 받기');
            	    $cell_loc = array('Q', 'R', 'S');
    	        } else if($sale_item['method'] == '입찰') {
            	    $this->objPHPExcel->getActiveSheet()->setCellValue('O1', '입찰판매 기간');
            	    $this->objPHPExcel->getActiveSheet()->setCellValue('P1', '시작가');
            	    $this->objPHPExcel->getActiveSheet()->setCellValue('Q1', '즉시판매가');
            	    $this->objPHPExcel->getActiveSheet()->setCellValue('R1', '재입찰 가격인하율');
            	    $this->objPHPExcel->getActiveSheet()->setCellValue('S1', '재입찰 반복횟수');
            	    $cell_loc = array('T', 'U', 'V');
    	        }
    	    } else {
        	    $this->objPHPExcel->getActiveSheet()->setCellValue('N1', '판매금액');
        	    $cell_loc = array('O', 'P', 'Q');
    	    }
	    }
	    $this->objPHPExcel->getActiveSheet()->setCellValue($cell_loc[0].'1', '판매유형');
	    $this->objPHPExcel->getActiveSheet()->setCellValue($cell_loc[1].'1', '판매상태');
	    $this->objPHPExcel->getActiveSheet()->setCellValue($cell_loc[2].'1', '판매승인');
	    $this->objPHPExcel->getActiveSheet()->getStyle('A1:'.$cell_loc[2].'1')->getFont()->setSize(10);
	    $this->objPHPExcel->getActiveSheet()->getStyle('A1:'.$cell_loc[2].'1')->getFont()->setBold(true);
	    $this->objPHPExcel->getActiveSheet()->getStyle('A1:'.$cell_loc[2].'1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('F0F0F0');
	    $this->objPHPExcel->getActiveSheet()->getStyle('A1:'.$cell_loc[2].'1')->getBorders()->getBottom()->getColor()->setRGB('5A5A5A');
	    $this->objPHPExcel->getActiveSheet()->getStyle('A1:'.$cell_loc[2].'1')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
	    $this->objPHPExcel->getActiveSheet()->getStyle('A1:'.$cell_loc[2].'1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	    $this->objPHPExcel->getActiveSheet()->getStyle('A1:'.$cell_loc[2].'1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	    $this->objPHPExcel->getActiveSheet()->getStyle('A1:'.$cell_loc[2].'2')->getAlignment()->setWrapText(true);
	    $this->objPHPExcel->getActiveSheet()->getStyle('A2:'.$cell_loc[2].'2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	    $this->objPHPExcel->getActiveSheet()->getStyle('A2:'.$cell_loc[2].'2')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	    $this->objPHPExcel->getActiveSheet()->getStyle('B2')->getNumberFormat()->setFormatCode('000000000000');
	    $this->objPHPExcel->getActiveSheet()->getDefaultRowDimension() -> setRowHeight(40);
	    $this->objPHPExcel->getActiveSheet()->getRowDimension(1) -> setRowHeight(20);
	    $this->objPHPExcel->getActiveSheet()->getRowDimension(2) -> setRowHeight(100);
	    $this->objPHPExcel->getActiveSheet()->getColumnDimension("A") -> setWidth(20);
	    $this->objPHPExcel->getActiveSheet()->getColumnDimension("B") -> setWidth(15);
	    $this->objPHPExcel->getActiveSheet()->getColumnDimension("C") -> setWidth(15);
	    $this->objPHPExcel->getActiveSheet()->getColumnDimension("D") -> setWidth(15);
	    $this->objPHPExcel->getActiveSheet()->getColumnDimension("E") -> setWidth(15);
	    $this->objPHPExcel->getActiveSheet()->getColumnDimension("F") -> setWidth(15);
	    $this->objPHPExcel->getActiveSheet()->getColumnDimension("G") -> setWidth(15);
	    $this->objPHPExcel->getActiveSheet()->getColumnDimension("H") -> setWidth(15);
	    
	    $n=2;
	    if($type == 'turnkey') {
    	    $this->objPHPExcel->getActiveSheet()->getColumnDimension("I") -> setWidth(25);
    	    $this->objPHPExcel->getActiveSheet()->getColumnDimension("J") -> setWidth(30);
    	    $this->objPHPExcel->getActiveSheet()->getColumnDimension("K") -> setWidth(20);
    	    $this->objPHPExcel->getActiveSheet()->getColumnDimension("L") -> setWidth(20);
    	    $this->objPHPExcel->getActiveSheet()->getColumnDimension("M") -> setWidth(15);
    	    $this->objPHPExcel->getActiveSheet()->getColumnDimension("N") -> setWidth(15);
    	    $this->objPHPExcel->getActiveSheet()->getColumnDimension("O") -> setWidth(20);
    	    $this->objPHPExcel->getActiveSheet()->getColumnDimension("P") -> setWidth(20);
    	    $this->objPHPExcel->getActiveSheet()->getColumnDimension("Q") -> setWidth(20);
    	    $this->objPHPExcel->getActiveSheet()->getColumnDimension("R") -> setWidth(16);
    	    $this->objPHPExcel->getActiveSheet()->getColumnDimension("S") -> setWidth(12);
    	    $this->objPHPExcel->getActiveSheet()->getColumnDimension("T") -> setWidth(12);
    	    
    	    $this->objPHPExcel->getActiveSheet()->setCellValue('A'.$n, $sale_item['sales_date']);
    	    $this->objPHPExcel->getActiveSheet()->setCellValue('B'.$n, $sale_item['sales_no']);
    	    $this->objPHPExcel->getActiveSheet()->setCellValue('C'.$n, $sale_item['kind_type']);
    	    $this->objPHPExcel->getActiveSheet()->setCellValue('D'.$n, $sale_item['kind_name']);
    	    $this->objPHPExcel->getActiveSheet()->setCellValue('E'.$n, $sale_item['mnf_name']);
    	    $this->objPHPExcel->getActiveSheet()->setCellValue('F'.$n, $sale_item['model_name']);
    	    $this->objPHPExcel->getActiveSheet()->setCellValue('G'.$n, $sale_item['model_year']);
    	    $this->objPHPExcel->getActiveSheet()->setCellValue('H'.$n, $this->price_format($sale_item['pur_price'])."원");
    	    $option_list = "";
    	    foreach($sale_item['option_list'] as $row) {
    	        $option_list .= $option_list == "" ? $row['option_name'] : ", ".$row['option_name'];
    	    }
    	    $this->objPHPExcel->getActiveSheet()->setCellValue('I'.$n, $option_list);
    	    $this->objPHPExcel->getActiveSheet()->setCellValue('J'.$n, $sale_item['remark']);
    	    $this->objPHPExcel->getActiveSheet()->setCellValue('K'.$n, $sale_item['factory']);
    	    $this->objPHPExcel->getActiveSheet()->setCellValue('L'.$n, $sale_item['production']);
    	    $this->objPHPExcel->getActiveSheet()->setCellValue('M'.$n, $sale_item['area_name']);
    	    $this->objPHPExcel->getActiveSheet()->setCellValue('N'.$n, $sale_item['quantity']);
    	    $this->objPHPExcel->getActiveSheet()->setCellValue('O'.$n, $sale_item['last_date']);
    	    $this->objPHPExcel->getActiveSheet()->setCellValue('P'.$n, $sale_item['creditor']);
    	    $this->objPHPExcel->getActiveSheet()->setCellValue('Q'.$n, $sale_item['expect_date']);
    	    $this->objPHPExcel->getActiveSheet()->setCellValue('R'.$n, $type_nm);
    	    $this->objPHPExcel->getActiveSheet()->setCellValue('S'.$n, $sale_state);
    	    $this->objPHPExcel->getActiveSheet()->setCellValue('T'.$n, $sale_item['state']);
	    } else {
	        $this->objPHPExcel->getActiveSheet()->getColumnDimension("I") -> setWidth(15);
	        $this->objPHPExcel->getActiveSheet()->getColumnDimension("J") -> setWidth(15);
	        $this->objPHPExcel->getActiveSheet()->getColumnDimension("K") -> setWidth(15);
	        $this->objPHPExcel->getActiveSheet()->getColumnDimension("L") -> setWidth(15);
	        $this->objPHPExcel->getActiveSheet()->getColumnDimension("M") -> setWidth(20);
	        
	        $this->objPHPExcel->getActiveSheet()->setCellValue('A'.$n, $sale_item['sales_date']);
	        $this->objPHPExcel->getActiveSheet()->setCellValue('B'.$n, $sale_item['sales_no']);
	        $this->objPHPExcel->getActiveSheet()->setCellValue('C'.$n, $sale_item['kind_type']);
	        $this->objPHPExcel->getActiveSheet()->setCellValue('D'.$n, $sale_item['kind_name']);
	        $this->objPHPExcel->getActiveSheet()->setCellValue('E'.$n, $sale_item['mnf_name']);
	        $this->objPHPExcel->getActiveSheet()->setCellValue('F'.$n, $sale_item['model_name']);
	        $this->objPHPExcel->getActiveSheet()->setCellValue('G'.$n, $sale_item['area_name']);
	        $this->objPHPExcel->getActiveSheet()->setCellValue('H'.$n, $sale_item['model_year']);
	        $this->objPHPExcel->getActiveSheet()->setCellValue('I'.$n, $sale_item['serial_num']);
	        $this->objPHPExcel->getActiveSheet()->setCellValue('J'.$n, $sale_item['size']);
	        $this->objPHPExcel->getActiveSheet()->setCellValue('K'.$n, str_replace('kg', '', $sale_item['weight']).'kg');
	        $this->objPHPExcel->getActiveSheet()->setCellValue('L'.$n, $sale_item['controller']);
	        $option_list = "";
	        foreach($sale_item['option_list'] as $row) {
	            $option_list .= $option_list == "" ? $row['option_name'] : ", ".$row['option_name'];
	        }
	        $this->objPHPExcel->getActiveSheet()->setCellValue('M'.$n, $option_list);
	        
	        if($type == 'self') {
    	        $this->objPHPExcel->getActiveSheet()->getColumnDimension("N") -> setWidth(15);
    	        $this->objPHPExcel->getActiveSheet()->setCellValue('N'.$n, $sale_item['method']);
	            if($sale_item['method'] == '고정가격판매') {
        	        $this->objPHPExcel->getActiveSheet()->getColumnDimension("O") -> setWidth(20);
        	        $this->objPHPExcel->getActiveSheet()->getColumnDimension("P") -> setWidth(20);
        	        $this->objPHPExcel->getActiveSheet()->getColumnDimension("Q") -> setWidth(16);
        	        $this->objPHPExcel->getActiveSheet()->getColumnDimension("R") -> setWidth(12);
        	        $this->objPHPExcel->getActiveSheet()->getColumnDimension("S") -> setWidth(12);
        	        
        	        $this->objPHPExcel->getActiveSheet()->setCellValue('O'.$n, $this->price_format($sale_item['fixed_price'])."원");
        	        $this->objPHPExcel->getActiveSheet()->setCellValue('P'.$n, $sale_item['price_proposal'] == '0' ? '받지않음' : $sale_item['price_proposal']."% 이내");
        	        $this->objPHPExcel->getActiveSheet()->setCellValue('Q'.$n, $type_nm);
        	        $this->objPHPExcel->getActiveSheet()->setCellValue('R'.$n, $sale_state);
        	        $this->objPHPExcel->getActiveSheet()->setCellValue('S'.$n, $sale_item['state']);
	            } else if($sale_item['method'] == '입찰') {
	                $this->objPHPExcel->getActiveSheet()->getColumnDimension("O") -> setWidth(15);
	                $this->objPHPExcel->getActiveSheet()->getColumnDimension("P") -> setWidth(15);
	                $this->objPHPExcel->getActiveSheet()->getColumnDimension("Q") -> setWidth(15);
	                $this->objPHPExcel->getActiveSheet()->getColumnDimension("R") -> setWidth(20);
	                $this->objPHPExcel->getActiveSheet()->getColumnDimension("S") -> setWidth(20);
	                $this->objPHPExcel->getActiveSheet()->getColumnDimension("T") -> setWidth(16);
	                $this->objPHPExcel->getActiveSheet()->getColumnDimension("U") -> setWidth(12);
	                $this->objPHPExcel->getActiveSheet()->getColumnDimension("V") -> setWidth(12);
	                
	                $this->objPHPExcel->getActiveSheet()->setCellValue('O'.$n, $sale_item['bid_duration']."일");
	                $this->objPHPExcel->getActiveSheet()->setCellValue('P'.$n, $this->price_format($sale_item['bid_start_price'])."원");
	                $this->objPHPExcel->getActiveSheet()->setCellValue('Q'.$n, $this->price_format($sale_item['bid_price'])."원");
	                $this->objPHPExcel->getActiveSheet()->setCellValue('R'.$n, $sale_item['reduction_rate'] == '0' ? '없음' : $sale_item['reduction_rate']."%");
	                $this->objPHPExcel->getActiveSheet()->setCellValue('S'.$n, $sale_item['repeat_no'] == '0' ? '없음' : $sale_item['repeat_no']."회");
	                $this->objPHPExcel->getActiveSheet()->setCellValue('T'.$n, $type_nm);
	                $this->objPHPExcel->getActiveSheet()->setCellValue('U'.$n, $sale_state);
	                $this->objPHPExcel->getActiveSheet()->setCellValue('V'.$n, $sale_item['state']);
	            }
	        } else {
	            $this->objPHPExcel->getActiveSheet()->getColumnDimension("N") -> setWidth(15);
	            $this->objPHPExcel->getActiveSheet()->getColumnDimension("O") -> setWidth(16);
	            $this->objPHPExcel->getActiveSheet()->getColumnDimension("P") -> setWidth(12);
	            $this->objPHPExcel->getActiveSheet()->getColumnDimension("Q") -> setWidth(12);
	            
	            $this->objPHPExcel->getActiveSheet()->setCellValue('N'.$n, $this->price_format($sale_item['real_price'])."원");
	            $this->objPHPExcel->getActiveSheet()->setCellValue('O'.$n, $type_nm);
	            $this->objPHPExcel->getActiveSheet()->setCellValue('P'.$n, $sale_state);
	            $this->objPHPExcel->getActiveSheet()->setCellValue('Q'.$n, $sale_item['state']);
	        }
	    }
	    $filename = '기계정보_'.date('Ymd').'.xls';
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
	
	private function send_email($state, $email, $message) {
	    if($email){
	        sale_permit_mail($state, $email, $message);
	    }
	}
	
	private function send_sms($state, $phone, $message) {
	    if($phone) {
	        sale_permit_sms($state, $phone, $message);
	    }
	}
	
	private function send_common_mail($email, $title, $message)
	{
	    if ($email) {
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
	
	private function getUserDataBySeq($member_seq) {
	    return $this->membermodel->get_member_data($member_seq);
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
	
	private function getRestTime($date)
	{
	    $now_date = strtotime(date('Y-m-d H:i:s'));
	    
	    $date1 = strtotime($date);
	    $date2 = $now_date;
	    
	    $restTime = $date1 - $date2;
	    
	    $day = floor($restTime / (60*60*24));
	    $hour = floor(($restTime-($day*60*60*24))/(60*60));
	    $minute = floor(($restTime-($day*60*60*24)-($hour*60*60))/(60));
	    $second = $restTime - ($day*60*60*24) - ($hour*60*60) - ($minute*60);
	    
	    if($day < 0) {
	        $day = 0;
	        $hour = 0;
	        $minute = 0;
	        $second = 0;
	    }
	    $day = $day < 10 ? '0'.$day : $day;
	    $hour = $hour < 10 ? '0'.$hour : $hour;
	    $minute = $minute < 10 ? '0'.$minute : $minute;
	    $second = $second < 10 ? '0'.$second : $second;
	    
	    return $day."일 ".$hour.":".$minute.":".$second;
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
	
	private function getUserDataById($userid) {
	    $query = "select * from fm_member where userid='".$userid."'";
	    $query = $this->db->query($query);
	    $result = $query->row_array();
	    return $this->membermodel->get_member_data($result['member_seq']);
	}
}