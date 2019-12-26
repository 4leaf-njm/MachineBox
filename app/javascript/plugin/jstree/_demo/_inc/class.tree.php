<?php
class _tree_struct {
	// Structure table and fields
	protected $table	= "";
	protected $fields	= array(
			"id"		=> false,
			"parent_id"	=> false,
			"position"	=> false,
			"left"		=> false,
			"right"		=> false,
			"level"		=> false,
			"category_code" => false
		);

	// Constructor
	function __construct($table = "tree", $fields = array()) {
		$this->table = "fm_category";
		if(!count($fields)) {
			foreach($this->fields as $k => &$v) { $v = $k; }
		}
		else {
			foreach($fields as $key => $field) {
				switch($key) {
					case "id":
					case "parent_id":
					case "position":
					case "left":
					case "right":
					case "level":
						$this->fields[$key] = $field;
						break;
				}
			}
		}
	}

	function _get_node($id) {
		$this->jstreedb->query("SELECT `".implode("` , `", $this->fields)."` FROM `".$this->table."` WHERE `".$this->fields["id"]."` = ".(int) $id);
		$this->jstreedb->nextr();
		return $this->jstreedb->nf() === 0 ? false : $this->jstreedb->get_row("assoc");
	}
	function _get_children($id, $recursive = false) {
		$children = array();
		if($recursive) {
			$node = $this->_get_node($id);
			$this->jstreedb->query("SELECT `".implode("` , `", $this->fields)."` FROM `".$this->table."` WHERE `".$this->fields["left"]."` >= ".(int) $node[$this->fields["left"]]." AND `".$this->fields["right"]."` <= ".(int) $node[$this->fields["right"]]." ORDER BY `".$this->fields["left"]."` ASC");
		}
		else {
			$this->jstreedb->query("SELECT `".implode("` , `", $this->fields)."` FROM `".$this->table."` WHERE `".$this->fields["parent_id"]."` = ".(int) $id." ORDER BY `".$this->fields["position"]."` ASC");
		}
		while($this->jstreedb->nextr()) $children[$this->jstreedb->f($this->fields["id"])] = $this->jstreedb->get_row("assoc");
		return $children;
	}
	function _get_path($id) {
		$node = $this->_get_node($id);
		$path = array();
		if(!$node === false) return false;
		$this->jstreedb->query("SELECT `".implode("` , `", $this->fields)."` FROM `".$this->table."` WHERE `".$this->fields["left"]."` <= ".(int) $node[$this->fields["left"]]." AND `".$this->fields["right"]."` >= ".(int) $node[$this->fields["right"]]);
		while($this->jstreedb->nextr()) $path[$this->jstreedb->f($this->fields["id"])] = $this->jstreedb->get_row("assoc");
		return $path;
	}

	function _create($parent, $position) {
		return $this->_move(0, $parent, $position);
	}
	function _remove($id) {
		if((int)$id === 1) { return false; }
		$data = $this->_get_node($id);
		$lft = (int)$data[$this->fields["left"]];
		$rgt = (int)$data[$this->fields["right"]];
		$dif = $rgt - $lft + 1;

		// deleting node and its children
		$this->jstreedb->query("" .
			"DELETE FROM `".$this->table."` " .
			"WHERE `".$this->fields["left"]."` >= ".$lft." AND `".$this->fields["right"]."` <= ".$rgt
		);
		// shift left indexes of nodes right of the node
		$this->jstreedb->query("".
			"UPDATE `".$this->table."` " .
				"SET `".$this->fields["left"]."` = `".$this->fields["left"]."` - ".$dif." " .
			"WHERE `".$this->fields["left"]."` > ".$rgt
		);
		// shift right indexes of nodes right of the node and the node's parents
		$this->jstreedb->query("" .
			"UPDATE `".$this->table."` " .
				"SET `".$this->fields["right"]."` = `".$this->fields["right"]."` - ".$dif." " .
			"WHERE `".$this->fields["right"]."` > ".$lft
		);

		$pid = (int)$data[$this->fields["parent_id"]];
		$pos = (int)$data[$this->fields["position"]];

		// Update position of siblings below the deleted node
		$this->jstreedb->query("" .
			"UPDATE `".$this->table."` " .
				"SET `".$this->fields["position"]."` = `".$this->fields["position"]."` - 1 " .
			"WHERE `".$this->fields["parent_id"]."` = ".$pid." AND `".$this->fields["position"]."` > ".$pos
		);
		return true;
	}
	function _move($id, $ref_id, $position = 0, $is_copy = false) {
		if((int)$ref_id === 0 || (int)$id === 1) { return false; }
		$sql		= array();						// Queries executed at the end
		$node		= $this->_get_node($id);		// Node data
		$nchildren	= $this->_get_children($id);	// Node children
		$ref_node	= $this->_get_node($ref_id);	// Ref node data
		$rchildren	= $this->_get_children($ref_id);// Ref node children

		$ndif = 2;
		$node_ids = array(-1);
		if($node !== false) {
			$node_ids = array_keys($this->_get_children($id, true));
			// TODO: should be !$is_copy && , but if copied to self - screws some right indexes
			if(in_array($ref_id, $node_ids)) return false;
			$ndif = $node[$this->fields["right"]] - $node[$this->fields["left"]] + 1;
		}
		if($position >= count($rchildren)) {
			$position = count($rchildren);
		}

		// Not creating or copying - old parent is cleaned
		if($node !== false && $is_copy == false) {
			$sql[] = "" .
				"UPDATE `".$this->table."` " .
					"SET `".$this->fields["position"]."` = `".$this->fields["position"]."` - 1 " .
				"WHERE " .
					"`".$this->fields["parent_id"]."` = ".$node[$this->fields["parent_id"]]." AND " .
					"`".$this->fields["position"]."` > ".$node[$this->fields["position"]];
			$sql[] = "" .
				"UPDATE `".$this->table."` " .
					"SET `".$this->fields["left"]."` = `".$this->fields["left"]."` - ".$ndif." " .
				"WHERE `".$this->fields["left"]."` > ".$node[$this->fields["right"]];
			$sql[] = "" .
				"UPDATE `".$this->table."` " .
					"SET `".$this->fields["right"]."` = `".$this->fields["right"]."` - ".$ndif." " .
				"WHERE " .
					"`".$this->fields["right"]."` > ".$node[$this->fields["left"]]." AND " .
					"`".$this->fields["id"]."` NOT IN (".implode(",", $node_ids).") ";
		}
		// Preparing new parent
		$sql[] = "" .
			"UPDATE `".$this->table."` " .
				"SET `".$this->fields["position"]."` = `".$this->fields["position"]."` + 1 " .
			"WHERE " .
				"`".$this->fields["parent_id"]."` = ".$ref_id." AND " .
				"`".$this->fields["position"]."` >= ".$position." " .
				( $is_copy ? "" : " AND `".$this->fields["id"]."` NOT IN (".implode(",", $node_ids).") ");

		$ref_ind = $ref_id === 0 ? (int)$rchildren[count($rchildren) - 1][$this->fields["right"]] + 1 : (int)$ref_node[$this->fields["right"]];
		$ref_ind = max($ref_ind, 1);

		$self = ($node !== false && !$is_copy && (int)$node[$this->fields["parent_id"]] == $ref_id && $position > $node[$this->fields["position"]]) ? 1 : 0;
		foreach($rchildren as $k => $v) {
			if($v[$this->fields["position"]] - $self == $position) {
				$ref_ind = (int)$v[$this->fields["left"]];
				break;
			}
		}
		if($node !== false && !$is_copy && $node[$this->fields["left"]] < $ref_ind) {
			$ref_ind -= $ndif;
		}

		$sql[] = "" .
			"UPDATE `".$this->table."` " .
				"SET `".$this->fields["left"]."` = `".$this->fields["left"]."` + ".$ndif." " .
			"WHERE " .
				"`".$this->fields["left"]."` >= ".$ref_ind." " .
				( $is_copy ? "" : " AND `".$this->fields["id"]."` NOT IN (".implode(",", $node_ids).") ");
		$sql[] = "" .
			"UPDATE `".$this->table."` " .
				"SET `".$this->fields["right"]."` = `".$this->fields["right"]."` + ".$ndif." " .
			"WHERE " .
				"`".$this->fields["right"]."` >= ".$ref_ind." " .
				( $is_copy ? "" : " AND `".$this->fields["id"]."` NOT IN (".implode(",", $node_ids).") ");

		$ldif = $ref_id == 0 ? 0 : $ref_node[$this->fields["level"]] + 1;
		$idif = $ref_ind;

		$v = var_export($_POST, true);
		$fp = fopen('/www/lks/data/debug.txt','w');
		fwrite($fp,$v);
		fclose($fp);
		@chown('/www/lks/data/debug.txt', 'lks');

		if($node !== false) {
			$ldif = $node[$this->fields["level"]] - ($ref_node[$this->fields["level"]] + 1);
			$idif = $node[$this->fields["left"]] - $ref_ind;
			if($is_copy) {
				$sql[] = "" .
					"INSERT INTO `".$this->table."` (" .
						"`".$this->fields["parent_id"]."`, " .
						"`".$this->fields["position"]."`, " .
						"`".$this->fields["left"]."`, " .
						"`".$this->fields["right"]."`, " .
						"`".$this->fields["level"]."`" .
					") " .
						"SELECT " .
							"".$ref_id.", " .
							"`".$this->fields["position"]."`, " .
							"`".$this->fields["left"]."` - (".($idif + ($node[$this->fields["left"]] >= $ref_ind ? $ndif : 0))."), " .
							"`".$this->fields["right"]."` - (".($idif + ($node[$this->fields["left"]] >= $ref_ind ? $ndif : 0))."), " .
							"`".$this->fields["level"]."` - (".$ldif.") " .
						"FROM `".$this->table."` " .
						"WHERE " .
							"`".$this->fields["id"]."` IN (".implode(",", $node_ids).") " .
						"ORDER BY `".$this->fields["level"]."` ASC";
			} else {
				$sql[] = "" .
					"UPDATE `".$this->table."` SET " .
						"`".$this->fields["parent_id"]."` = ".$ref_id.", " .
						"`".$this->fields["position"]."` = ".$position." " .
					"WHERE " .
						"`".$this->fields["id"]."` = ".$id;
				$sql[] = "" .
					"UPDATE `".$this->table."` SET " .
						"`".$this->fields["left"]."` = `".$this->fields["left"]."` - (".$idif."), " .
						"`".$this->fields["right"]."` = `".$this->fields["right"]."` - (".$idif."), " .
						"`".$this->fields["level"]."` = `".$this->fields["level"]."` - (".$ldif.") " .
					"WHERE " .
						"`".$this->fields["id"]."` IN (".implode(",", $node_ids).") ";
				$this->move_code($ref_id,$node_ids);
			}
		} else {
			$category_code = $this -> get_insert_code($ref_id);
			$sql[] = "" .
				"INSERT INTO `".$this->table."` (" .
					"`".$this->fields["parent_id"]."`, " .
					"`".$this->fields["position"]."`, " .
					"`".$this->fields["left"]."`, " .
					"`".$this->fields["right"]."`, " .
					"`".$this->fields["level"]."`, " .
					"`category_code`,`regist_date`,`update_date` " .
					") " .
				"VALUES (" .
					$ref_id.", " .
					$position.", " .
					$idif.", " .
					($idif + 1).", " .
					$ldif.", " .
					"'".$category_code."',now(),now()".
				")";
		}

		foreach($sql as $q) $this->jstreedb->query($q);
		$ind = $this->jstreedb->insert_id();
		if($is_copy){
			$this->_fix_copy($ind, $position);
			$children = $this->_get_children($ind, true);

			foreach($children as $data)
			{
				$code = ($data['parent_id'] == '2')?$this->get_next_code(''):$this->get_insert_code($data['parent_id']);
				$query = "update fm_category set category_code='$code',regist_date=now(),update_date=now() where id='".$data['id']."'";
				$this->jstreedb->query($query);
			}
		}
		return $node === false || $is_copy ? $ind : true;
	}

	function _fix_copy($id, $position) {
		$node = $this->_get_node($id);
		$children = $this->_get_children($id, true);

		$map = array();
		for($i = $node[$this->fields["left"]] + 1; $i < $node[$this->fields["right"]]; $i++) {
			$map[$i] = $id;
		}
		foreach($children as $cid => $child) {
			if((int)$cid == (int)$id) {
				$this->jstreedb->query("UPDATE `".$this->table."` SET `".$this->fields["position"]."` = ".$position." WHERE `".$this->fields["id"]."` = ".$cid);
				continue;
			}
			$this->jstreedb->query("UPDATE `".$this->table."` SET `".$this->fields["parent_id"]."` = ".$map[(int)$child[$this->fields["left"]]]." WHERE `".$this->fields["id"]."` = ".$cid);
			for($i = $child[$this->fields["left"]] + 1; $i < $child[$this->fields["right"]]; $i++) {
				$map[$i] = $cid;
			}
		}
	}

	function check_parent_code($code,$parentCode){
		$plen = strlen($parentCode);
		$len = strlen($code);
		if($plen > $len) return false;
		if( substr($code,0,$plen) == $parentCode ) return true;
		return false;
	}

	function get_next_code($parentCode){
		if( $parentCode ){
			$len = strlen($parentCode)+4;
			$query = "".
				"select max(category_code) max from `".$this->table."` ".
				"where category_code like '$parentCode%' and length(category_code)=$len";
		}else{
			$query = "".
				"SELECT max(category_code) max FROM `".$this->table."` WHERE length(category_code)=4";
			$len = 4;
		}
		$this->jstreedb->query($query);
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

	function move_code($ref_id,$ids){
		$next = $this->get_insert_code($ref_id);
		$query = "SELECT category_code,id FROM `".$this->table."` WHERE id in (".implode(',',$ids).") order by category_code asc";
		$this->jstreedb->query($query);
		$result = $this->jstreedb->get_all("assoc");
		foreach($result as $k => $tmp)
		{
			$old = $tmp['category_code'];
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

			$query = "update `".$this->table."` set category_code = '$code' where id = '".$tmp['id']."'";
			$this->jstreedb->query($query);

			$arr[$old] = $code;
		}
	}

	function get_insert_code($ref_id){
		$parentCode = "";
		if($ref_id){
			$query = "SELECT category_code FROM `".$this->table."` WHERE id ='$ref_id'";
			$this->jstreedb->query($query);
			$this->jstreedb->nextr();
			$tmp = $this->jstreedb->get_row("assoc");
			$parentCode = $tmp['category_code'];
		}

		return $this->get_next_code($parentCode);
	}

	function _reconstruct() {
		$this->jstreedb->query("" .
			"CREATE TEMPORARY TABLE `temp_tree` (" .
				"`".$this->fields["id"]."` INTEGER NOT NULL, " .
				"`".$this->fields["parent_id"]."` INTEGER NOT NULL, " .
				"`". $this->fields["position"]."` INTEGER NOT NULL" .
			") type=HEAP"
		);
		$this->jstreedb->query("" .
			"INSERT INTO `temp_tree` " .
				"SELECT " .
					"`".$this->fields["id"]."`, " .
					"`".$this->fields["parent_id"]."`, " .
					"`".$this->fields["position"]."` " .
				"FROM `".$this->table."`"
		);

		$this->jstreedb->query("" .
			"CREATE TEMPORARY TABLE `temp_stack` (" .
				"`".$this->fields["id"]."` INTEGER NOT NULL, " .
				"`".$this->fields["left"]."` INTEGER, " .
				"`".$this->fields["right"]."` INTEGER, " .
				"`".$this->fields["level"]."` INTEGER, " .
				"`stack_top` INTEGER NOT NULL, " .
				"`".$this->fields["parent_id"]."` INTEGER, " .
				"`".$this->fields["position"]."` INTEGER " .
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
					"`".$this->fields["id"]."`, " .
					"1, " .
					"NULL, " .
					"0, " .
					"1, " .
					"`".$this->fields["parent_id"]."`, " .
					"`".$this->fields["position"]."` " .
				"FROM `temp_tree` " .
				"WHERE `".$this->fields["parent_id"]."` = 0"
		);
		$this->jstreedb->query("DELETE FROM `temp_tree` WHERE `".$this->fields["parent_id"]."` = 0");

		while ($counter <= $maxcounter) {
			$this->jstreedb->query("" .
				"SELECT " .
					"`temp_tree`.`".$this->fields["id"]."` AS tempmin, " .
					"`temp_tree`.`".$this->fields["parent_id"]."` AS pid, " .
					"`temp_tree`.`".$this->fields["position"]."` AS lid " .
				"FROM `temp_stack`, `temp_tree` " .
				"WHERE " .
					"`temp_stack`.`".$this->fields["id"]."` = `temp_tree`.`".$this->fields["parent_id"]."` AND " .
					"`temp_stack`.`stack_top` = ".$currenttop." " .
				"ORDER BY `temp_tree`.`".$this->fields["position"]."` ASC LIMIT 1"
			);

			if ($this->jstreedb->nextr()) {
				$tmp = $this->jstreedb->f("tempmin");

				$q = "INSERT INTO temp_stack (stack_top, `".$this->fields["id"]."`, `".$this->fields["left"]."`, `".$this->fields["right"]."`, `".$this->fields["level"]."`, `".$this->fields["parent_id"]."`, `".$this->fields["position"]."`) VALUES(".($currenttop + 1).", ".$tmp.", ".$counter.", NULL, ".$currenttop.", ".$this->jstreedb->f("pid").", ".$this->jstreedb->f("lid").")";
				$this->jstreedb->query($q);
				$this->jstreedb->query("DELETE FROM `temp_tree` WHERE `".$this->fields["id"]."` = ".$tmp);
				$counter++;
				$currenttop++;
			}
			else {
				$this->jstreedb->query("" .
					"UPDATE temp_stack SET " .
						"`".$this->fields["right"]."` = ".$counter.", " .
						"`stack_top` = -`stack_top` " .
					"WHERE `stack_top` = ".$currenttop
				);
				$counter++;
				$currenttop--;
			}
		}

		$temp_fields = $this->fields;
		unset($temp_fields["parent_id"]);
		unset($temp_fields["position"]);
		unset($temp_fields["left"]);
		unset($temp_fields["right"]);
		unset($temp_fields["level"]);
		if(count($temp_fields) > 1) {
			$this->jstreedb->query("" .
				"CREATE TEMPORARY TABLE `temp_tree2` " .
					"SELECT `".implode("`, `", $temp_fields)."` FROM `".$this->table."` "
			);
		}
		$this->jstreedb->query("TRUNCATE TABLE `".$this->table."`");
		$this->jstreedb->query("" .
			"INSERT INTO ".$this->table." (" .
					"`".$this->fields["id"]."`, " .
					"`".$this->fields["parent_id"]."`, " .
					"`".$this->fields["position"]."`, " .
					"`".$this->fields["left"]."`, " .
					"`".$this->fields["right"]."`, " .
					"`".$this->fields["level"]."` " .
				") " .
				"SELECT " .
					"`".$this->fields["id"]."`, " .
					"`".$this->fields["parent_id"]."`, " .
					"`".$this->fields["position"]."`, " .
					"`".$this->fields["left"]."`, " .
					"`".$this->fields["right"]."`, " .
					"`".$this->fields["level"]."` " .
				"FROM temp_stack " .
				"ORDER BY `".$this->fields["id"]."`"
		);
		if(count($temp_fields) > 1) {
			$sql = "" .
				"UPDATE `".$this->table."` v, `temp_tree2` SET v.`".$this->fields["id"]."` = v.`".$this->fields["id"]."` ";
			foreach($temp_fields as $k => $v) {
				if($k == "id") continue;
				$sql .= ", v.`".$v."` = `temp_tree2`.`".$v."` ";
			}
			$sql .= " WHERE v.`".$this->fields["id"]."` = `temp_tree2`.`".$this->fields["id"]."` ";
			$this->jstreedb->query($sql);
		}
	}

	function _analyze() {
		$report = array();

		$this->jstreedb->query("" .
			"SELECT " .
				"`".$this->fields["left"]."` FROM `".$this->table."` s " .
			"WHERE " .
				"`".$this->fields["parent_id"]."` = 0 "
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
				"COUNT(*) FROM `".$this->table."` s " .
			"WHERE " .
				"`".$this->fields["parent_id"]."` != 0 AND " .
				"(SELECT COUNT(*) FROM `".$this->table."` WHERE `".$this->fields["id"]."` = s.`".$this->fields["parent_id"]."`) = 0 ");
		$this->jstreedb->nextr();
		$report[] = ($this->jstreedb->f(0) > 0) ? "[FAIL]\tMissing parents." : "[OK]\tNo missing parents.";

		$this->jstreedb->query("SELECT MAX(`".$this->fields["right"]."`) FROM `".$this->table."`");
		$this->jstreedb->nextr();
		$n = $this->jstreedb->f(0);
		$this->jstreedb->query("SELECT COUNT(*) FROM `".$this->table."`");
		$this->jstreedb->nextr();
		$c = $this->jstreedb->f(0);
		$report[] = ($n/2 != $c) ? "[FAIL]\tRight index does not match node count." : "[OK]\tRight index matches count.";

		$this->jstreedb->query("" .
			"SELECT COUNT(`".$this->fields["id"]."`) FROM `".$this->table."` s " .
			"WHERE " .
				"(SELECT COUNT(*) FROM `".$this->table."` WHERE " .
					"`".$this->fields["right"]."` < s.`".$this->fields["right"]."` AND " .
					"`".$this->fields["left"]."` > s.`".$this->fields["left"]."` AND " .
					"`".$this->fields["level"]."` = s.`".$this->fields["level"]."` + 1" .
				") != " .
				"(SELECT COUNT(*) FROM `".$this->table."` WHERE " .
					"`".$this->fields["parent_id"]."` = s.`".$this->fields["id"]."`" .
				") "
			);
		$this->jstreedb->nextr();
		$report[] = ($this->jstreedb->f(0) > 0) ? "[FAIL]\tAdjacency and nested set do not match." : "[OK]\tNS and AJ match";

		return implode("<br />",$report);
	}

	function _dump($output = false) {
		$nodes = array();
		$this->jstreedb->query("SELECT * FROM ".$this->table." ORDER BY `".$this->fields["left"]."`");
		while($this->jstreedb->nextr()) $nodes[] = $this->jstreedb->get_row("assoc");
		if($output) {
			echo "<pre>";
			foreach($nodes as $node) {
				echo str_repeat("&#160;",(int)$node[$this->fields["level"]] * 2);
				echo $node[$this->fields["id"]]." (".$node[$this->fields["left"]].",".$node[$this->fields["right"]].",".$node[$this->fields["level"]].",".$node[$this->fields["parent_id"]].",".$node[$this->fields["position"]].")<br />";
			}
			echo str_repeat("-",40);
			echo "</pre>";
		}
		return $nodes;
	}
	function _drop() {
		$this->jstreedb->query("TRUNCATE TABLE `".$this->table."`");
		$this->jstreedb->query("" .
				"INSERT INTO `".$this->table."` (" .
					"`".$this->fields["id"]."`, " .
					"`".$this->fields["parent_id"]."`, " .
					"`".$this->fields["position"]."`, " .
					"`".$this->fields["left"]."`, " .
					"`".$this->fields["right"]."`, " .
					"`".$this->fields["level"]."` " .
					") " .
				"VALUES (" .
					"1, " .
					"0, " .
					"0, " .
					"1, " .
					"2, " .
					"0 ".
				")");
	}

}

class json_tree extends _tree_struct {
	function __construct($table = "tree", $fields = array(), $add_fields = array("title" => "title", "type" => "type")) {
		parent::__construct($table, $fields);
		$this->fields = array_merge($this->fields, $add_fields);
		$this->add_fields = $add_fields;
	}

	function create_node($data) {
		$id = parent::_create((int)$data[$this->fields["id"]], (int)$data[$this->fields["position"]]);
		if($id) {
			$data["id"] = $id;
			$this->set_data($data);
			return  "{ \"status\" : 1, \"id\" : ".(int)$id." }";
		}
		return "{ \"status\" : 0 }";
	}
	function set_data($data) {
		if(count($this->add_fields) == 0) { return "{ \"status\" : 1 }"; }
		$s = "UPDATE `".$this->table."` SET `".$this->fields["id"]."` = `".$this->fields["id"]."` ";
		foreach($this->add_fields as $k => $v) {
			if(isset($data[$k]))	$s .= ", `".$this->fields[$v]."` = \"".$this->jstreedb->escape($data[$k])."\" ";
			else					$s .= ", `".$this->fields[$v]."` = `".$this->fields[$v]."` ";
		}
		$s .= "WHERE `".$this->fields["id"]."` = ".(int)$data["id"];
		$this->jstreedb->query($s);
		return "{ \"status\" : 1 }";
	}
	function rename_node($data) { return $this->set_data($data); }

	function move_node($data) {
		$id = parent::_move((int)$data["id"], (int)$data["ref"], (int)$data["position"], (int)$data["copy"]);
		if(!$id) return "{ \"status\" : 0 }";
		if((int)$data["copy"] && count($this->add_fields)) {
			$ids	= array_keys($this->_get_children($id, true));
			$data	= $this->_get_children((int)$data["id"], true);

			$i = 0;
			foreach($data as $dk => $dv) {
				$s = "UPDATE `".$this->table."` SET `".$this->fields["id"]."` = `".$this->fields["id"]."` ";
				foreach($this->add_fields as $k => $v) {
					if(isset($dv[$k]))	$s .= ", `".$this->fields[$v]."` = \"".$this->jstreedb->escape($dv[$k])."\" ";
					else				$s .= ", `".$this->fields[$v]."` = `".$this->fields[$v]."` ";
				}
				$s .= "WHERE `".$this->fields["id"]."` = ".$ids[$i];
				$this->jstreedb->query($s);
				$i++;
			}
		}
		return "{ \"status\" : 1, \"id\" : ".$id." }";
	}
	function remove_node($data) {
		$id = parent::_remove((int)$data["id"]);
		return "{ \"status\" : 1 }";
	}
	function get_children($data) {
		$tmp = $this->_get_children((int)$data["id"]);
		if((int)$data["id"] === 1 && count($tmp) === 0) {
			$this->_create_default();
			$tmp = $this->_get_children((int)$data["id"]);
		}
		$result = array();
		if((int)$data["id"] === 0) return json_encode($result);
		foreach($tmp as $k => $v) {
			$result[] = array(
				"attr" => array("id" => "node_".$k, "rel" => $v[$this->fields["type"]]),
				"data" => $v[$this->fields["title"]],
				"category" => $v[$this->fields["category_code"]],
				"state" => ((int)$v[$this->fields["right"]] - (int)$v[$this->fields["left"]] > 1) ? "closed" : ""
			);
		}
		return json_encode($result);
	}
	function search($data) {
		$this->jstreedb->query("SELECT `".$this->fields["left"]."`, `".$this->fields["right"]."` FROM `".$this->table."` WHERE `".$this->fields["title"]."` LIKE '%".$this->jstreedb->escape($data["search_str"])."%'");
		if($this->jstreedb->nf() === 0) return "[]";
		$q = "SELECT DISTINCT `".$this->fields["id"]."` FROM `".$this->table."` WHERE 0 ";
		while($this->jstreedb->nextr()) {
			$q .= " OR (`".$this->fields["left"]."` < ".(int)$this->jstreedb->f(0)." AND `".$this->fields["right"]."` > ".(int)$this->jstreedb->f(1).") ";
		}
		$result = array();
		$this->jstreedb->query($q);
		while($this->jstreedb->nextr()) { $result[] = "#node_".$this->jstreedb->f(0); }
		return json_encode($result);
	}

	function _create_default() {
		$this->_drop();
		$this->create_node(array(
			"id" => 1,
			"position" => 0,
			"title" => "C:",
			"type" => "drive"
		));
		$this->create_node(array(
			"id" => 1,
			"position" => 1,
			"title" => "D:",
			"type" => "drive"
		));
		$this->create_node(array(
			"id" => 2,
			"position" => 0,
			"title" => "_demo",
			"type" => "folder"
		));
		$this->create_node(array(
			"id" => 2,
			"position" => 1,
			"title" => "_docs",
			"type" => "folder"
		));
		$this->create_node(array(
			"id" => 4,
			"position" => 0,
			"title" => "index.html",
			"type" => "default"
		));
		$this->create_node(array(
			"id" => 5,
			"position" => 1,
			"title" => "doc.html",
			"type" => "default"
		));
	}


}

?>