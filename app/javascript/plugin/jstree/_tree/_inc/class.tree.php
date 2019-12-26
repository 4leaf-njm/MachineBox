<?php
class _tree_struct {
	// Structure table and fields
	protected $m_sTable		= '';
	protected $m_sCodeField	= '';
	protected $m_aFields	= array(
		"id"		=> "id",
		"parent_id"	=> "parent_id",
		"position"	=> "position",
		"left"		=> "left",
		"right"		=> "right",
		"level"		=> "level"
	);
	protected $m_aError	= array(
		'link'			=>'연결 에러',
		'code'			=>'코드 에러',
		'tree'			=>'위치 에러',
		'max'			=>'최대 4차까지 가능합니다'
	);
	public $m_sMsg		= '';

	// Constructor
	public function __construct($table, $fields)
	{
		$this->m_sTable = $table;
		foreach($fields as $sField => $sfieldVal) {
			$this->m_aFields[$sField]	= $sField;
			$this->m_sCodeField			= $sField;
		}
	}

	public function _get_node($id) {
		$this->jstreedb->query("SELECT `".implode("` , `", $this->m_aFields)."` FROM `".$this->m_sTable."` WHERE `".$this->m_aFields["id"]."` = ".(int) $id);
		$this->jstreedb->nextr();
		return $this->jstreedb->nf() === 0 ? false : $this->jstreedb->get_row("assoc");
	}
	public function _get_children($id, $recursive = false) {
		$children = array();
		if($recursive) {
			$node = $this->_get_node($id);
			$this->jstreedb->query("SELECT `".implode("` , `", $this->m_aFields)."` FROM `".$this->m_sTable."` WHERE `".$this->m_aFields["left"]."` >= ".(int) $node[$this->m_aFields["left"]]." AND `".$this->m_aFields["right"]."` <= ".(int) $node[$this->m_aFields["right"]]." ORDER BY `".$this->m_aFields["left"]."` ASC");
		}
		else {
			$this->jstreedb->query("SELECT `".implode("` , `", $this->m_aFields)."` FROM `".$this->m_sTable."` WHERE `".$this->m_aFields["parent_id"]."` = ".(int) $id." ORDER BY `".$this->m_aFields["position"]."` ASC");
		}
		while($this->jstreedb->nextr()) $children[$this->jstreedb->f($this->m_aFields["id"])] = $this->jstreedb->get_row("assoc");
		return $children;
	}
	public function _get_path($id) {
		$node = $this->_get_node($id);
		$path = array();
		if(!$node === false) return false;
		$this->jstreedb->query("SELECT `".implode("` , `", $this->m_aFields)."` FROM `".$this->m_sTable."` WHERE `".$this->m_aFields["left"]."` <= ".(int) $node[$this->m_aFields["left"]]." AND `".$this->m_aFields["right"]."` >= ".(int) $node[$this->m_aFields["right"]]);
		while($this->jstreedb->nextr()) $path[$this->jstreedb->f($this->m_aFields["id"])] = $this->jstreedb->get_row("assoc");
		return $path;
	}

	public function _create($parent, $position) {
		return $this->_move(0, $parent, $position);
	}
	public function _remove($id) {
		if((int)$id === 1) { return false; }
		$data = $this->_get_node($id);
		$lft = (int)$data[$this->m_aFields["left"]];
		$rgt = (int)$data[$this->m_aFields["right"]];
		$dif = $rgt - $lft + 1;

		// deleting node and its children
		$this->jstreedb->query("" .
			"DELETE FROM `".$this->m_sTable."` " .
			"WHERE `".$this->m_aFields["left"]."` >= ".$lft." AND `".$this->m_aFields["right"]."` <= ".$rgt
		, __FUNCTION__);
		// shift left indexes of nodes right of the node
		$this->jstreedb->query("".
			"UPDATE `".$this->m_sTable."` " .
				"SET `".$this->m_aFields["left"]."` = `".$this->m_aFields["left"]."` - ".$dif." " .
			"WHERE `".$this->m_aFields["left"]."` > ".$rgt
		, __FUNCTION__);
		// shift right indexes of nodes right of the node and the node's parents
		$this->jstreedb->query("" .
			"UPDATE `".$this->m_sTable."` " .
				"SET `".$this->m_aFields["right"]."` = `".$this->m_aFields["right"]."` - ".$dif." " .
			"WHERE `".$this->m_aFields["right"]."` > ".$lft
		, __FUNCTION__);

		$pid = (int)$data[$this->m_aFields["parent_id"]];
		$pos = (int)$data[$this->m_aFields["position"]];

		// Update position of siblings below the deleted node
		$this->jstreedb->query("" .
			"UPDATE `".$this->m_sTable."` " .
				"SET `".$this->m_aFields["position"]."` = `".$this->m_aFields["position"]."` - 1 " .
			"WHERE `".$this->m_aFields["parent_id"]."` = ".$pid." AND `".$this->m_aFields["position"]."` > ".$pos
		, __FUNCTION__);

		return true;
	}
	public function _move($id, $ref_id, $position = 0, $is_copy = false) {
		if((int)$ref_id === 0 || (int)$id === 1) { return false; }
		$sql		= array();						// Queries executed at the end
		$node		= $this->_get_node($id);		// Node data
		$nchildren	= $this->_get_children($id);	// Node children
		$ref_node	= $this->_get_node($ref_id);	// Ref node data
		$rchildren	= $this->_get_children($ref_id);// Ref node children

		/* 이동후의 뎁스 : 4뎁스까지만 허용 */
		if($node !== false && $is_copy == false) {
			$depth	= $this->get_depth($ref_id)+$this->get_child_depth($id);
			if($depth > 4){
				if( !$this->m_sMsg ) $this->m_sMsg	= $this->m_aError['max']." [1000]";
				return false;
			}
		}

		$ndif = 2;
		$node_ids = array(-1);
		if($node !== false) {
			$node_ids = array_keys($this->_get_children($id, true));
			// TODO: should be !$is_copy && , but if copied to self - screws some right indexes
			if(in_array($ref_id, $node_ids)) return false;
			$ndif = $node[$this->m_aFields["right"]] - $node[$this->m_aFields["left"]] + 1;
		}
		if($position >= count($rchildren)) {
			$position = count($rchildren);
		}

		// Not creating or copying - old parent is cleaned
		if($node !== false && $is_copy == false) {
			$sql[] = "" .
				"UPDATE `".$this->m_sTable."` " .
					"SET `".$this->m_aFields["position"]."` = `".$this->m_aFields["position"]."` - 1 " .
				"WHERE " .
					"`".$this->m_aFields["parent_id"]."` = ".$node[$this->m_aFields["parent_id"]]." AND " .
					"`".$this->m_aFields["position"]."` > ".$node[$this->m_aFields["position"]];
			$sql[] = "" .
				"UPDATE `".$this->m_sTable."` " .
					"SET `".$this->m_aFields["left"]."` = `".$this->m_aFields["left"]."` - ".$ndif." " .
				"WHERE `".$this->m_aFields["left"]."` > ".$node[$this->m_aFields["right"]];
			$sql[] = "" .
				"UPDATE `".$this->m_sTable."` " .
					"SET `".$this->m_aFields["right"]."` = `".$this->m_aFields["right"]."` - ".$ndif." " .
				"WHERE " .
					"`".$this->m_aFields["right"]."` > ".$node[$this->m_aFields["left"]]." AND " .
					"`".$this->m_aFields["id"]."` NOT IN (".implode(",", $node_ids).") ";
		}
		// Preparing new parent
		$sql[] = "" .
			"UPDATE `".$this->m_sTable."` " .
				"SET `".$this->m_aFields["position"]."` = `".$this->m_aFields["position"]."` + 1 " .
			"WHERE " .
				"`".$this->m_aFields["parent_id"]."` = ".$ref_id." AND " .
				"`".$this->m_aFields["position"]."` >= ".$position." " .
				( $is_copy ? "" : " AND `".$this->m_aFields["id"]."` NOT IN (".implode(",", $node_ids).") ");

		$ref_ind = $ref_id === 0 ? (int)$rchildren[count($rchildren) - 1][$this->m_aFields["right"]] + 1 : (int)$ref_node[$this->m_aFields["right"]];
		$ref_ind = max($ref_ind, 1);

		$self = ($node !== false && !$is_copy && (int)$node[$this->m_aFields["parent_id"]] == $ref_id && $position > $node[$this->m_aFields["position"]]) ? 1 : 0;
		foreach($rchildren as $k => $v) {
			if($v[$this->m_aFields["position"]] - $self == $position) {
				$ref_ind = (int)$v[$this->m_aFields["left"]];
				break;
			}
		}
		if($node !== false && !$is_copy && $node[$this->m_aFields["left"]] < $ref_ind) {
			$ref_ind -= $ndif;
		}

		$sql[] = "" .
			"UPDATE `".$this->m_sTable."` " .
				"SET `".$this->m_aFields["left"]."` = `".$this->m_aFields["left"]."` + ".$ndif." " .
			"WHERE " .
				"`".$this->m_aFields["left"]."` >= ".$ref_ind." " .
				( $is_copy ? "" : " AND `".$this->m_aFields["id"]."` NOT IN (".implode(",", $node_ids).") ");
		$sql[] = "" .
			"UPDATE `".$this->m_sTable."` " .
				"SET `".$this->m_aFields["right"]."` = `".$this->m_aFields["right"]."` + ".$ndif." " .
			"WHERE " .
				"`".$this->m_aFields["right"]."` >= ".$ref_ind." " .
				( $is_copy ? "" : " AND `".$this->m_aFields["id"]."` NOT IN (".implode(",", $node_ids).") ");

		$ldif = $ref_id == 0 ? 0 : $ref_node[$this->m_aFields["level"]] + 1;
		$idif = $ref_ind;

		if($node !== false) {
			$ldif = $node[$this->m_aFields["level"]] - ($ref_node[$this->m_aFields["level"]] + 1);
			$idif = $node[$this->m_aFields["left"]] - $ref_ind;
			if($is_copy) {
				$sql[] = "" .
					"INSERT INTO `".$this->m_sTable."` (" .
						"`".$this->m_aFields["parent_id"]."`, " .
						"`".$this->m_aFields["position"]."`, " .
						"`".$this->m_aFields["left"]."`, " .
						"`".$this->m_aFields["right"]."`, " .
						"`".$this->m_aFields["level"]."`," .
						"`list_default_sort`," .
						"`list_style`," .
						"`list_count_w`," .
						"`list_count_h`," .
						"`list_image_size`," .
						"`list_text_align`," .
						"`list_image_decorations`," .
						"`list_info_settings`" .
					") " .
						"SELECT " .
							"".$ref_id.", " .
							"`".$this->m_aFields["position"]."`, " .
							"`".$this->m_aFields["left"]."` - (".($idif + ($node[$this->m_aFields["left"]] >= $ref_ind ? $ndif : 0))."), " .
							"`".$this->m_aFields["right"]."` - (".($idif + ($node[$this->m_aFields["left"]] >= $ref_ind ? $ndif : 0))."), " .
							"`".$this->m_aFields["level"]."` - (".$ldif."), " .
							"`list_default_sort`, " .
							"`list_style`, " .
							"`list_count_w`, " .
							"`list_count_h`, " .
							"`list_image_size`, " .
							"`list_text_align`, " .
							"`list_image_decorations`, " .
							"`list_info_settings` " .
						"FROM `".$this->m_sTable."` " .
						"WHERE " .
							"`".$this->m_aFields["id"]."` IN (".implode(",", $node_ids).") " .
						"ORDER BY `".$this->m_aFields["level"]."` ASC";
			} else {
				$sql[] = "" .
					"UPDATE `".$this->m_sTable."` SET " .
						"`".$this->m_aFields["parent_id"]."` = ".$ref_id.", " .
						"`".$this->m_aFields["position"]."` = ".$position." " .
					"WHERE " .
						"`".$this->m_aFields["id"]."` = ".$id;
				$sql[] = "" .
					"UPDATE `".$this->m_sTable."` SET " .
						"`".$this->m_aFields["left"]."` = `".$this->m_aFields["left"]."` - (".$idif."), " .
						"`".$this->m_aFields["right"]."` = `".$this->m_aFields["right"]."` - (".$idif."), " .
						"`".$this->m_aFields["level"]."` = `".$this->m_aFields["level"]."` - (".$ldif.") " .
					"WHERE " .
						"`".$this->m_aFields["id"]."` IN (".implode(",", $node_ids).") ";
				$result	= $this->move_code($ref_id,$node_ids);
				if(!$result){
					if( !$this->m_sMsg ) $this->m_sMsg	=$this->m_aError['code']." [2000]";
					return false;
				}
			}
		} else {
			$query = "select ".
				"`list_default_sort`, " .
				"`list_style`, " .
				"`list_count_w`, " .
				"`list_count_h`, " .
				"`list_paging_use`, " .
				"`list_image_size`, " .
				"`list_text_align`, " .
				"`list_image_decorations`, " .
				"`list_info_settings` " .
				"from `".$this->m_sTable."` " .
				"where id = '".$ref_id."'";
			$this->jstreedb->query($query, __FUNCTION__);
			$this->jstreedb->nextr();
			$parentListConfig = $this->jstreedb->get_row("assoc");

			$sCode = $this -> get_insert_code($ref_id);
			$sql[] = "" .
				"INSERT INTO `".$this->m_sTable."` (" .
					"`".$this->m_aFields["parent_id"]."`, " .
					"`".$this->m_aFields["position"]."`, " .
					"`".$this->m_aFields["left"]."`, " .
					"`".$this->m_aFields["right"]."`, " .
					"`".$this->m_aFields["level"]."`, " .
					"`list_default_sort`, " .
					"`list_style`, " .
					"`list_count_w`, " .
					"`list_count_h`, " .
					"`list_paging_use`, " .
					"`list_image_size`, " .
					"`list_text_align`, " .
					"`list_image_decorations`, " .
					"`list_info_settings`, " .
					"`".$this->m_sCodeField."`,`regist_date`,`update_date` " .
					") " .
				"VALUES (" .
					$ref_id.", " .
					$position.", " .
					$idif.", " .
					($idif + 1).", " .
					$ldif.", " .
					"'".$parentListConfig["list_default_sort"]."', " .
					"'".$parentListConfig["list_style"]."', " .
					"'".$parentListConfig["list_count_w"]."', " .
					"'".$parentListConfig["list_count_h"]."', " .
					"'".$parentListConfig["list_paging_use"]."', " .
					"'".$parentListConfig["list_image_size"]."', " .
					"'".$parentListConfig["list_text_align"]."', " .
					"'".$parentListConfig["list_image_decorations"]."', " .
					"'".addslashes($parentListConfig["list_info_settings"])."', " .
					"'".$sCode."',now(),now()".
				")";
		}

		foreach($sql as $q){
			$result	= $this->jstreedb->query($q, __FUNCTION__);
			if(!$result){
				if( !$this->m_sMsg ) $this->m_sMsg	=$this->m_aError['tree']." [3000]";
				return false;
			}
		}

		$ind = $this->jstreedb->insert_id();
		if($is_copy){
			$this->_fix_copy($ind, $position);

			$children = $this->_get_children($ind, true);

			foreach($children as $data)
			{
				$code = ($data['parent_id'] == '2')?$this->get_next_code(''):$this->get_insert_code($data['parent_id']);
				$query = "update `".$this->m_sTable."` set category_code='$code',regist_date=now(),update_date=now() where id='".$data['id']."'";
				$result	= $this->jstreedb->query($query, __FUNCTION__);
				if(!$result){
					if( !$this->m_sMsg ) $this->m_sMsg	=$this->m_aError['code']." [2020]";
					return false;
				}
			}

		}
		return $node === false || $is_copy ? $ind : true;
	}

	public function _fix_copy($id, $position) {
		$node = $this->_get_node($id);
		$children = $this->_get_children($id, true);

		$map = array();
		for($i = $node[$this->m_aFields["left"]] + 1; $i < $node[$this->m_aFields["right"]]; $i++) {
			$map[$i] = $id;
		}
		foreach($children as $cid => $child) {
			if((int)$cid == (int)$id) {
				$result	= $this->jstreedb->query("UPDATE `".$this->m_sTable."` SET `".$this->m_aFields["position"]."` = ".$position." WHERE `".$this->m_aFields["id"]."` = ".$cid, __FUNCTION__);
				if(!$result){
					if( !$this->m_sMsg ) $this->m_sMsg	=$this->m_aError['tree']." [3010]";
					return false;
				}
				continue;
			}
			$result	= $this->jstreedb->query("UPDATE `".$this->m_sTable."` SET `".$this->m_aFields["parent_id"]."` = ".$map[(int)$child[$this->m_aFields["left"]]]." WHERE `".$this->m_aFields["id"]."` = ".$cid, __FUNCTION__);
			if(!$result){
				if( !$this->m_sMsg ) $this->m_sMsg	=$this->m_aError['tree']." [3020]";
				return false;
			}
			for($i = $child[$this->m_aFields["left"]] + 1; $i < $child[$this->m_aFields["right"]]; $i++) {
				$map[$i] = $cid;
			}
		}
	}

	public function check_parent_code($code, $parentCode){
		$plen = strlen($parentCode);
		$len = strlen($code);
		if($plen > $len) return false;
		if( substr($code,0,$plen) == $parentCode ) return true;
		return false;
	}

	public function get_next_code($parentCode){
		if( $parentCode ){
			$len = strlen($parentCode)+4;
			$query = "".
					"select max(".$this->m_sCodeField.") max from `".$this->m_sTable."` ".
					"where ".$this->m_sCodeField." like '$parentCode%' and length(".$this->m_sCodeField.")=$len";
		}else{
			$query = "".
					"SELECT max(".$this->m_sCodeField.") max FROM `".$this->m_sTable."` WHERE length(".$this->m_sCodeField.")=4";
			$len = 4;
		}
		$this->jstreedb->query($query, __FUNCTION__);
		$this->jstreedb->nextr();
		$tmp = $this->jstreedb->get_row("assoc");

		if($tmp['max']){
			$tmp['max'] += 1;
			$code = sprintf('%0'.$len.'d',$tmp['max']);
		}else{
			$code = $parentCode.'0001';
		}
		return $code;
	}

	public function move_code($ref_id,$ids){
		$next = $this->get_insert_code($ref_id);
		$query = "SELECT ".$this->m_sCodeField.",id FROM `".$this->m_sTable."` WHERE id in (".implode(',',$ids).") order by ".$this->m_sCodeField." asc";
		$this->jstreedb->query($query, __FUNCTION__);
		$result = $this->jstreedb->get_all("assoc");
		foreach($result as $k => $tmp)
		{
			$old = $tmp[$this->m_sCodeField];
			if($k == 0){
				$n = strlen($next) - 4;
				if( $n==strlen($old)-4 && substr($next,0,$n) == substr($old,0,$n) ) break;
				$code = $next;
			}else{
				if( $arr ){
					foreach($arr as $l=>$v){
						if( $this->check_parent_code($old,$l) ){
							$parentCode = $v;
						}
					}
				}
				$code = $this->get_next_code($parentCode);
			}

			$query = "update `".$this->m_sTable."` set ".$this->m_sCodeField." = '$code' where id = '".$tmp['id']."'";
			$result	= $this->jstreedb->query($query, __FUNCTION__);
			if( !$result ){
				if( !$this->m_sMsg ) $this->m_sMsg = $this->m_aError['code']." [2030]";
				return false;
			}
			$arr[$old] = $code;
		}
		return true;
	}

	public function get_insert_code($ref_id){
		$parentCode = "";
		if($ref_id){
			$query = "SELECT ".$this->m_sCodeField." FROM `".$this->m_sTable."` WHERE id ='$ref_id'";
			$this->jstreedb->query($query);
			$this->jstreedb->nextr();
			$tmp = $this->jstreedb->get_row("assoc");
			$parentCode = $tmp[$this->m_sCodeField];
		}

		return $this->get_next_code($parentCode);
	}

	public function _reconstruct() {
		$this->jstreedb->query("" .
			"CREATE TEMPORARY TABLE `temp_tree` (" .
				"`".$this->m_aFields["id"]."` INTEGER NOT NULL, " .
				"`".$this->m_aFields["parent_id"]."` INTEGER NOT NULL, " .
				"`". $this->m_aFields["position"]."` INTEGER NOT NULL" .
			") type=HEAP"
		);
		$this->jstreedb->query("" .
			"INSERT INTO `temp_tree` " .
				"SELECT " .
					"`".$this->m_aFields["id"]."`, " .
					"`".$this->m_aFields["parent_id"]."`, " .
					"`".$this->m_aFields["position"]."` " .
				"FROM `".$this->m_sTable."`"
		);

		$this->jstreedb->query("" .
			"CREATE TEMPORARY TABLE `temp_stack` (" .
				"`".$this->m_aFields["id"]."` INTEGER NOT NULL, " .
				"`".$this->m_aFields["left"]."` INTEGER, " .
				"`".$this->m_aFields["right"]."` INTEGER, " .
				"`".$this->m_aFields["level"]."` INTEGER, " .
				"`stack_top` INTEGER NOT NULL, " .
				"`".$this->m_aFields["parent_id"]."` INTEGER, " .
				"`".$this->m_aFields["position"]."` INTEGER " .
			") type=HEAP"
		);
		$counter = 2;
		$this->jstreedb->query("SELECT COUNT(*) FROM temp_tree");
		$this->jstreedb->nextr();
		$maxcounter = (int) $this->jstreedb->f(0) * 2;
		$currenttop = 1;
		$this->jstreedb->query("" .
			"INSERT INTO `temp_stack` " .
				"SELECT " .
					"`".$this->m_aFields["id"]."`, " .
					"1, " .
					"NULL, " .
					"0, " .
					"1, " .
					"`".$this->m_aFields["parent_id"]."`, " .
					"`".$this->m_aFields["position"]."` " .
				"FROM `temp_tree` " .
				"WHERE `".$this->m_aFields["parent_id"]."` = 0"
		);
		$this->jstreedb->query("DELETE FROM `temp_tree` WHERE `".$this->m_aFields["parent_id"]."` = 0");

		while ($counter <= $maxcounter) {
			$this->jstreedb->query("" .
				"SELECT " .
					"`temp_tree`.`".$this->m_aFields["id"]."` AS tempmin, " .
					"`temp_tree`.`".$this->m_aFields["parent_id"]."` AS pid, " .
					"`temp_tree`.`".$this->m_aFields["position"]."` AS lid " .
				"FROM `temp_stack`, `temp_tree` " .
				"WHERE " .
					"`temp_stack`.`".$this->m_aFields["id"]."` = `temp_tree`.`".$this->m_aFields["parent_id"]."` AND " .
					"`temp_stack`.`stack_top` = ".$currenttop." " .
				"ORDER BY `temp_tree`.`".$this->m_aFields["position"]."` ASC LIMIT 1"
			);

			if ($this->jstreedb->nextr()) {
				$tmp = $this->jstreedb->f("tempmin");

				$q = "INSERT INTO temp_stack (stack_top, `".$this->m_aFields["id"]."`, `".$this->m_aFields["left"]."`, `".$this->m_aFields["right"]."`, `".$this->m_aFields["level"]."`, `".$this->m_aFields["parent_id"]."`, `".$this->m_aFields["position"]."`) VALUES(".($currenttop + 1).", ".$tmp.", ".$counter.", NULL, ".$currenttop.", ".$this->jstreedb->f("pid").", ".$this->jstreedb->f("lid").")";
				$this->jstreedb->query($q);
				$this->jstreedb->query("DELETE FROM `temp_tree` WHERE `".$this->m_aFields["id"]."` = ".$tmp);
				$counter++;
				$currenttop++;
			}
			else {
				$this->jstreedb->query("" .
					"UPDATE temp_stack SET " .
						"`".$this->m_aFields["right"]."` = ".$counter.", " .
						"`stack_top` = -`stack_top` " .
					"WHERE `stack_top` = ".$currenttop
				);
				$counter++;
				$currenttop--;
			}
		}

		$temp_fields = $this->m_aFields;
		unset($temp_fields["parent_id"]);
		unset($temp_fields["position"]);
		unset($temp_fields["left"]);
		unset($temp_fields["right"]);
		unset($temp_fields["level"]);
		if(count($temp_fields) > 1) {
			$this->jstreedb->query("" .
				"CREATE TEMPORARY TABLE `temp_tree2` " .
					"SELECT `".implode("`, `", $temp_fields)."` FROM `".$this->m_sTable."` "
			);
		}
		$this->jstreedb->query("TRUNCATE TABLE `".$this->m_sTable."`");
		$this->jstreedb->query("" .
			"INSERT INTO ".$this->m_sTable." (" .
					"`".$this->m_aFields["id"]."`, " .
					"`".$this->m_aFields["parent_id"]."`, " .
					"`".$this->m_aFields["position"]."`, " .
					"`".$this->m_aFields["left"]."`, " .
					"`".$this->m_aFields["right"]."`, " .
					"`".$this->m_aFields["level"]."` " .
				") " .
				"SELECT " .
					"`".$this->m_aFields["id"]."`, " .
					"`".$this->m_aFields["parent_id"]."`, " .
					"`".$this->m_aFields["position"]."`, " .
					"`".$this->m_aFields["left"]."`, " .
					"`".$this->m_aFields["right"]."`, " .
					"`".$this->m_aFields["level"]."` " .
				"FROM temp_stack " .
				"ORDER BY `".$this->m_aFields["id"]."`"
		);
		if(count($temp_fields) > 1) {
			$sql = "" .
				"UPDATE `".$this->m_sTable."` v, `temp_tree2` SET v.`".$this->m_aFields["id"]."` = v.`".$this->m_aFields["id"]."` ";
			foreach($temp_fields as $k => $v) {
				if($k == "id") continue;
				$sql .= ", v.`".$v."` = `temp_tree2`.`".$v."` ";
			}
			$sql .= " WHERE v.`".$this->m_aFields["id"]."` = `temp_tree2`.`".$this->m_aFields["id"]."` ";
			$this->jstreedb->query($sql);
		}
	}

	public function _analyze() {
		$report = array();

		$this->jstreedb->query("" .
			"SELECT " .
				"`".$this->m_aFields["left"]."` FROM `".$this->m_sTable."` s " .
			"WHERE " .
				"`".$this->m_aFields["parent_id"]."` = 0 "
		);
		$this->jstreedb->nextr();
		if($this->jstreedb->nf() == 0) {
			$report[] = "[FAIL]\tNo root node.";
		}
		else {
			$report[] = ($this->jstreedb->nf() > 1) ? "[FAIL]\tMore than one root node." : "[OK]\tJust one root node.";
		}
		$report[] = ($this->jstreedb->f(0) != 1) ? "[FAIL]\tRoot node's left index is not 1." : "[OK]\tRoot node's left index is 1.";

		$this->jstreedb->query("" .
			"SELECT " .
				"COUNT(*) FROM `".$this->m_sTable."` s " .
			"WHERE " .
				"`".$this->m_aFields["parent_id"]."` != 0 AND " .
				"(SELECT COUNT(*) FROM `".$this->m_sTable."` WHERE `".$this->m_aFields["id"]."` = s.`".$this->m_aFields["parent_id"]."`) = 0 ");
		$this->jstreedb->nextr();
		$report[] = ($this->jstreedb->f(0) > 0) ? "[FAIL]\tMissing parents." : "[OK]\tNo missing parents.";

		$this->jstreedb->query("SELECT MAX(`".$this->m_aFields["right"]."`) FROM `".$this->m_sTable."`");
		$this->jstreedb->nextr();
		$n = $this->jstreedb->f(0);
		$this->jstreedb->query("SELECT COUNT(*) FROM `".$this->m_sTable."`");
		$this->jstreedb->nextr();
		$c = $this->jstreedb->f(0);
		$report[] = ($n/2 != $c) ? "[FAIL]\tRight index does not match node count." : "[OK]\tRight index matches count.";

		$this->jstreedb->query("" .
			"SELECT COUNT(`".$this->m_aFields["id"]."`) FROM `".$this->m_sTable."` s " .
			"WHERE " .
				"(SELECT COUNT(*) FROM `".$this->m_sTable."` WHERE " .
					"`".$this->m_aFields["right"]."` < s.`".$this->m_aFields["right"]."` AND " .
					"`".$this->m_aFields["left"]."` > s.`".$this->m_aFields["left"]."` AND " .
					"`".$this->m_aFields["level"]."` = s.`".$this->m_aFields["level"]."` + 1" .
				") != " .
				"(SELECT COUNT(*) FROM `".$this->m_sTable."` WHERE " .
					"`".$this->m_aFields["parent_id"]."` = s.`".$this->m_aFields["id"]."`" .
				") "
			);
		$this->jstreedb->nextr();
		$report[] = ($this->jstreedb->f(0) > 0) ? "[FAIL]\tAdjacency and nested set do not match." : "[OK]\tNS and AJ match";

		return implode("<br />",$report);
	}

	public function _dump($output = false) {
		$nodes = array();
		$this->jstreedb->query("SELECT * FROM ".$this->m_sTable." ORDER BY `".$this->m_aFields["left"]."`");
		while($this->jstreedb->nextr()) $nodes[] = $this->jstreedb->get_row("assoc");
		if($output) {
			echo "<pre>";
			foreach($nodes as $node) {
				echo str_repeat("&#160;",(int)$node[$this->m_aFields["level"]] * 2);
				echo $node[$this->m_aFields["id"]]." (".$node[$this->m_aFields["left"]].",".$node[$this->m_aFields["right"]].",".$node[$this->m_aFields["level"]].",".$node[$this->m_aFields["parent_id"]].",".$node[$this->m_aFields["position"]].")<br />";
			}
			echo str_repeat("-",40);
			echo "</pre>";
		}
		return $nodes;
	}
	public function _drop() {
		$this->jstreedb->query("TRUNCATE TABLE `".$this->m_sTable."`");
		$this->jstreedb->query("" .
				"INSERT INTO `".$this->m_sTable."` (" .
					"`".$this->m_aFields["id"]."`, " .
					"`".$this->m_aFields["parent_id"]."`, " .
					"`".$this->m_aFields["position"]."`, " .
					"`".$this->m_aFields["left"]."`, " .
					"`".$this->m_aFields["right"]."`, " .
					"`".$this->m_aFields["title"]."`, " .
					"`".$this->m_aFields["type"]."`, " .
					"`".$this->m_aFields["level"]."` " .
					") " .
				"VALUES (" .
					"2, " .
					"1, " .
					"0, " .
					"1, " .
					"2, " .
					"'쇼핑몰', " .
					"'drive', " .
					"1 ".
				")");
	}

}

class json_tree extends _tree_struct {
	public function __construct($table = "tree", $fields = array(), $add_fields = array("title" => "title", "type" => "type")) {
		parent::__construct($table, $fields);
		$this->m_aFields = array_merge($this->m_aFields, $add_fields);
		$this->add_fields = $add_fields;
	}

	public function create_node($data) {
		$id = parent::_create((int)$data[$this->m_aFields["id"]], (int)$data[$this->m_aFields["position"]]);
		if($id) {
			$data["id"] = $id;
			$result		= $this->set_data($data);
			if(!$result){
				if( !$this->m_sMsg ) $this->m_sMsg	= $this->m_aError['tree']." [3030]";
				return false;
			}
			return  "{ \"status\" : 1, \"id\" : ".(int)$id." }";
		}
		return false;
	}

	public function set_data($data) {
		if(count($this->add_fields) == 0) { return "{ \"status\" : 1 }"; }
		$s = "UPDATE `".$this->m_sTable."` SET `".$this->m_aFields["id"]."` = `".$this->m_aFields["id"]."` ";
		foreach($this->add_fields as $k => $v) {
			if(isset($data[$k]))	$s .= ", `".$this->m_aFields[$v]."` = \"".$this->jstreedb->escape($data[$k])."\" ";
			else					$s .= ", `".$this->m_aFields[$v]."` = `".$this->m_aFields[$v]."` ";
		}
		$s .= "WHERE `".$this->m_aFields["id"]."` = ".(int)$data["id"];
		$result	= $this->jstreedb->query($s);
		if(!$result){
			if( !$this->m_sMsg ) $this->m_sMsg	= $this->m_aError['tree']." [3040]";
			return false;
		}
		return "{ \"status\" : 1 }";
	}

	public function rename_node($data) {
		$result	= $this->set_data($data);
		if(!$result){
			if( !$this->m_sMsg ) $this->m_sMsg	= $this->m_aError['tree']." [3050]";
			return false;
		}
		return $result;
	}

	public function move_node($data) {

		$this->jstreedb->query("select * from `".$this->m_sTable."` where id='{$data["id"]}'", __FUNCTION__);
		$this->jstreedb->nextr();
		$res = $this->jstreedb->get_row("assoc");
		$sOriCode = $res[$this->m_sCodeField];
		if(!$sOriCode){
			if( !$this->m_sMsg ) $this->m_sMsg	= $this->m_aError['code']." [2040]";
			return false;
		}

		$id = parent::_move((int)$data["id"], (int)$data["ref"], (int)$data["position"], (int)$data["copy"]);
		if(!$id){
			if( !$this->m_sMsg ) $this->m_sMsg	= $this->m_aError['tree']." [3060]";
			return false;
		}

		/* 카피 */
		if((int)$data["copy"] && count($this->add_fields)) {

			$this->jstreedb->query("select * from `".$this->m_sTable."` where id='{$id}'", __FUNCTION__);
			$this->jstreedb->nextr();
			$res = $this->jstreedb->get_row("assoc");
			$sCode = $res[$this->m_sCodeField];
			if(!$sCode){
				if( !$this->m_sMsg ) $this->m_sMsg	= $this->m_aError['code']." [2050]";
				return false;
			}

			$ids	= array_keys($this->_get_children($id, true));
			$data	= $this->_get_children((int)$data["id"], true);

			$i = 0;
			foreach($data as $dk => $dv) {

				$s = "UPDATE `".$this->m_sTable."` SET `".$this->m_aFields["id"]."` = `".$this->m_aFields["id"]."` ";
				foreach($this->add_fields as $k => $v) {
					if(isset($dv[$k]))	$s .= ", `".$this->m_aFields[$v]."` = \"".$this->jstreedb->escape($dv[$k])."\" ";
					else				$s .= ", `".$this->m_aFields[$v]."` = `".$this->m_aFields[$v]."` ";
				}
				$s .= "WHERE `".$this->m_aFields["id"]."` = ".$ids[$i];
				$result	= $this->jstreedb->query($s, __FUNCTION__);
				if(!$result){
					if( !$this->m_sMsg ) $this->m_sMsg	= $this->m_aError['tree']." [3070]";
					return false;
				}
				$i++;
			}
		}
		/* 이동 */
		else if(!$data["copy"]){
			$this->jstreedb->query("select * from `".$this->m_sTable."` where id='{$data["id"]}'", __FUNCTION__);
			$this->jstreedb->nextr();
			$res = $this->jstreedb->get_row("assoc");
			$sCode = $res[$this->m_sCodeField];
			if(!$sCode){
				if( !$this->m_sMsg ) $this->m_sMsg	= $this->m_aError['code']." [2060]";
				return false;
			}

			if($sCode != $sOriCode){
				$len	= strlen($sOriCode)+1;
				$query = "update `".$this->m_sTable."` set ".$this->m_sCodeField." = concat('".$sCode."',substring(".$this->m_sCodeField.",".$len.")) where ".$this->m_sCodeField." like '".$sOriCode."%'";
				$result	= $this->jstreedb->query($query);
				if(!$result){
					if( !$this->m_sMsg ) $this->m_sMsg	= $this->m_aError['code']." [2070]";
					return false;
				}
			}
		}

		return "{ \"status\" : 1, \"id\" : ".$id.", \"".$this->m_sCodeField."\" : \"".$sCode."\" }";
	}
	public function get_depth($id){
		$query = $this->jstreedb->query("select * from `".$this->m_sTable."` where id='{$id}'");
		$this->jstreedb->nextr();
		$res = $this->jstreedb->get_row("assoc");
		$level = $res['level'];

		return $level-1;

	}

	public function get_child_depth($id){
		$query = $this->jstreedb->query("select * from `".$this->m_sTable."` where id='".$id."'");
		$this->jstreedb->nextr();
		$res = $this->jstreedb->get_row("assoc");
		$level = $res['level'];
		$sCode = $res[$this->m_sCodeField];

		if( !$sCode || $level< 2 ){
			if( !$this->m_sMsg ) $this->m_sMsg	= $this->m_aError['code']." [2080]";
			return false;
		}

		$query = $this->jstreedb->query("select max(level) as level from `".$this->m_sTable."` where ".$this->m_sCodeField." like '".$sCode."%'");
		$this->jstreedb->nextr();
		$res = $this->jstreedb->get_row("assoc");
		$last_level = $res['level'];
		return $last_level-$level+1;
	}

	public function remove_node($data) {
		$id = parent::_remove((int)$data["id"]);
		if( !$id ){
			if( !$this->m_sMsg ) $this->m_sMsg	= $this->m_aError['tree']." [3080]";
			return false;
		}
		return "{ \"status\" : 1 }";
	}

	public function get_children($data) {
		$tmp = $this->_get_children((int)$data["id"]);
		if((int)$data["id"] === 1 && count($tmp) === 0) {
			$this->_create_default();
			$tmp = $this->_get_children((int)$data["id"]);
		}
		$result = array();
		if((int)$data["id"] === 0) return json_encode($result);
		foreach($tmp as $k => $v) {
			$result[] = array(
				"attr"										=> array("id" => "node_".$k, "rel" => $v[$this->m_aFields["type"]]),
				"data"										=> $v[$this->m_aFields["title"]],
				str_replace('_code','',$this->m_sCodeField)	=> $v[$this->m_aFields[$this->m_sCodeField]],
				"state" 									=> ((int)$v[$this->m_aFields["right"]] - (int)$v[$this->m_aFields["left"]] > 1) ? "closed" : ""
			);
		}
		return json_encode($result);
	}

	public function search($data) {
		$this->jstreedb->query("SELECT `".$this->m_aFields["left"]."`, `".$this->m_aFields["right"]."` FROM `".$this->m_sTable."` WHERE `".$this->m_aFields["title"]."` LIKE '%".$this->jstreedb->escape($data["search_str"])."%'");
		if($this->jstreedb->nf() === 0) return "[]";
		$q = "SELECT DISTINCT `".$this->m_aFields["id"]."` FROM `".$this->m_sTable."` WHERE 0 ";
		while($this->jstreedb->nextr()) {
			$q .= " OR (`".$this->m_aFields["left"]."` < ".(int)$this->jstreedb->f(0)." AND `".$this->m_aFields["right"]."` > ".(int)$this->jstreedb->f(1).") ";
		}
		$result = array();
		$this->jstreedb->query($q);
		while($this->jstreedb->nextr()) { $result[] = "#node_".$this->jstreedb->f(0); }
		return json_encode($result);
	}

	public function _create_default() {
		$this->_drop();
		$this->create_node(array(
			"id" => 2,
			"position" => 1,
			"title" => "미분류",
			"type" => "folder",
			"level" => "2",
			$this->m_sCodeField => "0001"
		));
	}

	public function _get_child_cnt($sCode){
		$query = $this->jstreedb->query("select count(*) as cnt from `".$this->m_sTable."` where category_code like '{$sCode}%' and category_code!='{$sCode}'");
		$this->jstreedb->nextr();
		$res = $this->jstreedb->get_row("assoc");
		return $res['cnt'];
	}

	public function _get_child_code($sCode){
		$result	= false;
		$query	= "select ".$this->m_sCodeField." from ".$this->m_sTable." where ".$this->m_sCodeField." like '".$sCode."%' order by ".$this->m_sCodeField."+0 asc";
		$query = $this->jstreedb->query($query);
		while($this->jstreedb->nextr()){
			$row = $this->jstreedb->get_row("assoc");
			$result[]	= $row[$this->m_sCodeField];
		}
		return $result;
	}

	public function _get_link_cnt($sCode){
		$query = $this->jstreedb->query("select count(*) as cnt from `".$this->m_sTable."_link` where ".$this->m_sCodeField." like '".$sCode."%'");
		$this->jstreedb->nextr();
		$res = $this->jstreedb->get_row("assoc");
		return $res['cnt'];
	}

	public function get_display_seq($sCode){
		$query = "SELECT recommend_display_seq FROM `".$this->m_sTable."` WHERE ".$this->m_sCodeField." like '{$sCode}%'";
		$this->jstreedb->query($query);
		$result = $this->jstreedb->get_all("assoc");
		return $result;
	}

	public function etc_move_node($sOriCodes, $aNewCodes, $conn_id) {
		if(count($sOriCodes) != count($aNewCodes) ){
			if( !$this->m_sMsg ) $this->m_sMsg	= $this->m_aError['link']." [4000]";
			return false;
		}
		
		// 이동 시 상위 depth 새연결 생성
		foreach($sOriCodes as $k => $sOriCode){
			$sNewCode		= $aNewCodes[$k];
			$iNewCodeLen	= strlen($sNewCode);
			if($iNewCodeLen > 4 && $sOriCode){
				for($i=4;$i<$iNewCodeLen;$i+=4){
					$sInsertCode	= substr($sNewCode,0 , $i);
					if(!in_array($sInsertCode, $aNewCodes)){
						$query		= "select goods_seq from ".$this->m_sTable."_link where ".$this->m_sCodeField." = '".$sOriCode."'";
						$rLinkQuery	= mysqli_query($conn_id, $query);
						while( $dataLink	= mysqli_fetch_array($rLinkQuery) ){
							$query			= "replace into `".$this->m_sTable."_link` set ".$this->m_sCodeField."='".$sInsertCode."', goods_seq='".$dataLink['goods_seq']."', regist_date=now(), sort=0, mobile_sort=0";
							$result			= $this->jstreedb->query($query);
							if(!$result){
								if( !$this->m_sMsg ) $this->m_sMsg	= $this->m_aError['link']." [4010]";
								return false;
							}
						}
					}
				}
			}
		}
		
		foreach($sOriCodes as $k => $sOriCode){
			$sNewCode	= $aNewCodes[$k];
			$result		= $this->jstreedb->query("update `".$this->m_sTable."_link` set ".$this->m_sCodeField."='".$sNewCode."' where ".$this->m_sCodeField."='".$sOriCode."'");
			if(!$result){
				if( !$this->m_sMsg ) $this->m_sMsg	= $this->m_aError['link']." [4010]";
				return false;
			}
			if( $this->m_sTable == 'fm_category' ){
				$result		= $this->jstreedb->query("update `".$this->m_sTable."_group` set ".$this->m_sCodeField."='".$sNewCode."' where ".$this->m_sCodeField."='".$sOriCode."'");
				if(!$result){
					if( !$this->m_sMsg ) $this->m_sMsg	= $this->m_aError['link']." [4020]";
					return false;
				}
				$result		= $this->jstreedb->query("update `fm_coupon_issuecategory` set ".$this->m_sCodeField."='".$sNewCode."' where ".$this->m_sCodeField."='".$sOriCode."'");
				if(!$result){
					if( !$this->m_sMsg ) $this->m_sMsg	= $this->m_aError['link']." [4030]";
					return false;
				}
				$result		= $this->jstreedb->query("update `fm_download_issuecategory` set ".$this->m_sCodeField."='".$sNewCode."' where ".$this->m_sCodeField."='".$sOriCode."'");
				if(!$result){
					if( !$this->m_sMsg ) $this->m_sMsg	= $this->m_aError['link']." [4040]";
					return false;
				}
				$result		= $this->jstreedb->query("update `fm_event_choice` set ".$this->m_sCodeField."='".$sNewCode."' where ".$this->m_sCodeField."='".$sOriCode."'");
				if(!$result){
					if( !$this->m_sMsg ) $this->m_sMsg	= $this->m_aError['link']." [4050]";
					return false;
				}
				$result		= $this->jstreedb->query("update `fm_member_group_issuecategory` set ".$this->m_sCodeField."='".$sNewCode."' where ".$this->m_sCodeField."='".$sOriCode."'");
				if(!$result){
					if( !$this->m_sMsg ) $this->m_sMsg	= $this->m_aError['link']." [4060]";
					return false;
				}
			}
		}
		return true;
	}

	public function etc_remove_node($sOriCode, $aDisplaySeqs) {
		$result		= $this->jstreedb->query("delete from `".$this->m_sTable."_link` where ".$this->m_sCodeField." like '".$sOriCode."%'");
		if(!$result){
			if( !$this->m_sMsg ) $this->m_sMsg	= $this->m_aError['link']." [4070]";
			return false;
		}

		foreach($aDisplaySeqs as $aDataDisplaySeq){
			if( !$aDataDisplaySeq['display_seq'] ) continue;
			$result		= $this->jstreedb->query("DELETE FROM `fm_design_display` WHERE display_seq = '".$aDataDisplaySeq['display_seq']."'");
			if(!$result){
				if( !$this->m_sMsg ) $this->m_sMsg	= $this->m_aError['link']." [4130]";
				return false;
			}
		}

		if( $this->m_sTable == 'fm_category' ){
			$result		= $this->jstreedb->query("delete from `".$this->m_sTable."_group` where ".$this->m_sCodeField." like '".$sOriCode."%'");
			if(!$result){
				if( !$this->m_sMsg ) $this->m_sMsg	= $this->m_aError['link']." [4080]";
				return false;
			}
			$result		= $this->jstreedb->query("delete from `fm_coupon_issuecategory` where ".$this->m_sCodeField." like '".$sOriCode."%'");
			if(!$result){
				if( !$this->m_sMsg ) $this->m_sMsg	= $this->m_aError['link']." [4090]";
				return false;
			}
			$result		= $this->jstreedb->query("delete from `fm_download_issuecategory` where ".$this->m_sCodeField." like '".$sOriCode."%'");
			if(!$result){
				if( !$this->m_sMsg ) $this->m_sMsg	= $this->m_aError['link']." [4100]";
				return false;
			}
			$result		= $this->jstreedb->query("delete from `fm_event_choice` where ".$this->m_sCodeField." like '".$sOriCode."%'");
			if(!$result){
				if( !$this->m_sMsg ) $this->m_sMsg	= $this->m_aError['link']." [4110]";
				return false;
			}
			$result		= $this->jstreedb->query("delete from `fm_member_group_issuecategory` where ".$this->m_sCodeField." like '".$sOriCode."%'");
			if(!$result){
				if( !$this->m_sMsg ) $this->m_sMsg	= $this->m_aError['link']." [4120]";
				return false;
			}
		}

		return true;
	}
}
?>