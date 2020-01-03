<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);
class main extends front_base {

	public function main_index()
	{
		redirect("main/index");
	}

    ## 메인 캐쉬 읽기
    protected function _index(){
        $this->load->library('cachemain');
        $sSkinPath  = 'main/cache_main.html';
        $this->cachemain->cache_file_path = $this->template->template_dir."/".$this->skin.'/';
        $this->cachemain->set_cache_file($sSkinPath);
        $cachepreview   = $this->input->get('cachepreview');
        $mainCachePrn = "skinCache";
        if($this->fammerceMode){
            $mainCachePrn = "fammerceSkinCache";
        }else if($this->mobileMode){
            $mainCachePrn = "mobileSkinCache";
        }
        
        $resultMap = $this->get_main_query();
        $data = array("header_change" => "-change");
        $this->template->assign($data);
        $this->template->assign('sales_new_cnt', $resultMap['sales_new_cnt']);
        $this->template->assign('sales_total_cnt', $resultMap['sales_total_cnt']);
        $this->template->assign('osc_new_cnt', $resultMap['osc_new_cnt']);
        $this->template->assign('osc_total_cnt', $resultMap['osc_total_cnt']);
        $this->template->assign('zone_list_01', $resultMap['zone_list_01']);
        $this->template->assign('zone_list_02', $resultMap['zone_list_02']);
        $this->template->assign('zone_list_03', $resultMap['zone_list_03']);
        $this->template->assign('zone_list_04', $resultMap['zone_list_04']);
        $this->template->assign('direct_list', $resultMap['direct_list']);
        $this->template->assign('review_list', $resultMap['review_list']);
        
        // 비회원, 아이디자인 off, 관리자가 아닌 경우, cache 파일 정상일 경우
        $chk    = $this->cachemain->check_cache_file();
        if(
            ($chk == '10' && $this->config_system[$mainCachePrn]=='y' && !$this->userInfo['member_seq'] && !$this->designMode && !$this->managerInfo) ||
            ($chk == '10' && $cachepreview=='y')
            ){
                $this->template->define('LAYOUT', $this->skin . '/' . $sSkinPath);
                $this->template->assign('main', true);
                $this->template->print_('LAYOUT');                
        }else{
            $aResult        = $this->_read();
            $category_plan  = $aResult['category_plan'];
            $this->template->assign('category_plan', $category_plan);
            $this->template->assign('main', true);
            $this->print_layout($this->template_path());
        }        
    }
    
	public function loadMain() {
		header("Content-Type: application/json");
		
		echo true;
	}
	
    ## 메인에 노출 필요 데이터 로드
    protected function _read()
    {
        $this->load->model('categorymodel');
        $category_plan = array();
        $query = $this->db->query("select * from fm_category where plan='y' and level=3 order by category_code asc");
        foreach($query->result_array() as $row){
            $childCategoryData = $this->categorymodel->get_list($row['category_code'],array(
                "hide = '0'",
                "plan_main_display != 'n'",
                "level=4",
                "parent_id=" . $row['id']
            ));
            $category_plan[substr($row['category_code'],0,4)] = $childCategoryData;
        }
        return array('category_plan' => $category_plan);
    }
    
    ## 메인에 노출 필요 데이터 로드
    protected function _cache()
    {
        $this->load->library('cachemain');
        $aResult        = $this->_read();
        $aTmp           = $this->cachemain->main_cache($aResult);                
        $sPrintLayoutPath   = $aTmp[0];
        $aMessage           = $aTmp[1];
        $sCachedPath        = $aTmp[2];
        $sTmpCachedPath     = $aTmp[3];
        $aAutoDisplays      = $aTmp[4];
        $aAutoPopups        = $aTmp[5];
        
        if( $sPrintLayoutPath ){
            ob_start();
            $category_plan  = $aResult['category_plan'];
            $this->template->assign('category_plan', $category_plan);
            $this->template->assign('main', true);
            $this->print_layout($sPrintLayoutPath);
            $cache_contents	= ob_get_contents();
            ob_end_clean();
            if($aAutoDisplays){
                foreach($aAutoDisplays as $iSeq){
                    $sSource    = "{=showDesignDisplay(".$iSeq.")}";
                    $sTarget    = "[[[=showDesignDisplay(".$iSeq.")]]]";
                    $cache_contents = str_replace($sTarget, $sSource, $cache_contents);
                }
            }
            if($aAutoPopups){
                foreach($aAutoPopups as $iSeq){
                    $sSource    = "{=showDesignPopup(".$iSeq.")}";
                    $sTarget    = "[[[=showDesignPopup(".$iSeq.")]]]";
                    $cache_contents = str_replace($sTarget, $sSource, $cache_contents);
                }
            }
            $this->cachemain->set_cache_file($sCachedPath);
            $this->cachemain->make_file($cache_contents);
            $this->cachemain->set_cache_file($sTmpCachedPath);
            if( is_file( $this->cachemain->cache_full_path ) ){
                $this->cachemain->del_file();
            }
        }
        echo implode('', $aMessage);
    }
    
    ## 메인 분기
    public function index()
    {
        $sCreateCached  = $this->input->get('createCached');        
        $sPreviewSkin  = $this->input->get('previewSkin');        
       
        /* 미리보기 스킨 세션처리 */
        if(count($this->uri->segments) == 0){
            /* 미리보기 스킨 세션처리 */
            if($sPreviewSkin){
                setcookie('previewSkin', $_GET['previewSkin'], 0, '/');
                set_cookie(array(
                    'name'   => 'setDesignMode',
                    'value'  => false,
                    'path'   => '/'
                ));
            }elseif($_COOKIE['previewSkin']){
                $this->load->helper("cookie");
                delete_cookie('previewSkin');
                setcookie('previewSkin', '', 0, '/');
            }
            if($sPreviewSkin || $_COOKIE['previewSkin']){
                if($_SERVER['QUERY_STRING']){
                    redirect("main/index?".$_SERVER['QUERY_STRING']);
                }else{
                    // 검색엔진 최적화를 위해 (http://webmastertool.naver.com/guide/basic_optimize.naver#chapter4.2)
                    redirect("main/index", "auto", 301);
                }            
            }
        }
        
        if( $sCreateCached == 1 ){ // 캐쉬 생성
            $this->_cache();
        }else{
            $this->_index();
        }
    }	

	public function blank()
	{
		unset($this);
		echo '<html lang="ko">';
		echo '<head><title>blank</title></head>';
		echo '</html>';
		exit;
	}
    
    public function viewGoodsDisplayCache()
    {
        $aGetParams     = $this->input->get();
        $display_seq    = $aGetParams['display_seq'];
        $display_tab_index    = $aGetParams['display_tab_index'];
        $perpage    = $aGetParams['perpage'];
        $kind       = $aGetParams['kind'];
        $this->load->model('goodsdisplay');
        $aData = $this->goodsdisplay->get_display($display_seq);
        if($aData['platform'] == 'fammerce'){
            $this->skin = $this->config_system['fammerceSkin'];
            $this->fammerceMode = true;
        }else if($aData['platform'] == 'mobile'){
            $this->skin = $this->config_system['mobileSkin'];
            $this->mobileMode = true;
        }
        
        echo '<link rel="stylesheet" type="text/css" href="/data/skin/'.$this->skin.'/css/style.css" />';
        $this->template->include_('showDesignDisplay');
        showDesignDisplay($display_seq, $perpage, $kind);
    }
    
    private function get_main_query() {
        $resultMap = array();
        
        $query = "select count(*) as new_cnt from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_kind c, fm_cm_machine_model d, fm_cm_machine_manufacturer e ".
                 "where a.sales_seq = b.sales_seq and b.kind_seq = c.kind_seq and b.model_seq = d.model_seq and b.mnf_seq = e.mnf_seq ".
                 "and state in('승인', '미승인', '보류', '입금대기', '계약대기') and sales_date > date_add(now(),interval - 1 day)";
        $query = $this->db->query($query);
        $result = $query->row();
        $resultMap['sales_new_cnt'] = $result->new_cnt;

        $query = "select count(*) as total_cnt from fm_cm_machine_sales a, fm_cm_machine_sales_info b, fm_cm_machine_kind c, fm_cm_machine_model d, fm_cm_machine_manufacturer e ".
                 "where a.sales_seq = b.sales_seq and b.kind_seq = c.kind_seq and b.model_seq = d.model_seq and b.mnf_seq = e.mnf_seq and state in('승인', '미승인', '보류', '입금대기', '계약대기')";
        $query = $this->db->query($query);
        $result = $query->row();
        $resultMap['sales_total_cnt'] = $result->total_cnt;
        
        $query = "select count(*) as new_cnt from fm_cm_machine_outsourcing where state = 1 and reg_date > date_add(now(),interval - 1 day)";
        $query = $this->db->query($query);
        $result = $query->row();
        $resultMap['osc_new_cnt'] = $result->new_cnt;
        
        $query = "select count(*) as total_cnt from fm_cm_machine_outsourcing";
        $query = $this->db->query($query);
        $result = $query->row();
        $resultMap['osc_total_cnt'] = $result->total_cnt;
        
        $query = "select min(x.sort_price) as min_price, x.* from fm_cm_machine_sales_info A, (select a.sort_price, b.kind_name, f.model_name, g.mnf_name, a.model_year, e.sales_date, c.area_name, d.path, e.type, a.info_seq ".
                 "from fm_cm_machine_sales_info a, fm_cm_machine_kind b, fm_cm_machine_area c, fm_cm_machine_sales_picture d, fm_cm_machine_sales e, fm_cm_machine_model f, fm_cm_machine_manufacturer g where a.kind_seq = b.kind_seq ".
                 "and a.area_seq = c.area_seq and a.info_seq = d.info_seq and a.sales_seq = e.sales_seq and a.model_seq = f.model_seq and a.mnf_seq = g.mnf_seq and d.sort = 2 and a.sort_price != 0 and a.sort_price is not null and ".
                 "e.type != 'direct' and a.state = '승인' and a.test_yn = 'n' order by a.sort_price asc LIMIT 18446744073709551615) as x group by kind_name order by min(x.sort_price) asc limit 25";
        $query = $this->db->query($query);
        $result = $query->result_array();
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
        $resultMap['zone_list_01'] = $result;
        
        $query = "select * from fm_cm_machine_sales_info a, fm_cm_machine_kind b, fm_cm_machine_area c, fm_cm_machine_sales_picture d, fm_cm_machine_sales e, fm_cm_machine_model f, fm_cm_machine_manufacturer g ".
            "where a.kind_seq = b.kind_seq and a.area_seq = c.area_seq and a.info_seq = d.info_seq and a.sales_seq = e.sales_seq and a.model_seq = f.model_seq and a.mnf_seq = g.mnf_seq ".
            "and d.sort = 2 and a.sort_price != 0 and a.sort_price is not null and e.type != 'direct' and a.state = '승인' and a.test_yn = 'n' order by sales_date desc limit 20";
        $query = $this->db->query($query);
        $result = $query->result_array();
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
        $resultMap['zone_list_02'] = $result;
        
        $query = "select * from fm_cm_machine_outsourcing a, fm_cm_machine_area b where a.area_seq = b.area_seq order by reg_date desc limit 20";
        $query = $this->db->query($query);
        $result = $query->result_array();
        
        foreach($result as &$row) {
            $osc_tech = $row['osc_tech'];
            if(!empty($osc_tech)) {
                $tech_list = explode(',', $osc_tech);
                $row['tech_list'] = $tech_list; 
            }
            $query2 = "select count(*) as apply_cnt from fm_cm_machine_partner_osc where osc_seq = ".$row['osc_seq'];
            $query2 = $this->db->query($query2);
            $result2 = $query2->row_array();
            $row['apply_cnt'] = $result2['apply_cnt'];
        }
        $resultMap['zone_list_03'] = $result;
        
        $query = "select * from fm_cm_machine_partner a, fm_cm_machine_area b where a.area_seq = b.area_seq ".
                 "order by (select COALESCE(convert(avg(grade), signed integer), 0) as grade from fm_cm_machine_partner_eval where partner_seq = a.partner_seq) desc limit 20";
        $query = $this->db->query($query);
        $result = $query->result_array();
        
        foreach($result as &$row) {
            $query2 = "select COALESCE(convert(avg(grade), signed integer), 0) as grade, count(*) as eval_cnt from fm_cm_machine_partner_eval where partner_seq = ".$row['partner_seq'];
            $query2 = $this->db->query($query2);
            $result2 = $query2->row_array();
            $row['grade'] = $result2['grade'];
            $row['eval_cnt'] = $result2['eval_cnt'];
            
            $query2 = "select count(*) as osc_cnt from fm_cm_machine_partner_osc where partner_seq = ".$row['partner_seq'];
            $query2 = $this->db->query($query2);
            $result2 = $query2->row_array();
            $row['osc_cnt'] = $result2['osc_cnt'];
            
            $query2 = "select * from fm_cm_machine_partner_certificate where partner_seq = ".$row['partner_seq'];
            $query2 = $this->db->query($query2);
            $cert_list = $query2->result_array();
            $row['cert_list'] = $cert_list;
        }
        $resultMap['zone_list_04'] = $result;
        
        $query = "select * from fm_cm_machine_sales_info a, fm_cm_machine_kind b, fm_cm_machine_area c, fm_cm_machine_sales_picture d, fm_cm_machine_sales e, fm_cm_machine_model f, fm_cm_machine_manufacturer g ".
            "where a.kind_seq = b.kind_seq and a.area_seq = c.area_seq and a.info_seq = d.info_seq and a.sales_seq = e.sales_seq and a.model_seq = f.model_seq and a.mnf_seq = g.mnf_seq ".
            "and d.sort = 2 and a.sort_price != 0 and a.sort_price is not null and e.type = 'direct' and a.test_yn = 'n' order by sort_price asc limit 20";
        $query = $this->db->query($query);
        $result = $query->result_array();
        $resultMap['direct_list'] = $result;
        
        $query = "select * from fm_cm_machine_sales_review order by reg_date desc limit 30";
        $query = $this->db->query($query);
        $result = $query->result_array();
        $resultMap['review_list'] = $result;
        
        return $resultMap;
    }
    
    
    
}