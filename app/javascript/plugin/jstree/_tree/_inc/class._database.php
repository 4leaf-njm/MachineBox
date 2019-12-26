<?php
class _database {
	private $linker		= false;
	private $result		= false;
	private $row		= false;

	public $settings	= array(
			"servername"=> "localhost",
			"serverport"=> "3306",
			"username"	=> false,
			"password"	=> false,
			"database"	=> false,
			"persist"	=> false,
			"dieonerror"=> false,
			"showerror"	=> false,
			"error_file"	=> true,
			"isdebug"=> false
		);

	function __construct($db_config) {
		$this->settings = array_merge($this->settings, $db_config);
		if($this->settings["error_file"] === true) $this->settings["error_file"] = dirname(__FILE__)."/__mysql_errors.log";
		if($this->settings["isdebug"] === true) $this->settings["isdebug"] = dirname(__FILE__)."/__mysql_debug.log";
	}

	function connect() {
		if (!$this->linker) {
			$this->linker = mysqli_connect($this->settings["servername"],	$this->settings["username"], $this->settings["password"], $this->settings["database"]);
		}
		if( !$this->linker ) {
			return false;
		}
		if($this->linker)	$this->linker->query("SET NAMES 'utf8'");
		return ($this->linker) ? true : false;
	}

	function trans_begin(){
		$result	= $this->query('SET AUTOCOMMIT=0');
		if( $result ){
			$this->query('START TRANSACTION');
		}
	}

	function trans_commit(){
		$result	= $this->query('COMMIT');
		if( $result ){
			$this->query('SET AUTOCOMMIT=1');
		}
	}

	function trans_rollback(){
		$result	= $this->query('ROLLBACK');
		if( $result ){
			$this->query('SET AUTOCOMMIT=1');
		}
	}

	function query($sql, $func=null) {
		if (!$this->linker && !$this->connect()) $this->error();

		// Start the Query Timer
		$time_start = microtime(TRUE);

		if (!($this->result = $this->linker->query($sql))) $this->error($sql);

		// Stop and aggregate the query time results
		$time_end = microtime(TRUE);
		$query_times = $time_end - $time_start;
		if( $this->settings["isdebug"] && $func ) $this->sqllog($sql,number_format($query_times, 4)." :: ".$func);
		return ($this->result) ? true : false;
	}

	function nextr() {
		if(!$this->result) {
			$this->error("No query pending");
			return false;
		}
		unset($this->row);
		$this->row = $this->result->fetch_array();
		return ($this->row) ? true : false ;
	}

	function get_row($mode = "both") {
		if(!$this->row) return false;

		$return = array();
		switch($mode) {
			case "assoc":
				foreach($this->row as $k => $v) {
					if(!is_int($k)) $return[$k] = $v;
				}
				break;
			case "num":
				foreach($this->row as $k => $v) {
					if(is_int($k)) $return[$k] = $v;
				}
				break;
			default:
				$return = $this->row;
				break;
		}
		return array_map("stripslashes",$return);
	}

	function get_all($mode = "both", $key = false) {
		if(!$this->result) {
			$this->error("No query pending");
			return false;
		}
		$return = array();
		while($this->nextr()) {
			if($key !== false) $return[$this->f($key)] = $this->get_row($mode);
			else $return[] = $this->get_row($mode);
		}
		return $return;
	}

	function f($index) {
		return stripslashes($this->row[$index]);
	}

	function go_to($row) {
		if(!$this->result) {
			$this->error("No query pending");
			return false;
		}
		if(!mysqli_data_seek($this->result, $row)) $this->error();
	}

	function nf() {
		if ($numb = mysqli_num_rows($this->result) === false) $this->error();
		return mysqli_num_rows($this->result);
	}
	function af() {
		return mysqli_affected_rows();
	}

	function sqllog($string="",$time="") {
		$handle = @fopen($this->settings["isdebug"], "a+");
		if($handle) {
			@fwrite($handle, "[".date("Y-m-d H:i:s")."] [".$time."]	".$string."\n");
			@fclose($handle);
		}
	}

	function error($string="") {
		$error = $this->linker->error;
		if($this->settings["show_error"]) echo $error;
		if($this->settings["error_file"] !== false) {
			$handle = @fopen($this->settings["error_file"], "a+");
			if($handle) {
				@fwrite($handle, "[".date("Y-m-d H:i:s")."] ".$string." <".$error.">\n");
				@fclose($handle);
			}
		}
		if($this->settings["dieonerror"]) {
			if(isset($this->result)) mysqli_free_result($this->result);
			mysqli_close($this->linker);
			die();
		}
	}
	function insert_id() {
		if(!$this->linker) return false;
		return $this->linker->insert_id;
	}
	function escape($string){
		if(!$this->linker) return addslashes($string);

		//return mysqli_real_escape_string($string);	// 한글에 대한 오류로 인해 아래 방식으로 변경
		return addslashes($string);
	}

	function destroy(){
		if (isset($this->result)) $this->result->free_result();
		if (isset($this->linker)) $this->linker->close();
	}
}
?>