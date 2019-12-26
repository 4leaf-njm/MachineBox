<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class category extends admin_base {

	public function __construct() {
		parent::__construct();
		$auth = $this->authmodel->manager_limit_act('goods_view');
		if(!$auth){
			pageBack("권한이 없습니다.");
			exit;
		}
	}

	public function index()
	{
		redirect("/admin/category/catalog");
	}

	public function catalog()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();
		$design_ex = "1. EYE-DESIGN 환경으로 이동하세요.<br/>2. EYE-DESIGN을 ON하세요.<br/>3. 카테고리 영역을 클릭하세요.<br/>4. 카테고리 디자인 스타일(가로형, 세로형)을 선택하세요.<br/>※ HTML편집으로 세부 디자인을 수정할 수도 있습니다.";
		$this->template->assign(array('category_design_ex'=>$design_ex));
		$this->template->define(array('tpl'=>$file_path));

		// 웹FTP 템플릿 define
		$this->template->define(array('webftp'=>$this->skin.'/webftp/_webftp.html'));
		$this->template->define(array('mini_webftp'=>$this->skin.'/webftp/_mini_webftp.html'));


		$this->template->print_("tpl");
	}

	public function ifrm_category_info(){
		$this->tempate_modules();
		$this->load->model('categorymodel');

		$categoryCode = $_GET['categoryCode'];

		$categoryData = $this->categorymodel->get_category_data($categoryCode);

		$categoryData['parentcodetext'] = $this->categorymodel->get_category_goods_code($categoryCode,'modify');

		$parentCategoryCode = $this->categorymodel->get_category_code($categoryData["parent_id"]);
		$parentCategoryData = $this->categorymodel->get_category_data($parentCategoryCode);

		// 접속자 제한 부분 :: START 2018-12-26 lwh
		$access_limit = $this->categorymodel->get_category_group_for_member($categoryCode);
		$grp_name			= array();
		$user_type			= array();
		$access_limit_arr	= array();
		foreach($access_limit as $k => $info){
			if($info['group_name'])		$grp_name[]		= $info['group_name'];
			if($info['user_type'])		$user_type[]	= ($info['user_type'] == 'business') ? '기업' : '개인';
		}

		if(count($grp_name) > 0 || count($user_type) > 0){
			if(count($grp_name) > 0){
				$access_limit_arr[]	= implode(',', $grp_name);
			}
			if(count($user_type) > 0){
				$access_limit_arr[]	= implode(',', $user_type);
			}
			$access_limit_txt = implode(' | ', $access_limit_arr);
		}else{
			$access_limit_txt = '모든 사용자';
		}
		$this->template->assign(array('access_limit_txt'=>$access_limit_txt));
		// 접속자 제한 부분 :: END

		/* 회원 그룹 개발시 변경*/
		$groups = "";
		$query	= $this->db->query("select group_seq,group_name from fm_member_group");
		foreach($query->result_array() as $row){
			$groups[] = $row;
		}
		/******************/	

		$this->template->assign(array('operation_type'=>$this->config_system['operation_type']));
		$this->template->assign(array('groups'=>$groups));
		$this->template->assign(array('parentCategoryData'=>$parentCategoryData));
		$this->template->assign(array('categoryCode'=>$categoryCode));
		$this->template->assign(array('categoryData'=>$categoryData));
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	public function ifrm_category_design(){
		$this->tempate_modules();
		$this->load->model('goodsdisplay');
		$this->load->model('categorymodel');
		$this->load->model('designmodel');
		$categoryCode = $_GET['categoryCode'];

		$categoryData = $this->categorymodel->get_category_data($categoryCode);

		$this->template->assign(array(
			'categoryData'					=> $categoryData,
			'categoryCode'					=> $categoryCode

		));
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	public function tree(){
		set_time_limit(0);
		error_reporting(E_ERROR|E_PARSE);
		header("HTTP/1.0 200 OK");
		header('Content-type: application/json; charset=utf-8');
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Pragma: no-cache");

		$aDisplaySeqs	= array();
		$db_config = array(
				"servername"	=> $this->db->hostname,
				"username"		=> $this->db->username,
				"password"		=> $this->db->password,
				"database"		=> $this->db->database
		);

		require_once(APPPATH."/javascript/plugin/jstree/_tree/_inc/class._database.php");
		require_once(APPPATH."/javascript/plugin/jstree/_tree/_inc/class.tree.php");

		$jstree = new json_tree('fm_category', array('category_code'=>'category_code'));
		$jstree->jstreedb = new _database($db_config);

		if(isset($_POST['operation'])){
			$operation	= $_POST['operation'];
			$params		= $_POST;
		}else if(isset($_GET['operation'])){
			$operation	= $_GET['operation'];
			$params		= $_GET;
		}

		/* 관리자 권한 체크 : 시작 */
		if($operation != 'get_children'){
			$auth = $this->authmodel->manager_limit_act('goods_act');
			if( !$auth ){
				echo "{ \"status\" : 0, \"msg\" : \"관리자 권한이 없습니다.\" }";
				die();
			}
		}
		/* 관리자 권한 체크 : 끝 */

		$result	= false;
		$jstree->jstreedb->trans_begin();
		if( $operation=='remove_node' || ($operation=='move_node' && $params['copy']==0) ){
			$data_ori				= $jstree->_get_node($params['id']);
			$sOriCategoryCode		= $data_ori['category_code'];
		}
		if( $operation=='move_node' && $params['copy']==0 ){
			$aOriCategoryCodes	= $jstree->_get_child_code($sOriCategoryCode);
			if(!$aOriCategoryCodes){
				$jstree->jstreedb->trans_rollback();
				die();
			}
		}
		if( $operation=='remove_node' ){
			$aDisplaySeqs	= $jstree->get_display_seq($sOriCategoryCode);
		}
		if( isset($operation) && strpos($operation, "_") !== 0 && method_exists($jstree, $operation) ) {
			$result			= $jstree->{$operation}($params);
		}

		$result_etc	= true;
		if( $result && $operation=='move_node' && $params['copy']==0 ){
			$obj	= json_decode($result);
			if( $obj->category_code && $obj->category_code != $sOriCategoryCode ){
				$link_cnt	= $jstree->_get_link_cnt($sOriCategoryCode);
				if( $link_cnt > 10000 ){
					$jstree->jstreedb->trans_rollback();
					echo "{ \"status\" : 0, \"msg\" : \"연결한 상품수 10,000개 이상 입니다.. \\n이동하시려면 고객센터로 연락하시기 바랍니다.\" }";
					die();
				}
				$aNewCategoryCodes			= $jstree->_get_child_code($obj->category_code);
				$result_etc					= $jstree->etc_move_node($aOriCategoryCodes, $aNewCategoryCodes, $this->db->conn_id);
			}
		}
		if( $operation=='remove_node' && !empty($sOriCategoryCode) ){
			$result_etc					= $jstree->etc_remove_node($sOriCategoryCode, $aDisplaySeqs);
		}
		if( $result && $result_etc ){
			$jstree->jstreedb->trans_commit();
			echo $result;
		}else{
			$jstree->jstreedb->trans_rollback();
			echo "{ \"status\" : 0, \"msg\" : \"".$jstree->m_sMsg."\" }";
		}
	}

	public function view(){
		// 카테고리 코드
		$category = $_POST['category'];
		
		// 카테고리 그룹
		$query = "select a.group_seq,b.group_name from fm_category_group a,fm_member_group b where a.group_seq=b.group_seq and  a.category_code='$category'";
		$query = $this->db->query($query);
		foreach($query->result_array() as $row){
			$groups[] = $row;
		}
		
		// 카테고리 타입
		$types = array();
		$query = "select * from fm_category_group where group_seq is null and  category_code='$category' and user_type != ''";
		$query = $this->db->query($query);
		foreach($query->result_array() as $row){
			$types[] = $row;
		}
		
		// 카테고리 하위포함 상품 수
		$query = "select count(*) cnt from (
			select a.* from fm_category_link as a inner join fm_goods as b on a.goods_seq = b.goods_seq
			where a.category_code like '$category%' group by a.goods_seq
		) as a";
		$query = $this->db->query($query);
		$tmp = $query->result_array();
		$cnt = $tmp[0]['cnt'];
		
		// [반응형스킨] light 버전 추가 및 데이터 가공 추가 :: 2018-12-03 pjw
		$this->load->model('pagemanagermodel');
		$category_config			 = $this->pagemanagermodel->get_page_config('category');
		
		// 카테고리 정보
		$query = "select * from fm_category where category_code ='{$category}'";
		$query = $this->db->query($query);
		foreach($query->result_array() as $row){
			$row['top_html'] = $row['top_html'] == '<p><br></p>' ? '' : $row['top_html'];
			$row['goodsCnt'] = $cnt;
			$row['types'] = $types;
			if($row['category_code'] == $category && isset($groups)){
				$row['groups'] = $groups;
			}
			
			// 사용여부 결정 :: 2018-12-27 lwh
			$row['use_search_filter']  = ($category_config['filter_cnt'] == 0) ? 'N' : 'Y';

			// 검색필터 상세 값 정의 :: 2018-12-27 lwh
			if($row['use_search_filter'] == 'Y') foreach($category_config['filter_col'] as $k => $column){
				foreach($column as $filter_code => $filter_nm){
					if(array_search($filter_code, $category_config['search_filter']) !== false){
						$category_config['search_filter_arr'][$filter_code] = $filter_nm;
					}
				}
			}
			$row['set_search_filter'] = implode(', ', $category_config['search_filter_arr']);

			$result[] = $row;
		}

		echo json_encode($result);
	}

	// [반응형스킨] 카테고리 추천상품 데이터 가져오기 추가 :: 2018-12-14 pjw
	public function recommend_view(){
		$category = $_POST['category'];

		$this->load->model('pagemanagermodel');
		$operation_type				 = $this->config_system['operation_type'];		
		$display_tabs				 = $this->pagemanagermodel->get_recommend_list($operation_type, 'category', $category);		
		
		$this->template->assign(array('display_tabs' => $display_tabs));
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");

	}

	/* 카테고리에 속한 상품 목록(순서정렬에 사용) */
	public function get_category_goods_list(){

		$this->load->model('goodsmodel');
		$this->load->model('categorymodel');

		$categoryCode = $_GET['categoryCode'];
		$page = $_GET['page'] ? $_GET['page'] : 1;
		$perpage = $_GET['perpage'] ? (int)$_GET['perpage'] : 10;

		$categoryData = $this->categorymodel->get_category_data($categoryCode);

		$result = array(
			'totalCnt' => 0,
			'goods' => array()
		);

		$sc=array();
		$sc['sort']				= 'popular';
		$sc['page']				= $page;
		$sc['search_text']		= '';
		$sc['category']			= $categoryCode;
		$sc['perpage']			= $perpage;
		$sc['image_size']		= $categoryData['list_image_size'];
		$sc['get_goods_stock']	= true;
		$list					= $this->goodsmodel->goods_list($sc);
		$result['goods']		= $list['record'];
		$result['page']			= $page;
		$result['perpage']		= $perpage;
		$result['totalCnt']		= $list['page']['totalcount'];
		$result['paging']		= pagingtagjs($page, $list['page']['page'], $list['page']['totalpage'], 'show_next_sortgoods([:PAGE:])');
/*
		$sc=array();
		$sc['sort']				= 'popular';
		$sc['page']				= 1;
		$sc['search_text']		= '';
		$sc['category']			= $categoryCode;
		$sc['perpage']			= 10000;
		$sc['image_size']		= $categoryData['list_image_size'];
		$list	= $this->goodsmodel->goods_list($sc);
		$result['totalCnt'] = count($list['record']);
*/
		echo json_encode($result);
	}

	/* 카테고리페이지 한꺼번에 꾸미기 */
	public function batch_design_setting(){
		$this->admin_menu();
		$this->tempate_modules();

		$this->load->model('goodsdisplay');
		$this->load->model('categorymodel');
		$this->load->helper('design');

		$skinConfiguration = skin_configuration($this->designWorkingSkin);

		$this->template->assign(array(
			'auto_orders'					=> $this->goodsdisplay->auto_orders,
			'orders'						=> $this->goodsdisplay->orders,
			'skinVersion'					=> $this->workingMobileSkinVersion
		));
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}
}

/* End of file category.php */
/* Location: ./app/controllers/admin/category.php */